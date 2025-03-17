<?php

class LSD_Personalize_Single_Features extends LSD_Personalize_Single
{
    /**
     * {@inheritDoc}
     */
    public static function make(string $CSS): string
    {
        $feature_types = ['features'];
        $feature_settings = [];

        foreach ($feature_types as $type) $feature_settings[$type] = self::feature($type);
        return self::replacement($CSS, $feature_settings);
    }

    /**
     * Retrieves and sanitizes form input settings for a given form type.
     *
     * @param string $feature_types The type of form (e.g., 'form', 'search_form').
     * @return array Sanitized form input settings.
     */
    public static function feature(string $feature_types): array
    {
        $feature_settings = LSD_Options::customizer("single.{$feature_types}");

        return [
            'bg_color' => isset($feature_settings['icons']['_']['bg_color']) ? sanitize_text_field($feature_settings['icons']['_']['bg_color']) : '',
            'text_color' => isset($feature_settings['icons']['_']['text_color']) ? sanitize_text_field($feature_settings['icons']['_']['text_color']) : '',
        ];
    }

    /**
     * Replaces the placeholders in the CSS with the personalized button styles.
     *
     * @param string $CSS The raw CSS content to be modified.
     * @param array $feature_settings The array of button styles to apply to the CSS.
     * @return string The modified CSS with the button styles replaced.
     */
    private static function replacement(string $CSS, array $feature_settings): string
    {
        foreach ($feature_settings as $feature_types => $settings)
        {
            $CSS = str_replace("(({$feature_types}_icons_bg_color))", $settings['bg_color'], $CSS);
            $CSS = str_replace("(({$feature_types}_icons_text_color))", $settings['text_color'], $CSS);
        }

        return $CSS;
    }
}
