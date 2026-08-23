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

        $queried = get_queried_object();
        if (!isset($queried->taxonomy, $queried->term_id)) return $template;

        $taxonomy = $queried->taxonomy;
        if (!in_array($taxonomy, $this->taxonomies(), true)) return $template;
        if ($this->archive_shortcode($taxonomy, (int) $queried->term_id) <= 0) return $template;

        $file = 'taxonomy-' . $taxonomy . '.php';
        $located_template = locate_template($file);

        // Listdom Template
        if ($located_template === '') return lsd_template($file);

        return $located_template;
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
        $LSD = new LSD_Shortcodes_Listdom();
        $shortcode_id = $this->archive_shortcode($q->taxonomy, (int) $q->term_id);

        $atts = [];
        if ($shortcode_id > 0) $atts['id'] = $shortcode_id;

        echo LSD_Kses::full($LSD->output($atts, [
            'lsd_filter' => [],
        ]));
    }

    /**
     * Resolve the archive shortcode ID for a taxonomy or term.
     * @param string $taxonomy
     * @param int $term_id
     * @return int
     */
    public static function archive_shortcode_id(string $taxonomy, int $term_id = 0): int
    {
        $shortcode_id = 0;

        // Term Override
        if ($term_id > 0 && LSD_Base::isPro())
        {
            $term_shortcode = get_term_meta($term_id, 'lsd_archive_shortcode', true);
            $term_shortcode = is_scalar($term_shortcode) ? absint($term_shortcode) : 0;
            if ($term_shortcode > 0) $shortcode_id = $term_shortcode;
        }

        // Global Archive Setting
        if ($shortcode_id <= 0)
        {
            $setting_key = self::archive_setting_key($taxonomy);
            if ($setting_key === '') return 0;

            $settings = LSD_Options::settings();
            $shortcode_id = isset($settings[$setting_key]) && is_scalar($settings[$setting_key]) ? absint($settings[$setting_key]) : 0;
        }

        return self::translate_archive_shortcode_id($shortcode_id, $term_id);
    }

    /**
     * Resolve an archive shortcode for the archive term's Polylang language when possible.
     * @param int $shortcode_id
     * @param int $term_id
     * @return int
     */
    protected static function translate_archive_shortcode_id(int $shortcode_id, int $term_id = 0): int
    {
        if ($shortcode_id <= 0 || !function_exists('pll_get_post')) return $shortcode_id;

        $language = '';
        if ($term_id > 0 && function_exists('pll_get_term_language'))
        {
            $term_language = pll_get_term_language($term_id);
            if (is_string($term_language) && $term_language !== '') $language = $term_language;
        }

        $translated_shortcode_id = pll_get_post($shortcode_id, $language);
        return is_numeric($translated_shortcode_id) && (int) $translated_shortcode_id > 0 ? (int) $translated_shortcode_id : $shortcode_id;
    }

    /**
     * Resolve the archive shortcode ID for this taxonomy instance.
     * @param mixed $taxonomy
     * @param int $term_id
     * @return int
     */
    protected function archive_shortcode($taxonomy, int $term_id = 0): int
    {
        // Archive Shortcode
        return self::archive_shortcode_id((string) $taxonomy, $term_id);
    }

    /**
     * Get the global archive setting key for a taxonomy.
     * @param string $taxonomy
     * @return string
     */
    protected static function archive_setting_key(string $taxonomy): string
    {
        // Taxonomy Key
        if ($taxonomy === LSD_Base::TAX_LOCATION) return 'location_archive';
        if ($taxonomy === LSD_Base::TAX_CATEGORY) return 'category_archive';
        if ($taxonomy === LSD_Base::TAX_TAG) return 'tag_archive';
        if ($taxonomy === LSD_Base::TAX_FEATURE) return 'feature_archive';
        if ($taxonomy === LSD_Base::TAX_LABEL) return 'label_archive';

        return '';
    }

    protected function archive_shortcode_add_field(): void
    {
        ?>
        <div class="form-field">
            <h3><?php esc_html_e('Archive Shortcode Settings', 'listdom'); ?></h3>
            <div class="lsd-border lsd-border-radius lsd-p-4 lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-4 width-95-percent">
                <div>
                    <label for="lsd_archive_shortcode"><?php esc_html_e('Archive Shortcode', 'listdom'); ?></label>
                    <?php if ($this->isPro()): ?>
                        <?php echo LSD_Form::shortcodes([
                            'id' => 'lsd_archive_shortcode',
                            'name' => 'lsd_archive_shortcode',
                            'value' => '',
                            'only_archive_skins' => '1',
                            'show_empty' => '1',
                            'empty_label' => esc_html__('Inherit from global archive settings', 'listdom'),
                        ]); ?>
                        <p class="description"><?php esc_html_e('Select a shortcode to override the archive design for this term. This has priority over Settings > General > Archives.', 'listdom'); ?></p>
                    <?php else: ?>
                        <?php echo LSD_Base::alert($this->missFeatureMessage(esc_html__('Archive Shortcode', 'listdom')), 'warning'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    protected function archive_shortcode_edit_field($term): void
    {
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_archive_shortcode"><?php esc_html_e('Archive Shortcode', 'listdom'); ?></label>
            </th>
            <td>
                <?php if ($this->isPro()): ?>
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_archive_shortcode',
                        'name' => 'lsd_archive_shortcode',
                        'value' => get_term_meta($term->term_id, 'lsd_archive_shortcode', true),
                        'only_archive_skins' => '1',
                        'show_empty' => '1',
                        'empty_label' => esc_html__('Inherit from global archive settings', 'listdom'),
                    ]); ?>
                    <p class="description"><?php esc_html_e('Select a shortcode to override the archive design for this term. This has priority over Settings > General > Archives.', 'listdom'); ?></p>
                <?php else: ?>
                    <?php echo LSD_Base::alert($this->missFeatureMessage(esc_html__('Archive Shortcode', 'listdom')), 'warning'); ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    protected function save_archive_shortcode($term_id): void
    {
        if (!$this->isPro()) return;

        $shortcode = absint($this->posted_scalar('lsd_archive_shortcode'));
        update_term_meta($term_id, 'lsd_archive_shortcode', $shortcode);
    }

    /**
     * Read a scalar posted value.
     * @param string $key
     * @return string
     */
    protected function posted_scalar(string $key): string
    {
        // Posted Value
        if (!isset($_POST[$key]) || !is_scalar($_POST[$key])) return '';

        return sanitize_text_field(wp_unslash($_POST[$key]));
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
