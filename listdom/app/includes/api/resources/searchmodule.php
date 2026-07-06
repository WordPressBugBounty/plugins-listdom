<?php

class LSD_API_Resources_SearchModule extends LSD_API_Resource
{
    public static function get($id): array
    {
        // Resource
        $resource = new LSD_API_Resource();

        // Helper
        $helper = new LSD_Search_Helper();

        // Search Module
        $search = get_post($id);

        // Guard: return an empty payload if the search module cannot be retrieved.
        if (!($search instanceof WP_Post)) return [];

        // Meta Values
        $metas = $resource->get_post_meta($id);

        // Form Values
        $form = isset($metas['lsd_form']) && is_array($metas['lsd_form']) ? $metas['lsd_form'] : [];

        // Fields
        $raw = isset($metas['lsd_fields']) && is_array($metas['lsd_fields']) ? $metas['lsd_fields'] : [];

        $fields = [];
        foreach ($raw as $r => $row)
        {
            $filters = [];
            if (isset($row['filters']) && is_array($row['filters']))
            {
                foreach ($row['filters'] as $k => $f)
                {
                    $method = $f['method'] ?? 'dropdown';
                    $field_key = $f['key'] ?? '';
                    $buttons_multiple = !empty($f['buttons_multiple']);

                    // ACF dropdown fields do not support button rendering. Keep
                    // saved filters aligned with the frontend fallback.
                    if ($helper->get_type_by_key($field_key) === 'acf_dropdown' && $method === 'buttons') $method = $buttons_multiple ? 'checkboxes' : 'radio';

                    // Filter Params
                    $keys = ['sf-' . $field_key];
                    $values = [];

                    $type = $helper->get_type_by_key($field_key);
                    switch ($type)
                    {
                        case 'taxonomy':

                            if ($method === 'dropdown-multiple' || $method === 'checkboxes' || ($method === 'buttons' && $buttons_multiple)) $keys = ['sf-' . $field_key . '[]'];

                            $controller = new LSD_API_Controllers_Taxonomies();

                            $terms = $controller->hierarchy($field_key, 0, $f['hide_empty'] ?? null);
                            $values = LSD_API_Resources_Taxonomy::collection($terms);

                            break;

                        case 'text':
                        case 'date':
                        case 'time':
                        case 'datetime':

                            (strpos($field_key, 'acf_email_') === 0) ? $acf_key = substr($field_key, strlen('acf_email_')) : ((strpos($field_key, 'acf_text_') === 0) ? $acf_key = substr($field_key, strlen('acf_text_')) : $acf_key = '');
                            $keys = (strpos($field_key, 'acf-') !== false)
                                ? ['sf-acf-' . $acf_key . '-atx']
                                : ['sf-' . $field_key . '-lk'];

                            break;

                        case 'number':

                            $keys = ['sf-' . $field_key . '-eq'];
                            if ($method === 'dropdown-plus') $keys = ['sf-' . $field_key . '-grq'];
                            else if ($method === 'range')
                            {
                                $keys = [
                                    'sf-att-' . $field_key . '-grb-min',
                                    'sf-att-' . $field_key . '-grb-max',
                                ];
                            }

                            $values = $helper->get_terms($f, true);

                            break;

                        case 'dropdown':
                        case 'checkbox':
                        case 'radio':

                            $keys = ['sf-' . $field_key . '-eq'];
                            if ($method === 'dropdown-multiple' || $method === 'checkboxes' || ($method === 'buttons' && $buttons_multiple)) $keys = ['sf-' . $field_key . '-in[]'];

                            $values = $helper->get_terms($f, true);

                            break;

                        case 'acf_dropdown':

                            $acf_key = '';
                            if (strpos($field_key, 'acf_select_') === 0) $acf_key = substr($field_key, strlen('acf_select_'));
                            else if (strpos($field_key, 'acf_radio_') === 0) $acf_key = substr($field_key, strlen('acf_radio_'));
                            else if (strpos($field_key, 'acf_checkbox_') === 0) $acf_key = substr($field_key, strlen('acf_checkbox_'));
                            else if (strpos($field_key, 'acf_true_false_') === 0) $acf_key = substr($field_key, strlen('acf_true_false_'));

                            $keys = ['sf-acf-' . $acf_key . '-dra'];
                            if ($method === 'dropdown-multiple' || $method === 'checkboxes') $keys = ['sf-acf-' . $acf_key . '-drm[]'];

                            $values = $helper->acf_field_data($acf_key, 'choices')[0] ?? [];

                            break;

                        case 'acf_range':

                            (strpos($field_key, 'acf_range_') === 0) ? $acf_key = substr($field_key, strlen('acf_range_')) : $acf_key = '';

                            $keys[] = [
                                'sf-acf-' . $acf_key . '_min',
                                'sf-acf-' . $acf_key . '_max',
                            ];

                            break;

                        case 'price':

                            if (!LSD_Components::pricing()) continue 2;

                            if ($method === 'dropdown-plus') $keys = ['sf-att-' . $field_key . '-grq'];
                            else if ($method === 'mm-input' || $method === 'range')
                            {
                                $keys = [
                                    'sf-att-' . $field_key . '-bt-min',
                                    'sf-att-' . $field_key . '-bt-max',
                                ];
                            }

                            break;

                        case 'address':

                            if (!LSD_Components::map()) continue 2;

                            $keys = ['sf-att-' . $field_key . '-lk'];
                            break;

                        case 'review_rate':

                            $keys = ['sf-att-' . $field_key . '-grq'];
                            $values = [0, 1, 2, 3, 4];
                            break;
                    }

                    $f['request'] = LSD_API_SearchRequest::resolve($f, $type, $method, (int) $id, $values);
                    $f['key'] = $keys;
                    $f['values'] = $values;

                    $filters[$k] = $f;
                }
            }

            $fields[$r] = [
                'type' => $row['type'] ?? 'row',
            ];

            if (isset($row['filters'])) $fields[$r]['filters'] = $filters;
            if (isset($row['buttons'])) $fields[$r]['buttons'] = $row['buttons'];
            if (isset($row['clear'])) $fields[$r]['clear'] = $row['clear'];
            if (class_exists(\LSDPACMA\Base::class) && isset($row['filters_button'])) $fields[$r]['filters_button'] = $row['filters_button'];
        }

        return apply_filters('lsd_api_resource_searchmodule', [
            'id' => $search->ID,
            'data' => [
                'ID' => $search->ID,
                'title' => get_the_title($search),
                'status' => $search->post_status,
                'form' => [
                    'style' => $form['style'] ?? null,
                    'page' => isset($form['page']) && trim($form['page']) ? get_permalink($form['page']) : null,
                    'shortcode' => $form['shortcode'] ?? null,
                ],
                'fields' => $fields,
            ],
        ], $id);
    }

    public static function collection($ids): array
    {
        $items = [];
        foreach ($ids as $id) $items[] = self::get($id);

        return $items;
    }
}
