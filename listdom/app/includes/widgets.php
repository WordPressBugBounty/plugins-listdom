<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Widgets Class.
 *
 * @class LSD_Widgets
 * @version    1.0.0
 */
class LSD_Widgets extends LSD_Base
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
        add_action('widgets_init', [$this, 'register']);
    }

    public function register()
    {
        // Search Widget
        register_widget('LSD_Widgets_Search');

        // Shortcode Widget
        register_widget('LSD_Widgets_Shortcode');

        // All Listings Widget
        register_widget('LSD_Widgets_Alllistings');

        // Taxonomy Cloud Widget
        register_widget('LSD_Widgets_TaxonomyCloud');

        // Terms Widget
        register_widget('LSD_Widgets_Terms');

        // SimpleMap Widget
        register_widget('LSD_Widgets_SimpleMap');
    }
}
