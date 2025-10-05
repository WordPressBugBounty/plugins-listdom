<?php

class LSD_Dashboard_Taxonomies_Terms extends LSD_Base
{
    public function init()
    {
        add_action('wp_ajax_lsd_dashboard_new_term', [$this, 'create']);
        add_action('wp_ajax_nopriv_lsd_dashboard_new_term', [$this, 'create']);
    }

    /**
     * Display taxonomy form
     */
    public function display($args = [])
    {
        ob_start();
        include lsd_template('dashboard/taxonomies/terms.php');
        return ob_get_clean();
    }

    public function create()
    {
        check_ajax_referer('lsd_dashboard_new_term', '_wpnonce');

        $taxonomy = $_POST['taxonomy'] ?? self::TAX_CATEGORY;
        $term_name = sanitize_text_field($_POST['term_name'] ?? '');

        // Basic term args
        $args = ['description' => sanitize_text_field($_POST['term_description'] ?? '')];

        // Optional parent
        if (!empty($_POST['term_parent'])) $args['parent'] = (int) $_POST['term_parent'];

        // Insert term
        $result = wp_insert_term($term_name, $taxonomy, $args);

        if (is_wp_error($result)) $this->response(['success' => 0, 'message' => $result->get_error_message()]);

        // Term meta
        $term_id = $result['term_id'];

        $term_color = sanitize_hex_color($_POST['term_color'] ?? '');
        $term_icon = sanitize_text_field($_POST['term_icon'] ?? '');

        if (!empty($term_color)) add_term_meta($term_id, 'lsd_color', $term_color, true);
        if (!empty($term_icon)) add_term_meta($term_id, 'lsd_icon', $term_icon, true);

        $this->response([
            'success' => 1,
            'message' => esc_html__('Term successfully added.', 'listdom'),
            'data' => [
                'term_id' => $term_id,
                'term_name' => $term_name,
                'taxonomy' => $taxonomy,
            ],
        ]);
    }

    public static function taxonomy_fields($taxonomy)
    {
        $fields = [
            self::TAX_CATEGORY => ['icon', 'color'],
            self::TAX_LOCATION => [],
            self::TAX_TAG => [],
            self::TAX_FEATURE => ['icon'],
            self::TAX_LABEL => ['color'],
        ];

        return $fields[$taxonomy] ?? [];
    }
}
