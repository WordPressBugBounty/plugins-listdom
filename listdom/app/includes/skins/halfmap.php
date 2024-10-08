<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Skins Half Map Class.
 *
 * @class LSD_Skins_Halfmap
 * @version    1.0.0
 */
class LSD_Skins_Halfmap extends LSD_Skins
{
    public $skin = 'halfmap';
    public $default_style = 'style1';
    public $map_position;
    public $map_height;
    public $map_limit;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_action('wp_ajax_lsd_halfmap_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_halfmap_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_halfmap_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_halfmap_sort', [$this, 'filter']);
    }

    public function after_start()
    {
        // Map Position
        $this->map_position = isset($this->skin_options['map_position']) && trim($this->skin_options['map_position']) ? $this->skin_options['map_position'] : 'left';

        // Map height
        $this->map_height = isset($this->skin_options['map_height']) && trim($this->skin_options['map_height']) ? (int) $this->skin_options['map_height'] : 500;

        // Map Limit
        $this->map_limit = isset($this->skin_options['maplimit']) && trim($this->skin_options['maplimit']) ? (int) $this->skin_options['maplimit'] : 300;

        // Current View
        $this->default_view = isset($_POST['view']) ? sanitize_text_field($_POST['view']) : $this->default_view;
    }
}
