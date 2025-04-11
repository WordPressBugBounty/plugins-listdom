<?php

class LSD_Widgets extends LSD_Base
{
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
