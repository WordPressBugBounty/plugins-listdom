<?php

class LSD_Payments_Order extends LSD_Base
{
    private $order;

    public function __construct($order)
    {
        $this->order = get_post($order);
    }

    public function get_id(): int
    {
        return $this->order ? $this->order->ID : 0;
    }

    public function get_user_id(): int
    {
        return (int) get_post_meta($this->get_id(), 'lsd_user_id', true);
    }

    public function get_user(): ?WP_User
    {
        $user_id = $this->get_user_id();
        return $user_id ? get_userdata($user_id) : null;
    }

    public function get_coupon(): ?string
    {
        $coupon = get_post_meta($this->get_id(), 'lsd_coupon', true);
        return $coupon !== '' ? $coupon : null;
    }

    public function get_gateway(): ?string
    {
        $gateway = get_post_meta($this->get_id(), 'lsd_gateway', true);
        return $gateway !== '' ? $gateway : null;
    }

    public function get_name(): string
    {
        $name = get_post_meta($this->get_id(), 'lsd_name', true);
        return $name ?: '';
    }

    public function get_email(): string
    {
        $email = get_post_meta($this->get_id(), 'lsd_email', true);
        return $email ?: '';
    }

    public function get_message(): string
    {
        $message = get_post_meta($this->get_id(), 'lsd_message', true);
        return $message ?: '';
    }

    public function get_key(): string
    {
        $key = get_post_meta($this->get_id(), 'lsd_key', true);
        return $key ?: '';
    }

    public function get_invoice_url(): string
    {
        $order_key = $this->get_key();
        if (!$order_key) return '';

        $url = add_query_arg('lsd-invoice', $order_key, home_url('/'));
        return is_string($url) ? $url : '';
    }

    public function get_items(): array
    {
        $items = get_post_meta($this->get_id(), 'lsd_items', true);
        return is_array($items) ? $items : [];
    }

    public function get_fees(): array
    {
        $fees = get_post_meta($this->get_id(), 'lsd_fees', true);
        return is_array($fees) ? $fees : [];
    }

    public function get_lines(): array
    {
        $items = $this->get_items();
        $fees = $this->get_fees();
        $currency = LSD_Options::currency();

        $lines = [];
        foreach ($items as $item)
        {
            $plan_id = (int)($item['plan_id'] ?? 0);
            $tier_id = $item['tier_id'] ?? '';

            $plan = new LSD_Payments_Plan($plan_id, $tier_id);
            $tier = $plan->get_tier();

            $lines[] = $plan->get_name() . ($tier ? ' - ' . $tier->get_name() : '');
        }

        foreach ($fees as $fee)
        {
            $title = isset($fee['title']) ? trim((string) $fee['title']) : '';
            $amount = isset($fee['amount']) ? (float) $fee['amount'] : 0;

            $line = $title !== '' ? $title : esc_html__('Fee', 'listdom');
            if ($amount !== 0) $line .= ' - ' . $this->render_price($amount, $currency, false, false);

            $lines[] = $line;
        }

        return $lines;
    }

    public function get_metas(array $item): array
    {
        $metas = $item['meta'] ?? [];

        $lines = [];
        if (!is_array($metas)) return $lines;

        foreach ($metas as $key => $value)
        {
            if (!is_scalar($value)) continue;

            $lines[] = $this->get_meta_label($key) . ': ' . $this->get_meta_value($key, $value);
        }

        return $lines;
    }

    public function get_meta_label(string $key): string
    {
        if ($key === 'lsd_listing_id') return __('Listing', 'listdom');
        if ($key === 'lsd_package_id') return __('Package', 'listdom');
        if ($key === 'lsd_user_id') return __('User', 'listdom');
        if ($key === 'lsd_claim_id') return __('Claim', 'listdom');

        return $key;
    }

    public function get_meta_value(string $key, string $value): string
    {
        if (in_array($key, ['lsd_listing_id', 'lsd_package_id', 'lsd_claim_id']))
        {
            $post = get_post($value);
            if ($post instanceof WP_Post)
            {
                $pt = get_post_type_object($post->post_type);
                $title = $post->post_title;

                if ($pt && !$pt->public)
                {
                    $url = get_edit_post_link($post);
                    if (!current_user_can('edit_post', $post)) $title = $value;
                }
                else $url = get_permalink($post);

                return $url ? '<a href="'.esc_url($url).'">'.esc_html($title).'</a>' : $title;
            }
        }

        return $value;
    }

    public function get_subtotal(): float
    {
        return (float) get_post_meta($this->get_id(), 'lsd_subtotal', true);
    }

    public function get_discount(): float
    {
        return (float) get_post_meta($this->get_id(), 'lsd_discount', true);
    }

    public function get_tax(): float
    {
        return (float) get_post_meta($this->get_id(), 'lsd_tax', true);
    }

    public function get_tax_location(): array
    {
        $location = get_post_meta($this->get_id(), 'lsd_tax_location', true);
        return is_array($location) ? $location : [];
    }

    public function prices_include_tax(): bool
    {
        return (bool) get_post_meta($this->get_id(), 'lsd_tax_prices_include', true);
    }

    public function get_tax_items(): array
    {
        $items = get_post_meta($this->get_id(), 'lsd_tax_items', true);
        if (!is_array($items)) return [];

        $sanitized = [];
        foreach ($items as $item)
        {
            if (!is_array($item)) continue;

            $rate = isset($item['rate']) ? (float) $item['rate'] : 0.0;
            $amount = isset($item['amount']) ? (float) $item['amount'] : 0.0;
            $label = isset($item['label']) ? sanitize_text_field($item['label']) : '';

            $sanitized[] = [
                'rate' => $rate,
                'amount' => $amount,
                'label' => $label !== '' ? $label : esc_html__('Tax', 'listdom'),
            ];
        }

        return $sanitized;
    }

    public function get_total(): float
    {
        return (float) get_post_meta($this->get_id(), 'lsd_total', true);
    }

    public function get_recurring_id(): int
    {
        return (int) get_post_meta($this->get_id(), 'lsd_recurring_id', true);
    }

    public function get_recurring(): ?LSD_Payments_Recurring
    {
        $recurring_id = $this->get_recurring_id();
        if (!$recurring_id) return null;

        return LSD_Payments_Recurrings::get($recurring_id);
    }

    public function get_recurring_number(): int
    {
        return (int) get_post_meta($this->get_id(), 'lsd_recurring_number', true);
    }
}
