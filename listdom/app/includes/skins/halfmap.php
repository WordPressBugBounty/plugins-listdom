<?php

class LSD_Skins_Halfmap extends LSD_Skins
{
    public $skin = 'halfmap';
    public $default_style = 'style1';
    public $map_limit;

    public function init()
    {
        add_action('wp_ajax_lsd_halfmap_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_halfmap_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_halfmap_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_halfmap_sort', [$this, 'filter']);
    }

    public function after_start()
    {
        // Map Limit
        $this->map_limit = isset($this->skin_options['maplimit']) && trim($this->skin_options['maplimit']) ? (int) $this->skin_options['maplimit'] : 300;

        // Current View
        $this->default_view = isset($_POST['view']) ? sanitize_text_field($_POST['view']) : $this->default_view;
    }

    public function output()
    {
        // No Map Component
        if (!LSD_Components::map()) return '';

        return parent::output();
    }
}
