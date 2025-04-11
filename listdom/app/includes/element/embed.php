<?php

class LSD_Element_Embed extends LSD_Element
{
    public $key = 'embed';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Embed', 'listdom');
        $this->has_title_settings = false;
        $this->pro_needed = true;
    }

    public function get($post_id = null)
    {
        // Disabled in Lite
        if ($this->isLite()) return false;

        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/embeds.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}

