<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Search Class.
 *
 * @class LSD_Search
 * @version    1.0.0
 */
class LSD_Search extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

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
