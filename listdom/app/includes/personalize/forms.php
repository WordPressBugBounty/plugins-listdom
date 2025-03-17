<?php

class LSD_Personalize_Forms extends LSD_Personalize
{
    /**
     * Generates personalized form styles and applies them to the CSS.
     *
     * @param string $CSS The raw CSS content. If null, loads the default CSS.
     * @return string Modified CSS with personalized form styles.
     */
    public static function make(string $CSS): string
    {
        $form_types = ['general', 'search'];
        $form_settings = [];

        foreach ($form_types as $type) $form_settings[$type] = self::forms($type);

        return self::replacement($CSS, $form_settings);
    }

    /**
     * Retrieves and sanitizes form input settings for a given form type.
     *
     * @param string $form_type The type of form (e.g., 'form', 'search_form').
     * @return array Sanitized form input settings.
     */
    private static function forms(string $form_type): array
    {
        $form_settings = LSD_Options::customizer("forms.{$form_type}_forms");

        $normal = $form_settings['normal']['_'] ?? [];
        $hover = $form_settings['hover']['_'] ?? [];

        $fields = [
            'bg_color' => sanitize_text_field($normal['input_bg_color']),
            'text_color' => sanitize_text_field($normal['text']),
            'placeholder_color' => sanitize_text_field($normal['placeholder']),
            'border' => self::get_border($normal['border'] ?? []),
            'border_radius' => sanitize_text_field($normal['border']['radius']) . 'px',
            'hover_bg_color' => sanitize_text_field($hover['input_bg_color']),
            'hover_border' => self::get_border($hover['border'] ?? []),
            'hover_border_radius' => sanitize_text_field($hover['border']['radius']) . 'px',

            'family' => sanitize_text_field($normal['typography']['family']),
            'weight' => sanitize_text_field($normal['typography']['weight']),
            'align' => sanitize_text_field($normal['typography']['align']),
            'size' => sanitize_text_field($normal['typography']['size']) . 'px',
            'line_height' => sanitize_text_field($normal['typography']['line_height']) . 'px',
        ];

        if ($form_type === 'search')
        {
            $fields['form_bg_color'] = isset($normal['form_bg_color']) ? sanitize_text_field($normal['form_bg_color']) : '#fff';
            $fields['icon_color'] = isset($normal['icon_color']) ? sanitize_text_field($normal['icon_color']) : '#33c6ff';
        }

        return $fields;
    }

    /**
     * Replaces form input settings placeholders in the CSS with actual values.
     *
     * @param string $CSS The raw CSS content.
     * @param array $forms_settings The form settings to apply.
     * @return string Modified CSS.
     */
    private static function replacement(string $CSS, array $forms_settings): string
    {
        foreach ($forms_settings as $form_type => $settings)
        {
            // Normal state (non-hover settings)
            $CSS = str_replace("(({$form_type}_form_input_bg_color))", $settings['bg_color'], $CSS);
            if ($form_type === 'search') $CSS = str_replace("(({$form_type}_form_bg_color))", $settings['form_bg_color'], $CSS);

            $CSS = str_replace("(({$form_type}_form_input_text_color))", $settings['text_color'], $CSS);
            $CSS = str_replace("(({$form_type}_form_input_placeholder_text_color))", $settings['placeholder_color'], $CSS);
            $CSS = str_replace("(({$form_type}_form_input_border_width))", self::borders($settings['border']), $CSS);
            $CSS = str_replace("(({$form_type}_form_input_border_style))", $settings['border']['style'], $CSS);
            $CSS = str_replace("(({$form_type}_form_input_border_color))", $settings['border']['color'], $CSS);
            $CSS = str_replace("(({$form_type}_form_input_border_radius))", $settings['border_radius'], $CSS);
            if ($form_type === 'search') $CSS = str_replace("(({$form_type}_form_input_icons_color))", $settings['icon_color'], $CSS);

            // Hover state settings
            $CSS = str_replace("(({$form_type}_form_input_hover_bg_color))", $settings['hover_bg_color'], $CSS);
            $CSS = str_replace("(({$form_type}_form_input_hover_border_width))", self::borders($settings['hover_border']), $CSS);
            $CSS = str_replace("(({$form_type}_form_input_hover_border_style))", $settings['hover_border']['style'], $CSS);
            $CSS = str_replace("(({$form_type}_form_input_hover_border_color))", $settings['hover_border']['color'], $CSS);
            $CSS = str_replace("(({$form_type}_form_input_hover_border_radius))", $settings['hover_border_radius'], $CSS);

            // Typography
            $CSS = str_replace("(({$form_type}_form_input_font_family))", $settings['family'], $CSS);
            $CSS = str_replace("(({$form_type}_form_input_font_weight))", $settings['weight'], $CSS);
            $CSS = str_replace("(({$form_type}_form_input_text_align))", $settings['align'], $CSS);
            $CSS = str_replace("(({$form_type}_form_input_font_size))", $settings['size'], $CSS);
            $CSS = str_replace("(({$form_type}_form_input_line_height))", $settings['line_height'], $CSS);
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
