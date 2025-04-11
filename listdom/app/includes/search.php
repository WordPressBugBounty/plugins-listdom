<?php

class LSD_Search extends LSD_Base
{
    public function init()
    {
        // Search Post Type
        $PType = new LSD_PTypes_Search();
        $PType->init();

        // Search Shortcode
        $Shortcode = new LSD_Shortcodes_Search();
        $Shortcode->init();
    }
}
