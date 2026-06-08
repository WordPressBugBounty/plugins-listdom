<?php

class LSD_Query extends LSD_Base
{
    public static function attribute($key, $value)
    {
        $id = '';
        $type = '';

        $operators = [
            'neq',
            'grq',
            'grb',
            'lwq',
            'nlk',
            'nin',
            'nbt',
            'nex',
            'eq',
            'gr',
            'lw',
            'lk',
            'in',
            'bt',
            'ex',
        ];

        foreach ($operators as $operator)
        {
            $suffix = '-' . $operator;
            if (substr($key, -strlen($suffix)) !== $suffix) continue;

            $id = substr($key, 0, -strlen($suffix));
            $type = $operator;
            break;
        }

        if ($id === '' || $type === '') return false;

        if ($id == 'address') $field = 'lsd_address';
        else if ($id == 'price') $field = 'lsd_price';
        else if ($id == 'class') $field = 'lsd_price_class';
        else if ($id == 'rate') $field = 'lsd_rate';
        else if (strpos($id, 'revf-') === 0)
        {
            if (!class_exists(\LSDPACREV\Reviews::class)) return false;

            $field = \LSDPACREV\Reviews::review_field_meta_key(substr($id, 5));
        }
        else if ($id == 'acf_fields') $field = 'acf_fields';
        else $field = 'lsd_attribute_' . LSD_Main::get_attr_slug($id);

        if (($id === 'rate' || strpos($id, 'revf-') === 0) && is_numeric($value) && (float) $value <= 0) return false;

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

                $values = self::normalize_multi_values($value);
                if (!count($values)) break;

                $query = [
                    'relation' => 'OR',
                    [
                        'key' => $field,
                        'value' => $values,
                        'compare' => 'IN',
                    ],
                ];

                foreach ($values as $v)
                {
                    $query[] = [
                        'key' => $field,
                        'value' => self::regexp_csv_pattern($v),
                        'compare' => 'REGEXP',
                    ];
                }

                break;

            case 'nin':

                $values = self::normalize_multi_values($value);
                if (!count($values)) break;

                $query = [
                    'relation' => 'AND',
                    [
                        'key' => $field,
                        'value' => $values,
                        'compare' => 'NOT IN',
                    ],
                ];

                foreach ($values as $v)
                {
                    $query[] = [
                        'key' => $field,
                        'value' => self::regexp_csv_pattern($v),
                        'compare' => 'NOT REGEXP',
                    ];
                }

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
                        'key' => $key_field,
                        'value' => $v,
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

    private static function normalize_multi_values($value): array
    {
        if (!is_array($value)) $value = [$value];

        $normalized = [];
        foreach ($value as $item)
        {
            if (!is_scalar($item)) continue;

            $item = trim((string) $item);
            if ($item === '') continue;

            $normalized[] = $item;
        }

        return array_values(array_unique($normalized));
    }

    private static function regexp_csv_pattern(string $value): string
    {
        return '(^|,)' . preg_quote($value, '/') . '(,|$)';
    }
}
