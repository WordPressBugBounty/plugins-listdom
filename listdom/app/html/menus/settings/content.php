<?php
// no direct access
defined('ABSPATH') || die();

switch ($this->tab)
{
    case 'single-listing':

        $this->include_html_file('menus/settings/tabs/single-listing.php');
        break;

    case 'frontend-dashboard':

        $this->include_html_file('menus/settings/tabs/frontend-dashboard.php');
        break;

    case 'customizer':

        $this->include_html_file('menus/settings/tabs/customizer.php');
        break;

    case 'slugs':

        $this->include_html_file('menus/settings/tabs/slugs.php');
        break;

    case 'addons':

        $this->include_html_file('menus/settings/tabs/addons.php');
        break;

    case 'ai':

        $this->include_html_file('menus/settings/tabs/ai.php');
        break;

    case 'api':

        $this->include_html_file('menus/settings/tabs/api.php');
        break;

    case 'auth':

        $this->include_html_file('menus/settings/tabs/auth.php');
        break;

    case 'advanced':

        $this->include_html_file('menus/settings/tabs/advanced.php');
        break;

    default:

        $this->include_html_file('menus/settings/tabs/general.php');
        break;
}
