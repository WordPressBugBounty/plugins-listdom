<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Skins Table Class.
 *
 * @class LSD_Skins_Table
 * @version    1.0.0
 */
class LSD_Skins_Table extends LSD_Skins
{
    public $skin = 'table';
    public $default_style = 'style1';

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_action('wp_ajax_lsd_table_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_table_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_table_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_table_sort', [$this, 'filter']);
    }
}
