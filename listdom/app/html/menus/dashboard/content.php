<?php
// no direct access
defined('ABSPATH') || die();

switch($this->tab)
{
    case 'dashboard':
        
        $this->include_html_file('menus/dashboard/tabs/dashboard.php');
        break;

    default:

        /**
         * For showing new tabs in admin dashboard by third party plugins
         */
        do_action('lsd_admin_dashboard_contents', $this->tab);
        break;
}