<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Shortcodes Class.
 *
 * @class LSD_Shortcodes
 * @version    1.0.0
 */
class LSD_Shortcodes extends LSD_Base
{
    public function init()
    {
        // Listdom Shortcode
        (new LSD_Shortcodes_Listdom())->init();

        // SimpleMap Shortcode
        (new LSD_Shortcodes_Simplemap())->init();

        // Location Shortcode
        (new LSD_Shortcodes_Location())->init();

        // Category Shortcode
        (new LSD_Shortcodes_Category())->init();

        // Tag Shortcode
        (new LSD_Shortcodes_Tag())->init();

        // Feature Shortcode
        (new LSD_Shortcodes_Feature())->init();

        // Label Shortcode
        (new LSD_Shortcodes_Label())->init();

        // Taxonomy Cloud Shortcode
        (new LSD_Shortcodes_TaxonomyCloud())->init();

        // Terms Shortcode
        (new LSD_Shortcodes_Terms())->init();

        // Auth Shortcode
        (new LSD_Shortcodes_Auth())->init();
    }

    public function parse($post_id, $atts = []): array
    {
        $post_atts = [];
        if ($post_id) $post_atts = $this->get_post_meta($post_id);

        // Overwrite values passed directly from shortcode
        $skin = $post_atts['lsd_display']['skin'] ?? $this->get_default_skin();
        foreach ($atts as $key => $value)
        {
            if (isset($post_atts['lsd_display'][$skin][$key]))
            {
                $post_atts['lsd_display'][$skin][$key] = $value;
            }
        }

        return wp_parse_args($atts, $post_atts);
    }

    public function get_default_skin(): string
    {
        return 'grid';
    }
}
