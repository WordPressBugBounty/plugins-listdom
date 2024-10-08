<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Query Class.
 *
 * @class LSD_Query
 * @version    1.0.0
 */
class LSD_Query extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function attribute($key, $value)
    {
        list($id, $type) = explode('-', $key);

        if ($id == 'address') $field = 'lsd_address';
        else if ($id == 'price') $field = 'lsd_price';
        else if ($id == 'class') $field = 'lsd_price_class';
        else $field = 'lsd_attribute_' . $id;

        $query = [];
        switch ($type)
        {
            case 'eq':

                $query = [
                    'key' => $field,
                    'value' => $value,
                    'compare' => '=',
                ];

                break;

            case 'neq':

                $query = [
                    'key' => $field,
                    'value' => $value,
                    'compare' => '!=',
                ];

                break;

            case 'gr':

                $query = [
                    'key' => $field,
                    'value' => $value,
                    'compare' => '>',
                    'type' => 'NUMERIC',
                ];

                break;

            case 'grq':

                $query = [
                    'key' => $field,
                    'value' => $value,
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ];

                break;

            case 'lw':

                $query = [
                    'key' => $field,
                    'value' => $value,
                    'compare' => '<',
                    'type' => 'NUMERIC',
                ];

                break;

            case 'lwq':

                $query = [
                    'key' => $field,
                    'value' => $value,
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ];

                break;

            case 'lk':

                $query = [
                    'key' => $field,
                    'value' => $value,
                    'compare' => 'LIKE',
                ];

                break;

            case 'nlk':

                $query = [
                    'key' => $field,
                    'value' => $value,
                    'compare' => 'NOT LIKE',
                ];

                break;

            case 'in':

                // Force to Array
                if (!is_array($value)) $value = [$value];

                $query = [
                    'key' => $field,
                    'value' => $value,
                    'compare' => 'IN',
                ];

                break;

            case 'nin':

                // Force to Array
                if (!is_array($value)) $value = [$value];

                $query = [
                    'key' => $field,
                    'value' => $value,
                    'compare' => 'NOT IN',
                ];

                break;

            case 'bt':

                $query = [
                    'key' => $field,
                    'value' => explode(':', $value),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                ];

                break;

            case 'nbt':

                $query = [
                    'key' => $field,
                    'value' => explode(':', $value),
                    'compare' => 'NOT BETWEEN',
                ];

                break;

            case 'ex':

                $query = [
                    'key' => $field,
                    'compare' => 'EXISTS',
                ];

                break;

            case 'nex':

                $query = [
                    'key' => $field,
                    'compare' => 'NOT EXISTS',
                ];

                break;
        }

        return count($query) ? $query : false;
    }
}
