<?php

class LSD_Personalize_Skins extends LSD_Personalize
{
    /**
     * Generates personalized tab styles and applies them to the CSS.
     *
     * @param string $CSS The raw CSS content. If null, loads the default CSS.
     * @return string Modified CSS with personalized tab styles.
     */
    public static function make(string $CSS): string
    {
        $skin_types = ['masonry'];
        $skin_settings = [];

        foreach ($skin_types as $type) $skin_settings[$type] = self::skins($type);
        return self::replacement($CSS, $skin_settings);
    }

    /**
     * Retrieves and sanitizes form input settings for a given form type.
     *
     * @param string $skin_type The type of form (e.g., 'form', 'search_form').
     * @return array Sanitized form input settings.
     */
    public static function skins(string $skin_type): array
    {
        $skin_settings = LSD_Options::customizer("skins.{$skin_type}.tabs");
        $normal = $skin_settings['normal'] ?? [];
        $hover = $skin_settings['hover'] ?? [];

        return [
            'bg_color' => sanitize_text_field($normal['bg_color']),
            'text_color' => sanitize_text_field($normal['text_color']),
            'border' => $normal['border'] ?? [],
            'border_style' => $normal['border']['style'],
            'border_color' => $normal['border']['color'] ?: 'inherit',
            'border_radius' => sanitize_text_field($normal['border']['radius']) . 'px',

            'hover_bg_color' => sanitize_text_field($hover['bg_color']),
            'hover_text_color' => sanitize_text_field($hover['text_color']),
            'border_hover' => $hover['border'] ?? [],
            'border_hover_style' => $hover['border']['style'],
            'border_hover_color' => $hover['border']['color'] ?: 'inherit',
            'hover_border_radius' => sanitize_text_field($hover['border']['radius']) . 'px',
        ];
    }

    /**
     * Replaces the placeholders in the CSS with the personalized button styles.
     *
     * @param string $CSS The raw CSS content to be modified.
     * @param array $skin_settings The array of button styles to apply to the CSS.
     * @return string The modified CSS with the button styles replaced.
     */
    private static function replacement(string $CSS, array $skin_settings): string
    {
        foreach ($skin_settings as $skin_type => $settings)
        {
            $CSS = str_replace("(({$skin_type}_tabs_bg_color))", $settings['bg_color'], $CSS);
            $CSS = str_replace("(({$skin_type}_tabs_text_color))", $settings['text_color'], $CSS);

            // Tabs Hover Settings
            $CSS = str_replace("(({$skin_type}_tabs_hover_bg_color))", $settings['hover_bg_color'], $CSS);
            $CSS = str_replace("(({$skin_type}_tabs_hover_text_color))", $settings['hover_text_color'], $CSS);

            // Tabs Border Settings
            $CSS = str_replace("(({$skin_type}_tabs_border))", self::borders($settings['border']), $CSS);
            $CSS = str_replace("(({$skin_type}_tabs_border_style))", $settings['border_style'], $CSS);
            $CSS = str_replace("(({$skin_type}_tabs_border_color))", $settings['border_color'], $CSS);
            $CSS = str_replace("(({$skin_type}_tabs_border_hover))", self::borders($settings['border_hover']), $CSS);
            $CSS = str_replace("(({$skin_type}_tabs_border_hover_style))", $settings['border_hover_style'], $CSS);
            $CSS = str_replace("(({$skin_type}_tabs_border_hover_color))", $settings['border_hover_color'], $CSS);
            $CSS = str_replace("(({$skin_type}_tabs_border_radius))", $settings['border_radius'], $CSS);
            $CSS = str_replace("(({$skin_type}_tabs_hover_border_radius))", $settings['hover_border_radius'], $CSS);
        }

        return $CSS;
    }
}
