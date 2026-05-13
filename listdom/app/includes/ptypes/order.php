<?php

class LSD_PTypes_Order extends LSD_PTypes
{
    public $PT;

    public function __construct()
    {
        $this->PT = LSD_Base::PTYPE_ORDER;
    }

    public function init()
    {
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'register_metaboxes']);

        add_filter('manage_' . $this->PT . '_posts_columns', [$this, 'columns']);
        add_filter('manage_edit-' . $this->PT . '_sortable_columns', [$this, 'columns_sortable']);
        add_action('manage_' . $this->PT . '_posts_custom_column', [$this, 'columns_content'], 10, 2);

        add_action('restrict_manage_posts', [$this, 'add_filters']);
        add_filter('parse_query', [$this, 'apply_filters']);

        add_filter('bulk_actions-edit-' . $this->PT, [$this, 'bulk_add']);
        add_filter('handle_bulk_actions-edit-' . $this->PT, [$this, 'bulk_do'], 10, 3);
        add_action('admin_notices', [$this, 'bulk_notice']);

        add_filter('posts_join', [$this, 'search_join'], 10, 2);
        add_filter('posts_where', [$this, 'search_where'], 10, 2);
        add_filter('posts_distinct', [$this, 'search_distinct'], 10, 2);

        add_action('post_submitbox_start', [$this, 'status_dropdown']);
        add_action('save_post_' . $this->PT, [$this, 'save'], 10, 2);

        add_filter('gettext', function ($translation, $text)
        {
            if ($text === 'Publish' && function_exists('get_current_screen'))
            {
                $screen = get_current_screen();
                if ($screen && $screen->post_type === $this->PT)
                    return esc_html__('Save', 'listdom');
            }

            return $translation;
        }, 10, 2);

        add_action('add_meta_boxes_' . $this->PT, function ()
        {
            global $wp_meta_boxes;
            if (isset($wp_meta_boxes[$this->PT]['side']['core']['submitdiv']))
            {
                $wp_meta_boxes[$this->PT]['side']['core']['submitdiv']['title'] = esc_html__('Status', 'listdom');
            }
        }, 100);
    }

    public function register_post_type()
    {
        $args = [
            'labels' => [
                'name' => esc_html__('Orders', 'listdom'),
                'singular_name' => esc_html__('Order', 'listdom'),
                'add_new' => esc_html__('Add Order', 'listdom'),
                'add_new_item' => esc_html__('Add New Order', 'listdom'),
                'edit_item' => esc_html__('Edit Order', 'listdom'),
                'new_item' => esc_html__('New Order', 'listdom'),
                'view_item' => esc_html__('View Order', 'listdom'),
                'view_items' => esc_html__('View Orders', 'listdom'),
                'search_items' => esc_html__('Search Orders', 'listdom'),
                'not_found' => esc_html__('No orders found!', 'listdom'),
                'not_found_in_trash' => esc_html__('No orders found in Trash!', 'listdom'),
                'all_items' => esc_html__('Orders', 'listdom'),
                'archives' => esc_html__('Order Archives', 'listdom'),
            ],
            'public' => false,
            'has_archive' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'supports' => ['title'],
            'capabilities' => [
                'create_posts' => 'do_not_allow',
                'edit_post' => 'manage_options',
                'read_post' => 'manage_options',
                'delete_post' => 'manage_options',
                'edit_posts' => 'manage_options',
                'edit_others_posts' => 'manage_options',
                'delete_posts' => 'manage_options',
                'publish_posts' => 'manage_options',
                'read_private_posts' => 'manage_options',
            ],
            'menu_position' => 28,
        ];

        register_post_type($this->PT, apply_filters('lsd_ptype_order_args', $args));
    }

    public function register_metaboxes()
    {
        add_meta_box(
            'lsd-order-items',
            esc_html__('Items', 'listdom'),
            [$this, 'metabox_items'],
            $this->PT,
            'normal'
        );

        add_meta_box(
            'lsd-order-details',
            esc_html__('Details', 'listdom'),
            [$this, 'metabox_details'],
            $this->PT,
            'normal'
        );
    }

    public function metabox_items($post)
    {
        include $this->include_html_file('metaboxes/order/items.php', ['return_path' => true]);
    }

    public function metabox_details($post)
    {
        include $this->include_html_file('metaboxes/order/details.php', ['return_path' => true]);
    }

    public function status_dropdown()
    {
        global $post;
        if ($post->post_type !== $this->PT) return;

        include $this->include_html_file('metaboxes/order/status.php', ['return_path' => true]);
    }

    public function save($post_id, WP_Post $post)
    {
        if ($post->post_type !== $this->PT) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['lsd_order_status'])) return;

        $new_status = sanitize_text_field($_POST['lsd_order_status']);
        if ($new_status !== $post->post_status)
        {
            LSD_Payments_Orders::update_status($post_id, $new_status);
        }
    }

    public function columns($columns)
    {
        $date = $columns['date'];
        unset($columns['date']);

        $columns['items'] = esc_html__('Items', 'listdom');
        $columns['total'] = esc_html__('Amount', 'listdom');
        $columns['gateway'] = esc_html__('Gateway', 'listdom');
        $columns['customer'] = esc_html__('Customer', 'listdom');
        $columns['date'] = $date;

        return $columns;
    }

    public function columns_sortable($columns)
    {
        $columns['total'] = 'lsd_total';
        return $columns;
    }

    public function columns_content($column, $post_id)
    {
        $order = new LSD_Payments_Order($post_id);

        if ($column === 'items')
        {
            $lines = $order->get_lines();
            if ($lines) echo implode('<br>', $lines);
        }
        else if ($column === 'total')
        {
            echo $this->render_price($order->get_total(), LSD_Options::currency(), false, false);
        }
        else if ($column === 'gateway')
        {
            $key = $order->get_gateway();
            if ($key === null || $key === '')
            {
                echo esc_html__('N/A', 'listdom');
                return;
            }

            $gateway = LSD_Payments::gateway($key);

            echo esc_html($gateway ? $gateway->name() : $key);
        }
        else if ($column === 'customer')
        {
            $name = $order->get_name();
            $email = $order->get_email();

            echo esc_html($name);
            if ($email) echo '<br>' . esc_html($email);
        }
    }

    public function add_filters($post_type)
    {
        if ($post_type !== $this->PT) return;

        // Plans
        $plans = get_posts([
            'post_type' => LSD_Base::PTYPE_PLAN,
            'post_status' => 'any',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $plan_options = [];
        foreach ($plans as $plan) $plan_options[$plan->ID] = $plan->post_title;

        echo LSD_Form::select([
            'id' => 'lsd_order_filter_plan',
            'name' => 'lsd_plan',
            'options' => $plan_options,
            'value' => isset($_GET['lsd_plan']) ? sanitize_text_field($_GET['lsd_plan']) : '',
            'empty_label' => esc_html__('All Plans', 'listdom'),
            'show_empty' => true,
        ]);

        // Gateways
        $gateway_options = [];
        foreach (LSD_Payments::gateways() as $gateway)
            $gateway_options[$gateway->key()] = $gateway->name();

        echo LSD_Form::select([
            'id' => 'lsd_order_filter_gateway',
            'name' => 'lsd_gateway',
            'options' => $gateway_options,
            'value' => isset($_GET['lsd_gateway']) ? sanitize_text_field($_GET['lsd_gateway']) : '',
            'empty_label' => esc_html__('All Gateways', 'listdom'),
            'show_empty' => true,
        ]);
    }

    public function apply_filters($query)
    {
        global $pagenow, $typenow;
        if ($pagenow !== 'edit.php' || $typenow !== $this->PT) return;
        if (!$query->is_main_query()) return;

        $meta_query = [];

        if (isset($_GET['lsd_gateway']) && $_GET['lsd_gateway'] !== '')
        {
            $meta_query[] = [
                'key' => 'lsd_gateway',
                'value' => sanitize_text_field($_GET['lsd_gateway']),
            ];
        }

        if (isset($_GET['lsd_plan']) && $_GET['lsd_plan'] !== '')
        {
            $meta_query[] = [
                'key' => 'lsd_items',
                'value' => '"plan_id";i:' . intval($_GET['lsd_plan']),
                'compare' => 'LIKE',
            ];
        }

        if ($meta_query)
        {
            if (count($meta_query) > 1) $meta_query['relation'] = 'AND';
            $query->set('meta_query', $meta_query);
        }

        if ($query->get('orderby') === 'lsd_total')
        {
            $query->set('meta_key', 'lsd_total');
            $query->set('orderby', 'meta_value_num');
        }
    }

    public function bulk_add($actions)
    {
        unset($actions['edit']);

        $actions['lsd_change_status_' . LSD_Payments::STATUS_PENDING] = esc_html__('Change status to pending', 'listdom');
        $actions['lsd_change_status_' . LSD_Payments::STATUS_ON_HOLD] = esc_html__('Change status to on-hold', 'listdom');
        $actions['lsd_change_status_' . LSD_Payments::STATUS_COMPLETED] = esc_html__('Change status to completed', 'listdom');
        $actions['lsd_change_status_' . LSD_Payments::STATUS_CANCELED] = esc_html__('Change status to canceled', 'listdom');

        return $actions;
    }

    public function bulk_do($redirect_to, $action, $post_ids): string
    {
        if (strpos($action, 'lsd_change_status_') !== 0) return $redirect_to;

        $status = str_replace('lsd_change_status_', '', $action);
        $changed = 0;

        foreach ($post_ids as $post_id)
        {
            $post = get_post($post_id);
            if ($status !== $post->post_status)
            {
                LSD_Payments_Orders::update_status($post_id, $status);
                $changed++;
            }
        }

        return add_query_arg([
            'lsd_status_changed' => $changed,
            'lsd_new_status' => $status,
        ], $redirect_to);
    }

    public function bulk_notice()
    {
        if (!isset($_REQUEST['lsd_status_changed'], $_REQUEST['lsd_new_status'])) return;

        $screen = get_current_screen();
        if ($screen->post_type !== $this->PT) return;

        $count = (int) $_REQUEST['lsd_status_changed'];
        $key = sanitize_text_field($_REQUEST['lsd_new_status']);

        $statuses = (new LSD_Payments_Statuses())->statuses();
        $label = $statuses[$key]['args']['label'] ?? $key;

        echo '<div id="message" class="updated notice notice-success is-dismissible"><p>' .
            sprintf(esc_html__("%1\$d orders updated to %2\$s.", 'listdom'), $count, esc_html($label)) .
        '</p></div>';
    }

    protected function is_order_search(WP_Query $query): bool
    {
        return is_admin() && $query->is_main_query() && $query->get('post_type') === $this->PT && $query->get('s');
    }

    public function search_join(string $join, WP_Query $query): string
    {
        if (!$this->is_order_search($query)) return $join;

        global $wpdb;
        return $join . ' LEFT JOIN ' . $wpdb->postmeta . ' lsd_pm ON ' . $wpdb->posts . '.ID = lsd_pm.post_id ';
    }

    public function search_where($where, WP_Query $query)
    {
        if (!$this->is_order_search($query)) return $where;

        global $wpdb;
        $keys = ['lsd_coupon', 'lsd_name', 'lsd_email', 'lsd_message'];
        $keys_list = "'" . implode("','", $keys) . "'";

        $pattern = '/\(\s*' . $wpdb->posts . '\.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/';
        $replacement = '(' . $wpdb->posts . '.post_title LIKE $1) OR (lsd_pm.meta_key IN (' . $keys_list . ') AND lsd_pm.meta_value LIKE $1)';

        return preg_replace($pattern, $replacement, $where);
    }

    public function search_distinct(string $distinct, WP_Query $query): string
    {
        if (!$this->is_order_search($query)) return $distinct;
        return 'DISTINCT';
    }
}
