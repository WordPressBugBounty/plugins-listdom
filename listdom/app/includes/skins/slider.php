<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Skins Slider Class.
 *
 * @class LSD_Skins_Slider
 * @version    1.0.0
 */
class LSD_Skins_Slider extends LSD_Skins
{
    public $skin = 'slider';
    public $default_style = 'style1';

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
    }

    public function query_meta(): array
    {
        return [['key' => '_thumbnail_id']];
    }
}
