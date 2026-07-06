<?php

class LSD_API_Controllers_Payments_Stripe extends LSD_API_Controller
{
    public function webhook(WP_REST_Request $request): WP_REST_Response
    {
        $gateway = LSD_Payments::gateway('stripe');
        if (!$gateway instanceof LSD_Payments_Gateways_Stripe)
        {
            return $this->response([
                'data' => [
                    'success' => 0,
                    'message' => esc_html__('Stripe gateway is not available.', 'listdom'),
                ],
                'status' => 500,
            ]);
        }

        $secret = $gateway->webhook_secret();
        if ($secret === '')
        {
            return $this->response([
                'data' => [
                    'success' => 0,
                    'message' => esc_html__('Stripe webhook signing secret is not configured.', 'listdom'),
                ],
                'status' => 400,
            ]);
        }

        $body = $request->get_body();
        if (!is_string($body)) $body = '';

        if ($body === '')
        {
            return $this->response([
                'data' => [
                    'success' => 0,
                    'message' => esc_html__('Stripe webhook payload is empty.', 'listdom'),
                ],
                'status' => 400,
            ]);
        }

        if (!$this->verify_signature($request, $body, $secret))
        {
            return $this->response([
                'data' => [
                    'success' => 0,
                    'message' => esc_html__('Stripe webhook signature could not be verified.', 'listdom'),
                ],
                'status' => 401,
            ]);
        }

        $payload = json_decode($body, true);
        if (!is_array($payload))
        {
            return $this->response([
                'data' => [
                    'success' => 0,
                    'message' => esc_html__('Invalid Stripe payload.', 'listdom'),
                ],
                'status' => 400,
            ]);
        }

        $type = isset($payload['type']) ? (string) $payload['type'] : '';
        $handled = false;

        if (in_array($type, ['invoice.payment_succeeded', 'invoice.paid'], true))
        {
            $handled = $this->handle_invoice_payment_succeeded($payload);
        }
        else if ($type === 'invoice.payment_failed')
        {
            $handled = $this->handle_invoice_payment_failed($payload);
        }
        else if ($type === 'customer.subscription.updated')
        {
            $handled = $this->handle_subscription_updated($payload);
        }
        else if ($type === 'customer.subscription.deleted')
        {
            $handled = $this->handle_subscription_deleted($payload);
        }

        return $this->response([
            'data' => [
                'success' => 1,
                'handled' => $handled ? 1 : 0,
            ],
            'status' => 200,
        ]);
    }

    protected function verify_signature(WP_REST_Request $request, string $payload, string $secret): bool
    {
        $header = (string) $request->get_header('stripe-signature');
        if ($header === '') return false;

        $tolerance = (int) apply_filters('lsd_payments_stripe_webhook_tolerance', 300);
        $tolerance = $tolerance > 0 ? $tolerance : null;

        if (class_exists(\Stripe\WebhookSignature::class))
        {
            try
            {
                \Stripe\WebhookSignature::verifyHeader($payload, $header, $secret, $tolerance);
                return true;
            }
            catch (Exception $exception)
            {
                do_action('lsd_payments_stripe_webhook_signature_error', $exception);
                return false;
            }
        }

        return $this->verify_signature_manually($payload, $secret, $header, $tolerance);
    }

    protected function verify_signature_manually(string $payload, string $secret, string $header, ?int $tolerance): bool
    {
        $timestamp = 0;
        $signatures = [];

        foreach (explode(',', $header) as $part)
        {
            $pair = explode('=', trim($part), 2);
            if (count($pair) !== 2) continue;

            [$key, $value] = $pair;
            if ($key === 't') $timestamp = (int) $value;
            else if ($key === 'v1') $signatures[] = trim($value);
        }

        if (!$timestamp || !$signatures) return false;

        if ($tolerance !== null && $timestamp < (time() - $tolerance)) return false;

        $signed_payload = $timestamp . '.' . $payload;
        $expected_signature = hash_hmac('sha256', $signed_payload, $secret);

        foreach ($signatures as $signature)
        {
            if (hash_equals($expected_signature, $signature)) return true;
        }

        return false;
    }

    protected function handle_invoice_payment_succeeded(array $event): bool
    {
        $invoice = isset($event['data']['object']) && is_array($event['data']['object'])
            ? $event['data']['object']
            : null;

        if (!$invoice) return false;

        $billing_reason = isset($invoice['billing_reason']) ? (string) $invoice['billing_reason'] : '';
        if ($billing_reason === 'subscription_create') return false;

        [$recurrings, $context] = $this->resolve_recurrings_from_invoice($invoice);
        if (!count($recurrings)) return false;

        $subscription_id = isset($context['subscription_id']) ? (string) $context['subscription_id'] : '';
        $customer_id = isset($context['customer_id']) ? (string) $context['customer_id'] : '';

        $invoice_id = isset($invoice['id']) ? (string) $invoice['id'] : '';

        $amount_total = isset($invoice['amount_paid']) ? (int) $invoice['amount_paid'] : 0;
        if ($amount_total <= 0 && isset($invoice['amount_due']))
        {
            $amount_total = (int) $invoice['amount_due'];
        }

        $currency = isset($invoice['currency']) ? strtoupper((string) $invoice['currency']) : '';

        $gateway = LSD_Payments::gateway('stripe');
        $handled = false;
        $period = $this->extract_subscription_period_from_invoice($invoice);
        $recurring_count = count($recurrings);

        foreach ($recurrings as $recurring)
        {
            if (!$recurring instanceof LSD_Payments_Recurring) continue;

            $recurring_id = $recurring->get_id();
            if ($recurring_id <= 0) continue;

            if ($invoice_id !== '' && $this->invoice_order_exists_for_recurring($invoice_id, $recurring_id))
            {
                $handled = true;
                continue;
            }

            $items = $recurring->get_items();
            if (!$items) continue;

            $recurring_currency = $currency !== '' ? $currency : $recurring->get_currency();
            $total = $this->get_invoice_total_for_recurring($invoice, $recurring, $gateway);

            if ($total <= 0)
            {
                if ($recurring_count === 1 && $gateway instanceof LSD_Payments_Gateways_Stripe && $amount_total > 0)
                {
                    $total = $gateway->normalize($amount_total, $recurring_currency);
                }
                else
                {
                    $total = $recurring->get_total();
                }
            }

            $first_order = $recurring->get_first_order();
            $fees = $recurring_count === 1 && $first_order instanceof LSD_Payments_Order ? $first_order->get_fees() : [];
            $name = $first_order instanceof LSD_Payments_Order ? $first_order->get_name() : '';
            $email = $first_order instanceof LSD_Payments_Order ? $first_order->get_email() : '';
            $message = $first_order instanceof LSD_Payments_Order ? $first_order->get_message() : '';
            $title = $this->get_renewal_order_title($recurring, $first_order);

            $order_id = LSD_Payments_Orders::add([
                'items' => $items,
                'fees' => $fees,
                'subtotal' => $total,
                'discount' => 0,
                'total' => $total,
                'gateway' => 'stripe',
                'user_id' => $recurring->get_user_id(),
                'name' => $name,
                'email' => $email,
                'title' => $title,
                'message' => $message,
                'recurring_id' => $recurring_id,
            ]);

            if (!$order_id) continue;

            update_post_meta($order_id, 'lsd_recurring_id', $recurring_id);

            if ($invoice_id !== '') update_post_meta($order_id, 'lsd_stripe_invoice_id', sanitize_text_field($invoice_id));
            if ($subscription_id !== '') update_post_meta($order_id, 'lsd_stripe_subscription_id', sanitize_text_field($subscription_id));
            if ($customer_id !== '') update_post_meta($order_id, 'lsd_stripe_customer_id', sanitize_text_field($customer_id));

            if (isset($invoice['payment_intent']) && $invoice['payment_intent'])
            {
                update_post_meta($order_id, 'lsd_stripe_payment_intent_id', sanitize_text_field((string) $invoice['payment_intent']));
            }

            if (isset($invoice['charge']) && $invoice['charge'])
            {
                update_post_meta($order_id, 'lsd_stripe_charge_id', sanitize_text_field((string) $invoice['charge']));
            }

            LSD_Payments_Orders::completed($order_id);

            $meta_update = [
                'stripe_status' => isset($invoice['status']) ? (string) $invoice['status'] : 'paid',
                'stripe_last_payment_at' => wp_date('Y-m-d H:i:s'),
            ];

            if ($invoice_id !== '') $meta_update['stripe_last_invoice_id'] = $invoice_id;
            if ($subscription_id !== '') $meta_update['stripe_subscription_id'] = $subscription_id;
            if ($customer_id !== '') $meta_update['stripe_customer_id'] = $customer_id;
            if ($total > 0)
            {
                $decimals = ($gateway instanceof LSD_Payments_Gateways_Stripe && $gateway->is_zero_decimal_currency($recurring_currency)) ? 0 : 2;
                $meta_update['stripe_last_amount'] = number_format((float) $total, $decimals, '.', '');
            }

            if ($period['current_period_start'] > 0) $meta_update['current_period_start'] = (string) $period['current_period_start'];
            if ($period['current_period_end'] > 0) $meta_update['current_period_end'] = (string) $period['current_period_end'];

            LSD_Payments_Recurrings::update_gateway_meta($recurring_id, $meta_update);

            if ($subscription_id !== '') update_post_meta($recurring_id, 'lsd_stripe_subscription_id', sanitize_text_field($subscription_id));
            if ($customer_id !== '') update_post_meta($recurring_id, 'lsd_stripe_customer_id', sanitize_text_field($customer_id));

            $handled = true;
        }

        return $handled;
    }

    protected function handle_invoice_payment_failed(array $event): bool
    {
        $invoice = isset($event['data']['object']) && is_array($event['data']['object'])
            ? $event['data']['object']
            : null;

        if (!$invoice) return false;

        [$recurrings, $context] = $this->resolve_recurrings_from_invoice($invoice);
        if (!count($recurrings)) return false;

        $invoice_id = isset($invoice['id']) ? (string) $invoice['id'] : '';
        $subscription_id = isset($context['subscription_id']) ? (string) $context['subscription_id'] : '';
        $customer_id = isset($context['customer_id']) ? (string) $context['customer_id'] : '';
        $handled = false;

        foreach ($recurrings as $recurring)
        {
            if (!$recurring instanceof LSD_Payments_Recurring) continue;

            $recurring_id = $recurring->get_id();
            if ($recurring_id <= 0) continue;

            $meta_update = [
                'stripe_status' => 'payment_failed',
                'stripe_last_payment_failed_at' => wp_date('Y-m-d H:i:s'),
            ];

            if ($invoice_id !== '') $meta_update['stripe_last_failed_invoice_id'] = $invoice_id;
            if ($subscription_id !== '') $meta_update['stripe_subscription_id'] = $subscription_id;
            if ($customer_id !== '') $meta_update['stripe_customer_id'] = $customer_id;

            LSD_Payments_Recurrings::update_gateway_meta($recurring_id, $meta_update);

            if ($subscription_id !== '') update_post_meta($recurring_id, 'lsd_stripe_subscription_id', sanitize_text_field($subscription_id));
            if ($customer_id !== '') update_post_meta($recurring_id, 'lsd_stripe_customer_id', sanitize_text_field($customer_id));

            $handled = true;
        }

        return $handled;
    }

    protected function handle_subscription_updated(array $event): bool
    {
        $subscription = isset($event['data']['object']) && is_array($event['data']['object'])
            ? $event['data']['object']
            : null;

        if (!$subscription) return false;

        $subscription_id = isset($subscription['id']) ? (string) $subscription['id'] : '';
        if ($subscription_id === '') return false;

        $status = isset($subscription['status']) ? sanitize_key((string) $subscription['status']) : '';
        $cancel_at_period_end = !empty($subscription['cancel_at_period_end']);
        $recurrings = $this->get_recurrings_from_subscription_id($subscription_id);
        if (!count($recurrings)) return false;

        $handled = false;

        foreach ($recurrings as $recurring)
        {
            if (!$recurring instanceof LSD_Payments_Recurring) continue;

            $this->update_recurring_from_subscription($recurring, $subscription);

            if (in_array($status, ['active', 'trialing'], true) && $cancel_at_period_end)
            {
                LSD_Payments_Recurrings::cancel($recurring->get_id());
            }
            else if (in_array($status, ['active', 'trialing'], true))
            {
                LSD_Payments_Recurrings::active($recurring->get_id());
            }
            else if (in_array($status, ['canceled', 'unpaid', 'incomplete_expired'], true))
            {
                LSD_Payments_Recurrings::cancel($recurring->get_id());
            }

            $handled = true;
        }

        return $handled;
    }

    protected function handle_subscription_deleted(array $event): bool
    {
        $subscription = isset($event['data']['object']) && is_array($event['data']['object'])
            ? $event['data']['object']
            : null;

        if (!$subscription) return false;

        $subscription_id = isset($subscription['id']) ? (string) $subscription['id'] : '';
        if ($subscription_id === '') return false;

        $recurrings = $this->get_recurrings_from_subscription_id($subscription_id);
        if (!count($recurrings)) return false;

        $handled = false;

        foreach ($recurrings as $recurring)
        {
            if (!$recurring instanceof LSD_Payments_Recurring) continue;

            $this->update_recurring_from_subscription($recurring, $subscription);
            LSD_Payments_Recurrings::cancel($recurring->get_id());
            $handled = true;
        }

        return $handled;
    }

    protected function update_recurring_from_subscription(LSD_Payments_Recurring $recurring, array $subscription): void
    {
        $recurring_id = $recurring->get_id();
        if ($recurring_id <= 0) return;

        $subscription_id = isset($subscription['id']) ? (string) $subscription['id'] : '';
        $customer_id = isset($subscription['customer']) ? (string) $subscription['customer'] : '';
        $status = isset($subscription['status']) ? (string) $subscription['status'] : '';

        $meta_update = [];
        if ($subscription_id !== '') $meta_update['stripe_subscription_id'] = $subscription_id;
        if ($customer_id !== '') $meta_update['stripe_customer_id'] = $customer_id;
        if ($status !== '') $meta_update['stripe_status'] = $status;
        $meta_update['cancel_at_period_end'] = !empty($subscription['cancel_at_period_end']) ? '1' : '0';

        foreach (['current_period_start', 'current_period_end', 'cancel_at', 'canceled_at', 'ended_at'] as $key)
        {
            $meta_update[$key] = isset($subscription[$key]) && (int) $subscription[$key] > 0
                ? (string) ((int) $subscription[$key])
                : '0';
        }

        if ($meta_update) LSD_Payments_Recurrings::update_gateway_meta($recurring_id, $meta_update);

        if ($subscription_id !== '') update_post_meta($recurring_id, 'lsd_stripe_subscription_id', sanitize_text_field($subscription_id));
        if ($customer_id !== '') update_post_meta($recurring_id, 'lsd_stripe_customer_id', sanitize_text_field($customer_id));
    }

    protected function resolve_recurrings_from_invoice(array $invoice): array
    {
        $subscription_id = $this->extract_subscription_id_from_invoice($invoice);
        $customer_id = isset($invoice['customer']) ? (string) $invoice['customer'] : '';

        if ($subscription_id !== '')
        {
            $recurrings = $this->get_recurrings_from_subscription_id($subscription_id);
            if (count($recurrings))
            {
                return [$recurrings, [
                    'subscription_id' => $subscription_id,
                    'customer_id' => $customer_id,
                ]];
            }
        }

        $first_order_id = $this->extract_first_order_id($invoice);
        if ($first_order_id)
        {
            $recurrings = $this->get_recurrings_from_order($first_order_id, $subscription_id);
            if (count($recurrings))
            {
                $context = [
                    'subscription_id' => $subscription_id,
                    'customer_id' => $customer_id,
                    'first_order_id' => $first_order_id,
                ];

                if ($context['subscription_id'] === '')
                {
                    $meta = $recurrings[0]->get_gateway_meta();
                    if (isset($meta['stripe_subscription_id']) && is_string($meta['stripe_subscription_id']))
                    {
                        $context['subscription_id'] = $meta['stripe_subscription_id'];
                    }
                }

                return [$recurrings, $context];
            }
        }

        if ($customer_id !== '' && $subscription_id === '')
        {
            $recurring = LSD_Payments_Recurrings::find_by_gateway_meta('stripe', 'stripe_customer_id', $customer_id);
            if ($recurring instanceof LSD_Payments_Recurring)
            {
                $context = [
                    'subscription_id' => '',
                    'customer_id' => $customer_id,
                ];

                $meta = $recurring->get_gateway_meta();
                if (isset($meta['stripe_subscription_id']) && is_string($meta['stripe_subscription_id']))
                {
                    $context['subscription_id'] = $meta['stripe_subscription_id'];
                }

                return [[$recurring], $context];
            }
        }

        $meta_candidates = [];

        if ($customer_id !== '') $meta_candidates['lsd_stripe_customer_id'] = $customer_id;
        if (!empty($invoice['payment_intent'])) $meta_candidates['lsd_stripe_payment_intent_id'] = (string) $invoice['payment_intent'];
        if (!empty($invoice['charge'])) $meta_candidates['lsd_stripe_charge_id'] = (string) $invoice['charge'];

        foreach ($meta_candidates as $meta_key => $meta_value)
        {
            $meta_value = trim((string) $meta_value);
            if ($meta_value === '') continue;

            $order_ids = LSD_Main::get_post_ids($meta_key, $meta_value);
            foreach ($order_ids as $order_id)
            {
                if (!$order_id) continue;

                $order_subscription_id = (string) get_post_meta($order_id, 'lsd_stripe_subscription_id', true);

                if ($subscription_id !== '' && $order_subscription_id !== '' && $order_subscription_id !== $subscription_id) continue;

                $recurrings = $this->get_recurrings_from_order((int) $order_id, $subscription_id);
                if (count($recurrings))
                {
                    $meta = $recurrings[0]->get_gateway_meta();
                    $recurring_subscription_id = '';

                    if (isset($meta['stripe_subscription_id']) && is_string($meta['stripe_subscription_id'])) $recurring_subscription_id = $meta['stripe_subscription_id'];
                    else if ($order_subscription_id !== '') $recurring_subscription_id = $order_subscription_id;

                    if ($subscription_id !== '' && $recurring_subscription_id !== '' && $recurring_subscription_id !== $subscription_id) continue;

                    $context = [
                        'subscription_id' => $subscription_id !== '' ? $subscription_id : $recurring_subscription_id,
                        'customer_id' => $customer_id,
                        'first_order_id' => (int) $order_id,
                    ];

                    if ($context['customer_id'] === '' && $meta_key === 'lsd_stripe_customer_id') $context['customer_id'] = $meta_value;
                    return [$recurrings, $context];
                }
            }
        }

        return [[], [
            'subscription_id' => $subscription_id,
            'customer_id' => $customer_id,
        ]];
    }

    protected function get_recurrings_from_subscription_id(string $subscription_id): array
    {
        $subscription_id = sanitize_text_field($subscription_id);
        if ($subscription_id === '') return [];

        $gateway = LSD_Payments::gateway('stripe');
        if ($gateway instanceof LSD_Payments_Gateways_Stripe && method_exists($gateway, 'recurrings_from_subscription_id'))
        {
            $recurrings = $gateway->recurrings_from_subscription_id($subscription_id);
            if (count($recurrings)) return $this->unique_recurrings($recurrings);
        }

        $recurring = $this->resolve_recurring_from_subscription_id($subscription_id);
        return $recurring instanceof LSD_Payments_Recurring ? [$recurring] : [];
    }

    protected function resolve_recurring_from_subscription_id(string $subscription_id): ?LSD_Payments_Recurring
    {
        $subscription_id = sanitize_text_field($subscription_id);
        if ($subscription_id === '') return null;

        if (method_exists('LSD_Payments_Recurrings', 'find_by_stripe_subscription_id'))
        {
            $recurring = LSD_Payments_Recurrings::find_by_stripe_subscription_id($subscription_id);
            if ($recurring instanceof LSD_Payments_Recurring) return $recurring;
        }

        $recurring_id = LSD_Main::get_post_id_by_meta('lsd_stripe_subscription_id', $subscription_id);
        if ($recurring_id)
        {
            $recurring = LSD_Payments_Recurrings::get((int) $recurring_id);
            if ($recurring instanceof LSD_Payments_Recurring) return $recurring;
        }

        return LSD_Payments_Recurrings::find_by_gateway_meta('stripe', 'stripe_subscription_id', $subscription_id);
    }

    protected function get_recurrings_from_order(int $order_id, string $subscription_id = ''): array
    {
        if (!$order_id) return [];

        $recurrings = [];
        $subscription_id = sanitize_text_field($subscription_id);

        if ($subscription_id !== '')
        {
            $order = LSD_Payments_Orders::get($order_id);
            if ($order instanceof LSD_Payments_Order)
            {
                foreach ($order->get_items() as $item)
                {
                    if (!is_array($item)) continue;

                    $item_subscription_id = isset($item['stripe_subscription_id']) ? sanitize_text_field((string) $item['stripe_subscription_id']) : '';
                    $item_recurring_id = isset($item['recurring_id']) ? (int) $item['recurring_id'] : 0;

                    if ($item_subscription_id !== '' && $item_subscription_id !== $subscription_id) continue;
                    if ($item_recurring_id < 1) continue;

                    $recurring = LSD_Payments_Recurrings::get($item_recurring_id);
                    if ($recurring instanceof LSD_Payments_Recurring) $recurrings[] = $recurring;
                }
            }

            if (count($recurrings)) return $this->unique_recurrings($recurrings);

            $recurrings = $this->get_recurrings_from_subscription_id($subscription_id);
            if (count($recurrings)) return $this->unique_recurrings($recurrings);
        }

        $recurring_ids = get_post_meta($order_id, 'lsd_recurring_ids', true);
        if (is_array($recurring_ids))
        {
            foreach ($recurring_ids as $recurring_id)
            {
                $recurring = LSD_Payments_Recurrings::get((int) $recurring_id);
                if ($recurring instanceof LSD_Payments_Recurring) $recurrings[] = $recurring;
            }
        }

        $recurring_id = (int) get_post_meta($order_id, 'lsd_recurring_id', true);
        if ($recurring_id > 0)
        {
            $recurring = LSD_Payments_Recurrings::get($recurring_id);
            if ($recurring instanceof LSD_Payments_Recurring) $recurrings[] = $recurring;
        }

        if (method_exists('LSD_Payments_Recurrings', 'find_all_by_first_order_id'))
        {
            $recurrings = array_merge($recurrings, LSD_Payments_Recurrings::find_all_by_first_order_id($order_id));
        }
        else
        {
            $recurring = LSD_Payments_Recurrings::find_by_first_order_id($order_id);
            if ($recurring instanceof LSD_Payments_Recurring) $recurrings[] = $recurring;
        }

        return $this->unique_recurrings($recurrings);
    }

    protected function resolve_recurring_from_order(int $order_id, string $subscription_id = ''): ?LSD_Payments_Recurring
    {
        $recurrings = $this->get_recurrings_from_order($order_id, $subscription_id);
        return count($recurrings) ? $recurrings[0] : null;
    }

    protected function extract_subscription_id_from_invoice(array $invoice): string
    {
        if (!empty($invoice['subscription'])) return sanitize_text_field((string) $invoice['subscription']);

        if (!empty($invoice['subscription_details']['subscription']))
        {
            return sanitize_text_field((string) $invoice['subscription_details']['subscription']);
        }

        if (!empty($invoice['lines']['data']) && is_array($invoice['lines']['data']))
        {
            foreach ($invoice['lines']['data'] as $line)
            {
                if (!is_array($line)) continue;

                if (!empty($line['subscription'])) return sanitize_text_field((string) $line['subscription']);

                if (!empty($line['parent']['subscription_item_details']['subscription']))
                {
                    return sanitize_text_field((string) $line['parent']['subscription_item_details']['subscription']);
                }
            }
        }

        return '';
    }

    protected function extract_subscription_period_from_invoice(array $invoice): array
    {
        $period = [
            'current_period_start' => 0,
            'current_period_end' => 0,
        ];

        if (!empty($invoice['lines']['data']) && is_array($invoice['lines']['data']))
        {
            foreach ($invoice['lines']['data'] as $line)
            {
                if (!is_array($line)) continue;
                if (empty($line['period']) || !is_array($line['period'])) continue;

                if (!empty($line['period']['start'])) $period['current_period_start'] = (int) $line['period']['start'];
                if (!empty($line['period']['end'])) $period['current_period_end'] = (int) $line['period']['end'];

                if ($period['current_period_start'] > 0 || $period['current_period_end'] > 0) break;
            }
        }

        return $period;
    }

    protected function get_renewal_order_title(LSD_Payments_Recurring $recurring, ?LSD_Payments_Order $first_order = null): string
    {
        $recurring_post = get_post($recurring->get_id());
        if ($recurring_post instanceof WP_Post && $recurring_post->post_title !== '')
        {
            /* translators: %s: recurring payment title. */
            return sprintf(esc_html__('%s Renewal', 'listdom'), $recurring_post->post_title);
        }

        if ($first_order instanceof LSD_Payments_Order)
        {
            $first_post = get_post($first_order->get_id());
            if ($first_post instanceof WP_Post && $first_post->post_title !== '')
            {
                $base_title = LSD_Payments_Orders::title_without_recurring_suffix($first_order->get_id(), $first_post->post_title);
                /* translators: %s: order title. */
                return sprintf(esc_html__('%s Renewal', 'listdom'), $base_title !== '' ? $base_title : $first_post->post_title);
            }
        }

        return wp_date('Y-m-d H:i:s');
    }

    protected function invoice_order_exists_for_recurring(string $invoice_id, int $recurring_id): bool
    {
        $invoice_id = sanitize_text_field($invoice_id);
        if ($invoice_id === '' || $recurring_id < 1) return false;

        foreach (LSD_Main::get_post_ids('lsd_stripe_invoice_id', $invoice_id) as $order_id)
        {
            if ((int) get_post_meta($order_id, 'lsd_recurring_id', true) === $recurring_id) return true;

            $recurring_ids = get_post_meta($order_id, 'lsd_recurring_ids', true);
            if (is_array($recurring_ids) && in_array($recurring_id, array_map('intval', $recurring_ids), true)) return true;
        }

        return false;
    }

    protected function get_invoice_total_for_recurring(array $invoice, LSD_Payments_Recurring $recurring, $gateway = null): float
    {
        $price_id = $this->get_recurring_price_id($recurring);
        if ($price_id === '') return 0.0;

        $currency = isset($invoice['currency']) ? strtoupper((string) $invoice['currency']) : $recurring->get_currency();
        $amount = 0;
        $lines = isset($invoice['lines']['data']) && is_array($invoice['lines']['data']) ? $invoice['lines']['data'] : [];

        foreach ($lines as $line)
        {
            if (!is_array($line)) continue;

            $line_price_id = '';
            if (isset($line['price']['id'])) $line_price_id = sanitize_text_field((string) $line['price']['id']);
            else if (isset($line['pricing']['price_details']['price'])) $line_price_id = sanitize_text_field((string) $line['pricing']['price_details']['price']);
            else if (isset($line['parent']['subscription_item_details']['price'])) $line_price_id = sanitize_text_field((string) $line['parent']['subscription_item_details']['price']);

            if ($line_price_id !== $price_id) continue;
            if (isset($line['amount'])) $amount += (int) $line['amount'];
        }

        if ($amount > 0 && $gateway instanceof LSD_Payments_Gateways_Stripe)
        {
            return $gateway->normalize($amount, $currency);
        }

        return 0.0;
    }

    protected function get_recurring_price_id(LSD_Payments_Recurring $recurring): string
    {
        $meta = $recurring->get_gateway_meta();

        if (isset($meta['stripe_price_id']) && trim((string) $meta['stripe_price_id']) !== '')
        {
            return sanitize_text_field((string) $meta['stripe_price_id']);
        }

        $price_id = get_post_meta($recurring->get_id(), 'lsd_stripe_price_id', true);
        if ($price_id) return sanitize_text_field((string) $price_id);

        foreach ($recurring->get_items() as $item)
        {
            if (!is_array($item) || empty($item['stripe_price_id'])) continue;

            return sanitize_text_field((string) $item['stripe_price_id']);
        }

        return '';
    }

    protected function unique_recurrings(array $recurrings): array
    {
        $unique = [];

        foreach ($recurrings as $recurring)
        {
            if (!$recurring instanceof LSD_Payments_Recurring) continue;

            $recurring_id = $recurring->get_id();
            if ($recurring_id < 1) continue;

            $unique[$recurring_id] = $recurring;
        }

        return array_values($unique);
    }

    protected function extract_first_order_id(array $invoice): int
    {
        $candidates = [];

        if (!empty($invoice['subscription_details']['metadata']) && is_array($invoice['subscription_details']['metadata']))
        {
            $metadata = $invoice['subscription_details']['metadata'];
            if (isset($metadata['lsd_first_order_id'])) $candidates[] = $metadata['lsd_first_order_id'];
        }

        if (!empty($invoice['metadata']) && is_array($invoice['metadata']))
        {
            $metadata = $invoice['metadata'];
            if (isset($metadata['lsd_first_order_id'])) $candidates[] = $metadata['lsd_first_order_id'];
        }

        if (!empty($invoice['lines']['data']) && is_array($invoice['lines']['data']))
        {
            foreach ($invoice['lines']['data'] as $line)
            {
                if (!is_array($line)) continue;

                if (!empty($line['metadata']) && is_array($line['metadata']) && isset($line['metadata']['lsd_first_order_id']))
                {
                    $candidates[] = $line['metadata']['lsd_first_order_id'];
                }

                if (!empty($line['subscription_details']['metadata']) && is_array($line['subscription_details']['metadata']) && isset($line['subscription_details']['metadata']['lsd_first_order_id']))
                {
                    $candidates[] = $line['subscription_details']['metadata']['lsd_first_order_id'];
                }

                if (!empty($line['parent']['subscription_item_details']['metadata']) && is_array($line['parent']['subscription_item_details']['metadata']) && isset($line['parent']['subscription_item_details']['metadata']['lsd_first_order_id']))
                {
                    $candidates[] = $line['parent']['subscription_item_details']['metadata']['lsd_first_order_id'];
                }
            }
        }

        foreach ($candidates as $candidate)
        {
            $order_id = (int) $candidate;
            if ($order_id > 0) return $order_id;
        }

        return 0;
    }
}
