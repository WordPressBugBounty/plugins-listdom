<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Embed Resource Class.
 *
 * @class LSD_API_Resources_Embed
 * @version    1.0.0
 */
class LSD_API_Resources_Embed extends LSD_API_Resource
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function get($embed): array
    {
        return apply_filters('lsd_api_resource_embed', $embed);
    }

    public static function collection($embeds): array
    {
        $items = [];
        foreach ($embeds as $embed) $items[] = self::get($embed);

        return $items;
    }
}
