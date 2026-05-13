<?php

class LSD_PTypes_Recurring extends LSD_PTypes
{
    public $PT;

    public function __construct()
    {
        $this->PT = LSD_Base::PTYPE_RECURRING;
    }

    public function init()
    {
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'register_metaboxes']);

        add_filter('manage_' . $this->PT . '_posts_columns', [$this, 'columns']);
        add_filter('manage_edit-' . $this->PT . '_sortable_columns', [$this, 'columns_sortable']);
        add_action('manage_' . $this->PT . '_posts_custom_column', [$this, 'columns_content'], 10, 2);

        add_action('post_submitbox_start', [$this, 'status_dropdown']);
        add_action('save_post_' . $this->PT, [$this, 'save'], 10, 2);

        add_filter('gettext', function ($translation, $text)
        {
            if ($text === 'Publish' && function_exists('get_current_screen'))
            {
                $screen = get_current_screen();
                if ($screen && $screen->post_type === $this->PT)
                {
                    return esc_html__('Save', 'listdom');
                }
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
                'name' => esc_html__('Recurring Payments', 'listdom'),
                'singular_name' => esc_html__('Recurring Payment', 'listdom'),
                'add_new' => esc_html__('Add Recurring Payment', 'listdom'),
                'add_new_item' => esc_html__('Add New Recurring Payment', 'listdom'),
                'edit_item' => esc_html__('Edit Recurring Payment', 'listdom'),
                'new_item' => esc_html__('New Recurring Payment', 'listdom'),
                'view_item' => esc_html__('View Recurring Payment', 'listdom'),
                'view_items' => esc_html__('View Recurring Payments', 'listdom'),
                'search_items' => esc_html__('Search Recurring Payments', 'listdom'),
                'not_found' => esc_html__('No recurring payments found!', 'listdom'),
                'not_found_in_trash' => esc_html__('No recurring payments found in Trash!', 'listdom'),
                'all_items' => esc_html__('Recurring Payments', 'listdom'),
                'archives' => esc_html__('Recurring Payment Archives', 'listdom'),
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
        ];

        register_post_type($this->PT, apply_filters('lsd_ptype_recurring_args', $args));
    }

    public function register_metaboxes()
    {
        add_meta_box(
            'lsd-recurring-orders',
            esc_html__('Orders', 'listdom'),
            [$this, 'metabox_orders'],
            $this->PT,
            'normal'
        );

        add_meta_box(
            'lsd-recurring-details',
            esc_html__('Details', 'listdom'),
            [$this, 'metabox_details'],
            $this->PT,
            'normal'
        );
    }

    public function metabox_orders($post)
    {
        include $this->include_html_file('metaboxes/recurring/orders.php', ['return_path' => true]);
    }

    public function metabox_details($post)
    {
        include $this->include_html_file('metaboxes/recurring/details.php', ['return_path' => true]);
    }

    public function status_dropdown()
    {
        global $post;
        if ($post->post_type !== $this->PT) return;

        include $this->include_html_file('metaboxes/recurring/status.php', ['return_path' => true]);
    }

    public function save($post_id, WP_Post $post)
    {
        if ($post->post_type !== $this->PT) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['lsd_recurring_status'])) return;

        $new_status = sanitize_text_field($_POST['lsd_recurring_status']);
        if ($new_status !== $post->post_status)
        {
            LSD_Payments_Recurrings::update_status($post_id, $new_status);
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
        $recurring = new LSD_Payments_Recurring($post_id);

        if ($column === 'items')
        {
            $lines = [];
            foreach ($recurring->get_items() as $item)
            {
                $plan_id = isset($item['plan_id']) ? (int) $item['plan_id'] : 0;
                $tier_id = isset($item['tier_id']) ? (string) $item['tier_id'] : '';

                $plan = new LSD_Payments_Plan($plan_id, $tier_id);
                $tier = $plan->get_tier();

                $line = $plan->get_name();
                if ($tier) $line .= ' - ' . $tier->get_name();

                $lines[] = $line;
            }

            if ($lines) echo esc_html(implode(', ', $lines));
        }
        else if ($column === 'total')
        {
            echo $this->render_price($recurring->get_total(), $recurring->get_currency(), false, false);
        }
        else if ($column === 'gateway')
        {
            $gateway = $recurring->get_gateway_instance();
            echo esc_html($gateway ? $gateway->name() : $recurring->get_gateway());
        }
        else if ($column === 'customer')
        {
            $user = $recurring->get_user();
            if ($user)
            {
                echo esc_html($user->display_name);
                if ($user->user_email) echo '<br>' . esc_html($user->user_email);
            }
        }
    }
}
