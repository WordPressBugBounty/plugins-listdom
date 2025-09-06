<?php

class LSD_Skins_Gallery extends LSD_Skins
{
    public $skin = 'gallery';
    public $default_style = 'style1';

    public function init()
    {
        add_action('wp_ajax_lsd_gallery_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_gallery_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_gallery_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_gallery_sort', [$this, 'filter']);
    }

    public function output()
    {
        // Pro is needed
        if (LSD_Base::isLite())
        {
            return LSD_Base::alert(
                LSD_Base::missFeatureMessage(
                    esc_html__('Gallery Skin', 'listdom')
                ),
                'warning'
            );
        }

        return parent::output();
    }
}
