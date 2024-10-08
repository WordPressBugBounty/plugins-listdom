<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Personalize Class.
 *
 * @class LSD_Personalize
 * @version    1.0.0
 */
class LSD_Personalize extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function generate()
    {
        // Global Settings
        $settings = LSD_Options::settings();

        $main = new LSD_Main();
        $raw = LSD_File::read($main->get_listdom_path() . '/assets/css/personalized.raw');

        $CSS = str_replace('((dply_main_color))', $settings['dply_main_color'], $raw);
        $CSS = str_replace('((dply_secondary_color))', $settings['dply_secondary_color'], $CSS);

        $fonts = $main->get_fonts();
        $font = $fonts[$settings['dply_main_font']] ?? ['family' => 'Lato'];
        $CSS = str_replace('((dply_main_font))', $font['family'], $CSS);

        // Blog ID
        $blog_id = get_current_blog_id();

        // Write the generated CSS file
        LSD_File::write($main->get_listdom_path() . '/assets/css/personalized' . ($blog_id > 1 ? '-' . $blog_id : '') . '.css', $CSS);
    }

    public function assets()
    {
        // Global Settings
        $settings = LSD_Options::settings();

        $fonts = $this->get_fonts();
        $font = $fonts[$settings['dply_main_font']] ?? ['code' => 'Lato'];

        // Include the Font
        wp_enqueue_style('google-font-' . sanitize_title($font['code']), 'https://fonts.googleapis.com/css?family=' . urlencode($font['code']));

        // CSS File
        $css = $this->lsd_asset_url('css/personalized.css');

        // Blog ID
        $blog_id = get_current_blog_id();

        // Blog CSS File
        if ($blog_id > 1 && LSD_File::exists($this->get_listdom_path() . '/assets/css/personalized-' . $blog_id . '.css')) $css = $this->lsd_asset_url('css/personalized-' . $blog_id . '.css');

        // Include Listdom personalized CSS file
        wp_enqueue_style('lsd-personalized', $css, ['lsd-frontend'], LSD_VERSION);
    }
}
