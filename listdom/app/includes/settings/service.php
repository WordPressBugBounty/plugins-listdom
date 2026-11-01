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
        if (array_key_exists('domain', $input)) return $this->scoped_read($input, false);
        if (array_key_exists('addon', $input)) return $this->scoped_invalid();
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
        if (array_key_exists('domain', $input)) return $this->scoped_read($input, true);
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

    private function scoped_read(array $input, bool $selected)
    {
        $domain_id = $input['domain'];
        if (!is_string($domain_id) || ($selected && $domain_id === '*')) return $this->scoped_invalid();
        $domains = $this->catalog->domains();
        if ($domain_id !== '*' && !isset($domains[$domain_id])) return new WP_Error('listdom_unknown_domain', __('Select a registered settings domain.', 'listdom'));
        $keys = $input['keys'] ?? [];
        if ($selected && (!is_array($keys) || !$keys || count($keys) > 20 || count(array_filter($keys, 'is_string')) !== count($keys))) return $this->scoped_invalid();
        $definitions = [];
        foreach ($domains as $id => $domain)
        {
            if ($domain_id !== '*' && $domain_id !== $id) continue;
            if (isset($input['addon']) && $input['addon'] !== ($domain['addon'] ?? '')) continue;
            foreach ($domain['fields'] as $key => $field)
            {
                if (isset($input['group']) && $input['group'] !== '' && $input['group'] !== $field['group']) continue;
                $definitions[$id . ':' . $key] = $field;
            }
        }
        if ($selected)
        {
            $chosen = [];
            foreach (array_unique($keys) as $key)
            {
                $field = $definitions[$domain_id . ':' . $key] ?? null;
                if (!$field) return new WP_Error('listdom_unknown_setting', __('A requested key is not registered in this domain.', 'listdom'));
                $chosen[] = $field;
            }
        }
        else
        {
            $page = $input['page'] ?? 1;
            $limit = $input['perPage'] ?? 10;
            if (!is_int($page) || $page < 1 || $page > 1000 || !is_int($limit) || $limit < 1 || $limit > 20) return $this->scoped_invalid();
            $chosen = array_slice(array_values($definitions), ($page - 1) * $limit, $limit);
        }
        $snapshots = [];
        $revisions = [];
        // A selected empty page still returns a usable domain snapshot token.
        $read_ids = $domain_id === '*' ? array_values(array_unique(array_column($chosen, 'domain'))) : [$domain_id];
        foreach ($read_ids as $id)
        {
            $writer = $domains[$id]['writer'] ?? null;
            $capture = $writer instanceof LSD_Settings_Writer ? $writer->capture($domains[$id]) : null;
            if (is_wp_error($capture)) return $capture;
            $snapshot = $capture ? $capture['values'] : call_user_func($domains[$id]['read']);
            if (is_wp_error($snapshot)) return $snapshot;
            if (!is_array($snapshot) || !is_array($snapshot['stored'] ?? null) || !is_array($snapshot['effective'] ?? null) || !is_array($snapshot['defaults'] ?? null))
                return new WP_Error('listdom_settings_read_failed', __('The domain reader did not provide a valid snapshot.', 'listdom'));
            $snapshots[$id] = $snapshot;
            $revisions[$id] = LSD_Settings_Writer::revision($id, $domains[$id], $snapshot, $capture['physical'] ?? null);
        }
        $items = [];
        foreach ($chosen as $field) $items[] = $this->catalog->scoped_describe($field, $snapshots[$field['domain']], !empty($input['includeStored']));
        $revision = $domain_id === '*' ? hash('sha256', serialize(['settings-discovery', get_current_blog_id(), array_keys($definitions)])) : $revisions[$domain_id];
        $result = ['settings' => $items, 'revision' => $revision, 'domain' => $domain_id, 'readOnly' => $domain_id === '*' || !($domains[$domain_id]['writer'] ?? null) instanceof LSD_Settings_Writer];
        if (!$selected) $result += ['page' => $page, 'perPage' => $limit, 'total' => count($definitions), 'hasMore' => $page * $limit < count($definitions)];
        $json = wp_json_encode($result);
        if ($json === false || strlen($json) > 32768) return new WP_Error('listdom_result_limit', __('The result is too large. Request fewer settings.', 'listdom'));
        return $result;
    }

    private function scoped_invalid(): WP_Error
    {
        return new WP_Error('listdom_invalid_input', __('Use a registered domain and bounded settings selection.', 'listdom'));
    }

    /** expectedRevision is a concurrency precondition, not proof of approval. */
    public function update(array $input, bool $preview = false)
    {
        if (!self::permitted()) return array_key_exists('domain', $input)
            ? LSD_Settings_Writer::error('listdom_settings_forbidden', __('Listdom settings require an authenticated site administrator capability.', 'listdom')) : $this->denied();
        if (array_key_exists('domain', $input)) return $this->scoped_update($input, $preview);
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
            if (!$preview) $this->log('blocked', $audit, ['code' => 'stale_revision', 'keys' => array_keys($patch)]);
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
        if ($preview) return ['preview' => ['title' => __('Proposed settings changes', 'listdom'), 'fields' => array_map(static function ($key) use ($before, $patch) { return ['key' => $key, 'label' => $key, 'before' => $before[$key], 'after' => $patch[$key]]; }, array_keys($patch)), 'warnings' => []]];
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

    private function scoped_update(array $input, bool $preview = false)
    {
        $id = $input['domain'];
        $patch = $input['patch'] ?? null;
        if ($patch instanceof stdClass) $patch = (array) $patch;
        if (array_diff(array_keys($input), ['domain', 'patch', 'expectedRevision']) || !is_string($id) || $id === '*'
            || !is_array($patch) || !$patch || count($patch) > 20
            || !is_string($input['expectedRevision'] ?? null) || !preg_match('/^[a-f0-9]{64}$/D', $input['expectedRevision']))
            return LSD_Settings_Writer::error('listdom_invalid_input', __('Supply one concrete domain, 1–20 changes and its current revision.', 'listdom'));
        $domains = $this->catalog->domains();
        $domain = $domains[$id] ?? null;
        $writer = $domain['writer'] ?? null;
        if (!$writer instanceof LSD_Settings_Writer)
            return LSD_Settings_Writer::error('listdom_setting_read_only', __('This domain has no approved writer.', 'listdom'));
        ksort($patch);
        $audit = new LSD_Action_Context(['user_id' => get_current_user_id(), 'source' => 'abilities']);
        try
        {
            $capture = $writer->capture($domain);
            if (is_wp_error($capture)) return $capture;
            $revision = LSD_Settings_Writer::revision($id, $domain, $capture['values'], $capture['physical']);
            if (!hash_equals($revision, $input['expectedRevision']))
                return LSD_Settings_Writer::error('listdom_stale_revision', __('Read this domain again before updating; its settings or contract changed.', 'listdom'));
            $prepared = $writer->prepare($domain, $capture, $patch);
            if (is_wp_error($prepared)) return $prepared;
            $fields = [];
            foreach ($patch as $key => $value)
            {
                $before = $this->catalog->scoped_describe($domain['fields'][$key], $capture['values'], false)['effectiveValue'];
                $fields[] = ['key' => $key, 'before' => $before, 'requested' => $value, 'current' => $value, 'verified' => true, 'errors' => []];
            }
            $result = ['domain' => $id, 'outcome' => $prepared['changed'] ? 'applied' : 'unchanged', 'verified' => true, 'revision' => $revision, 'fields' => $fields];
            // Reserve enough output space for bounded error codes and a fresh token.
            $encoded = wp_json_encode($result);
            if ($encoded === false || strlen($encoded) + 1024 > 32768)
                return LSD_Settings_Writer::error('listdom_result_limit', __('Request fewer changes so their verification fits the result limit.', 'listdom'));
            if ($preview) return ['preview' => ['title' => __('Proposed settings changes', 'listdom'),
                'fields' => array_map(static function ($field) { return ['key' => $field['key'], 'label' => $field['key'], 'before' => $field['before'], 'after' => $field['requested']]; }, $fields),
                'warnings' => [__('Owning-package validation and side effects apply. Rendered appearance is unverified.', 'listdom')]], 'revision' => $revision];
            $this->log('validated', $audit, ['domain' => $id, 'before' => array_column($fields, 'before', 'key'), 'requested' => $patch]);
            $guard = function () use ($id, $revision, $patch)
            {
                $fresh = $this->catalog->domains()[$id] ?? null;
                $adapter = $fresh['writer'] ?? null;
                if (!$adapter instanceof LSD_Settings_Writer || !$adapter->permitted())
                    return LSD_Settings_Writer::error('listdom_forbidden', __('The owning settings policy changed.', 'listdom'));
                $current = $adapter->capture($fresh);
                if (is_wp_error($current)) return $current;
                if (!hash_equals($revision, LSD_Settings_Writer::revision($id, $fresh, $current['values'], $current['physical'])))
                    return LSD_Settings_Writer::error('listdom_stale_revision', __('Settings or prerequisites changed before saving.', 'listdom'));
                $valid = $adapter->prepare($fresh, $current, $patch);
                return is_wp_error($valid) ? $valid : true;
            };
            $allowed = $guard();
            if (is_wp_error($allowed)) return $allowed;
        }
        catch (Throwable $error)
        {
            return LSD_Settings_Writer::error('listdom_settings_failed', __('The settings candidate could not be prepared. Nothing was saved.', 'listdom'));
        }
        $readback = false;
        try
        {
            $saved = $writer->commit($domain, $capture, $prepared, $guard);
            if (is_wp_error($saved))
            {
                $this->log('failed', $audit, ['domain' => $id, 'code' => $saved->get_error_code(), 'keys' => array_keys($patch)]);
                return LSD_Settings_Writer::error($saved->get_error_code(), $saved->get_error_message());
            }
            if (get_current_blog_id() !== $capture['physical']['blogId']) throw new RuntimeException('settings_site_changed');
            if ($prepared['changed'] && !$writer->after($prepared)) throw new RuntimeException('settings_side_effect_failed');
            if (get_current_blog_id() !== $capture['physical']['blogId']) throw new RuntimeException('settings_site_changed');
            $fresh = $this->catalog->domains()[$id] ?? null;
            if (!$fresh || !($fresh['writer'] ?? null) instanceof LSD_Settings_Writer || !$fresh['writer']->permitted()) throw new RuntimeException('settings_domain_missing');
            $current = $fresh['writer']->capture($fresh);
            if (is_wp_error($current)) throw new RuntimeException('settings_readback_failed');
            foreach ($result['fields'] as &$field)
            {
                // Include effective values even when a dependency is now disabled.
                $definition = $fresh['fields'][$field['key']];
                $definition['available'] = true;
                $field['current'] = $this->catalog->scoped_describe($definition, $current['values'], false)['effectiveValue'];
                $field['verified'] = LSD_Settings_Writer::equal($field['current'], $field['requested']);
                $field['errors'] = $field['verified'] ? [] : ['readback_mismatch'];
            }
            unset($field);
            $readback = true;
            $result['revision'] = LSD_Settings_Writer::revision($id, $fresh, $current['values'], $current['physical']);
            $result['verified'] = !in_array(false, array_column($result['fields'], 'verified'), true);
            $json = wp_json_encode($result);
            if (!$result['verified'] || $json === false || strlen($json) > 32768) throw new RuntimeException('settings_readback_mismatch');
            $this->log('completed', $audit, $result);
            return $result;
        }
        catch (Throwable $error)
        {
            $this->log('uncertain', $audit, ['domain' => $id, 'keys' => array_keys($patch)]);
            $data = ['domain' => $id];
            if ($readback && strlen((string) wp_json_encode($result)) < 31744)
                $data += ['fields' => $result['fields'], 'revision' => $result['revision']];
            return LSD_Settings_Writer::error('listdom_settings_uncertain', __('The save or its effective readback could not be confirmed. Inspect the site before another update; no retry or rollback was attempted.', 'listdom'), $data, true);
        }
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
