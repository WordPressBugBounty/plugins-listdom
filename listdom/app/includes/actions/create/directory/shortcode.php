<?php

class LSD_Actions_Create_Directory_Shortcode extends LSD_Actions_Action
{
    public function get_id(): string
    {
        return 'create_directory_shortcode';
    }

    public function get_label(): string
    {
        return esc_html__('Create Directory Shortcode', 'listdom');
    }

    public function get_capability(): string
    {
        return 'manage_options';
    }

    public function get_schema(): array
    {
        return [
            'title' => ['type' => 'string', 'required' => true],
            'display' => ['type' => 'array', 'required' => true],
            'search' => ['type' => 'array', 'default' => []],
            'search_form_id' => ['type' => 'int', 'default' => 0],
            'reuse_existing' => ['type' => 'bool', 'default' => false],
        ];
    }

    public function validate(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        [$input, $errors] = $this->validate_schema($input);
        if (($input['display']['skin'] ?? '') !== 'listgrid') $errors[] = esc_html__('The directory shortcode must use the List + Grid skin.', 'listdom');
        if ($input['search_form_id'] > 0 && !get_post($input['search_form_id'])) $errors[] = esc_html__('Referenced search form was not found.', 'listdom');

        if (count($errors)) return $this->failure('validation_failed', esc_html__('The directory shortcode request is invalid.', 'listdom'), $errors);

        $input['search']['searchable'] = 1;
        $input['search']['position'] = 'top';
        $input['search']['shortcode'] = (string) $input['search_form_id'];

        $existing = LSD_Base::get_post_by_title($input['title'], LSD_Base::PTYPE_SHORTCODE);
        $warnings = [];
        if ($existing instanceof WP_Post && $existing->post_status !== 'trash')
        {
            if ($input['reuse_existing'] && $this->compatible($existing, $input))
            {
                $warnings[] = esc_html__('An existing compatible directory shortcode will be reused.', 'listdom');
                $input['existing_post_id'] = (int) $existing->ID;
                $input['reuse_existing_mode'] = true;
            }
            else
            {
                $original_title = $input['title'];
                $input = $this->alternative_shortcode($input);
                $warnings[] = !empty($input['reuse_existing_mode'])
                    ? esc_html__('An existing compatible directory shortcode with a conflicting title will be reused.', 'listdom')
                    : sprintf(esc_html__('A shortcode named "%1$s" already exists with different settings. A new shortcode named "%2$s" will be created.', 'listdom'), esc_html($original_title), esc_html($input['title']));
            }
        }

        return $this->validated($input, $warnings, [
            'operation' => !empty($input['reuse_existing_mode']) ? 'reuse' : 'create',
            'title' => $input['title'],
        ]);
    }

    private function compatible(WP_Post $post, array $input): bool
    {
        $display = get_post_meta($post->ID, 'lsd_display', true);
        $search = get_post_meta($post->ID, 'lsd_search', true);
        $listgrid = is_array($display) && isset($display['listgrid']) && is_array($display['listgrid'])
            ? $display['listgrid']
            : [];

        return is_array($display)
            && ($display['skin'] ?? '') === 'listgrid'
            && ($listgrid['map_provider'] ?? '') === LSD_MP_LEAFLET
            && ($listgrid['map_position'] ?? '') === 'top'
            && is_array($search)
            && (!isset($search['searchable']) || !empty($search['searchable']))
            && ($search['position'] ?? 'top') === 'top'
            && ($input['search_form_id'] <= 0 || (int) ($search['shortcode'] ?? 0) === (int) $input['search_form_id']);
    }

    private function alternative_shortcode(array $input): array
    {
        $base_title = (string) $input['title'];
        $number = 0;

        while (true)
        {
            $title = $number === 0
                ? sprintf(__('%s (Listdom)', 'listdom'), $base_title)
                : sprintf(__('%s (Listdom %d)', 'listdom'), $base_title, $number + 1);
            $existing = LSD_Base::get_post_by_title($title, LSD_Base::PTYPE_SHORTCODE);

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
            return $this->success(esc_html__('Existing directory shortcode reused.', 'listdom'), [
                'post_id' => $post_id,
                'operation' => 'reuse',
            ]);
        }

        $post_id = wp_insert_post([
            'post_title' => $input['title'],
            'post_content' => 'listdom',
            'post_type' => LSD_Base::PTYPE_SHORTCODE,
            'post_status' => 'publish',
        ], true);
        if (is_wp_error($post_id)) return $this->failure('insert_failed', $post_id->get_error_message());

        update_post_meta($post_id, 'lsd_display', $input['display']);
        update_post_meta($post_id, 'lsd_search', $input['search']);
        update_post_meta($post_id, 'lsd_skin', 'listgrid');
        update_post_meta($post_id, 'lsd_filter', []);
        update_post_meta($post_id, 'lsd_exclude', []);
        update_post_meta($post_id, 'lsd_mapcontrols', LSD_Options::defaults('mapcontrols'));
        update_post_meta($post_id, 'lsd_sorts', LSD_Options::defaults('sorts'));
        $this->mark_post((int) $post_id, $context);
        if ($context->source() === 'blueprint') update_post_meta((int) $post_id, 'lsd_blueprint_owned', '1');

        return $this->success(esc_html__('Directory shortcode created successfully.', 'listdom'), [
            'post_id' => (int) $post_id,
            'operation' => 'create',
        ]);
    }
}
