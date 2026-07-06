<?php

use Stripe\Coupon;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Price;
use Stripe\SetupIntent;
use Stripe\Subscription;
use Stripe\TaxRate;

class LSD_Payments_Gateways_Stripe extends LSD_Payments_Gateway
{
    /**
     * @var array|null
     */
    protected $subscription_context = null;

    /**
     * @var array|null
     */
    protected $subscription_setup = null;

    /**
     * @var string
     */
    protected $checkout_init_error = '';

    /**
     * @var string
     */
    protected $checkout_init_mode = 'payment';

    public function __construct()
    {
        $this->id = 5;
        $this->key = 'stripe';
    }

    public function label(): string
    {
        return esc_html__('Stripe', 'listdom');
    }

    public function description(): string
    {
        return esc_html__('Accept credit card payments via Stripe.', 'listdom');
    }

    public function icon(): string
    {
        return 'fa fa-credit-card-alt';
    }

    public function enabled(): bool
    {
        if (!parent::enabled()) return false;

        $options = $this->options();

        return isset($options['publishable_key']) && trim($options['publishable_key'])
            && isset($options['secret_key']) && trim($options['secret_key']);
    }

    public function webhook_secret(): string
    {
        $options = $this->options();

        return isset($options['webhook_secret'])
            ? trim((string) $options['webhook_secret'])
            : '';
    }

    protected function reset_checkout_init_error(): void
    {
        $this->checkout_init_error = '';
    }

    protected function set_checkout_init_error(string $error): void
    {
        $this->checkout_init_error = sanitize_key($error);
    }

    public function checkout_init_notice(): string
    {
        if ($this->checkout_init_error === '') return '';

        $summary = $this->checkout_init_mode === 'setup'
            ? __('the subscription payment session could not be prepared', 'listdom')
            : __('the payment session could not be prepared', 'listdom');

        return sprintf(
        /* translators: 1: short problem summary, 2: support reference code */
            __('Stripe checkout is temporarily unavailable because %1$s. Please contact the website administrator and mention reference %2$s.', 'listdom'),
            $summary,
            $this->checkout_init_reference()
        );
    }

    protected function checkout_init_reference(): string
    {
        if ($this->checkout_init_mode === 'setup')
        {
            return $this->checkout_init_error === 'missing_key'
                ? 'LSD-STRIPE-SETUP-CONFIG'
                : 'LSD-STRIPE-SETUP-INIT';
        }

        return $this->checkout_init_error === 'missing_key'
            ? 'LSD-STRIPE-PAYMENT-CONFIG'
            : 'LSD-STRIPE-PAYMENT-INIT';
    }

    public function form_specific(array $data = []): string
    {
        return $this->include_html_file('payments/specific/stripe.php', [
            'return_output' => true,
            'parameters' => [
                'data' => $data,
            ],
        ]);
    }

    protected function zero_decimal_currencies(): array
    {
        return ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];
    }

    public function is_zero_decimal_currency(string $currency): bool
    {
        return in_array(strtoupper($currency), $this->zero_decimal_currencies(), true);
    }

    public function multiply($amount, string $currency): int
    {
        return $this->is_zero_decimal_currency($currency)
            ? (int) round($amount)
            : (int) round($amount * 100);
    }

    public function normalize($amount, string $currency): float
    {
        return $this->is_zero_decimal_currency($currency)
            ? (float) $amount
            : round(((float) $amount) / 100, 2);
    }

    protected function set_api_key(): bool
    {
        $options = $this->options();
        $secret = isset($options['secret_key']) ? trim((string) $options['secret_key']) : '';
        if (!$secret) return false;

        \Stripe\Stripe::setApiKey($secret);
        return true;
    }

    protected function get_recurring_subscription_id(LSD_Payments_Recurring $recurring): string
    {
        $meta = $recurring->get_gateway_meta();
        if (isset($meta['stripe_subscription_id']) && trim((string) $meta['stripe_subscription_id']) !== '')
        {
            return sanitize_text_field((string) $meta['stripe_subscription_id']);
        }

        $subscription_id = get_post_meta($recurring->get_id(), 'lsd_stripe_subscription_id', true);
        return $subscription_id ? sanitize_text_field((string) $subscription_id) : '';
    }

    protected function sync_recurring_subscription_meta(LSD_Payments_Recurring $recurring, $subscription): void
    {
        $recurring_id = $recurring->get_id();
        if ($recurring_id < 1 || !is_object($subscription)) return;

        $meta = [
            'stripe_subscription_id' => isset($subscription->id) ? (string) $subscription->id : '',
            'stripe_status' => isset($subscription->status) ? (string) $subscription->status : '',
            'cancel_at_period_end' => !empty($subscription->cancel_at_period_end) ? '1' : '0',
        ];

        foreach (['current_period_start', 'current_period_end', 'cancel_at', 'canceled_at', 'ended_at'] as $key)
        {
            $meta[$key] = isset($subscription->{$key}) && (int) $subscription->{$key} > 0
                ? (string) ((int) $subscription->{$key})
                : '0';
        }

        if (isset($subscription->customer) && trim((string) $subscription->customer) !== '')
        {
            $meta['stripe_customer_id'] = (string) $subscription->customer;
        }

        LSD_Payments_Recurrings::update_gateway_meta($recurring_id, $meta);

        if (isset($meta['stripe_subscription_id']) && $meta['stripe_subscription_id'] !== '')
        {
            update_post_meta($recurring_id, 'lsd_stripe_subscription_id', $meta['stripe_subscription_id']);
        }

        if (isset($meta['stripe_customer_id']) && $meta['stripe_customer_id'] !== '')
        {
            update_post_meta($recurring_id, 'lsd_stripe_customer_id', $meta['stripe_customer_id']);
        }
    }

    protected function rollback_partial_subscription_setup(int $order_id, array $subscription_ids = [], array $recurring_ids = [], array $price_ids = [], array $coupon_ids = [], array $tax_rate_ids = []): void
    {
        foreach (array_unique(array_filter(array_map('strval', $subscription_ids))) as $subscription_id)
        {
            if ($subscription_id === '') continue;

            try
            {
                Subscription::update($subscription_id, [
                    'cancel_at_period_end' => false,
                ]);
                Subscription::retrieve($subscription_id)->cancel();
            }
            catch (ApiErrorException $e)
            {
                // Keep the original checkout error as the caller-facing result.
            }
        }

        foreach (array_unique(array_filter(array_map('strval', $price_ids))) as $price_id)
        {
            if ($price_id === '') continue;

            try
            {
                Price::update($price_id, [
                    'active' => false,
                ]);
            }
            catch (ApiErrorException $e)
            {
                // Keep the original checkout error as the caller-facing result.
            }
        }

        foreach (array_unique(array_filter(array_map('strval', $coupon_ids))) as $coupon_id)
        {
            if ($coupon_id === '') continue;

            try
            {
                Coupon::delete($coupon_id, []);
            }
            catch (ApiErrorException $e)
            {
                // Keep the original checkout error as the caller-facing result.
            }
        }

        foreach (array_unique(array_filter(array_map('strval', $tax_rate_ids))) as $tax_rate_id)
        {
            if ($tax_rate_id === '') continue;

            try
            {
                TaxRate::update($tax_rate_id, [
                    'active' => false,
                ]);
            }
            catch (ApiErrorException $e)
            {
                // Keep the original checkout error as the caller-facing result.
            }
        }

        $recurring_ids = array_values(array_unique(array_filter(array_map('intval', $recurring_ids))));
        if (!$recurring_ids) return;

        foreach ($recurring_ids as $recurring_id)
        {
            wp_delete_post($recurring_id, true);
        }

        $remaining_ids = array_values(array_diff(LSD_Payments_Orders::get_recurring_ids($order_id), $recurring_ids));

        if ($remaining_ids) update_post_meta($order_id, 'lsd_recurring_ids', $remaining_ids);
        else delete_post_meta($order_id, 'lsd_recurring_ids');

        if (count($remaining_ids) === 1) update_post_meta($order_id, 'lsd_recurring_id', $remaining_ids[0]);
        else delete_post_meta($order_id, 'lsd_recurring_id');
    }

    public function disable_autorenew(LSD_Payments_Recurring $recurring)
    {
        if (!$this->set_api_key())
        {
            return new WP_Error('lsd_stripe_config_missing', esc_html__('Stripe is not configured correctly.', 'listdom'));
        }

        $subscription_id = $this->get_recurring_subscription_id($recurring);
        if ($subscription_id === '')
        {
            return new WP_Error('lsd_stripe_subscription_missing', esc_html__('Unable to find the Stripe subscription for this recurring payment.', 'listdom'));
        }

        try
        {
            $subscription = Subscription::update($subscription_id, [
                'cancel_at_period_end' => true,
            ]);
        }
        catch (ApiErrorException $e)
        {
            return new WP_Error('lsd_stripe_subscription_update_failed', $e->getMessage());
        }

        $this->sync_recurring_subscription_meta($recurring, $subscription);

        return true;
    }

    public function activate_autorenew(LSD_Payments_Recurring $recurring)
    {
        if (!$this->set_api_key())
        {
            return new WP_Error('lsd_stripe_config_missing', esc_html__('Stripe is not configured correctly.', 'listdom'));
        }

        $subscription_id = $this->get_recurring_subscription_id($recurring);
        if ($subscription_id === '')
        {
            return new WP_Error('lsd_stripe_subscription_missing', esc_html__('Unable to find the Stripe subscription for this recurring payment.', 'listdom'));
        }

        try
        {
            $subscription = Subscription::update($subscription_id, [
                'cancel_at_period_end' => false,
            ]);
        }
        catch (ApiErrorException $e)
        {
            return new WP_Error('lsd_stripe_subscription_update_failed', $e->getMessage());
        }

        $this->sync_recurring_subscription_meta($recurring, $subscription);

        return true;
    }

    protected function analyze_cart(): array
    {
        $cart = new LSD_Cart();
        $items = $cart->get_items();

        [$total, $discount, $tax] = $cart->apply_coupon();

        $result = [
            'has_recurring' => false,
            'has_non_recurring' => false,
            'items' => [],
            'subtotal' => $cart->get_sub_total(),
            'total' => $total,
            'discount' => $discount,
            'tax' => $tax,
            'currency' => LSD_Options::currency(),
            'recurring_subtotal' => 0.0,
            'recurring_discount' => 0.0,
        ];

        foreach ($items as $cart_index => $item)
        {
            $plan_id = isset($item['plan_id']) ? (int) $item['plan_id'] : 0;
            $tier_id = isset($item['tier_id']) ? (string) $item['tier_id'] : '';

            if (!$plan_id || !$tier_id) continue;

            $plan = new LSD_Payments_Plan($plan_id, $tier_id);
            $tier = $plan->get_tier();
            if (!$tier) continue;

            if ($tier->is_recurring())
            {
                $result['has_recurring'] = true;

                $price = $tier->get_price();

                $result['items'][] = [
                    'cart_index' => (int) $cart_index,
                    'plan_id' => $plan_id,
                    'tier_id' => $tier_id,
                    'price' => $price,
                    'name' => $plan->get_title(),
                    'tier_name' => $tier->get_name(),
                    'frequency_days' => max($tier->get_frequency_days(), 1),
                    'cart_item' => $item,
                ];

                $result['recurring_subtotal'] += $price;
            }
            else
            {
                $result['has_non_recurring'] = true;
            }
        }

        if ($result['has_recurring'])
        {
            $result['recurring_subtotal'] = round($result['recurring_subtotal'], 2);
            if ($result['recurring_subtotal'] > 0 && $result['discount'] > 0)
            {
                $result['recurring_discount'] = min($result['discount'], $result['recurring_subtotal']);
            }
        }
        else
        {
            $result['recurring_subtotal'] = 0.0;
            $result['recurring_discount'] = 0.0;
        }

        return $result;
    }

    /**
     * Create a Stripe intent for the current cart.
     */
    public function createIntent(): ?array
    {
        $analysis = $this->analyze_cart();
        $this->reset_checkout_init_error();
        $this->checkout_init_mode = $analysis['has_recurring'] ? 'setup' : 'payment';

        if (!$this->set_api_key())
        {
            $this->set_checkout_init_error('missing_key');
            return null;
        }

        try
        {
            if ($analysis['has_recurring'])
            {
                $intent = SetupIntent::create([
                    'usage' => 'off_session',
                    'payment_method_types' => ['card'],
                ]);

                return [
                    'mode' => 'setup',
                    'id' => $intent->id,
                    'client_secret' => $intent->client_secret,
                ];
            }

            $intent = PaymentIntent::create([
                'amount' => $this->multiply($analysis['total'], $analysis['currency']),
                'currency' => $analysis['currency'],
                'automatic_payment_methods' => [
                    'enabled' => 'true',
                ],
            ]);

            return [
                'mode' => 'payment',
                'id' => $intent->id,
                'client_secret' => $intent->client_secret,
            ];
        }
        catch (ApiErrorException $e)
        {
            $this->set_checkout_init_error('api_error');
            return null;
        }
    }

    /**
     * Retrieve a Stripe payment intent by id.
     */
    public function retrieveIntent(string $id): ?PaymentIntent
    {
        if (!$this->set_api_key()) return null;

        try
        {
            return PaymentIntent::retrieve($id);
        }
        catch (ApiErrorException $e)
        {
            return null;
        }
    }

    protected function retrieveSetupIntent(string $id): ?SetupIntent
    {
        if (!$this->set_api_key()) return null;

        try
        {
            return SetupIntent::retrieve($id);
        }
        catch (ApiErrorException $e)
        {
            return null;
        }
    }

    public function validate(array $args = []): bool
    {
        $analysis = $this->analyze_cart();

        if ($analysis['has_recurring'])
        {
            if ($analysis['has_non_recurring']) return false;

            $setup_intent_id = isset($args['setup_intent']) ? sanitize_text_field($args['setup_intent']) : '';
            $payment_method = isset($args['payment_method']) ? sanitize_text_field($args['payment_method']) : '';
            if (!$setup_intent_id || !$payment_method) return false;

            $intent = $this->retrieveSetupIntent($setup_intent_id);
            if (!$intent || $intent->status !== 'succeeded' || $intent->payment_method !== $payment_method) return false;

            if ($analysis['recurring_subtotal'] <= 0) return false;

            $this->subscription_context = $analysis;
            $this->subscription_setup = [
                'setup_intent' => $setup_intent_id,
                'payment_method' => $payment_method,
            ];

            return true;
        }

        $payment_intent = isset($args['payment_intent']) ? sanitize_text_field($args['payment_intent']) : '';
        if (!$payment_intent) return false;

        $intent = $this->retrieveIntent($payment_intent);
        if (!$intent) return false;

        $currency = $analysis['currency'];
        $expected_amount = $this->multiply($analysis['total'], $currency);

        return in_array($intent->status, ['succeeded', 'processing'], true)
            && strtolower($intent->currency) === strtolower($currency)
            && $intent->amount === $expected_amount;
    }

    protected function allocate_subscription_amounts(): array
    {
        if (!$this->subscription_context) return [];

        $items = $this->subscription_context['items'];
        if (!$items) return [];

        $allocated = [];
        $total = 0.0;

        foreach ($items as $item)
        {
            $amount = round(isset($item['price']) ? (float) $item['price'] : 0.0, 2);
            if ($amount <= 0) return [];

            $allocated[] = array_merge($item, ['amount' => $amount]);
            $total += $amount;
        }

        return $total > 0 ? $allocated : [];
    }

    protected function create_subscription_tax_rates(LSD_Payments_Order $order): array
    {
        $tax_items = $order->get_tax_items();
        if (!count($tax_items)) return [
            'ids' => [],
            'created_ids' => [],
        ];

        $ids = [];
        foreach ($tax_items as $tax_item)
        {
            $rate = isset($tax_item['rate']) ? (float) $tax_item['rate'] : 0.0;
            if ($rate <= 0) continue;

            $label = isset($tax_item['label']) ? sanitize_text_field((string) $tax_item['label']) : '';
            if ($label === '') $label = esc_html__('Tax', 'listdom');

            try
            {
                $tax_rate = TaxRate::create([
                    'display_name' => $label,
                    'inclusive' => $order->prices_include_tax(),
                    'percentage' => round($rate, 4),
                    'metadata' => [
                        'lsd_first_order_id' => (string) $order->get_id(),
                        'lsd_label' => $label,
                    ],
                ]);
            }
            catch (ApiErrorException $e)
            {
                return [
                    'error' => new WP_Error('lsd_stripe_tax_rate_error', $e->getMessage()),
                    'ids' => $ids,
                    'created_ids' => $ids,
                ];
            }

            $ids[] = $tax_rate->id;
        }

        return [
            'ids' => $ids,
            'created_ids' => $ids,
        ];
    }

    protected function create_subscription_price(array $item, string $currency, bool $prices_include_tax = false): array
    {
        $unit_amount = $this->multiply($item['amount'], $currency);
        if ($unit_amount <= 0) return [];

        $interval_count = max(1, (int) $item['frequency_days']);
        $product_name = trim($item['name'] . ($item['tier_name'] ? ' - ' . $item['tier_name'] : ''));
        if ($product_name === '')
        {
            $product_name = sprintf('Listdom Plan %d', (int) $item['plan_id']);
        }

        try
        {
            $price = Price::create([
                'unit_amount' => $unit_amount,
                'currency' => $currency,
                'tax_behavior' => $prices_include_tax ? 'inclusive' : 'exclusive',
                'recurring' => [
                    'interval' => 'day',
                    'interval_count' => $interval_count,
                ],
                'product_data' => [
                    'name' => $product_name,
                    'metadata' => [
                        'lsd_plan_id' => (string) $item['plan_id'],
                        'lsd_tier_id' => (string) $item['tier_id'],
                    ],
                ],
                'metadata' => [
                    'lsd_plan_id' => (string) $item['plan_id'],
                    'lsd_tier_id' => (string) $item['tier_id'],
                ],
            ]);
        }
        catch (ApiErrorException $e)
        {
            return [];
        }

        return [
            'item' => ['price' => $price->id, 'quantity' => 1],
            'price_id' => $price->id,
        ];
    }

    protected function allocate_first_cycle_discounts(array $allocated, float $discount, string $currency): array
    {
        $discount = round(max(0, $discount), 2);
        if ($discount <= 0) return [];

        $total = 0.0;
        foreach ($allocated as $item)
        {
            $total += isset($item['amount']) ? (float) $item['amount'] : 0.0;
        }

        if ($total <= 0) return [];

        $remaining_discount_minor = $this->multiply(min($discount, $total), $currency);
        $remaining_total = $total;
        $discounts = [];
        $last_key = array_key_last($allocated);

        foreach ($allocated as $index => $item)
        {
            $amount = isset($item['amount']) ? (float) $item['amount'] : 0.0;
            if ($amount <= 0) continue;

            if ($index === $last_key)
            {
                $minor = $remaining_discount_minor;
            }
            else
            {
                $minor = (int) round($remaining_discount_minor * ($amount / $remaining_total));
            }

            $max_minor = $this->multiply($amount, $currency);
            $minor = max(0, min($minor, $max_minor, $remaining_discount_minor));

            if ($minor > 0)
            {
                $discounts[$index] = $this->normalize($minor, $currency);
            }

            $remaining_discount_minor -= $minor;
            $remaining_total -= $amount;

            if ($remaining_discount_minor <= 0) break;
        }

        return $discounts;
    }

    protected function get_order_recurring_items_map(LSD_Payments_Order $order, array $allocated): array
    {
        $order_items = $order->get_items();
        $used_indexes = [];
        $map = [];

        foreach ($allocated as $allocated_index => $allocated_item)
        {
            $matched_index = null;
            $cart_index = isset($allocated_item['cart_index']) ? (int) $allocated_item['cart_index'] : -1;

            if ($cart_index >= 0 && isset($order_items[$cart_index]) && is_array($order_items[$cart_index]))
            {
                $matched_index = $cart_index;
            }
            else
            {
                foreach ($order_items as $order_item_index => $order_item)
                {
                    if (in_array($order_item_index, $used_indexes, true) || !is_array($order_item)) continue;

                    $plan_id = isset($order_item['plan_id']) ? (int) $order_item['plan_id'] : 0;
                    $tier_id = isset($order_item['tier_id']) ? (string) $order_item['tier_id'] : '';

                    if ($plan_id === (int) $allocated_item['plan_id'] && $tier_id === (string) $allocated_item['tier_id'])
                    {
                        $matched_index = $order_item_index;
                        break;
                    }
                }
            }

            if ($matched_index === null) continue;

            $used_indexes[] = $matched_index;
            $map[$allocated_index] = [
                'index' => $matched_index,
                'item' => $order_items[$matched_index],
            ];
        }

        return $map;
    }

    protected function extract_subscription_payment_intent_status($subscription): string
    {
        if (isset($subscription->latest_invoice) && is_object($subscription->latest_invoice) && isset($subscription->latest_invoice->payment_intent))
        {
            $payment_intent = $subscription->latest_invoice->payment_intent;
            if (is_object($payment_intent) && isset($payment_intent->status))
            {
                return sanitize_key($payment_intent->status);
            }
        }

        return '';
    }

    protected function subscription_is_incomplete($subscription): bool
    {
        $subscription_status = isset($subscription->status) ? sanitize_key($subscription->status) : '';
        $payment_intent_status = $this->extract_subscription_payment_intent_status($subscription);

        $incomplete_statuses = ['incomplete', 'incomplete_expired'];
        $requires_payment_method_statuses = ['requires_payment_method'];

        return in_array($subscription_status, $incomplete_statuses, true)
            || ($payment_intent_status && in_array($payment_intent_status, $requires_payment_method_statuses, true));
    }

    protected function prepare_recurring_item(array $order_item, array $allocated_item, string $price_id, string $subscription_id): array
    {
        $recurring_item = $order_item;

        $recurring_item['frequency_days'] = max(1, (int) ($allocated_item['frequency_days'] ?? 1));
        $recurring_item['amount'] = isset($allocated_item['amount']) ? (float) $allocated_item['amount'] : 0.0;
        $recurring_item['stripe_price_id'] = $price_id;
        $recurring_item['stripe_subscription_id'] = $subscription_id;

        return $recurring_item;
    }

    protected function get_existing_order_item_recurring_id(array $order_item): int
    {
        $recurring_id = isset($order_item['recurring_id']) ? (int) $order_item['recurring_id'] : 0;

        return $recurring_id > 0 && get_post_type($recurring_id) === 'listdom-recurring'
            ? $recurring_id
            : 0;
    }

    protected function collect_existing_recurring_stripe_meta(int $recurring_id, array &$subscription_ids, array &$price_ids): void
    {
        if ($recurring_id < 1) return;

        $recurring = LSD_Payments_Recurrings::get($recurring_id);
        if (!$recurring instanceof LSD_Payments_Recurring) return;

        $meta = $recurring->get_gateway_meta();

        $subscription_id = isset($meta['stripe_subscription_id']) ? sanitize_text_field((string) $meta['stripe_subscription_id']) : '';
        if ($subscription_id !== '') $subscription_ids[] = $subscription_id;

        $price_id = isset($meta['stripe_price_id']) ? sanitize_text_field((string) $meta['stripe_price_id']) : '';
        if ($price_id !== '')
        {
            $price_ids[] = $price_id;
            return;
        }

        if (isset($meta['stripe_price_ids']) && is_array($meta['stripe_price_ids']))
        {
            foreach ($meta['stripe_price_ids'] as $candidate_price_id)
            {
                $candidate_price_id = sanitize_text_field((string) $candidate_price_id);
                if ($candidate_price_id !== '') $price_ids[] = $candidate_price_id;
            }
        }
    }

    public function complete_order(int $order_id, array $args = [])
    {
        if (!$this->subscription_context || !$this->subscription_setup) return true;
        if (!$this->set_api_key()) return new WP_Error('lsd_stripe_missing_key', esc_html__('Stripe secret key is missing.', 'listdom'));

        $allocated = $this->allocate_subscription_amounts();
        if (!$allocated) return new WP_Error('lsd_stripe_invalid_amount', esc_html__('Unable to determine subscription amount.', 'listdom'));

        $currency = $this->subscription_context['currency'];
        $recurring_total = isset($this->subscription_context['recurring_subtotal'])
            ? (float) $this->subscription_context['recurring_subtotal']
            : 0.0;

        if ($recurring_total <= 0)
        {
            foreach ($allocated as $item)
            {
                $recurring_total += isset($item['amount']) ? (float) $item['amount'] : 0.0;
            }
        }

        $first_cycle_discount = isset($this->subscription_context['recurring_discount'])
            ? (float) $this->subscription_context['recurring_discount']
            : 0.0;

        if ($recurring_total > 0 && $first_cycle_discount > $recurring_total)
        {
            $first_cycle_discount = $recurring_total;
        }

        $customer_name = isset($args['name']) ? sanitize_text_field($args['name']) : '';
        $customer_email = isset($args['email']) ? sanitize_email($args['email']) : '';

        if (!$customer_name)
        {
            $customer_name = get_post_meta($order_id, 'lsd_name', true);
        }

        if (!$customer_email)
        {
            $customer_email = get_post_meta($order_id, 'lsd_email', true);
        }

        $order = LSD_Payments_Orders::get($order_id);
        if (!$order)
        {
            return new WP_Error('lsd_stripe_order_missing', esc_html__('Unable to load the order for Stripe subscription setup.', 'listdom'));
        }

        $user_id = $order->get_user_id();
        $order_items = $order->get_items();
        $order_item_map = $this->get_order_recurring_items_map($order, $allocated);
        $item_discounts = $this->allocate_first_cycle_discounts($allocated, $first_cycle_discount, $currency);

        $all_items_already_linked = count($order_item_map) > 0;
        foreach ($order_item_map as $mapped_item)
        {
            $order_item = isset($mapped_item['item']) && is_array($mapped_item['item']) ? $mapped_item['item'] : [];
            if ($this->get_existing_order_item_recurring_id($order_item) < 1)
            {
                $all_items_already_linked = false;
                break;
            }
        }

        if ($all_items_already_linked)
        {
            $this->subscription_context = null;
            $this->subscription_setup = null;

            return true;
        }

        try
        {
            $customer = Customer::create(array_filter([
                'name' => $customer_name ?: null,
                'email' => $customer_email ?: null,
                'metadata' => [
                    'lsd_first_order_id' => (string) $order_id,
                ],
            ]));

            $payment_method_id = $this->subscription_setup['payment_method'];

            $payment_method = PaymentMethod::retrieve($payment_method_id);
            $payment_method->attach(['customer' => $customer->id]);
            Customer::update($customer->id, [
                'invoice_settings' => [
                    'default_payment_method' => $payment_method_id,
                ],
            ]);
        }
        catch (ApiErrorException $e)
        {
            return new WP_Error('lsd_stripe_customer_error', $e->getMessage());
        }

        $persisted_subscription_ids = [];
        $persisted_price_ids = [];
        $persisted_recurring_ids = [];
        $created_subscription_ids = [];
        $created_price_ids = [];
        $created_recurring_ids = [];
        $created_coupon_ids = [];
        $created_tax_rate_ids = [];
        $updated_order_items = $order_items;

        $tax_rate_payload = $this->create_subscription_tax_rates($order);
        if (isset($tax_rate_payload['error']) && $tax_rate_payload['error'] instanceof WP_Error)
        {
            $this->rollback_partial_subscription_setup($order_id, [], [], [], [], $tax_rate_payload['created_ids'] ?? []);
            return $tax_rate_payload['error'];
        }

        $subscription_tax_rate_ids = isset($tax_rate_payload['ids']) && is_array($tax_rate_payload['ids'])
            ? array_values(array_filter(array_map('strval', $tax_rate_payload['ids'])))
            : [];
        $created_tax_rate_ids = isset($tax_rate_payload['created_ids']) && is_array($tax_rate_payload['created_ids'])
            ? array_values(array_filter(array_map('strval', $tax_rate_payload['created_ids'])))
            : [];

        foreach ($allocated as $allocated_index => $allocated_item)
        {
            if (!isset($order_item_map[$allocated_index])) continue;

            $order_item_index = $order_item_map[$allocated_index]['index'];
            $order_item = $order_item_map[$allocated_index]['item'];

            $existing_recurring_id = $this->get_existing_order_item_recurring_id($order_item);
            if ($existing_recurring_id > 0)
            {
                $persisted_recurring_ids[] = $existing_recurring_id;
                $this->collect_existing_recurring_stripe_meta($existing_recurring_id, $persisted_subscription_ids, $persisted_price_ids);
                continue;
            }

            $price_payload = $this->create_subscription_price($allocated_item, $currency, $order->prices_include_tax());
            if (!$price_payload)
            {
                $this->rollback_partial_subscription_setup($order_id, $created_subscription_ids, $created_recurring_ids, $created_price_ids, $created_coupon_ids, $created_tax_rate_ids);
                return new WP_Error('lsd_stripe_price_error', esc_html__('Unable to prepare a Stripe subscription price.', 'listdom'));
            }

            $price_id = $price_payload['price_id'];
            $persisted_price_ids[] = $price_id;
            $created_price_ids[] = $price_id;

            $subscription_args = [
                'customer' => $customer->id,
                'items' => [$price_payload['item']],
                'default_payment_method' => $payment_method_id,
                'metadata' => [
                    'lsd_first_order_id' => (string) $order_id,
                    'lsd_order_item_index' => (string) $order_item_index,
                    'lsd_plan_id' => (string) $allocated_item['plan_id'],
                    'lsd_tier_id' => (string) $allocated_item['tier_id'],
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ];

            if ($subscription_tax_rate_ids)
            {
                $subscription_args['items'][0]['tax_rates'] = $subscription_tax_rate_ids;
            }

            $item_discount = isset($item_discounts[$allocated_index]) ? (float) $item_discounts[$allocated_index] : 0.0;
            $item_coupon_ids = [];
            if ($item_discount > 0)
            {
                $discount_amount = $this->multiply($item_discount, $currency);
                if ($discount_amount > 0)
                {
                    try
                    {
                        $coupon = Coupon::create([
                            'duration' => 'once',
                            'amount_off' => $discount_amount,
                            'currency' => $currency,
                            'name' => sprintf(
                            /* translators: %s: order id */
                                esc_html__('Listdom Order %s Discount', 'listdom'),
                                (string) $order_id
                            ),
                            'metadata' => [
                                'lsd_first_order_id' => (string) $order_id,
                                'lsd_order_item_index' => (string) $order_item_index,
                            ],
                        ]);

                        $item_coupon_ids[] = $coupon->id;
                        $created_coupon_ids[] = $coupon->id;
                        $subscription_args['discounts'] = [
                            [
                                'coupon' => $coupon->id,
                            ],
                        ];
                    }
                    catch (ApiErrorException $e)
                    {
                        $this->rollback_partial_subscription_setup($order_id, $created_subscription_ids, $created_recurring_ids, $created_price_ids, $created_coupon_ids, $created_tax_rate_ids);
                        return new WP_Error('lsd_stripe_coupon_error', $e->getMessage());
                    }
                }
            }

            try
            {
                $subscription = Subscription::create($subscription_args);
            }
            catch (ApiErrorException $e)
            {
                $this->rollback_partial_subscription_setup($order_id, $created_subscription_ids, $created_recurring_ids, $created_price_ids, $created_coupon_ids, $created_tax_rate_ids);
                return new WP_Error('lsd_stripe_subscription_error', $e->getMessage());
            }

            $persisted_subscription_ids[] = $subscription->id;
            $created_subscription_ids[] = $subscription->id;

            if ($this->subscription_is_incomplete($subscription))
            {
                $this->rollback_partial_subscription_setup($order_id, $created_subscription_ids, $created_recurring_ids, $created_price_ids, $created_coupon_ids, $created_tax_rate_ids);
                $this->subscription_context = null;
                $this->subscription_setup = null;

                return new WP_Error(
                    'lsd_stripe_subscription_incomplete',
                    esc_html__('The subscription could not be activated because the payment was not completed. Please try another payment method.', 'listdom')
                );
            }

            $recurring_item = $this->prepare_recurring_item($order_item, $allocated_item, $price_id, $subscription->id);

            $recurring_id = LSD_Payments_Recurrings::add([
                'first_order_id' => $order_id,
                'gateway' => $this->key(),
                'user_id' => $user_id,
                'items' => [$recurring_item],
                'total' => isset($allocated_item['amount']) ? (float) $allocated_item['amount'] : 0.0,
                'currency' => $currency,
                'status' => LSD_Payments_Recurrings::STATUS_ACTIVE,
                'gateway_meta' => [
                    'stripe_customer_id' => $customer->id,
                    'stripe_subscription_id' => $subscription->id,
                    'stripe_status' => $subscription->status,
                    'stripe_price_id' => $price_id,
                    'stripe_price_ids' => [$price_id],
                    'stripe_payment_method_id' => $payment_method_id,
                    'stripe_setup_intent_id' => $this->subscription_setup['setup_intent'],
                    'stripe_coupon_ids' => $item_coupon_ids,
                    'stripe_tax_rate_ids' => $subscription_tax_rate_ids,
                    'current_period_start' => isset($subscription->current_period_start) ? (int) $subscription->current_period_start : 0,
                    'current_period_end' => isset($subscription->current_period_end) ? (int) $subscription->current_period_end : 0,
                ],
            ]);

            if (!$recurring_id)
            {
                $this->rollback_partial_subscription_setup($order_id, $created_subscription_ids, $created_recurring_ids, $created_price_ids, $created_coupon_ids, $created_tax_rate_ids);
                return new WP_Error('lsd_stripe_recurring_error', esc_html__('Unable to create the Listdom recurring payment.', 'listdom'));
            }

            $persisted_recurring_ids[] = $recurring_id;
            $created_recurring_ids[] = $recurring_id;

            $updated_order_items[$order_item_index]['recurring_id'] = $recurring_id;
            $updated_order_items[$order_item_index]['stripe_subscription_id'] = $subscription->id;
            $updated_order_items[$order_item_index]['stripe_price_id'] = $price_id;
        }

        $persisted_recurring_ids = array_values(array_unique(array_filter(array_map('intval', $persisted_recurring_ids))));
        $persisted_subscription_ids = array_values(array_unique(array_filter($persisted_subscription_ids)));
        $persisted_price_ids = array_values(array_unique(array_filter($persisted_price_ids)));
        $created_recurring_ids = array_values(array_unique(array_filter(array_map('intval', $created_recurring_ids))));

        if (!count($persisted_recurring_ids))
        {
            $this->rollback_partial_subscription_setup($order_id, $created_subscription_ids, $created_recurring_ids, $created_price_ids, $created_coupon_ids, $created_tax_rate_ids);
            return new WP_Error('lsd_stripe_no_recurring_created', esc_html__('No Stripe subscriptions could be created for this order.', 'listdom'));
        }

        update_post_meta($order_id, 'lsd_items', $updated_order_items);
        update_post_meta($order_id, 'lsd_stripe_setup_intent_id', $this->subscription_setup['setup_intent']);
        update_post_meta($order_id, 'lsd_stripe_payment_method_id', $this->subscription_setup['payment_method']);
        update_post_meta($order_id, 'lsd_stripe_customer_id', $customer->id);
        update_post_meta($order_id, 'lsd_stripe_subscription_ids', $persisted_subscription_ids);
        update_post_meta($order_id, 'lsd_stripe_price_ids', $persisted_price_ids);
        update_post_meta($order_id, 'lsd_recurring_ids', $persisted_recurring_ids);

        if (count($persisted_subscription_ids) === 1)
        {
            update_post_meta($order_id, 'lsd_stripe_subscription_id', $persisted_subscription_ids[0]);
        }
        else
        {
            delete_post_meta($order_id, 'lsd_stripe_subscription_id');
        }

        if (count($persisted_recurring_ids) === 1)
        {
            update_post_meta($order_id, 'lsd_recurring_id', $persisted_recurring_ids[0]);
        }
        else
        {
            delete_post_meta($order_id, 'lsd_recurring_id');
        }

        update_post_meta($order_id, 'lsd_stripe_subscription_status', 'active');

        $this->subscription_context = null;
        $this->subscription_setup = null;

        return true;
    }
}
