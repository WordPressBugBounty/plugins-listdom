<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Features Shortcode Class.
 *
 * @class LSD_Shortcodes_Features
 * @version    1.0.0
 */
class LSD_Shortcodes_Feature extends LSD_Shortcodes_Taxonomy
{
    // Taxonomy
    protected $TX = LSD_Base::TAX_FEATURE;

    // Valid Styles
    protected $valid_styles = ['simple', 'clean'];

    public function init()
    {
        add_shortcode('listdom_feature', [$this, 'output']);
    }
}
