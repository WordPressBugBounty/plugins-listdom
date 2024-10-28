<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Styles Class.
 *
 * @class LSD_Styles
 * @version    1.0.0
 */
class LSD_Styles extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

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

    public static function grid()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
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
        ], 'listgrid');
    }

    public static function masonry()
    {
        return LSD_Styles::filter([
            'style1' => esc_html__('Style 1', 'listdom'),
            'style2' => esc_html__('Style 2', 'listdom'),
            'style3' => esc_html__('Style 3', 'listdom'),
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
