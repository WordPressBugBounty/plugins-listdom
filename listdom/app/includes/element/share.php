<?php

class LSD_Element_Share extends LSD_Element
{
    public $key = 'share';
    public $label;
    public $layout;
    public $args;

    public function __construct(string $layout = 'full', array $args = [])
    {
        parent::__construct();

        $this->label = esc_html__('Share', 'listdom');
        $this->inline_title = true;
        $this->layout = $layout;
        $this->args = $args;
    }

    public function get($post_id = null)
    {
        if (!LSD_Components::socials()) return '';

        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/share.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
