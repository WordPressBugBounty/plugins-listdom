<?php

class LSD_Actions_Create_Category extends LSD_Actions_Action
{
    public function get_id(): string
    {
        return 'create_category';
    }

    public function get_label(): string
    {
        return esc_html__('Create Category', 'listdom');
    }

    public function get_capability(): string
    {
        return 'manage_categories';
    }

    public function get_schema(): array
    {
        return [
            'name' => ['type' => 'string', 'required' => true],
            'taxonomy' => ['type' => 'string', 'default' => LSD_Base::TAX_CATEGORY],
            'description' => ['type' => 'string', 'default' => ''],
            'parent' => ['type' => 'int', 'default' => 0],
            'slug' => ['type' => 'string', 'default' => ''],
            'color' => ['type' => 'string', 'default' => ''],
            'icon' => ['type' => 'string', 'default' => ''],
            'overwrite_existing' => ['type' => 'bool', 'default' => false],
            'reuse_existing' => ['type' => 'bool', 'default' => false],
        ];
    }

    public function validate(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        [$input, $errors] = $this->validate_schema($input);

        $allowed = [
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_LOCATION,
            LSD_Base::TAX_LABEL,
            LSD_Base::TAX_FEATURE,
            LSD_Base::TAX_TAG,
        ];

        if (!in_array($input['taxonomy'], $allowed, true)) $errors[] = esc_html__('Unsupported taxonomy.', 'listdom');
        if ($input['color'] !== '' && !sanitize_hex_color($input['color'])) $errors[] = esc_html__('Color must be a valid hex value.', 'listdom');
        if ($input['parent'] > 0 && !get_term($input['parent'], $input['taxonomy'])) $errors[] = esc_html__('Parent term was not found.', 'listdom');

        if (count($errors))
        {
            return $this->failure('validation_failed', esc_html__('The category request is invalid.', 'listdom'), $errors);
        }

        $input['slug'] = $input['slug'] !== '' ? sanitize_title($input['slug']) : '';
        $warnings = [];
        $existing = $input['slug'] !== ''
            ? get_term_by('slug', $input['slug'], $input['taxonomy'])
            : get_term_by('name', $input['name'], $input['taxonomy']);

        if ($existing instanceof WP_Term)
        {
            if ($input['reuse_existing'])
            {
                $warnings[] = esc_html__('An existing term will be reused.', 'listdom');
                $input['existing_term_id'] = (int) $existing->term_id;
                $input['reuse_existing_mode'] = true;
            }
            else
            {
                if (!$input['overwrite_existing'])
                {
                    return $this->failure('already_exists', esc_html__('A matching term already exists.', 'listdom'), [], [
                        'term_id' => (int) $existing->term_id,
                        'operation' => 'blocked',
                    ]);
                }

                $warnings[] = esc_html__('An existing term will be updated because overwrite mode is enabled.', 'listdom');
                $input['existing_term_id'] = (int) $existing->term_id;
            }
        }

        $input['color'] = sanitize_hex_color($input['color']) ?: '';
        return $this->validated($input, $warnings, [
            'operation' => !empty($input['reuse_existing_mode']) ? 'reuse' : (isset($input['existing_term_id']) ? 'update' : 'create'),
            'taxonomy' => $input['taxonomy'],
            'name' => $input['name'],
        ]);
    }

    public function execute(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        $term_id = (int) ($input['existing_term_id'] ?? 0);
        if (!empty($input['reuse_existing_mode']) && $term_id > 0)
        {
            $this->mark_term($term_id, $context);

            return $this->success(esc_html__('Existing category reused.', 'listdom'), [
                'term_id' => $term_id,
                'taxonomy' => $input['taxonomy'],
                'operation' => 'reuse',
            ]);
        }

        $creating = $term_id <= 0;

        if ($creating)
        {
            $args = [
                'description' => $input['description'],
                'parent' => $input['parent'],
            ];

            if ($input['slug'] !== '') $args['slug'] = $input['slug'];
            $insert = wp_insert_term($input['name'], $input['taxonomy'], $args);
            if (is_wp_error($insert))
            {
                return $this->failure('insert_failed', $insert->get_error_message());
            }

            $term_id = (int) $insert['term_id'];
        }
        else
        {
            $update = wp_update_term($term_id, $input['taxonomy'], [
                'name' => $input['name'],
                'description' => $input['description'],
                'parent' => $input['parent'],
                'slug' => $input['slug'],
            ]);

            if (is_wp_error($update))
            {
                return $this->failure('update_failed', $update->get_error_message());
            }
        }

        if ($input['icon'] !== '') update_term_meta($term_id, 'lsd_icon', $input['icon']);
        if ($input['color'] !== '') update_term_meta($term_id, 'lsd_color', $input['color']);
        $this->mark_term($term_id, $context);

        return $this->success(
            $creating ? esc_html__('Category created successfully.', 'listdom') : esc_html__('Category updated successfully.', 'listdom'),
            [
                'term_id' => $term_id,
                'taxonomy' => $input['taxonomy'],
                'operation' => $creating ? 'create' : 'update',
            ]
        );
    }
}
