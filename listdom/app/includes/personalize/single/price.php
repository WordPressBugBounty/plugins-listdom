<?php

class LSD_Personalize_Single_Price extends LSD_Personalize_Single
{
    /**
     * {@inheritDoc}
     */
    public static function make(string $CSS): string
    {
        $price_types = ['price'];
        $price_settings = [];

        foreach ($price_types as $type) $price_settings[$type] = self::price($type);

        return self::replacement($CSS, $price_settings);
    }

    /**
     * Retrieves and sanitizes form input settings for a given form type.
     *
     * @param string $price_type The type of form (e.g., 'form', 'search_form').
     * @return array Sanitized form input settings.
     */
    private static function price(string $price_type): array
    {
        $price_settings = LSD_Options::customizer("single.{$price_type}");

        $normal = $price_settings['price']['normal'] ?? [];
        $hover = $price_settings['price']['hover'] ?? [];

        return [
            'bg_color' => sanitize_text_field($normal['bg_color']),
            'text_color' => sanitize_text_field($normal['text_color']),
            'border' => $normal['border'] ?? [],
            'padding' => $normal['padding'] ?? [],
            'border_style' => $normal['border']['style'],
            'border_color' => $normal['border']['color'],
            'border_radius' => sanitize_text_field($normal['border']['radius']) . 'px',

            'hover_bg_color' => sanitize_text_field($hover['bg_color']),
            'hover_text_color' => sanitize_text_field($hover['text_color']),
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
     * Replaces price style placeholders in the CSS with actual values.
     *
     * @param string $CSS The raw CSS content.
     * @param array $price_settings The price settings to apply.
     * @return string Modified CSS with applied price styles.
     */

    private static function replacement(string $CSS, array $price_settings): string
    {
        foreach ($price_settings as $price_type => $settings)
        {
            // Normal state (non-hover settings)
            $CSS = str_replace("(({$price_type}_bg_color))", $settings['bg_color'], $CSS);
            $CSS = str_replace("(({$price_type}_text_color))", $settings['text_color'], $CSS);

            // Hover state settings
            $CSS = str_replace("(({$price_type}_hover_bg_color))", $settings['hover_bg_color'], $CSS);
            $CSS = str_replace("(({$price_type}_hover_text_color))", $settings['hover_text_color'], $CSS);

            $CSS = str_replace("(({$price_type}_border))", self::borders($settings['border']), $CSS);
            $CSS = str_replace("(({$price_type}_padding))", self::paddings($settings['padding']), $CSS);
            $CSS = str_replace("(({$price_type}_border_style))", $settings['border_style'], $CSS);
            $CSS = str_replace("(({$price_type}_border_color))", $settings['border_color'], $CSS);
            $CSS = str_replace("(({$price_type}_border_hover))", self::borders($settings['border_hover']), $CSS);
            $CSS = str_replace("(({$price_type}_padding_hover))", self::paddings($settings['padding_hover']), $CSS);
            $CSS = str_replace("(({$price_type}_border_hover_style))", $settings['border_hover_style'], $CSS);
            $CSS = str_replace("(({$price_type}_border_hover_color))", $settings['border_hover_color'], $CSS);

            $CSS = str_replace("(({$price_type}_border_radius))", $settings['border_radius'], $CSS);
            $CSS = str_replace("(({$price_type}_hover_border_radius))", $settings['hover_border_radius'], $CSS);

            // Typography
            $CSS = str_replace("(({$price_type}_font_family))", self::font_family($settings['family'] ?? ''), $CSS);
            $CSS = str_replace("(({$price_type}_font_weight))", $settings['weight'], $CSS);
            $CSS = str_replace("(({$price_type}_text_align))", $settings['align'], $CSS);
            $CSS = str_replace("(({$price_type}_font_size))", $settings['size'], $CSS);
            $CSS = str_replace("(({$price_type}_line_height))", $settings['line_height'], $CSS);
        }

        return $CSS;
    }
}
