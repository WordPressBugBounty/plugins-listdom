<?php

class LSD_Taxonomies extends LSD_Base
{
    private static $slug_update_cache = [];

    public function init()
    {
        // Listing Attribute
        $attribute = new LSD_Taxonomies_Attribute();
        $attribute->init();

        // Listing Category
        $category = new LSD_Taxonomies_Category();
        $category->init();

        // Listing Location
        $location = new LSD_Taxonomies_Location();
        $location->init();

        // Listing Tag
        $tag = new LSD_Taxonomies_Tag();
        $tag->init();

        // Listing Feature
        $feature = new LSD_Taxonomies_Feature();
        $feature->init();

        // Listing Label
        $label = new LSD_Taxonomies_Label();
        $label->init();

        // Redirect to Archive Page
        add_filter('init', [$this, 'redirect'], 999);

        // Listdom Taxonomy Template
        add_filter('template_include', [$this, 'template'], 99);

        // Listdom Archive Content
        foreach ($this->taxonomies() as $tax) add_action($tax . '_archive_content', [$this, 'content']);

        // Track and broadcast term slug changes
        add_action('edit_terms', [$this, 'term_slug_capture'], 10, 2);
        add_action('edited_term', [$this, 'term_slug_emit'], 10, 3);
    }

    public function redirect()
    {
        foreach ($this->taxonomies() as $tax)
        {
            if (!isset($_GET[$tax])) continue;

            $term = (int) sanitize_text_field($_GET[$tax]);
            if (!$term) continue;

            $url = get_term_link($term, $tax);

            // Redirect to Term Page
            if (is_string($url))
            {
                wp_redirect($url);
                exit;
            }
        }
    }

    public function filter_sortable_columns($columns)
    {
        return $columns;
    }

    public function template($template)
    {
        // We're in an embed post
        if (is_embed()) return $template;

        // Listdom Settings
        $settings = LSD_Options::settings();

        // Listdom Location
        if (is_tax(LSD_Base::TAX_LOCATION) && isset($settings['location_archive']) && trim($settings['location_archive']))
        {
            $file = 'taxonomy-' . LSD_Base::TAX_LOCATION . '.php';
            $template = locate_template($file);

            // Listdom Template
            if ($template === '') $template = lsd_template($file);
        }
        // Listdom Category
        else if (is_tax(LSD_Base::TAX_CATEGORY) && isset($settings['category_archive']) && trim($settings['category_archive']))
        {
            $file = 'taxonomy-' . LSD_Base::TAX_CATEGORY . '.php';
            $template = locate_template($file);

            // Listdom Template
            if ($template === '') $template = lsd_template($file);
        }
        // Listdom Feature
        else if (is_tax(LSD_Base::TAX_FEATURE) && isset($settings['feature_archive']) && trim($settings['feature_archive']))
        {
            $file = 'taxonomy-' . LSD_Base::TAX_FEATURE . '.php';
            $template = locate_template($file);

            // Listdom Template
            if ($template === '') $template = lsd_template($file);
        }
        // Listdom Tag
        else if (is_tax(LSD_Base::TAX_TAG) && isset($settings['tag_archive']) && trim($settings['tag_archive']))
        {
            $file = 'taxonomy-' . LSD_Base::TAX_TAG . '.php';
            $template = locate_template($file);

            // Listdom Template
            if ($template === '') $template = lsd_template($file);
        }
        // Listdom Label
        else if (is_tax(LSD_Base::TAX_LABEL) && isset($settings['label_archive']) && trim($settings['label_archive']))
        {
            $file = 'taxonomy-' . LSD_Base::TAX_LABEL . '.php';
            $template = locate_template($file);

            // Listdom Template
            if ($template === '') $template = lsd_template($file);
        }

        return $template;
    }

    /**
     * @return void
     */
    public function content()
    {
        // Current Query
        $q = get_queried_object();

        // It's not a taxonomy query
        if (!isset($q->taxonomy) || !isset($q->term_id)) return;

        // It's not a Listdom taxonomy
        if (!in_array($q->taxonomy, $this->taxonomies())) return;

        // Listdom Settings
        $settings = LSD_Options::settings();

        $shortcode_id = (int) $settings['label_archive'];
        if ($q->taxonomy === LSD_Base::TAX_LOCATION) $shortcode_id = (int) $settings['location_archive'];
        else if ($q->taxonomy === LSD_Base::TAX_FEATURE) $shortcode_id = (int) $settings['feature_archive'];
        else if ($q->taxonomy === LSD_Base::TAX_CATEGORY) $shortcode_id = (int) $settings['category_archive'];
        else if ($q->taxonomy === LSD_Base::TAX_TAG) $shortcode_id = (int) $settings['tag_archive'];

        $LSD = new LSD_Shortcodes_Listdom();
        echo LSD_Kses::full($LSD->output([
            'id' => $shortcode_id,
        ], [
            'lsd_filter' => [],
        ]));
    }

    public static function id($term, $taxonomy)
    {
        if (is_array($term))
        {
            $ids = [];
            foreach ($term as $t)
            {
                $term = get_term_by('name', sanitize_text_field($t), $taxonomy);
                if ($term && isset($term->term_id)) $ids[] = $term->term_id;
            }

            return $ids;
        }
        else
        {
            $term = get_term_by('name', sanitize_text_field($term), $taxonomy);
            return $term ? $term->term_id : 0;
        }
    }

    public static function name($term, $taxonomy)
    {
        if (is_array($term))
        {
            $names = [];
            foreach ($term as $t)
            {
                $t = (int) $t;
                if (!$t) continue;

                $term = get_term_by('term_id', $t, $taxonomy);
                if ($term && isset($term->name)) $names[] = $term->name;
            }

            return $names;
        }

        $term = (int) $term;

        $term = get_term_by('term_id', $term, $taxonomy);
        return $term ? $term->name : '';
    }

    public static function resolve_term_ids(string $taxonomy, $values): array
    {
        if (!is_array($values)) $values = [$values];

        $ids = [];
        foreach ($values as $value)
        {
            if (is_array($value)) continue;

            $value = trim((string) $value);
            if ($value === '') continue;

            $term = null;
            if (is_numeric($value))
            {
                $term = get_term_by('term_id', (int) $value, $taxonomy);
            }

            if (!$term || is_wp_error($term))
            {
                $term = get_term_by('slug', $value, $taxonomy);
            }

            if (!$term || is_wp_error($term))
            {
                $term = get_term_by('name', $value, $taxonomy);
            }

            if ($term && !is_wp_error($term) && isset($term->term_id)) $ids[] = $term->term_id;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    public static function ids_to_slugs(string $taxonomy, $values): array
    {
        if (!is_array($values)) $values = [$values];

        $slugs = [];
        foreach ($values as $value)
        {
            if (is_array($value)) continue;

            $value = trim((string) $value);
            if ($value === '') continue;

            if (is_numeric($value))
            {
                $term = get_term_by('term_id', (int) $value, $taxonomy);
            }
            else
            {
                $term = get_term_by('slug', $value, $taxonomy);
                if (!$term || is_wp_error($term))
                {
                    $term = get_term_by('name', $value, $taxonomy);
                }
            }

            if ($term && !is_wp_error($term) && isset($term->slug))
            {
                $slugs[] = $term->slug;
                continue;
            }

            $slugs[] = $value;
        }

        $slugs = array_map('sanitize_title', $slugs);
        $slugs = array_filter($slugs, 'strlen');

        return array_values(array_unique($slugs));
    }

    public static function to_slug(string $taxonomy, $value): string
    {
        if (is_array($value) || is_object($value)) return '';

        $candidate = sanitize_title((string) $value);
        if ($candidate !== '')
        {
            $term = get_term_by('slug', $candidate, $taxonomy);
            if ($term && !is_wp_error($term) && isset($term->slug)) return sanitize_title($term->slug);
        }

        $term_id = (int) $value;
        if ($term_id <= 0) return '';

        $term = get_term($term_id, $taxonomy);
        if ($term && !is_wp_error($term) && isset($term->slug)) return sanitize_title($term->slug);

        return '';
    }

    public static function parents($term, $current = [])
    {
        if ($term->parent)
        {
            $current[] = $term->parent;
            $current = LSD_Taxonomies::parents(get_term($term->parent), $current);
        }

        return $current;
    }

    public static function icon($term_id, $class = 'fa-fw')
    {
        $disabled = get_term_meta($term_id, 'lsd_disabled_icon', true);
        if ($disabled) return apply_filters('lsd_term_icon', '', $term_id, $class);

        $icon = get_term_meta($term_id, 'lsd_icon', true);
        $HTML = trim($icon) ? '<i class="lsd-fe-icon ' . esc_attr($icon) . ' ' . esc_attr($class) . '" aria-hidden="true"></i>' : '';

        return apply_filters('lsd_term_icon', $HTML, $term_id, $class);
    }

    public static function sync_category_slug(string $old_slug, string $new_slug, string $taxonomy): void
    {
        $old_slug = sanitize_title($old_slug);
        $new_slug = sanitize_title($new_slug);
        if ($old_slug === '') return;

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'fields' => 'ids',
        ]);

        if (!is_array($terms)) return;

        foreach ($terms as $term_id)
        {
            $categories = get_term_meta((int) $term_id, 'lsd_categories', true);
            if (!is_array($categories)) continue;

            if (!array_key_exists($old_slug, $categories)) continue;

            unset($categories[$old_slug]);
            if ($new_slug !== '' && !array_key_exists($new_slug, $categories)) $categories[$new_slug] = 1;

            update_term_meta((int) $term_id, 'lsd_categories', $categories);
        }
    }

    public function term_slug_capture($term_id, $taxonomy): int
    {
        if (!in_array($taxonomy, $this->taxonomies(), true)) return (int) $term_id;

        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) return (int) $term_id;

        if (!isset(self::$slug_update_cache[$taxonomy])) self::$slug_update_cache[$taxonomy] = [];

        self::$slug_update_cache[$taxonomy][$term_id] = $term->slug;

        return (int) $term_id;
    }

    public function term_slug_emit($term_id, $tt_id, $taxonomy): void
    {
        if (!in_array($taxonomy, $this->taxonomies(), true)) return;

        $old_slug = sanitize_title((string) (self::$slug_update_cache[$taxonomy][$term_id] ?? ''));
        unset(self::$slug_update_cache[$taxonomy][$term_id]);

        if (isset(self::$slug_update_cache[$taxonomy]) && !count(self::$slug_update_cache[$taxonomy])) unset(self::$slug_update_cache[$taxonomy]);
        if ($old_slug === '') return;

        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) return;

        $new_slug = sanitize_title($term->slug);
        if ($new_slug === '' || $old_slug === $new_slug) return;

        do_action('lsd_' . $taxonomy . '_term_slug_updated', $old_slug, $new_slug, $term);
    }
}
