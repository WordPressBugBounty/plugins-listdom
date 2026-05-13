<?php

class LSD_API_Controllers_Taxonomies extends LSD_API_Controller
{
    public function get(WP_REST_Request $request): WP_REST_Response
    {
        $taxonomies = [
            [
                'key' => LSD_Base::TAX_CATEGORY,
                'singular' => esc_html(lsd_t_label(LSD_Base::TAX_CATEGORY)),
                'plural' => esc_html(lsd_t_label(LSD_Base::TAX_CATEGORY, 'plural')),
            ],
            [
                'key' => LSD_Base::TAX_LOCATION,
                'singular' => esc_html(lsd_t_label(LSD_Base::TAX_LOCATION)),
                'plural' => esc_html(lsd_t_label(LSD_Base::TAX_LOCATION, 'plural')),
            ],
            [
                'key' => LSD_Base::TAX_TAG,
                'singular' => esc_html(lsd_t_label(LSD_Base::TAX_TAG)),
                'plural' => esc_html(lsd_t_label(LSD_Base::TAX_TAG, 'plural')),
            ],
            [
                'key' => LSD_Base::TAX_FEATURE,
                'singular' => esc_html(lsd_t_label(LSD_Base::TAX_FEATURE)),
                'plural' => esc_html(lsd_t_label(LSD_Base::TAX_FEATURE, 'plural')),
            ],
            [
                'key' => LSD_Base::TAX_LABEL,
                'singular' => esc_html(lsd_t_label(LSD_Base::TAX_LABEL)),
                'plural' => esc_html(lsd_t_label(LSD_Base::TAX_LABEL, 'plural')),
            ],
            [
                'key' => LSD_Base::TAX_ATTRIBUTE,
                'singular' => esc_html(lsd_t_label(LSD_Base::TAX_ATTRIBUTE)),
                'plural' => esc_html(lsd_t_label(LSD_Base::TAX_ATTRIBUTE, 'plural')),
            ],
        ];

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'taxonomies' => $taxonomies,
            ],
            'status' => 200,
        ]);
    }

    public function terms(WP_REST_Request $request): WP_REST_Response
    {
        $vars = $request->get_params();

        $taxonomy = $vars['taxonomy'] ?? LSD_Base::TAX_CATEGORY;
        $hide_empty = $vars['hide_empty'] ?? false;
        $parent = $vars['parent'] ?? 0;
        $orderby = $vars['orderby'] ?? 'name';
        $order = $vars['order'] ?? 'ASC';

        $terms = $this->hierarchy($taxonomy, $parent, $hide_empty, $orderby, $order);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'terms' => LSD_API_Resources_Taxonomy::collection($terms),
            ],
            'status' => 200,
        ]);
    }

    public function hierarchy($taxonomy, $parent = 0, $hide_empty = false, $orderby = 'name', $order = 'ASC'): array
    {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'parent' => $parent,
            'hide_empty' => $hide_empty,
            'orderby' => $orderby,
            'order' => $order,
        ]);

        $ids = [];

        // Return Empty
        if (is_wp_error($terms)) return $ids;

        $i = 0;
        foreach ($terms as $term)
        {
            $ids[$i] = [
                'id' => $term->term_id,
            ];

            $children = get_term_children($term->term_id, $taxonomy);
            if (is_array($children) && count($children))
            {
                $ids[$i]['childs'] = $this->hierarchy($taxonomy, $term->term_id, $hide_empty, $orderby, $order);
            }

            $i++;
        }

        return $ids;
    }
}
