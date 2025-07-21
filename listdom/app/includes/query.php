<?php

class LSD_Query extends LSD_Base
{
    public static function attribute($key, $value)
    {
        [$id, $type] = explode('-', $key);

        if ($id == 'address') $field = 'lsd_address';
        else if ($id == 'price') $field = 'lsd_price';
        else if ($id == 'class') $field = 'lsd_price_class';
        else if ($id == 'acf_fields') $field = 'acf_fields';
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

            case 'grb':

                $query = [
                    'key' => $field,
                    'value' => explode(':', rtrim($value, ':')),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
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

    public static function acf_fields($key, $value)
    {
        $type = substr($key, -3);
        $key_field = substr($key, 0, -4);

        $query = [];
        switch ($type)
        {
            case 'atx':
            case 'dra':
            case 'trf':

                $query = [
                    'key' => $key_field,
                    'value' => $value,
                    'compare' => 'LIKE',
                ];

                break;

            case 'nma':

                $query = [
                    'key' => $key_field,
                    'value' => $value,
                    'compare' => '=',
                ];

                break;

            case 'nmd':

                $query = [
                    'key' => $key_field,
                    'value' => $value,
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ];

                break;

            case 'drm':

                // Force to Array
                if (!is_array($value)) $value = [$value];

                foreach ($value as $v)
                {
                    $query[] = [
                        'key'     => $key_field,
                        'value'   => $v,
                        'compare' => 'LIKE',
                    ];
                }

                break;

            case 'ara':
                $value = explode(':', rtrim($value, ':'));

                $query = [
                    'key' => $key_field,
                    'value' => $value,
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                ];

                break;

        }

        return count($query) ? $query : false;
    }
}
