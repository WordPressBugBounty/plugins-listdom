<?php

class LSD_Personalize_Skins_Timeline extends LSD_Personalize
{
    public static function make(string $CSS): string
    {
        $connector = LSD_Options::customizer('skins.timeline.connector._');

        // Keep the original connector and marker colors until one is selected.
        $color = sanitize_hex_color($connector['color'] ?? '') ?: 'initial';
        return str_replace('((timeline_color))', $color, $CSS);
    }
}
