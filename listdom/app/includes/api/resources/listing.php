<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Listing Resource Class.
 *
 * @class LSD_API_Resources_Listing
 * @version    1.0.0
 */
class LSD_API_Resources_Listing extends LSD_API_Resource
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function get($id): array
    {
        // Resource
        $resource = new LSD_API_Resource();

        // Listing
        $listing = get_post($id);

        // Meta Values
        $metas = $resource->get_post_meta($id);

        $min_price = $metas['lsd_price'] ?? null;
        $max_price = $metas['lsd_price_max'] ?? null;
        $text_price = $metas['lsd_price_after'] ?? null;
        $currency = $metas['lsd_currency'] ?? null;

        $price = $resource->render_price($min_price, $currency);
        if ($max_price) $price .= ' - ' . $resource->render_price($max_price, $currency);
        if ($text_price) $price .= ' / ' . $text_price;

        // Media
        $thumbnail_id = get_post_thumbnail_id($listing);
        $gallery = isset($metas['lsd_gallery']) && is_array($metas['lsd_gallery']) ? $metas['lsd_gallery'] : [];
        $embeds = isset($metas['lsd_embeds']) && is_array($metas['lsd_embeds']) ? $metas['lsd_embeds'] : [];

        $status = get_post_status_object($listing->post_status);

        return apply_filters('lsd_api_resource_listing', [
            'id' => $listing->ID,
            'data' => [
                'ID' => $listing->ID,
                'title' => get_the_title($listing),
                'content' => apply_filters('the_content', $listing->post_content),
                'excerpt' => $listing->post_excerpt,
                'date' => $listing->post_date,
                'status' => [
                    'key' => $listing->post_status,
                    'label' => $status->label,
                ],
                'address' => $metas['lsd_address'] ?? null,
                'price' => $price,
                'latitude' => $metas['lsd_latitude'] ?? null,
                'longitude' => $metas['lsd_longitude'] ?? null,
                'zoomlevel' => $metas['lsd_zoomlevel'] ?? null,
                'object_type' => $metas['lsd_object_type'] ?? 'marker',
                'shape_type' => $metas['lsd_shape_type'] ?? null,
                'shape_paths' => $metas['lsd_shape_paths'] ?? null,
                'shape_radius' => $metas['lsd_shape_radius'] ?? null,
                'link' => $metas['lsd_link'] ?? null,
                'email' => $metas['lsd_email'] ?? null,
                'phone' => $metas['lsd_phone'] ?? null,
                'remark' => $metas['lsd_remark'] ?? null,
                'availability' => LSD_API_Resources_Availability::get($listing->ID),
                'favorite' => apply_filters('lsd_is_favorite', 0, $listing->ID),
                'claimed' => apply_filters('lsd_is_claimed', 0, $listing->ID),
            ],
            'raw' => [
                'content' => strip_tags(apply_filters('the_content', $listing->post_content)),
                'price' => $min_price,
                'price_max' => $max_price,
                'price_after' => $text_price,
                'currency' => $currency,
                'featured_image' => $thumbnail_id,
                'gallery' => $gallery,
            ],
            'taxonomies' => LSD_API_Resources_Taxonomy::listing($listing->ID),
            'attributes' => LSD_API_Resources_Attribute::listing($listing->ID),
            'media' => [
                'featured_image' => LSD_API_Resources_Image::get($thumbnail_id),
                'gallery' => LSD_API_Resources_Image::collection($gallery),
                'embed' => LSD_API_Resources_Embed::collection($embeds),
            ],
            'user' => LSD_API_Resources_User::minify($listing->post_author),
        ], $id);
    }

    public static function collection($ids): array
    {
        $items = [];
        foreach ($ids as $id) $items[] = self::get($id);

        return $items;
    }

    public static function minify($id)
    {
        // Listing
        $listing = get_post($id);

        // Featured Image
        $thumbnail_id = get_post_thumbnail_id($listing);

        // Listing Status
        $status = get_post_status_object($listing->post_status);

        return apply_filters('lsd_api_resource_listing', [
            'id' => $listing->ID,
            'data' => [
                'ID' => $listing->ID,
                'title' => get_the_title($listing),
                'status' => [
                    'key' => $listing->post_status,
                    'label' => $status->label,
                ],
            ],
            'media' => [
                'featured_image' => LSD_API_Resources_Image::get($thumbnail_id),
            ],
        ], $id);
    }

    public static function pagination($skin)
    {
        $found = $skin->found_listings;

        $total_pages = ceil(($found / $skin->limit));
        $current_page = ($skin->next_page - 1);
        $previous_page = ($current_page - 1);
        $next_page = $skin->next_page;

        if ($current_page >= $total_pages) $next_page = null;
        if ($current_page <= 1) $previous_page = null;

        return apply_filters('lsd_api_resource_pagination', [
            'found_listings' => $found,
            'listings_per_page' => $skin->limit,
            'total_pages' => $total_pages,
            'previous_page' => $previous_page,
            'current_page' => $current_page,
            'next_page' => $next_page,
        ], $skin);
    }
}
