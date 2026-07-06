<?php

class LSD_Actions_Create_Directory_Page extends LSD_Actions_Action
{
    public function get_id(): string
    {
        return 'create_directory_page';
    }

    public function get_label(): string
    {
        return esc_html__('Create Directory Page', 'listdom');
    }

    public function get_capability(): string
    {
        return 'publish_pages';
    }

    public function get_schema(): array
    {
        return [
            'title' => ['type' => 'string', 'required' => true],
            'content' => ['type' => 'string', 'default' => ''],
            'shortcode_id' => ['type' => 'int', 'default' => 0],
            'search_form_id' => ['type' => 'int', 'default' => 0],
            'status' => ['type' => 'string', 'default' => 'publish'],
            'overwrite_existing' => ['type' => 'bool', 'default' => false],
            'reuse_existing' => ['type' => 'bool', 'default' => false],
            'option_name' => ['type' => 'string', 'default' => ''],
            'option_path' => ['type' => 'string', 'default' => ''],
        ];
    }

    public function validate(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        [$input, $errors] = $this->validate_schema($input);
        if (!in_array($input['status'], ['publish', 'draft', 'pending'], true)) $errors[] = esc_html__('Unsupported page status.', 'listdom');

        if ($input['shortcode_id'] > 0 && !get_post($input['shortcode_id'])) $errors[] = esc_html__('Referenced shortcode was not found.', 'listdom');
        if ($input['search_form_id'] > 0 && !get_post($input['search_form_id'])) $errors[] = esc_html__('Referenced search form was not found.', 'listdom');

        $content = trim($input['content']);
        if ($input['shortcode_id'] > 0) $content = trim($content . "\n\n" . '[listdom id="' . $input['shortcode_id'] . '"]');
        if ($input['search_form_id'] > 0) $content = trim($content . "\n\n" . '[listdom-search id="' . $input['search_form_id'] . '"]');
        $input['content'] = $content;

        if (count($errors))
        {
            return $this->failure('validation_failed', esc_html__('The directory page request is invalid.', 'listdom'), $errors);
        }

        $existing = LSD_Base::get_post_by_title($input['title'], 'page');
        $warnings = [];
        if ($existing instanceof WP_Post)
        {
            if ($input['reuse_existing'])
            {
                $warnings[] = esc_html__('An existing page will be reused.', 'listdom');
                $input['existing_post_id'] = (int) $existing->ID;
                $input['reuse_existing_mode'] = true;
            }
            else if (!$input['overwrite_existing'])
            {
                return $this->failure('already_exists', esc_html__('A page with this title already exists.', 'listdom'), [], [
                    'post_id' => (int) $existing->ID,
                    'operation' => 'blocked',
                ]);
            }
            else
            {
                $warnings[] = esc_html__('An existing page will be updated because overwrite mode is enabled.', 'listdom');
                $input['existing_post_id'] = (int) $existing->ID;
            }
        }

        return $this->validated($input, $warnings, [
            'operation' => !empty($input['reuse_existing_mode']) ? 'reuse' : (isset($input['existing_post_id']) ? 'update' : 'create'),
            'title' => $input['title'],
            'content' => $input['content'],
        ]);
    }

    public function execute(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        $post_id = (int) ($input['existing_post_id'] ?? 0);
        if (!empty($input['reuse_existing_mode']) && $post_id > 0)
        {
            if (LSD_Base::is_post($post_id, 'trash')) LSD_Base::untrash_post($post_id);
            $sync_result = $this->sync_reused_page($post_id, $input);
            if (is_wp_error($sync_result))
            {
                return $this->failure('update_failed', $sync_result->get_error_message());
            }

            if ($input['option_name'] !== '' && $input['option_path'] !== '')
            {
                $this->set_nested_option($input['option_name'], $input['option_path'], (int) $post_id);
            }

            $this->mark_post($post_id, $context);

            return $this->success(esc_html__('Existing directory page reused.', 'listdom'), [
                'post_id' => (int) $post_id,
                'permalink' => get_permalink($post_id),
                'operation' => 'reuse',
            ]);
        }

        $creating = $post_id <= 0;

        if ($creating)
        {
            $post_id = wp_insert_post([
                'post_title' => $input['title'],
                'post_content' => $input['content'],
                'post_type' => 'page',
                'post_status' => $input['status'],
            ], true);
        }
        else
        {
            if (LSD_Base::is_post($post_id, 'trash')) LSD_Base::untrash_post($post_id);

            $post_id = wp_update_post([
                'ID' => $post_id,
                'post_title' => $input['title'],
                'post_content' => $input['content'],
                'post_status' => $input['status'],
            ], true);
        }

        if (is_wp_error($post_id))
        {
            return $this->failure($creating ? 'insert_failed' : 'update_failed', $post_id->get_error_message());
        }

        if ($input['option_name'] !== '' && $input['option_path'] !== '')
        {
            $this->set_nested_option($input['option_name'], $input['option_path'], (int) $post_id);
        }
        $this->mark_post((int) $post_id, $context);

        return $this->success(
            $creating ? esc_html__('Directory page created successfully.', 'listdom') : esc_html__('Directory page updated successfully.', 'listdom'),
            [
                'post_id' => (int) $post_id,
                'permalink' => get_permalink($post_id),
                'operation' => $creating ? 'create' : 'update',
            ]
        );
    }

    private function sync_reused_page(int $post_id, array $input)
    {
        $post = get_post($post_id);
        if (!$post instanceof WP_Post) return new WP_Error('invalid_post', esc_html__('The existing page could not be loaded.', 'listdom'));

        $expected_content = isset($input['content']) ? (string) $input['content'] : '';
        $expected_status = isset($input['status']) ? (string) $input['status'] : 'publish';
        $needs_update = $post->post_status !== $expected_status
            || (string) $post->post_title !== (string) $input['title']
            || !$this->page_contains_expected_content($post, $expected_content);

        if (!$needs_update) return $post_id;

        return wp_update_post([
            'ID' => $post_id,
            'post_title' => $input['title'],
            'post_content' => $expected_content,
            'post_status' => $expected_status,
        ], true);
    }

    private function page_contains_expected_content(WP_Post $post, string $expected_content): bool
    {
        if (trim($expected_content) === '') return true;

        $current_content = (string) ($post->post_content ?? '');
        if (trim($current_content) === trim($expected_content)) return true;

        preg_match_all('/\[([a-z0-9_-]+)(\s|]|\/)/i', $expected_content, $matches);
        $shortcodes = isset($matches[1]) && is_array($matches[1]) ? array_unique($matches[1]) : [];

        foreach ($shortcodes as $shortcode)
        {
            if (!preg_match('/\[(' . preg_quote($shortcode, '/') . ')(\s|]|\/)/i', $current_content)) return false;
        }

        return count($shortcodes) > 0;
    }
}
