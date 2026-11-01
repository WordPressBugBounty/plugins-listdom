<?php

/** Conditional saved-view image controls shared by native preview, save and verification. */
class LSD_Repairs_Display
{
    public static function error(string $code, bool $uncertain = false): WP_Error
    {
        return new WP_Error('listdom_repair_' . $code,
            $uncertain ? __('The repair may have changed state. Inspect its independent readback before another change; no retry or rollback was attempted.', 'listdom')
                : __('The repair prerequisites are unavailable or changed. Obtain a fresh preview.', 'listdom'),
            ['outcome' => $uncertain ? 'uncertain' : 'failed']);
    }

    public static function audit(string $action, int $target, array $changes): void
    {
        LSD_Action_Log::write('completed', 'repair_' . $action, new LSD_Action_Context(['source' => 'abilities']),
            ['blogId' => get_current_blog_id(), 'target' => $target, 'changes' => $changes]);
    }

    public static function revision($state): string
    {
        return hash_hmac('sha256', serialize([1, get_current_blog_id(), $state]), wp_salt('auth'));
    }

    public function read(int $id)
    {
        $post = LSD_Diagnostics_Report::resource($id, [LSD_Base::PTYPE_SHORTCODE]);
        if (is_wp_error($post)) return $post;
        $rows = get_post_meta($id, 'lsd_display', false);
        if (count($rows) !== 1 || !is_array($rows[0])) return self::error('invalid_display');
        $report = new LSD_Diagnostics_Report('saved_configuration');
        $result = (new LSD_Diagnostics_Display())->inspect(['view_id' => $id, 'atts' => []], $report);
        if (is_wp_error($result)) return $result;
        $skin = $result['skin'] ?? null;
        // This first release only writes controls with identical first-party mappings.
        if (!$skin || !LSD_Base::isPro() || !in_array($skin->skin, ['grid', 'list'], true)
            || !preg_match('/^style[1-5]$/D', (string) $skin->style) || $skin->is_builder_style()) return self::error('unsupported_display');
        $values = ['display_image' => (bool) $skin->display_image,
            'image_method' => $skin->image_method === 'slider' ? 'slider' : $skin->skin,
            'image_fit' => $skin->image_fit === 'contain' ? 'contain' : 'cover'];
        return ['view_id' => $id, 'skin' => $skin->skin, 'stored' => $rows[0], 'values' => $values,
            'revision' => self::revision([$id, $rows[0], $values, LSD_Base::isPro()])];
    }

    public function prepare(array $input, bool $check_revision = true)
    {
        $state = $this->read((int) ($input['view_id'] ?? 0));
        if (is_wp_error($state)) return $state;
        $patch = $input['patch'] ?? [];
        if (!$patch || count($patch) > 3 || array_diff(array_keys($patch), array_keys($state['values']))) return self::error('invalid_patch');
        foreach ($patch as $key => $value)
        {
            if (($key === 'display_image' && !is_bool($value))
                || ($key === 'image_method' && !in_array($value, [$state['skin'], 'slider'], true))
                || ($key === 'image_fit' && !in_array($value, ['cover', 'contain'], true))) return self::error('invalid_patch');
        }
        if ($check_revision && !hash_equals($state['revision'], (string) ($input['expectedRevision'] ?? ''))) return self::error('stale');
        $state['patch'] = $patch;
        return $state;
    }

    public function preview(array $input)
    {
        $state = $this->prepare($input);
        if (is_wp_error($state)) return $state;
        $fields = [];
        $labels = ['display_image' => __('Show image', 'listdom'), 'image_method' => __('Image source method', 'listdom'), 'image_fit' => __('Image fit', 'listdom')];
        foreach ($state['patch'] as $key => $value)
        {
            $fields[] = ['key' => $key, 'label' => $labels[$key], 'before' => $state['values'][$key], 'after' => $value];
        }
        return ['preview' => ['title' => __('Saved directory image controls', 'listdom'), 'fields' => $fields,
            'warnings' => [__('This changes the saved view everywhere it is used. Browser appearance is not verified.', 'listdom')]], 'revision' => $state['revision']];
    }

    public function update(array $input)
    {
        $state = $this->prepare($input);
        if (is_wp_error($state)) return $state;
        $candidate = $state['stored'];
        if (isset($candidate[$state['skin']]) && !is_array($candidate[$state['skin']])) return self::error('invalid_display');
        foreach ($state['patch'] as $key => $value)
        {
            $candidate[$state['skin']][$key] = $value;
        }
        $candidate = LSD_PTypes_Shortcode::normalize_display($candidate);
        if ($candidate !== array_replace_recursive($state['stored'], [$state['skin'] => $state['patch']])) return self::error('normalization');
        $changed = false;
        foreach ($state['patch'] as $key => $value)
        {
            if ($state['values'][$key] !== $value) $changed = true;
        }
        try
        {
            $fresh = $this->read($state['view_id']);
            if (is_wp_error($fresh) || $fresh['revision'] !== $state['revision']) return self::error('stale');
            if ($changed && !LSD_PTypes_Shortcode::persist_display($state['view_id'], $candidate, $state['stored'])) return self::error('save', true);
            $current = $this->read($state['view_id']);
            if (is_wp_error($current)) return self::error('readback', true);
            foreach ($state['patch'] as $key => $value)
            {
                if ($current['values'][$key] !== $value) return self::error('readback', true);
            }
            if ($changed)
            {
                self::audit('display', $state['view_id'], $state['patch']);
                do_action('lsd_ability_repair_applied', 'display', $state['view_id'], $state['patch']);
            }
            return ['outcome' => $changed ? 'applied' : 'unchanged', 'revision' => $current['revision'], 'values' => $current['values']];
        }
        catch (Throwable $error)
        {
            return self::error('interrupted', true);
        }
    }

    public function verify(array $input)
    {
        $state = $this->prepare($input, false);
        if (is_wp_error($state)) return $state;
        $matched = 0;
        foreach ($state['patch'] as $key => $value)
        {
            if ($state['values'][$key] === $value) $matched++;
        }
        return ['verification' => ['outcome' => $matched === count($state['patch']) ? 'fixed' : ($matched ? 'partially_fixed' : 'not_fixed'),
            'scope' => 'saved_configuration'], 'values' => $state['values'],
            'limitations' => [__('Rendered appearance requires browser inspection.', 'listdom')]];
    }
}
