<?php

class LSD_Skins_Table extends LSD_Skins
{
    public $skin = 'table';
    public $default_style = 'style1';

    public function init()
    {
        add_action('wp_ajax_lsd_table_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_table_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_table_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_table_sort', [$this, 'filter']);
    }
}
