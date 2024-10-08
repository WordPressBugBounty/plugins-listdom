<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Search Module Resource Class.
 *
 * @class LSD_API_Resources_SearchModule
 * @version    1.0.0
 */
class LSD_API_Resources_SearchModule extends LSD_API_Resource
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
            if (isset($row['filters']) and is_array($row['filters']))
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

                            if ($method == 'dropdown-multiple' || $method == 'checkboxes') $keys = ['sf-' . $f['key'] . '[]'];

                            $controller = new LSD_API_Controllers_Taxonomies();

                            $terms = $controller->hierarchy($f['key'], 0, $f['hide_empty'] ?? null);
                            $values = LSD_API_Resources_Taxonomy::collection($terms);

                            break;

                        case 'text':

                            $keys = ['sf-' . $f['key'] . '-lk'];
                            break;

                        case 'number':

                            $keys = ['sf-' . $f['key'] . '-eq'];
                            if ($method == 'dropdown-plus') $keys = ['sf-' . $f['key'] . '-grq'];

                            $values = $helper->get_terms($f, true);

                            break;

                        case 'dropdown':

                            $keys = ['sf-' . $f['key'] . '-eq'];
                            if ($method == 'dropdown-multiple' or $method == 'checkboxes') $keys = ['sf-' . $f['key'] . '-in[]'];

                            $values = $helper->get_terms($f, true);

                            break;

                        case 'price':

                            if ($method == 'dropdown-plus') $keys = ['sf-att-' . $f['key'] . '-grq'];
                            else if ($method == 'mm-input')
                            {
                                $keys = [
                                    'sf-att-' . $f['key'] . '-bt-min',
                                    'sf-att-' . $f['key'] . '-bt-max',
                                ];
                            }

                            break;

                        case 'address':

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
