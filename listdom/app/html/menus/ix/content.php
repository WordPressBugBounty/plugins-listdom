<?php
// no direct access
defined('ABSPATH') || die();

switch($this->tab)
{
    case 'json':

        $this->include_html_file('menus/ix/tabs/json.php');
        break;

    default:

        /**
         * Third party plugins
         */
        do_action('lsd_admin_ix_contents', $this->tab);

        break;
}