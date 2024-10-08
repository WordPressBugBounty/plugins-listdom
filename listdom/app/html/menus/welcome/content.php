<?php
// no direct access
defined('ABSPATH') || die();

switch($this->tab)
{
    case 'welcome':
        
        $this->include_html_file('menus/welcome/tabs/welcome.php');

        break;

    default:

        $this->include_html_file('menus/welcome/tabs/location.php');
        $this->include_html_file('menus/welcome/tabs/quick.php');
        $this->include_html_file('menus/welcome/tabs/dummy-data.php');
        $this->include_html_file('menus/welcome/tabs/finish.php');

        break;
}
