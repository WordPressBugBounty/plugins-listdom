<?php

class LSD_Element_Tags extends LSD_Element
{
    public $key = 'tags';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Tags', 'listdom');
        $this->inline_title = true;
    }

    public function get($post_id = null, $enable_link = true)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        $terms = wp_get_post_terms($post_id, LSD_Base::TAX_TAG);
        if (!count($terms)) return '';

        $output = '<ul>';
        foreach ($terms as $term)
        {
            $link = $enable_link ? esc_url(get_term_link($term->term_id)) : '#';
            $output .= '<li><a href="' . $link . '">' . esc_html($term->name) . '</a></li>';
        }

        $output .= '</ul>';

        return $this->content(
            $output,
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
