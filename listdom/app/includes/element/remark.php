<?php

class LSD_Element_Remark extends LSD_Element
{
    public $key = 'remark';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Remark / Owner Message', 'listdom');
    }

    public function get($post_id)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        return $this->content(
            get_post_meta($post_id, 'lsd_remark', true),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
