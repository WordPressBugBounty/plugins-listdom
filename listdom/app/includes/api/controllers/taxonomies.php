<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Taxonomies Controller Class.
 *
 * @class LSD_API_Controllers_Taxonomies
 * @version    1.0.0
 */
class LSD_API_Controllers_Taxonomies extends LSD_API_Controller
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function get(WP_REST_Request $request)
    {
        $taxonomies = [
            [
                'key' => LSD_Base::TAX_CATEGORY,
                'singular' => esc_html__('Category', 'listdom'),
                'plural' => esc_html__('Categories', 'listdom'),
            ],
            [
                'key' => LSD_Base::TAX_LOCATION,
                'singular' => esc_html__('Location', 'listdom'),
                'plural' => esc_html__('Locations', 'listdom'),
            ],
            [
                'key' => LSD_Base::TAX_TAG,
                'singular' => esc_html__('Tag', 'listdom'),
                'plural' => esc_html__('Tags', 'listdom'),
            ],
            [
                'key' => LSD_Base::TAX_FEATURE,
                'singular' => esc_html__('Feature', 'listdom'),
                'plural' => esc_html__('Features', 'listdom'),
            ],
            [
                'key' => LSD_Base::TAX_LABEL,
                'singular' => esc_html__('Label', 'listdom'),
                'plural' => esc_html__('Labels', 'listdom'),
            ],
            [
                'key' => LSD_Base::TAX_ATTRIBUTE,
                'singular' => esc_html__('Attribute', 'listdom'),
                'plural' => esc_html__('Attributes', 'listdom'),
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

    public function terms(WP_REST_Request $request)
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

    public function hierarchy($taxonomy, $parent = 0, $hide_empty = false, $orderby = 'name', $order = 'ASC')
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
