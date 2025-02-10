<?php
// no direct access
defined('ABSPATH') || die();

class LSD_Element_Title extends LSD_Element
{
    public $key = 'title';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Listing Title', 'listdom');
        $this->has_title_settings = false;
    }

    public function get($post_id)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        return $this->content(
            get_the_title($post_id),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
