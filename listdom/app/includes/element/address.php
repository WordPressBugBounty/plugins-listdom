<?php

class LSD_Element_Address extends LSD_Element
{
    public $key = 'address';
    public $label;

    public function __construct()
    {
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

        $address_markup = '<span class="lsd-address-text" itemprop="streetAddress">' . esc_html($address) . '</span>';

        return $this->content(
            ($icon ? '<i class="lsd-fe-icon fas fa-map-marker-alt fa-lg" aria-hidden="true"></i> ' : '') . $address_markup,
            $this,
            [
                'post_id' => $post_id,
                'icon' => $icon,
            ]
        );
    }
}
