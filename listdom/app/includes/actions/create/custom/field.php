<?php

class LSD_Actions_Create_Custom_Field extends LSD_Actions_Action
{
    public function get_id(): string
    {
        return 'create_custom_field';
    }

    public function get_label(): string
    {
        return esc_html__('Create Custom Field', 'listdom');
    }

    public function get_capability(): string
    {
        return 'manage_categories';
    }

    public function get_schema(): array
    {
        return [
            'name' => ['type' => 'string', 'required' => true],
            'field_type' => ['type' => 'string', 'required' => true],
            'slug' => ['type' => 'string', 'default' => ''],
            'values' => ['type' => 'array', 'default' => []],
            'values_text' => ['type' => 'string', 'default' => ''],
            'all_categories' => ['type' => 'bool', 'default' => true],
            'categories' => ['type' => 'array', 'default' => []],
            'required' => ['type' => 'bool', 'default' => false],
            'editor' => ['type' => 'bool', 'default' => false],
            'link_label' => ['type' => 'string', 'default' => ''],
            'index' => ['type' => 'string', 'default' => '99.00'],
            'icon' => ['type' => 'string', 'default' => ''],
            'disable_icon' => ['type' => 'bool', 'default' => false],
            'itemprop' => ['type' => 'string', 'default' => ''],
            'file_extensions' => ['type' => 'string', 'default' => ''],
            'file_max_size' => ['type' => 'int', 'default' => 0],
            'overwrite_existing' => ['type' => 'bool', 'default' => false],
            'reuse_existing' => ['type' => 'bool', 'default' => false],
        ];
    }

    public function validate(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        [$input, $errors] = $this->validate_schema($input);

        $types = ['text', 'radio', 'checkbox', 'number', 'email', 'tel', 'url', 'date', 'time', 'datetime', 'image', 'file', 'dropdown', 'textarea', 'separator'];
        if (!in_array($input['field_type'], $types, true)) $errors[] = esc_html__('Unsupported custom field type.', 'listdom');

        $values = $input['values'];
        if (!count($values) && $input['values_text'] !== '') $values = preg_split('/[\s,]+/', $input['values_text']);
        $values = array_values(array_filter(array_map('sanitize_text_field', is_array($values) ? $values : [])));

        if (in_array($input['field_type'], ['dropdown', 'radio', 'checkbox'], true) && !count($values))
        {
            $errors[] = esc_html__('Choice-based fields require at least one value.', 'listdom');
        }

        if (!in_array($input['field_type'], ['dropdown', 'radio', 'checkbox'], true)) $values = [];

        $categories = LSD_Taxonomies_Attribute::normalize_categories($input['categories']);
        if (!$input['all_categories'] && !count($categories)) $errors[] = esc_html__('At least one related category is required when all categories is disabled.', 'listdom');

        if ($input['editor']) $input['required'] = false;
        if (!in_array($input['field_type'], ['url', 'email', 'tel'], true)) $input['link_label'] = '';
        if ($input['field_type'] !== 'file')
        {
            $input['file_extensions'] = '';
            $input['file_max_size'] = 0;
        }

        if (count($errors))
        {
            return $this->failure('validation_failed', esc_html__('The custom field request is invalid.', 'listdom'), $errors);
        }

        $input['slug'] = $input['slug'] !== '' ? sanitize_title($input['slug']) : '';
        $input['values'] = $values;
        $input['categories'] = $categories;
        $existing = $input['slug'] !== ''
            ? get_term_by('slug', $input['slug'], LSD_Base::TAX_ATTRIBUTE)
            : get_term_by('name', $input['name'], LSD_Base::TAX_ATTRIBUTE);

        $warnings = [];
        if ($existing instanceof WP_Term)
        {
            if ($input['reuse_existing'])
            {
                $warnings[] = esc_html__('An existing custom field will be reused.', 'listdom');
                $input['existing_term_id'] = (int) $existing->term_id;
                $input['reuse_existing_mode'] = true;
            }
            else if (!$input['overwrite_existing'])
            {
                return $this->failure('already_exists', esc_html__('A matching custom field already exists.', 'listdom'), [], [
                    'term_id' => (int) $existing->term_id,
                    'operation' => 'blocked',
                ]);
            }
            else
            {
                $warnings[] = esc_html__('An existing custom field will be updated because overwrite mode is enabled.', 'listdom');
                $input['existing_term_id'] = (int) $existing->term_id;
            }
        }

        return $this->validated($input, $warnings, [
            'operation' => !empty($input['reuse_existing_mode']) ? 'reuse' : (isset($input['existing_term_id']) ? 'update' : 'create'),
            'field_type' => $input['field_type'],
            'name' => $input['name'],
        ]);
    }

    public function execute(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        $term_id = (int) ($input['existing_term_id'] ?? 0);
        if (!empty($input['reuse_existing_mode']) && $term_id > 0)
        {
            $term = get_term($term_id, LSD_Base::TAX_ATTRIBUTE);
            $this->mark_term($term_id, $context);

            return $this->success(esc_html__('Existing custom field reused.', 'listdom'), [
                'term_id' => $term_id,
                'operation' => 'reuse',
                'slug' => $term instanceof WP_Term ? $term->slug : '',
            ]);
        }

        $creating = $term_id <= 0;

        if ($creating)
        {
            $args = [];
            if ($input['slug'] !== '') $args['slug'] = $input['slug'];

            $insert = wp_insert_term($input['name'], LSD_Base::TAX_ATTRIBUTE, $args);
            if (is_wp_error($insert))
            {
                return $this->failure('insert_failed', $insert->get_error_message());
            }

            $term_id = (int) $insert['term_id'];
        }
        else
        {
            $update = wp_update_term($term_id, LSD_Base::TAX_ATTRIBUTE, [
                'name' => $input['name'],
                'slug' => $input['slug'],
            ]);

            if (is_wp_error($update))
            {
                return $this->failure('update_failed', $update->get_error_message());
            }
        }

        $file_extensions = $this->normalize_extensions($input['file_extensions']);

        update_term_meta($term_id, 'lsd_field_type', $input['field_type']);
        update_term_meta($term_id, 'lsd_values', implode(',', $input['values']));
        update_term_meta($term_id, 'lsd_index', $input['index']);
        update_term_meta($term_id, 'lsd_all_categories', $input['all_categories'] ? 1 : 0);
        update_term_meta($term_id, 'lsd_categories', $input['all_categories'] ? [] : $input['categories']);
        update_term_meta($term_id, 'lsd_icon', $input['icon']);
        update_term_meta($term_id, 'lsd_disabled_icon', $input['disable_icon'] ? 1 : 0);
        update_term_meta($term_id, 'lsd_itemprop', $input['itemprop']);
        update_term_meta($term_id, 'lsd_required', $input['required'] ? 1 : 0);
        update_term_meta($term_id, 'lsd_editor', $input['editor'] ? 1 : 0);
        update_term_meta($term_id, 'lsd_link_label', $input['link_label']);
        update_term_meta($term_id, 'lsd_file_extensions', $file_extensions);
        update_term_meta($term_id, 'lsd_file_max_size', max(0, (int) $input['file_max_size']));
        $this->mark_term($term_id, $context);

        $term = get_term($term_id, LSD_Base::TAX_ATTRIBUTE);

        return $this->success(
            $creating ? esc_html__('Custom field created successfully.', 'listdom') : esc_html__('Custom field updated successfully.', 'listdom'),
            [
                'term_id' => $term_id,
                'operation' => $creating ? 'create' : 'update',
                'slug' => $term instanceof WP_Term ? $term->slug : '',
            ]
        );
    }

    protected function normalize_extensions(string $raw): string
    {
        $extensions = [];
        foreach (preg_split('/[\s,]+/', $raw) as $extension)
        {
            $extension = strtolower(trim((string) $extension));
            $extension = ltrim($extension, '.');
            $extension = preg_replace('/[^a-z0-9]/', '', $extension);

            if ($extension === '' || in_array($extension, $extensions, true)) continue;
            $extensions[] = $extension;
        }

        return implode(',', $extensions);
    }
}
