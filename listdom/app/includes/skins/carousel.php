<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Skins Carousel Class.
 *
 * @class LSD_Skins_Carousel
 * @version    1.0.0
 */
class LSD_Skins_Carousel extends LSD_Skins
{
    public $skin = 'carousel';
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
