<?php

class LSD_Shortcodes_Label extends LSD_Shortcodes_Taxonomy
{
    // Taxonomy
    protected $TX = LSD_Base::TAX_LABEL;

    // Valid Styles
    protected $valid_styles = ['simple', 'clean'];

    public function init()
    {
        add_shortcode('listdom_label', [$this, 'output']);
    }
}
