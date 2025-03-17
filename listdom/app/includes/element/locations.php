<?php

class LSD_Element_Locations extends LSD_Element
{
    public $key = 'locations';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Locations', 'listdom');
        $this->has_title_settings = false;
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
        include lsd_template('elements/locations.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
