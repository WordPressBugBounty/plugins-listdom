<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Locations Element Class.
 *
 * @class LSD_Element_Locations
 * @version    1.0.0
 */
class LSD_Element_Locations extends LSD_Element
{
    public $key = 'locations';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Locations', 'listdom');
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
