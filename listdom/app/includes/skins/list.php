<?php

class LSD_Skins_List extends LSD_Skins
{
    public $skin = 'list';
    public $default_style = 'style1';

    public function init()
    {
        add_action('wp_ajax_lsd_list_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_list_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_list_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_list_sort', [$this, 'filter']);
    }

    public function has_bottom_bar(LSD_Entity_Listing $listing): bool
    {
        switch ($this->style)
        {
            case 'style1':
            case 'style4':
                return $this->display_share_buttons || $this->has_listing_price($listing);
            case 'style2':
                return $this->display_share_buttons || $this->display_location;
            case 'style3':
                return $this->display_review_stars || $this->has_listing_address($listing);
        }

        return parent::has_bottom_bar($listing);
    }

    public function has_body(LSD_Entity_Listing $listing): bool
    {
        switch ($this->style)
        {
            case 'style1':
                return $this->display_title
                    || $this->display_favorite_icon
                    || $this->display_compare_icon
                    || $this->has_listing_address($listing)
                    || $this->display_contact_info
                    || $this->has_bottom_bar($listing)
                    || $this->display_cta;
            case 'style2':
                return $this->display_description
                    || $this->display_categories
                    || $this->display_availability
                    || $this->display_title
                    || $this->display_favorite_icon
                    || $this->display_compare_icon
                    || $this->has_listing_price($listing)
                    || $this->has_bottom_bar($listing)
                    || $this->display_cta;
            case 'style3':
                return $this->display_availability
                    || $this->display_categories
                    || $this->display_price_class
                    || $this->display_title
                    || $this->display_description
                    || $this->has_bottom_bar($listing)
                    || $this->display_cta;
            case 'style4':
                return $this->display_categories
                    || $this->display_title
                    || $this->display_favorite_icon
                    || $this->display_compare_icon
                    || $this->display_contact_info
                    || $this->has_bottom_bar($listing)
                    || $this->display_cta;
        }

        return parent::has_body($listing);
    }
}
