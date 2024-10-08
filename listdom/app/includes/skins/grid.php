<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Skins Grid Class.
 *
 * @class LSD_Skins_Grid
 * @version    1.0.0
 */
class LSD_Skins_Grid extends LSD_Skins
{
    public $skin = 'grid';
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
        add_action('wp_ajax_lsd_grid_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_grid_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_grid_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_grid_sort', [$this, 'filter']);
    }
}
