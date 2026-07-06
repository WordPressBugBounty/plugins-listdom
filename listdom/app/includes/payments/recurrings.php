<?php

class LSD_Payments_Recurrings extends LSD_Base
{
    const STATUS_ACTIVE = 'lsd-recurring-active';
    const STATUS_CANCEL = 'lsd-recurring-cancel';
    const STATUS_REFUNDED = 'lsd-recurring-refunded';

    protected static array $direct_gateway_meta_keys = [
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_price_id',
        'stripe_payment_method_id',
        'stripe_setup_intent_id',
    ];

    public static function add(array $args = []): int
    {
        $status = isset($args['status']) ? sanitize_key($args['status']) : self::STATUS_ACTIVE;
        if (!in_array($status, [self::STATUS_ACTIVE, self::STATUS_CANCEL, self::STATUS_REFUNDED], true)) $status = self::STATUS_ACTIVE;

        $title = isset($args['title']) ? trim((string) $args['title']) : '';

        if ($title === '') $title = self::generate_title($args);
        if ($title === '') $title = wp_date('Y-m-d H:i:s');

        $post = [
            'post_type' => LSD_Base::PTYPE_RECURRING,
            'post_status' => $status,
            'post_title' => sanitize_text_field($title),
        ];

        $recurring_id = wp_insert_post($post, true);
        if (is_wp_error($recurring_id)) return 0;

        $first_order_id = isset($args['first_order_id']) ? (int) $args['first_order_id'] : 0;

        update_post_meta($recurring_id, 'lsd_first_order_id', $first_order_id);
        update_post_meta($recurring_id, 'lsd_gateway', isset($args['gateway']) ? sanitize_text_field($args['gateway']) : '');
        update_post_meta($recurring_id, 'lsd_user_id', isset($args['user_id']) ? (int) $args['user_id'] : 0);

        if (isset($args['items'])) update_post_meta($recurring_id, 'lsd_items', $args['items']);
        if (isset($args['total'])) update_post_meta($recurring_id, 'lsd_total', (float) $args['total']);
        if (isset($args['currency'])) update_post_meta($recurring_id, 'lsd_currency', sanitize_text_field($args['currency']));

        if ($first_order_id > 0)
        {
            self::link_first_order($first_order_id, (int) $recurring_id, $args);
        }

        if (isset($args['gateway_meta']) && is_array($args['gateway_meta'])) self::update_gateway_meta($recurring_id, $args['gateway_meta'], false);

        return (int) $recurring_id;
    }

    protected static function link_first_order(int $first_order_id, int $recurring_id, array $args = []): void
    {
        if ($first_order_id < 1 || $recurring_id < 1) return;

        $force_single_link = !empty($args['force_single_order_link']);
        $skip_single_link = !empty($args['skip_single_order_link']);
        $recurring_items = isset($args['items']) && is_array($args['items']) ? $args['items'] : [];
        $order = LSD_Payments_Orders::get($first_order_id);
        $order_items = $order instanceof LSD_Payments_Order ? $order->get_items() : [];
        $is_multi_item_initial_order = count($order_items) > 1 && count($recurring_items) === 1;

        if (method_exists('LSD_Payments_Orders', 'add_recurring_id'))
        {
            LSD_Payments_Orders::add_recurring_id($first_order_id, $recurring_id);
        }
        else
        {
            $ids = get_post_meta($first_order_id, 'lsd_recurring_ids', true);
            $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('intval', $ids)))) : [];
            if (!in_array($recurring_id, $ids, true)) $ids[] = $recurring_id;
            update_post_meta($first_order_id, 'lsd_recurring_ids', $ids);
        }

        // For a normal single-recurring order, preserve legacy behavior and mark the first
        // order as recurring payment number 1. For multi-recurring initial checkout orders,
        // do not overwrite lsd_recurring_id with only one of the recurring records.
        if (!$skip_single_link && ($force_single_link || !$is_multi_item_initial_order))
        {
            LSD_Payments_Orders::set_recurring_number($first_order_id, $recurring_id, 1);
        }
        else if ($is_multi_item_initial_order)
        {
            delete_post_meta($first_order_id, 'lsd_recurring_id');
        }
    }

    protected static function sanitize_gateway_meta(array $meta): array
    {
        $sanitized = [];

        foreach ($meta as $key => $value)
        {
            $meta_key = sanitize_key($key);
            if ($meta_key === '') continue;

            if (is_array($value))
            {
                $items = [];
                foreach ($value as $item)
                {
                    if (!is_scalar($item)) continue;

                    $item_value = sanitize_text_field((string) $item);
                    if ($item_value === '') continue;

                    $items[] = $item_value;
                }

                if ($items) $sanitized[$meta_key] = $items;
            }
            else
            {
                $value = sanitize_text_field((string) $value);
                if ($value === '') continue;

                $sanitized[$meta_key] = $value;
            }
        }

        return $sanitized;
    }

    public static function update_gateway_meta(int $recurring_id, array $meta, bool $merge = true): bool
    {
        if (!$recurring_id) return false;

        $existing = get_post_meta($recurring_id, 'lsd_gateway_meta', true);
        $existing = is_array($existing) ? $existing : [];

        $sanitized = self::sanitize_gateway_meta($meta);
        if ($merge)
        {
            if (!$sanitized) return true;
            $updated = array_merge($existing, $sanitized);
        }
        else $updated = $sanitized;

        if ($updated) update_post_meta($recurring_id, 'lsd_gateway_meta', $updated);
        else delete_post_meta($recurring_id, 'lsd_gateway_meta');

        self::sync_direct_gateway_meta($recurring_id, $updated);

        return true;
    }

    protected static function sync_direct_gateway_meta(int $recurring_id, array $meta): void
    {
        if ($recurring_id < 1) return;

        foreach (self::$direct_gateway_meta_keys as $key)
        {
            if (isset($meta[$key]) && is_scalar($meta[$key]) && trim((string) $meta[$key]) !== '')
            {
                update_post_meta($recurring_id, 'lsd_' . $key, sanitize_text_field((string) $meta[$key]));
            }
            else
            {
                delete_post_meta($recurring_id, 'lsd_' . $key);
            }
        }
    }

    public static function find_by_gateway_meta(string $gateway, string $meta_key, string $meta_value): ?LSD_Payments_Recurring
    {
        $gateway = sanitize_text_field($gateway);
        $meta_key = sanitize_key($meta_key);
        $meta_value = sanitize_text_field($meta_value);

        if ($gateway === '' || $meta_key === '' || $meta_value === '') return null;

        $direct_meta_key = 'lsd_' . $meta_key;
        if (in_array($meta_key, self::$direct_gateway_meta_keys, true))
        {
            $direct_ids = get_posts([
                'post_type' => LSD_Base::PTYPE_RECURRING,
                'post_status' => 'any',
                'numberposts' => 1,
                'fields' => 'ids',
                'suppress_filters' => true,
                'meta_query' => [
                    [
                        'key' => 'lsd_gateway',
                        'value' => $gateway,
                        'compare' => '=',
                    ],
                    [
                        'key' => $direct_meta_key,
                        'value' => $meta_value,
                        'compare' => '=',
                    ],
                ],
            ]);

            if ($direct_ids)
            {
                $recurring = self::get((int) $direct_ids[0]);
                if ($recurring instanceof LSD_Payments_Recurring) return $recurring;
            }
        }

        $recurring_ids = get_posts([
            'post_type' => LSD_Base::PTYPE_RECURRING,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids',
            'suppress_filters' => true,
            'meta_query' => [
                [
                    'key' => 'lsd_gateway',
                    'value' => $gateway,
                    'compare' => '=',
                ],
                [
                    'key' => 'lsd_gateway_meta',
                    'value' => $meta_value,
                    'compare' => 'LIKE',
                ],
            ],
        ]);

        foreach ($recurring_ids as $recurring_id)
        {
            $recurring = self::get((int) $recurring_id);
            if (!$recurring) continue;

            $meta = $recurring->get_gateway_meta();
            if (isset($meta[$meta_key]) && (string) $meta[$meta_key] === $meta_value)
            {
                return $recurring;
            }
        }

        return null;
    }

    public static function find_by_stripe_subscription_id(string $subscription_id): ?LSD_Payments_Recurring
    {
        return self::find_by_gateway_meta('stripe', 'stripe_subscription_id', $subscription_id);
    }

    public static function find_all_by_first_order_id(int $order_id): array
    {
        if ($order_id < 1) return [];

        $recurring_ids = get_posts([
            'post_type' => LSD_Base::PTYPE_RECURRING,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids',
            'suppress_filters' => true,
            'meta_key' => 'lsd_first_order_id',
            'meta_value' => $order_id,
        ]);

        $recurrings = [];
        foreach ($recurring_ids as $recurring_id)
        {
            $recurring = self::get((int) $recurring_id);
            if ($recurring instanceof LSD_Payments_Recurring) $recurrings[] = $recurring;
        }

        return $recurrings;
    }

    public static function find_by_first_order_id(int $order_id): ?LSD_Payments_Recurring
    {
        if (!$order_id) return null;

        $recurrings = self::find_all_by_first_order_id($order_id);
        return $recurrings[0] ?? null;
    }

    protected static function generate_title(array $args): string
    {
        $email = '';
        $items = isset($args['items']) && is_array($args['items']) ? $args['items'] : [];

        $first_order_id = isset($args['first_order_id']) ? (int) $args['first_order_id'] : 0;
        if ($first_order_id)
        {
            $order = LSD_Payments_Orders::get($first_order_id);
            if ($order instanceof LSD_Payments_Order)
            {
                $email = trim($order->get_email());
                if ($email === '')
                {
                    $order_user = $order->get_user();
                    if ($order_user instanceof WP_User && $order_user->user_email)
                    {
                        $email = trim($order_user->user_email);
                    }
                }

                if (!$items)
                {
                    $items = $order->get_items();
                }
            }
        }

        if ($email === '' && isset($args['user_id']))
        {
            $user = get_userdata((int) $args['user_id']);
            if ($user instanceof WP_User && $user->user_email)
            {
                $email = trim($user->user_email);
            }
        }

        $item_titles = [];
        foreach ($items as $item)
        {
            if (!is_array($item)) continue;

            $plan_id = isset($item['plan_id']) ? (int) $item['plan_id'] : 0;
            if (!$plan_id) continue;

            $tier_id = isset($item['tier_id']) ? (string) $item['tier_id'] : '';

            $plan = new LSD_Payments_Plan($plan_id, $tier_id !== '' ? $tier_id : null);
            $plan_name = trim($plan->get_name());

            $line = $plan_name;
            $tier = $plan->get_tier();
            if ($tier)
            {
                $tier_name = trim($tier->get_name());
                if ($tier_name !== '')
                {
                    $tiers = $plan->get_tiers();
                    if (count($tiers) > 1)
                    {
                        if ($line !== '') $line .= ' - ' . $tier_name;
                        else $line = $tier_name;
                    }
                    else if ($line === '') $line = $tier_name;
                }
            }

            if ($line !== '') $item_titles[] = $line;
        }

        $parts = [];

        if ($email !== '') $parts[] = $email;
        if ($item_titles) $parts[] = implode(', ', $item_titles);

        return trim(implode(' - ', $parts));
    }

    public static function get(int $id): ?LSD_Payments_Recurring
    {
        $post = self::get_entity_post($id, LSD_Base::PTYPE_RECURRING);
        if (!$post instanceof WP_Post) return null;

        return new LSD_Payments_Recurring($id);
    }

    public static function by_user(int $user_id, int $limit = -1): array
    {
        $recurrings = [];
        foreach (self::get_user_post_ids_by_meta('lsd_user_id', $user_id, $limit) as $recurring_id)
        {
            $recurring = self::get((int) $recurring_id);
            if ($recurring instanceof LSD_Payments_Recurring) $recurrings[] = $recurring;
        }

        return $recurrings;
    }

    protected static function set_status(int $recurring_id, string $status): bool
    {
        $result = wp_update_post([
            'ID' => $recurring_id,
            'post_status' => $status,
        ], true);

        return !is_wp_error($result);
    }

    public static function active(int $recurring_id): bool
    {
        $changed = self::set_status($recurring_id, self::STATUS_ACTIVE);
        if ($changed) do_action('lsd_payments_recurring_active', $recurring_id);

        return $changed;
    }

    public static function cancel(int $recurring_id): bool
    {
        $changed = self::set_status($recurring_id, self::STATUS_CANCEL);
        if ($changed) do_action('lsd_payments_recurring_cancel', $recurring_id);

        return $changed;
    }

    public static function refunded(int $recurring_id): bool
    {
        $changed = self::set_status($recurring_id, self::STATUS_REFUNDED);
        if ($changed) do_action('lsd_payments_recurring_refunded', $recurring_id);

        return $changed;
    }

    public static function update_status(int $recurring_id, string $status): bool
    {
        if ($status === self::STATUS_REFUNDED) return self::refunded($recurring_id);
        if ($status === self::STATUS_CANCEL) return self::cancel($recurring_id);
        if ($status === self::STATUS_ACTIVE) return self::active($recurring_id);

        return false;
    }
}
