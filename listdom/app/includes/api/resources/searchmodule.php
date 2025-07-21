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

                    // Filter Params
                    $keys = ['sf-' . $f['key']];
                    $values = [];

                    $type = $helper->get_type_by_key($f['key']);
                    switch ($type)
                    {
                        case 'taxonomy':

                            if ($method === 'dropdown-multiple' || $method === 'checkboxes') $keys = ['sf-' . $f['key'] . '[]'];

                            $controller = new LSD_API_Controllers_Taxonomies();

                            $terms = $controller->hierarchy($f['key'], 0, $f['hide_empty'] ?? null);
                            $values = LSD_API_Resources_Taxonomy::collection($terms);

                            break;

                        case 'text':

                            (strpos($f['key'], 'acf_email_') === 0) ? $acf_key = substr($f['key'], strlen('acf_email_')) : ((strpos($f['key'], 'acf_text_') === 0) ? $acf_key = substr($f['key'], strlen('acf_text_')) : $acf_key = '');
                            $keys = (strpos($f['key'], 'acf-') !== false)
                                ? ['sf-acf-' . $acf_key . '-atx']
                                : ['sf-' . $f['key'] . '-lk'];

                            break;

                        case 'number':

                            $keys = ['sf-' . $f['key'] . '-eq'];
                            if ($method === 'dropdown-plus') $keys = ['sf-' . $f['key'] . '-grq'];
                            else if ($method === 'range')
                            {
                                $keys = [
                                    'sf-att-' . $f['key'] . '-grb-min',
                                    'sf-att-' . $f['key'] . '-grb-max',
                                ];
                            }

                            $values = $helper->get_terms($f, true);

                            break;

                        case 'dropdown':

                            $keys = ['sf-' . $f['key'] . '-eq'];
                            if ($method === 'dropdown-multiple' || $method === 'checkboxes') $keys = ['sf-' . $f['key'] . '-in[]'];

                            $values = $helper->get_terms($f, true);

                            break;

                        case 'acf_dropdown':

                            $acf_key = '';
                            if (strpos($f['key'], 'acf_select_') === 0) $acf_key = substr($f['key'], strlen('acf_select_'));
                            else if (strpos($f['key'], 'acf_radio_') === 0) $acf_key = substr($f['key'], strlen('acf_radio_'));
                            else if (strpos($f['key'], 'acf_checkbox_') === 0) $acf_key = substr($f['key'], strlen('acf_checkbox_'));
                            else if (strpos($f['key'], 'acf_true_false_') === 0) $acf_key = substr($f['key'], strlen('acf_true_false_'));

                            $keys = ['sf-acf-' . $acf_key . '-dra'];
                            if ($method === 'dropdown-multiple' || $method === 'checkboxes') $keys = ['sf-acf-' . $acf_key . '-drm[]'];

                            $values = $helper->acf_field_data($acf_key, 'choices')[0] ?? [];

                            break;

                        case 'acf_range':

                            (strpos($f['key'], 'acf_range_') === 0) ? $acf_key = substr($f['key'], strlen('acf_range_')) : $acf_key = '';

                            $keys[] = [
                                'sf-acf-' . $acf_key . '_min',
                                'sf-acf-' . $acf_key . '_max',
                            ];

                            break;

                        case 'price':

                            if (!LSD_Components::pricing()) continue 2;

                            if ($method === 'dropdown-plus') $keys = ['sf-att-' . $f['key'] . '-grq'];
                            else if ($method === 'mm-input' || $method === 'range')
                            {
                                $keys = [
                                    'sf-att-' . $f['key'] . '-bt-min',
                                    'sf-att-' . $f['key'] . '-bt-max',
                                ];
                            }

                            break;

                        case 'address':

                            if (!LSD_Components::map()) continue 2;

                            $keys = ['sf-att-' . $f['key'] . '-lk'];
                            break;
                    }

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
