<?php

class LSD_Element_Price extends LSD_Element
{
    public $key = 'price';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Price', 'listdom');
    }

    public function get($post_id = null, $minimized = false)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/price.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'minimized' => $minimized,
            ]
        );
    }
}
