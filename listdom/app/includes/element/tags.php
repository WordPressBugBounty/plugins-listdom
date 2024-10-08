<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Tags Element Class.
 *
 * @class LSD_Element_Tags
 * @version    1.0.0
 */
class LSD_Element_Tags extends LSD_Element
{
    public $key = 'tags';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Tags', 'listdom');
    }

    public function get($post_id = null)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        $terms = wp_get_post_terms($post_id, LSD_Base::TAX_TAG, []);
        if (!count($terms)) return '';

        $output = '<ul>';
        foreach ($terms as $term) $output .= '<li><a href="' . esc_url(get_term_link($term->term_id)) . '">' . esc_html($term->name) . '</a></li>';
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
