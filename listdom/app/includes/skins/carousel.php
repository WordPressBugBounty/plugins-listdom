<?php

class LSD_Skins_Carousel extends LSD_Skins
{
    public $skin = 'carousel';
    public $default_style = 'style1';

    public function init()
    {
    }

    public function query_meta(): array
    {
        $query = parent::query_meta();
        $query[] = ['key' => '_thumbnail_id'];

        return $query;
    }

    public function has_bottom_bar(LSD_Entity_Listing $listing): bool
    {
        switch ($this->style)
        {
            case 'style1':
                return $this->display_share_buttons || $this->has_listing_price($listing);
            case 'style2':
                return $this->display_share_buttons || $this->display_location;
            case 'style5':
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
                    || $this->has_listing_address($listing)
                    || $this->display_contact_info
                    || $this->has_bottom_bar($listing)
                    || $this->display_cta;
            case 'style2':
                return $this->display_title
                    || $this->has_listing_price($listing)
                    || $this->display_categories
                    || $this->display_availability
                    || $this->has_bottom_bar($listing)
                    || $this->display_cta;
            case 'style5':
                return $this->display_categories
                    || $this->display_price_class
                    || $this->display_title
                    || $this->display_description
                    || $this->has_bottom_bar($listing)
                    || $this->display_cta;
        }

        return parent::has_body($listing);
    }
}
