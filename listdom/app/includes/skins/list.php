<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Skins List Class.
 *
 * @class LSD_Skins_List
 * @version    1.0.0
 */
class LSD_Skins_List extends LSD_Skins
{
    public $skin = 'list';
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
        add_action('wp_ajax_lsd_list_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_list_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_list_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_list_sort', [$this, 'filter']);
    }
}
