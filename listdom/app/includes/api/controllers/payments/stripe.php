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

        if ($type === 'invoice.payment_succeeded')
        {
            $handled = $this->handle_invoice_payment_succeeded($payload);
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

        [$recurring, $context] = $this->resolve_recurring_from_invoice($invoice);
        if (!$recurring instanceof LSD_Payments_Recurring) return false;

        $subscription_id = isset($context['subscription_id']) ? (string) $context['subscription_id'] : '';
        $customer_id = isset($context['customer_id']) ? (string) $context['customer_id'] : '';

        $recurring_id = $recurring->get_id();
        if ($recurring_id <= 0) return false;

        $invoice_id = isset($invoice['id']) ? (string) $invoice['id'] : '';
        if ($invoice_id !== '')
        {
            $existing = LSD_Main::get_post_id_by_meta('lsd_stripe_invoice_id', $invoice_id);
            if ($existing) return true;
        }

        $amount_total = isset($invoice['amount_paid']) ? (int) $invoice['amount_paid'] : 0;
        if ($amount_total <= 0 && isset($invoice['amount_due']))
        {
            $amount_total = (int) $invoice['amount_due'];
        }

        $currency = isset($invoice['currency']) ? strtoupper((string) $invoice['currency']) : $recurring->get_currency();

        $gateway = LSD_Payments::gateway('stripe');
        $total = $recurring->get_total();
        if ($gateway instanceof LSD_Payments_Gateways_Stripe && $amount_total > 0)
        {
            $total = $gateway->normalize($amount_total, $currency);
        }

        $first_order = $recurring->get_first_order();
        $fees = $first_order instanceof LSD_Payments_Order ? $first_order->get_fees() : [];
        $name = $first_order instanceof LSD_Payments_Order ? $first_order->get_name() : '';
        $email = $first_order instanceof LSD_Payments_Order ? $first_order->get_email() : '';
        $message = $first_order instanceof LSD_Payments_Order ? $first_order->get_message() : '';
        $items = $recurring->get_items();
        if (!$items && $first_order instanceof LSD_Payments_Order)
        {
            $items = $first_order->get_items();
        }

        $title = wp_date('Y-m-d H:i:s');
        if ($first_order instanceof LSD_Payments_Order)
        {
            $first_post = get_post($first_order->get_id());
            if ($first_post instanceof WP_Post && $first_post->post_title !== '')
            {
                $base_title = LSD_Payments_Orders::title_without_recurring_suffix($first_order->get_id(), $first_post->post_title);
                /* translators: %s: order title. */
                $title = sprintf(esc_html__('%s Renewal', 'listdom'), $base_title !== '' ? $base_title : $first_post->post_title);
            }
        }
        else
        {
            $recurring_post = get_post($recurring->get_id());
            if ($recurring_post instanceof WP_Post && $recurring_post->post_title !== '')
            {
                /* translators: %s: recurring payment title. */
                $title = sprintf(esc_html__('%s Renewal', 'listdom'), $recurring_post->post_title);
            }
        }

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

        if (!$order_id) return false;

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
        if ($amount_total > 0)
        {
            $decimals = ($gateway instanceof LSD_Payments_Gateways_Stripe && $gateway->is_zero_decimal_currency($currency)) ? 0 : 2;
            $meta_update['stripe_last_amount'] = number_format((float) $total, $decimals, '.', '');
        }

        LSD_Payments_Recurrings::update_gateway_meta($recurring_id, $meta_update);

        return true;
    }

    protected function resolve_recurring_from_invoice(array $invoice): array
    {
        $subscription_id = isset($invoice['subscription']) ? (string) $invoice['subscription'] : '';
        $customer_id = isset($invoice['customer']) ? (string) $invoice['customer'] : '';

        if ($subscription_id !== '')
        {
            $recurring = LSD_Payments_Recurrings::find_by_gateway_meta('stripe', 'stripe_subscription_id', $subscription_id);
            if ($recurring instanceof LSD_Payments_Recurring)
            {
                return [$recurring, [
                    'subscription_id' => $subscription_id,
                    'customer_id' => $customer_id,
                ]];
            }
        }

        $first_order_id = $this->extract_first_order_id($invoice);
        if ($first_order_id)
        {
            $recurring = $this->resolve_recurring_from_order($first_order_id);
            if ($recurring instanceof LSD_Payments_Recurring)
            {
                $context = [
                    'subscription_id' => $subscription_id,
                    'customer_id' => $customer_id,
                    'first_order_id' => $first_order_id,
                ];

                if ($context['subscription_id'] === '')
                {
                    $meta = $recurring->get_gateway_meta();
                    if (isset($meta['stripe_subscription_id']) && is_string($meta['stripe_subscription_id']))
                    {
                        $context['subscription_id'] = $meta['stripe_subscription_id'];
                    }
                }

                return [$recurring, $context];
            }
        }

        if ($customer_id !== '')
        {
            $recurring = LSD_Payments_Recurrings::find_by_gateway_meta('stripe', 'stripe_customer_id', $customer_id);
            if ($recurring instanceof LSD_Payments_Recurring)
            {
                $context = [
                    'subscription_id' => $subscription_id,
                    'customer_id' => $customer_id,
                ];

                if ($context['subscription_id'] === '')
                {
                    $meta = $recurring->get_gateway_meta();
                    if (isset($meta['stripe_subscription_id']) && is_string($meta['stripe_subscription_id']))
                    {
                        $context['subscription_id'] = $meta['stripe_subscription_id'];
                    }
                }

                return [$recurring, $context];
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

                if ($subscription_id === '') continue;
                if ($order_subscription_id !== '' && $order_subscription_id !== $subscription_id) continue;

                $recurring = $this->resolve_recurring_from_order((int) $order_id);
                if ($recurring instanceof LSD_Payments_Recurring)
                {
                    $meta = $recurring->get_gateway_meta();
                    $recurring_subscription_id = '';

                    if (isset($meta['stripe_subscription_id']) && is_string($meta['stripe_subscription_id'])) $recurring_subscription_id = $meta['stripe_subscription_id'];
                    else if ($order_subscription_id !== '') $recurring_subscription_id = $order_subscription_id;

                    if ($recurring_subscription_id === '' || $recurring_subscription_id !== $subscription_id) continue;

                    $context = [
                        'subscription_id' => $subscription_id,
                        'customer_id' => $customer_id,
                        'first_order_id' => (int) $order_id,
                    ];

                    if ($context['customer_id'] === '' && $meta_key === 'lsd_stripe_customer_id') $context['customer_id'] = $meta_value;
                    return [$recurring, $context];
                }
            }
        }

        return [null, [
            'subscription_id' => $subscription_id,
            'customer_id' => $customer_id,
        ]];
    }

    protected function resolve_recurring_from_order(int $order_id): ?LSD_Payments_Recurring
    {
        if (!$order_id) return null;

        $recurring = LSD_Payments_Recurrings::find_by_first_order_id($order_id);
        if ($recurring instanceof LSD_Payments_Recurring) return $recurring;

        $recurring_id = (int) get_post_meta($order_id, 'lsd_recurring_id', true);
        if ($recurring_id)
        {
            $recurring = LSD_Payments_Recurrings::get($recurring_id);
            if ($recurring instanceof LSD_Payments_Recurring) return $recurring;
        }

        return null;
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
