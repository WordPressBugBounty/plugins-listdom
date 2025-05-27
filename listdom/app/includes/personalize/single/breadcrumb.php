<?php

class LSD_Personalize_Single_Breadcrumb extends LSD_Personalize_Single
{
    /**
     * {@inheritDoc}
     */
    public static function make(string $CSS): string
    {
        $breadcrumb_types = ['breadcrumb'];
        $breadcrumb_settings = [];

        foreach ($breadcrumb_types as $type) $breadcrumb_settings[$type] = self::breadcrumb($type);

        return self::replacement($CSS, $breadcrumb_settings);
    }

    /**
     * Retrieves and sanitizes form input settings for a given form type.
     *
     * @param string $breadcrumb_type The type of form (e.g., 'form', 'search_form').
     * @return array Sanitized form input settings.
     */
    private static function breadcrumb(string $breadcrumb_type): array
    {
        $breadcrumb_settings = LSD_Options::customizer("single.{$breadcrumb_type}");

        $home = $breadcrumb_settings['breadcrumb']['home'] ?? [];
        $taxonomy = $breadcrumb_settings['breadcrumb']['taxonomy'] ?? [];
        $current = $breadcrumb_settings['breadcrumb']['current'] ?? [];
        $separator = $breadcrumb_settings['breadcrumb']['separator'] ?? [];

        return [
            'icon_gap' => sanitize_text_field($home['gap']) . 'px',
            'home_text_color' => sanitize_text_field($home['text_color']),
            'icon_color' => sanitize_text_field($home['icon_color']),
            'home_family' => sanitize_text_field($home['typography']['family']),
            'home_weight' => sanitize_text_field($home['typography']['weight']),
            'home_align' => sanitize_text_field($home['typography']['align']),
            'home_size' => sanitize_text_field($home['typography']['size']) . 'px',
            'home_line_height' => sanitize_text_field($home['typography']['line_height']) . 'px',

            'taxonomy_text_color' => sanitize_text_field($taxonomy['text_color']),
            'taxonomy_family' => sanitize_text_field($taxonomy['typography']['family']),
            'taxonomy_weight' => sanitize_text_field($taxonomy['typography']['weight']),
            'taxonomy_align' => sanitize_text_field($taxonomy['typography']['align']),
            'taxonomy_size' => sanitize_text_field($taxonomy['typography']['size']) . 'px',
            'taxonomy_line_height' => sanitize_text_field($taxonomy['typography']['line_height']) . 'px',

            'current_text_color' => sanitize_text_field($current['text_color']),
            'current_family' => sanitize_text_field($current['typography']['family']),
            'current_weight' => sanitize_text_field($current['typography']['weight']),
            'current_align' => sanitize_text_field($current['typography']['align']),
            'current_size' => sanitize_text_field($current['typography']['size']) . 'px',
            'current_line_height' => sanitize_text_field($current['typography']['line_height']) . 'px',

            'separator_text_color' => sanitize_text_field($separator['text_color']),
        ];
    }

    /**
     * Replaces breadcrumb style placeholders in the CSS with actual values.
     *
     * @param string $CSS The raw CSS content.
     * @param array $breadcrumb_settings The breadcrumb settings to apply.
     * @return string Modified CSS with applied breadcrumb styles.
     */

    private static function replacement(string $CSS, array $breadcrumb_settings): string
    {
        foreach ($breadcrumb_settings as $breadcrumb_type => $settings)
        {
            // Home
            $CSS = str_replace("((home_{$breadcrumb_type}_text_color))", $settings['home_text_color'], $CSS);
            $CSS = str_replace("((home_{$breadcrumb_type}_icon_gap))", $settings['icon_gap'], $CSS);
            $CSS = str_replace("((home_{$breadcrumb_type}_icon_color))", $settings['icon_color'], $CSS);

            // Typography
            $CSS = str_replace("((home_{$breadcrumb_type}_font_family))", self::font_family($settings['home_family'] ?? ''), $CSS);
            $CSS = str_replace("((home_{$breadcrumb_type}_font_weight))", $settings['home_weight'], $CSS);
            $CSS = str_replace("((home_{$breadcrumb_type}_text_align))", $settings['home_align'], $CSS);
            $CSS = str_replace("((home_{$breadcrumb_type}_font_size))", $settings['home_size'], $CSS);
            $CSS = str_replace("((home_{$breadcrumb_type}_line_height))", $settings['home_line_height'], $CSS);

            // Taxonomy
            $CSS = str_replace("((taxonomy_{$breadcrumb_type}_text_color))", $settings['taxonomy_text_color'], $CSS);
            // Typography
            $CSS = str_replace("((taxonomy_{$breadcrumb_type}_font_family))", self::font_family($settings['taxonomy_family'] ?? ''), $CSS);
            $CSS = str_replace("((taxonomy_{$breadcrumb_type}_font_weight))", $settings['taxonomy_weight'], $CSS);
            $CSS = str_replace("((taxonomy_{$breadcrumb_type}_text_align))", $settings['taxonomy_align'], $CSS);
            $CSS = str_replace("((taxonomy_{$breadcrumb_type}_font_size))", $settings['taxonomy_size'], $CSS);
            $CSS = str_replace("((taxonomy_{$breadcrumb_type}_line_height))", $settings['taxonomy_line_height'], $CSS);

            // Current
            $CSS = str_replace("((current_{$breadcrumb_type}_text_color))", $settings['current_text_color'], $CSS);
            // Typography
            $CSS = str_replace("((current_{$breadcrumb_type}_font_family))", self::font_family($settings['current_family'] ?? ''), $CSS);
            $CSS = str_replace("((current_{$breadcrumb_type}_font_weight))", $settings['current_weight'], $CSS);
            $CSS = str_replace("((current_{$breadcrumb_type}_text_align))", $settings['current_align'], $CSS);
            $CSS = str_replace("((current_{$breadcrumb_type}_font_size))", $settings['current_size'], $CSS);
            $CSS = str_replace("((current_{$breadcrumb_type}_line_height))", $settings['current_line_height'], $CSS);

            $CSS = str_replace("((separator_{$breadcrumb_type}_text_color))", $settings['separator_text_color'], $CSS);
        }

        return $CSS;
    }
}
