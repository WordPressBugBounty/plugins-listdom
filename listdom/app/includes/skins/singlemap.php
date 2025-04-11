<?php

class LSD_Skins_Singlemap extends LSD_Skins
{
    public $skin = 'singlemap';
    public $sidebar = false;
    public $sidebar_search = true;
    public $sidebar_state = 'open_optional';
    public $tablet_limit = 12;

    public function init()
    {
    }

    public function after_start()
    {
        // Sidebar Options
        $this->sidebar = isset($this->skin_options['sidebar']) && $this->skin_options['sidebar'];
        $this->sidebar_search = !isset($this->skin_options['sidebar_search']) || $this->skin_options['sidebar_search'];

        $this->tablet_limit = isset($this->skin_options['sidebar_tablet_limit']) && $this->skin_options['sidebar_tablet_limit']
            ? $this->skin_options['sidebar_tablet_limit']
            : 12;

        $this->sidebar_state = isset($this->skin_options['sidebar_default_state']) && $this->skin_options['sidebar_default_state']
            ? $this->skin_options['sidebar_default_state']
            : 'open_optional';
    }
}
