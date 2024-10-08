<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Share Element Class.
 *
 * @class LSD_Element_Share
 * @version    1.0.0
 */
class LSD_Element_Share extends LSD_Element
{
    public $key = 'share';
    public $label;
    public $layout;
    public $args;

    /**
     * Constructor method
     * @param string $layout
     * @param array $args
     */
    public function __construct(string $layout = 'full', array $args = [])
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Share', 'listdom');
        $this->layout = $layout;
        $this->args = $args;
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
