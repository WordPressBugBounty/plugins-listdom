<?php

class LSD_Skins_Grid extends LSD_Skins
{
    public $skin = 'grid';
    public $default_style = 'style3';

    public function init()
    {
        add_action('wp_ajax_lsd_grid_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_grid_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_grid_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_grid_sort', [$this, 'filter']);
    }
}
