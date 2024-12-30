<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Video Element Class.
 *
 * @class LSD_Element_Video
 * @version    1.0.0
 */
class LSD_Element_Video extends LSD_Element
{
    public $key = 'video';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Featured Video', 'listdom');
        $this->has_title_settings = false;
        $this->pro_needed = true;
    }

    public function get($post_id = null)
    {
        // Disabled in Lite
        if ($this->isLite()) return false;

        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/video.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}
