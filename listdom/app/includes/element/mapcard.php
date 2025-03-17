<?php

class LSD_Element_Mapcard extends LSD_Element
{
    public $key = 'map_card';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Map Card', 'listdom');
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
        include lsd_template('elements/map-card.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
