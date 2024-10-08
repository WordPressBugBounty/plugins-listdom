<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Resource Class.
 *
 * @class LSD_API_Resource
 * @version    1.0.0
 */
class LSD_API_Resource extends LSD_API
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
        return [];
    }

    public static function collection($ids): array
    {
        $items = [];
        foreach ($ids as $id) $items[] = self::get($id);

        return $items;
    }
}
