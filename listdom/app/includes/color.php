<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Color Class.
 *
 * @class LSD_Color
 * @version    1.0.0
 */
class LSD_Color extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
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
        return ($yiq >= 130) ? '#000000' : '#ffffff';
    }

    public static function text_class($color = 'main'): string
    {
        // Get Main or Secondary BG color
        if (in_array($color, ['main', 'secondary']))
        {
            $settings = LSD_Options::settings();

            // Get Main Color
            if ($color == 'main') $bg_color = $settings['dply_main_color'];
            // Get Secondary BG color
            else $bg_color = $settings['dply_secondary_color'];
        }
        // Custom Color
        else $bg_color = $color;

        // Clean it
        $bg_color = trim($bg_color, '# ');

        $r = hexdec(substr($bg_color, 0, 2));
        $g = hexdec(substr($bg_color, 2, 2));
        $b = hexdec(substr($bg_color, 4, 2));

        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return ($yiq >= 130) ? 'lsd-color-black-txt' : 'lsd-color-white-txt';
    }

    public static function brightness($hex, $percent): string
    {
        $hex = ltrim($hex, '#');

        // 6 Character Color
        if (strlen($hex) == 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];

        $hex = array_map('hexdec', str_split($hex, 2));
        foreach ($hex as &$color)
        {
            $adjustableLimit = ($percent < 0) ? $color : 255 - $color;
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
