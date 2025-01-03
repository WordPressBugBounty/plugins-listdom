<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Embed Element Class.
 *
 * @class LSD_Element_Embed
 * @version    1.0.0
 */
class LSD_Element_Embed extends LSD_Element
{
    public $key = 'embed';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Embed', 'listdom');
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
        include lsd_template('elements/embeds.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}

