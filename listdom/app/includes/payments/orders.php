<?php

class LSD_Payments_Orders extends LSD_Base
{
    public static function add(array $args = []): int
    {
        $recurring_id = isset($args['recurring_id']) ? (int) $args['recurring_id'] : 0;
        $payment_number = $recurring_id ? self::get_next_recurring_number($recurring_id) : 0;

        $title = $args['title'] ?? wp_date('Y-m-d H:i:s');
        if ($payment_number > 0) $title = self::format_recurring_title($title, $payment_number);

        $post = [
            'post_type' => LSD_Base::PTYPE_ORDER,
            'post_status' => LSD_Payments::STATUS_PENDING,
            'post_title' => $title,
        ];

        $order_id = wp_insert_post($post, true);
        if (is_wp_error($order_id)) return 0;

        if (isset($args['items'])) update_post_meta($order_id, 'lsd_items', $args['items']);
        if (isset($args['fees']) && is_array($args['fees'])) update_post_meta($order_id, 'lsd_fees', $args['fees']);
        if (isset($args['subtotal'])) update_post_meta($order_id, 'lsd_subtotal', $args['subtotal']);
        if (isset($args['discount'])) update_post_meta($order_id, 'lsd_discount', $args['discount']);
        if (isset($args['tax'])) update_post_meta($order_id, 'lsd_tax', $args['tax']);
        if (isset($args['tax_items']) && is_array($args['tax_items'])) update_post_meta($order_id, 'lsd_tax_items', $args['tax_items']);
        if (isset($args['total'])) update_post_meta($order_id, 'lsd_total', $args['total']);
        if (isset($args['gateway'])) update_post_meta($order_id, 'lsd_gateway', $args['gateway']);
        if (isset($args['coupon'])) update_post_meta($order_id, 'lsd_coupon', $args['coupon']);
        if (isset($args['user_id'])) update_post_meta($order_id, 'lsd_user_id', (int) $args['user_id']);
        if (isset($args['name'])) update_post_meta($order_id, 'lsd_name', sanitize_text_field($args['name']));
        if (isset($args['email'])) update_post_meta($order_id, 'lsd_email', sanitize_email($args['email']));
        if (isset($args['message'])) update_post_meta($order_id, 'lsd_message', sanitize_textarea_field($args['message']));
        if (isset($args['tax_location']) && is_array($args['tax_location']))
        {
            $location = [
                'country' => sanitize_text_field($args['tax_location']['country'] ?? ''),
                'state' => sanitize_text_field($args['tax_location']['state'] ?? ''),
            ];

            update_post_meta($order_id, 'lsd_tax_location', $location);
        }

        if (isset($args['tax_prices_include']))
        {
            update_post_meta($order_id, 'lsd_tax_prices_include', (int) $args['tax_prices_include'] ? 1 : 0);
        }

        // Recurring ID
        update_post_meta($order_id, 'lsd_recurring_id', $recurring_id);

        if ($payment_number > 0) self::set_recurring_number($order_id, $recurring_id, $payment_number);

        // Unique Order Key
        $key = LSD_id::code(32);
        update_post_meta($order_id, 'lsd_key', $key);

        do_action('lsd_payments_order_new_receiver', $order_id);
        do_action('lsd_payments_order_new_payer', $order_id);

        return (int) $order_id;
    }

    public static function get(int $id): ?LSD_Payments_Order
    {
        $post = get_post($id);
        if (!$post || $post->post_type !== LSD_Base::PTYPE_ORDER) return null;

        return new LSD_Payments_Order($id);
    }

    public static function get_by_key(string $key): ?LSD_Payments_Order
    {
        $id = LSD_Main::get_post_id_by_meta('lsd_key', $key);
        if (!$id) return null;

        return self::get($id);
    }

    protected static function get_next_recurring_number(int $recurring_id): int
    {
        if ($recurring_id < 1) return 0;

        $next = (int) get_post_meta($recurring_id, 'lsd_recurring_next_number', true);
        if ($next < 1) $next = 1;

        return $next;
    }

    protected static function mark_recurring_number_used(int $recurring_id, int $payment_number): void
    {
        if ($recurring_id < 1 || $payment_number < 1) return;

        $next = $payment_number + 1;
        $current = (int) get_post_meta($recurring_id, 'lsd_recurring_next_number', true);

        if ($current < $next)
        {
            update_post_meta($recurring_id, 'lsd_recurring_next_number', $next);
        }
    }

    protected static function title_without_recurring_number($title, int $number): string
    {
        if (!is_string($title))
        {
            $title = is_scalar($title) ? (string) $title : '';
        }

        $title = trim($title);
        if ($title === '' || $number < 1) return $title;

        $pattern = '/\s*\(' . preg_quote((string) $number, '/') . '\)\s*$/u';
        $stripped = preg_replace($pattern, '', $title, 1, $count);

        if (is_string($stripped) && $count > 0)
        {
            $title = trim(preg_replace('/\s{2,}/u', ' ', $stripped));
        }

        return $title;
    }

    public static function title_without_recurring_suffix(int $order_id, $title = ''): string
    {
        if (!is_string($title) || $title === '')
        {
            $title = get_post_field('post_title', $order_id);
        }

        $number = $order_id > 0 ? (int) get_post_meta($order_id, 'lsd_recurring_number', true) : 0;

        return self::title_without_recurring_number($title, $number);
    }

    protected static function format_recurring_title($title, int $payment_number): string
    {
        if (!is_string($title))
        {
            $title = is_scalar($title) ? (string) $title : '';
        }

        $title = trim($title);
        if ($title === '') return '';

        $clean_title = self::title_without_recurring_number($title, $payment_number);
        if ($payment_number > 1 && $clean_title === $title)
        {
            $clean_title = self::title_without_recurring_number($title, $payment_number - 1);
        }
        if ($clean_title === '') $clean_title = $title;

        return trim($clean_title) . ' (' . $payment_number . ')';
    }

    public static function set_recurring_number(int $order_id, int $recurring_id, int $payment_number): void
    {
        if ($order_id < 1 || $payment_number < 1) return;

        if ($recurring_id > 0)
        {
            update_post_meta($order_id, 'lsd_recurring_id', $recurring_id);
        }

        update_post_meta($order_id, 'lsd_recurring_number', $payment_number);

        self::mark_recurring_number_used($recurring_id, $payment_number);

        $current_title = get_post_field('post_title', $order_id);
        if (!is_string($current_title) || $current_title === '') return;

        $updated_title = self::format_recurring_title($current_title, $payment_number);
        if ($updated_title === '' || $updated_title === $current_title) return;

        wp_update_post([
            'ID' => $order_id,
            'post_title' => $updated_title,
        ]);
    }

    protected static function set_status(int $order_id, string $status): bool
    {
        $result = wp_update_post([
            'ID' => $order_id,
            'post_status' => $status,
        ], true);

        return !is_wp_error($result);
    }

    public static function pending(int $order_id): bool
    {
        $changed = self::set_status($order_id, LSD_Payments::STATUS_PENDING);
        if ($changed) do_action('lsd_payments_order_pending', $order_id);

        return $changed;
    }

    public static function on_hold(int $order_id): bool
    {
        $changed = self::set_status($order_id, LSD_Payments::STATUS_ON_HOLD);
        if ($changed) do_action('lsd_payments_order_on_hold', $order_id);

        return $changed;
    }

    public static function completed(int $order_id): bool
    {
        $changed = self::set_status($order_id, LSD_Payments::STATUS_COMPLETED);
        if ($changed) do_action('lsd_payments_order_completed', $order_id);

        return $changed;
    }

    public static function canceled(int $order_id): bool
    {
        $changed = self::set_status($order_id, LSD_Payments::STATUS_CANCELED);
        if ($changed) do_action('lsd_payments_order_canceled', $order_id);

        return $changed;
    }

    public static function failed(int $order_id): bool
    {
        $changed = self::set_status($order_id, LSD_Payments::STATUS_FAILED);
        if ($changed) do_action('lsd_payments_order_failed', $order_id);

        return $changed;
    }

    public static function draft(int $order_id): bool
    {
        $changed = self::set_status($order_id, LSD_Payments::STATUS_DRAFT);
        if ($changed) do_action('lsd_payments_order_draft', $order_id);

        return $changed;
    }

    public static function refunded(int $order_id): bool
    {
        $changed = self::set_status($order_id, LSD_Payments::STATUS_REFUNDED);
        if ($changed) do_action('lsd_payments_order_refunded', $order_id);

        return $changed;
    }

    public static function update_status(int $order_id, string $status): bool
    {
        // Refund
        if ($status === LSD_Payments::STATUS_REFUNDED) return LSD_Payments_Orders::refunded($order_id);

        // Draft
        if ($status === LSD_Payments::STATUS_DRAFT) return LSD_Payments_Orders::draft($order_id);

        // Failed
        if ($status === LSD_Payments::STATUS_FAILED) return LSD_Payments_Orders::failed($order_id);

        // Canceled
        if ($status === LSD_Payments::STATUS_CANCELED) return LSD_Payments_Orders::canceled($order_id);

        // Completed
        if ($status === LSD_Payments::STATUS_COMPLETED) return LSD_Payments_Orders::completed($order_id);

        // On-Hold
        if ($status === LSD_Payments::STATUS_ON_HOLD) return LSD_Payments_Orders::on_hold($order_id);

        // Pending
        if ($status === LSD_Payments::STATUS_PENDING) return LSD_Payments_Orders::pending($order_id);

        return false;
    }
}
