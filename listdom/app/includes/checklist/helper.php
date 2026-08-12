<?php

class LSD_Checklist_Helper
{
    protected const SHORTCODE_SCAN_BATCH_SIZE = 100;

    protected array $published_shortcode_post_ids = [];

    public function published_post_ids(string $post_type): array
    {
        $posts = get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'DESC',
            'suppress_filters' => false,
        ]);

        return array_map('intval', is_array($posts) ? $posts : []);
    }

    public function post_new_url(string $post_type): string
    {
        return admin_url('post-new.php?post_type=' . $post_type);
    }

    public function edit_post_url(int $post_id): string
    {
        if ($post_id < 1) return '';

        $edit_link = get_edit_post_link($post_id, '');
        if (is_string($edit_link) && $edit_link !== '') return $edit_link;

        return admin_url('post.php?post=' . $post_id . '&action=edit');
    }

    public function build_action_url(array $item): string
    {
        $action_url = (string) ($item['action_url'] ?? '');
        if ($action_url === '') return '';

        $focus_target = (string) ($item['action_focus_target'] ?? '');
        if ($focus_target === '') return $action_url;

        $action_query_args = [
            'lsd_checklist_focus' => '1',
            'lsd_checklist_focus_target' => $this->encode_checklist_focus_value($focus_target),
            'lsd_checklist_focus_target_encoding' => 'b64',
        ];

        return add_query_arg($action_query_args, $action_url);
    }

    public function settings_action_url(string $tab, string $subtab = ''): string
    {
        $args = [
            'page' => 'listdom-settings',
            'tab' => $tab,
        ];

        if ($subtab !== '') $args['subtab'] = $subtab;

        return admin_url(add_query_arg($args, 'admin.php'));
    }

    public function category_term_action_url(int $term_id = 0): string
    {
        if ($term_id > 0)
        {
            $edit_link = get_edit_term_link($term_id, LSD_Base::TAX_CATEGORY, LSD_Base::PTYPE_LISTING);
            if (is_string($edit_link) && $edit_link !== '') return $edit_link;
        }

        return admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_CATEGORY . '&post_type=' . LSD_Base::PTYPE_LISTING);
    }

    public function published_posts_count(string $post_type): int
    {
        $counts = wp_count_posts($post_type, 'readable');
        return is_object($counts) ? (int) ($counts->publish ?? 0) : 0;
    }

    public function term_count(string $taxonomy): int
    {
        $count = wp_count_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);

        return is_numeric($count) ? (int) $count : 0;
    }

    /**
     * Get builder widget settings for a Listdom shortcode.
     * @param string $shortcode
     * @return array
     */
    protected function builder_shortcode_widget(string $shortcode): array
    {
        // Builder Widgets
        $widgets = [
            'listdom' => ['type' => 'lsd-listing-shortcode', 'setting' => 'shortcode',],
            'listdom-search' => ['type' => 'lsd-listing-search', 'setting' => 'search',],
        ];

        return $widgets[$shortcode] ?? [];
    }

    /**
     * Get Elementor builder elements for a page.
     * @param int $page_id
     * @return array
     */
    protected function elementor_builder_elements(int $page_id): array
    {
        return $this->elementor_builder_elements_from_value(get_post_meta($page_id, '_elementor_data', true));
    }

    protected function elementor_builder_elements_from_value($raw): array
    {
        $raw = maybe_unserialize($raw);
        if (is_array($raw)) $raw = wp_json_encode($raw);
        if (!is_string($raw) || trim($raw) === '') return [];

        // Decoded Elements
        $elements = json_decode($raw, true);

        return is_array($elements) ? $elements : [];
    }

    /**
     * Get Bricks builder elements for a page.
     * @param int $page_id
     * @return array
     */
    protected function bricks_builder_elements(int $page_id): array
    {
        return $this->bricks_builder_elements_from_values(
            get_post_meta($page_id, '_bricks_page_content_2', true),
            get_post_meta($page_id, '_bricks_page_content', true)
        );
    }

    protected function bricks_builder_elements_from_values($primary, $fallback): array
    {
        $elements = maybe_unserialize($primary);
        if (!is_array($elements) || !count($elements)) $elements = maybe_unserialize($fallback);

        return is_array($elements) ? $elements : [];
    }

    /**
     * Get public post types that can host frontend shortcodes.
     * @return array
     */
    protected function shortcode_host_post_types(): array
    {
        // Host Types
        $post_types = get_post_types(['public' => true], 'names');
        if (!is_array($post_types)) return ['page', 'post'];

        $post_types = array_values(array_diff($post_types, ['attachment']));

        return count($post_types) ? $post_types : ['page', 'post'];
    }

    protected function builder_content_meta_keys(int $post_id = 0): array
    {
        $meta_keys = apply_filters(
            'lsd_checklist_builder_content_meta_keys',
            [
                '_elementor_data',
                '_bricks_page_content_2',
                '_bricks_page_content',
            ],
            $post_id
        );

        if (!is_array($meta_keys)) return [];

        return array_values(array_filter($meta_keys, 'is_string'));
    }

    protected function shortcode_host_post_batches(): Generator
    {
        global $wpdb;

        $post_types = $this->shortcode_host_post_types();
        if (!count($post_types)) return;

        // Keep the report memory-bounded while preserving post-query visibility filters.
        $page = 1;

        do
        {
            $query = new WP_Query([
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => self::SHORTCODE_SCAN_BATCH_SIZE,
                'paged' => $page,
                'fields' => 'ids',
                'orderby' => 'ID',
                'order' => 'ASC',
                'suppress_filters' => false,
                'no_found_rows' => true,
                'cache_results' => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ]);
            $post_ids = array_map('absint', is_array($query->posts) ? $query->posts : []);
            if (!count($post_ids)) break;

            $meta_keys = [
                '_elementor_data',
                '_bricks_page_content_2',
                '_bricks_page_content',
            ];
            foreach ($post_ids as $post_id)
            {
                $meta_keys = array_merge($meta_keys, $this->builder_content_meta_keys($post_id));
            }

            $posts = $this->shortcode_host_posts($post_ids);
            yield [$posts, $this->builder_meta_values($post_ids, array_values(array_unique($meta_keys)))];

            $page++;
        }
        while (true);
    }

    protected function shortcode_host_posts(array $post_ids): array
    {
        global $wpdb;

        if (!count($post_ids)) return [];

        $post_id_placeholders = implode(', ', array_fill(0, count($post_ids), '%d'));
        $query = $wpdb->prepare(
            "SELECT ID, post_content, post_password
            FROM {$wpdb->posts}
            WHERE ID IN ({$post_id_placeholders})",
            $post_ids
        );
        $rows = $wpdb->get_results($query);
        $posts = [];

        foreach ($rows as $row)
        {
            if ($row->post_password !== '') continue;

            $posts[(int) $row->ID] = $row;
        }

        return $posts;
    }

    protected function builder_meta_values(array $post_ids, array $meta_keys): array
    {
        global $wpdb;

        if (!count($post_ids) || !count($meta_keys)) return [];

        $post_id_placeholders = implode(', ', array_fill(0, count($post_ids), '%d'));
        $meta_key_placeholders = implode(', ', array_fill(0, count($meta_keys), '%s'));
        $query = $wpdb->prepare(
            "SELECT meta_id, post_id, meta_key, meta_value
            FROM {$wpdb->postmeta}
            WHERE post_id IN ({$post_id_placeholders})
                AND meta_key IN ({$meta_key_placeholders})
            ORDER BY post_id ASC, meta_key ASC, meta_id ASC",
            array_merge($post_ids, $meta_keys)
        );
        $rows = $wpdb->get_results($query);
        $values = [];

        foreach ($rows as $row)
        {
            $post_id = (int) $row->post_id;
            if (isset($values[$post_id][$row->meta_key])) continue;

            $values[$post_id][$row->meta_key] = $row->meta_value;
        }

        return $values;
    }

    protected function shortcode_post_content_sources(object $post, array $meta_values): array
    {
        $sources = [];
        $post_content = (string) ($post->post_content ?? '');

        if (trim($post_content) !== '') $sources['post_content'] = $post_content;

        foreach ($this->builder_content_meta_keys((int) $post->ID) as $meta_key)
        {
            if (!array_key_exists($meta_key, $meta_values)) continue;

            $value = $meta_values[$meta_key];
            $content = $this->normalize_content_source(maybe_unserialize($value));
            if ($content !== '') $sources[$meta_key] = $content;
        }

        return apply_filters('lsd_checklist_post_content_sources', array_values($sources), (int) $post->ID);
    }

    /**
     * Get Elementor references for a Listdom shortcode widget.
     * @param int $page_id
     * @param string $shortcode
     * @param string $post_type
     * @return array
     */
    protected function elementor_referenced_post_ids(int $page_id, string $shortcode, string $post_type): array
    {
        // Widget Config
        $widget = $this->builder_shortcode_widget($shortcode);
        if (!count($widget)) return [];

        // Builder Elements
        $elements = $this->elementor_builder_elements($page_id);
        if (!count($elements)) return [];

        // Referenced IDs
        $referenced_ids = [];

        $this->collect_elementor_referenced_post_ids(
            $elements,
            $widget['type'],
            $widget['setting'],
            $post_type,
            $referenced_ids
        );

        return array_values($referenced_ids);
    }

    /**
     * Collect Elementor widget references recursively.
     * @param array $elements
     * @param string $widget_type
     * @param string $setting
     * @param string $post_type
     * @param array $referenced_ids
     * @return void
     */
    protected function collect_elementor_referenced_post_ids(array $elements, string $widget_type, string $setting, string $post_type, array &$referenced_ids): void
    {
        // Elements
        foreach ($elements as $element)
        {
            if (!is_array($element)) continue;

            // Widget Match
            if (($element['widgetType'] ?? '') === $widget_type)
            {
                $settings = isset($element['settings']) && is_array($element['settings']) ? $element['settings'] : [];
                $post_id = absint($settings[$setting] ?? 0);

                if ($post_id > 0 && get_post_type($post_id) === $post_type && $this->is_published_post($post_id)) $referenced_ids[$post_id] = $post_id;
            }

            // Children
            if (isset($element['elements']) && is_array($element['elements'])) $this->collect_elementor_referenced_post_ids($element['elements'], $widget_type, $setting, $post_type, $referenced_ids);
        }
    }

    /**
     * Get Bricks references for a Listdom shortcode widget.
     * @param int $page_id
     * @param string $shortcode
     * @param string $post_type
     * @return array
     */
    protected function bricks_referenced_post_ids(int $page_id, string $shortcode, string $post_type): array
    {
        // Widget Config
        $widget = $this->builder_shortcode_widget($shortcode);
        if (!count($widget)) return [];

        // Builder Elements
        $elements = $this->bricks_builder_elements($page_id);
        if (!count($elements)) return [];

        // Referenced IDs
        $referenced_ids = [];
        $this->collect_bricks_referenced_post_ids(
            $elements,
            $widget['type'],
            $widget['setting'],
            $post_type,
            $referenced_ids
        );

        return array_values($referenced_ids);
    }

    /**
     * Collect Bricks widget references recursively.
     * @param array $elements
     * @param string $widget_type
     * @param string $setting
     * @param string $post_type
     * @param array $referenced_ids
     * @return void
     */
    protected function collect_bricks_referenced_post_ids(array $elements, string $widget_type, string $setting, string $post_type, array &$referenced_ids): void
    {
        // Elements
        foreach ($elements as $element)
        {
            if (!is_array($element)) continue;

            // Widget Match
            if (($element['name'] ?? '') === $widget_type)
            {
                $settings = isset($element['settings']) && is_array($element['settings']) ? $element['settings'] : [];
                $referenced_post_id = absint($settings[$setting] ?? 0);

                if ($referenced_post_id > 0 && get_post_type($referenced_post_id) === $post_type && $this->is_published_post($referenced_post_id))
                {
                    $referenced_ids[$referenced_post_id] = $referenced_post_id;
                }
            }

            // Children
            if (isset($element['children']) && is_array($element['children']))
            {
                $this->collect_bricks_referenced_post_ids($element['children'], $widget_type, $setting, $post_type, $referenced_ids);
            }
        }
    }

    public function published_shortcode_post_ids(string $shortcode, string $post_type): array
    {
        $cache_key = $shortcode . ':' . $post_type;
        if (isset($this->published_shortcode_post_ids[$cache_key])) return $this->published_shortcode_post_ids[$cache_key];

        $referenced_ids = [];
        $widget = $this->builder_shortcode_widget($shortcode);
        $widget_type = (string) ($widget['type'] ?? '');
        $widget_setting = (string) ($widget['setting'] ?? '');

        foreach ($this->shortcode_host_post_batches() as [$posts, $meta_values])
        {
            foreach ($posts as $post)
            {
                $post_id = (int) $post->ID;
                $values = $meta_values[$post_id] ?? [];

                foreach ($this->shortcode_post_content_sources($post, $values) as $content)
                {
                    $this->collect_shortcode_referenced_post_ids((string) $content, $shortcode, $post_type, $referenced_ids);
                }

                if ($widget_type === '' || $widget_setting === '') continue;

                $elementor = $this->elementor_builder_elements_from_value($values['_elementor_data'] ?? '');
                $this->collect_elementor_referenced_post_ids($elementor, $widget_type, $widget_setting, $post_type, $referenced_ids);

                $bricks = $this->bricks_builder_elements_from_values(
                    $values['_bricks_page_content_2'] ?? '',
                    $values['_bricks_page_content'] ?? ''
                );
                $this->collect_bricks_referenced_post_ids($bricks, $widget_type, $widget_setting, $post_type, $referenced_ids);
            }
        }

        $this->published_shortcode_post_ids[$cache_key] = array_values($referenced_ids);
        return $this->published_shortcode_post_ids[$cache_key];
    }

    protected function collect_shortcode_referenced_post_ids(string $content, string $shortcode, string $post_type, array &$referenced_ids): void
    {
        $pattern = get_shortcode_regex([$shortcode]);
        $matches = [];

        if (!preg_match_all('/' . $pattern . '/s', wp_unslash($content), $matches, PREG_SET_ORDER)) return;

        foreach ($matches as $match)
        {
            if (($match[1] ?? '') === '[' && ($match[6] ?? '') === ']') continue;
            if (($match[2] ?? '') !== $shortcode) continue;

            $attributes = shortcode_parse_atts($match[3] ?? '');
            if (!is_array($attributes)) continue;

            $referenced_post_id = absint($attributes['id'] ?? 0);
            if ($referenced_post_id < 1) continue;
            if (get_post_type($referenced_post_id) !== $post_type) continue;
            if (!$this->is_published_post($referenced_post_id)) continue;

            $referenced_ids[$referenced_post_id] = $referenced_post_id;
        }
    }

    public function published_search_form_ids(): array
    {
        $resolved = [];

        $direct_form_ids = $this->published_shortcode_post_ids('listdom-search', LSD_Base::PTYPE_SEARCH);

        foreach ($direct_form_ids as $form_id)
        {
            $form_id = absint($form_id);

            if ($form_id > 0) $resolved[$form_id] = $form_id;
        }

        $directory_view_ids = $this->published_shortcode_post_ids('listdom', LSD_Base::PTYPE_SHORTCODE);
        $searchable_skins = LSD_Skins::get_searchable_skins();

        foreach ($directory_view_ids as $shortcode_id)
        {
            $display = get_post_meta($shortcode_id, 'lsd_display', true);
            $skin = is_array($display) && !empty($display['skin']) ? (string) $display['skin'] : 'grid';

            if (!isset($searchable_skins[$skin])) continue;

            $search = get_post_meta($shortcode_id, 'lsd_search', true);
            if (!is_array($search)) continue;

            $form_id = absint($search['shortcode'] ?? 0);

            if ($form_id < 1) continue;

            if (get_post_type($form_id) !== LSD_Base::PTYPE_SEARCH) continue;
            if (!$this->is_published_post($form_id)) continue;

            $resolved[$form_id] = $form_id;
        }

        return array_values($resolved);
    }

    public function content_has_unescaped_shortcode(string $content, string $shortcode): bool
    {
        if (trim($content) === '' || $shortcode === '') return false;

        $content = $this->strip_html_comments($content);
        if (trim($content) === '') return false;

        $pattern = get_shortcode_regex([$shortcode]);
        $matches = [];

        if (!preg_match_all('/' . $pattern . '/s', $content, $matches, PREG_SET_ORDER)) return false;

        foreach ($matches as $match)
        {
            // Ignore escaped forms such as [[listdom-dashboard]].
            if (($match[1] ?? '') === '[' && ($match[6] ?? '') === ']') continue;

            if (($match[2] ?? '') === $shortcode) return true;
        }

        return false;
    }

    /**
     * Remove HTML comments before shortcode checks.
     * @param string $content
     * @return string
     */
    protected function strip_html_comments(string $content): string
    {
        // Commented Content
        return (string) preg_replace('/<!--.*?-->/s', '', $content);
    }

    protected function normalize_content_source($value): string
    {
        if (is_string($value)) return trim($value);

        if (is_array($value) || is_object($value))
        {
            $encoded = wp_json_encode($value);
            return is_string($encoded) ? trim($encoded) : '';
        }

        return '';
    }

    public function post_content_sources(int $post_id): array
    {
        if ($post_id < 1) return [];

        $sources = [];

        $post_content = get_post_field('post_content', $post_id);

        if (is_string($post_content) && trim($post_content) !== '') $sources['post_content'] = $post_content;

        $builder_meta_keys = apply_filters(
            'lsd_checklist_builder_content_meta_keys',
            [
                // Elementor
                '_elementor_data',

                // Bricks
                '_bricks_page_content_2',
                '_bricks_page_content',
            ],
            $post_id
        );

        foreach ($builder_meta_keys as $meta_key)
        {
            $value = get_post_meta($post_id, $meta_key, true);
            $content = $this->normalize_content_source($value);

            if ($content === '') continue;

            $sources[$meta_key] = $content;
        }

        return apply_filters('lsd_checklist_post_content_sources', array_values($sources), $post_id);
    }

    public function published_post_has_shortcodes(int $post_id, array $shortcodes): bool
    {
        if (!$this->is_published_post($post_id)) return false;

        foreach ($this->post_content_sources($post_id) as $content)
        {
            foreach ($shortcodes as $shortcode)
            {
                if ($this->content_has_unescaped_shortcode($content, (string) $shortcode)) return true;
            }
        }

        return false;
    }

    public function count_pages_with_shortcodes(array $shortcodes): int
    {
        $count = 0;

        foreach ($this->shortcode_host_post_batches() as [$posts, $meta_values])
        {
            foreach ($posts as $post)
            {
                foreach ($this->shortcode_post_content_sources($post, $meta_values[(int) $post->ID] ?? []) as $content)
                {
                    foreach ($shortcodes as $shortcode)
                    {
                        if (!$this->content_has_unescaped_shortcode((string) $content, (string) $shortcode)) continue;

                        $count++;
                        continue 3;
                    }
                }
            }
        }

        return $count;
    }

    public function search_form_snapshot(?array $post_ids = null): array
    {
        $posts = $post_ids ?? $this->published_post_ids(LSD_Base::PTYPE_SEARCH);
        $exposed_shortcodes = array_fill_keys($this->published_shortcode_post_ids('listdom', LSD_Base::PTYPE_SHORTCODE), true);

        $snapshot = [
            'count' => 0,
            'ajax' => 0,
            'ajax_ready' => 0,
            'connected_shortcodes' => 0,
            'autocomplete' => 0,
            'ai_search' => 0,
            'locate' => 0,
        ];

        foreach ($posts as $post_id)
        {
            $snapshot['count']++;

            $form = get_post_meta($post_id, 'lsd_form', true);
            $form = is_array($form) ? $form : [];

            $ajax = !empty($form['ajax']);
            $connected = false;

            foreach ((array) ($form['connected_shortcodes'] ?? []) as $shortcode_id)
            {
                $shortcode_id = absint($shortcode_id);

                if ($shortcode_id < 1) continue;

                if (get_post_type($shortcode_id) !== LSD_Base::PTYPE_SHORTCODE) continue;
                if (!isset($exposed_shortcodes[$shortcode_id])) continue;

                $connected = true;
                break;
            }

            if ($ajax) $snapshot['ajax']++;
            if ($connected) $snapshot['connected_shortcodes']++;
            if ($ajax && $connected) $snapshot['ajax_ready']++;

            $form_flags = [
                'autocomplete' => false,
                'ai_search' => false,
                'locate' => false,
            ];

            foreach (['lsd_fields', 'lsd_tablet', 'lsd_mobile'] as $meta_key)
            {
                $this->collect_search_filters(get_post_meta($post_id, $meta_key, true), $snapshot, $form_flags);
            }

            foreach ($form_flags as $key => $enabled)
            {
                if ($enabled && isset($snapshot[$key])) $snapshot[$key]++;
            }
        }

        return $snapshot;
    }

    public function is_published_post(int $post_id): bool
    {
        if ($post_id < 1 || get_post_status($post_id) !== 'publish') return false;

        // Password Protection
        return get_post_field('post_password', $post_id) === '';
    }

    public function single_listing_layout_context(?array $details = null): array
    {
        $details = $details ?? LSD_Options::details_page();
        $style = (string) ($details['general']['style'] ?? '');
        $is_template_builder = strpos($style, 'tb_') === 0;
        $template_id = $is_template_builder ? absint(substr($style, 3)) : 0;
        $is_template = $template_id > 0 && get_post_type($template_id) === LSD_Base::PTYPE_TEMPLATE;

        return [
            'style' => $style,
            'template_id' => $template_id,
            'is_template_builder' => $is_template_builder,
            'is_valid_template' => $is_template && $this->is_published_post($template_id),
            'action_url' => $is_template ? $this->edit_post_url($template_id) : $this->settings_action_url('single-listing', 'style-elements'),
        ];
    }

    public function listing_element_visible(string $element): bool
    {
        $details = LSD_Options::details_page();
        $layout = $this->single_listing_layout_context($details);
        $style = $layout['style'];

        // Template builder check
        if ($layout['is_template_builder'])
        {
            if (!$layout['is_valid_template']) return false;

            $state = json_decode((string) get_post_meta($layout['template_id'], '_lsd_template_layout', true), true);
            $nodes = isset($state['layout']) && is_array($state['layout']) ? $state['layout'] : [];

            return $this->template_layout_has_element($nodes, $element);
        }

        if (empty($details['elements'][$element]['enabled'])) return false;

        if ($style === 'style1') return strpos(LSD_Options::details_page_pattern(), '{' . $element . '}') !== false;

        if ($style === 'dynamic')
        {
            $sections = isset($details['builder']) && is_array($details['builder']) ? $details['builder'] : [];

            foreach ($sections as $section)
            {
                if (!is_array($section)) continue;

                $elements = isset($section['elements']) && is_array($section['elements']) ? $section['elements'] : [];

                if (!empty($elements[$element]['enabled'])) return true;
            }

            return false;
        }

        if (in_array($style, ['style2', 'style3', 'style4'], true)) return !empty($details['elements'][$element]['enabled']);

        return (bool) apply_filters('lsd_checklist_listing_element_visible', false, $element, $details, $style);
    }

    /**
     * Check whether a template layout tree contains an element.
     * @param array $nodes
     * @param string $element
     * @return bool
     */
    protected function template_layout_has_element(array $nodes, string $element): bool
    {
        // Layout Nodes
        foreach ($nodes as $node)
        {
            if (!is_array($node)) continue;

            // Element Type
            if (($node['type'] ?? '') === $element) return true;

            // Children
            if (isset($node['children']) && is_array($node['children']) && $this->template_layout_has_element($node['children'], $element))
            {
                return true;
            }
        }

        return false;
    }

    public function first_published_post_id(string $post_type): int
    {
        $posts = get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'DESC',
            'suppress_filters' => false,
        ]);

        return isset($posts[0]) ? (int) $posts[0] : 0;
    }

    public function ai_visibility_required_fields(): array
    {
        return ['description', 'categories', 'images'];
    }

    protected function encode_checklist_focus_value(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    protected function collect_search_filters($data, array &$snapshot, array &$form_flags): void
    {
        if (!is_array($data)) return;

        foreach ($data as $row)
        {
            if (!is_array($row)) continue;

            $filters = $row['filters'] ?? null;
            if (!is_array($filters))
            {
                $this->collect_search_filters($row, $snapshot, $form_flags);
                continue;
            }

            foreach ($filters as $filter_key => $filter)
            {
                if (!is_array($filter)) continue;

                $key = sanitize_key((string) ($filter['key'] ?? $filter_key));
                $type = sanitize_key((string) ($filter['type'] ?? ''));
                $method = sanitize_key((string) ($filter['method'] ?? ''));

                if (($type === 'address' || in_array($method, ['radius', 'radius-dropdown'], true)) && (int) ($filter['autocomplete_dropdown'] ?? 1) === 1) $form_flags['autocomplete'] = true;
                if ($method === 'ai-search') $form_flags['ai_search'] = true;
                if ((int) ($filter['radius_locate'] ?? 0) === 1) $form_flags['locate'] = true;
            }
        }
    }
}
