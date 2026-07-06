<?php

class LSD_Color extends LSD_Base
{
    public static function with_opacity(string $color, float $opacity = 0.1): string
    {
        $hex = trim($color);
        if (!preg_match('/^#?([a-f0-9]{6})$/i', $hex, $matches)) return '';

        $hex = strtolower($matches[1]);

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $saturation = $max > 0 ? (($max - $min) / $max) : 0;

        // Only darken very light neutral/pale colors.
        // Strong colors like orange, yellow, and red should keep their original hue.
        if ($yiq >= 180 && $saturation <= 0.25)
        {
            $r = max(0, (int) round($r * 0.25));
            $g = max(0, (int) round($g * 0.25));
            $b = max(0, (int) round($b * 0.25));

            $opacity = max($opacity, 0.8);
        }

        $opacity = max(0, min(1, $opacity));
        $hex = sprintf('%02x%02x%02x', $r, $g, $b);
        $alpha = str_pad(strtoupper(dechex((int) round($opacity * 255))), 2, '0', STR_PAD_LEFT);

        return '#' . $hex . $alpha;
    }

    public static function text_color($bg_color = null): string
    {
        // Get Main BG color
        if (is_null($bg_color))
        {
            $settings = LSD_Options::settings();
            $bg_color = $settings['dply_main_color'];
        }

        // Default Black Color
        if (!$bg_color) return '#000000';

        // Clean it
        $bg_color = trim($bg_color, '# ');

        $r = hexdec(substr($bg_color, 0, 2));
        $g = hexdec(substr($bg_color, 2, 2));
        $b = hexdec(substr($bg_color, 4, 2));

        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        return $yiq >= 130 ? '#000000' : '#ffffff';
    }

    public static function text_class($color = 'main'): string
    {
        // Get Main or Secondary BG color
        if ($color === 'main')
        {
            $settings = LSD_Options::settings();
            $bg_color = $settings['dply_main_color'];
        }
        // Custom Color
        else $bg_color = $color;

        // Clean it
        $bg_color = trim($bg_color, '# ');

        $r = hexdec(substr($bg_color, 0, 2));
        $g = hexdec(substr($bg_color, 2, 2));
        $b = hexdec(substr($bg_color, 4, 2));

        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return $yiq >= 130 ? 'lsd-color-black-txt' : 'lsd-color-white-txt';
    }

    public static function brightness($hex, $percent): string
    {
        $hex = ltrim($hex, '#');

        // 6 Character Color
        if (strlen($hex) == 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];

        $hex = array_map('hexdec', str_split($hex, 2));
        foreach ($hex as &$color)
        {
            $adjustableLimit = $percent < 0 ? $color : 255 - $color;
            $adjustAmount = ceil($adjustableLimit * $percent);

            $color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
        }

        return '#' . implode($hex);
    }

    public static function lighter($color, $percent): string
    {
        return self::brightness($color, ($percent / 100));
    }

    public static function darker($color, $percent): string
    {
        return self::brightness($color, -($percent / 100));
    }
}
