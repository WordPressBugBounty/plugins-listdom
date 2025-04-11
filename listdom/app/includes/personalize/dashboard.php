<?php

class LSD_Personalize_Dashboard extends LSD_Personalize
{
    public static function make(string $CSS): string
    {
        return self::menu($CSS);
    }

    private static function menu(string $CSS): string
    {
        $config = LSD_Options::customizer('dashboard.menu');

        $normal = $config['normal']['_'] ?? [];
        $hover = $config['hover']['_'] ?? [];

        // Dashboard Container Settings
        $CSS = str_replace("((dashboard_container_bg_color))", sanitize_text_field($normal['container_bg']), $CSS);

        // Dashboard Background and Text Color
        $CSS = str_replace("((dashboard_bg_color))", sanitize_text_field($normal['bg_color']), $CSS);
        $CSS = str_replace("((dashboard_text_color))", sanitize_text_field($normal['text_color']), $CSS);

        // Dashboard Icon Color
        $CSS = str_replace("((dashboard_icon_color))", sanitize_text_field($normal['icon_color']), $CSS);

        // Dashboard Hover Settings
        $CSS = str_replace("((dashboard_hover_bg_color))", sanitize_text_field($hover['bg_color']), $CSS);
        $CSS = str_replace("((dashboard_hover_text_color))", sanitize_text_field($hover['text_color']), $CSS);

        // Dashboard Border Settings
        $CSS = str_replace("((dashboard_border))", self::borders($normal['border'] ?? []), $CSS);
        $CSS = str_replace("((dashboard_border_style))", $normal['border']['style'], $CSS);
        $CSS = str_replace("((dashboard_border_color))", $normal['border']['color'], $CSS);
        $CSS = str_replace("((dashboard_border_hover))", self::borders($hover['border'] ?? []), $CSS);
        $CSS = str_replace("((dashboard_border_hover_style))", $hover['border']['style'], $CSS);
        $CSS = str_replace("((dashboard_border_hover_color))", $hover['border']['color'], $CSS);

        // Dashboard Border Radius
        $CSS = str_replace("((dashboard_border_radius))", sanitize_text_field($normal['border']['radius']) . 'px', $CSS);
        $CSS = str_replace("((dashboard_hover_border_radius))", sanitize_text_field($hover['border']['radius']) . 'px', $CSS);

        // Typography
        $CSS = str_replace("((dashboard_font_family))", self::font_family($normal['typography']['family'] ?? ''), $CSS);
        $CSS = str_replace("((dashboard_font_weight))", sanitize_text_field($normal['typography']['weight']), $CSS);
        $CSS = str_replace("((dashboard_text_align))", sanitize_text_field($normal['typography']['align']), $CSS);
        $CSS = str_replace("((dashboard_font_size))", sanitize_text_field($normal['typography']['size']) . 'px', $CSS);

        return str_replace("((dashboard_line_height))", sanitize_text_field($normal['typography']['line_height']) . 'px', $CSS);
    }
}
