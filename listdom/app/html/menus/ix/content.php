<?php
// no direct access
defined('ABSPATH') || die();

switch($this->tab)
{
    case 'json':

        $this->include_html_file('menus/ix/tabs/json.php');
        break;

    case 'csv':

        $this->include_html_file('menus/ix/tabs/csv.php');
        break;

    case 'dummy-data':

        $this->include_html_file('menus/ix/tabs/dummy-data.php');
        break;

    case $this->tab === 'excel' && !class_exists(\LSDPACEXL\Base::class):

        $this->include_html_file('menus/ix/tabs/excel.php');
        break;

    default:

        /**
         * Third party plugins
         */
        do_action('lsd_admin_ix_contents', $this->tab);

        break;
}
