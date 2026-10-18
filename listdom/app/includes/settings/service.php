<?php

/** Caller-neutral settings reads and conditional updates. */
class LSD_Settings_Service
{
    protected LSD_Settings_Catalog $catalog;

    public function __construct()
    {
        $this->catalog = new LSD_Settings_Catalog();
    }

    public static function permitted(): bool
    {
        return get_current_user_id() > 0 && current_user_can('manage_options');
    }

    public static function save_admin(array $settings, bool $rewrite): void
    {
        self::persist_admin($settings);
        self::after_save($rewrite);
    }

    public static function persist_admin(array $settings): void
    {
        LSD_Options::merge('lsd_settings', $settings);
    }

    public static function after_save(bool $rewrite): bool
    {
        $generated = LSD_Personalize::generate();
        if ($rewrite) LSD_RewriteRules::todo();
        return $generated;
    }

    public function list_settings(array $input)
    {
        if (!self::permitted()) return $this->denied();
        $snapshot = $this->snapshot();
        if (is_wp_error($snapshot)) return $snapshot;
        $definitions = array_values(array_filter($this->catalog->definitions(), static function ($field) use ($input)
        {
            return empty($input['group']) || $field['group'] === $input['group'];
        }));
        $page = max(1, (int) ($input['page'] ?? 1));
        $limit = min(20, max(1, (int) ($input['perPage'] ?? 10)));
        $effective = $snapshot['effective'];
        $items = [];
        foreach (array_slice($definitions, ($page - 1) * $limit, $limit) as $field) $items[] = $this->catalog->describe($field, $effective, $snapshot['stored']);
        return ['settings' => $items, 'page' => $page, 'perPage' => $limit, 'total' => count($definitions), 'hasMore' => $page * $limit < count($definitions),
            'revision' => $this->fingerprint($snapshot, $this->catalog->definitions())];
    }

    public function get_settings(array $input)
    {
        if (!self::permitted()) return $this->denied();
        $snapshot = $this->snapshot();
        if (is_wp_error($snapshot)) return $snapshot;
        $keys = $input['keys'] ?? [];
        $definitions = $this->catalog->definitions();
        if (!is_array($keys) || !$keys || count($keys) > 20 || array_diff($keys, array_keys($definitions))) return new WP_Error('listdom_unknown_setting', __('Request between 1 and 20 approved settings.', 'listdom'));
        $effective = $snapshot['effective'];
        $settings = [];
        foreach (array_unique($keys) as $key) $settings[] = $this->catalog->describe($definitions[$key], $effective, $snapshot['stored'], !empty($input['includeStored']));
        return ['settings' => $settings, 'revision' => $this->fingerprint($snapshot, $definitions)];
    }

    /** expectedRevision is a concurrency precondition, not proof of approval. */
    public function update(array $input)
    {
        if (!self::permitted()) return $this->denied();
        if (array_diff(array_keys($input), ['patch', 'expectedRevision'])
            || !is_string($input['expectedRevision'] ?? null) || !preg_match('/^[a-f0-9]{64}$/D', $input['expectedRevision']))
            return new WP_Error('listdom_invalid_input', __('Supply a settings patch and the revision returned by a settings read.', 'listdom'));
        $audit = new LSD_Action_Context(['user_id' => get_current_user_id(), 'source' => 'abilities']);
        $snapshot = $this->snapshot();
        if (is_wp_error($snapshot)) return $snapshot;
        $patch = $this->catalog->validate_patch($input['patch'] ?? null);
        if (is_wp_error($patch)) return $patch;
        $definitions = $this->catalog->definitions();
        if (!hash_equals($input['expectedRevision'], $this->fingerprint($snapshot, $definitions)))
        {
            $this->log('blocked', $audit, ['code' => 'stale_revision', 'keys' => array_keys($patch)]);
            return new WP_Error('listdom_stale_revision', __('Listdom settings or available modules changed. Read the settings again before updating.', 'listdom'));
        }
        $effective = $snapshot['effective'];
        $next_effective = $this->apply_patch($effective, $patch, $definitions);
        $dependency = $this->dependencies($patch, $next_effective);
        if (is_wp_error($dependency)) return $dependency;
        $changed = [];
        foreach ($patch as $key => $value) if ($this->catalog->value($effective, $definitions[$key]) !== $value) $changed[$key] = $value;
        $before = [];
        foreach ($patch as $key => $value) $before[$key] = $this->catalog->value($effective, $definitions[$key]);
        $this->log('validated', $audit, ['before' => $before, 'requested' => $patch]);
        if ($changed)
        {
            $stored = $this->apply_patch($snapshot['stored'], $changed, $definitions);
            try { $saved = LSD_Options::compare_settings($snapshot, $stored); }
            catch (Throwable $error)
            {
                $this->log('uncertain', $audit, ['code' => 'save_interrupted', 'keys' => array_keys($changed)]);
                return new WP_Error('listdom_settings_uncertain', __('The settings save was interrupted. Inspect the site before another update.', 'listdom'));
            }
            if (is_wp_error($saved))
            {
                $this->log('failed', $audit, ['code' => $saved->get_error_code(), 'keys' => array_keys($changed)]);
                return $saved;
            }
            $rewrite = isset($changed['gallery_featured_image']) || isset($changed['address_autosuggest']);
            try
            {
                if (!self::after_save($rewrite)) throw new RuntimeException('personalized_css_failed');
            }
            catch (Throwable $error)
            {
                $this->log('uncertain', $audit, ['code' => 'side_effect_failed', 'keys' => array_keys($changed)]);
                return new WP_Error('listdom_settings_uncertain', __('Settings may have changed, but their side effects could not be confirmed. Inspect the site before another update.', 'listdom'));
            }
        }
        $current = $this->snapshot();
        if (is_wp_error($current))
        {
            $this->log('uncertain', $audit, ['code' => 'readback_changed', 'keys' => array_keys($patch)]);
            return $current;
        }
        $current_effective = $current['effective'];
        $fields = [];
        foreach ($patch as $key => $value)
        {
            $actual = $this->catalog->value($current_effective, $definitions[$key]);
            $fields[] = ['key' => $key, 'before' => $this->catalog->value($effective, $definitions[$key]), 'requested' => $value, 'current' => $actual, 'verified' => $actual === $value, 'errors' => $actual === $value ? [] : ['readback_mismatch']];
        }
        $verified = !in_array(false, array_column($fields, 'verified'), true);
        $result = ['outcome' => $verified ? ($changed ? 'applied' : 'noop') : 'uncertain', 'verified' => $verified, 'fields' => $fields];
        $this->log($verified ? 'completed' : 'uncertain', $audit, $result);
        if (!$verified) return new WP_Error('listdom_settings_uncertain', __('The saved settings did not match the requested values. Inspect the site before another update.', 'listdom'), $result);
        return $result;
    }

    private function apply_patch(array $settings, array $patch, array $definitions): array
    {
        foreach ($patch as $key => $value)
        {
            $path = $definitions[$key]['path'];
            if (count($path) === 1) $settings[$path[0]] = $value;
            else
            {
                if (!isset($settings[$path[0]]) || !is_array($settings[$path[0]])) $settings[$path[0]] = [];
                $settings[$path[0]][$path[1]] = $value;
            }
        }
        return $settings;
    }

    private function dependencies(array $patch, array $settings)
    {
        if (array_key_exists('dashboard_menu_sidebar_horizontal_mode', $patch) && ($settings['dashboard_menu_sidebar_status'] ?? '') !== 'horizontal')
            return new WP_Error('listdom_setting_dependency', __('Horizontal menu mode requires a horizontal dashboard layout in the resulting configuration.', 'listdom'));
        return true;
    }

    private function fingerprint(array $snapshot, array $definitions): string
    {
        $effective = [];
        foreach ($definitions as $key => $field) $effective[$key] = $this->catalog->value($snapshot['effective'], $field);
        return hash('sha256', serialize([$snapshot['blogId'], $snapshot['serialized'] === null,
            hash('sha256', (string) $snapshot['serialized']), $definitions, $effective]));
    }

    /** Internal raw/effective snapshot; never return it from an ability. */
    public function snapshot()
    {
        if (!self::permitted()) return $this->denied();
        $snapshot = LSD_Options::settings_snapshot();
        // Use the canonical reader, including WordPress option filters, while
        // bypassing request-local caches that may precede another admin's save.
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('lsd_settings', 'options');
        wp_cache_delete('notoptions', 'options');
        LSD_Options::clear_settings_cache();
        $snapshot['effective'] = LSD_Options::settings();
        $after = LSD_Options::settings_snapshot();
        if ($snapshot['serialized'] !== $after['serialized'] || $snapshot['blogId'] !== $after['blogId'])
            return new WP_Error('listdom_settings_conflict', __('Settings changed while reading or verifying the operation. Read the settings again.', 'listdom'));
        return $snapshot;
    }

    private function log(string $stage, LSD_Action_Context $context, array $data): void
    {
        LSD_Action_Log::write($stage, 'update_settings', $context, ['blogId' => get_current_blog_id(), 'data' => $data]);
    }

    private function denied(): WP_Error
    {
        return new WP_Error('listdom_settings_forbidden', __('Listdom settings require an authenticated site administrator capability.', 'listdom'));
    }

}
