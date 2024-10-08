<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Addon Resource Class.
 *
 * @class LSD_API_Resources_Addon
 * @version    1.0.0
 */
class LSD_API_Resources_Addon extends LSD_API_Resource
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function all()
    {
        return apply_filters('lsd_api_resource_addon', LSD_Base::addons());
    }
}
