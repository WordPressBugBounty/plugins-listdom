<?php

class LSD_Styles extends LSD_Base
{
    public static function render($skin)
    {
        do_action('lsd_styles_render', $skin);
    }

    public static function filter($styles, $skin)
    {
        return apply_filters('lsd_styles', $styles, $skin);
    }

    public static function carousel()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
            'style4' => esc_html__('Style 4', 'listdom'),
            'style5' => esc_html__('Style 5', 'listdom'),
        ], 'carousel');
    }

    public static function cover()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
            'style4' => esc_html__('Style 4', 'listdom'),
        ], 'cover');
    }

    public static function accordion()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
        ], 'accordion');
    }

    public static function gallery()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
        ], 'gallery');
    }

    public static function mosaic()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
        ], 'mosaic');
    }

    public static function timeline()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
        ], 'timeline');
    }

    public static function grid()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
            'style4' => esc_html__('Style 4', 'listdom'),
        ], 'grid');
    }

    public static function halfmap()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
        ], 'halfmap');
    }

    public static function list()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
            'style4' => esc_html__('Style 4', 'listdom'),
        ], 'list');
    }

    public static function side()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
        ], 'side');
    }

    public static function listgrid()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
            'style4' => esc_html__('Style 4', 'listdom'),
        ], 'listgrid');
    }

    public static function masonry()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
            'style4' => esc_html__('Style 4', 'listdom'),
        ], 'masonry');
    }

    public static function slider()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
            'style4' => esc_html__('Style 4', 'listdom'),
            'style5' => esc_html__('Style 5', 'listdom'),
        ], 'slider');
    }

    public static function table()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
        ], 'table');
    }

    public static function detail_types()
    {
        return LSD_Styles::filter([
            'premade' => esc_html__('Pre-Made Styles', 'listdom'),
            'dynamic' => esc_html__('Design Builder', 'listdom'),
        ], 'detail_types');
    }

    public static function details()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1 - Basic', 'listdom'),
            'style2' => esc_html__('Style 2 - Sidebar', 'listdom'),
            'style3' => esc_html__('Style 3 - Slider Header', 'listdom'),
            'style4' => esc_html__('Style 4 - User Directory', 'listdom'),
            'dynamic' => esc_html__('Design Builder', 'listdom'),
        ], 'details');
    }

    public static function infowindow()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
        ], 'infowindow');
    }
}
