<?php

class LSD_Element_Infowindow extends LSD_Element
{
    public $key = 'infowindow';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Infowindow', 'listdom');
    }

    public function get($post_id = null)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/infowindow.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
