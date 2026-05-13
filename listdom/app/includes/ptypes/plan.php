<?php

class LSD_PTypes_Plan extends LSD_PTypes
{
    public $PT;

    public function __construct()
    {
        $this->PT = LSD_Base::PTYPE_PLAN;
    }

    public function init()
    {
        add_action('init', [$this, 'register_post_type']);

        add_action('add_meta_boxes', [$this, 'register_metaboxes'], 10, 2);
        add_action('save_post', [$this, 'save'], 10, 2);
        add_action('wp_ajax_lsd_plan_new_tier', [$this, 'ajax_new_tier']);

        add_filter('manage_' . $this->PT . '_posts_columns', [$this, 'columns']);
        add_action('manage_' . $this->PT . '_posts_custom_column', [$this, 'columns_content'], 10, 2);
    }

    public function register_post_type()
    {
        $args = [
            'labels' => [
                'name' => esc_html__('Plans', 'listdom'),
                'singular_name' => esc_html__('Plan', 'listdom'),
                'add_new' => esc_html__('Add Plan', 'listdom'),
                'add_new_item' => esc_html__('Add New Plan', 'listdom'),
                'edit_item' => esc_html__('Edit Plan', 'listdom'),
                'new_item' => esc_html__('New Plan', 'listdom'),
                'view_item' => esc_html__('View Plan', 'listdom'),
                'view_items' => esc_html__('View Plans', 'listdom'),
                'search_items' => esc_html__('Search Plans', 'listdom'),
                'not_found' => esc_html__('No plans found!', 'listdom'),
                'not_found_in_trash' => esc_html__('No plans found in Trash!', 'listdom'),
                'all_items' => esc_html__('Plans', 'listdom'),
                'archives' => esc_html__('Plan Archives', 'listdom'),
            ],
            'public' => false,
            'has_archive' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'supports' => ['title', 'editor'],
            'capabilities' => [
                'edit_post' => 'manage_options',
                'read_post' => 'manage_options',
                'delete_post' => 'manage_options',
                'edit_posts' => 'manage_options',
                'edit_others_posts' => 'manage_options',
                'delete_posts' => 'manage_options',
                'publish_posts' => 'manage_options',
                'read_private_posts' => 'manage_options',
            ],
        ];

        register_post_type($this->PT, apply_filters('lsd_ptype_plan_args', $args));
    }

    public function register_metaboxes()
    {
        add_meta_box('lsd_metabox_plan_tiers', esc_html__('Tiers', 'listdom'), [$this, 'metabox_tiers'], $this->PT, 'normal', 'high');
    }

    public function metabox_tiers($post)
    {
        include $this->include_html_file('metaboxes/plan/tiers.php', ['return_path' => true]);
    }

    public function save($post_id, $post)
    {
        if ($post->post_type !== $this->PT) return;
        if (!isset($_POST['_lsdnonce'])) return;
        if (!wp_verify_nonce(sanitize_text_field($_POST['_lsdnonce']), 'lsd_plan_cpt')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $tiers = $_POST['lsd_tiers'] ?? [];
        $sanitized = [];
        $default = -1;
        $recurring_available = lsd_payment()->is_recurring_available();

        if (is_array($tiers))
        {
            foreach ($tiers as $tier)
            {
                $id = sanitize_text_field($tier['id'] ?? '');
                if ($id === '') $id = wp_generate_uuid4();

                $name = sanitize_text_field($tier['name'] ?? '');
                $price = isset($tier['price']) ? floatval($tier['price']) : 0;
                $expiry = isset($tier['expiry']) && trim($tier['expiry']) !== '' ? (int) $tier['expiry'] : '';
                $type = $tier['type'] === 'recurring' ? 'recurring' : 'one_time';
                $is_default = isset($tier['default']) && (int) $tier['default'];

                if ($type === 'recurring')
                {
                    if (!$recurring_available) $type = 'one_time';
                    else if ($expiry === '') continue;
                }

                if ($is_default) $default = count($sanitized);

                $sanitized[] = [
                    'id' => $id,
                    'name' => $name,
                    'price' => $price,
                    'expiry' => $expiry,
                    'type' => $type,
                    'default' => 0,
                ];
            }

            if ($default > -1 && isset($sanitized[$default])) $sanitized[$default]['default'] = 1;
        }

        update_post_meta($post_id, 'lsd_tiers', $sanitized);
    }

    public function columns($columns)
    {
        $date = $columns['date'];
        unset($columns['date']);

        $columns['price'] = esc_html__('Price', 'listdom');
        $columns['date'] = $date;

        return $columns;
    }

    public function columns_content($column, $post_id)
    {
        if ($column === 'price')
        {
            $tiers = (new LSD_Payments_Plan($post_id))->get_tiers();

            $lines = [];
            foreach ($tiers as $tier) $lines[] = $tier->get_price_frequency_html();

            echo LSD_Kses::element(implode('<br>', $lines));
        }
    }

    public function ajax_new_tier()
    {
        check_ajax_referer('lsd_plan_cpt', '_lsdnonce');

        $index = isset($_POST['index']) ? (int) $_POST['index'] : 0;
        $html = $this->include_html_file('metaboxes/plan/tier.php', [
            'return_output' => true,
            'parameters' => [
                'index' => $index,
                'tier' => [],
            ],
        ]);

        $this->response(['success' => 1, 'html' => $html]);
    }
}
