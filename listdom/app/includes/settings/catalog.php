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

    /** The existing 26-field definitions() remain the legacy compatibility view. */
    public function domains(): array
    {
        $domains = LSD_Settings_Core::domains($this);
        /**
         * Trusted Listdom modules register named domains, not arbitrary UI fields.
         * Each domain declares owner/addon/available, a read callback returning
         * stored/effective/defaults, and explicit bounded field definitions.
         * Writers are fixed package-owned adapters with explicit field policies.
         */
        $domains = apply_filters('lsd_settings_domains', $domains);
        if (!is_array($domains)) return [];
        foreach ($domains as $id => &$domain)
        {
            if (!is_string($id) || !preg_match('/^(core|addons)\.[a-z0-9_-]+$/D', $id)
                || !is_array($domain) || !is_callable($domain['read'] ?? null) || !is_array($domain['fields'] ?? null))
            {
                unset($domains[$id]);
                continue;
            }
            foreach ($domain['fields'] as $key => &$field)
            {
                if (!is_string($key) || strlen($key) > 191 || !is_array($field)
                    || !is_array($field['path'] ?? null) || !$field['path'] || !is_array($field['schema'] ?? null))
                {
                    unset($domain['fields'][$key]);
                    continue;
                }
                $field += ['key' => $key, 'label' => $key, 'description' => '', 'group' => '', 'tab' => 'addons', 'subtab' => '',
                    'available' => true, 'dependencies' => [], 'sensitivity' => 'configuration', 'default' => null, 'capability' => 'manage_options'];
                $field['domain'] = $id;
                $field['owner'] = mb_substr(sanitize_text_field($domain['owner'] ?? 'Listdom'), 0, 100);
                $field['addon'] = sanitize_key($domain['addon'] ?? '');
                $field['readable'] = current_user_can($field['capability']);
                $field['available'] = $field['readable'] && !empty($field['available']) && !empty($domain['available']);
                $field['write'] = $field['write'] ?? 'read_only';
                $writer = $domain['writer'] ?? null;
                $field['writeReason'] = $field['sensitivity'] === 'status_only' ? 'status_only'
                    : (!$field['readable'] ? 'permission'
                    : ($field['write'] !== 'leaf' ? 'read_only'
                    : (!$writer instanceof LSD_Settings_Writer ? 'domain_writer_not_available'
                    : (empty($domain['available']) ? 'dependency' : (!$field['available'] || empty($field['writeAvailable'] ?? true) ? 'dependency' : '')))));
                $field['writable'] = $field['writeReason'] === '';
            }
            unset($field);
            ksort($domain['fields']);
        }
        unset($domain);
        ksort($domains);
        return $domains;
    }

    /** Read only a declared path; never serialize an option tree. */
    public function at($values, array $path, &$present = null)
    {
        $present = true;
        foreach ($path as $part)
        {
            if (!is_array($values) || !array_key_exists($part, $values))
            {
                $present = false;
                return null;
            }
            $values = $values[$part];
        }
        return $values;
    }

    public function scoped_value(array $field, $value)
    {
        if (($field['projection'] ?? '') === 'configured') return !empty($value);
        if (($field['projection'] ?? '') === 'first_reference' && is_array($value)) $value = $value[0] ?? 0;
        if (($field['projection'] ?? '') === 'integer_keys')
        {
            // Some checkbox maps persist IDs as keys rather than array values.
            // Invalid/non-ID keys are withheld, never silently dropped or leaked.
            if (!is_array($value)) return null;
            $ids = [];
            foreach (array_keys($value) as $key)
            {
                if (!preg_match('/^[0-9]{1,10}$/D', (string) $key)) return null;
                $ids[] = (int) $key;
            }
            $value = $ids;
        }
        $value = $this->scoped_representation($field['schema'], $value);
        if (is_wp_error(rest_validate_value_from_schema($value, $field['schema'], 'setting'))) return null;
        return $value;
    }

    /** Normalize only schema-declared representations, including saved arrays. */
    private function scoped_representation(array $schema, $value)
    {
        foreach ($schema['anyOf'] ?? [] as $choice)
        {
            $candidate = $this->scoped_representation($choice, $value);
            if (!is_wp_error(rest_validate_value_from_schema($candidate, $choice, 'setting'))) return $candidate;
        }
        $type = $schema['type'] ?? '';
        // WordPress validates integer-like form strings without coercing them.
        if (($type === 'integer' || (isset($schema['enum'][0]) && is_int($schema['enum'][0])))
            && is_string($value) && preg_match('/^-?[0-9]{1,10}$/D', $value)) $value = (int) $value;
        elseif ($type === 'number' && is_string($value) && is_numeric($value) && strlen($value) <= 32) $value = (float) $value;
        if ($type === 'array' && is_array($value) && isset($schema['items']))
            foreach ($value as &$item) $item = $this->scoped_representation($schema['items'], $item);
        unset($item);
        if ($type === 'object' && (is_array($value) || is_object($value)))
        {
            $value = (array) $value;
            foreach ($schema['properties'] ?? [] as $key => $property)
                if (array_key_exists($key, $value)) $value[$key] = $this->scoped_representation($property, $value[$key]);
            $value = (object) $value;
        }
        return $value;
    }

    public function scoped_describe(array $field, array $snapshot, bool $include_stored): array
    {
        $stored = $this->at($snapshot['stored'], $field['path'], $present);
        $default = $this->at($snapshot['defaults'], $field['path'], $has_default);
        if (!$has_default) $default = $field['default'];
        $effective = $this->at($snapshot['effective'], $field['path'], $has_effective);
        if (!$has_effective) $effective = $default;
        if (isset($field['customizer']))
        {
            $section_path = $field['customizer'];
            $seen = [];
            do
            {
                $section_key = implode('.', $section_path);
                if (isset($seen[$section_key])) { $effective = null; break; }
                $seen[$section_key] = true;
                $section = $this->at($snapshot['effective'], $section_path);
                if (!is_array($section)) break;
                if (empty($section['inherit']) || empty($section['inherit_from']) || !is_string($section['inherit_from']))
                {
                    $resolved = $this->at($section, array_slice($field['path'], 2), $has_resolved);
                    if ($has_resolved) $effective = $resolved;
                    break;
                }
                $section_path = explode('.', $section['inherit_from']);
            }
            while (true);
        }
        $value = !empty($snapshot['invalidFields'][$field['key']]) ? null : $this->scoped_value($field, $effective);
        $status = !$field['available'] ? 'unavailable' : ($value === null ? 'invalid' : 'valid');
        $result = [
            'key' => $field['key'], 'domain' => $field['domain'], 'owner' => $field['owner'], 'addon' => $field['addon'],
            'path' => $field['path'], 'label' => mb_substr(sanitize_text_field($field['label']), 0, 120),
            'description' => mb_substr(sanitize_text_field($field['description']), 0, 300), 'group' => $field['group'],
            'allowedValues' => $field['schema']['enum'] ?? [],
            'type' => isset($field['schema']['anyOf']) ? 'limit' : (is_string($field['schema']['type'] ?? null) ? $field['schema']['type'] : 'union'),
            'minimum' => $field['schema']['minimum'] ?? ($field['schema']['anyOf'][0]['minimum'] ?? null),
            'maximum' => $field['schema']['maximum'] ?? ($field['schema']['anyOf'][0]['maximum'] ?? null),
            'schema' => (object) $field['schema'], 'defaultValue' => $this->scoped_value($field, $default),
            'effectiveValue' => $field['available'] ? $value : null, 'valueStatus' => $status,
            'available' => $field['available'], 'readable' => $field['readable'], 'writable' => $field['writable'],
            'permissions' => [$field['capability']],
            'writeReason' => $field['writeReason'], 'sensitivity' => $field['sensitivity'],
            'risk' => 'configuration', 'dependencies' => $field['dependencies'], 'tab' => $field['tab'], 'subtab' => $field['subtab'],
            'adminUrl' => (new LSD_Checklist_Helper())->settings_action_url($field['tab'], $field['subtab']),
        ];
        if ($include_stored)
        {
            $result['storedPresent'] = $field['readable'] && $present;
            $result['storedValue'] = $field['readable'] && $present ? $this->scoped_value($field, $stored) : null;
        }
        return $result;
    }

}
