<?php

class LSD_Dashboard_Payments extends LSD_Base
{
    public const MODE = 'payments-billing';
    public const SECTION_QUERY_VAR = 'payment_section';
    public const DETAIL_SECTION = 'details';
    public const SUBSCRIPTIONS_STATUS_QUERY_VAR = 'subscriptions_status';
    public const SUBSCRIPTIONS_SEARCH_QUERY_VAR = 'subscriptions_search';
    public const ORDERS_STATUS_QUERY_VAR = 'orders_status';
    public const ORDERS_SEARCH_QUERY_VAR = 'orders_search';

    protected array $order_item_recurring_map_cache = [];

    public function init()
    {
        add_filter('lsd_dashboard_menus', [$this, 'menu'], 15, 2);
        add_filter('lsd_dashboard_modes', [$this, 'dashboard'], 15, 2);
        add_action('wp_ajax_lsd_dashboard_payments_load_more', [$this, 'load_more']);
        add_action('wp_ajax_lsd_dashboard_payments_disable_autorenew', [$this, 'disable_autorenew']);
        add_action('wp_ajax_lsd_dashboard_payments_activate_autorenew', [$this, 'activate_autorenew']);
    }

    public function menu(array $menus, LSD_Shortcodes_Dashboard $dashboard): array
    {
        if (!$this->is_available() || !get_current_user_id()) return $menus;

        $logout = $menus['logout'] ?? null;
        if ($logout) unset($menus['logout']);

        $menus[self::MODE] = [
            'label' => esc_html__('Payments and Billing', 'listdom'),
            'default_label' => esc_html__('Payments and Billing', 'listdom'),
            'id' => 'lsd_dashboard_menus_payments_billing',
            'url' => $dashboard->add_qs_vars([
                'mode' => self::MODE,
                self::SECTION_QUERY_VAR => 'overview',
            ], $dashboard->url),
            'icon' => 'fas fa-file-invoice-dollar',
        ];

        if ($logout) $menus['logout'] = $logout;

        return $menus;
    }

    public function dashboard(string $output, LSD_Shortcodes_Dashboard $dashboard): string
    {
        if ($dashboard->mode !== self::MODE) return $output;
        if (!get_current_user_id()) return $dashboard->auth();

        $this->maybe_save_billing_profile();

        ob_start();
        include lsd_template('dashboard/payments.php');
        return ob_get_clean();
    }

    public function is_available(): bool
    {
        return LSD_Payments_Engine::instance()->listdom();
    }

    public function get_section(): string
    {
        $section = isset($_GET[self::SECTION_QUERY_VAR]) ? sanitize_key($_GET[self::SECTION_QUERY_VAR]) : 'overview';
        if ($section === self::DETAIL_SECTION) return $section;

        $sections = $this->get_sections();

        return array_key_exists($section, $sections) ? $section : 'overview';
    }

    public function get_sections(): array
    {
        return [
            'overview' => [
                'label' => esc_html__('Overview', 'listdom'),
                'icon' => 'fa-solid fa-border-all',
            ],
            'subscriptions' => [
                'label' => esc_html__('Subscriptions', 'listdom'),
                'icon' => 'fa fa-refresh',
            ],
            'orders' => [
                'label' => esc_html__('Orders', 'listdom'),
                'icon' => 'fa-solid fa-cart-shopping',
            ],
            'billing' => [
                'label' => esc_html__('Billing', 'listdom'),
                'icon' => 'fa-solid fa-file-invoice-dollar',
            ],
        ];
    }

    public function get_section_url(LSD_Shortcodes_Dashboard $dashboard, string $section): string
    {
        return $dashboard->add_qs_vars([
            'mode' => self::MODE,
            self::SECTION_QUERY_VAR => $section,
        ], $dashboard->url);
    }

    public function get_order_detail_id(array $orders): int
    {
        $order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
        if (!$order_id) return 0;

        foreach ($orders as $order)
        {
            if ($order instanceof LSD_Payments_Order && $order->get_id() === $order_id) return $order_id;
        }

        $order = LSD_Payments_Orders::get($order_id);
        if (!$order instanceof LSD_Payments_Order) return 0;

        if ($this->can_access_order_detail($order)) return $order_id;

        return 0;
    }

    public function get_order_detail_notice(array $orders): string
    {
        $order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
        if (!$order_id) return '';

        foreach ($orders as $order)
        {
            if ($order instanceof LSD_Payments_Order && $order->get_id() === $order_id) return '';
        }

        $order = LSD_Payments_Orders::get($order_id);
        if (!$order instanceof LSD_Payments_Order) return esc_html__('Order not found.', 'listdom');

        if ($this->can_access_order_detail($order)) return '';

        return esc_html__('This order does not belong to your account.', 'listdom');
    }

    public function can_access_order_detail(LSD_Payments_Order $order): bool
    {
        $order_id = $order->get_id();
        if ($order_id < 1) return false;

        if (current_user_can('edit_post', $order_id)) return true;

        return $this->is_booking_listing_owner($order);
    }

    public function is_booking_listing_owner(LSD_Payments_Order $order): bool
    {
        $user_id = get_current_user_id();
        if ($user_id < 1) return false;

        foreach ($this->get_order_booking_ids($order) as $booking_id)
        {
            $listing_id = (int) get_post_meta($booking_id, 'lsd_listing', true);
            $listing = $listing_id ? get_post($listing_id) : null;

            if ($listing instanceof WP_Post && (int) $listing->post_author === $user_id) return true;
        }

        return false;
    }

    public function get_order_booking_ids(LSD_Payments_Order $order): array
    {
        $ids = [];

        foreach ($order->get_fees() as $fee)
        {
            if (!is_array($fee)) continue;

            $meta = $fee['meta'] ?? [];
            if (!is_array($meta)) continue;

            $raw = $meta['lsd_booking_ids'] ?? ($meta['lsd_booking_id'] ?? ($meta['lsd_booking'] ?? null));
            if (is_array($raw)) $raw = implode(',', $raw);

            if (!is_string($raw)) continue;

            foreach (explode(',', $raw) as $booking_id)
            {
                $booking_id = (int) trim($booking_id);
                if ($booking_id) $ids[] = $booking_id;
            }
        }

        if (!$ids)
        {
            $stored = get_post_meta($order->get_id(), 'lsd_bookings', true);
            if (is_array($stored)) $ids = array_map('intval', $stored);
        }

        if (!$ids)
        {
            $single = (int) get_post_meta($order->get_id(), 'lsd_booking', true);
            if ($single) $ids[] = $single;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    public function get_activity_detail_key(): string
    {
        return isset($_GET['activity_key']) ? sanitize_text_field(wp_unslash($_GET['activity_key'])) : '';
    }

    public function get_orders(int $user_id): array
    {
        return $user_id > 0 ? LSD_Payments_Orders::by_user($user_id) : [];
    }

    public function get_orders_status_filter(): string
    {
        $status = isset($_REQUEST[self::ORDERS_STATUS_QUERY_VAR]) ? sanitize_key(wp_unslash($_REQUEST[self::ORDERS_STATUS_QUERY_VAR])) : 'all';
        return in_array($status, ['all', 'successful', 'pending', 'failed'], true) ? $status : 'all';
    }

    public function get_orders_search_filter(): string
    {
        return isset($_REQUEST[self::ORDERS_SEARCH_QUERY_VAR])
            ? sanitize_text_field(wp_unslash($_REQUEST[self::ORDERS_SEARCH_QUERY_VAR]))
            : '';
    }

    public function get_filtered_orders(int $user_id): array
    {
        $orders = $this->get_orders($user_id);
        $status = $this->get_orders_status_filter();
        $search = strtolower(trim($this->get_orders_search_filter()));

        return array_values(array_filter($orders, function ($order) use ($status, $search)
        {
            if (!$order instanceof LSD_Payments_Order) return false;

            $order_post = get_post($order->get_id());
            $status_key = $order_post instanceof WP_Post ? sanitize_html_class($order_post->post_status) : '';

            if (!$this->is_allowed_order_status($status_key)) return false;
            if ($status === 'successful' && !in_array($status_key, ['publish', 'completed'], true)) return false;
            if ($status === 'pending' && !in_array($status_key, ['pending', 'on-hold'], true)) return false;
            if ($status === 'failed' && !in_array($status_key, ['failed', 'refunded', 'canceled'], true)) return false;

            if ($search === '') return true;

            $haystacks = [
                strtolower($this->format_order_number($order->get_id())),
                strtolower($order_post instanceof WP_Post ? $order_post->post_title : ''),
                strtolower($this->get_order_status_label($order_post instanceof WP_Post ? $order_post->post_status : '')),
                strtolower(implode(', ', $this->get_order_lines($order))),
            ];

            foreach ($haystacks as $haystack)
            {
                if ($haystack !== '' && strpos($haystack, $search) !== false) return true;
            }

            return false;
        }));
    }

    public function get_recurrings(int $user_id): array
    {
        return $user_id > 0 ? LSD_Payments_Recurrings::by_user($user_id) : [];
    }

    public function get_subscriptions(int $user_id): array
    {
        if ($user_id < 1 || !class_exists('\LSDPACSUB\Subscriptions')) return [];

        return \LSDPACSUB\Subscriptions::all($user_id);
    }

    public function get_active_recurrings(array $recurrings): array
    {
        return array_values(array_filter($recurrings, function ($recurring)
        {
            return $recurring instanceof LSD_Payments_Recurring
                && $recurring->get_status() === LSD_Payments_Recurrings::STATUS_ACTIVE
                && $this->should_show_recurring($recurring);
        }));
    }

    public function get_active_recurring_labels(array $recurrings): array
    {
        $labels = [];

        foreach ($this->get_active_recurrings($recurrings) as $recurring)
        {
            foreach ($recurring->get_items() as $item)
            {
                if (!is_array($item)) continue;

                $type = $this->get_activity_item_type_key($item);
                if (!in_array($type, ['membership-package', 'claim', 'labelize', 'topup'], true)) continue;

                $labels[] = $this->get_activity_item_type_label($item);
            }
        }

        return array_values(array_unique(array_filter($labels)));
    }

    public function get_overview_autorenew_groups(array $recurrings, string $dashboard_url): array
    {
        $groups = [
            'disabled' => [
                'type' => 'error',
                'icon' => 'fa-solid fa-circle-exclamation',
                'title' => esc_html__('Auto-renewal is disabled', 'listdom'),
                'message' => esc_html__('The following subscriptions will expire soon. Activate the auto-renewal or extend it to avoid loosing the function and features of them:', 'listdom'),
                'items' => [],
            ],
            'scheduled' => [
                'type' => 'info',
                'icon' => 'fa-solid fa-circle-check',
                'title' => esc_html__('Auto-renewal scheduled', 'listdom'),
                'message' => esc_html__('The following subscriptions will renew:', 'listdom'),
                'items' => [],
            ],
        ];

        foreach ($recurrings as $recurring)
        {
            if (!$recurring instanceof LSD_Payments_Recurring) continue;
            if (!$this->should_show_recurring($recurring)) continue;

            $state = $this->get_recurring_state($recurring);
            if (!in_array($state, ['active', 'disabled'], true)) continue;

            $group_key = $state === 'disabled' ? 'disabled' : 'scheduled';
            $groups[$group_key]['items'][] = [
                'title' => $this->get_recurring_title($recurring),
                'date' => $this->get_recurring_next_renewal($recurring),
                'action_label' => $state === 'disabled' ? esc_html__('Activate Auto-renewal', 'listdom') : '',
                'action_url' => $this->get_recurring_manage_url($dashboard_url, $recurring),
            ];
        }

        return $groups;
    }

    public function get_active_subscriptions(array $subscriptions): array
    {
        return array_values(array_filter($subscriptions, static function ($subscription)
        {
            return $subscription instanceof WP_Post && $subscription->post_status === 'publish';
        }));
    }

    public function get_membership_capacity(array $memberships): array
    {
        $active_memberships = $this->get_active_subscriptions($memberships);
        if (!count($active_memberships))
        {
            return [
                'value' => null,
                'label' => '',
            ];
        }

        $remaining = 0;

        foreach ($active_memberships as $membership)
        {
            $subscription = new \LSDPACSUB\Subscription($membership->ID);
            $limit = (int) $subscription->package->data('limit');

            if ($limit === 0)
            {
                return [
                    'value' => null,
                    'label' => esc_html__('Unlimited', 'listdom'),
                ];
            }

            $used = (int) $subscription->listings(true);
            $remaining += max(0, $limit - $used);
        }

        return [
            'value' => $remaining,
            'label' => sprintf(esc_html__('%d listings left', 'listdom'), $remaining),
        ];
    }

    public function get_user_listings(int $user_id, int $limit = -1): array
    {
        if ($user_id < 1) return [];

        return get_posts([
            'post_type' => self::PTYPE_LISTING,
            'post_status' => ['publish', 'private', 'pending', 'draft', 'future', self::STATUS_HOLD, self::STATUS_EXPIRED],
            'author' => $user_id,
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
    }

    public function get_quick_actions(LSD_Shortcodes_Dashboard $dashboard, int $user_id): array
    {
        if (!class_exists('\LSDPACSUB\Base')) return [];

        return [
            [
                'label' => esc_html__('Get a Membership', 'listdom'),
                'description' => esc_html__('Unlock listing capacity, and package benefits.', 'listdom'),
                'button_label' => esc_html__('View Memberships', 'listdom'),
                'icon' => 'fa-solid fa-medal',
                'url' => $dashboard->add_qs_vars([
                    'mode' => 'subscription',
                    'subscription_section' => 'packages',
                ], $dashboard->url),
            ],
        ];
    }

    public function get_table_page_size(string $context = 'default'): int
    {
        $limit = (int) apply_filters('lsd_dashboard_payments_table_page_size', 5, $context);
        return $limit > 0 ? $limit : 5;
    }

    public function get_table_load_more_args(string $context = 'default'): array
    {
        $page_size = $this->get_table_page_size($context);

        return [
            'limit' => $page_size,
            'button_label' => esc_attr__('Load More', 'listdom'),
            'nonce' => wp_create_nonce('lsd_dashboard_payments_load_more'),
        ];
    }

    public function get_dashboard_url(): string
    {
        return self::get_submission_dashboard_url(home_url('/'));
    }

    public function get_table_page_data(string $context, int $user_id, int $page = 1): array
    {
        $page = max(1, $page);
        $limit = $this->get_table_page_size($context);
        $collection = $this->get_table_collection($context, $user_id);
        $offset = ($page - 1) * $limit;
        $items = array_slice($collection, $offset, $limit);
        $total = count($collection);

        return [
            'items' => $items,
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
            'next_page' => ($offset + $limit) < $total ? $page + 1 : 0,
            'has_more' => ($offset + $limit) < $total,
        ];
    }

    public function get_table_collection(string $context, int $user_id): array
    {
        if ($context === 'orders') return $this->get_filtered_orders($user_id);
        if ($context === 'billing') return $this->get_billing_recurrings($user_id);
        if ($context === 'subscriptions') return $this->get_filtered_subscription_recurrings($user_id);
        if ($context === 'overview')
        {
            $orders = $this->get_orders($user_id);
            $subscriptions = $this->get_subscriptions($user_id);

            return $this->get_recent_activity($orders, $user_id, $subscriptions, 0);
        }

        return [];
    }

    public function get_billing_recurrings(int $user_id): array
    {
        $recurrings = $this->get_recurrings($user_id);

        return array_values(array_filter($recurrings, function ($recurring)
        {
            if (!$recurring instanceof LSD_Payments_Recurring) return false;
            if (!$this->should_show_recurring($recurring)) return false;

            return in_array($this->get_recurring_state($recurring), ['active', 'disabled', 'expired'], true);
        }));
    }

    public function table_rows(string $context, array $items, string $dashboard_url = ''): string
    {
        if ($dashboard_url === '') $dashboard_url = $this->get_dashboard_url();

        ob_start();

        if ($context === 'orders')
        {
            include lsd_template('dashboard/payments/rows/orders.php');
        }
        else if ($context === 'billing')
        {
            include lsd_template('dashboard/payments/rows/billing.php');
        }
        else if ($context === 'subscriptions')
        {
            include lsd_template('dashboard/payments/rows/subscriptions.php');
        }
        else if ($context === 'overview')
        {
            include lsd_template('dashboard/payments/rows/overview.php');
        }

        return ob_get_clean();
    }

    public function get_order_detail_link(string $dashboard_url, int $order_id, string $section = self::DETAIL_SECTION): string
    {
        return add_query_arg([
            'mode' => self::MODE,
            self::SECTION_QUERY_VAR => $section ?: self::DETAIL_SECTION,
            'order_id' => $order_id,
        ], $dashboard_url);
    }

    public function get_recurring_detail_id(array $recurrings): int
    {
        $recurring_id = isset($_GET['recurring_id']) ? absint(wp_unslash($_GET['recurring_id'])) : 0;
        if (!$recurring_id) return 0;

        foreach ($recurrings as $recurring)
        {
            if ($recurring instanceof LSD_Payments_Recurring && $recurring->get_id() === $recurring_id) return $recurring_id;
        }

        return 0;
    }

    public function get_recurring_detail_link(string $dashboard_url, int $recurring_id, string $section = self::DETAIL_SECTION): string
    {
        return add_query_arg([
            'mode' => self::MODE,
            self::SECTION_QUERY_VAR => $section ?: self::DETAIL_SECTION,
            'recurring_id' => $recurring_id,
        ], $dashboard_url);
    }

    public function get_activity_detail_link(string $dashboard_url, array $activity): string
    {
        return add_query_arg([
            'mode' => self::MODE,
            self::SECTION_QUERY_VAR => self::DETAIL_SECTION,
            'activity_key' => $activity['activity_key'] ?? '',
            'order_id' => isset($activity['order_id']) ? (int) $activity['order_id'] : 0,
        ], $dashboard_url);
    }

    public function load_more(): void
    {
        if (!isset($_POST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing!', 'listdom')]);
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'lsd_dashboard_payments_load_more')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $user_id = get_current_user_id();
        if ($user_id < 1) $this->response(['success' => 0, 'message' => esc_html__('You are not logged in.', 'listdom')]);

        $context = isset($_POST['context']) ? sanitize_key(wp_unslash($_POST['context'])) : '';
        $page = isset($_POST['page']) ? absint(wp_unslash($_POST['page'])) : 1;
        $dashboard_url = isset($_POST['dashboard_url']) ? esc_url_raw(wp_unslash($_POST['dashboard_url'])) : $this->get_dashboard_url();

        if (!in_array($context, ['overview', 'orders', 'billing', 'subscriptions'], true))
        {
            $this->response(['success' => 0, 'message' => esc_html__('Invalid table context.', 'listdom')]);
        }

        $data = $this->get_table_page_data($context, $user_id, $page);

        $this->response([
            'success' => 1,
            'html' => $this->table_rows($context, $data['items'], $dashboard_url),
            'next_page' => $data['next_page'],
            'has_more' => $data['has_more'] ? 1 : 0,
        ]);
    }

    public function disable_autorenew(): void
    {
        if (!isset($_POST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing!', 'listdom')]);
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'lsd_dashboard_payments_disable_autorenew')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $user_id = get_current_user_id();
        if ($user_id < 1) $this->response(['success' => 0, 'message' => esc_html__('You are not logged in.', 'listdom')]);

        $dashboard_url = isset($_POST['dashboard_url']) ? esc_url_raw(wp_unslash($_POST['dashboard_url'])) : $this->get_dashboard_url();
        $recurring_id = isset($_POST['recurring_id']) ? absint(wp_unslash($_POST['recurring_id'])) : 0;
        $recurring = $recurring_id ? LSD_Payments_Recurrings::get($recurring_id) : null;
        $redirect = $recurring instanceof LSD_Payments_Recurring ? $this->get_recurring_detail_link($dashboard_url, $recurring->get_id()) : $dashboard_url;

        if (!$recurring instanceof LSD_Payments_Recurring || $recurring->get_user_id() !== $user_id)
        {
            $this->response(['success' => 0, 'message' => esc_html__('Subscription not found.', 'listdom')]);
        }

        if ($recurring->get_status() !== LSD_Payments_Recurrings::STATUS_ACTIVE)
        {
            $this->response([
                'success' => 0,
                'message' => esc_html__('Auto renewal is already disabled.', 'listdom'),
                'redirect' => $redirect,
            ]);
        }

        $gateway = $recurring->get_gateway_instance();
        if ($gateway instanceof LSD_Payments_Gateway)
        {
            $result = $gateway->disable_autorenew($recurring);
            if (is_wp_error($result))
            {
                $this->response(['success' => 0, 'message' => $result->get_error_message()]);
            }
        }

        if (!LSD_Payments_Recurrings::cancel($recurring->get_id()))
        {
            $this->response(['success' => 0, 'message' => esc_html__('Unable to disable auto renewal right now.', 'listdom')]);
        }

        $this->response([
            'success' => 1,
            'message' => esc_html__('Auto renewal has been disabled.', 'listdom'),
            'redirect' => $redirect,
        ]);
    }

    public function activate_autorenew(): void
    {
        if (!isset($_POST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing!', 'listdom')]);
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'lsd_dashboard_payments_activate_autorenew')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $user_id = get_current_user_id();
        if ($user_id < 1) $this->response(['success' => 0, 'message' => esc_html__('You are not logged in.', 'listdom')]);

        $dashboard_url = isset($_POST['dashboard_url']) ? esc_url_raw(wp_unslash($_POST['dashboard_url'])) : $this->get_dashboard_url();
        $recurring_id = isset($_POST['recurring_id']) ? absint(wp_unslash($_POST['recurring_id'])) : 0;
        $recurring = $recurring_id ? LSD_Payments_Recurrings::get($recurring_id) : null;
        $redirect = $recurring instanceof LSD_Payments_Recurring ? $this->get_recurring_detail_link($dashboard_url, $recurring->get_id()) : $dashboard_url;

        if (!$recurring instanceof LSD_Payments_Recurring || $recurring->get_user_id() !== $user_id)
        {
            $this->response(['success' => 0, 'message' => esc_html__('Subscription not found.', 'listdom')]);
        }

        if ($recurring->get_status() === LSD_Payments_Recurrings::STATUS_ACTIVE)
        {
            $this->response([
                'success' => 0,
                'message' => esc_html__('Auto renewal is already active.', 'listdom'),
                'redirect' => $redirect,
            ]);
        }

        if ($this->get_recurring_state($recurring) !== 'disabled')
        {
            $this->response([
                'success' => 0,
                'message' => esc_html__('Auto renewal can only be activated for subscriptions that are still expiring.', 'listdom'),
                'redirect' => $redirect,
            ]);
        }

        $gateway = $recurring->get_gateway_instance();
        if ($gateway instanceof LSD_Payments_Gateway)
        {
            $result = $gateway->activate_autorenew($recurring);
            if (is_wp_error($result))
            {
                $this->response(['success' => 0, 'message' => $result->get_error_message()]);
            }
        }

        if (!LSD_Payments_Recurrings::active($recurring->get_id()))
        {
            $this->response(['success' => 0, 'message' => esc_html__('Unable to activate auto renewal right now.', 'listdom')]);
        }

        $this->response([
            'success' => 1,
            'message' => esc_html__('Auto renewal has been activated.', 'listdom'),
            'redirect' => $this->get_recurring_detail_link($dashboard_url, $recurring->get_id()),
        ]);
    }
    
    protected function get_order_recurring_ids(LSD_Payments_Order $order): array
    {
        $ids = [];

        $stored_ids = get_post_meta($order->get_id(), 'lsd_recurring_ids', true);
        if (is_array($stored_ids))
        {
            $ids = array_merge($ids, $stored_ids);
        }

        foreach ($order->get_items() as $item)
        {
            if (!is_array($item)) continue;

            $recurring_id = (int) ($item['recurring_id'] ?? 0);
            if ($recurring_id > 0) $ids[] = $recurring_id;
        }

        $legacy_id = $order->get_recurring_id();
        if ($legacy_id > 0) $ids[] = $legacy_id;

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    protected function order_has_recurring(LSD_Payments_Order $order): bool
    {
        return count($this->get_order_recurring_ids($order)) > 0;
    }

    protected function get_order_item_recurring_map(LSD_Payments_Order $order): array
    {
        $order_id = $order->get_id();
        if (isset($this->order_item_recurring_map_cache[$order_id]))
        {
            return $this->order_item_recurring_map_cache[$order_id];
        }

        $items = $order->get_items();
        $map = [];
        $slots = [];

        foreach ($this->get_order_recurring_ids($order) as $candidate_id)
        {
            $candidate = LSD_Payments_Recurrings::get((int) $candidate_id);
            if (!$candidate instanceof LSD_Payments_Recurring) continue;

            $candidate_meta = $candidate->get_gateway_meta();
            $candidate_subscription_id = isset($candidate_meta['stripe_subscription_id']) ? sanitize_text_field((string) $candidate_meta['stripe_subscription_id']) : '';
            $candidate_items = $candidate->get_items();

            if (!count($candidate_items))
            {
                $slots[] = [
                    'recurring_id' => (int) $candidate_id,
                    'subscription_id' => $candidate_subscription_id,
                    'plan_id' => 0,
                    'tier_id' => '',
                    'used' => false,
                ];
                continue;
            }

            foreach ($candidate_items as $candidate_item)
            {
                if (!is_array($candidate_item)) continue;

                $slots[] = [
                    'recurring_id' => (int) $candidate_id,
                    'subscription_id' => $candidate_subscription_id !== ''
                        ? $candidate_subscription_id
                        : (isset($candidate_item['stripe_subscription_id']) ? sanitize_text_field((string) $candidate_item['stripe_subscription_id']) : ''),
                    'plan_id' => (int) ($candidate_item['plan_id'] ?? 0),
                    'tier_id' => isset($candidate_item['tier_id']) ? (string) $candidate_item['tier_id'] : '',
                    'used' => false,
                ];
            }
        }

        foreach ($items as $item_index => $item)
        {
            if (!is_array($item)) continue;

            $explicit_id = (int) ($item['recurring_id'] ?? 0);
            if ($explicit_id > 0)
            {
                $map[$item_index] = $explicit_id;

                foreach ($slots as $slot_index => $slot)
                {
                    if ($slot['recurring_id'] === $explicit_id && !$slot['used'])
                    {
                        $slots[$slot_index]['used'] = true;
                        break;
                    }
                }
            }
        }

        foreach ($items as $item_index => $item)
        {
            if (!is_array($item) || isset($map[$item_index])) continue;

            $item_subscription_id = isset($item['stripe_subscription_id']) ? sanitize_text_field((string) $item['stripe_subscription_id']) : '';
            if ($item_subscription_id === '') continue;

            foreach ($slots as $slot_index => $slot)
            {
                if ($slot['used'] || $slot['subscription_id'] === '') continue;
                if ($slot['subscription_id'] !== $item_subscription_id) continue;

                $map[$item_index] = (int) $slot['recurring_id'];
                $slots[$slot_index]['used'] = true;
                break;
            }
        }

        foreach ($items as $item_index => $item)
        {
            if (!is_array($item) || isset($map[$item_index])) continue;

            $plan_id = (int) ($item['plan_id'] ?? 0);
            $tier_id = isset($item['tier_id']) ? (string) $item['tier_id'] : '';
            if ($plan_id < 1) continue;

            foreach ($slots as $slot_index => $slot)
            {
                if ($slot['used']) continue;
                if ((int) $slot['plan_id'] !== $plan_id || (string) $slot['tier_id'] !== $tier_id) continue;

                $map[$item_index] = (int) $slot['recurring_id'];
                $slots[$slot_index]['used'] = true;
                break;
            }
        }

        $unmapped_indexes = [];
        foreach ($items as $item_index => $item)
        {
            if (is_array($item) && !isset($map[$item_index])) $unmapped_indexes[] = $item_index;
        }

        $unused_slots = array_values(array_filter($slots, static function (array $slot): bool
        {
            return !$slot['used'];
        }));

        if (count($unmapped_indexes) === 1 && count($unused_slots) === 1)
        {
            $map[$unmapped_indexes[0]] = (int) $unused_slots[0]['recurring_id'];
        }

        $this->order_item_recurring_map_cache[$order_id] = $map;

        return $map;
    }

    protected function get_item_recurring_id_by_index(int $item_index, array $item, LSD_Payments_Order $order): int
    {
        $recurring_id = (int) ($item['recurring_id'] ?? 0);
        if ($recurring_id > 0) return $recurring_id;

        $map = $this->get_order_item_recurring_map($order);
        if (isset($map[$item_index])) return (int) $map[$item_index];

        $ids = $this->get_order_recurring_ids($order);
        return count($ids) === 1 ? (int) $ids[0] : 0;
    }

    protected function get_item_recurring_by_index(int $item_index, array $item, LSD_Payments_Order $order): ?LSD_Payments_Recurring
    {
        $recurring_id = $this->get_item_recurring_id_by_index($item_index, $item, $order);
        if ($recurring_id < 1) return null;

        $recurring = LSD_Payments_Recurrings::get($recurring_id);
        return $recurring instanceof LSD_Payments_Recurring ? $recurring : null;
    }

    public function get_order_primary_recurring(LSD_Payments_Order $order): ?LSD_Payments_Recurring
    {
        $ids = $this->get_order_recurring_ids($order);
        if (!count($ids)) return null;

        $recurring = LSD_Payments_Recurrings::get((int) $ids[0]);
        return $recurring instanceof LSD_Payments_Recurring ? $recurring : null;
    }

    public function get_recent_activity(array $orders, int $user_id = 0, array $memberships = [], int $limit = 10): array
    {
        // membership, claim, topup, labelize. that can be recurring or onetime.
        $rows = [];
        $order_ids = $this->get_activity_order_ids($orders);

        foreach ($orders as $order)
        {
            if (!$order instanceof LSD_Payments_Order) continue;

            $order_post = get_post($order->get_id());
            if (!$order_post instanceof WP_Post) continue;
            if (!$this->is_allowed_order_status(sanitize_html_class($order_post->post_status))) continue;

            $items = $order->get_items();
            if (!count($items))
            {
                $rows[] = [
                    'item' => $order_post->post_title,
                    'type' => esc_html__('Order', 'listdom'),
                    'type_key' => 'order',
                    'price' => $this->render_price($order->get_total(), LSD_Options::currency(), false, false),
                    'status' => $this->get_order_status_label($order_post->post_status),
                    'status_key' => $this->get_order_status_group($order_post->post_status),
                    'time' => strtotime($order_post->post_date_gmt ?: $order_post->post_date),
                    'order_id' => $order->get_id(),
                    'activity_key' => 'order-' . $order->get_id(),
                    'is_recurring' => $this->order_has_recurring($order),
                ];

                continue;
            }

            foreach ($items as $item_index => $item)
            {
                if (!is_array($item)) continue;
                if (!$this->should_show_activity_item($item)) continue;

                $rows[] = [
                    'item' => $this->get_activity_item_title($item),
                    'type' => $this->get_activity_item_type_label($item),
                    'type_key' => $this->get_activity_item_type_key($item),
                    'price' => $this->get_activity_item_price($item, $order),
                    'status' => $this->get_order_status_label($order_post->post_status),
                    'status_key' => $this->get_order_status_group($order_post->post_status),
                    'time' => strtotime($order_post->post_date_gmt ?: $order_post->post_date),
                    'order_id' => $order->get_id(),
                    'activity_key' => 'order-' . $order->get_id() . '-' . md5(wp_json_encode($item)),
                    'is_recurring' => $this->order_has_recurring($order),
                    'recurring_id' => $this->get_item_recurring_id_by_index((int) $item_index, $item, $order),
                ];
            }
        }

        if ($user_id > 0)
        {
            $rows = array_merge(
                $rows,
                $this->get_membership_activity($memberships, $order_ids),
                $this->get_claim_activity($user_id, $order_ids),
                $this->get_topup_activity($user_id, $order_ids),
                $this->get_label_activity($user_id, $order_ids)
            );
        }

        usort($rows, static function (array $a, array $b)
        {
            return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
        });

        foreach ($rows as $index => $row)
        {
            $rows[$index]['date'] = $this->format_activity_date((int) ($row['time'] ?? 0));
        }

        return $limit > 0 ? array_slice($rows, 0, $limit) : $rows;
    }

    public function get_activity_order_ids(array $orders): array
    {
        $ids = [];

        foreach ($orders as $order)
        {
            if ($order instanceof LSD_Payments_Order) $ids[] = $order->get_id();
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    public function get_membership_activity(array $memberships, array $order_ids = []): array
    {
        if (!count($memberships) || !class_exists('\LSDPACSUB\Subscription')) return [];

        $rows = [];
        foreach ($memberships as $membership)
        {
            if (!$membership instanceof WP_Post) continue;

            $related_order_id = (int) get_post_meta($membership->ID, 'lsd_payments_order_id', true);
            if ($related_order_id && in_array($related_order_id, $order_ids, true)) continue;

            $subscription = new \LSDPACSUB\Subscription($membership->ID);
            $package_id = $subscription->package ? $subscription->package->id() : 0;
            $package = $package_id ? get_post($package_id) : null;
            if (!$this->is_visible_post($package, \LSDPACSUB\Base::PTYPE_PACKAGE)) continue;

            $related_order = $related_order_id ? LSD_Payments_Orders::get($related_order_id) : null;
            $price = $related_order instanceof LSD_Payments_Order
                ? $this->render_price($related_order->get_total(), LSD_Options::currency(), false, false)
                : $subscription->package->price();

            $rows[] = [
                'item' => $subscription->package->title(),
                'type' => esc_html__('Membership Package', 'listdom'),
                'type_key' => 'membership-package',
                'price' => $price ?: esc_html__('N/A', 'listdom'),
                'status' => $this->get_status_label($membership->post_status),
                'status_key' => sanitize_html_class($membership->post_status),
                'time' => $this->get_activity_timestamp(get_post_meta($membership->ID, 'lsd_subscription_time', true), $membership->post_date),
                'order_id' => $related_order_id,
                'activity_key' => 'membership-' . $membership->ID,
                'is_recurring' => $related_order instanceof LSD_Payments_Order ? $this->order_has_recurring($related_order) : false,
            ];
        }

        return $rows;
    }

    public function get_claim_activity(int $user_id, array $order_ids = []): array
    {
        if ($user_id < 1 || !class_exists('\LSDPACCLM\Base')) return [];

        $user = get_userdata($user_id);
        if (!$user instanceof WP_User || !is_email($user->user_email)) return [];

        $claims = get_posts([
            'post_type' => \LSDPACCLM\Base::PTYPE_CLAIM,
            'post_status' => ['publish', 'pending', 'draft', 'future', 'private'],
            'posts_per_page' => 100,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => [
                [
                    'key' => 'lsd_email',
                    'value' => $user->user_email,
                    'compare' => '=',
                ],
            ],
        ]);

        $rows = [];
        foreach ($claims as $claim)
        {
            if (!$claim instanceof WP_Post) continue;

            $related_order_id = (int) get_post_meta($claim->ID, 'lsd_payments_order_id', true);
            if ($related_order_id && in_array($related_order_id, $order_ids, true)) continue;

            $listing_id = (int) get_post_meta($claim->ID, 'lsd_listing_id', true);
            $listing = $listing_id ? get_post($listing_id) : null;
            if ($listing_id && !$this->is_visible_post($listing, self::PTYPE_LISTING)) continue;

            $product_id = (int) get_post_meta($claim->ID, 'lsd_product', true);
            $plan = $product_id ? new LSD_Payments_Plan($product_id) : null;
            $related_order = $related_order_id ? LSD_Payments_Orders::get($related_order_id) : null;
            $payment_status = (int) get_post_meta($claim->ID, 'lsd_payment', true);
            $claim_status = (int) get_post_meta($claim->ID, 'lsd_status', true);

            $rows[] = [
                'item' => $listing instanceof WP_Post ? sprintf(esc_html__('Claim: %s', 'listdom'), $listing->post_title) : $claim->post_title,
                'type' => esc_html__('Claim', 'listdom'),
                'type_key' => 'claim',
                'price' => $related_order instanceof LSD_Payments_Order
                    ? $this->render_price($related_order->get_total(), LSD_Options::currency(), false, false)
                    : $this->format_recent_activity_plan_price($plan),
                'status' => $payment_status ? wp_strip_all_tags(\LSDPACCLM\Base::get_payment_status_label($payment_status)) : wp_strip_all_tags(\LSDPACCLM\Base::get_claim_status_label($claim_status)),
                'status_key' => $this->get_claim_status_key($payment_status, $claim_status),
                'time' => $this->get_activity_timestamp($claim->post_date_gmt, $claim->post_date),
                'order_id' => $related_order_id,
                'activity_key' => 'claim-' . $claim->ID,
                'is_recurring' => $related_order instanceof LSD_Payments_Order ? $this->order_has_recurring($related_order) : false,
            ];
        }

        return $rows;
    }

    public function get_topup_activity(int $user_id, array $order_ids = []): array
    {
        if ($user_id < 1 || !class_exists('\LSDPACTUP\Topup')) return [];

        $listings = $this->get_user_listings($user_id);
        if (!count($listings)) return [];

        $topup = new \LSDPACTUP\Topup();
        $product = $topup->options()['product'] ?? [];
        $product_id = is_array($product) ? (int) ($product[0] ?? 0) : (int) $product;
        $plan = $product_id ? new LSD_Payments_Plan($product_id) : null;
        $rows = [];

        foreach ($listings as $listing)
        {
            if (!$listing instanceof WP_Post) continue;

            $related_order_ids = array_values(array_unique(array_map('intval', get_post_meta($listing->ID, 'lsd_topup_order_id', false))));
            $topup_time = get_post_meta($listing->ID, 'lsd_topup', true);
            $topup_timestamp = $this->get_activity_timestamp($topup_time, $listing->post_date);
            $listing_timestamp = $this->get_activity_timestamp($listing->post_date_gmt, $listing->post_date);

            if (!count($related_order_ids) && !$topup_time) continue;

            $has_unlisted_order = false;
            foreach ($related_order_ids as $related_order_id)
            {
                if (!$related_order_id || in_array($related_order_id, $order_ids, true)) continue;

                $related_order = LSD_Payments_Orders::get($related_order_id);
                $rows[] = [
                    'item' => sprintf(esc_html__('Topup: %s', 'listdom'), $listing->post_title),
                    'type' => esc_html__('Topup', 'listdom'),
                    'type_key' => 'topup',
                    'price' => $related_order instanceof LSD_Payments_Order
                        ? $this->render_price($related_order->get_total(), LSD_Options::currency(), false, false)
                        : $this->format_recent_activity_plan_price($plan),
                    'status' => $related_order instanceof LSD_Payments_Order
                        ? $this->get_status_label(get_post_status($related_order->get_id()))
                        : esc_html__('Completed', 'listdom'),
                    'status_key' => $related_order instanceof LSD_Payments_Order
                        ? sanitize_html_class((string) get_post_status($related_order->get_id()))
                        : 'completed',
                    'time' => $topup_timestamp,
                    'order_id' => $related_order_id,
                    'activity_key' => 'topup-' . $listing->ID . '-' . $related_order_id,
                    'is_recurring' => $related_order instanceof LSD_Payments_Order ? $this->order_has_recurring($related_order) : false,
                ];
                $has_unlisted_order = true;
            }

            if (!$has_unlisted_order && !count($related_order_ids) && $topup_timestamp > ($listing_timestamp + 60))
            {
                $rows[] = [
                    'item' => sprintf(esc_html__('Topup: %s', 'listdom'), $listing->post_title),
                    'type' => esc_html__('Topup', 'listdom'),
                    'type_key' => 'topup',
                    'price' => $this->format_recent_activity_plan_price($plan),
                    'status' => esc_html__('Completed', 'listdom'),
                    'status_key' => 'completed',
                    'time' => $topup_timestamp,
                    'order_id' => 0,
                    'activity_key' => 'topup-' . $listing->ID . '-' . $topup_timestamp,
                    'is_recurring' => false,
                ];
            }
        }

        return $rows;
    }

    public function get_label_activity(int $user_id, array $order_ids = []): array
    {
        if ($user_id < 1 || !class_exists('\LSDPACLBL\Addon')) return [];

        $listings = $this->get_user_listings($user_id);
        if (!count($listings)) return [];

        $rows = [];
        foreach ($listings as $listing)
        {
            if (!$listing instanceof WP_Post) continue;

            $related_order_ids = array_values(array_unique(array_map('intval', get_post_meta($listing->ID, 'lsd_labelize_order_id', false))));
            $labels = $this->get_listing_label_activity_terms($listing->ID);
            $latest_time = $this->get_listing_label_activity_time($listing->ID);

            if (!count($related_order_ids) && !count($labels)) continue;

            $label_title = count($labels)
                ? sprintf(esc_html__('Labels: %1$s (%2$s)', 'listdom'), $listing->post_title, implode(', ', $labels))
                : sprintf(esc_html__('Labels: %s', 'listdom'), $listing->post_title);

            $added = false;
            foreach ($related_order_ids as $related_order_id)
            {
                if (!$related_order_id || in_array($related_order_id, $order_ids, true)) continue;

                $related_order = LSD_Payments_Orders::get($related_order_id);
                $rows[] = [
                    'item' => $label_title,
                    'type' => esc_html__('Labelize', 'listdom'),
                    'type_key' => 'labelize',
                    'price' => $related_order instanceof LSD_Payments_Order
                        ? $this->render_price($related_order->get_total(), LSD_Options::currency(), false, false)
                        : esc_html__('N/A', 'listdom'),
                    'status' => $related_order instanceof LSD_Payments_Order
                        ? $this->get_status_label(get_post_status($related_order->get_id()))
                        : esc_html__('Completed', 'listdom'),
                    'status_key' => $related_order instanceof LSD_Payments_Order
                        ? sanitize_html_class((string) get_post_status($related_order->get_id()))
                        : 'completed',
                    'time' => $this->get_activity_timestamp($latest_time, $listing->post_date),
                    'order_id' => $related_order_id,
                    'activity_key' => 'labelize-' . $listing->ID . '-' . $related_order_id,
                    'is_recurring' => $related_order instanceof LSD_Payments_Order ? $this->order_has_recurring($related_order) : false,
                ];
                $added = true;
            }

            if (!$added && !count($related_order_ids) && $latest_time)
            {
                $rows[] = [
                    'item' => $label_title,
                    'type' => esc_html__('Labelize', 'listdom'),
                    'type_key' => 'labelize',
                    'price' => esc_html__('N/A', 'listdom'),
                    'status' => esc_html__('Completed', 'listdom'),
                    'status_key' => 'completed',
                    'time' => $this->get_activity_timestamp($latest_time, $listing->post_date),
                    'order_id' => 0,
                    'activity_key' => 'labelize-' . $listing->ID . '-' . $this->get_activity_timestamp($latest_time, $listing->post_date),
                    'is_recurring' => false,
                ];
            }
        }

        return $rows;
    }

    public function get_listing_label_activity_terms(int $listing_id): array
    {
        $meta = get_post_meta($listing_id);
        if (!is_array($meta)) return [];

        $terms = [];
        foreach (array_keys($meta) as $key)
        {
            if (strpos($key, 'lsd_labelize_time_') !== 0) continue;

            $term_id = (int) substr($key, strlen('lsd_labelize_time_'));
            if (!$term_id) continue;

            $term = get_term($term_id, self::TAX_LABEL);
            if (!is_wp_error($term) && $term && isset($term->name)) $terms[] = $term->name;
        }

        return array_values(array_unique($terms));
    }

    public function get_listing_label_activity_time(int $listing_id): string
    {
        $meta = get_post_meta($listing_id);
        if (!is_array($meta)) return '';

        $times = [];
        foreach ($meta as $key => $values)
        {
            if (strpos($key, 'lsd_labelize_time_') !== 0 || !is_array($values)) continue;

            foreach ($values as $value)
            {
                if (is_string($value) && trim($value) !== '') $times[] = $value;
            }
        }

        rsort($times);
        return $times[0] ?? '';
    }

    public function get_activity_timestamp($primary, $fallback = ''): int
    {
        $primary_time = is_numeric($primary) ? (int) $primary : strtotime((string) $primary);
        if ($primary_time) return $primary_time;

        $fallback_time = is_numeric($fallback) ? (int) $fallback : strtotime((string) $fallback);
        return $fallback_time ?: 0;
    }

    public function get_claim_status_key(int $payment_status = 0, int $claim_status = 0): string
    {
        if ($payment_status === \LSDPACCLM\Base::PAYMENT_PAID) return 'completed';
        if ($payment_status === \LSDPACCLM\Base::PAYMENT_WAITING) return 'pending';
        if ($payment_status === \LSDPACCLM\Base::PAYMENT_FREE) return 'completed';
        if ($claim_status === \LSDPACCLM\Base::CLAIM_REJECTED) return 'canceled';
        if ($claim_status === \LSDPACCLM\Base::CLAIM_PENDING) return 'pending';
        if ($claim_status === \LSDPACCLM\Base::CLAIM_APPROVED) return 'completed';

        return 'pending';
    }

    public function get_activity_item_type_label(array $item): string
    {
        $type = $this->get_activity_item_type_key($item);

        if ($type === 'membership-package') return esc_html__('Membership Package', 'listdom');
        if ($type === 'claim') return esc_html__('Claim', 'listdom');
        if ($type === 'labelize') return esc_html__('Labelize', 'listdom');
        if ($type === 'booking') return esc_html__('Booking', 'listdom');
        if ($type === 'topup') return esc_html__('Topup', 'listdom');
        if ($type === 'listing') return esc_html__('Listing', 'listdom');

        return esc_html__('Order', 'listdom');
    }

    public function get_activity_item_type_key(array $item): string
    {
        $meta = isset($item['meta']) && is_array($item['meta']) ? $item['meta'] : [];

        if (!empty($meta['lsd_booking_ids']) || !empty($meta['lsd_booking_id']) || !empty($meta['lsd_booking'])) return 'booking';

        if (!empty($meta['lsd_package_id'])) return 'membership-package';
        if (!empty($meta['lsd_claim_id'])) return 'claim';

        $plan_id = (int) ($item['plan_id'] ?? 0);
        $listing_id = (int) ($meta['lsd_listing_id'] ?? 0);

        if ($listing_id && $plan_id && class_exists('\LSDPACLBL\Addon') && count(\LSDPACLBL\Addon::get_terms_id($plan_id)))
        {
            return 'labelize';
        }

        if ($listing_id && $plan_id && class_exists('\LSDPACTUP\Topup'))
        {
            $topup = new \LSDPACTUP\Topup();
            $product = $topup->options()['product'] ?? [];
            $product_id = is_array($product) ? (int) ($product[0] ?? 0) : (int) $product;

            if ($product_id && $plan_id === $product_id) return 'topup';
        }

        if ($listing_id) return 'listing';

        return 'order';
    }

    public function get_fee_type_key(array $fee): string
    {
        $meta = isset($fee['meta']) && is_array($fee['meta']) ? $fee['meta'] : [];

        if (!empty($meta['lsd_booking_ids']) || !empty($meta['lsd_booking_id']) || !empty($meta['lsd_booking'])) return 'booking';

        return 'fee';
    }

    public function get_fee_type_label(array $fee): string
    {
        $type = $this->get_fee_type_key($fee);

        if ($type === 'booking') return esc_html__('Booking', 'listdom');

        return esc_html__('Fee', 'listdom');
    }

    public function format_activity_date(int $timestamp): string
    {
        if ($timestamp < 1) return '';

        return wp_date('Y-m-d', $timestamp);
    }

    public function format_order_number(int $order_id): string
    {
        return sprintf('ORD-%d', $order_id);
    }

    public function get_recurring_interval_label(int $days): string
    {
        if ($days < 1) return '';

        return sprintf(esc_html__('%d Days', 'listdom'), $days);
    }

    protected function get_plan_interval_days(?LSD_Payments_Plan $plan): int
    {
        if (!$plan instanceof LSD_Payments_Plan) return 0;

        $tier = $plan->get_tier();
        if (!$tier instanceof LSD_Payments_Tier) return 0;

        $tier_data = $tier->data();
        $days = isset($tier_data['expiry']) ? (int) $tier_data['expiry'] : 0;

        return $days > 0 ? $days : 0;
    }

    protected function format_price_with_interval(string $price, int $days, bool $is_recurring): string
    {
        $interval = $this->get_recurring_interval_label($days);
        if ($interval === '')
        {
            return $is_recurring ? $price : $price . '/ ' . esc_html__('One Time', 'listdom');
        }

        if ($is_recurring) return $price . ' / ' . $interval;

        return $price . ' ' . sprintf(esc_html__('For %s', 'listdom'), $interval);
    }

    protected function format_recent_activity_plan_price(?LSD_Payments_Plan $plan): string
    {
        if (!$plan instanceof LSD_Payments_Plan) return esc_html__('N/A', 'listdom');

        $tier = $plan->get_tier();
        $price = $plan->get_price_html();

        if ($price === '') return esc_html__('N/A', 'listdom');
        if (!$tier instanceof LSD_Payments_Tier) return $price;
        if ($tier->is_recurring()) return $this->get_plan_price_with_interval($plan, true);

        return $this->get_plan_price_with_interval($plan, false);
    }

    public function get_plan_price_with_interval(?LSD_Payments_Plan $plan, ?bool $display_as_recurring = null): string
    {
        if (!$plan instanceof LSD_Payments_Plan) return esc_html__('N/A', 'listdom');

        $price = $plan->get_price_html();
        $tier = $plan->get_tier();
        $days = $this->get_plan_interval_days($plan);
        $is_recurring = $tier instanceof LSD_Payments_Tier && $tier->is_recurring();

        return $this->format_price_with_interval($price, $days, $display_as_recurring === null ? $is_recurring : $display_as_recurring);
    }

    public function get_order_item_price_with_interval(array $item, ?string $price = null, ?bool $display_as_recurring = null): string
    {
        if (!is_array($item)) return esc_html__('N/A', 'listdom');

        $plan = new LSD_Payments_Plan((int) ($item['plan_id'] ?? 0), (string) ($item['tier_id'] ?? ''));
        if (!$plan instanceof LSD_Payments_Plan) return esc_html__('N/A', 'listdom');

        if ($price === null)
        {
            return $this->get_plan_price_with_interval($plan, $display_as_recurring);
        }

        $tier = $plan->get_tier();
        $days = $this->get_plan_interval_days($plan);
        $is_recurring = $tier instanceof LSD_Payments_Tier && $tier->is_recurring();

        return $this->format_price_with_interval($price, $days, $display_as_recurring === null ? $is_recurring : $display_as_recurring);
    }

    public function get_order_item_billing_interval(array $item, ?LSD_Payments_Recurring $recurring = null): string
    {
        if (!is_array($item)) return '';

        $plan = new LSD_Payments_Plan((int) ($item['plan_id'] ?? 0), (string) ($item['tier_id'] ?? ''));
        $frequency_days = (int) $plan->get_frequency_days();

        if ($frequency_days < 1)
        {
            return esc_html__('Billed Once', 'listdom');
        }

        $interval = $this->get_recurring_interval_label($frequency_days);
        if ($interval === '')
        {
            return esc_html__('Billed Once', 'listdom');
        }

        if ($recurring instanceof LSD_Payments_Recurring && $this->get_recurring_state($recurring) === 'active')
        {
            return sprintf(esc_html__('Billed every %s until cancelled', 'listdom'), $interval);
        }

        return sprintf(esc_html__('Billed every %s', 'listdom'), $interval);
    }

    public function get_recurring_price_with_interval(LSD_Payments_Recurring $recurring): string
    {
        $item = $this->get_recurring_primary_item($recurring);
        $plan = is_array($item)
            ? new LSD_Payments_Plan((int) ($item['plan_id'] ?? 0), (string) ($item['tier_id'] ?? ''))
            : null;

        if ($plan instanceof LSD_Payments_Plan)
        {
            return $this->get_plan_price_with_interval($plan);
        }

        return $this->format_price_with_interval(
            $this->render_price($recurring->get_total(), $recurring->get_currency(), false, false),
            $this->get_recurring_frequency_days($recurring),
            true
        );
    }

    public function get_order_price_with_interval(LSD_Payments_Order $order): string
    {
        $items = array_values(array_filter($order->get_items(), 'is_array'));
        $price = $this->render_price($order->get_total(), LSD_Options::currency(), false, false);

        if (count($items) === 1)
        {
            return $this->get_order_item_price_with_interval($items[0], $price);
        }

        return $price;
    }

    public function get_billing_fields(array $details): array
    {
        $fields = [];
        $tax = new LSD_Payments_Tax();
        $countries = $tax->get_countries();
        $states = $tax->get_states_list();

        if (!empty($details['name'])) $fields[] = ['label' => esc_html__('Full Name', 'listdom'), 'value' => (string) $details['name']];
        if (!empty($details['email'])) $fields[] = ['label' => esc_html__('Email', 'listdom'), 'value' => (string) $details['email']];
        if (!empty($details['phone'])) $fields[] = ['label' => esc_html__('Phone', 'listdom'), 'value' => (string) $details['phone']];
        if (!empty($details['company_name'])) $fields[] = ['label' => esc_html__('Company Name', 'listdom'), 'value' => (string) $details['company_name']];
        if (!empty($details['tax_vat_id'])) $fields[] = ['label' => esc_html__('Tax/Vat ID', 'listdom'), 'value' => (string) $details['tax_vat_id']];
        if (!empty($details['country']))
        {
            $country_code = (string) $details['country'];
            $fields[] = ['label' => esc_html__('Country', 'listdom'), 'value' => (string) ($countries[$country_code] ?? $country_code)];
        }
        if (!empty($details['state']))
        {
            $country_code = (string) ($details['country'] ?? '');
            $state_code = (string) $details['state'];
            $state_value = isset($states[$country_code][$state_code]) ? $states[$country_code][$state_code] : $state_code;
            $fields[] = ['label' => esc_html__('State / Province', 'listdom'), 'value' => (string) $state_value];
        }
        if (!empty($details['address'])) $fields[] = ['label' => esc_html__('Address', 'listdom'), 'value' => (string) $details['address']];
        if (!empty($details['city'])) $fields[] = ['label' => esc_html__('City', 'listdom'), 'value' => (string) $details['city']];
        if (!empty($details['postal_code'])) $fields[] = ['label' => esc_html__('Postal Code', 'listdom'), 'value' => (string) $details['postal_code']];

        return $fields;
    }

    public function get_status_badge_class(string $status_key): string
    {
        if (in_array($status_key, ['publish', 'completed'], true)) return 'lsd-success';
        if (in_array($status_key, ['pending', 'canceled', 'on-hold'], true)) return 'lsd-warning';
        if (in_array($status_key, ['failed', 'refunded'], true)) return 'lsd-error';

        return '';
    }

    protected function get_allowed_recurring_post_statuses(): array
    {
        return [
            LSD_Payments_Recurrings::STATUS_ACTIVE,
            LSD_Payments_Recurrings::STATUS_CANCEL,
            LSD_Payments_Recurrings::STATUS_REFUNDED,
        ];
    }

    public function get_recurring_state(?LSD_Payments_Recurring $recurring): string
    {
        if (!$recurring instanceof LSD_Payments_Recurring) return 'active';

        $status = $recurring->get_status();
        if (!in_array($status, $this->get_allowed_recurring_post_statuses(), true)) return 'expired';
        if ($status === LSD_Payments_Recurrings::STATUS_REFUNDED) return 'expired';

        $meta = $recurring->get_gateway_meta();
        $cancel_at_period_end = isset($meta['cancel_at_period_end']) && in_array((string) $meta['cancel_at_period_end'], ['1', 'true', 'yes'], true);

        $next_renewal_timestamp = $this->get_recurring_next_renewal_timestamp($recurring);
        if ($status === LSD_Payments_Recurrings::STATUS_ACTIVE && !$cancel_at_period_end) return 'active';
        if ($next_renewal_timestamp > 0 && $next_renewal_timestamp > time()) return 'disabled';

        return 'expired';
    }

    public function get_recurring_state_badge_class(string $state): string
    {
        return $state === 'active' ? 'lsd-success' : 'lsd-error';
    }

    public function get_recurring_tooltip(?LSD_Payments_Recurring $recurring): string
    {
        if (!$recurring instanceof LSD_Payments_Recurring) return '';

        $state = $this->get_recurring_state($recurring);

        if ($state === 'active')
        {
            return esc_attr__("Active Recurring Subscription\nAuto renewal is Active.", 'listdom');
        }

        if ($state === 'disabled')
        {
            return esc_attr__("Expiring Recurring Subscription\nAuto renewal is Disabled", 'listdom');
        }

        return esc_attr__('Canceled Recurring Subscription', 'listdom');
    }

    public function recurring_badge(?LSD_Payments_Recurring $recurring): string
    {
        if (!$recurring instanceof LSD_Payments_Recurring) return '';

        $state = $this->get_recurring_state($recurring);
        $badge_class = $this->get_recurring_state_badge_class($state);
        $icon_class = $this->get_recurring_type_icon($state);
        $label = $state === 'expired' ? esc_attr__('Expired recurring subscription', 'listdom') : esc_attr__('Recurring subscription', 'listdom');
        $tooltip = $this->get_recurring_tooltip($recurring);

        return '<span class="lsd-dashboard-payments-activity-recurring lsd-badge lsd-tooltip lsd-tooltip-top ' . esc_attr($badge_class) . '" data-lsd-tooltip="' . $tooltip . '" aria-label="' . $label . '"><i class="lsd-fe-icon ' . esc_attr($icon_class) . '"></i></span>';
    }

    public function get_customer_fields(?LSD_Payments_Order $order = null, int $user_id = 0): array
    {
        $fields = [];
        $user = $user_id ? get_userdata($user_id) : ($order ? $order->get_user() : null);

        $name = $order ? $order->get_name() : ($user instanceof WP_User ? $user->display_name : '');
        $email = $order ? $order->get_email() : ($user instanceof WP_User ? $user->user_email : '');
        $phone = '';

        if ($user instanceof WP_User)
        {
            $phone_keys = ['billing_phone', 'phone', 'mobile'];
            foreach ($phone_keys as $phone_key)
            {
                $phone = (string) get_user_meta($user->ID, $phone_key, true);
                if (trim($phone) !== '') break;
            }
        }

        if ($name) $fields[] = ['label' => esc_html__('Name', 'listdom'), 'value' => $name];
        if ($email) $fields[] = ['label' => esc_html__('Email', 'listdom'), 'value' => $email];
        if ($phone) $fields[] = ['label' => esc_html__('Phone', 'listdom'), 'value' => $phone];
        if ($order && $order->get_message()) $fields[] = ['label' => esc_html__('Message', 'listdom'), 'value' => $order->get_message()];

        return $fields;
    }

    public function get_activity_detail(array $orders, int $user_id = 0, array $memberships = []): ?array
    {
        $activity_key = $this->get_activity_detail_key();
        if (!$activity_key) return null;

        $activities = $this->get_recent_activity($orders, $user_id, $memberships, 0);
        foreach ($activities as $activity)
        {
            if (($activity['activity_key'] ?? '') !== $activity_key) continue;

            return $this->get_activity_detail_context($activity, $user_id);
        }

        return null;
    }

    public function get_activity_detail_context(array $activity, int $user_id = 0): array
    {
        $order_id = (int) ($activity['order_id'] ?? 0);
        $order = $order_id ? LSD_Payments_Orders::get($order_id) : null;

        if ($order instanceof LSD_Payments_Order)
        {
            $detail = $this->get_order_detail($order);
            $detail['activity'] = $activity;
            return $detail;
        }

        $user = $user_id ? get_userdata($user_id) : null;
        $customer_fields = $this->get_customer_fields(null, $user_id);

        return [
            'order' => null,
            'order_post' => null,
            'order_id' => 0,
            'formatted_order_number' => '',
            'title' => esc_html__('Activity Details', 'listdom'),
            'order_status' => $activity['status'] ?? '',
            'status_key' => $activity['status_key'] ?? 'completed',
            'order_datetime' => $activity['date'] ?? '',
            'customer_name' => $user instanceof WP_User ? $user->display_name : '',
            'customer_email' => $user instanceof WP_User ? $user->user_email : '',
            'customer_message' => '',
            'customer_fields' => $customer_fields,
            'billing_fields' => [],
            'items' => [],
            'fees' => [],
            'currency' => LSD_Options::currency(),
            'subtotal' => $activity['price'] ?? esc_html__('N/A', 'listdom'),
            'discount' => '',
            'discount_value' => 0.0,
            'tax' => '',
            'tax_value' => 0.0,
            'total' => $activity['price'] ?? esc_html__('N/A', 'listdom'),
            'gateway_name' => esc_html__('N/A', 'listdom'),
            'invoice_url' => '',
            'email_url' => $user instanceof WP_User && $user->user_email ? 'mailto:' . antispambot($user->user_email) : '',
            'tax_items' => [],
            'is_recurring' => !empty($activity['is_recurring']),
            'activity' => $activity,
            'line_items' => [
                [
                    'item' => $activity['item'] ?? '',
                    'type' => $activity['type'] ?? esc_html__('Order', 'listdom'),
                    'type_key' => $activity['type_key'] ?? 'order',
                    'billing_interval' => !empty($activity['is_recurring']) ? '' : esc_html__('Billed Once', 'listdom'),
                    'quantity' => 1,
                    'price' => $activity['price'] ?? esc_html__('N/A', 'listdom'),
                    'is_recurring' => !empty($activity['is_recurring']),
                ],
            ],
        ];
    }

    public function get_order_detail(LSD_Payments_Order $order): array
    {
        $order_post = get_post($order->get_id());
        $currency = LSD_Options::currency();
        $items = $order->get_items();
        $fees = $order->get_fees();
        $billing_details = $order->get_billing_details();
        $line_items = [];

        foreach ($items as $item_index => $item)
        {
            if (!is_array($item)) continue;
            if (!$this->should_show_activity_item($item)) continue;

            $plan_id = (int) ($item['plan_id'] ?? 0);
            $tier_id = $item['tier_id'] ?? '';
            $plan = $plan_id ? new LSD_Payments_Plan($plan_id, $tier_id) : null;
            $tier = $plan ? $plan->get_tier() : null;
            $line_recurring = $this->get_item_recurring_by_index((int) $item_index, $item, $order);

            $line_items[] = [
                'item' => $this->get_activity_item_title($item),
                'type' => $this->get_activity_item_type_label($item),
                'type_key' => $this->get_activity_item_type_key($item),
                'billing_interval' => $this->get_order_item_billing_interval($item, $line_recurring),
                'quantity' => 1,
                'price' => $this->get_order_item_price_with_interval($item),
                'is_recurring' => $tier && $tier->is_recurring(),
                'recurring_badge' => $line_recurring instanceof LSD_Payments_Recurring ? $this->recurring_badge($line_recurring) : '',
                'manage_url' => $tier && $tier->is_recurring() && $line_recurring instanceof LSD_Payments_Recurring ? $this->get_recurring_manage_url($this->get_dashboard_url(), $line_recurring) : '',
            ];
        }

        foreach ($fees as $fee)
        {
            $fee_title = isset($fee['title']) ? trim((string) $fee['title']) : esc_html__('Fee', 'listdom');
            $fee_amount = isset($fee['amount']) ? (float) $fee['amount'] : 0;
            $fee_type_key = $this->get_fee_type_key($fee);

            $line_items[] = [
                'item' => $fee_title,
                'type' => $this->get_fee_type_label($fee),
                'type_key' => $fee_type_key,
                'billing_interval' => '',
                'quantity' => 1,
                'price' => $this->render_price($fee_amount, $currency, false, false),
                'is_recurring' => false,
            ];
        }
        $customer_fields = $this->get_customer_fields($order);
        $billing_details = $order->get_billing_details();

        return [
            'order' => $order,
            'order_post' => $order_post,
            'order_id' => $order->get_id(),
            'formatted_order_number' => $this->format_order_number($order->get_id()),
            'title' => sprintf(esc_html__('Order %s', 'listdom'), $this->format_order_number($order->get_id())),
            'order_status' => $order_post instanceof WP_Post ? $this->get_order_status_label($order_post->post_status) : '',
            'status_key' => $order_post instanceof WP_Post ? $this->get_order_status_group($order_post->post_status) : 'pending',
            'order_datetime' => $order_post instanceof WP_Post ? $this->format_activity_date($this->get_activity_timestamp($order_post->post_date_gmt, $order_post->post_date)) : '',
            'customer_name' => $order->get_name(),
            'customer_email' => $order->get_email(),
            'customer_message' => $order->get_message(),
            'customer_fields' => $customer_fields,
            'billing_details' => $billing_details,
            'billing_fields' => $this->get_billing_fields($billing_details),
            'items' => $items,
            'fees' => $fees,
            'currency' => $currency,
            'subtotal' => $this->render_price($order->get_subtotal(), $currency, false, false),
            'discount' => $this->render_price($order->get_discount(), $currency, false, false),
            'discount_value' => $order->get_discount(),
            'tax' => $this->render_price($order->get_tax(), $currency, false, false),
            'tax_value' => $order->get_tax(),
            'total' => $this->render_price($order->get_total(), $currency, false, false),
            'gateway_name' => $this->get_gateway_label($order->get_gateway()) ?: esc_html__('N/A', 'listdom'),
            'invoice_url' => $order->get_invoice_url(),
            'email_url' => $order->get_email() ? 'mailto:' . antispambot($order->get_email()) : '',
            'tax_items' => $order->get_tax_items(),
            'is_recurring' => $this->order_has_recurring($order),
            'recurring_manage_url' => $this->get_order_primary_recurring($order) instanceof LSD_Payments_Recurring ? $this->get_recurring_manage_url($this->get_dashboard_url(), $this->get_order_primary_recurring($order)) : '',
            'activity' => [],
            'line_items' => $line_items,
        ];
    }

    public function get_activity_item_title(array $item): string
    {
        if (!$this->should_show_activity_item($item)) return '';

        $meta = isset($item['meta']) && is_array($item['meta']) ? $item['meta'] : [];
        $plan = new LSD_Payments_Plan((int) ($item['plan_id'] ?? 0), $item['tier_id'] ?? '');
        $listing_id = (int) ($meta['lsd_listing_id'] ?? 0);
        $package_id = (int) ($meta['lsd_package_id'] ?? 0);
        $claim_id = (int) ($meta['lsd_claim_id'] ?? 0);

        if ($package_id)
        {
            return $this->get_package_title($package_id, $plan);
        }

        if ($claim_id)
        {
            $listing_id = (int) get_post_meta($claim_id, 'lsd_listing_id', true);
            $listing_title = $this->get_listing_title($listing_id);

            return $listing_title !== ''
                ? sprintf(esc_html__('Claim: %s', 'listdom'), $listing_title)
                : $plan->get_title();
        }

        if ($listing_id)
        {
            $listing_title = $this->get_listing_title($listing_id);
            if ($listing_title !== '')
            {
                $type = $this->get_activity_item_type_label($item);
                return sprintf('%s: %s', $type, $listing_title);
            }
        }

        return $plan->get_title() ?: esc_html__('Order Item', 'listdom');
    }

    public function get_activity_item_price(array $item, LSD_Payments_Order $order): string
    {
        $plan = new LSD_Payments_Plan((int) ($item['plan_id'] ?? 0), $item['tier_id'] ?? '');
        $price = $plan->get_price();
        if ($price <= 0) $price = $order->get_total();

        return $this->get_order_item_price_with_interval(
            $item,
            $this->render_price($price, LSD_Options::currency(), false, false)
        );
    }

    public function get_billing_profile(array $orders): array
    {
        $user_id = get_current_user_id();
        $user = $user_id ? get_userdata($user_id) : null;
        $tax_helper = new LSD_Payments_Tax();
        $default_location = $tax_helper->default_location();

        $profile = [
            'name' => $user_id ? (string) get_user_meta($user_id, 'billing_name', true) : '',
            'email' => $user_id ? (string) get_user_meta($user_id, 'billing_email', true) : '',
            'phone' => $user_id ? (string) get_user_meta($user_id, 'billing_phone', true) : '',
            'company_name' => $user_id ? (string) get_user_meta($user_id, 'billing_company', true) : '',
            'tax_vat_id' => $user_id ? (string) get_user_meta($user_id, 'billing_tax_vat_id', true) : '',
            'country' => $user_id ? (string) get_user_meta($user_id, 'billing_country', true) : '',
            'state' => $user_id ? (string) get_user_meta($user_id, 'billing_state', true) : '',
            'address' => $user_id ? (string) get_user_meta($user_id, 'billing_address_1', true) : '',
            'city' => $user_id ? (string) get_user_meta($user_id, 'billing_city', true) : '',
            'postal_code' => $user_id ? (string) get_user_meta($user_id, 'billing_postcode', true) : '',
        ];

        if ($profile['name'] === '' && $user instanceof WP_User) $profile['name'] = $user->display_name;
        if ($profile['email'] === '' && $user instanceof WP_User) $profile['email'] = $user->user_email;

        foreach ($orders as $order)
        {
            if (!$order instanceof LSD_Payments_Order) continue;

            $location = $order->get_tax_location();

            if ($profile['name'] === '') $profile['name'] = $order->get_name();
            if ($profile['email'] === '') $profile['email'] = $order->get_email();
            if ($profile['country'] === '') $profile['country'] = isset($location['country']) ? (string) $location['country'] : '';
            if ($profile['state'] === '') $profile['state'] = isset($location['state']) ? (string) $location['state'] : '';

            break;
        }

        if ($profile['country'] === '') $profile['country'] = isset($default_location['country']) ? (string) $default_location['country'] : '';
        if ($profile['state'] === '') $profile['state'] = isset($default_location['state']) ? (string) $default_location['state'] : '';

        return $profile;
    }

    public function get_billing_countries(): array
    {
        return (new LSD_Payments_Tax())->get_countries();
    }

    public function get_billing_states_list(): array
    {
        return (new LSD_Payments_Tax())->get_states_list();
    }

    public function get_billing_states(string $country): array
    {
        return (new LSD_Payments_Tax())->get_states($country);
    }

    protected function maybe_save_billing_profile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if ($this->get_section() !== 'billing') return;
        if (!isset($_POST['lsd_dashboard_payments_billing_nonce'])) return;

        $nonce = sanitize_text_field(wp_unslash($_POST['lsd_dashboard_payments_billing_nonce']));
        if (!wp_verify_nonce($nonce, 'lsd_dashboard_payments_billing')) return;

        $user_id = get_current_user_id();
        if ($user_id < 1) return;

        $raw = isset($_POST['lsd_billing']) && is_array($_POST['lsd_billing']) ? wp_unslash($_POST['lsd_billing']) : [];
        $tax_helper = new LSD_Payments_Tax();
        $countries = $tax_helper->get_countries();

        $country = isset($raw['country']) ? strtoupper(sanitize_text_field((string) $raw['country'])) : '';
        if ($country !== '' && !isset($countries[$country])) $country = '';

        $states = $tax_helper->get_states($country);
        $state = isset($raw['state']) ? strtoupper(sanitize_text_field((string) $raw['state'])) : '';
        if ($state !== '' && !isset($states[$state])) $state = '';

        $profile = [
            'billing_name' => isset($raw['name']) ? sanitize_text_field((string) $raw['name']) : '',
            'billing_email' => isset($raw['email']) ? sanitize_email((string) $raw['email']) : '',
            'billing_phone' => isset($raw['phone']) ? sanitize_text_field((string) $raw['phone']) : '',
            'billing_company' => isset($raw['company_name']) ? sanitize_text_field((string) $raw['company_name']) : '',
            'billing_tax_vat_id' => isset($raw['tax_vat_id']) ? sanitize_text_field((string) $raw['tax_vat_id']) : '',
            'billing_country' => $country,
            'billing_state' => $state,
            'billing_address_1' => isset($raw['address']) ? sanitize_text_field((string) $raw['address']) : '',
            'billing_city' => isset($raw['city']) ? sanitize_text_field((string) $raw['city']) : '',
            'billing_postcode' => isset($raw['postal_code']) ? sanitize_text_field((string) $raw['postal_code']) : '',
        ];

        foreach ($profile as $meta_key => $value)
        {
            update_user_meta($user_id, $meta_key, $value);
        }
    }

    public function get_gateway_label(?string $gateway_key): string
    {
        if (!$gateway_key) return '';

        $gateway = LSD_Payments::gateway($gateway_key);
        return $gateway ? $gateway->name() : $gateway_key;
    }

    public function get_status_label(string $status): string
    {
        $object = get_post_status_object($status);
        return $object ? $object->label : $status;
    }

    public function get_order_status_group(string $status): string
    {
        $status = sanitize_html_class($status);

        if (in_array($status, ['publish', 'completed'], true)) return 'completed';
        if (in_array($status, ['pending', 'on-hold'], true)) return 'pending';
        if (in_array($status, ['failed', 'refunded', 'canceled'], true)) return 'failed';

        return 'pending';
    }

    protected function is_allowed_order_status(string $status): bool
    {
        return in_array($status, ['publish', 'completed', 'pending', 'on-hold', 'failed', 'refunded', 'canceled'], true);
    }

    public function get_order_status_label(string $status): string
    {
        $group = $this->get_order_status_group($status);

        if ($group === 'completed') return esc_html__('Successful', 'listdom');
        if ($group === 'failed') return esc_html__('Failed', 'listdom');

        return esc_html__('Pending', 'listdom');
    }

    public function get_unique_gateways(array $orders): array
    {
        $gateways = [];

        foreach ($orders as $order)
        {
            if (!$order instanceof LSD_Payments_Order) continue;

            $label = $this->get_gateway_label($order->get_gateway());
            if ($label === '') continue;

            $gateways[$label] = $label;
        }

        return array_values($gateways);
    }

    public function get_order_lines(LSD_Payments_Order $order): array
    {
        $lines = [];

        foreach ($order->get_items() as $item)
        {
            if (!is_array($item)) continue;
            if (!$this->should_show_activity_item($item)) continue;

            $line = trim($this->get_activity_item_title($item));
            if ($line !== '') $lines[] = $line;
        }

        foreach ($order->get_fees() as $fee)
        {
            $title = isset($fee['title']) ? trim((string) $fee['title']) : '';
            $amount = isset($fee['amount']) ? (float) $fee['amount'] : 0.0;

            $line = $title !== '' ? $title : esc_html__('Fee', 'listdom');
            if ($amount !== 0.0) $line .= ' - ' . wp_strip_all_tags($this->render_price($amount, LSD_Options::currency(), false, false));

            $lines[] = $line;
        }

        return array_values(array_filter($lines));
    }

    public function get_order_table_items(LSD_Payments_Order $order): array
    {
        $lines = [];

        foreach ($order->get_items() as $item_index => $item)
        {
            if (!is_array($item)) continue;
            if (!$this->should_show_activity_item($item)) continue;

            $plan = new LSD_Payments_Plan((int) ($item['plan_id'] ?? 0), $item['tier_id'] ?? '');
            $tier = $plan->get_tier();

            $recurring_id = $this->get_item_recurring_id_by_index((int) $item_index, $item, $order);

            $lines[] = [
                'title' => $this->get_activity_item_title($item),
                'type' => $this->get_activity_item_type_label($item),
                'type_key' => $this->get_activity_item_type_key($item),
                'is_recurring' => $tier && $tier->is_recurring(),
                'recurring_id' => $recurring_id,
            ];
        }

        if (!$lines)
        {
            $lines[] = [
                'title' => esc_html__('N/A', 'listdom'),
                'type' => esc_html__('Order', 'listdom'),
                'type_key' => 'order',
                'is_recurring' => false,
            ];
        }

        return $lines;
    }

    protected function is_visible_post($post, string $expected_post_type = ''): bool
    {
        if (!($post instanceof WP_Post)) return false;
        if ($expected_post_type !== '' && $post->post_type !== $expected_post_type) return false;

        return $post->post_status !== self::STATUS_TRASH;
    }

    protected function should_show_activity_item(array $item): bool
    {
        $meta = isset($item['meta']) && is_array($item['meta']) ? $item['meta'] : [];
        $listing_id = (int) ($meta['lsd_listing_id'] ?? 0);
        $package_id = (int) ($meta['lsd_package_id'] ?? 0);
        $claim_id = (int) ($meta['lsd_claim_id'] ?? 0);

        if ($package_id > 0)
        {
            $package = get_post($package_id);
            $expected_type = class_exists('\LSDPACSUB\Base') ? \LSDPACSUB\Base::PTYPE_PACKAGE : '';
            return $this->is_visible_post($package, $expected_type);
        }

        if ($claim_id > 0)
        {
            $claim = get_post($claim_id);
            $expected_type = class_exists('\LSDPACCLM\Base') ? \LSDPACCLM\Base::PTYPE_CLAIM : '';
            if (!$this->is_visible_post($claim, $expected_type)) return false;

            $claim_listing_id = (int) get_post_meta($claim_id, 'lsd_listing_id', true);
            if ($claim_listing_id > 0)
            {
                return $this->is_visible_post(get_post($claim_listing_id), self::PTYPE_LISTING);
            }

            return true;
        }

        if ($listing_id > 0)
        {
            return $this->is_visible_post(get_post($listing_id), self::PTYPE_LISTING);
        }

        return true;
    }

    protected function get_recurring_primary_item(LSD_Payments_Recurring $recurring): ?array
    {
        foreach ($recurring->get_items() as $item)
        {
            if (is_array($item)) return $item;
        }

        return null;
    }

    protected function should_show_recurring(LSD_Payments_Recurring $recurring): bool
    {
        if (!in_array($recurring->get_status(), $this->get_allowed_recurring_post_statuses(), true))
        {
            return false;
        }

        $primary_item = $this->get_recurring_primary_item($recurring);
        if (is_array($primary_item))
        {
            return $this->should_show_activity_item($primary_item);
        }

        foreach ($recurring->get_orders() as $order)
        {
            if (!$order instanceof LSD_Payments_Order) continue;

            foreach ($order->get_items() as $item)
            {
                if (is_array($item) && $this->should_show_activity_item($item)) return true;
            }
        }

        return false;
    }

    public function get_recurring_title(LSD_Payments_Recurring $recurring): string
    {
        $item = $this->get_recurring_primary_item($recurring);

        if (is_array($item))
        {
            return $this->get_activity_item_title($item);
        }

        $post = get_post($recurring->get_id());

        return $post instanceof WP_Post && trim($post->post_title) !== ''
            ? $post->post_title
            : '#' . $recurring->get_id();
    }

    public function get_subscriptions_status_filter(): string
    {
        $status = isset($_REQUEST[self::SUBSCRIPTIONS_STATUS_QUERY_VAR]) ? sanitize_key(wp_unslash($_REQUEST[self::SUBSCRIPTIONS_STATUS_QUERY_VAR])) : 'all';
        return in_array($status, ['all', 'active', 'expiring', 'expired'], true) ? $status : 'all';
    }

    public function get_subscriptions_search_filter(): string
    {
        return isset($_REQUEST[self::SUBSCRIPTIONS_SEARCH_QUERY_VAR])
            ? sanitize_text_field(wp_unslash($_REQUEST[self::SUBSCRIPTIONS_SEARCH_QUERY_VAR]))
            : '';
    }

    public function get_filtered_subscription_recurrings(int $user_id): array
    {
        $recurrings = $this->get_recurrings($user_id);
        $status = $this->get_subscriptions_status_filter();
        $search = strtolower(trim($this->get_subscriptions_search_filter()));

        return array_values(array_filter($recurrings, function ($recurring) use ($status, $search)
        {
            if (!$recurring instanceof LSD_Payments_Recurring) return false;
            if (!$this->should_show_recurring($recurring)) return false;

            $state = $this->get_recurring_state($recurring);

            if ($status === 'active' && $state !== 'active') return false;
            if ($status === 'expiring' && $state !== 'disabled') return false;
            if ($status === 'expired' && $state !== 'expired') return false;

            if ($search === '') return true;

            $haystacks = [
                strtolower($this->get_recurring_title($recurring)),
                strtolower($this->get_gateway_label($recurring->get_gateway())),
                strtolower($this->get_recurring_status_label($recurring)),
            ];

            foreach ($haystacks as $haystack)
            {
                if ($haystack !== '' && strpos($haystack, $search) !== false) return true;
            }

            return false;
        }));
    }

    public function get_subscriptions_tab_url(LSD_Shortcodes_Dashboard $dashboard, string $status): string
    {
        return $dashboard->add_qs_vars([
            'mode' => self::MODE,
            self::SECTION_QUERY_VAR => 'subscriptions',
            self::SUBSCRIPTIONS_STATUS_QUERY_VAR => $status,
            self::SUBSCRIPTIONS_SEARCH_QUERY_VAR => $this->get_subscriptions_search_filter(),
        ], $dashboard->url);
    }

    public function get_orders_tab_url(LSD_Shortcodes_Dashboard $dashboard, string $status): string
    {
        return $dashboard->add_qs_vars([
            'mode' => self::MODE,
            self::SECTION_QUERY_VAR => 'orders',
            self::ORDERS_STATUS_QUERY_VAR => $status,
            self::ORDERS_SEARCH_QUERY_VAR => $this->get_orders_search_filter(),
        ], $dashboard->url);
    }

    public function get_recurring_status_label(LSD_Payments_Recurring $recurring): string
    {
        $state = $this->get_recurring_state($recurring);

        if ($recurring->get_status() === LSD_Payments_Recurrings::STATUS_REFUNDED) return esc_html__('Refunded', 'listdom');
        if ($state === 'active') return esc_html__('Active', 'listdom');
        if ($state === 'disabled') return esc_html__('Expiring', 'listdom');
        if ($state === 'expired') return esc_html__('Expired', 'listdom');

        return esc_html__('Expired', 'listdom');
    }

    public function get_recurring_status_key(LSD_Payments_Recurring $recurring): string
    {
        $state = $this->get_recurring_state($recurring);

        if ($state === 'active') return 'completed';
        if ($state === 'disabled') return 'pending';
        if ($recurring->get_status() === LSD_Payments_Recurrings::STATUS_REFUNDED) return 'refunded';

        return 'failed';
    }

    public function get_subscription_type_icon(string $type)
    {
        switch ($type)
        {
            case 'membership-package':
                return 'fa-solid fa-medal';

            case 'claim':
                return 'fa-solid fa-hand';

            case 'labelize':
                return  'fa-solid fa-tag';

            case 'booking':
                return  'fa-regular fa-bookmark';

            default:
                return 'fa-solid fa-arrow-up';
        }
    }

    public function get_recurring_type_icon(string $state): string
    {
        return $state === 'expired' ? 'fa fa-ban' : 'fa fa-refresh';
    }

    public function get_recurring_type_label(LSD_Payments_Recurring $recurring): string
    {
        $item = $this->get_recurring_primary_item($recurring);

        if (is_array($item))
        {
            return $this->get_activity_item_type_label($item);
        }

        return esc_html__('Recurring Payment', 'listdom');
    }

    public function get_recurring_type_key(LSD_Payments_Recurring $recurring): string
    {
        $item = $this->get_recurring_primary_item($recurring);

        if (is_array($item))
        {
            return $this->get_activity_item_type_key($item);
        }

        return 'order';
    }

    public function get_recurring_next_renewal(LSD_Payments_Recurring $recurring): string
    {
        if ($this->get_recurring_state($recurring) === 'expired')
        {
            return esc_html__('Expired', 'listdom');
        }

        $timestamp = $this->get_recurring_next_renewal_timestamp($recurring);
        return $timestamp > 0 ? wp_date('Y-m-d', $timestamp) : esc_html__('N/A', 'listdom');
    }

    public function get_recurring_manage_url(string $dashboard_url, LSD_Payments_Recurring $recurring): string
    {
        return $this->get_recurring_detail_link($dashboard_url, $recurring->get_id());
    }

    public function get_recurring_detail(LSD_Payments_Recurring $recurring): array
    {
        $recurring_id = $recurring->get_id();
        $orders = $recurring->get_orders();
        $currency = $recurring->get_currency();
        $next_renewal_timestamp = $this->get_recurring_next_renewal_timestamp($recurring);
        $frequency_days = $this->get_recurring_frequency_days($recurring);
        $started_timestamp = $this->get_recurring_started_timestamp($recurring);
        $current_period_start = $this->get_recurring_current_period_start_timestamp($recurring, $next_renewal_timestamp, $frequency_days);
        $state = $this->get_recurring_state($recurring);
        $remaining_days = $this->get_recurring_remaining_days($next_renewal_timestamp);
        $progress_percent = $this->get_recurring_progress_percent($state, $next_renewal_timestamp, $current_period_start);
        $renewal_history = $this->get_recurring_renewal_history($orders, $currency);
        $invoice_url = $this->get_recurring_invoice_url($renewal_history);

        return [
            'recurring' => $recurring,
            'recurring_id' => $recurring_id,
            'title' => $this->get_recurring_title($recurring),
            'gateway_name' => $this->get_gateway_label($recurring->get_gateway()) ?: esc_html__('N/A', 'listdom'),
            'type' => $this->get_recurring_type_label($recurring),
            'type_key' => $this->get_recurring_type_key($recurring),
            'status' => $this->get_recurring_status_label($recurring),
            'status_key' => $this->get_recurring_status_key($recurring),
            'is_recurring' => true,
            'recurring_state' => $state,
            'recurring_badge' => $this->recurring_badge($recurring),
            'progress' => $this->get_recurring_progress($state, $remaining_days, $progress_percent, $next_renewal_timestamp),
            'details_fields' => $this->get_recurring_details_fields($recurring, $currency, $started_timestamp, $next_renewal_timestamp, $frequency_days),
            'renewal_history' => $renewal_history,
            'invoice_url' => $invoice_url,
            'disable_autorenew_enabled' => $state === 'active',
            'disable_autorenew_nonce' => wp_create_nonce('lsd_dashboard_payments_disable_autorenew'),
            'activate_autorenew_enabled' => $state === 'disabled',
            'activate_autorenew_nonce' => wp_create_nonce('lsd_dashboard_payments_activate_autorenew'),
            'alert' => $this->get_recurring_alert($state, $next_renewal_timestamp, $remaining_days),
        ];
    }

    private function get_recurring_remaining_days(int $next_renewal_timestamp): int
    {
        return $next_renewal_timestamp > 0 ? max(0, (int) ceil(($next_renewal_timestamp - time()) / DAY_IN_SECONDS)) : 0;
    }

    private function get_recurring_progress_percent(string $state, int $next_renewal_timestamp, int $current_period_start): int
    {
        $progress_percent = $state === 'expired' ? 0 : 100;

        if ($next_renewal_timestamp > 0 && $current_period_start > 0 && $next_renewal_timestamp > $current_period_start)
        {
            $progress_percent = (int) round((($next_renewal_timestamp - time()) / ($next_renewal_timestamp - $current_period_start)) * 100);
            $progress_percent = max(0, min(100, $progress_percent));
        }

        return $progress_percent;
    }

    private function get_recurring_renewal_history(array $orders, string $currency): array
    {
        $renewal_history = [];

        foreach ($orders as $order)
        {
            if (!$order instanceof LSD_Payments_Order) continue;

            $order_post = get_post($order->get_id());
            $status_key = $order_post instanceof WP_Post ? sanitize_html_class($order_post->post_status) : '';

            $renewal_history[] = [
                'date' => $order_post instanceof WP_Post ? $this->format_activity_date($this->get_activity_timestamp($order_post->post_date_gmt, $order_post->post_date)) : esc_html__('N/A', 'listdom'),
                'amount' => $this->render_price($order->get_total(), $currency, false, false),
                'status' => $order_post instanceof WP_Post ? $this->get_status_label($order_post->post_status) : esc_html__('N/A', 'listdom'),
                'status_key' => $status_key,
                'invoice_url' => $order->get_invoice_url(),
            ];
        }

        return $renewal_history;
    }

    private function get_recurring_invoice_url(array $renewal_history): string
    {
        foreach ($renewal_history as $history)
        {
            if (!empty($history['invoice_url'])) return (string) $history['invoice_url'];
        }

        return '';
    }

    private function get_recurring_progress(string $state, int $remaining_days, int $progress_percent, int $next_renewal_timestamp): array
    {
        return [
            'remaining_days' => $remaining_days,
            'percent' => $progress_percent,
            'next_renewal' => $next_renewal_timestamp > 0 ? $this->format_activity_date($next_renewal_timestamp) : esc_html__('N/A', 'listdom'),
            'label' => sprintf(
                esc_html__('%1$s days remaining', 'listdom'),
                sprintf(
                    '<span class="lsd-status-label lsd-status-%1$s">%2$d</span>',
                    esc_attr($state),
                    $remaining_days
                )
            ),
        ];
    }

    private function get_recurring_details_fields(LSD_Payments_Recurring $recurring, string $currency, int $started_timestamp, int $next_renewal_timestamp, int $frequency_days): array
    {
        $price = $this->render_price($recurring->get_total(), $currency, false, false);

        return [
            [
                'label' => esc_html__('Start Date', 'listdom'),
                'value' => $started_timestamp > 0 ? $this->format_activity_date($started_timestamp) : esc_html__('N/A', 'listdom'),
            ],
            [
                'label' => esc_html__('Expiry Date', 'listdom'),
                'value' => $next_renewal_timestamp > 0 ? $this->format_activity_date($next_renewal_timestamp) : esc_html__('N/A', 'listdom'),
            ],
            [
                'label' => esc_html__('Billing Cycle', 'listdom'),
                'value' => $this->get_recurring_billing_cycle_label($frequency_days),
            ],
            [
                'label' => esc_html__('Price', 'listdom'),
                'value' => wp_strip_all_tags($price),
                'value_html' => $price,
            ],
        ];
    }

    private function get_recurring_alert(string $state, int $next_renewal_timestamp, int $remaining_days): ?array
    {
        if ($state === 'expired') return null;

        if ($state === 'disabled')
        {
            return [
                'type' => 'lsd-error',
                'icon' => 'fa-solid fa-circle-exclamation',
                'title' => esc_html__('Auto-renewal is disabled', 'listdom'),
                'message' => sprintf(
                    esc_html__('This subscription will expire on %1$s (%2$d days). Activate the auto-renewal or extend it to avoid loosing the function and features of this subscription.', 'listdom'),
                    $next_renewal_timestamp > 0 ? $this->format_activity_date($next_renewal_timestamp) : esc_html__('N/A', 'listdom'),
                    $remaining_days
                ),
            ];
        }

        return [
            'type' => 'lsd-info',
            'icon' => 'fa-solid fa-circle-check',
            'title' => esc_html__('Auto-renewal scheduled', 'listdom'),
            'message' => sprintf(
                esc_html__('This subscription renews on %1$s (%2$d days)', 'listdom'),
                $next_renewal_timestamp > 0 ? $this->format_activity_date($next_renewal_timestamp) : esc_html__('N/A', 'listdom'),
                $remaining_days
            ),
        ];
    }

    protected function get_recurring_next_renewal_timestamp(LSD_Payments_Recurring $recurring): int
    {
        $meta = $recurring->get_gateway_meta();

        foreach (['current_period_end', 'period_end', 'next_renewal', 'next_renewal_at', 'renewal_date'] as $key)
        {
            if (!isset($meta[$key])) continue;

            $value = $meta[$key];
            $timestamp = is_numeric($value) ? (int) $value : strtotime((string) $value);
            if ($timestamp > 0) return $timestamp;
        }

        $latest_order = $recurring->get_orders()[0] ?? null;
        $latest_post = $latest_order instanceof LSD_Payments_Order ? get_post($latest_order->get_id()) : null;
        $latest_time = $latest_post instanceof WP_Post ? $this->get_activity_timestamp($latest_post->post_date_gmt, $latest_post->post_date) : 0;
        $frequency_days = $this->get_recurring_frequency_days($recurring);

        if ($latest_time < 1 || $frequency_days < 1) return 0;

        return strtotime('+' . $frequency_days . ' days', $latest_time);
    }

    protected function get_recurring_frequency_days(LSD_Payments_Recurring $recurring): int
    {
        $frequency_days = 0;

        foreach ($recurring->get_items() as $item)
        {
            if (!is_array($item)) continue;

            $days = isset($item['frequency_days']) ? (int) $item['frequency_days'] : 0;

            if ($days < 1)
            {
                $plan = new LSD_Payments_Plan((int) ($item['plan_id'] ?? 0), $item['tier_id'] ?? '');
                $days = (int) $plan->get_frequency_days();
            }

            if ($days > $frequency_days) $frequency_days = $days;
        }

        return $frequency_days;
    }

    protected function get_recurring_started_timestamp(LSD_Payments_Recurring $recurring): int
    {
        $first_order = $recurring->get_first_order();
        $first_post = $first_order instanceof LSD_Payments_Order ? get_post($first_order->get_id()) : null;

        return $first_post instanceof WP_Post ? $this->get_activity_timestamp($first_post->post_date_gmt, $first_post->post_date) : 0;
    }

    protected function get_recurring_current_period_start_timestamp(LSD_Payments_Recurring $recurring, int $next_renewal_timestamp, int $frequency_days): int
    {
        $latest_order = $recurring->get_orders()[0] ?? null;
        $latest_post = $latest_order instanceof LSD_Payments_Order ? get_post($latest_order->get_id()) : null;
        $latest_timestamp = $latest_post instanceof WP_Post ? $this->get_activity_timestamp($latest_post->post_date_gmt, $latest_post->post_date) : 0;

        if ($latest_timestamp > 0) return $latest_timestamp;
        if ($next_renewal_timestamp > 0 && $frequency_days > 0) return strtotime('-' . $frequency_days . ' days', $next_renewal_timestamp);

        return $this->get_recurring_started_timestamp($recurring);
    }

    protected function get_recurring_billing_cycle_label(int $frequency_days): string
    {
        if ($frequency_days < 1) return esc_html__('N/A', 'listdom');

        return sprintf(esc_html__('%d days', 'listdom'), $frequency_days);
    }

    protected function get_listing_title(int $listing_id): string
    {
        if ($listing_id < 1) return '';

        $listing = get_post($listing_id);
        return $listing instanceof WP_Post ? $listing->post_title : '';
    }

    protected function get_package_title(int $package_id, ?LSD_Payments_Plan $plan = null): string
    {
        if ($package_id > 0 && class_exists('\LSDPACSUB\Package') && get_post_type($package_id) === \LSDPACSUB\Base::PTYPE_PACKAGE)
        {
            $package = new \LSDPACSUB\Package($package_id);
            $title = trim($package->title());
            if ($title !== '') return $title;
        }

        $package = $package_id > 0 ? get_post($package_id) : null;
        if ($package instanceof WP_Post && trim($package->post_title) !== '') return $package->post_title;

        return $plan instanceof LSD_Payments_Plan ? $plan->get_title() : '';
    }
}
