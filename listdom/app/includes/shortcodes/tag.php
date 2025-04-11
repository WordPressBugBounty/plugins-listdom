<?php

class LSD_Shortcodes_Tag extends LSD_Shortcodes_Taxonomy
{
    // Taxonomy
    protected $TX = LSD_Base::TAX_TAG;

    // Valid Styles
    protected $valid_styles = ['simple', 'clean'];

    public function init()
    {
        add_shortcode('listdom_tag', [$this, 'output']);
    }
}
