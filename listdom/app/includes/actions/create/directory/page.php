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
            'slug' => ['type' => 'string', 'default' => ''],
            'content' => ['type' => 'string', 'default' => ''],
            'shortcode_id' => ['type' => 'int', 'default' => 0],
            'shortcode_title' => ['type' => 'string', 'default' => ''],
            'search_form_id' => ['type' => 'int', 'default' => 0],
            'search_form_title' => ['type' => 'string', 'default' => ''],
            'directory_page' => ['type' => 'bool', 'default' => false],
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
        $input['slug'] = $input['slug'] !== '' ? sanitize_title($input['slug']) : '';
        if ($input['shortcode_id'] > 0) $content = trim($content . "\n\n" . '[listdom id="' . $input['shortcode_id'] . '"]');
        $input['content'] = $content;

        if (count($errors))
        {
            return $this->failure('validation_failed', esc_html__('The directory page request is invalid.', 'listdom'), $errors);
        }

        $existing = LSD_Base::get_post_by_title($input['title'], 'page');
        $warnings = [];
        if ($existing instanceof WP_Post && $existing->post_status !== 'trash')
        {
            if ($input['reuse_existing'])
            {
                if (!$this->compatible($existing, $input))
                {
                    $original_title = $input['title'];
                    $input = $this->alternative_page($input);
                    $warnings[] = !empty($input['reuse_existing_mode'])
                        ? esc_html__('An existing compatible Listdom page with a conflicting title will be reused.', 'listdom')
                        : sprintf(esc_html__('A page named "%1$s" already exists without the required Listdom page role. A new page named "%2$s" will be created.', 'listdom'), esc_html($original_title), esc_html($input['title']));
                }
                else
                {
                    $warnings[] = esc_html__('An existing compatible Listdom page will be reused without replacing its content.', 'listdom');
                    $input['existing_post_id'] = (int) $existing->ID;
                    $input['reuse_existing_mode'] = true;
                }
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

    private function compatible(WP_Post $post, array $input): bool
    {
        $expected_content = trim((string) ($input['content'] ?? ''));
        if ($expected_content === '' && !empty($input['shortcode_title'])) $expected_content = '[listdom]';
        else if ($expected_content === '' && !empty($input['search_form_title'])) $expected_content = '[listdom-search]';
        if ($expected_content === '' || !preg_match('/\[listdom(?:-[a-z0-9_-]+)?(?:\s|\]|\/)/i', $expected_content)) return false;

        return !count($this->missing_shortcodes((string) $post->post_content, $expected_content));
    }

    private function alternative_page(array $input): array
    {
        $base_title = (string) $input['title'];
        $number = 0;

        while (true)
        {
            $title = $number === 0
                ? sprintf(__('%s (Listdom)', 'listdom'), $base_title)
                : sprintf(__('%s (Listdom %d)', 'listdom'), $base_title, $number + 1);
            $existing = LSD_Base::get_post_by_title($title, 'page');

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
            $sync_result = $this->sync_reused_page($post_id, $input);
            if (is_wp_error($sync_result))
            {
                return $this->failure('update_failed', $sync_result->get_error_message());
            }

            if ($input['option_name'] !== '' && $input['option_path'] !== '')
            {
                $this->set_nested_option($input['option_name'], $input['option_path'], (int) $post_id);
            }

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
                'post_name' => $input['slug'],
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
        if ($creating && $context->source() === 'blueprint') update_post_meta((int) $post_id, 'lsd_blueprint_owned', '1');

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

        $content = (string) $post->post_content;
        $shortcode_id = (int) ($input['shortcode_id'] ?? 0);
        if ($shortcode_id > 0) $content = $this->sync_shortcode_id($content, 'listdom', $shortcode_id);

        $search_form_id = (int) ($input['search_form_id'] ?? 0);
        if ($search_form_id > 0) $content = $this->sync_shortcode_id($content, 'listdom-search', $search_form_id);

        $missing_shortcodes = $this->missing_shortcodes($content, (string) ($input['content'] ?? ''));
        $update = ['ID' => $post_id];

        if (count($missing_shortcodes)) $content = rtrim($content) . "\n\n" . implode("\n", $missing_shortcodes);
        if ($content !== (string) $post->post_content) $update['post_content'] = $content;
        if ($post->post_status !== $input['status']) $update['post_status'] = $input['status'];
        if (count($update) === 1) return $post_id;

        return wp_update_post($update, true);
    }

    private function sync_shortcode_id(string $content, string $tag, int $shortcode_id): string
    {
        $pattern = '/\[' . preg_quote($tag, '/') . '(?=\s|\])([^\]]*)\]/i';
        $updated_content = preg_replace_callback($pattern, static function ($match) use ($tag, $shortcode_id)
        {
            $attributes = isset($match[1]) ? (string) $match[1] : '';
            if (preg_match('/\bid\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s\]]+)/i', $attributes))
            {
                $attributes = preg_replace('/\bid\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s\]]+)/i', 'id="' . $shortcode_id . '"', $attributes, 1);
            }
            else $attributes = ' id="' . $shortcode_id . '"' . $attributes;

            return '[' . $tag . $attributes . ']';
        }, $content, 1, $replacements);

        return $replacements ? (string) $updated_content : $content;
    }

    private function missing_shortcodes(string $content, string $expected_content): array
    {
        preg_match_all('/\[([a-z0-9_-]+)(?:\s+[^\]]*)?\]/i', $expected_content, $matches, PREG_SET_ORDER);
        $shortcodes = is_array($matches) ? $matches : [];

        return array_values(array_map(static function ($shortcode)
        {
            return $shortcode[0];
        }, array_filter($shortcodes, static function ($shortcode) use ($content)
        {
            $tag = isset($shortcode[1]) ? $shortcode[1] : '';
            return !preg_match('/\[' . preg_quote($tag, '/') . '(?=\s|\])/i', $content);
        })));
    }

}
