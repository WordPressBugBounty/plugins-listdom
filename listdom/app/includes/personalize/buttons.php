<?php

class LSD_Personalize_Buttons extends LSD_Personalize
{
    /**
     * Generates the CSS for all button types, resolving inherited styles.
     *
     * @param string $CSS The raw CSS content to be modified.
     * @return string The modified CSS with personalized button styles.
     */
    public static function make(string $CSS): string
    {
        $button_types = ['primary', 'search', 'pagination', 'secondary', 'solid'];
        $buttons_settings = [];

        foreach ($button_types as $type) $buttons_settings[$type] = self::buttons($type);

        return self::replacement($CSS, $buttons_settings);
    }

    /**
     * Retrieves the button styles and settings for a given button type, resolving inheritance if necessary.
     *
     * @param string $button_type The type of button.
     * @return array An associative array containing button style settings.
     */
    private static function buttons(string $button_type): array
    {
        // Get the customizer settings for the specific button type
        $button_settings = LSD_Options::customizer("buttons.{$button_type}_button");

        $normal = $button_settings['normal']['_'] ?? [];
        $hover = $button_settings['hover']['_'] ?? [];

        return [
            'bg_color' => sanitize_text_field($normal['bg1']),
            'bg_color_2' => $normal['bg2'] ?? '',
            'text_color' => sanitize_text_field($normal['text']),
            'border_radius' => sanitize_text_field($normal['border']['radius']) . 'px',
            'hover_bg_color' => sanitize_text_field($hover['bg1']),
            'hover_bg_color_2' => sanitize_text_field($hover['bg2']),
            'hover_text_color' => sanitize_text_field($hover['text']),
            'padding' => $normal['padding'] ?? [],
            'border' => $normal['border'] ?? [],
            'border_hover' => $hover['border'] ?? [],
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
     * @param array $buttons_settings The array of button styles to apply to the CSS.
     * @return string The modified CSS with the button styles replaced.
     */
    private static function replacement(string $CSS, array $buttons_settings): string
    {
        foreach ($buttons_settings as $button_type => $settings)
        {
            // Normal
            $CSS = str_replace("(({$button_type}_button_bg_color))", $settings['bg_color'], $CSS);
            $CSS = str_replace("(({$button_type}_button_bg_color_2))", $settings['bg_color_2'], $CSS);
            $CSS = str_replace("(({$button_type}_button_text_color))", $settings['text_color'], $CSS);
            $CSS = str_replace("(({$button_type}_button_border))", self::borders($settings['border']), $CSS);
            $CSS = str_replace("(({$button_type}_button_border_style))", $settings['border']['style'], $CSS);
            $CSS = str_replace("(({$button_type}_button_border_color))", $settings['border']['color'] ?: 'none', $CSS);
            $CSS = str_replace("(({$button_type}_button_border_radius))", $settings['border_radius'], $CSS);

            // hover
            $CSS = str_replace("(({$button_type}_button_hover_bg_color))", $settings['hover_bg_color'], $CSS);
            $CSS = str_replace("(({$button_type}_button_hover_bg_color_2))", $settings['hover_bg_color_2'], $CSS);
            $CSS = str_replace("(({$button_type}_button_hover_text_color))", $settings['hover_text_color'], $CSS);
            $CSS = str_replace("(({$button_type}_button_border_hover))", self::borders($settings['border_hover']), $CSS);
            $CSS = str_replace("(({$button_type}_button_border_hover_style))", $settings['border_hover']['style'], $CSS);
            $CSS = str_replace("(({$button_type}_button_border_hover_color))", $settings['border_hover']['color'] ?: 'none', $CSS);
            $CSS = str_replace("(({$button_type}_button_hover_border_radius))", $settings['hover_border_radius'], $CSS);
            $CSS = str_replace("(({$button_type}_button_padding))", self::paddings($settings['padding']), $CSS);

            // Typography
            $CSS = str_replace("(({$button_type}_button_font_family))", self::font_family($settings['family'] ?? ''), $CSS);
            $CSS = str_replace("(({$button_type}_button_font_weight))", $settings['weight'], $CSS);
            $CSS = str_replace("(({$button_type}_button_text_align))", $settings['align'], $CSS);
            $CSS = str_replace("(({$button_type}_button_font_size))", $settings['size'], $CSS);
            $CSS = str_replace("(({$button_type}_button_line_height))", $settings['line_height'], $CSS);
        }

        return $CSS;
    }
}
