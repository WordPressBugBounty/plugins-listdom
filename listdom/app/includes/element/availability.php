<?php

class LSD_Element_Availability extends LSD_Element
{
    public $key = 'availability';
    public $label;
    public $oneday = false;
    public $day = null;

    public function __construct($oneday = false, $day = null)
    {
        parent::__construct();

        $this->label = esc_html__('Working Hours', 'listdom');
        $this->oneday = $oneday;
        $this->day = $day;
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
        include lsd_template('elements/availability.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
