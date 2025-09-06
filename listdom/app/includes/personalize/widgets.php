<?php

class LSD_Personalize_Widgets extends LSD_Personalize
{
    /**
     * Generates personalized widget styles and applies them to the CSS.
     *
     * @param string $CSS The raw CSS content. If null, loads the default CSS.
     * @return string Modified CSS with personalized widget styles.
     */
    public static function make(string $CSS): string
    {
        $widget_types = ['cloud', 'terms'];
        $widget_settings = [];

        foreach ($widget_types as $type) $widget_settings[$type] = self::widgets($type);

        return self::replacement($CSS, $widget_settings);
    }

    /**
     * Retrieves and sanitizes form input settings for a given form type.
     *
     * @param string $widget_type The type of form (e.g., 'form', 'search_form').
     * @return array Sanitized form input settings.
     */
    private static function widgets(string $widget_type): array
    {
        $widget_settings = LSD_Options::customizer("taxonomies.widgets.{$widget_type}");

        $normal = $widget_settings['normal'] ?? [];
        $hover = $widget_settings['hover'] ?? [];

        return [
            'bg_color' => sanitize_text_field($normal['bg']),
            'text_color' => sanitize_text_field($normal['text']),
            'border' => $normal['border'] ?? [],
            'padding' => $normal['padding'] ?? [],
            'border_style' => $normal['border']['style'],
            'border_color' => $normal['border']['color'],
            'border_radius' => sanitize_text_field($normal['border']['radius']) . 'px',

            'hover_bg_color' => sanitize_text_field($hover['bg']),
            'hover_text_color' => sanitize_text_field($hover['text']),
            'border_hover' => $hover['border'] ?? [],
            'padding_hover' => $hover['padding'] ?? [],
            'border_hover_style' => $hover['border']['style'],
            'border_hover_color' => $hover['border']['color'],
            'hover_border_radius' => sanitize_text_field($hover['border']['radius']) . 'px',

            'family' => sanitize_text_field($normal['typography']['family']),
            'weight' => sanitize_text_field($normal['typography']['weight']),
            'align' => sanitize_text_field($normal['typography']['align']),
            'size' => self::unit_number($normal['typography']['size']),
            'line_height' => self::unit_number($normal['typography']['line_height']),
        ];
    }

    /**
     * Replaces widget style placeholders in the CSS with actual values.
     *
     * @param string $CSS The raw CSS content.
     * @param array $widget_settings The widget settings to apply.
     * @return string Modified CSS with applied widget styles.
     */

    private static function replacement(string $CSS, array $widget_settings): string
    {
        foreach ($widget_settings as $widget_type => $settings)
        {
            // Normal state (non-hover settings)
            $CSS = str_replace("(({$widget_type}_widget_bg_color))", $settings['bg_color'], $CSS);
            $CSS = str_replace("(({$widget_type}_widget_title_text_color))", $settings['text_color'], $CSS);
            $CSS = str_replace("(({$widget_type}_widget_title_border_color))", $settings['border']['color'], $CSS);

            // Hover state settings
            $CSS = str_replace("(({$widget_type}_widget_hover_bg_color))", $settings['hover_bg_color'], $CSS);
            $CSS = str_replace("(({$widget_type}_widget_hover_text_color))", $settings['hover_text_color'], $CSS);

            $CSS = str_replace("(({$widget_type}_widget_border))", self::borders($settings['border']), $CSS);
            $CSS = str_replace("(({$widget_type}_widget_padding))", self::paddings($settings['padding']), $CSS);
            $CSS = str_replace("(({$widget_type}_widget_border_style))", $settings['border_style'], $CSS);
            $CSS = str_replace("(({$widget_type}_widget_border_color))", $settings['border_color'], $CSS);
            $CSS = str_replace("(({$widget_type}_widget_border_hover))", self::borders($settings['border_hover']), $CSS);
            $CSS = str_replace("(({$widget_type}_widget_padding_hover))", self::paddings($settings['padding_hover']), $CSS);
            $CSS = str_replace("(({$widget_type}_widget_border_hover_style))", $settings['border_hover_style'], $CSS);
            $CSS = str_replace("(({$widget_type}_widget_border_hover_color))", $settings['border_hover_color'], $CSS);

            $CSS = str_replace("(({$widget_type}_widget_border_radius))", $settings['border_radius'], $CSS);
            $CSS = str_replace("(({$widget_type}_widget_hover_border_radius))", $settings['hover_border_radius'], $CSS);

            // Typography
            $CSS = str_replace("(({$widget_type}_widget_font_family))", self::font_family($settings['family'] ?? ''), $CSS);
            $CSS = str_replace("(({$widget_type}_widget_font_weight))", $settings['weight'], $CSS);
            $CSS = str_replace("(({$widget_type}_widget_text_align))", $settings['align'], $CSS);
            $CSS = str_replace("(({$widget_type}_widget_font_size))", $settings['size'], $CSS);
            $CSS = str_replace("(({$widget_type}_widget_line_height))", $settings['line_height'], $CSS);
        }

        return $CSS;
    }
}
