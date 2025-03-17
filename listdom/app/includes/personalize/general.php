<?php

class LSD_Personalize_General extends LSD_Personalize
{
    public static function make(string $CSS): string
    {
        // Global Settings
        $settings = LSD_Options::customizer('general._');

        // Gaps
        [$tiny, $small, $compact, $base, $wide, $large, $huge] = self::gaps($settings['gaps']['_']['gap'] ?? 8);

        $CSS = str_replace('((gap_tiny))', $tiny.'px', $CSS);
        $CSS = str_replace('((gap_small))', $small.'px', $CSS);
        $CSS = str_replace('((gap_compact))', $compact.'px', $CSS);
        $CSS = str_replace('((gap_base))', $base.'px', $CSS);
        $CSS = str_replace('((gap_wide))', $wide.'px', $CSS);
        $CSS = str_replace('((gap_large))', $large.'px', $CSS);
        $CSS = str_replace('((gap_huge))', $huge.'px', $CSS);

        $CSS = str_replace('((dply_main_color))', $settings['colors']['_']['primary_color'] ?? 'inherit', $CSS);
        return str_replace('((dply_main_font))', $settings['fonts']['_']['primary_font']['family'] ?? 'Poppins', $CSS);
    }

    public static function gaps(int $base): array
    {
        // Ensure base is at least 4
        if ($base < 4) $base = 4;

        // Calculate smaller gaps
        $tiny = ceil($base / 8);
        $small = ceil($base / 4);
        $compact = ceil($base / 2);

        // Calculate larger gaps
        $wide = $base * 2;
        $large = $base * 4;
        $huge = $base * 8;

        // Return array from smallest to largest
        return [$tiny, $small, $compact, $base, $wide, $large, $huge];
    }
}
