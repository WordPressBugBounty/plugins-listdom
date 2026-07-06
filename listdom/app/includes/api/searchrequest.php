<?php

class LSD_API_SearchRequest extends LSD_Base
{
    public static function resolve(array $filter, string $type, string $method, int $search_id, array $values = []): array
    {
        $key = isset($filter['key']) ? (string) $filter['key'] : '';
        $helper = new LSD_Search_Helper();

        $request = [
            'endpoint' => 'listdom/v1/search',
            'method' => 'GET',
            'field_type' => $type,
            'operator' => 'keyword',
            'params' => [],
        ];

        if ($key === '')
        {
            return self::filtered($request, $filter, $type, $method, $search_id);
        }

        switch ($type)
        {
            case 'textsearch':

                $request['operator'] = 'keyword';
                $request['params'][] = self::param('sf-' . $key, 'string', false, true, 'keyword', 'free_text', self::string_example($filter, 'restaurant'));

                if ($method === 'ai-search')
                {
                    $request['params'][] = self::param('sf-ai', 'boolean', false, true, 'mode', 'fixed', 1);
                }

                break;

            case 'taxonomy':

                $multiple = in_array($method, ['dropdown-multiple', 'checkboxes'], true) || ($method === 'buttons' && !empty($filter['buttons_multiple']));
                $request['operator'] = 'taxonomy_in';
                $request['params'][] = self::param(
                    'sf-' . $key . ($multiple ? '[]' : ''),
                    $method === 'text-input' ? 'integer|string' : 'integer',
                    $multiple,
                    true,
                    $multiple ? 'values' : 'value',
                    $method === 'text-input' ? 'values[].id_or_name' : 'values[].id',
                    self::value_example($values, self::number_example($filter, 1))
                );

                break;

            case 'text':
            case 'date':
            case 'time':
            case 'datetime':
            case 'tel':
            case 'textarea':

                $request['operator'] = 'like';
                $request['params'][] = self::param(
                    self::text_param_name($key, $helper),
                    'string',
                    false,
                    true,
                    'value',
                    'free_text',
                    self::text_example($filter, $type, $method)
                );

                break;

            case 'numeric':
            case 'number':

                $request = self::number_request($request, $filter, $key, $method, $helper, $values);
                break;

            case 'dropdown':
            case 'checkbox':
            case 'radio':

                $request = self::dropdown_request($request, $filter, $key, $method, $helper, $values);
                break;

            case 'price':

                $request = self::price_request($request, $filter, $method);
                break;

            case 'class':

                $request['operator'] = 'equals';
                $request['params'][] = self::param('sf-att-class-eq', 'integer', false, true, 'value', 'fixed_range', self::number_example($filter, 2));
                break;

            case 'review_rate':

                $request['operator'] = 'greater_or_equal';
                $request['params'][] = self::param('sf-att-rate-grq', 'number', false, true, 'minimum', 'fixed_range', self::number_example($filter, 4));
                break;

            case 'address':

                $request = self::address_request($request, $filter, $method);
                break;

            case 'period':

                $request['operator'] = 'date_range';
                $datepicker_format = self::datepicker_format();
                $request['params'][] = self::param(
                    'sf-period',
                    'string',
                    false,
                    true,
                    'range',
                    'settings.datepicker_format',
                    self::string_example($filter, self::date_range_example($datepicker_format)),
                    ['format' => $datepicker_format]
                );
                break;

            case 'acf_dropdown':

                $request = self::acf_dropdown_request($request, $filter, $key, $method, $values);
                break;

            case 'acf_range':

                $acf_key = self::acf_key($key);
                $request['operator'] = 'between';
                $request['params'][] = self::param('sf-acf-' . $acf_key . '-ara-min', 'number', false, true, 'min', 'numeric_range', self::number_example($filter, 0));
                $request['params'][] = self::param('sf-acf-' . $acf_key . '-ara-max', 'number', false, true, 'max', 'numeric_range', self::number_example($filter, 100, 'max_default_value', 'max'));
                break;

            case 'acf_true_false':

                $request['operator'] = 'boolean';
                $request['params'][] = self::param('sf-acf-' . self::acf_key($key) . '-trf', 'boolean', false, true, 'value', 'fixed_range', self::number_example($filter, 1));
                break;
        }

        return self::filtered($request, $filter, $type, $method, $search_id);
    }

    private static function number_request(array $request, array $filter, string $key, string $method, LSD_Search_Helper $helper, array $values): array
    {
        if (strpos($key, 'acf_number_') === 0)
        {
            $acf_key = self::acf_key($key);

            if ($method === 'dropdown-plus')
            {
                $request['operator'] = 'greater_or_equal';
                $request['params'][] = self::param('sf-acf-' . $acf_key . '-nmd', 'number', false, true, 'minimum', 'numeric', self::number_example($filter, 10));
            }
            else if ($method === 'range')
            {
                $request['operator'] = 'between';
                $request['params'][] = self::param('sf-acf-' . $acf_key . '-ara-min', 'number', false, true, 'min', 'numeric_range', self::number_example($filter, 0));
                $request['params'][] = self::param('sf-acf-' . $acf_key . '-ara-max', 'number', false, true, 'max', 'numeric_range', self::number_example($filter, 100, 'max_default_value', 'max'));
            }
            else
            {
                $request['operator'] = 'equals';
                $request['params'][] = self::param('sf-acf-' . $acf_key . '-nma', 'number', false, true, 'value', $method === 'dropdown' ? 'values' : 'numeric', self::value_example($values, self::number_example($filter, 10)));
            }

            return $request;
        }

        $base = 'sf-' . $helper->standardize_key($key);

        if ($method === 'dropdown-plus')
        {
            $request['operator'] = 'greater_or_equal';
            $request['params'][] = self::param($base . '-grq', 'number', false, true, 'minimum', 'numeric', self::number_example($filter, 10));
        }
        else if ($method === 'range')
        {
            $request['operator'] = 'between';
            $request['params'][] = self::param($base . '-grb-min', 'number', false, true, 'min', 'numeric_range', self::number_example($filter, 0));
            $request['params'][] = self::param($base . '-grb-max', 'number', false, true, 'max', 'numeric_range', self::number_example($filter, 100, 'max_default_value', 'max'));
        }
        else
        {
            $request['operator'] = 'equals';
            $request['params'][] = self::param($base . '-eq', 'number', false, true, 'value', $method === 'dropdown' ? 'values' : 'numeric', self::value_example($values, self::number_example($filter, 10)));
        }

        return $request;
    }

    private static function dropdown_request(array $request, array $filter, string $key, string $method, LSD_Search_Helper $helper, array $values): array
    {
        $multiple = in_array($method, ['dropdown-multiple', 'checkboxes'], true) || ($method === 'buttons' && !empty($filter['buttons_multiple']));
        $base = 'sf-' . $helper->standardize_key($key);

        $request['operator'] = $multiple ? 'in' : 'equals';
        $request['params'][] = self::param(
            $base . ($multiple ? '-in[]' : '-eq'),
            'string',
            $multiple,
            true,
            $multiple ? 'values' : 'value',
            'values',
            self::value_example($values, self::string_example($filter, 'value'))
        );

        return $request;
    }

    private static function price_request(array $request, array $filter, string $method): array
    {
        if ($method === 'mm-input' || $method === 'range')
        {
            $request['operator'] = 'between';
            $request['params'][] = self::param('sf-att-price-bt-min', 'number', false, true, 'min', 'numeric_range', self::number_example($filter, 0));
            $request['params'][] = self::param('sf-att-price-bt-max', 'number', false, true, 'max', 'numeric_range', self::number_example($filter, 100, 'max_default_value', 'max'));
        }
        else
        {
            $request['operator'] = 'greater_or_equal';
            $request['params'][] = self::param('sf-att-price-grq', 'number', false, true, 'minimum', 'numeric', self::number_example($filter, 10));
        }

        return $request;
    }

    private static function address_request(array $request, array $filter, string $method): array
    {
        if (in_array($method, ['radius', 'radius-dropdown'], true))
        {
            $request['operator'] = 'radius';
            $request['params'][] = self::param('sf-circle-center-lat', 'number', false, true, 'latitude', 'coordinates', 43.6532);
            $request['params'][] = self::param('sf-circle-center-lng', 'number', false, true, 'longitude', 'coordinates', -79.3832);
            $request['params'][] = self::param('sf-circle-radius', 'integer', false, true, 'radius_meters', 'configuration', self::radius_example($filter, $method));
            $request['params'][] = self::param('sf-circle-center', 'string', false, false, 'center', 'coordinates', '43.6532,-79.3832');

            return $request;
        }

        $request['operator'] = 'like';
        $request['params'][] = self::param('sf-att-address-lk', 'string', false, true, 'value', 'free_text', self::string_example($filter, 'Toronto'));

        return $request;
    }

    private static function acf_dropdown_request(array $request, array $filter, string $key, string $method, array $values): array
    {
        $acf_key = self::acf_key($key);
        $buttons_multiple = !empty($filter['buttons_multiple']);

        // ACF dropdown fields do not support button rendering. Keep saved
        // filters aligned with the frontend fallback.
        if ($method === 'buttons') $method = $buttons_multiple ? 'checkboxes' : 'radio';

        $multiple = in_array($method, ['dropdown-multiple', 'checkboxes'], true);

        $request['operator'] = $multiple ? 'acf_multi_like' : 'like';
        $request['params'][] = self::param(
            'sf-acf-' . $acf_key . ($multiple ? '-drm[]' : '-dra'),
            'string',
            $multiple,
            true,
            $multiple ? 'values' : 'value',
            'choices',
            self::value_example($values, self::string_example($filter, 'value'))
        );

        return $request;
    }

    private static function text_param_name(string $key, LSD_Search_Helper $helper): string
    {
        if (strpos($key, 'acf_email_') === 0 || strpos($key, 'acf_text_') === 0)
        {
            return 'sf-acf-' . self::acf_key($key) . '-atx';
        }

        return 'sf-' . $helper->standardize_key($key) . '-lk';
    }

    private static function acf_key(string $key): string
    {
        foreach ([
                     'acf_email_',
                     'acf_text_',
                     'acf_number_',
                     'acf_select_',
                     'acf_radio_',
                     'acf_checkbox_',
                     'acf_true_false_',
                     'acf_range_',
                 ] as $prefix)
        {
            if (strpos($key, $prefix) === 0) return substr($key, strlen($prefix));
        }

        return $key;
    }

    private static function param(string $name, string $type, bool $array, bool $required, string $role, string $source, $example, array $extra = []): array
    {
        return array_merge([
            'name' => $name,
            'type' => $type,
            'array' => $array,
            'required' => $required,
            'role' => $role,
            'source' => $source,
            'example' => $example,
        ], $extra);
    }

    private static function filtered(array $request, array $filter, string $type, string $method, int $search_id): array
    {
        $request['query_example'] = self::query_example($request['params']);

        $request = apply_filters('lsd_api_searchmodule_request', $request, $filter, $type, $method, $search_id);
        return is_array($request) ? $request : [];
    }

    private static function query_example(array $params): string
    {
        $pairs = [];
        foreach ($params as $param)
        {
            if (empty($param['required']) || !array_key_exists('example', $param)) continue;

            $examples = is_array($param['example']) ? $param['example'] : [$param['example']];
            foreach ($examples as $example)
            {
                if ($example === '' || $example === null) continue;

                if (is_bool($example)) $example = $example ? '1' : '0';
                $pairs[] = $param['name'] . '=' . rawurlencode((string) $example);
            }
        }

        return implode('&', $pairs);
    }

    private static function value_example(array $values, $fallback)
    {
        foreach ($values as $key => $value)
        {
            if (is_array($value))
            {
                if (isset($value['id'])) return $value['id'];
                if (isset($value['key'])) return $value['key'];
            }

            if (!is_int($key) && $key !== '') return $key;
            if (is_scalar($value)) return $value;
        }

        return $fallback;
    }

    private static function string_example(array $filter, string $fallback): string
    {
        foreach (['default_value', 'placeholder'] as $key)
        {
            if (isset($filter[$key]) && trim((string) $filter[$key]) !== '') return (string) $filter[$key];
        }

        return $fallback;
    }

    private static function text_example(array $filter, string $type, string $method): string
    {
        if ($method === 'date-input' || $type === 'date') return self::string_example($filter, '2026-01-01');
        if ($method === 'time-input' || $type === 'time') return self::string_example($filter, '09:00');
        if ($method === 'datetime-input' || $type === 'datetime') return self::string_example($filter, '2026-01-01T09:00');

        return self::string_example($filter, 'restaurant');
    }

    private static function number_example(array $filter, $fallback, string $default_key = 'default_value', string $limit_key = 'min')
    {
        if (isset($filter[$default_key]) && is_numeric($filter[$default_key])) return $filter[$default_key] + 0;
        if (isset($filter[$limit_key]) && is_numeric($filter[$limit_key])) return $filter[$limit_key] + 0;

        return $fallback;
    }

    private static function datepicker_format(): string
    {
        $settings = LSD_Options::settings();
        return isset($settings['datepicker_format']) && trim((string) $settings['datepicker_format'])
            ? (string) $settings['datepicker_format']
            : 'yyyy-mm-dd';
    }

    private static function date_range_example(string $format): string
    {
        $examples = [
            'dd-mm-yyyy' => '01-01-2026 - 03-01-2026',
            'yyyy/mm/dd' => '2026/01/01 - 2026/01/03',
            'dd/mm/yyyy' => '01/01/2026 - 03/01/2026',
            'yyyy.mm.dd' => '2026.01.01 - 2026.01.03',
            'dd.mm.yyyy' => '01.01.2026 - 03.01.2026',
        ];

        return $examples[$format] ?? '2026-01-01 - 2026-01-03';
    }

    private static function radius_example(array $filter, string $method): int
    {
        if ($method === 'radius-dropdown' && isset($filter['radius_values']) && trim((string) $filter['radius_values']) !== '')
        {
            $values = array_filter(array_map('trim', explode(',', (string) $filter['radius_values'])));
            $first = reset($values);

            if ($first !== false && is_numeric($first))
            {
                $radius = (float) $first;
                $unit = isset($filter['radius_display_unit']) ? (string) $filter['radius_display_unit'] : 'm';

                if ($unit === 'km') $radius *= 1000;
                else if ($unit === 'mile') $radius *= 1609;

                return (int) round($radius);
            }
        }

        if (isset($filter['radius']) && is_numeric($filter['radius'])) return (int) $filter['radius'];

        return 5000;
    }
}
