<?php

/** Reviewed settings only; UI fields and third-party filters are not an allowlist. */
class LSD_Settings_Catalog
{
    public function definitions(): array
    {
        $enum = static function (array $values): array
        {
            return ['type' => is_int($values[0]) ? 'integer' : 'string', 'enum' => $values];
        };
        $limit = ['type' => ['integer', 'string'], 'anyOf' => [['type' => 'integer', 'minimum' => 0, 'maximum' => 2147483647], ['type' => 'string', 'enum' => ['']]]];
        $fields = [
            'dashboard_form_columns' => [__('Dashboard form columns', 'listdom'), __('Number of columns in the submission form.', 'listdom'), 'dashboard', 'general', $enum([1, 2]), 2],
            'dashboard_menu_sidebar_status' => [__('Dashboard menu layout', 'listdom'), __('Layout of the frontend dashboard navigation.', 'listdom'), 'dashboard', 'general', $enum(['default', 'compact', 'horizontal']), 'horizontal'],
            'dashboard_menu_sidebar_horizontal_mode' => [__('Horizontal dashboard menu mode', 'listdom'), __('Navigation mode used when the dashboard layout is horizontal.', 'listdom'), 'dashboard', 'general', $enum(['default', 'dropdown', 'carousel']), 'default'],
            'gallery_featured_image' => [__('Featured image in gallery', 'listdom'), __('Include the featured image always, only as a fallback, or never.', 'listdom'), 'gallery', 'general', $enum(['always', 'fallback', 'never']), 'always'],
            'address_autosuggest' => [__('Address autosuggest', 'listdom'), __('Enable or disable address suggestions when the map component is available.', 'listdom'), 'map', 'map-module', $enum([0, 1]), 1],
            'submission_max_gallery_images' => [__('Maximum gallery images', 'listdom'), __('Maximum gallery image count; empty or zero means no Listdom count restriction.', 'listdom'), 'submission', 'restrictions', $limit, ''],
            'submission_max_image_upload_size' => [__('Maximum image upload size', 'listdom'), __('Maximum image size in KB; empty or zero removes this Listdom restriction. Server upload limits still apply.', 'listdom'), 'submission', 'restrictions', $limit, ''],
            'submission_max_description_length' => [__('Maximum description length', 'listdom'), __('Maximum description length; empty or zero means no Listdom length restriction.', 'listdom'), 'submission', 'restrictions', $limit, ''],
            'submission_image_aspect_ratio' => [__('Submission image aspect ratio', 'listdom'), __('Required image ratio, or empty for no ratio restriction.', 'listdom'), 'submission', 'restrictions', $enum(['', '16:9', '4:3', '3:2', '1:1', '2:3', '3:4', '9:16']), ''],
        ];
        $defaults = LSD_Options::defaults();
        $definitions = [];
        foreach ($fields as $key => $field)
        {
            $tab = in_array($field[2], ['dashboard', 'submission'], true) ? 'frontend-dashboard' : 'general';
            $available = $key !== 'address_autosuggest' || LSD_Components::map();
            $definitions[$key] = $this->field($key, [$key], $field[0], $field[1], $field[2], $tab, $field[3], $field[4], $defaults[$key] ?? $field[5], $available);
        }

        // This fixed reviewed set is intersected with live availability. An addon
        // cannot gain option-write access just by adding a dashboard form module.
        $reviewed = ['contact', 'remark', 'gallery', 'attributes', 'locations', 'tags', 'features', 'labels', 'image', 'embed', 'faq', 'excerpt', 'cta', 'related', 'address', 'price', 'availability'];
        $available = array_column((new LSD_Dashboard())->modules(), 'label', 'key');
        $components = ['remark' => LSD_Components::remark(), 'embed' => LSD_Components::embed(), 'faq' => LSD_Components::faq(),
            'cta' => LSD_Components::cta(), 'related' => LSD_Components::related(), 'address' => LSD_Components::map(),
            'price' => LSD_Components::pricing(), 'availability' => LSD_Components::work_hours()];
        foreach ($reviewed as $module)
        {
            $key = 'submission_module.' . $module;
            $definitions[$key] = $this->field($key, ['submission_module', $module],
                sprintf(__('Submission module: %s', 'listdom'), sanitize_text_field($available[$module] ?? ucwords($module))),
                __('0 disables the module, 1 enables it, and 2 restricts it to editors and administrators.', 'listdom'),
                'submission', 'frontend-dashboard', 'restrictions', $enum([0, 1, 2]), $defaults['submission_module'][$module] ?? 0,
                isset($available[$module]) && (!array_key_exists($module, $components) || (bool) $components[$module]));
            $definitions[$key]['dependencies'] = ['reviewed dashboard module available'];
        }
        $definitions['dashboard_menu_sidebar_horizontal_mode']['dependencies'] = ['dashboard_menu_sidebar_status=horizontal'];
        $definitions['address_autosuggest']['dependencies'] = ['map component available'];
        return $definitions;
    }

    private function field(string $key, array $path, string $label, string $description, string $group, string $tab, string $subtab, array $schema, $default, bool $available): array
    {
        $label = mb_substr(sanitize_text_field($label), 0, 120);
        $description = mb_substr(sanitize_text_field($description), 0, 300);
        return compact('key', 'path', 'label', 'description', 'group', 'tab', 'subtab', 'schema', 'default', 'available') + ['dependencies' => []];
    }

    public function value(array $settings, array $field)
    {
        $value = $settings;
        foreach ($field['path'] as $part)
        {
            if (!is_array($value) || !array_key_exists($part, $value)) return $this->normalize($field, $field['default']);
            $value = $value[$part];
        }
        return $this->normalize($field, $value);
    }

    public function normalize(array $field, $value)
    {
        // Existing forms persist numbers as strings. Do not expose invalid legacy
        // values verbatim, since only the reviewed value domain is model-visible.
        if (is_string($value) && preg_match('/^(0|[1-9][0-9]*)$/D', $value) && strlen($value) <= 10) $value = (int) $value;
        return $this->valid_value($field, $value) ? $value : null;
    }

    private function valid_value(array $field, $value): bool
    {
        if (isset($field['schema']['enum'])) return in_array($value, $field['schema']['enum'], true);
        return $value === '' || (is_int($value) && $value >= 0 && $value <= 2147483647);
    }

    public function patch_schema(): array
    {
        $properties = [];
        foreach ($this->definitions() as $key => $field)
        {
            if ($field['available']) $properties[$key] = $field['schema'] + ['description' => $field['description']];
        }
        return ['type' => 'object', 'properties' => $properties, 'additionalProperties' => false, 'minProperties' => 1, 'maxProperties' => 20];
    }

    public function validate_patch($patch)
    {
        if (!is_array($patch) || !$patch || count($patch) > 20) return new WP_Error('listdom_invalid_patch', __('Provide between 1 and 20 approved setting changes.', 'listdom'));
        $definitions = $this->definitions();
        foreach ($patch as $key => $value)
        {
            if (!isset($definitions[$key]) || !$definitions[$key]['available']) return new WP_Error('listdom_setting_unavailable', __('The patch contains an unknown or unavailable setting.', 'listdom'));
            $schema = $definitions[$key]['schema'];
            // Native REST validation accepts numeric strings; machine writes use
            // actual integers so the exact approved value has one representation.
            if (is_string($value) && $value !== '' && (in_array($key, ['dashboard_form_columns', 'address_autosuggest'], true) || strpos($key, 'submission_module.') === 0 || isset($schema['anyOf'])))
                return new WP_Error('listdom_invalid_value', __('Use an integer for numeric settings, or empty for an unlimited restriction.', 'listdom'));
            if (!$this->valid_value($definitions[$key], $value)) return new WP_Error('listdom_invalid_value', __('A setting value does not match its approved schema.', 'listdom'));
        }
        ksort($patch);
        return $patch;
    }

    public function describe(array $field, array $effective, array $stored, bool $include_stored = false): array
    {
        $result = [
            'key' => $field['key'], 'label' => sanitize_text_field($field['label']), 'description' => $field['description'], 'group' => $field['group'],
            'allowedValues' => $field['schema']['enum'] ?? [], 'type' => isset($field['schema']['anyOf']) ? 'limit' : $field['schema']['type'],
            'minimum' => $field['schema']['anyOf'][0]['minimum'] ?? null,
            'maximum' => $field['schema']['anyOf'][0]['maximum'] ?? null,
            'defaultValue' => $this->normalize($field, $field['default']), 'effectiveValue' => $this->value($effective, $field),
            'available' => $field['available'], 'readable' => true, 'writable' => $field['available'], 'risk' => 'configuration',
            'dependencies' => $field['dependencies'], 'tab' => $field['tab'], 'subtab' => $field['subtab'],
            'adminUrl' => (new LSD_Checklist_Helper())->settings_action_url($field['tab'], $field['subtab']),
        ];
        if ($include_stored)
        {
            $cursor = $stored;
            $present = true;
            foreach ($field['path'] as $part)
            {
                if (!is_array($cursor) || !array_key_exists($part, $cursor)) { $present = false; break; }
                $cursor = $cursor[$part];
            }
            $result['storedPresent'] = $present;
            $result['storedValue'] = $present ? $this->normalize($field, $cursor) : null;
        }
        return $result;
    }
}
