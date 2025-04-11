<?php

class LSD_Element_Labels extends LSD_Element
{
    public $key = 'labels';
    public $style = 'tags';
    public $enable_link;
    public $label;

    public function __construct($style = 'tags', $enable_link = true)
    {
        parent::__construct();

        $this->label = esc_html__('Labels', 'listdom');
        $this->style = $style;
        $this->enable_link = $enable_link;
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
        include lsd_template('elements/labels.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }

    public static function styles($label_id): string
    {
        $color = get_term_meta($label_id, 'lsd_color', true);
        $text = LSD_Base::get_text_color($color);

        return 'style="background-color: ' . esc_attr($color) . '; color: ' . esc_attr($text) . ';"';
    }
}
