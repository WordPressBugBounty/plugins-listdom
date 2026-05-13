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

    public function has_bottom_bar(LSD_Entity_Listing $listing): bool
    {
        return $this->display_review_stars || $this->display_share_buttons;
    }

    public function has_body(LSD_Entity_Listing $listing): bool
    {
        return $this->display_labels
            || $this->display_categories
            || $this->display_favorite_icon
            || $this->display_compare_icon
            || $this->display_title
            || $this->has_bottom_bar($listing)
            || $this->display_cta;
    }
}
