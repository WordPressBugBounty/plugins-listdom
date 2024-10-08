<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Availability Resource Class.
 *
 * @class LSD_API_Resources_Availability
 * @version    1.0.0
 */
class LSD_API_Resources_Availability extends LSD_API_Resource
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
        $availability = get_post_meta($id, 'lsd_ava', true);
        if (!is_array($availability)) return [];

        $rendered = [];
        foreach (LSD_Main::get_weekdays() as $weekday)
        {
            $daycode = $weekday['code'];
            $hours = null;
            $off = 0;

            if (isset($availability[$daycode]) and isset($availability[$daycode]['off']) and $availability[$daycode]['off'])
            {
                $hours = esc_html__('Off', 'listdom');
                $off = 1;
            }
            else if (isset($availability[$daycode]) and isset($availability[$daycode]['hours'])) $hours = $availability[$daycode]['hours'];

            $rendered[$daycode] = [
                'day' => esc_html__($weekday['day'], 'listdom'),
                'hours' => $hours,
                'off' => $off,
            ];
        }

        return apply_filters('lsd_api_resource_availability', $rendered, $id);
    }

    public static function collection($ids): array
    {
        $items = [];
        foreach ($ids as $id) $items[] = self::get($id);

        return $items;
    }
}
