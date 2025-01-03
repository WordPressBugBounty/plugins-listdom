<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Title Element Class.
 *
 * @class LSD_Element_Title
 * @version    1.0.0
 */
class LSD_Element_Title extends LSD_Element
{
    public $key = 'title';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Listing Title', 'listdom');
        $this->has_title_settings = false;
    }

    public function get($post_id)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        return $this->content(
            get_the_title($post_id),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
