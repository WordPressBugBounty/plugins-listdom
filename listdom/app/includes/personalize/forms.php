<?php

class LSD_Personalize_Forms extends LSD_Personalize
{
    public static function make(string $CSS): string
    {
        $CSS = self::general($CSS);
        $CSS = self::search($CSS);

        return self::auth($CSS);
    }

    private static function general(string $CSS): string
    {
        $config = LSD_Options::customizer('forms.general_forms');

        $normal = $config['inputs']['normal'] ?? [];
        $hover = $config['inputs']['hover'] ?? [];

        // Normal state (non-hover settings)
        $CSS = str_replace("((general_form_input_bg_color))", sanitize_text_field($normal['input_bg_color'] ?: 'inherit'), $CSS);

        $CSS = str_replace("((general_form_input_text_color))", sanitize_text_field($normal['text'] ?: 'inherit'), $CSS);
        $CSS = str_replace("((general_form_input_placeholder_text_color))", sanitize_text_field($normal['placeholder'] ?: 'inherit'), $CSS);
        $CSS = str_replace("((general_form_input_border_width))", self::borders($normal['border'] ?? []), $CSS);
        $CSS = str_replace("((general_form_input_border_style))", sanitize_text_field($normal['border']['style']), $CSS);
        $CSS = str_replace("((general_form_input_border_color))", sanitize_text_field($normal['border']['color']), $CSS);
        $CSS = str_replace("((general_form_input_border_radius))", sanitize_text_field($normal['border']['radius']) . 'px', $CSS);

        // Hover state settings
        $CSS = str_replace("((general_form_input_hover_bg_color))", sanitize_text_field($hover['input_bg_color'] ?: 'inherit'), $CSS);
        $CSS = str_replace("((general_form_input_hover_border_width))", self::borders($hover['border'] ?? []), $CSS);
        $CSS = str_replace("((general_form_input_hover_border_style))", sanitize_text_field($hover['border']['style']), $CSS);
        $CSS = str_replace("((general_form_input_hover_border_color))", sanitize_text_field($hover['border']['color']), $CSS);
        $CSS = str_replace("((general_form_input_hover_border_radius))", sanitize_text_field($hover['border']['radius']) . 'px', $CSS);

        // Typography
        $CSS = str_replace("((general_form_input_font_family))", self::font_family($normal['typography']['family'] ?? ''), $CSS);
        $CSS = str_replace("((general_form_input_font_weight))", sanitize_text_field($normal['typography']['weight']), $CSS);
        $CSS = str_replace("((general_form_input_text_align))", sanitize_text_field($normal['typography']['align']), $CSS);
        $CSS = str_replace("((general_form_input_font_size))", self::unit_number($normal['typography']['size']), $CSS);

        return str_replace("((general_form_input_line_height))", self::unit_number($normal['typography']['line_height']), $CSS);
    }

    private static function search(string $CSS): string
    {
        $config = LSD_Options::customizer('forms.search_forms');

        $form = $config['form']['_'] ?? [];
        $icon = $config['icon']['_'] ?? [];
        $normal = $config['inputs']['normal'] ?? [];
        $hover = $config['inputs']['hover'] ?? [];

        $CSS = str_replace("((search_form_bg_color))", sanitize_text_field($form['form_bg_color'] ?: 'inherit'), $CSS);
        $CSS = str_replace("((search_form_input_icons_color))", sanitize_text_field($icon['icon_color'] ?: 'inherit'), $CSS);

        // Normal state (non-hover settings)
        $CSS = str_replace("((search_form_input_bg_color))", sanitize_text_field($normal['input_bg_color'] ?: 'inherit'), $CSS);

        $CSS = str_replace("((search_form_input_text_color))", sanitize_text_field($normal['text'] ?: 'inherit'), $CSS);
        $CSS = str_replace("((search_form_input_placeholder_text_color))", sanitize_text_field($normal['placeholder'] ?: 'inherit'), $CSS);
        $CSS = str_replace("((search_form_input_border_width))", self::borders($normal['border'] ?? []), $CSS);
        $CSS = str_replace("((search_form_input_border_style))", sanitize_text_field($normal['border']['style']), $CSS);
        $CSS = str_replace("((search_form_input_border_color))", sanitize_text_field($normal['border']['color']), $CSS);
        $CSS = str_replace("((search_form_input_border_radius))", sanitize_text_field($normal['border']['radius']) . 'px', $CSS);

        // Hover state settings
        $CSS = str_replace("((search_form_input_hover_bg_color))", sanitize_text_field($hover['input_bg_color'] ?: 'inherit'), $CSS);
        $CSS = str_replace("((search_form_input_hover_border_width))", self::borders($hover['border'] ?? []), $CSS);
        $CSS = str_replace("((search_form_input_hover_border_style))", sanitize_text_field($hover['border']['style']), $CSS);
        $CSS = str_replace("((search_form_input_hover_border_color))", sanitize_text_field($hover['border']['color']), $CSS);
        $CSS = str_replace("((search_form_input_hover_border_radius))", sanitize_text_field($hover['border']['radius']) . 'px', $CSS);

        // Typography
        $CSS = str_replace("((search_form_input_font_family))", self::font_family($normal['typography']['family'] ?? ''), $CSS);
        $CSS = str_replace("((search_form_input_font_weight))", sanitize_text_field($normal['typography']['weight']), $CSS);
        $CSS = str_replace("((search_form_input_text_align))", sanitize_text_field($normal['typography']['align']), $CSS);
        $CSS = str_replace("((search_form_input_font_size))", self::unit_number($normal['typography']['size']), $CSS);

        return str_replace("((search_form_input_line_height))", self::unit_number($normal['typography']['line_height']), $CSS);
    }

    private static function auth(string $CSS): string
    {
        $config = LSD_Options::customizer('forms.auth_forms');

        $box = $config['box']['_'] ?? [];
        $labels = $config['labels']['_'] ?? [];

        // Box
        $CSS = str_replace('((auth_forms_box_bg))', sanitize_text_field($box['bg'] ?: 'transparent'), $CSS);
        $CSS = str_replace('((auth_forms_box_padding))', self::paddings($box['padding'] ?? []), $CSS);
        $CSS = str_replace('((auth_forms_box_border))', self::borders($box['border'] ?? []), $CSS);
        $CSS = str_replace('((auth_forms_box_border_style))', sanitize_text_field($box['border']['style'] ?: 'none'), $CSS);
        $CSS = str_replace('((auth_forms_box_border_color))', sanitize_text_field($box['border']['color'] ?: 'transparent'), $CSS);
        $CSS = str_replace('((auth_forms_box_border_radius))', sanitize_text_field($box['border']['radius'] ?: 0) . 'px', $CSS);

        // Tabs
        foreach (['normal', 'hover', 'active'] as $state)
        {
            $tab = $config['tabs'][$state] ?? [];

            $CSS = str_replace('((auth_forms_tabs_' . $state . '_bg1))', sanitize_text_field($tab['bg1'] ?: 'transparent'), $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_bg2))', sanitize_text_field($tab['bg2'] ?: 'transparent'), $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_text))', sanitize_text_field($tab['text'] ?: 'inherit'), $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_border))', self::borders($tab['border'] ?? []), $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_border_style))', sanitize_text_field($tab['border']['style'] ?: 'none'), $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_border_color))', sanitize_text_field($tab['border']['color'] ?: 'inherit'), $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_border_radius))', sanitize_text_field($tab['border']['radius'] ?: 0) . 'px', $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_padding))', self::paddings($tab['padding'] ?? []), $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_font_family))', self::font_family($tab['typography']['family'] ?? ''), $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_font_weight))', sanitize_text_field($tab['typography']['weight'] ?: 'inherit'), $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_text_align))', sanitize_text_field($tab['typography']['align'] ?: 'inherit'), $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_font_size))', self::unit_number($tab['typography']['size'] ?? '', 'px', 'inherit'), $CSS);
            $CSS = str_replace('((auth_forms_tabs_' . $state . '_line_height))', self::unit_number($tab['typography']['line_height'] ?? '', 'px', 'inherit'), $CSS);
        }

        // Labels
        $CSS = str_replace('((auth_forms_labels_color))', sanitize_text_field($labels['text'] ?: 'inherit'), $CSS);
        $CSS = str_replace('((auth_forms_labels_font_family))', self::font_family($labels['typography']['family'] ?? ''), $CSS);
        $CSS = str_replace('((auth_forms_labels_font_weight))', sanitize_text_field($labels['typography']['weight'] ?: 'inherit'), $CSS);
        $CSS = str_replace('((auth_forms_labels_text_align))', sanitize_text_field($labels['typography']['align'] ?: 'inherit'), $CSS);
        $CSS = str_replace('((auth_forms_labels_font_size))', self::unit_number($labels['typography']['size'] ?? '', 'px', 'inherit'), $CSS);

        return str_replace('((auth_forms_labels_line_height))', self::unit_number($labels['typography']['line_height'] ?? '', 'px', 'inherit'), $CSS);
    }
}
