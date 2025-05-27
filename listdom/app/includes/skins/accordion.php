<?php

class LSD_Skins_Accordion extends LSD_Skins
{
    public $skin = 'accordion';
    public $default_style = 'style1';

    public function init()
    {
        add_action('wp_ajax_lsd_accordion_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_accordion_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_accordion_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_accordion_sort', [$this, 'filter']);
    }

    public function output()
    {
        // Pro is needed
        if (LSD_Base::isLite())
        {
            return LSD_Base::alert(
                LSD_Base::missFeatureMessage(
                    esc_html__('Accordion Skin', 'listdom')
                ),
                'warning'
            );
        }

        return parent::output();
    }
}
