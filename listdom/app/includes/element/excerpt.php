<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Excerpt Element Class.
 *
 * @class LSD_Element_Excerpt
 * @version    1.0.0
 */
class LSD_Element_Excerpt extends LSD_Element_Content
{
    public $key = 'excerpt';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Listing Excerpt', 'listdom');
    }

    public function get($post_id, $limit = 15, $read_more = false)
    {
        return $this->excerpt($post_id, $limit, $read_more);
    }
}
