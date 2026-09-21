<?php

class LSD_Actions_Create_Search_Form extends LSD_Actions_Action
{
    public function get_id(): string
    {
        return 'create_search_form';
    }

    public function get_label(): string
    {
        return esc_html__('Create Search Form', 'listdom');
    }

    public function get_capability(): string
    {
        return 'manage_options';
    }

    public function get_schema(): array
    {
        return [
            'title' => ['type' => 'string', 'required' => true],
            'fields' => ['type' => 'array', 'default' => []],
            'tablet' => ['type' => 'array', 'default' => []],
            'mobile' => ['type' => 'array', 'default' => []],
            'devices' => ['type' => 'array', 'default' => []],
            'form' => ['type' => 'array', 'default' => []],
            'status' => ['type' => 'string', 'default' => 'publish'],
            'overwrite_existing' => ['type' => 'bool', 'default' => false],
            'reuse_existing' => ['type' => 'bool', 'default' => false],
        ];
    }

    public function validate(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        [$input, $errors] = $this->validate_schema($input);
        if (!in_array($input['status'], ['publish', 'draft', 'pending'], true)) $errors[] = esc_html__('Unsupported search form status.', 'listdom');

        if (count($errors))
        {
            return $this->failure('validation_failed', esc_html__('The search form request is invalid.', 'listdom'), $errors);
        }

        $existing = LSD_Base::get_post_by_title($input['title'], LSD_Base::PTYPE_SEARCH);
        $warnings = [];
        if ($existing instanceof WP_Post && $existing->post_status !== 'trash')
        {
            if ($input['reuse_existing'])
            {
                if (!$this->compatible($existing, $input))
                {
                    $original_title = $input['title'];
                    $input = $this->alternative_form($input);
                    $warnings[] = !empty($input['reuse_existing_mode'])
                        ? esc_html__('An existing compatible search form with a conflicting title will be reused.', 'listdom')
                        : sprintf(esc_html__('A search form named "%1$s" does not contain the required blueprint filters or methods. A new form named "%2$s" will be created.', 'listdom'), esc_html($original_title), esc_html($input['title']));
                }
                else
                {
                    $warnings[] = esc_html__('An existing search form will be reused.', 'listdom');
                    $input['existing_post_id'] = (int) $existing->ID;
                    $input['reuse_existing_mode'] = true;
                }
            }
            else if (!$input['overwrite_existing'])
            {
                return $this->failure('already_exists', esc_html__('A search form with this title already exists.', 'listdom'), [], [
                    'post_id' => (int) $existing->ID,
                    'operation' => 'blocked',
                ]);
            }
            else
            {
                $warnings[] = esc_html__('An existing search form will be updated because overwrite mode is enabled.', 'listdom');
                $input['existing_post_id'] = (int) $existing->ID;
            }
        }

        return $this->validated($input, $warnings, [
            'operation' => !empty($input['reuse_existing_mode']) ? 'reuse' : (isset($input['existing_post_id']) ? 'update' : 'create'),
            'title' => $input['title'],
            'status' => $input['status'],
        ]);
    }

    /**
     * Check that a reusable form supports every filter required by the plan.
     *
     * Existing filters that are absent from the plan are intentionally ignored. Wizard
     * reapplication is non-destructive, so disabling an option must not remove filters
     * that may have been customized by an administrator.
     */
    protected function compatible(WP_Post $post, array $input): bool
    {
        $current = get_post_meta($post->ID, 'lsd_fields', true);
        if (!is_array($current)) return false;

        $required = [];
        foreach ($input['fields'] as $row)
        {
            if (!is_array($row) || !isset($row['filters']) || !is_array($row['filters'])) continue;
            foreach ($row['filters'] as $key => $filter)
            {
                $key = (string) $key;
                $required[$key] = is_array($filter) && isset($filter['method']) ? (string) $filter['method'] : '';
            }
        }

        if (!count($required)) return true;

        foreach ($current as $row)
        {
            if (!is_array($row) || !isset($row['filters']) || !is_array($row['filters'])) continue;
            foreach ($row['filters'] as $key => $filter)
            {
                $key = (string) $key;
                if (!array_key_exists($key, $required)) continue;
                if (is_array($filter) && (string) ($filter['method'] ?? '') === $required[$key]) unset($required[$key]);
            }
        }

        return !count($required);
    }

    private function alternative_form(array $input): array
    {
        $base_title = (string) $input['title'];
        $number = 0;

        while (true)
        {
            $title = $number === 0
                ? sprintf(__('%s (Listdom)', 'listdom'), $base_title)
                : sprintf(__('%s (Listdom %d)', 'listdom'), $base_title, $number + 1);
            $existing = LSD_Base::get_post_by_title($title, LSD_Base::PTYPE_SEARCH);

            if ($existing instanceof WP_Post && $existing->post_status !== 'trash')
            {
                if ($this->compatible($existing, $input))
                {
                    $input['title'] = $title;
                    $input['existing_post_id'] = (int) $existing->ID;
                    $input['reuse_existing_mode'] = true;
                    return $input;
                }

                $number++;
                continue;
            }

            $input['title'] = $title;
            return $input;
        }
    }

    public function execute(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        $post_id = (int) ($input['existing_post_id'] ?? 0);
        if (!empty($input['reuse_existing_mode']) && $post_id > 0)
        {
            if (LSD_Base::is_post($post_id, 'trash')) LSD_Base::untrash_post($post_id);

            // Reuse is deliberately non-destructive; do not reconcile stored form fields here.
            return $this->success(esc_html__('Existing search form reused.', 'listdom'), [
                'post_id' => (int) $post_id,
                'shortcode' => '[listdom-search id="' . (int) $post_id . '"]',
                'operation' => 'reuse',
            ]);
        }

        $creating = $post_id <= 0;

        if ($creating)
        {
            $post_id = wp_insert_post([
                'post_title' => $input['title'],
                'post_content' => 'listdom',
                'post_type' => LSD_Base::PTYPE_SEARCH,
                'post_status' => $input['status'],
            ], true);
        }
        else
        {
            if (LSD_Base::is_post($post_id, 'trash')) LSD_Base::untrash_post($post_id);

            $post_id = wp_update_post([
                'ID' => $post_id,
                'post_title' => $input['title'],
                'post_status' => $input['status'],
            ], true);
        }

        if (is_wp_error($post_id))
        {
            return $this->failure($creating ? 'insert_failed' : 'update_failed', $post_id->get_error_message());
        }

        update_post_meta($post_id, 'lsd_fields', $input['fields']);
        update_post_meta($post_id, 'lsd_tablet', $input['tablet']);
        update_post_meta($post_id, 'lsd_mobile', $input['mobile']);
        update_post_meta($post_id, 'lsd_devices', $input['devices']);
        update_post_meta($post_id, 'lsd_form', $input['form']);
        $this->mark_post((int) $post_id, $context);
        if ($creating && $context->source() === 'blueprint') update_post_meta((int) $post_id, 'lsd_blueprint_owned', '1');

        return $this->success(
            $creating ? esc_html__('Search form created successfully.', 'listdom') : esc_html__('Search form updated successfully.', 'listdom'),
            [
                'post_id' => (int) $post_id,
                'shortcode' => '[listdom-search id="' . (int) $post_id . '"]',
                'operation' => $creating ? 'create' : 'update',
            ]
        );
    }
}
