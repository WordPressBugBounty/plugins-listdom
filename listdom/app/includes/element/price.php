<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Price Element Class.
 *
 * @class LSD_Element_Price
 * @version    1.0.0
 */
class LSD_Element_Price extends LSD_Element
{
    public $key = 'price';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Price', 'listdom');
    }

    public function get($post_id = null, $minimized = false)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/price.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'minimized' => $minimized,
            ]
        );
    }
}
