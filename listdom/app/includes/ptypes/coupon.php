<?php

class LSD_PTypes_Coupon extends LSD_PTypes
{
    public $PT;

    public function __construct()
    {
        $this->PT = LSD_Base::PTYPE_COUPON;
    }

    public function init()
    {
        add_action('init', [$this, 'register_post_type']);

        add_filter('manage_' . $this->PT . '_posts_columns', [$this, 'columns']);
        add_action('manage_' . $this->PT . '_posts_custom_column', [$this, 'columns_content'], 10, 2);
        add_filter('manage_edit-' . $this->PT . '_sortable_columns', [$this, 'columns_sortable']);
        add_action('pre_get_posts', [$this, 'sort']);

        add_action('add_meta_boxes', [$this, 'register_metaboxes'], 10, 2);
        add_action('save_post', [$this, 'save'], 10, 2);
    }

    public function register_post_type()
    {
        $args = [
            'labels' => [
                'name' => esc_html__('Coupons', 'listdom'),
                'singular_name' => esc_html__('Coupon', 'listdom'),
                'add_new' => esc_html__('Add Coupon', 'listdom'),
                'add_new_item' => esc_html__('Add New Coupon', 'listdom'),
                'edit_item' => esc_html__('Edit Coupon', 'listdom'),
                'new_item' => esc_html__('New Coupon', 'listdom'),
                'view_item' => esc_html__('View Coupon', 'listdom'),
                'view_items' => esc_html__('View Coupons', 'listdom'),
                'search_items' => esc_html__('Search Coupons', 'listdom'),
                'not_found' => esc_html__('No coupons found!', 'listdom'),
                'not_found_in_trash' => esc_html__('No coupons found in Trash!', 'listdom'),
                'all_items' => esc_html__('Coupons', 'listdom'),
                'archives' => esc_html__('Coupon Archives', 'listdom'),
            ],
            'public' => false,
            'has_archive' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'supports' => ['title'],
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

        register_post_type($this->PT, apply_filters('lsd_ptype_coupon_args', $args));
    }

    public function register_metaboxes()
    {
        add_meta_box('lsd_metabox_coupon', esc_html__('Coupon', 'listdom'), [$this, 'metabox_coupon'], $this->PT, 'normal', 'high');
    }

    public function metabox_coupon($post)
    {
        include $this->include_html_file('metaboxes/coupon/coupon.php', ['return_path' => true]);
    }

    public function save($post_id, $post)
    {
        if ($post->post_type !== $this->PT) return;
        if (!isset($_POST['_lsdnonce'])) return;

        if (!wp_verify_nonce(sanitize_text_field($_POST['_lsdnonce']), 'lsd_coupon_cpt')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $lsd = $_POST['lsd'] ?? [];
        array_walk_recursive($lsd, 'sanitize_text_field');

        $discount = isset($lsd['discount']) && $lsd['discount'] !== '' ? floatval($lsd['discount']) : 0;
        update_post_meta($post_id, 'lsd_discount', $discount);

        $deduction = isset($lsd['deduction']) && $lsd['deduction'] !== '' ? floatval($lsd['deduction']) : 0;
        update_post_meta($post_id, 'lsd_deduction', $deduction);

        $usage_limit = isset($lsd['usage_limit']) && $lsd['usage_limit'] !== '' ? intval($lsd['usage_limit']) : '';
        update_post_meta($post_id, 'lsd_usage_limit', $usage_limit);
    }

    public function columns($columns)
    {
        $date = $columns['date'];
        unset($columns['date']);

        $columns['discount'] = esc_html__('Discount', 'listdom');
        $columns['deduction'] = esc_html__('Deduction', 'listdom');
        $columns['usage_limit'] = esc_html__('Usage Limit', 'listdom');
        $columns['used_times'] = esc_html__('Used Times', 'listdom');
        $columns['date'] = $date;

        return $columns;
    }

    public function columns_sortable($columns)
    {
        $columns['discount'] = 'lsd_discount';
        $columns['deduction'] = 'lsd_deduction';
        $columns['usage_limit'] = 'lsd_usage_limit';

        return $columns;
    }

    public function columns_content($column, $post_id)
    {
        if ($column === 'discount')
        {
            $discount = get_post_meta($post_id, 'lsd_discount', true);
            if ($discount === '') $discount = 0;

            echo esc_html($discount) . '%';
        }
        else if ($column === 'deduction')
        {
            $deduction = get_post_meta($post_id, 'lsd_deduction', true);
            if ($deduction === '') $deduction = 0;

            echo $this->render_price($deduction, LSD_Options::currency(), false, false);
        }
        else if ($column === 'usage_limit')
        {
            $limit = get_post_meta($post_id, 'lsd_usage_limit', true);
            echo esc_html($limit === '' ? __('Unlimited', 'listdom') : $limit);
        }
        // @todo
        else if ($column === 'used_times') echo 0;
    }

    public function sort(WP_Query $query)
    {
        if (!is_admin() || $query->get('post_type') !== $this->PT || !$query->is_main_query()) return;

        $orderby = $query->get('orderby');
        if (in_array($orderby, ['lsd_discount', 'lsd_deduction', 'lsd_usage_limit']))
        {
            $query->set('meta_key', $orderby);
            $query->set('orderby', 'meta_value_num');
        }
    }
}
