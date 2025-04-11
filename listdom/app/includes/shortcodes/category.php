<?php

class LSD_Shortcodes_Category extends LSD_Shortcodes_Taxonomy
{
    // Taxonomy
    protected $TX = LSD_Base::TAX_CATEGORY;

    // Valid Styles
    protected $valid_styles = ['image', 'simple', 'clean', 'carousel'];

    public function init()
    {
        add_shortcode('listdom_category', [$this, 'output']);
    }
}
