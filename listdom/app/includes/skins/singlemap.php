<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Skins Singlemap Class.
 *
 * @class LSD_Skins_Singlemap
 * @version    1.0.0
 */
class LSD_Skins_Singlemap extends LSD_Skins
{
    public $skin = 'singlemap';
    public $sidebar = false;
    public $sidebar_search = true;
    public $sidebar_state = 'open_optional';

    public function init()
    {
    }

    public function after_start()
    {
        // Sidebar Options
        $this->sidebar = isset($this->skin_options['sidebar']) && $this->skin_options['sidebar'];
        $this->sidebar_search = !isset($this->skin_options['sidebar_search']) || $this->skin_options['sidebar_search'];
        $this->sidebar_state = isset($this->skin_options['default_state']) && $this->skin_options['default_state']
            ? $this->skin_options['default_state']
            : 'open_optional';
    }
}
