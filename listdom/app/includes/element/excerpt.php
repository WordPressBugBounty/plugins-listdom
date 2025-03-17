<?php

class LSD_Element_Excerpt extends LSD_Element_Content
{
    public $key = 'excerpt';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Listing Excerpt', 'listdom');
    }

    public function get($post_id, $limit = 15, $read_more = false)
    {
        return $this->excerpt($post_id, $limit, $read_more);
    }
}
