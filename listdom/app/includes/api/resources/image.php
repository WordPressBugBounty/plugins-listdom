<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Image Resource Class.
 *
 * @class LSD_API_Resources_Image
 * @version    1.0.0
 */
class LSD_API_Resources_Image extends LSD_API_Resource
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
        return apply_filters('lsd_api_resource_image', [
            'id' => $id,
            'url' => wp_get_attachment_url($id),
        ], $id);
    }

    public static function collection($ids): array
    {
        $items = [];
        foreach ($ids as $id) $items[] = self::get($id);

        return $items;
    }
}
