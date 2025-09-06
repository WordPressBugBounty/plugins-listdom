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
        $CSS = LSD_Personalize_Profile::make($CSS);
        $CSS = LSD_Personalize_Skins::make($CSS);
        $CSS = LSD_Personalize_Single::make($CSS);

        // Remove Comments & New Lines
        $CSS = preg_replace('!/\*.*?\*/!s', '', $CSS);
        $CSS = trim(preg_replace('/\s+/', ' ', $CSS));

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
     * Returns the formatted border CSS for the given border settings.
     *
     * @param array $border The border settings.
     * @return string The border CSS string.
     */
    protected static function borders(array $border): string
    {
        return sprintf('%s %s %s %s',
            isset($border['top']) ? sanitize_text_field($border['top']) . 'px' : 'inherit',
            isset($border['right']) ? sanitize_text_field($border['right']) . 'px' : 'inherit',
            isset($border['bottom']) ? sanitize_text_field($border['bottom']) . 'px' : 'inherit',
            isset($border['left']) ? sanitize_text_field($border['left']) . 'px' : 'inherit'
        );
    }

    /**
     * Returns the formatted border CSS for the given border settings.
     *
     * @param array $padding The border settings.
     * @return string The border CSS string.
     */
    protected static function paddings(array $padding): string
    {
        return sprintf('%s %s %s %s',
            isset($padding['top']) ? sanitize_text_field($padding['top']) . 'px' : 'inherit',
            isset($padding['right']) ? sanitize_text_field($padding['right']) . 'px' : 'inherit',
            isset($padding['bottom']) ? sanitize_text_field($padding['bottom']) . 'px' : 'inherit',
            isset($padding['left']) ? sanitize_text_field($padding['left']) . 'px' : 'inherit'
        );
    }

    protected static function unit_number($data, string $fallback = 'px', string $empty = ''): string
    {
        if (is_array($data))
        {
            $value = $data['value'] ?? '';
            $unit = $data['unit'] ?? $fallback;
        }
        else
        {
            $value = $data;
            $unit = $fallback;
        }

        $value = trim(sanitize_text_field($value));
        if ($value === '') return $empty;

        $unit = sanitize_text_field($unit);
        $allowed = ['px', 'em', 'rem', '%'];
        if (!in_array($unit, $allowed)) $unit = $fallback;

        return $value . $unit;
    }

    protected static function font_family(string $family = ''): string
    {
        return trim($family) && $family !== 'inherit' ? '"' . ucfirst($family) . '", Arial, monospace' : 'inherit';
    }
}
