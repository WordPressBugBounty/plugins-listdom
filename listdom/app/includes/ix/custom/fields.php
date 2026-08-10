<?php

class LSD_IX_Custom_Fields extends LSD_IX
{
    public const TYPES = [
        'text',
        'textarea',
        'number',
        'email',
        'tel',
        'url',
        'date',
        'time',
        'datetime',
        'dropdown',
        'radio',
        'checkbox',
    ];

    public static function type_suggestion(array $samples): array
    {
        $values = [];
        foreach ($samples as $sample)
        {
            if (!is_scalar($sample)) continue;

            $value = trim((string) $sample);
            if ($value === '') continue;

            $values[$value] = $value;
            if (count($values) >= 20) break;
        }

        $values = array_values($values);
        if (!count($values)) return ['type' => 'text', 'values' => []];

        $normalized = array_map('strtolower', $values);
        $boolean_values = ['yes', 'no', 'y', 'n', 'true', 'false', '0', '1'];
        if (!array_diff($normalized, $boolean_values)) return ['type' => 'radio', 'values' => $values];

        if (!array_filter($values, static function ($value)
        {
            return !is_numeric($value);
        })) return ['type' => 'number', 'values' => []];

        if (!array_filter($values, static function ($value)
        {
            return !is_email($value);
        })) return ['type' => 'email', 'values' => []];

        if (!array_filter($values, static function ($value)
        {
            return !filter_var($value, FILTER_VALIDATE_URL);
        })) return ['type' => 'url', 'values' => []];

        return ['type' => 'text', 'values' => []];
    }

    public function provision(array $definitions, array $mapping): array
    {
        $prepared = [];
        $errors = [];
        $slugs = [];
        $names = [];

        foreach ($definitions as $index => $definition)
        {
            if (!is_array($definition)) continue;

            $column = -1;
            if (isset($definition['column']) && (is_int($definition['column']) || is_string($definition['column'])))
            {
                $column_value = (string) $definition['column'];
                if (preg_match('/^\d+$/', $column_value)) $column = (int) $column_value;
            }
            $name = isset($definition['name']) ? sanitize_text_field((string) $definition['name']) : '';
            $slug = isset($definition['slug']) && trim((string) $definition['slug']) !== ''
                ? sanitize_title((string) $definition['slug'])
                : sanitize_title($name);
            $field_type = isset($definition['field_type']) ? sanitize_key((string) $definition['field_type']) : 'text';
            $values_text = isset($definition['values']) && is_scalar($definition['values']) ? (string) $definition['values'] : '';
            $values = [];
            if (in_array($field_type, ['dropdown', 'radio', 'checkbox'], true))
            {
                foreach (explode(',', $values_text) as $value)
                {
                    $value = sanitize_text_field(trim($value));
                    if ($value !== '') $values[] = $value;
                }
            }

            $existing = $slug !== '' ? get_term_by('slug', $slug, LSD_Base::TAX_ATTRIBUTE) : null;
            if (!($existing instanceof WP_Term) && $name !== '') $existing = get_term_by('name', $name, LSD_Base::TAX_ATTRIBUTE);

            if ($existing instanceof WP_Term)
            {
                $slug = $existing->slug;
                $name = $existing->name;
            }

            if ($column < 0) $errors[] = esc_html__('Please select a source column for every custom field.', 'listdom');
            if ($name === '') $errors[] = esc_html__('Every custom field needs a name.', 'listdom');
            if ($slug === '') $errors[] = esc_html__('Every custom field needs a valid slug.', 'listdom');
            if (!in_array($field_type, self::TYPES, true)) $errors[] = esc_html__('An unsupported custom field type was selected.', 'listdom');

            if ($name !== '')
            {
                $name_key = function_exists('mb_strtolower')
                    ? mb_strtolower(trim($name), 'UTF-8')
                    : strtolower(trim($name));
                if (isset($names[$name_key])) $errors[] = esc_html__('Each new custom field needs a unique name.', 'listdom');
                $names[$name_key] = true;
            }

            if ($slug !== '')
            {
                if (isset($slugs[$slug])) $errors[] = esc_html__('Each new custom field needs a unique slug.', 'listdom');
                $slugs[$slug] = true;
            }

            $prepared[$index] = [
                'column' => $column,
                'name' => $name,
                'slug' => $slug,
                'field_type' => $field_type,
                'values' => $values,
                'values_text' => '',
                'all_categories' => true,
                'required' => false,
                'editor' => false,
                'link_label' => '',
                'index' => '99.00',
                'icon' => '',
                'disable_icon' => false,
                'itemprop' => '',
                'file_extensions' => '',
                'file_max_size' => 0,
                'reuse_existing' => true,
            ];
        }

        if (!count($prepared)) $errors[] = esc_html__('Add at least one custom field before continuing.', 'listdom');

        $errors = array_values(array_unique(array_filter($errors)));
        if (count($errors)) return ['success' => false, 'errors' => $errors, 'mapping' => $mapping];

        foreach ($prepared as $field)
        {
            $result = LSD_Actions::instance()->execute('create_custom_field', $field, [
                'source' => 'import',
                'dry_run' => true,
            ]);

            if (empty($result['success']))
            {
                $field_errors = isset($result['errors']) && is_array($result['errors']) ? $result['errors'] : [];
                $errors = array_merge($errors, $field_errors ?: [$result['message'] ?? esc_html__('The custom field request is invalid.', 'listdom')]);
            }
        }

        $errors = array_values(array_unique(array_filter($errors)));
        if (count($errors)) return ['success' => false, 'errors' => $errors, 'mapping' => $mapping];

        $created = [];
        $warnings = [];
        foreach ($prepared as $field)
        {
            $result = LSD_Actions::instance()->execute('create_custom_field', $field, [
                'source' => 'import',
            ]);

            if (empty($result['success']))
            {
                $errors[] = $result['message'] ?? esc_html__('The custom field could not be created.', 'listdom');
                continue;
            }

            $data = isset($result['data']) && is_array($result['data']) ? $result['data'] : [];
            $slug = isset($data['slug']) ? sanitize_title((string) $data['slug']) : $field['slug'];
            if ($slug === '')
            {
                $errors[] = esc_html__('The custom field was created without a valid slug.', 'listdom');
                continue;
            }

            $mapping['lsd_attribute_' . $slug] = [
                'map' => $field['column'],
                'default' => '',
            ];

            $created[] = [
                'name' => $field['name'],
                'slug' => $slug,
                'operation' => $data['operation'] ?? 'create',
            ];

            if (isset($result['warnings']) && is_array($result['warnings'])) $warnings = array_merge($warnings, $result['warnings']);
        }

        return [
            'success' => !count($errors),
            'errors' => array_values(array_unique(array_filter($errors))),
            'warnings' => array_values(array_unique(array_filter($warnings))),
            'created' => $created,
            'mapping' => $mapping,
        ];
    }
}
