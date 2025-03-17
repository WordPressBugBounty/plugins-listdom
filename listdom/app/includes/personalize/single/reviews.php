<?php

class LSD_Personalize_Single_Reviews extends LSD_Personalize_Single
{
    /**
     * {@inheritDoc}
     */
    public static function make(string $CSS): string
    {
        $reviews_types = ['reviews'];
        $reviews_settings = [];

        foreach ($reviews_types as $type) $reviews_settings[$type] = self::reviews($type);
        return self::replacement($CSS, $reviews_settings);
    }

    /**
     * Retrieves and sanitizes form input settings for a given form type.
     *
     * @param string $reviews_types The type of form (e.g., 'form', 'search_form').
     * @return array Sanitized form input settings.
     */
    public static function reviews(string $reviews_types): array
    {
        $reviews_settings = LSD_Options::customizer("single.{$reviews_types}.review_tabs");

        $normal = $reviews_settings['normal'] ?? [];
        $hover = $reviews_settings['hover'] ?? [];

        return [
            'bg_color' => sanitize_text_field($normal['bg_color']),
            'text_color' => sanitize_text_field($normal['text_color']),
            'border' => self::get_border($normal['border'] ?? []),
            'border_style' => $normal['border']['style'],
            'border_color' => $normal['border']['color'] ?: 'inherit',
            'border_radius' => sanitize_text_field($normal['border']['radius']) . 'px',

            'hover_bg_color' => sanitize_text_field($hover['bg_color']),
            'hover_text_color' => sanitize_text_field($hover['text_color']),
            'border_hover' => self::get_border($hover['border'] ?? []),
            'border_hover_style' => $hover['border']['style'],
            'border_hover_color' => $hover['border']['color'] ?: 'inherit',
            'hover_border_radius' => sanitize_text_field($hover['border']['radius']) . 'px',
        ];
    }

    /**
     * Replaces the placeholders in the CSS with the personalized button styles.
     *
     * @param string $CSS The raw CSS content to be modified.
     * @param array $reviews_settings The array of button styles to apply to the CSS.
     * @return string The modified CSS with the button styles replaced.
     */
    private static function replacement(string $CSS, array $reviews_settings): string
    {
        foreach ($reviews_settings as $reviews_types => $settings)
        {
            $CSS = str_replace("(({$reviews_types}_tabs_bg_color))", $settings['bg_color'], $CSS);
            $CSS = str_replace("(({$reviews_types}_tabs_text_color))", $settings['text_color'], $CSS);

            // Tabs Hover Settings
            $CSS = str_replace("(({$reviews_types}_tabs_hover_bg_color))", $settings['hover_bg_color'], $CSS);
            $CSS = str_replace("(({$reviews_types}_tabs_hover_text_color))", $settings['hover_text_color'], $CSS);

            // Tabs Border Settings
            $CSS = str_replace("(({$reviews_types}_tabs_border))", self::borders($settings['border']), $CSS);
            $CSS = str_replace("(({$reviews_types}_tabs_border_style))", $settings['border_style'], $CSS);
            $CSS = str_replace("(({$reviews_types}_tabs_border_color))", $settings['border_color'], $CSS);
            $CSS = str_replace("(({$reviews_types}_tabs_border_hover))", self::borders($settings['border_hover']), $CSS);
            $CSS = str_replace("(({$reviews_types}_tabs_border_hover_style))", $settings['border_hover_style'], $CSS);
            $CSS = str_replace("(({$reviews_types}_tabs_border_hover_color))", $settings['border_hover_color'], $CSS);
            $CSS = str_replace("(({$reviews_types}_tabs_border_radius))", $settings['border_radius'], $CSS);
            $CSS = str_replace("(({$reviews_types}_tabs_hover_border_radius))", $settings['hover_border_radius'], $CSS);
        }

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
