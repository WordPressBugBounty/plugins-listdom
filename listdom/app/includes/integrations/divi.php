<?php
// no direct access
defined('ABSPATH') || die();

if(!class_exists('LSD_Integrations_Divi')):

/**
 * Listdom Integrations Divi Class.
 *
 * @class LSD_Integrations_Divi
 * @version	1.0.0
 */
class LSD_Integrations_Divi extends LSD_Integrations
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
    }
}

endif;