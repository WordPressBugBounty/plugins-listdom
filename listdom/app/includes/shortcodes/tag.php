<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Tags Shortcode Class.
 *
 * @class LSD_Shortcodes_Tags
 * @version    1.0.0
 */
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
