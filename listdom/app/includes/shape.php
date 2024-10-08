<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Entity Class.
 *
 * @class LSD_Shape
 * @version    1.0.0
 */
class LSD_Shape extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function get(array $args = [])
    {
        if (!count($args)) return false;

        $type = $args['type'];
        $paths = $args['paths'];
        $radius = $args['radius'];

        // Boundaries Array
        $boundaries = self::toBoundaries($paths);

        // Invalid Boundaries
        if (!count($boundaries)) return false;

        // Shape Data
        $shape = [];
        $shape['type'] = $type;

        switch ($type)
        {
            case 'circle':

                // Invalid Circle
                if (!is_object($boundaries[0])) return false;

                $shape['center'] = $boundaries[0];
                $shape['radius'] = $radius;

                break;

            case 'rectangle':

                // Invalid Rectangle
                if (!is_object($boundaries[0]) or !is_object($boundaries[1])) return false;

                $shape['north'] = $boundaries[0]->lat;
                $shape['east'] = $boundaries[0]->lng;
                $shape['south'] = $boundaries[1]->lat;
                $shape['west'] = $boundaries[1]->lng;

                break;

            case 'polygon':

                // Closing the Polygon
                $boundaries[] = $boundaries[0];

                $shape['boundaries'] = $boundaries;

                break;

            case 'polyline':

                $shape['boundaries'] = $boundaries;

                break;
        }

        return $shape;
    }

    /**
     * @param string $paths
     * @return array
     */
    public static function toBoundaries(string $paths): array
    {
        if (!trim($paths)) return [];

        $boundaries = [];

        $ex1 = explode(')', trim($paths, ', '));
        foreach ($ex1 as $value)
        {
            if (trim($value) == '') continue;

            $ex2 = explode(',', trim($value, '(), Latng'));

            $std = new stdClass();
            $std->lat = trim($ex2[0]);
            $std->lng = trim($ex2[1]);

            $boundaries[] = $std;
        }

        return $boundaries;
    }

    /**
     * @param string $string
     * @return array
     */
    public static function toPolygon(string $string): array
    {
        $points = explode('),', $string);

        $first_point = null;
        $polygon = [];

        foreach ($points as $point)
        {
            $geo = explode(',', trim($point, '(), '));
            $latlng = [trim($geo[0]), trim($geo[1])];

            if (!$first_point) $first_point = $latlng;
            $polygon[] = $latlng;
        }

        // Close the Polygon with first Point
        if ($first_point) $polygon[] = $first_point;

        return $polygon;
    }
}
