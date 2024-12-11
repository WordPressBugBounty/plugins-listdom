<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Meta Class.
 *
 * @class LSD_Meta
 * @version    1.0.0
 */
class LSD_Meta extends LSD_Base
{
    public static function all()
    {
        $callback = function ($key, $id) {
            return get_post_meta($id, $key, true);
        };

        $metas = [
            'lsd_address' => [
                'key' => 'lsd_address',
                'name' => esc_html__('Address', 'listdom'),
                'get' => $callback,
            ],
            'lsd_latitude' => [
                'key' => 'lsd_latitude',
                'name' => esc_html__('Latitude', 'listdom'),
                'get' => $callback,
            ],
            'lsd_longitude' => [
                'key' => 'lsd_longitude',
                'name' => esc_html__('Longitude', 'listdom'),
                'get' => $callback,
            ],
            'lsd_price' => [
                'key' => 'lsd_price',
                'name' => esc_html__('Price', 'listdom'),
                'get' => $callback,
            ],
            'lsd_price_max' => [
                'key' => 'lsd_price_max',
                'name' => esc_html__('Price (Max)', 'listdom'),
                'get' => $callback,
            ],
            'lsd_price_after' => [
                'key' => 'lsd_price_after',
                'name' => esc_html__('Price Description', 'listdom'),
                'get' => $callback,
            ],
            'lsd_price_class' => [
                'key' => 'lsd_price_class',
                'name' => esc_html__('Price Class', 'listdom'),
                'get' => function ($key, $id) {
                    $class = (int) get_post_meta($id, $key, true);
                    if (!trim($class)) $class = 2;

                    return str_repeat('$', $class);
                },
            ],
            'lsd_currency' => [
                'key' => 'lsd_currency',
                'name' => esc_html__('Currency', 'listdom'),
                'get' => $callback,
            ],
            'lsd_primary_category' => [
                'key' => 'lsd_primary_category',
                'name' => esc_html__('Primary Category', 'listdom'),
                'get' => function ($key, $id) {
                    $category_id = (int) get_post_meta($id, $key, true);
                    $category = $category_id ? get_term($category_id) : null;

                    return $category && isset($category->name) ? $category->name : '';
                },
            ],
            'lsd_email' => [
                'key' => 'lsd_email',
                'name' => esc_html__('Email', 'listdom'),
                'get' => $callback,
            ],
            'lsd_phone' => [
                'key' => 'lsd_phone',
                'name' => esc_html__('Phone', 'listdom'),
                'get' => $callback,
            ],
            'lsd_website' => [
                'key' => 'lsd_website',
                'name' => esc_html__('Website', 'listdom'),
                'get' => $callback,
            ],
            'lsd_contact_address' => [
                'key' => 'lsd_contact_address',
                'name' => esc_html__('Contact Address', 'listdom'),
                'get' => $callback,
            ],
            'lsd_remark' => [
                'key' => 'lsd_remark',
                'name' => esc_html__('Remark', 'listdom'),
                'get' => $callback,
            ],
            'lsd_link' => [
                'key' => 'lsd_link',
                'name' => esc_html__('Listing Custom Link', 'listdom'),
                'get' => $callback,
            ],
        ];

        // Socials
        $sc = new LSD_Socials();

        $networks = LSD_Options::socials();
        foreach ($networks as $network => $values)
        {
            $obj = $sc->get($network, $values);

            // Social Network is not Enabled
            if (!$obj || !$obj->option('listing')) continue;

            $key = 'lsd_' . $obj->key();
            $metas[$key] = [
                'key' => $key,
                'name' => $obj->label(),
                'get' => $callback,
            ];
        }

        // Attributes
        $attributes = LSD_Main::get_attributes();

        foreach ($attributes as $attribute)
        {
            $type = get_term_meta($attribute->term_id, 'lsd_field_type', true);
            if ($type === 'separator') continue;

            $key = 'lsd_attribute_' . $attribute->term_id;
            $metas[$key] = [
                'key' => $key,
                'name' => $attribute->name,
                'get' => $callback,
            ];
        }

        return apply_filters('lsd_listing_meta', $metas);
    }

    public static function get($key): array
    {
        // All Meta Fields
        $all = LSD_Meta::all();

        // Return Meta
        return isset($all[$key]) && is_array($all[$key]) ? $all[$key] : [];
    }
}
