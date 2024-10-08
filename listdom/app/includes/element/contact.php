<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Contact Information Element Class.
 *
 * @class LSD_Element_Contact
 * @version    1.0.0
 */
class LSD_Element_Contact extends LSD_Element
{
    public $key = 'contact';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Contact Information', 'listdom');
    }

    public function get($post_id = null, array $args = [])
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/contact-info.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
