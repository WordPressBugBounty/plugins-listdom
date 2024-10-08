<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Address Element Class.
 *
 * @class LSD_Element_Address
 * @version    1.0.0
 */
class LSD_Element_Address extends LSD_Element
{
    public $key = 'address';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Address', 'listdom');
    }

    public function get($post_id = null, $icon = true)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        $address = get_post_meta($post_id, 'lsd_address', true);
        if (trim($address) == '') return '';

        return $this->content(
            ($icon ? '<i class="lsd-icon fas fa-map-marker-alt fa-lg lsd-color-m-txt" aria-hidden="true"></i> ' : '') . esc_html($address),
            $this,
            [
                'post_id' => $post_id,
                'icon' => $icon,
            ]
        );
    }
}
