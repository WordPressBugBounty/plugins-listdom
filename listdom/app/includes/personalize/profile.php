<?php

class LSD_Personalize_Profile extends LSD_Personalize
{
    public static function make(string $CSS): string
    {
        $CSS = self::profile($CSS);

        return self::users($CSS);
    }

    private static function profile(string $CSS): string
    {
        $config = LSD_Options::customizer('profile.profile');

        // Section and Title
        $section = $config['section']['container'] ?? [];
        $title = $config['section']['title'] ?? [];

        $CSS = self::section_styles($CSS, 'profile_container', $section);
        $CSS = self::text_styles($CSS, 'profile_title', $title);

        // Profile Content
        $contents = ['name', 'job_title', 'count', 'contact_info', 'bio'];
        foreach ($contents as $item)
        {
            $CSS = self::text_styles($CSS, "profile_" . $item, $config['content'][$item] ?? []);
        }

        return $CSS;
    }

    private static function users(string $CSS): string
    {
        $config = LSD_Options::customizer('profile.users');

        // User Profiles
        $cards = $config['cards']['item'] ?? [];
        $CSS = self::section_styles($CSS, 'user_profile_container', $cards);

        $userContents = ['name', 'job_title', 'contact_info', 'bio'];
        foreach ($userContents as $item)
        {
            $CSS = self::text_styles($CSS, "user_profile_" . $item, $config['cards'][$item] ?? []);
        }

        return $CSS;
    }

    private static function section_styles(string $CSS, string $prefix, array $data): string
    {
        $CSS = str_replace("(($prefix" . "_bg_color))", sanitize_text_field($data['container_bg'] ?? ''), $CSS);
        $CSS = str_replace("(($prefix" . "_border))", self::borders($data['border'] ?? []), $CSS);
        $CSS = str_replace("(($prefix" . "_border_style))", sanitize_text_field($data['border']['style'] ?? ''), $CSS);
        $CSS = str_replace("(($prefix" . "_border_color))", sanitize_text_field($data['border']['color'] ?? ''), $CSS);
        $CSS = str_replace("(($prefix" . "_border_radius))", sanitize_text_field($data['border']['radius'] ?? '') . 'px', $CSS);

        return str_replace("(($prefix" . "_padding))", self::paddings($data['padding'] ?? []), $CSS);
    }

    private static function text_styles(string $CSS, string $prefix, array $data): string
    {
        $typography = $data['typography'] ?? [];

        $CSS = str_replace("(($prefix" . "_text_color))", sanitize_text_field($data['text_color'] ?? ''), $CSS);
        $CSS = str_replace("(($prefix" . "_font_family))", self::font_family($typography['family'] ?? ''), $CSS);
        $CSS = str_replace("(($prefix" . "_font_weight))", sanitize_text_field($typography['weight'] ?? ''), $CSS);
        $CSS = str_replace("(($prefix" . "_text_align))", sanitize_text_field($typography['align'] ?? ''), $CSS);
        $CSS = str_replace("(($prefix" . "_font_size))", isset($typography['size']) ? sanitize_text_field($typography['size']) . 'px' : '', $CSS);
        $CSS = str_replace("(($prefix" . "_line_height))", isset($typography['line_height']) ? sanitize_text_field($typography['line_height']) . 'px' : '', $CSS);

        if (isset($data['icon_color']))
        {
            $CSS = str_replace("(($prefix" . "_icon_color))", sanitize_text_field($data['icon_color']), $CSS);
        }

        if (isset($data['socials_icon_color']))
        {
            $CSS = str_replace("(($prefix" . "_socials_icon_color))", sanitize_text_field($data['socials_icon_color']), $CSS);
        }

        return $CSS;
    }
}
