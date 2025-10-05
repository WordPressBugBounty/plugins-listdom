<?php

class LSD_Sanitize extends LSD_Base
{
    public static function deep(array $values, ?callable $sanitizer = null): array
    {
        $sanitizer = $sanitizer ?: static function ($value)
        {
            if (is_string($value)) return sanitize_text_field($value);

            if (is_scalar($value)) return $value;

            return is_null($value) ? '' : $value;
        };

        foreach ($values as $key => $value)
        {
            if (is_array($value))
            {
                $values[$key] = self::deep($value, $sanitizer);
                continue;
            }

            $values[$key] = $sanitizer($value);
        }

        return $values;
    }

    public static function search(array $filters): array
    {
        $sanitized = [];

        $float_keys = [
            'min_latitude',
            'max_latitude',
            'min_longitude',
            'max_longitude',
            'rect_min_latitude',
            'rect_max_latitude',
            'rect_min_longitude',
            'rect_max_longitude',
            'circle_latitude',
            'circle_longitude',
            'circle_radius',
        ];

        $taxonomy_keys = array_merge(LSD_Base::taxonomies(), [LSD_Base::TAX_TAG]);

        foreach ($filters as $key => $value)
        {
            if (in_array($key, $float_keys, true))
            {
                $float = self::float($value);
                if ($float !== null) $sanitized[$key] = $float;
                continue;
            }

            if ($key === 'shape')
            {
                $sanitized[$key] = sanitize_text_field((string) $value);
                continue;
            }

            if ($key === 'polygon')
            {
                $polygon = self::polygon($value);
                if ($polygon !== '') $sanitized[$key] = $polygon;
                continue;
            }

            if ($key === 'circle' && is_array($value))
            {
                $circle = self::circle($value);
                if (!empty($circle)) $sanitized[$key] = $circle;
                continue;
            }

            if (in_array($key, $taxonomy_keys, true))
            {
                $sanitized[$key] = self::taxonomy($value);
                continue;
            }

            $sanitized[$key] = self::generic($value);
        }

        return $sanitized;
    }

    protected static function float($value): ?float
    {
        if (is_array($value)) return null;

        if (is_string($value)) $value = trim($value);

        if ($value === '' || !is_numeric($value)) return null;

        return (float) $value;
    }

    protected static function circle(array $circle): array
    {
        $sanitized = [];

        if (isset($circle['center']))
        {
            if (is_array($circle['center']))
            {
                if (isset($circle['center']['lat'], $circle['center']['lng']))
                {
                    $lat = self::float($circle['center']['lat']);
                    $lng = self::float($circle['center']['lng']);

                    if ($lat !== null && $lng !== null) $sanitized['center'] = [$lat, $lng];
                }
                else
                {
                    $center = [];
                    foreach ($circle['center'] as $coordinate)
                    {
                        $float = self::float($coordinate);
                        if ($float !== null) $center[] = $float;
                    }

                    if (count($center) === 2) $sanitized['center'] = [$center[0], $center[1]];
                }
            }
            else if (is_scalar($circle['center'])) $sanitized['center'] = sanitize_text_field((string) $circle['center']);
        }

        if (isset($circle['radius']))
        {
            $radius = self::float($circle['radius']);
            if ($radius !== null) $sanitized['radius'] = $radius;
        }

        return $sanitized;
    }

    protected static function taxonomy($value)
    {
        if (is_array($value))
        {
            $sanitized = [];
            foreach ($value as $term)
            {
                if (is_numeric($term))
                {
                    $term_id = absint($term);
                    if ($term_id) $sanitized[] = $term_id;
                }
                else if (is_scalar($term))
                {
                    $term_value = sanitize_text_field((string) $term);
                    if ($term_value !== '') $sanitized[] = $term_value;
                }
            }

            return $sanitized;
        }

        if (is_numeric($value)) return absint($value);

        if (is_scalar($value)) return sanitize_text_field((string) $value);

        return $value;
    }

    protected static function generic($value)
    {
        if (is_array($value))
        {
            $sanitized = [];
            foreach ($value as $key => $val) $sanitized[$key] = self::generic($val);
            return $sanitized;
        }

        if (is_scalar($value))
        {
            if (is_numeric($value)) return 0 + $value;

            return sanitize_text_field((string) $value);
        }

        return $value;
    }

    protected static function polygon($value): string
    {
        $points = [];

        $add_point = static function (float $lat, float $lng) use (&$points)
        {
            $points[] = '(' . $lat . ', ' . $lng . ')';
        };

        if (is_array($value))
        {
            foreach ($value as $point)
            {
                if (is_array($point))
                {
                    if (isset($point['lat'], $point['lng']))
                    {
                        $lat = self::float($point['lat']);
                        $lng = self::float($point['lng']);
                    }
                    else
                    {
                        $point_values = array_values($point);
                        $lat = isset($point_values[0]) ? self::float($point_values[0]) : null;
                        $lng = isset($point_values[1]) ? self::float($point_values[1]) : null;
                    }

                    if ($lat !== null && $lng !== null) $add_point($lat, $lng);
                }
                else if (is_string($point))
                {
                    $coordinates = self::extract_coordinates($point);
                    if ($coordinates) $add_point($coordinates[0], $coordinates[1]);
                }
            }
        }
        else if (is_string($value))
        {
            $segments = preg_split('/\),/', $value);
            foreach ($segments as $segment)
            {
                $coordinates = self::extract_coordinates($segment);
                if ($coordinates) $add_point($coordinates[0], $coordinates[1]);
            }
        }

        return implode(', ', $points);
    }

    protected static function extract_coordinates(string $segment): ?array
    {
        if (!trim($segment)) return null;

        if (preg_match_all('/-?\d+(?:\.\d+)?/', $segment, $matches) && count($matches[0]) >= 2)
        {
            return [(float) $matches[0][0], (float) $matches[0][1]];
        }

        return null;
    }
}
