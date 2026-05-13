<?php

use Stripe\Coupon;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Price;
use Stripe\SetupIntent;
use Stripe\Subscription;

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

        foreach ($items as $item)
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
                    'plan_id' => $plan_id,
                    'tier_id' => $tier_id,
                    'price' => $price,
                    'name' => $plan->get_title(),
                    'tier_name' => $tier->get_name(),
                    'frequency_days' => max($tier->get_frequency_days(), 1),
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

    protected function create_subscription_items(array $allocated, string $currency): array
    {
        $items = [];
        $price_ids = [];

        foreach ($allocated as $item)
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

            $items[] = ['price' => $price->id, 'quantity' => 1];
            $price_ids[] = $price->id;
        }

        return ['items' => $items, 'price_ids' => $price_ids];
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

            $items_payload = $this->create_subscription_items($allocated, $currency);
            if (!$items_payload) return new WP_Error('lsd_stripe_price_error', esc_html__('Unable to prepare Stripe subscription items.', 'listdom'));

            $subscription_args = [
                'customer' => $customer->id,
                'items' => $items_payload['items'],
                'default_payment_method' => $payment_method_id,
                'metadata' => [
                    'lsd_first_order_id' => (string) $order_id,
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ];

            if ($first_cycle_discount > 0)
            {
                $discount_amount = $this->multiply($first_cycle_discount, $currency);
                if ($discount_amount > 0)
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
                        ],
                    ]);

                    $subscription_args['discounts'] = [
                        [
                            'coupon' => $coupon->id,
                        ],
                    ];
                }
            }

            $subscription = Subscription::create($subscription_args);
        }
        catch (ApiErrorException $e)
        {
            return new WP_Error('lsd_stripe_subscription_error', $e->getMessage());
        }

        update_post_meta($order_id, 'lsd_stripe_setup_intent_id', $this->subscription_setup['setup_intent']);
        update_post_meta($order_id, 'lsd_stripe_payment_method_id', $this->subscription_setup['payment_method']);
        update_post_meta($order_id, 'lsd_stripe_customer_id', $customer->id);
        update_post_meta($order_id, 'lsd_stripe_subscription_id', $subscription->id);
        update_post_meta($order_id, 'lsd_stripe_subscription_status', $subscription->status);
        update_post_meta($order_id, 'lsd_stripe_price_ids', $items_payload['price_ids']);

        $subscription_status = isset($subscription->status) ? sanitize_key($subscription->status) : '';
        $payment_intent_status = '';
        if (isset($subscription->latest_invoice) && is_object($subscription->latest_invoice) && isset($subscription->latest_invoice->payment_intent))
        {
            $payment_intent = $subscription->latest_invoice->payment_intent;
            if (is_object($payment_intent) && isset($payment_intent->status))
            {
                $payment_intent_status = sanitize_key($payment_intent->status);
            }
        }

        $incomplete_statuses = ['incomplete', 'incomplete_expired'];
        $requires_payment_method_statuses = ['requires_payment_method'];

        if (in_array($subscription_status, $incomplete_statuses, true) || ($payment_intent_status && in_array($payment_intent_status, $requires_payment_method_statuses, true)))
        {
            $this->subscription_context = null;
            $this->subscription_setup = null;

            return new WP_Error(
                'lsd_stripe_subscription_incomplete',
                esc_html__('The subscription could not be activated because the payment was not completed. Please try another payment method.', 'listdom')
            );
        }

        $order = LSD_Payments_Orders::get($order_id);
        $user_id = $order ? $order->get_user_id() : (int) get_post_meta($order_id, 'lsd_user_id', true);

        $recurring_id = LSD_Payments_Recurrings::add([
            'first_order_id' => $order_id,
            'gateway' => $this->key(),
            'user_id' => $user_id,
            'items' => $allocated,
            'total' => $recurring_total,
            'currency' => $currency,
            'status' => LSD_Payments_Recurrings::STATUS_ACTIVE,
            'gateway_meta' => [
                'stripe_customer_id' => $customer->id,
                'stripe_subscription_id' => $subscription->id,
                'stripe_status' => $subscription->status,
                'stripe_price_ids' => $items_payload['price_ids'],
                'stripe_payment_method_id' => $payment_method_id,
                'stripe_setup_intent_id' => $this->subscription_setup['setup_intent'],
            ],
        ]);

        if ($recurring_id)
        {
            update_post_meta($order_id, 'lsd_recurring_id', $recurring_id);
        }

        $this->subscription_context = null;
        $this->subscription_setup = null;

        return true;
    }
}
