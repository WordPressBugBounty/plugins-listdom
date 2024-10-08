<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Skins ListGrid Class.
 *
 * @class LSD_Skins_Listgrid
 * @version    1.0.0
 */
class LSD_Skins_Listgrid extends LSD_Skins
{
    public $skin = 'listgrid';
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
        add_action('wp_ajax_lsd_listgrid_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_listgrid_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_listgrid_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_listgrid_sort', [$this, 'filter']);
    }

    public function after_start()
    {
        // Current View
        $this->default_view = isset($_POST['view']) ? sanitize_text_field($_POST['view']) : $this->default_view;
    }
}
