<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Integrations Class.
 *
 * @class LSD_Integrations
 * @version    1.0.0
 */
class LSD_Integrations extends LSD_Base
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
        // Elementor Integration
        $elementor = new LSD_Integrations_Elementor();
        $elementor->init();

        // Block Editor Integration
        $be = new LSD_Integrations_BE();
        $be->init();

        // VC Integration
        $vc = new LSD_Integrations_VC();
        $vc->init();

        // Divi Integration
        $divi = new LSD_Integrations_Divi();
        $divi->init();

        // KC Integration
        $kc = new LSD_Integrations_KC();
        $kc->init();
    }
}
