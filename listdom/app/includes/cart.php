<?php

class LSD_Cart extends LSD_Base
{
    protected $cookie_name = 'lsd_cart_id';
    protected $option_prefix = 'lsd_cart_';

    protected function get(): array
    {
        $cart_id = $this->get_id();
        $cart = get_option($this->option_prefix . $cart_id, []);

        if (!is_array($cart)) $cart = [];
        if (!isset($cart['items']) || !is_array($cart['items'])) $cart['items'] = [];
        if (!isset($cart['fees']) || !is_array($cart['fees'])) $cart['fees'] = [];
        if (!isset($cart['coupon']) || !is_array($cart['coupon'])) $cart['coupon'] = [];
        if (!isset($cart['tax_location']) || !is_array($cart['tax_location'])) $cart['tax_location'] = [];

        return $cart;
    }

    protected function save(array $cart): void
    {
        $cart_id = $this->get_id();
        update_option($this->option_prefix . $cart_id, $cart, false);
    }

    public function get_items(): array
    {
        $cart = $this->get();
        return $cart['items'];
    }

    public function get_fees(): array
    {
        $cart = $this->get();
        return $cart['fees'];
    }

    public function get_tax_location(): array
    {
        $cart = $this->get();
        $location = $cart['tax_location'] ?? [];

        $country = isset($location['country']) ? sanitize_text_field($location['country']) : '';
        $state = isset($location['state']) ? sanitize_text_field($location['state']) : '';

        if ($country === '' && $state === '')
        {
            $default_location = (new LSD_Payments_Tax())->default_location();
            if ($default_location)
            {
                $this->set_tax_location($default_location['country'] ?? '', $default_location['state'] ?? '');
                $cart = $this->get();
                $location = $cart['tax_location'] ?? [];
            }
        }

        return [
            'country' => isset($location['country']) ? sanitize_text_field($location['country']) : '',
            'state' => isset($location['state']) ? sanitize_text_field($location['state']) : '',
        ];
    }

    public function set_tax_location(string $country = '', string $state = ''): void
    {
        $cart = $this->get();

        $cart['tax_location'] = [
            'country' => strtoupper(sanitize_text_field($country)),
            'state' => strtoupper(sanitize_text_field($state)),
        ];

        $this->save($cart);
    }

    public function add(int $plan_id, ?string $tier_id = null, array $meta = []): void
    {
        $cart = $this->get();

        // Tier ID
        if ($tier_id === null || $tier_id === '') $tier_id = (new LSD_Payments_Plan($plan_id))->get_default_tier_id();
        else $tier_id = sanitize_text_field($tier_id);

        // Item ID
        $id = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : md5(uniqid('lsd', true));

        $cart['items'][$id] = [
            'plan_id' => $plan_id,
            'tier_id' => $tier_id,
            'meta' => $meta,
        ];

        $this->save($cart);
    }

    public function set_fees(array $fees): void
    {
        $cart = $this->get();
        $cart['fees'] = $fees;

        $this->save($cart);
    }

    public function clear_fees(): void
    {
        $cart = $this->get();
        $cart['fees'] = [];

        $this->save($cart);
    }

    public function remove(string $id): void
    {
        $cart = $this->get();

        if (isset($cart['items'][$id]))
        {
            unset($cart['items'][$id]);
            $this->save($cart);
        }
    }

    public function update(string $id, string $tier_id): void
    {
        $cart = $this->get();

        if (isset($cart['items'][$id]))
        {
            $cart['items'][$id]['tier_id'] = sanitize_text_field($tier_id);
            $this->save($cart);
        }
    }

    protected function get_id(): string
    {
        if (isset($_COOKIE[$this->cookie_name]) && $_COOKIE[$this->cookie_name])
            return sanitize_text_field($_COOKIE[$this->cookie_name]);

        $cart_id = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : md5(uniqid('lsd', true));

        if (!headers_sent()) setcookie($this->cookie_name, $cart_id, time() + DAY_IN_SECONDS * 30, COOKIEPATH, COOKIE_DOMAIN);
        $_COOKIE[$this->cookie_name] = $cart_id;

        return $cart_id;
    }

    public function is_empty(): bool
    {
        return !count($this->get_items()) && !count($this->get_fees());
    }

    public function get_coupon(): string
    {
        $cart = $this->get();
        return $cart['coupon']['code'] ?? '';
    }

    public function add_coupon(string $code): void
    {
        $cart = $this->get();
        $cart['coupon'] = [
            'code' => sanitize_text_field($code),
        ];

        $this->save($cart);
    }

    public function remove_coupon(): void
    {
        $cart = $this->get();
        $cart['coupon'] = [];

        $this->save($cart);
    }

    public function clear(): void
    {
        $cart_id = $this->get_id();
        delete_option($this->option_prefix . $cart_id);

        if (!headers_sent()) setcookie($this->cookie_name, '', time() - DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
        unset($_COOKIE[$this->cookie_name]);
    }

    public function get_sub_total()
    {
        $subtotals = $this->get_subtotals();
        return $subtotals['subtotal'];
    }

    protected function get_subtotals(): array
    {
        $items = $this->get_items();
        $fees = $this->get_fees();

        $items_total = 0;
        foreach ($items as $item)
        {
            $plan_id = (int) ($item['plan_id'] ?? 0);
            $tier_id = $item['tier_id'] ?? '';

            // Plan
            $plan = new LSD_Payments_Plan($plan_id, $tier_id);

            $items_total += $plan->get_price();
        }

        $fees_total = 0;
        foreach ($fees as $fee)
        {
            $fees_total += isset($fee['amount']) ? (float) $fee['amount'] : 0;
        }

        return [
            'items' => $items_total,
            'fees' => $fees_total,
            'subtotal' => $items_total + $fees_total,
        ];
    }

    protected function calculate_discount(float $subtotal): float
    {
        $coupon_code = $this->get_coupon();
        $coupon = $coupon_code ? new LSD_Payments_Coupon($coupon_code) : null;

        $discount = $coupon ? $coupon->calculate($subtotal) : 0;

        return round($discount, 2);
    }

    public function get_totals(): array
    {
        $subtotals = $this->get_subtotals();
        $subtotal = $subtotals['subtotal'];

        $discount = $this->calculate_discount($subtotal);
        $post_discount_total = max($subtotal - $discount, 0);

        $tax_helper = new LSD_Payments_Tax();
        $taxable_subtotal = $subtotals['items'];
        $tax_location = $this->get_tax_location();
        $tax_items = $tax_helper->calculate_items($taxable_subtotal, $discount, $subtotal, $tax_location);
        $tax = round(array_reduce($tax_items, static function ($carry, $item)
        {
            $amount = isset($item['amount']) ? (float) $item['amount'] : 0.0;
            return $carry + $amount;
        }, 0.0), 2);

        $total = $tax_helper->apply_to_total($post_discount_total, $tax);

        return [
            'items_subtotal' => $subtotals['items'],
            'fees_subtotal' => $subtotals['fees'],
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'taxes' => $tax_items,
            'total' => $total,
        ];
    }

    public function apply_coupon(): array
    {
        $totals = $this->get_totals();
        return [$totals['total'], $totals['discount'], $totals['tax']];
    }
}
