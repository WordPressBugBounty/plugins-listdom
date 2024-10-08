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
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        // Listdom Shortcode
        $Listdom = new LSD_Shortcodes_Listdom();
        $Listdom->init();

        // SimpleMap Shortcode
        $Simplemap = new LSD_Shortcodes_Simplemap();
        $Simplemap->init();

        // Location Shortcode
        $Location = new LSD_Shortcodes_Location();
        $Location->init();

        // Category Shortcode
        $Category = new LSD_Shortcodes_Category();
        $Category->init();

        // Label Shortcode
        $Label = new LSD_Shortcodes_Label();
        $Label->init();

        // Taxonomy Cloud Shortcode
        $TaxonomyCloud = new LSD_Shortcodes_TaxonomyCloud();
        $TaxonomyCloud->init();

        // Terms Shortcode
        $Terms = new LSD_Shortcodes_Terms();
        $Terms->init();
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
        return 'singlemap';
    }
}
