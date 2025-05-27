<?php

class LSD_Personalize_Single extends LSD_Personalize
{
    /**
     * Generates personalized tab styles and applies them to the CSS.
     *
     * @param string $CSS The raw CSS content. If null, loads the default CSS.
     * @return string Modified CSS with personalized tab styles.
     */
    public static function make(string $CSS): string
    {
        $CSS = LSD_Personalize_Single_Features::make($CSS);
        $CSS = LSD_Personalize_Single_Price::make($CSS);
        $CSS = LSD_Personalize_Single_Breadcrumb::make($CSS);
        return LSD_Personalize_Single_Reviews::make($CSS);
    }
}
