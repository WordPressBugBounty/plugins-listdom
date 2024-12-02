<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Label Shortcode Class.
 *
 * @class LSD_Shortcodes_Label
 * @version    1.0.0
 */
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
