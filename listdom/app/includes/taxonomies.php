<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Taxonomies Class.
 *
 * @class LSD_Taxonomies
 * @version    1.0.0
 */
class LSD_Taxonomies extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        // Listing Category
        $Category = new LSD_Taxonomies_Category();
        $Category->init();

        // Listing Location
        $Location = new LSD_Taxonomies_Location();
        $Location->init();

        // Listing Tag
        $Tag = new LSD_Taxonomies_Tag();
        $Tag->init();

        // Listing Feature
        $Feature = new LSD_Taxonomies_Feature();
        $Feature->init();

        // Listing Label
        $Label = new LSD_Taxonomies_Label();
        $Label->init();

        // Redirect to Archive Page
        add_filter('init', [$this, 'redirect'], 999);

        // Listdom Taxonomy Template
        add_filter('template_include', [$this, 'template'], 99);

        // Listdom Archive Content
        foreach ($this->taxonomies() as $tax) add_action($tax . '_archive_content', [$this, 'content']);
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
            if ($template == '') $template = lsd_template($file);
        }
        // Listdom Category
        else if (is_tax(LSD_Base::TAX_CATEGORY) && isset($settings['category_archive']) && trim($settings['category_archive']))
        {
            $file = 'taxonomy-' . LSD_Base::TAX_CATEGORY . '.php';
            $template = locate_template($file);

            // Listdom Template
            if ($template == '') $template = lsd_template($file);
        }
        // Listdom Feature
        else if (is_tax(LSD_Base::TAX_FEATURE) && isset($settings['feature_archive']) && trim($settings['feature_archive']))
        {
            $file = 'taxonomy-' . LSD_Base::TAX_FEATURE . '.php';
            $template = locate_template($file);

            // Listdom Template
            if ($template == '') $template = lsd_template($file);
        }
        // Listdom Tag
        else if (is_tax(LSD_Base::TAX_TAG) && isset($settings['tag_archive']) && trim($settings['tag_archive']))
        {
            $file = 'taxonomy-' . LSD_Base::TAX_TAG . '.php';
            $template = locate_template($file);

            // Listdom Template
            if ($template == '') $template = lsd_template($file);
        }
        // Listdom Label
        else if (is_tax(LSD_Base::TAX_LABEL) && isset($settings['label_archive']) && trim($settings['label_archive']))
        {
            $file = 'taxonomy-' . LSD_Base::TAX_LABEL . '.php';
            $template = locate_template($file);

            // Listdom Template
            if ($template == '') $template = lsd_template($file);
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
                $term = get_term_by('name', $t, $taxonomy);
                if ($term && isset($term->term_id)) $ids[] = $term->term_id;
            }

            return $ids;
        }
        else
        {
            $term = get_term_by('name', $term, $taxonomy);
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
                $term = get_term_by('term_id', $t, $taxonomy);
                if ($term && isset($term->name)) $names[] = $term->name;
            }

            return $names;
        }
        else
        {
            $term = get_term_by('term_id', $term, $taxonomy);
            return $term ? $term->name : null;
        }
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
        $icon = get_term_meta($term_id, 'lsd_icon', true);
        $HTML = trim($icon) ? '<i class="lsd-icon ' . esc_attr($icon) . ' ' . esc_attr($class) . '" aria-hidden="true"></i>' : '';

        return apply_filters('lsd_term_icon', $HTML, $term_id, $class);
    }
}
