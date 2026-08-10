<?php

class LSD_AI_Visibility_Discovery extends LSD_Base
{
    protected LSD_AI_Visibility $owner;
    protected LSD_AI_Visibility_Settings $settings;
    protected LSD_AI_Visibility_Public_API $public_api;

    /**
     * Store discovery service dependencies.
     * @param LSD_AI_Visibility $owner
     * @param LSD_AI_Visibility_Settings $settings
     * @param LSD_AI_Visibility_Public_API $public_api
     * @return void
     */
    public function __construct(LSD_AI_Visibility $owner, LSD_AI_Visibility_Settings $settings, LSD_AI_Visibility_Public_API $public_api)
    {
        // Dependencies
        $this->owner = $owner;
        $this->settings = $settings;
        $this->public_api = $public_api;
    }

    /**
     * Register discovery hooks.
     * @return void
     */
    public function init(): void
    {
        // Discovery Hooks
        add_action('parse_request', [$this, 'maybe_render_llms_txt'], 0);
        add_filter('robots_txt', [$this, 'filter_robots_txt'], 20, 2);
        add_action('wp_head', [$this, 'render_discovery_links'], 6);
    }

    /**
     * Render dynamic llms.txt when Listdom owns it.
     * @param WP $wp
     * @return void
     */
    public function maybe_render_llms_txt(WP $wp): void
    {
        // LLMS Path
        if (trim((string) $wp->request, '/') !== 'llms.txt') return;

        // Let WordPress or another plugin handle the route when
        // LLMS Availability
        if (!$this->should_serve_llms_txt()) return;
        if ($this->route_claimed_by_content('llms.txt')) return;

        $content = trim($this->llms_content());

        // Route Claim
        if ($content === '') return;

        status_header(200);
        nocache_headers();

        if (!headers_sent()) header('Content-Type: text/plain; charset=' . get_option('blog_charset'));

        echo $content;
        exit;
    }

    /**
     * Build the dynamic llms.txt body.
     * @return string
     */
    public function llms_content(): string
    {
        // Feed URLs
        $urls = $this->string_urls($this->public_api->feed_urls(['scope' => 'llms']));

        if (!count($urls)) return '';

        $lines = [
            __('Website AI Information', 'listdom'),
            __('This website contains a directory powered by Listdom.', 'listdom'),
            '',
            __('Machine-readable directory data:', 'listdom'),
            '',
        ];

        foreach ($urls as $key => $url)
        {
            $lines[] = $this->feed_url_label($key) . ':';
            $lines[] = $url;
            $lines[] = '';
        }

        $lines[] = __('Use this data to understand public listings, categories, and locations.', 'listdom');

        $content = implode("\n", $lines);

        return (string) apply_filters('lsd_ai_llms_content', $content, $urls, $this->owner);
    }

    /**
     * Add Listdom AI hints to robots.txt.
     * @param string $output
     * @param bool $public
     * @return string
     */
    public function filter_robots_txt(string $output, bool $public): string
    {
        // Robots Availability
        if (!$public) return $output;
        if (!$this->should_append_robots_txt()) return $output;

        $urls = $this->string_urls($this->public_api->feed_urls(['scope' => 'robots']));
        if (!count($urls)) return $output;

        $lines = [];

        $lines[] = '# Listdom AI Data:';
        foreach ($urls as $url) $lines[] = $url;

        $lines = apply_filters('lsd_ai_robots_entries', $lines, $urls, $this->owner);
        if (!is_array($lines) || !count($lines)) return $output;

        $normalized = [];
        foreach ($lines as $line)
        {
            if (!is_scalar($line)) continue;

            $line = trim((string) $line);
            if ($line === '') continue;

            if (preg_match('/^https?:\/\//i', $line)) $line = '# ' . $line;
            else if ($line[0] !== '#' && !preg_match('/^[A-Za-z][A-Za-z0-9_-]*\s*:/', $line)) $line = '# ' . $line;

            $normalized[] = $line;
        }

        if (!count($normalized)) return $output;

        $output = rtrim($output);
        if ($output !== '') $output .= "\n\n";

        return $output . implode("\n", $normalized) . "\n";
    }

    /**
     * Output HTML discovery link tags.
     * @return void
     */
    public function render_discovery_links(): void
    {
        // Link Availability
        if (!$this->should_render_discovery_links()) return;

        $context = $this->current_context();
        if (!count($context)) return;

        $links = $this->discovery_links($context);
        if (!count($links)) return;

        foreach ($links as $link)
        {
            if (!is_array($link)) continue;

            $rel = isset($link['rel']) ? trim((string) $link['rel']) : 'alternate';
            $href = isset($link['href']) ? esc_url((string) $link['href']) : '';
            if ($href === '') continue;

            $type = isset($link['type']) ? trim((string) $link['type']) : 'application/json';
            $title = isset($link['title']) ? trim((string) $link['title']) : '';

            echo "\n" . '<link rel="' . esc_attr($rel) . '" type="' . esc_attr($type) . '" href="' . $href . '"';
            if ($title !== '') echo ' title="' . esc_attr($title) . '"';
            echo '>';
        }

        echo "\n";
    }

    /**
     * Build discovery links for the current context.
     * @param array $context
     * @return array
     */
    protected function discovery_links(array $context): array
    {
        // Context URLs
        $url_sets = [];

        if (isset($context['shortcodes']) && is_array($context['shortcodes']) && count($context['shortcodes']))
        {
            foreach ($context['shortcodes'] as $shortcode_context)
            {
                if (!is_array($shortcode_context)) continue;

                $url_sets[] = $this->string_urls($this->public_api->feed_urls($shortcode_context));
            }
        }
        else
        {
            $url_sets[] = $this->string_urls($this->public_api->feed_urls($context));
        }

        $links = [];
        $hrefs = [];

        foreach ($url_sets as $urls)
        {
            foreach ($this->links_for_urls($urls) as $link)
            {
                if (!is_array($link)) continue;

                $href = isset($link['href']) ? trim((string) $link['href']) : '';
                if ($href === '' || isset($hrefs[$href])) continue;

                $hrefs[$href] = true;
                $links[] = $link;
            }
        }

        if ($this->llms_txt_available())
        {
            $links[] = [
                'rel' => 'alternate',
                'type' => 'text/plain',
                'title' => esc_html__('Listdom llms.txt', 'listdom'),
                'href' => home_url('/llms.txt'),
            ];
        }

        $links = apply_filters('lsd_ai_discovery_links', $links, $context, $this->owner);

        return is_array($links) ? $links : [];
    }

    /**
     * Convert feed URLs into link tag data.
     * @param array $urls
     * @return array
     */
    protected function links_for_urls(array $urls): array
    {
        // Link Items
        $links = [];

        if (isset($urls['listing']))
        {
            $links[] = [
                'rel' => 'alternate',
                'type' => 'application/json',
                'title' => esc_html__('Listdom Public Listing', 'listdom'),
                'href' => $urls['listing'],
            ];
        }

        if (isset($urls['category_listings']))
        {
            $links[] = [
                'rel' => 'alternate',
                'type' => 'application/json',
                'title' => esc_html__('Listdom Category Listings Feed', 'listdom'),
                'href' => $urls['category_listings'],
            ];
        }

        if (isset($urls['location_listings']))
        {
            $links[] = [
                'rel' => 'alternate',
                'type' => 'application/json',
                'title' => esc_html__('Listdom Location Listings Feed', 'listdom'),
                'href' => $urls['location_listings'],
            ];
        }

        foreach (['tag_listings', 'feature_listings', 'label_listings'] as $key)
        {
            if (!isset($urls[$key])) continue;

            $links[] = [
                'rel' => 'alternate',
                'type' => 'application/json',
                'title' => sprintf(
                    /* translators: %s: feed label. */
                    esc_html__('%s Feed', 'listdom'),
                    $this->feed_url_label($key)
                ),
                'href' => $urls[$key],
            ];
        }

        foreach (['listings', 'categories', 'locations'] as $key)
        {
            if (!isset($urls[$key])) continue;

            $links[] = [
                'rel' => 'alternate',
                'type' => 'application/json',
                'title' => sprintf(
                    /* translators: %s: feed label. */
                    esc_html__('%s Feed', 'listdom'),
                    $this->feed_url_label($key)
                ),
                'href' => $urls[$key],
            ];
        }

        return $links;
    }

    /**
     * Detect the current public discovery context.
     * @return array
     */
    protected function current_context(): array
    {
        // Listing Context
        if (is_singular(LSD_Base::PTYPE_LISTING))
        {
            $listing_id = get_queried_object_id();
            if ($listing_id > 0 && $this->owner->payload_service()->is_public_listing(get_post($listing_id)))
            {
                return [
                    'scope' => 'html',
                    'page' => 'listing',
                    'listing_id' => $listing_id,
                ];
            }
        }

        $queried = get_queried_object();
        if ($queried instanceof WP_Term && in_array($queried->taxonomy, [
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_LOCATION,
            LSD_Base::TAX_TAG,
            LSD_Base::TAX_FEATURE,
            LSD_Base::TAX_LABEL,
        ], true))
        {
            if ($this->owner->schema_service()->shortcode_collection_page_active())
            {
                $shortcodes = $this->archive_shortcode_contexts($queried);
                if (count($shortcodes)) return ['scope' => 'html', 'page' => 'shortcode', 'shortcodes' => $shortcodes,];
            }

            return [
                'scope' => 'html',
                'page' => 'taxonomy',
                'term' => $queried,
                'taxonomy' => $queried->taxonomy,
            ];
        }

        if (is_post_type_archive(LSD_Base::PTYPE_LISTING)) return ['scope' => 'html', 'page' => 'archive',];

        if ($this->is_listing_search_results_page())
        {
            return [
                'scope' => 'html',
                'page' => 'search',
                'search' => sanitize_text_field((string) get_search_query(false)),
            ];
        }

        $shortcodes = $this->shortcode_contexts();
        if (count($shortcodes)) return ['scope' => 'html', 'page' => 'shortcode', 'shortcodes' => $shortcodes,];

        return [];
    }

    /**
     * Check if Listdom should serve llms.txt.
     * @return bool
     */
    protected function should_serve_llms_txt(): bool
    {
        // LLMS Status
        return $this->settings->llms_txt_enabled() && $this->discovery_enabled();
    }

    /**
     * Check if llms.txt has content to link.
     * @return bool
     */
    protected function llms_txt_available(): bool
    {
        // Route Status
        if (!$this->should_serve_llms_txt()) return false;
        if ($this->route_claimed_by_content('llms.txt')) return false;

        return trim($this->llms_content()) !== '';
    }

    /**
     * Check if robots.txt hints should be appended.
     * @return bool
     */
    protected function should_append_robots_txt(): bool
    {
        // Robots Status
        return $this->settings->robots_txt_enabled() && $this->discovery_enabled();
    }

    /**
     * Check if HTML discovery links should render.
     * @return bool
     */
    protected function should_render_discovery_links(): bool
    {
        // Request Context
        if (is_admin() || wp_doing_ajax() || is_feed()) return false;
        if (function_exists('wp_is_json_request') && wp_is_json_request()) return false;

        return $this->settings->html_links_enabled() && $this->discovery_enabled();
    }

    /**
     * Check if public discovery is enabled.
     * @return bool
     */
    protected function discovery_enabled(): bool
    {
        // Discovery Status
        return $this->settings->public_feed_available();
    }

    /**
     * Keep only valid string URLs.
     * @param array $urls
     * @return array
     */
    protected function string_urls(array $urls): array
    {
        // String URLs
        $normalized = [];

        foreach ($urls as $key => $url)
        {
            if (!is_string($url)) continue;

            $url = esc_url_raw($url);
            if ($url === '') continue;

            $normalized[(string) $key] = $url;
        }

        return $normalized;
    }

    /**
     * Check if content already owns a route path.
     * @param string $path
     * @return bool
     */
    protected function route_claimed_by_content(string $path): bool
    {
        // Route Post
        $post_types = get_post_types(['public' => true], 'names');
        $post = get_page_by_path($path, OBJECT, array_values($post_types));

        if (!($post instanceof WP_Post)) return false;

        // Public Route
        return $post->post_status === 'publish' && $post->post_password === '';
    }

    /**
     * Check if the current post contains Listdom shortcodes.
     * @return bool
     */
    protected function current_post_has_listdom_shortcode(): bool
    {
        // Shortcode Search
        return count($this->shortcode_contexts()) > 0;
    }

    /**
     * Check if the search page only contains listings.
     * @return bool
     */
    protected function is_listing_search_results_page(): bool
    {
        // Search Query
        if (!is_search()) return false;
        if (trim((string) get_search_query(false)) === '') return false;

        global $wp_query;

        if (!($wp_query instanceof WP_Query) || !count($wp_query->posts)) return false;

        foreach ($wp_query->posts as $post)
        {
            if (!$post instanceof WP_Post) continue;
            if ($post->post_type !== LSD_Base::PTYPE_LISTING) return false;
        }

        return true;
    }

    /**
     * Get Elementor builder elements for a post.
     * @param int $post_id
     * @return array
     */
    protected function elementor_builder_elements(int $post_id): array
    {
        // Raw Data
        $raw = get_post_meta($post_id, '_elementor_data', true);
        if (is_array($raw)) $raw = wp_json_encode($raw);
        if (!is_string($raw) || trim($raw) === '') return [];

        // Decoded Elements
        $elements = json_decode($raw, true);

        return is_array($elements) ? $elements : [];
    }

    /**
     * Get Bricks builder elements for a post.
     * @param int $post_id
     * @return array
     */
    protected function bricks_builder_elements(int $post_id): array
    {
        // Bricks Data
        $elements = get_post_meta($post_id, '_bricks_page_content_2', true);
        if (!is_array($elements) || !count($elements)) $elements = get_post_meta($post_id, '_bricks_page_content', true);

        return is_array($elements) ? $elements : [];
    }

    /**
     * Add a shortcode context once.
     * @param array $contexts
     * @param array $seen
     * @param array $context
     * @return void
     */
    protected function push_shortcode_context(array &$contexts, array &$seen, array $context): void
    {
        // Context Key
        $key = wp_json_encode($context);
        if (!is_string($key) || isset($seen[$key])) return;

        // Unique Context
        $seen[$key] = true;
        $contexts[] = $context;
    }

    /**
     * Build a shortcode context from a builder widget ID.
     * @param int $shortcode_id
     * @return array
     */
    protected function builder_shortcode_context(int $shortcode_id): array
    {
        // Shortcode Context
        return $this->listdom_shortcode_context($shortcode_id > 0 ? ['id' => $shortcode_id] : []);
    }

    /**
     * Extract Listdom shortcode contexts from the current post.
     * @return array
     */
    protected function shortcode_contexts(): array
    {
        // Source Posts
        $post = get_post();
        if (!($post instanceof WP_Post)) return [];
        if (is_singular(LSD_Base::PTYPE_LISTING)) return [];

        $sources = [];

        // Post Content
        $content = (string) $post->post_content;
        if (trim($content) !== '') $sources[] = $content;

        // Elementor Data
        $elementor_data = $this->elementor_builder_elements($post->ID);
        if (count($elementor_data)) $sources[] = wp_json_encode($elementor_data);

        // Bricks Data
        $bricks_data = $this->bricks_builder_elements($post->ID);
        if (count($bricks_data)) $sources[] = wp_json_encode($bricks_data);

        // Parsed Contexts
        $contexts = [];
        $seen = [];

        foreach ($sources as $source)
        {
            foreach ($this->extract_listdom_shortcode_contexts($source) as $context)
            {
                $this->push_shortcode_context($contexts, $seen, $context);
            }
        }

        // Elementor Widgets
        foreach ($this->elementor_shortcode_contexts($post->ID) as $context)
        {
            $this->push_shortcode_context($contexts, $seen, $context);
        }

        // Bricks Widgets
        foreach ($this->bricks_shortcode_contexts($post->ID) as $context)
        {
            $this->push_shortcode_context($contexts, $seen, $context);
        }

        return $contexts;
    }

    /**
     * Extract Listdom shortcode contexts from Elementor widgets.
     * @param int $post_id
     * @return array
     */
    protected function elementor_shortcode_contexts(int $post_id): array
    {
        // Elementor Data
        $elements = $this->elementor_builder_elements($post_id);
        if (!count($elements)) return [];

        $contexts = [];
        $this->collect_elementor_shortcode_contexts($elements, $contexts);

        return $contexts;
    }

    /**
     * Collect Listdom shortcode contexts from Elementor widgets.
     * @param array $elements
     * @param array $contexts
     * @return void
     */
    protected function collect_elementor_shortcode_contexts(array $elements, array &$contexts): void
    {
        // Elements
        foreach ($elements as $element)
        {
            if (!is_array($element)) continue;

            // Widget Match
            if (($element['widgetType'] ?? '') === 'lsd-listing-shortcode')
            {
                $settings = isset($element['settings']) && is_array($element['settings']) ? $element['settings'] : [];
                $shortcode_id = absint($settings['shortcode'] ?? 0);
                $context = $this->builder_shortcode_context($shortcode_id);

                if (count($context)) $contexts[] = $context;
            }

            // Children
            if (isset($element['elements']) && is_array($element['elements']))
            {
                $this->collect_elementor_shortcode_contexts($element['elements'], $contexts);
            }
        }
    }

    /**
     * Extract Listdom shortcode contexts from Bricks widgets.
     * @param int $post_id
     * @return array
     */
    protected function bricks_shortcode_contexts(int $post_id): array
    {
        // Bricks Data
        $elements = $this->bricks_builder_elements($post_id);
        if (!count($elements)) return [];

        $contexts = [];
        $this->collect_bricks_shortcode_contexts($elements, $contexts);

        return $contexts;
    }

    /**
     * Collect Listdom shortcode contexts from Bricks widgets.
     * @param array $elements
     * @param array $contexts
     * @return void
     */
    protected function collect_bricks_shortcode_contexts(array $elements, array &$contexts): void
    {
        // Elements
        foreach ($elements as $element)
        {
            if (!is_array($element)) continue;

            // Widget Match
            if (($element['name'] ?? '') === 'lsd-listing-shortcode')
            {
                $settings = isset($element['settings']) && is_array($element['settings']) ? $element['settings'] : [];
                $shortcode_id = absint($settings['shortcode'] ?? 0);
                $context = $this->builder_shortcode_context($shortcode_id);

                if (count($context)) $contexts[] = $context;
            }

            // Children
            if (isset($element['children']) && is_array($element['children']))
            {
                $this->collect_bricks_shortcode_contexts($element['children'], $contexts);
            }
        }
    }

    /**
     * Extract Listdom shortcode contexts from content.
     * @param string $source
     * @return array
     */
    protected function extract_listdom_shortcode_contexts(string $source): array
    {
        // Parsed Shortcodes
        if (trim($source) === '' || !function_exists('get_shortcode_regex')) return [];

        $pattern = get_shortcode_regex(['listdom']);
        if (!preg_match_all('/' . $pattern . '/s', $source, $matches, PREG_SET_ORDER)) return [];

        $contexts = [];

        foreach ($matches as $match)
        {
            if (!is_array($match) || ($match[2] ?? '') !== 'listdom') continue;
            if (($match[1] ?? '') === '[' || ($match[6] ?? '') === ']') continue;

            $atts = shortcode_parse_atts($match[3] ?? '');
            $context = $this->listdom_shortcode_context(is_array($atts) ? $atts : []);

            if (count($context)) $contexts[] = $context;
        }

        return $contexts;
    }

    /**
     * Build shortcode contexts for taxonomy archives.
     * @param WP_Term $term
     * @return array
     */
    protected function archive_shortcode_contexts(WP_Term $term): array
    {
        // Archive Shortcode
        $shortcode_id = $this->owner->archive_shortcode_id($term);
        if ($shortcode_id <= 0) return [];

        $context = $this->listdom_shortcode_context(['id' => $shortcode_id]);
        if (!count($context)) return [];

        $query_key = $this->archive_taxonomy_query_key($term->taxonomy);
        if ($query_key !== '')
        {
            $filters = isset($context['filters']) && is_array($context['filters']) ? $context['filters'] : [];
            $existing = isset($filters[$query_key]) ? (array) $filters[$query_key] : [];
            $existing[] = $term->slug;
            $filters[$query_key] = array_values(array_unique(array_filter(array_map('strval', $existing))));
            $context['filters'] = $filters;

            if ($this->has_unrepresentable_taxonomy_matching($filters, false)) $context['representable'] = false;
        }

        return [$context];
    }

    /**
     * Build discovery context for one shortcode.
     * @param array $atts
     * @return array
     */
    protected function listdom_shortcode_context(array $atts): array
    {
        // Shortcode Attributes
        $shortcode_id = isset($atts['id']) ? absint($atts['id']) : 0;
        $shortcode = new LSD_Shortcodes_Listdom();
        $resolved = apply_filters('lsd_shortcode_atts', $shortcode->parse($shortcode_id, $atts));
        if (!is_array($resolved)) $resolved = [];

        $context = [
            'scope' => 'html',
            'page' => 'shortcode',
        ];

        $filters = $this->shortcode_listing_filters($resolved, $shortcode_id);
        if (count($filters)) $context['filters'] = $filters;

        $search = $this->shortcode_search_value($resolved, $shortcode_id);
        if ($search !== '') $context['search'] = $search;

        $context['representable'] = $this->shortcode_context_is_representable($resolved, $shortcode_id);

        return $context;
    }

    /**
     * Extract feed-representable shortcode filters.
     * @param array $atts
     * @param int $shortcode_id
     * @return array
     */
    protected function shortcode_listing_filters(array $atts, int $shortcode_id): array
    {
        // Stored Filters
        $filters = [];
        $shortcode_filters = isset($atts['lsd_filter']) && is_array($atts['lsd_filter']) ? $atts['lsd_filter'] : [];
        $shortcode_excludes = isset($atts['lsd_exclude']) && is_array($atts['lsd_exclude']) ? $atts['lsd_exclude'] : [];

        $taxonomy_map = [
            'category' => LSD_Base::TAX_CATEGORY,
            'location' => LSD_Base::TAX_LOCATION,
            'tag' => LSD_Base::TAX_TAG,
            'feature' => LSD_Base::TAX_FEATURE,
            'label' => LSD_Base::TAX_LABEL,
        ];

        foreach ($taxonomy_map as $key => $taxonomy)
        {
            $value = $shortcode_filters[$taxonomy] ?? null;
            if ($value === null || $value === '') continue;

            $normalized = $this->taxonomy_filter_values($value);
            if (count($normalized)) $filters[$key] = array_values(array_unique($normalized));
        }

        foreach (['authors', 'include', 'exclude'] as $key)
        {
            if (!isset($shortcode_filters[$key])) continue;

            $values = is_array($shortcode_filters[$key]) ? $shortcode_filters[$key] : [$shortcode_filters[$key]];
            $normalized = [];

            foreach ($values as $item)
            {
                if (!is_scalar($item)) continue;

                $item = absint($item);
                if ($item > 0) $normalized[] = $item;
            }

            if (count($normalized)) $filters[$key] = array_values(array_unique($normalized));
        }

        if (isset($shortcode_excludes['exclude']))
        {
            $values = is_array($shortcode_excludes['exclude']) ? $shortcode_excludes['exclude'] : [$shortcode_excludes['exclude']];
            $normalized = [];

            foreach ($values as $item)
            {
                if (!is_scalar($item)) continue;

                $item = absint($item);
                if ($item > 0) $normalized[] = $item;
            }

            if (count($normalized)) $filters['exclude'] = array_values(array_unique($normalized));
        }

        if ($this->request_targets_shortcode($shortcode_id))
        {
            $request_filters = $this->request_shortcode_filters();
            foreach ($this->mergeable_shortcode_filter_keys() as $key)
            {
                if (!isset($request_filters[$key])) continue;

                $filters[$key] = $request_filters[$key];
            }
        }

        return $filters;
    }

    /**
     * Resolve shortcode search text for discovery.
     * @param array $atts
     * @param int $shortcode_id
     * @return string
     */
    protected function shortcode_search_value(array $atts, int $shortcode_id): string
    {
        // Request Search
        $search = '';
        $sf = $this->request_sf();

        if ($this->request_targets_shortcode($shortcode_id) && isset($sf['s']) && is_scalar($sf['s']))
        {
            $search = sanitize_text_field((string) $sf['s']);
        }

        if ($search !== '') return $search;

        $filters = isset($atts['lsd_filter']) && is_array($atts['lsd_filter']) ? $atts['lsd_filter'] : [];
        if (isset($filters['s']) && is_scalar($filters['s'])) return sanitize_text_field((string) $filters['s']);

        return '';
    }

    /**
     * Check if a shortcode can map to a public feed URL.
     * @param array $atts
     * @param int $shortcode_id
     * @return bool
     */
    protected function shortcode_context_is_representable(array $atts, int $shortcode_id): bool
    {
        // Representable Filters
        $filters = isset($atts['lsd_filter']) && is_array($atts['lsd_filter']) ? $atts['lsd_filter'] : [];
        $exclude_filters = isset($atts['lsd_exclude']) && is_array($atts['lsd_exclude']) ? $atts['lsd_exclude'] : [];
        $unsupported = ['attributes', 'circle', 'inquiry', 'acf_fields'];
        $request_targets_shortcode = $this->request_targets_shortcode($shortcode_id);

        if (!$this->shortcode_default_sort_is_representable($atts)) return false;
        if ($request_targets_shortcode && !$this->request_sort_is_representable()) return false;
        if ($this->has_unrepresentable_taxonomy_matching($filters, true)) return false;
        if ($this->has_unrepresentable_stored_filters($filters)) return false;

        foreach ($unsupported as $key)
        {
            if (!empty($filters[$key])) return false;
        }

        foreach ([LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION, LSD_Base::TAX_TAG, LSD_Base::TAX_FEATURE, LSD_Base::TAX_LABEL, 'authors'] as $key)
        {
            if (!empty($exclude_filters[$key])) return false;
        }

        if (isset($filters['status']))
        {
            $statuses = is_array($filters['status']) ? $filters['status'] : [$filters['status']];

            foreach ($statuses as $status)
            {
                if (!is_scalar($status) || sanitize_key((string) $status) !== 'publish') return false;
            }
        }

        if (!$request_targets_shortcode) return true;

        if ($this->request_uses_aps_text_search()) return false;

        $request_filters = $this->request_shortcode_filters();
        if (!empty($request_filters['spatial'])) return false;

        if ($this->has_unrepresentable_taxonomy_matching($request_filters, false)) return false;

        foreach (['attributes', 'circle', 'inquiry', 'acf_fields'] as $key)
        {
            if (!empty($request_filters[$key])) return false;
        }

        return true;
    }

    /**
     * Check if APS changes request text search semantics.
     * @return bool
     */
    protected function request_uses_aps_text_search(): bool
    {
        // APS text search is not representable by the public feed.
        if (!class_exists('LSDPACAPS\\Base')) return false;

        $sf = $this->request_sf();
        if (!isset($sf['s']) || !is_scalar($sf['s'])) return false;

        return trim((string) $sf['s']) !== '';
    }

    /**
     * Detect stored filters unsupported by public feeds.
     * @param array $filters
     * @return bool
     */
    protected function has_unrepresentable_stored_filters(array $filters): bool
    {
        // Stored Filter Keys
        $supported = [
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_LOCATION,
            LSD_Base::TAX_TAG,
            LSD_Base::TAX_FEATURE,
            LSD_Base::TAX_LABEL,
            'authors',
            'include',
            'exclude',
            's',
            'status',
        ];

        foreach ($filters as $key => $value)
        {
            if (in_array($key, $supported, true)) continue;
            if ($this->stored_filter_value_present($value)) return true;
        }

        return false;
    }

    /**
     * Check whether a stored filter has a value.
     * @param mixed $value
     * @return bool
     */
    protected function stored_filter_value_present($value): bool
    {
        // Nested Values
        if (is_array($value))
        {
            foreach ($value as $item)
            {
                if ($this->stored_filter_value_present($item)) return true;
            }

            return false;
        }

        if (!is_scalar($value)) return false;

        return trim((string) $value) !== '';
    }

    /**
     * Detect taxonomy AND matching that feeds cannot express.
     * @param array $filters
     * @param bool $stored
     * @return bool
     */
    protected function has_unrepresentable_taxonomy_matching(array $filters, bool $stored): bool
    {
        // Taxonomy Operators
        foreach ($this->taxonomy_filter_map() as $query_key => $taxonomy)
        {
            $key = $stored ? $taxonomy : $query_key;
            if (!isset($filters[$key])) continue;
            if (count($this->taxonomy_filter_values($filters[$key])) < 2) continue;

            $operator = strtoupper((string) apply_filters('lsd_search_' . $taxonomy . '_operator', 'IN'));
            if ($operator === 'AND') return true;
        }

        return false;
    }

    /**
     * Normalize taxonomy filter values.
     * @param mixed $value
     * @return array
     */
    protected function taxonomy_filter_values($value): array
    {
        // Filter Values
        $values = is_array($value) ? $value : [$value];
        $normalized = [];

        foreach ($values as $item)
        {
            if (!is_scalar($item)) continue;

            foreach (explode(',', (string) $item) as $part)
            {
                $part = trim(sanitize_text_field($part));
                if ($part === '') continue;

                $normalized[] = $part;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Check whether shortcode default sorting is representable.
     * @param array $atts
     * @return bool
     */
    protected function shortcode_default_sort_is_representable(array $atts): bool
    {
        // Default Sort
        $sorts = isset($atts['lsd_sorts']) && is_array($atts['lsd_sorts']) ? $atts['lsd_sorts'] : LSD_Options::defaults('sorts');
        $default = isset($sorts['default']) && is_array($sorts['default']) ? $sorts['default'] : [];

        return $this->representable_order($default);
    }

    /**
     * Check whether request sorting is representable.
     * @return bool
     */
    protected function request_sort_is_representable(): bool
    {
        // Request Sort
        if (!isset($_GET['orderby']) && !isset($_GET['order'])) return true;
        if (isset($_GET['orderby']) && !is_scalar($_GET['orderby'])) return false;
        if (isset($_GET['order']) && !is_scalar($_GET['order'])) return false;

        return $this->representable_order($_GET);
    }

    /**
     * Check whether the request targets a shortcode.
     * @param int $shortcode_id
     * @return bool
     */
    protected function request_targets_shortcode(int $shortcode_id): bool
    {
        // Shortcode Target
        if (!isset($_GET['sf-shortcode'])) return true;
        if (!is_scalar($_GET['sf-shortcode'])) return false;

        $target = trim((string) wp_unslash($_GET['sf-shortcode']));
        if (!preg_match('/^\d+$/', $target)) return false;

        $target = (int) $target;
        if ($target <= 0) return false;

        return $shortcode_id > 0 && $shortcode_id === $target;
    }

    /**
     * Build search form request values.
     * @return array
     */
    protected function request_sf(): array
    {
        // Search Values
        $sf = [];

        if (isset($_GET['sf']) && is_array($_GET['sf']))
        {
            $nested = wp_unslash($_GET['sf']);
            if (is_array($nested)) $sf = $nested;
        }

        foreach (wp_unslash($_GET) as $key => $value)
        {
            if (!is_string($key) || strpos($key, 'sf-') !== 0) continue;

            $parameter = substr($key, 3);
            if ($parameter === '') continue;

            if (strpos($parameter, 'att-') === 0)
            {
                $attribute = substr($parameter, 4);
                if ($attribute !== '') $sf['attributes'][$attribute] = $value;
                continue;
            }

            if (strpos($parameter, 'circle-') === 0)
            {
                $circle = substr($parameter, 7);
                if ($circle !== '') $sf['circle'][$circle] = $value;
                continue;
            }

            if (strpos($parameter, 'acf-') === 0)
            {
                $acf = substr($parameter, 4);
                if ($acf !== '') $sf['acf_values'][$acf] = $value;
                continue;
            }

            if ($parameter === 'adults-eq')
            {
                $sf['adults'] = $value;
                continue;
            }

            if ($parameter === 'children-eq')
            {
                $sf['children'] = $value;
                continue;
            }

            if (in_array($parameter, [
                's',
                'ai',
                'period',
                'shape',
                'min_latitude',
                'max_latitude',
                'min_longitude',
                'max_longitude',
                'rect_min_latitude',
                'rect_max_latitude',
                'rect_min_longitude',
                'rect_max_longitude',
                'circle_latitude',
                'circle_longitude',
                'circle_radius',
            ], true))
            {
                if (is_scalar($value)) $sf[$parameter] = $value;
                continue;
            }

            if ($parameter === 'polygon' || in_array($parameter, LSD_Base::taxonomies(), true))
            {
                $sf[$parameter] = $value;
            }
        }

        foreach (['s', 'ai', 'period', 'shape', 'adults', 'children'] as $scalar_key)
        {
            if (isset($sf[$scalar_key]) && !is_scalar($sf[$scalar_key])) unset($sf[$scalar_key]);
        }

        $sf = LSD_Sanitize::search($sf);

        if (isset($sf['acf_values']) && is_array($sf['acf_values']))
        {
            if (!isset($sf['acf_fields']) || !is_array($sf['acf_fields'])) $sf['acf_fields'] = [];
            $sf['acf_fields']['acf_values'] = $sf['acf_values'];
        }

        return $sf;
    }

    /**
     * Build shortcode filters from the request.
     * @return array
     */
    protected function request_shortcode_filters(): array
    {
        // Search Values
        $sf = $this->request_sf();
        if (!count($sf)) return [];

        $filters = [];

        foreach ([
            'category' => LSD_Base::TAX_CATEGORY,
            'location' => LSD_Base::TAX_LOCATION,
            'feature' => LSD_Base::TAX_FEATURE,
            'label' => LSD_Base::TAX_LABEL,
        ] as $key => $taxonomy)
        {
            $value = $sf[$taxonomy] ?? null;
            if ($value === null || $value === '') continue;

            $normalized = $this->taxonomy_filter_values($value);
            if (count($normalized)) $filters[$key] = array_values(array_unique($normalized));
        }

        if (isset($sf[LSD_Base::TAX_TAG]))
        {
            $tag_value = $sf[LSD_Base::TAX_TAG];

            if (is_array($tag_value))
            {
                $normalized = $this->taxonomy_filter_values($tag_value);
                if (count($normalized)) $filters['tag'] = array_values(array_unique($normalized));
            }
            else if (is_scalar($tag_value))
            {
                $normalized = $this->taxonomy_filter_values($tag_value);
                if (count($normalized)) $filters['tag'] = array_values(array_unique($normalized));
            }
        }

        foreach (['adults', 'children'] as $key)
        {
            if (!isset($sf[$key]) || !is_scalar($sf[$key])) continue;

            $value = absint($sf[$key]);
            if ($value > 0)
            {
                if (!isset($filters['inquiry'])) $filters['inquiry'] = [];
                $filters['inquiry'][$key] = $value;
            }
        }

        if (isset($sf['period']) && $this->request_value_present($sf['period']))
        {
            $filters['inquiry']['period'] = $sf['period'];
        }

        if (isset($sf['attributes']) && is_array($sf['attributes']) && count($sf['attributes'])) $filters['attributes'] = $sf['attributes'];
        if (isset($sf['circle']) && is_array($sf['circle']) && count($sf['circle'])) $filters['circle'] = $sf['circle'];
        if ($this->request_has_unrepresentable_spatial_filter($sf)) $filters['spatial'] = true;

        foreach ($sf as $key => $value)
        {
            if (strpos((string) $key, 'acf') !== false && $this->request_value_present($value))
            {
                if (!isset($filters['acf_fields'])) $filters['acf_fields'] = [];
                $filters['acf_fields'][$key] = $value;
            }
        }

        return $filters;
    }

    /**
     * Check whether request spatial filters are unrepresentable.
     * @param array $sf
     * @return bool
     */
    protected function request_has_unrepresentable_spatial_filter(array $sf): bool
    {
        // Spatial Shape
        if (isset($sf['shape']) && !is_scalar($sf['shape'])) return true;

        $shape = isset($sf['shape']) && is_scalar($sf['shape']) ? sanitize_key((string) $sf['shape']) : '';

        if ($shape === '' && isset($sf['min_latitude'], $sf['max_latitude'], $sf['min_longitude'], $sf['max_longitude'])) return true;
        if ($shape === 'rectangle' && isset($sf['rect_min_latitude'], $sf['rect_max_latitude'], $sf['rect_min_longitude'], $sf['rect_max_longitude'])) return true;
        if ($shape === 'circle' && isset($sf['circle_latitude'], $sf['circle_longitude'], $sf['circle_radius'])) return true;
        if ($shape === 'polygon' && isset($sf['polygon']) && $this->request_value_present($sf['polygon'])) return true;

        return false;
    }

    /**
     * Check whether a request value is present.
     * @param mixed $value
     * @return bool
     */
    protected function request_value_present($value): bool
    {
        // Value
        if (is_array($value)) return count($value) > 0;
        if (!is_scalar($value)) return false;

        return trim((string) $value) !== '';
    }

    /**
     * Get shortcode filters that can merge into feed URLs.
     * @return array
     */
    protected function mergeable_shortcode_filter_keys(): array
    {
        // Filter Keys
        return ['category', 'location', 'tag', 'feature', 'label'];
    }

    /**
     * Get taxonomy filter map.
     * @return array
     */
    protected function taxonomy_filter_map(): array
    {
        // Taxonomy Filters
        return [
            'category' => LSD_Base::TAX_CATEGORY,
            'location' => LSD_Base::TAX_LOCATION,
            'tag' => LSD_Base::TAX_TAG,
            'feature' => LSD_Base::TAX_FEATURE,
            'label' => LSD_Base::TAX_LABEL,
        ];
    }

    /**
     * Resolve archive taxonomy query key.
     * @param string $taxonomy
     * @return string
     */
    protected function archive_taxonomy_query_key(string $taxonomy): string
    {
        // Taxonomy Key
        if ($taxonomy === LSD_Base::TAX_CATEGORY) return 'category';
        if ($taxonomy === LSD_Base::TAX_LOCATION) return 'location';
        if ($taxonomy === LSD_Base::TAX_TAG) return 'tag';
        if ($taxonomy === LSD_Base::TAX_FEATURE) return 'feature';
        if ($taxonomy === LSD_Base::TAX_LABEL) return 'label';

        return '';
    }

    /**
     * Resolve public feed URL label.
     * @param string $key
     * @return string
     */
    protected function feed_url_label(string $key): string
    {
        // URL Label
        return LSD_AI_Visibility::public_feed_url_label($key);
    }

    /**
     * Check whether sort order can be represented by the public feed.
     * @param array $default
     * @return bool
     */
    protected function representable_order(array $default): bool
    {
        // Sort Order
        $orderby = isset($default['orderby']) && is_scalar($default['orderby']) ? sanitize_key((string) $default['orderby']) : 'post_date';
        $order = isset($default['order']) && is_scalar($default['order']) ? strtoupper(sanitize_text_field((string) $default['order'])) : 'DESC';

        return in_array($orderby, ['post_date', 'date'], true) && $order === 'DESC';
    }
}
