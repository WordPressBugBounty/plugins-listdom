<?php

class LSD_Personalize_Dashboard extends LSD_Personalize
{
    /**
     * Generates personalized dashboard styles and applies them to the CSS.
     *
     * @param string $CSS The raw CSS content. If null, loads the default CSS.
     * @return string Modified CSS with personalized dashboard styles.
     */
    public static function make(string $CSS): string
    {
        $dashboard_settings = self::dashboard('menu');
        return self::replacement($CSS, $dashboard_settings);
    }

    /**
     * Retrieves and sanitizes form input settings for a given form type.
     *
     * @param string $dashboard_type The type of form (e.g., 'form', 'search_form').
     * @return array Sanitized form input settings.
     */
    public static function dashboard(string $dashboard_type): array
    {
        $dashboard_settings = LSD_Options::customizer("dashboard.{$dashboard_type}");

        $normal = $dashboard_settings['normal']['_'] ?? [];
        $hover = $dashboard_settings['hover']['_'] ?? [];

        return [
            'container_bg' => sanitize_text_field($normal['container_bg']),
            'bg_color' => sanitize_text_field($normal['bg_color']),
            'text_color' => sanitize_text_field($normal['text_color']),
            'icon_color' => sanitize_text_field($normal['icon_color']),
            'border' => self::get_border($normal['border'] ?? []),
            'border_style' => $normal['border']['style'],
            'border_color' => $normal['border']['color'],
            'border_radius' => sanitize_text_field($normal['border']['radius']) . 'px',

            'hover_bg_color' => sanitize_text_field($hover['bg_color']),
            'hover_text_color' => sanitize_text_field($hover['text_color']),
            'border_hover' => self::get_border($hover['border'] ?? []),
            'border_hover_style' => $hover['border']['style'],
            'border_hover_color' => $hover['border']['color'],
            'hover_border_radius' => sanitize_text_field($hover['border']['radius']) . 'px',

            'family' => sanitize_text_field($normal['typography']['family']),
            'weight' => sanitize_text_field($normal['typography']['weight']),
            'align' => sanitize_text_field($normal['typography']['align']),
            'size' => sanitize_text_field($normal['typography']['size']) . 'px',
            'line_height' => sanitize_text_field($normal['typography']['line_height']) . 'px',
        ];
    }

    /**
     * Replaces the placeholders in the CSS with the personalized button styles.
     *
     * @param string $CSS The raw CSS content to be modified.
     * @param array $settings The array of button styles to apply to the CSS.
     * @return string The modified CSS with the button styles replaced.
     */
    private static function replacement(string $CSS, array $settings): string
    {
        // Dashboard Container Settings
        $CSS = str_replace("((dashboard_container_bg_color))", $settings['container_bg'], $CSS);

        // Dashboard Background and Text Color
        $CSS = str_replace("((dashboard_bg_color))", $settings['bg_color'], $CSS);
        $CSS = str_replace("((dashboard_text_color))", $settings['text_color'], $CSS);

        // Dashboard Icon Color
        $CSS = str_replace("((dashboard_icon_color))", $settings['icon_color'], $CSS);

        // Dashboard Hover Settings
        $CSS = str_replace("((dashboard_hover_bg_color))", $settings['hover_bg_color'], $CSS);
        $CSS = str_replace("((dashboard_hover_text_color))", $settings['hover_text_color'], $CSS);

        // Dashboard Border Settings
        $CSS = str_replace("((dashboard_border))", self::borders($settings['border']), $CSS);
        $CSS = str_replace("((dashboard_border_style))", $settings['border_style'], $CSS);
        $CSS = str_replace("((dashboard_border_color))", $settings['border_color'], $CSS);
        $CSS = str_replace("((dashboard_border_hover))", self::borders($settings['border_hover']), $CSS);
        $CSS = str_replace("((dashboard_border_hover_style))", $settings['border_hover_style'], $CSS);
        $CSS = str_replace("((dashboard_border_hover_color))", $settings['border_hover_color'], $CSS);

        // Dashboard Border Radius
        $CSS = str_replace("((dashboard_border_radius))", $settings['border_radius'], $CSS);
        $CSS = str_replace("((dashboard_hover_border_radius))", $settings['hover_border_radius'], $CSS);

        // Typography
        $CSS = str_replace("((dashboard_font_family))", $settings['family'], $CSS);
        $CSS = str_replace("((dashboard_font_weight))", $settings['weight'], $CSS);
        $CSS = str_replace("((dashboard_text_align))", $settings['align'], $CSS);
        $CSS = str_replace("((dashboard_font_size))", $settings['size'], $CSS);
        $CSS = str_replace("((dashboard_line_height))", $settings['line_height'], $CSS);

        return $CSS;
    }

    /**
     * Returns the formatted border CSS for the given border settings.
     *
     * @param array $border The border settings.
     * @return string The border CSS string.
     */
    private static function borders(array $border): string
    {
        return sprintf('%s %s %s %s', $border['top'], $border['right'], $border['bottom'], $border['left']);
    }
}
