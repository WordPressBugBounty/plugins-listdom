<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Infowindow Element Class.
 *
 * @class LSD_Element_Infowindow
 * @version    1.0.0
 */
class LSD_Element_Infowindow extends LSD_Element
{
    public $key = 'infowindow';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Infowindow', 'listdom');
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
        include lsd_template('elements/infowindow.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
