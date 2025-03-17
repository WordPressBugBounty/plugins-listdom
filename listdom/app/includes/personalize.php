<?php

class LSD_Personalize extends LSD_Base
{
    public static function generate()
    {
        $main = new LSD_Main();
        $raw = LSD_File::read($main->get_listdom_path() . '/assets/css/personalized.raw');

        $CSS = LSD_Personalize_General::make($raw);
        $CSS = LSD_Personalize_Buttons::make($CSS);
        $CSS = LSD_Personalize_Forms::make($CSS);
        $CSS = LSD_Personalize_Widgets::make($CSS);
        $CSS = LSD_Personalize_Dashboard::make($CSS);
        $CSS = LSD_Personalize_Skins::make($CSS);
        $CSS = LSD_Personalize_Single::make($CSS);

        // Blog ID
        $blog_id = get_current_blog_id();

        // Write the generated CSS file
        LSD_File::write($main->get_listdom_path() . '/assets/css/personalized' . ($blog_id > 1 ? '-' . $blog_id : '') . '.css', $CSS);

        // Delete Fonts
        delete_transient('lsd-customizer-fonts');
    }

    public function assets()
    {
        // Global Settings
        $fonts = LSD_Customizer::fonts();

        // Include the Font
        if (trim($fonts)) wp_enqueue_style('lsd-google-fonts', 'https://fonts.googleapis.com/css?family=' . urlencode($fonts));

        // CSS File
        $css = $this->lsd_asset_url('css/personalized.css');

        // Blog ID
        $blog_id = get_current_blog_id();

        // Blog CSS File
        if ($blog_id > 1 && LSD_File::exists($this->get_listdom_path() . '/assets/css/personalized-' . $blog_id . '.css')) $css = $this->lsd_asset_url('css/personalized-' . $blog_id . '.css');

        // Include Listdom personalized CSS file
        wp_enqueue_style('lsd-personalized', $css, ['lsd-frontend'], LSD_Assets::version());
    }

    /**
     * Retrieves and sanitizes border settings for the button styles.
     *
     * @param array $border The border settings.
     * @return array An associative array containing the border settings.
     */
    protected static function get_border(array $border): array
    {
        if (empty($border)) return [];

        return [
            'top' => sanitize_text_field($border['top']) . 'px',
            'right' => sanitize_text_field($border['right']) . 'px',
            'bottom' => sanitize_text_field($border['bottom']) . 'px',
            'left' => sanitize_text_field($border['left']) . 'px',
            'style' => sanitize_text_field($border['style']),
            'color' => sanitize_text_field($border['color'] ?? ''),
        ];
    }

    /**
     * Retrieves and sanitizes border settings for the button styles.
     *
     * @param array $padding The border settings.
     * @return array An associative array containing the border settings.
     */
    protected static function get_padding(array $padding): array
    {
        if (empty($padding)) return [];

        return [
            'top' => sanitize_text_field($padding['top']) . 'px',
            'right' => sanitize_text_field($padding['right']) . 'px',
            'bottom' => sanitize_text_field($padding['bottom']) . 'px',
            'left' => sanitize_text_field($padding['left']) . 'px',
        ];
    }
}
