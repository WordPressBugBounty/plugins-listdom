<?php

class LSD_Search extends LSD_Base
{
    public function init()
    {
        // Search Post Type
        $ptype = new LSD_PTypes_Search();
        $ptype->init();

        // Search Shortcode
        $shortcode = new LSD_Shortcodes_Search();
        $shortcode->init();
    }

    public static function get_styles(): array
    {
        return apply_filters('lsd_search_styles', [
            'default' => esc_html__('Default', 'listdom'),
            'sidebar' => esc_html__('Sidebar', 'listdom'),
        ]);
    }
}
