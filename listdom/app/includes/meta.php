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
    const URL = 'url';
    const TEXT = 'text';
    const NUMBER = 'number';
    const IMAGE = 'image';
    const GALLERY = 'gallery';
    const DATETIME = 'datetime';
    const EMAIL = 'email';
    const TEL = 'tel';

    public static function all(): array
    {
        $callback = function ($key, $id)
        {
            return get_post_meta($id, $key, true);
        };

        $date_format = get_option('date_format');

        $metas = [
            'post_url' => [
                'key' => 'post_url',
                'type' => LSD_Meta::URL,
                'name' => esc_html__('Listing URL', 'listdom'),
                'get' => function ($key, $id)
                {
                    return get_post_permalink($id);
                },
            ],
            'post_title' => [
                'key' => 'post_title',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Listing Title', 'listdom'),
                'get' => function ($key, $id)
                {
                    return get_the_title($id);
                },
            ],
            'post_featured_image' => [
                'key' => 'post_featured_image',
                'type' => LSD_Meta::IMAGE,
                'name' => esc_html__('Listing Image', 'listdom'),
                'get' => function ($key, $id)
                {
                    return get_post_thumbnail_id($id);
                },
            ],
            'post_excerpt' => [
                'key' => 'post_excerpt',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Listing Excerpt', 'listdom'),
                'get' => function ($key, $id)
                {
                    return get_the_excerpt($id);
                },
            ],
            'post_date' => [
                'key' => 'post_date',
                'type' => LSD_Meta::DATETIME,
                'name' => esc_html__('Listing Date', 'listdom'),
                'get' => function ($key, $id) use ($date_format)
                {
                    return get_the_date($date_format, $id);
                },
            ],
            'post_modified' => [
                'key' => 'post_modified',
                'type' => LSD_Meta::DATETIME,
                'name' => esc_html__('Listing Modification Date', 'listdom'),
                'get' => function ($key, $id) use ($date_format)
                {
                    return get_the_modified_date($date_format, $id);
                },
            ],
            'post_comments' => [
                'key' => 'post_comments',
                'type' => LSD_Meta::NUMBER,
                'name' => esc_html__('Listing Comments Count', 'listdom'),
                'get' => function ($key, $id)
                {
                    // Post
                    $post = get_post($id);

                    return $post && isset($post->comment_count) ? $post->comment_count : 0;
                },
            ],
            'author_display_name' => [
                'key' => 'author_display_name',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Author Display Name', 'listdom'),
                'get' => function ($key, $id)
                {
                    // Post
                    $post = get_post($id);

                    return $post && isset($post->post_author)
                        ? get_the_author_meta('display_name', $post->post_author)
                        : '';
                },
            ],
            'author_first_name' => [
                'key' => 'author_first_name',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Author First Name', 'listdom'),
                'get' => function ($key, $id)
                {
                    // Post
                    $post = get_post($id);

                    return $post && isset($post->post_author)
                        ? get_the_author_meta('first_name', $post->post_author)
                        : '';
                },
            ],
            'author_last_name' => [
                'key' => 'author_last_name',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Author Last Name', 'listdom'),
                'get' => function ($key, $id)
                {
                    // Post
                    $post = get_post($id);

                    return $post && isset($post->post_author)
                        ? get_the_author_meta('last_name', $post->post_author)
                        : '';
                },
            ],
            'author_email' => [
                'key' => 'author_email',
                'type' => LSD_Meta::EMAIL,
                'name' => esc_html__('Author Email', 'listdom'),
                'get' => function ($key, $id)
                {
                    // Post
                    $post = get_post($id);

                    return $post && isset($post->post_author)
                        ? get_the_author_meta('user_email', $post->post_author)
                        : '';
                },
            ],
            'author_job_title' => [
                'key' => 'author_job_title',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Author Job Title', 'listdom'),
                'get' => function ($key, $id)
                {
                    // Post
                    $post = get_post($id);

                    return $post && isset($post->post_author)
                        ? get_user_meta($post->post_author, 'lsd_job_title', true)
                        : '';
                },
            ],
            'author_phone' => [
                'key' => 'author_phone',
                'type' => LSD_Meta::TEL,
                'name' => esc_html__('Author Phone', 'listdom'),
                'get' => function ($key, $id)
                {
                    // Post
                    $post = get_post($id);

                    return $post && isset($post->post_author)
                        ? get_user_meta($post->post_author, 'lsd_phone', true)
                        : '';
                },
            ],
            'author_mobile' => [
                'key' => 'author_mobile',
                'type' => LSD_Meta::TEL,
                'name' => esc_html__('Author Mobile', 'listdom'),
                'get' => function ($key, $id)
                {
                    // Post
                    $post = get_post($id);

                    return $post && isset($post->post_author)
                        ? get_user_meta($post->post_author, 'lsd_mobile', true)
                        : '';
                },
            ],
            'author_website' => [
                'key' => 'author_website',
                'type' => LSD_Meta::URL,
                'name' => esc_html__('Author Website', 'listdom'),
                'get' => function ($key, $id)
                {
                    // Post
                    $post = get_post($id);

                    return $post && isset($post->post_author)
                        ? get_user_meta($post->post_author, 'lsd_website', true)
                        : '';
                },
            ],
            'author_fax' => [
                'key' => 'author_fax',
                'type' => LSD_Meta::TEL,
                'name' => esc_html__('Author Fax', 'listdom'),
                'get' => function ($key, $id)
                {
                    // Post
                    $post = get_post($id);

                    return $post && isset($post->post_author)
                        ? get_user_meta($post->post_author, 'lsd_fax', true)
                        : '';
                },
            ],
            'lsd_address' => [
                'key' => 'lsd_address',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Address', 'listdom'),
                'get' => $callback,
            ],
            'lsd_latitude' => [
                'key' => 'lsd_latitude',
                'type' => LSD_Meta::NUMBER,
                'name' => esc_html__('Latitude', 'listdom'),
                'get' => $callback,
            ],
            'lsd_longitude' => [
                'key' => 'lsd_longitude',
                'type' => LSD_Meta::NUMBER,
                'name' => esc_html__('Longitude', 'listdom'),
                'get' => $callback,
            ],
            'lsd_price' => [
                'key' => 'lsd_price',
                'type' => LSD_Meta::NUMBER,
                'name' => esc_html__('Price', 'listdom'),
                'get' => $callback,
            ],
            'lsd_price_max' => [
                'key' => 'lsd_price_max',
                'type' => LSD_Meta::NUMBER,
                'name' => esc_html__('Price (Max)', 'listdom'),
                'get' => $callback,
            ],
            'lsd_price_after' => [
                'key' => 'lsd_price_after',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Price Description', 'listdom'),
                'get' => $callback,
            ],
            'lsd_price_class' => [
                'key' => 'lsd_price_class',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Price Class', 'listdom'),
                'get' => function ($key, $id)
                {
                    $class = (int) get_post_meta($id, $key, true);
                    if (!trim($class)) $class = 2;

                    return str_repeat('$', $class);
                },
            ],
            'lsd_currency' => [
                'key' => 'lsd_currency',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Currency', 'listdom'),
                'get' => $callback,
            ],
            'lsd_primary_category' => [
                'key' => 'lsd_primary_category',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Primary Category', 'listdom'),
                'get' => function ($key, $id)
                {
                    $category_id = (int) get_post_meta($id, $key, true);
                    $category = $category_id ? get_term($category_id) : null;

                    return $category && isset($category->name) ? $category->name : '';
                },
            ],
            'lsd_email' => [
                'key' => 'lsd_email',
                'type' => LSD_Meta::EMAIL,
                'name' => esc_html__('Email', 'listdom'),
                'get' => $callback,
            ],
            'lsd_phone' => [
                'key' => 'lsd_phone',
                'type' => LSD_Meta::TEL,
                'name' => esc_html__('Phone', 'listdom'),
                'get' => $callback,
            ],
            'lsd_website' => [
                'key' => 'lsd_website',
                'type' => LSD_Meta::URL,
                'name' => esc_html__('Website', 'listdom'),
                'get' => $callback,
            ],
            'lsd_contact_address' => [
                'key' => 'lsd_contact_address',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Contact Address', 'listdom'),
                'get' => $callback,
            ],
            'lsd_remark' => [
                'key' => 'lsd_remark',
                'type' => LSD_Meta::TEXT,
                'name' => esc_html__('Remark (Owner Message)', 'listdom'),
                'get' => $callback,
            ],
            'lsd_link' => [
                'key' => 'lsd_link',
                'type' => LSD_Meta::URL,
                'name' => esc_html__('Listing Custom Link', 'listdom'),
                'get' => $callback,
            ],
            'lsd_gallery' => [
                'key' => 'lsd_gallery',
                'type' => LSD_Meta::GALLERY,
                'name' => esc_html__('Gallery', 'listdom'),
                'get' => function ($key, $id)
                {
                    $gallery = get_post_meta($id, $key, true);
                    if (!is_array($gallery)) $gallery = [];

                    return $gallery;
                },
            ],
            'lsd_visible_until' => [
                'key' => 'lsd_visible_until',
                'type' => LSD_Meta::DATETIME,
                'name' => esc_html__('Visible Until', 'listdom'),
                'get' => function ($key, $id) use ($date_format)
                {
                    $timestamp = (int) get_post_meta($id, $key, true);
                    return $timestamp ? wp_date($date_format, $timestamp) : '';
                },
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
                'type' => LSD_Meta::URL,
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
                'type' => in_array($type, [LSD_Meta::NUMBER, LSD_Meta::EMAIL, LSD_Meta::URL])
                    ? $type
                    : LSD_Meta::TEXT,
                'name' => $attribute->name,
                'get' => $callback,
            ];
        }

        return apply_filters('lsd_listing_meta', $metas, $callback);
    }

    public static function get($key): array
    {
        // All Meta Fields
        $all = LSD_Meta::all();

        // Return Meta
        return isset($all[$key]) && is_array($all[$key]) ? $all[$key] : [];
    }

    public static function type(string $type): array
    {
        $metas = [];
        foreach (LSD_Meta::all() as $key => $meta)
        {
            if ($meta['type'] !== $type) continue;

            $metas[$key] = $meta;
        }

        return $metas;
    }

    public static function value(string $key, int $post_id)
    {
        // Meta Field
        $meta = LSD_Meta::get($key);

        // No Callback!
        if (!isset($meta['get']) || !is_callable($meta['get'])) return '';

        // Get the content
        return call_user_func($meta['get'], $key, $post_id);
    }

    public static function pairs(array $types = []): array
    {
        $metas = [];
        foreach (LSD_Meta::all() as $key => $meta)
        {
            if ($types && !in_array($meta['type'], $types)) continue;
            $metas[$key] = $meta['name'];
        }

        return $metas;
    }
}
