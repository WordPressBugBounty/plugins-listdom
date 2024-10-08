<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Entity Class.
 *
 * @class LSD_Entity
 * @version    1.0.0
 */
class LSD_Entity extends LSD_Base
{
    public $settings;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        // Listdom Settings
        $this->settings = LSD_Options::settings();
    }
}
