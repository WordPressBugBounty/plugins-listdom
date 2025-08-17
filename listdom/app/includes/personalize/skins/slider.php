<?php

class LSD_Personalize_Skins_Slider extends LSD_Personalize
{
    /**
     * Generates personalized tab styles and applies them to the CSS.
     *
     * @param string $CSS The raw CSS content. If null, loads the default CSS.
     * @return string Modified CSS with personalized tab styles.
     */
    public static function make(string $CSS): string
    {
        $skin_types = ['slider'];
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
        $skin_settings = LSD_Options::customizer("skins.{$skin_type}.arrows");
        $setting = $skin_settings['_'] ?? [];

        return [
            'icon_color' => sanitize_text_field($setting['icon_color']),
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
            $CSS = str_replace("(({$skin_type}_arrows_icon_color))", $settings['icon_color'], $CSS);
        }

        return $CSS;
    }
}
