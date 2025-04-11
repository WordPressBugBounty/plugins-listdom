<?php

class LSD_Menus_Dashboard extends LSD_Menus
{
    /**
     * @var string
     */
    public $tab;

    public function output()
    {
        // Get the current tab
        $this->tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';

        // Generate output
        $this->include_html_file('menus/dashboard/tpl.php');
    }
}
