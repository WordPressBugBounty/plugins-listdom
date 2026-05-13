<?php

class LSD_Payments_Notifications extends LSD_Notifications
{
    public function init()
    {
        add_filter('lsd_notification_hooks', [$this, 'hooks']);
        add_filter('lsd_notification_placeholders', [$this, 'register_placeholders']);

        foreach ([
            'pending',
            'on_hold',
            'completed',
            'canceled',
            'failed',
            'draft',
            'refunded',
            'new_receiver',
            'new_payer',
        ] as $event) add_action('lsd_payments_order_' . $event, [$this, 'order']);
    }

    public function hooks($hooks)
    {
        $hooks['lsd_payments_order_pending'] = esc_html__('Order Pending', 'listdom');
        $hooks['lsd_payments_order_on_hold'] = esc_html__('Order On Hold', 'listdom');
        $hooks['lsd_payments_order_completed'] = esc_html__('Order Completed', 'listdom');
        $hooks['lsd_payments_order_canceled'] = esc_html__('Order Canceled', 'listdom');
        $hooks['lsd_payments_order_failed'] = esc_html__('Order Failed', 'listdom');
        $hooks['lsd_payments_order_draft'] = esc_html__('Order Draft', 'listdom');
        $hooks['lsd_payments_order_refunded'] = esc_html__('Order Refunded', 'listdom');
        $hooks['lsd_payments_order_new_receiver'] = esc_html__('New Order (Receiver)', 'listdom');
        $hooks['lsd_payments_order_new_payer'] = esc_html__('New Order (Payer)', 'listdom');

        return $hooks;
    }

    public function register_placeholders($placeholders): array
    {
        $order_placeholders = [
            'order_id' => esc_html__('Order ID', 'listdom'),
            'order_status' => esc_html__('Order Status', 'listdom'),
            'order_total' => esc_html__('Order Total', 'listdom'),
            'order_subtotal' => esc_html__('Order Subtotal', 'listdom'),
            'order_discount' => esc_html__('Order Discount', 'listdom'),
            'order_coupon' => esc_html__('Order Coupon', 'listdom'),
            'order_gateway' => esc_html__('Gateway Name', 'listdom'),
            'order_message' => esc_html__('Order Comment', 'listdom'),
            'order_items' => esc_html__('Order Items', 'listdom'),
            'order_admin_link' => esc_html__('Link to the order manage page in WordPress backend', 'listdom'),
            'order_invoice_url' => esc_html__('Order Invoice URL', 'listdom'),
            'customer_name' => esc_html__('Customer Name', 'listdom'),
            'customer_email' => esc_html__('Customer Email', 'listdom'),
        ];

        foreach ([
            'lsd_payments_order_pending',
            'lsd_payments_order_on_hold',
            'lsd_payments_order_completed',
            'lsd_payments_order_canceled',
            'lsd_payments_order_failed',
            'lsd_payments_order_draft',
            'lsd_payments_order_refunded',
            'lsd_payments_order_new_receiver',
            'lsd_payments_order_new_payer',
        ] as $hook) $placeholders[$hook] = $order_placeholders;

        return $placeholders;
    }

    public function order(int $order_id): array
    {
        $hook = current_filter();

        $notifications = $this->get($hook);
        if (!$notifications) return [];

        $order = new LSD_Payments_Order($order_id);

        $sts = get_post_status_object(get_post_status($order_id));
        $status = $sts->label ?? '';

        $currency = LSD_Options::currency();
        $total = $this->render_price($order->get_total(), $currency, false, false);
        $subtotal = $this->render_price($order->get_subtotal(), $currency, false, false);
        $discount = $this->render_price($order->get_discount(), $currency, false, false);
        $coupon = $order->get_coupon();

        $gateway_key = $order->get_gateway();
        $gateway_obj = $gateway_key ? LSD_Payments::gateway($gateway_key) : null;
        $gateway_name = $gateway_obj ? $gateway_obj->name() : '';

        $message = $order->get_message();
        $invoice_url = $order->get_invoice_url();
        $admin_link = get_edit_post_link($order_id);

        $items_table = '';
        $items = $order->get_items();
        $fees = $order->get_fees();
        if ($items || $fees)
        {
            $rows = [];
            foreach ($items as $item)
            {
                $plan_id = (int)($item['plan_id'] ?? 0);
                $tier_id = $item['tier_id'] ?? '';
                $plan = new LSD_Payments_Plan($plan_id, $tier_id);
                $tier = $plan->get_tier();

                $title = get_the_title($plan_id);
                if ($tier) $title .= ' - ' . $tier->get_name();

                $metas = $order->get_metas($item);
                if ($metas) $title .= '<br>' . implode('<br>', $metas);

                $rows[] = '<tr><td>' . $title . '</td></tr>';
            }

            foreach ($fees as $fee)
            {
                $title = isset($fee['title']) ? trim((string) $fee['title']) : '';
                $amount = isset($fee['amount']) ? (float) $fee['amount'] : 0;

                $line = $title !== '' ? esc_html($title) : esc_html__('Fee', 'listdom');
                if ($amount !== 0) $line .= ' - ' . $this->render_price($amount, $currency, false, false);

                $rows[] = '<tr><td>' . $line . '</td></tr>';
            }

            $items_table = '<table>' . implode('', $rows) . '</table>';
        }

        // Original Receiver
        $original = $order->get_email() ?: get_bloginfo('admin_email');
        $user_id = $order->get_user_id() ?: $this->admin_id();

        // Admin Notification
        if ($hook === 'lsd_payments_order_new_receiver')
        {
            $original = get_bloginfo('admin_email');
            $user_id = $this->admin_id();
        }

        $mails = [];
        foreach ($notifications as $notification)
        {
            $content = get_post_meta($notification->ID, 'lsd_content', true);
            $subject = get_the_title($notification);

            $original_to = get_post_meta($notification->ID, 'lsd_original_to', true);
            $to = $original_to
                ? $original
                : trim(get_post_meta($notification->ID, 'lsd_to', true), ', ');

            $cc = trim(get_post_meta($notification->ID, 'lsd_cc', true), ', ');
            $bcc = trim(get_post_meta($notification->ID, 'lsd_bcc', true), ', ');

            foreach (['content', 'subject'] as $item)
            {
                $$item = str_replace('#order_id#', $order_id, $$item);
                $$item = str_replace('#order_status#', $status, $$item);
                $$item = str_replace('#order_total#', $total, $$item);
                $$item = str_replace('#order_subtotal#', $subtotal, $$item);
                $$item = str_replace('#order_discount#', $discount, $$item);
                $$item = str_replace('#order_coupon#', $coupon ?: '', $$item);
                $$item = str_replace('#order_gateway#', $gateway_name, $$item);
                $$item = str_replace('#order_message#', $message, $$item);
                $$item = str_replace('#order_items#', $items_table, $$item);
                $$item = str_replace('#customer_name#', $order->get_name(), $$item);
                $$item = str_replace('#customer_email#', $order->get_email(), $$item);
                $$item = str_replace('#order_admin_link#', '<a href="' . $admin_link . '">' . $admin_link . '</a>', $$item);
                $$item = str_replace('#order_invoice_url#', $invoice_url, $$item);
            }

            $sender = new LSD_Notifications_Email_Sender();
            $mails[] = $sender->boot($notification->ID)
                ->to($to)
                ->cc($cc)
                ->bcc($bcc)
                ->subject($subject)
                ->content($content)
                ->render()
                ->send();

            do_action('lsd_send_notification', $user_id, [
                'order_id' => $order->get_id(),
                'order_status' => $status,
                'order_total' => $total,
                'order_subtotal' => $subtotal,
                'order_discount' => $discount,
                'order_coupon' => $coupon,
                'order_gateway' => $gateway_name,
                'order_message' => $message,
                'order_items' => $items_table,
                'customer_name' => $order->get_name(),
                'customer_email' => $order->get_email(),
                'order_admin_link' => $admin_link,
                'order_invoice_url' => $invoice_url,
            ], $notification->ID, $order_id);
        }

        return $mails;
    }
}
