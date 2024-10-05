<?php
// no direct access
defined('ABSPATH') || die();

if(!class_exists('LSD_Skins_Singlemap')):

/**
 * Listdom Skins Singlemap Class.
 *
 * @class LSD_Skins_Singlemap
 * @version	1.0.0
 */
class LSD_Skins_Singlemap extends LSD_Skins
{
    public $skin = 'singlemap';

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