<?php

class LSD_Shortcodes_Location extends LSD_Shortcodes_Taxonomy
{
    // Taxonomy
    protected $TX = LSD_Base::TAX_LOCATION;

    // Valid Styles
    protected $valid_styles = ['image', 'simple', 'clean'];

    public function init()
    {
        add_shortcode('listdom_location', [$this, 'output']);
    }
}
