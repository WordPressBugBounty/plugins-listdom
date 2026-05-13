<?php

class LSD_Skins_Mosaic extends LSD_Skins
{
    public $skin = 'mosaic';
    public $default_style = 'style1';

    public function init()
    {
        add_action('wp_ajax_lsd_mosaic_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_mosaic_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_mosaic_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_mosaic_sort', [$this, 'filter']);
    }

    public function output()
    {
        // Pro is needed
        if (LSD_Base::isLite())
        {
            return LSD_Base::alert(
                LSD_Base::missFeatureMessage(
                    esc_html__('Mosaic Skin', 'listdom')
                ),
                'warning'
            );
        }

        return parent::output();
    }

    public function has_bottom_bar(LSD_Entity_Listing $listing): bool
    {
        return $this->has_listing_price($listing) || $this->display_share_buttons;
    }

    public function has_body(LSD_Entity_Listing $listing): bool
    {
        if (is_numeric($this->style)) return true;

        return $this->display_categories
            || $this->display_title
            || $this->display_labels
            || $this->display_contact_info
            || $this->has_bottom_bar($listing)
            || $this->display_review_stars
            || $this->display_cta;
    }
}
