<?php

class LSD_Personalize_Skins extends LSD_Personalize
{
    /**
     * Applies personalized skin styles to the CSS.
     *
     * @param string $CSS The raw CSS content.
     * @return string Modified CSS with personalized skin styles.
     */
    public static function make(string $CSS): string
    {
        $CSS = LSD_Personalize_Skins_Masonry::make($CSS);
        $CSS = LSD_Personalize_Skins_Slider::make($CSS);
        return LSD_Personalize_Skins_Timeline::make($CSS);
    }
}
