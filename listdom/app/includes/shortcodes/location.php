<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Location Shortcode Class.
 *
 * @class LSD_Shortcodes_Location
 * @version    1.0.0
 */
class LSD_Shortcodes_Location extends LSD_Shortcodes_Taxonomy
{
    // Taxonomy
    protected $TX = LSD_Base::TAX_LOCATION;

    // Valid Styles
    protected $valid_styles = ['image', 'simple', 'clean'];

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_shortcode('listdom_location', [$this, 'output']);
    }
}
