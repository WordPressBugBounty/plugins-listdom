<?php
// no direct access
defined('ABSPATH') || die();

switch($this->tab)
{
    case 'custom-styles':

        $this->include_html_file('menus/settings/tabs/styles.php');
        break;

    case 'details-page':

        $this->include_html_file('menus/settings/tabs/details-page.php');
        break;

    case 'archive-pages':

        $this->include_html_file('menus/settings/tabs/archive-pages.php');
        break;

    case 'slugs':

        $this->include_html_file('menus/settings/tabs/slugs.php');
        break;

    case 'social-networks':

        $this->include_html_file('menus/settings/tabs/social-networks.php');
        break;

    case 'addons':

        $this->include_html_file('menus/settings/tabs/addons.php');
        break;

    case 'api':

        $this->include_html_file('menus/settings/tabs/api.php');
        break;

    case 'auth':

        $this->include_html_file('menus/settings/tabs/auth.php');
        break;

    default:

        $this->include_html_file('menus/settings/tabs/general.php');
        break;
}
