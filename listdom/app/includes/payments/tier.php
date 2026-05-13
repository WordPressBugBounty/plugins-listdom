<?php

class LSD_Payments_Tier extends LSD_Base
{
    private $plan_id;
    private $tier_id;
    private $tier;

    public function __construct(int $plan_id, string $tier_id)
    {
        $this->plan_id = $plan_id;
        $this->tier_id = $tier_id;
        $this->tier = $this->data();
    }

    public function get_id(): string
    {
        return $this->tier_id;
    }

    public function get_name(): string
    {
        return isset($this->tier['name']) && trim($this->tier['name'])
            ? $this->tier['name']
            : $this->get_id();
    }

    public function get_type(): string
    {
        return isset($this->tier['type']) && trim($this->tier['type'])
            ? $this->tier['type']
            : 'one_time';
    }

    public function is_recurring(): bool
    {
        return $this->get_type() === 'recurring';
    }

    public function is_default(): bool
    {
        return isset($this->tier['default']) && $this->tier['default'];
    }

    public function data(): array
    {
        $plan = get_post($this->plan_id);
        if (!$plan || $plan->post_type !== LSD_Base::PTYPE_PLAN) return [];

        $tiers = get_post_meta($this->plan_id, 'lsd_tiers', true);
        if (!is_array($tiers)) return [];

        foreach ($tiers as $tier)
        {
            if (isset($tier['id']) && $tier['id'] === $this->tier_id)
                return $tier;
        }

        return [];
    }

    public function get_price(): float
    {
        return isset($this->tier['price']) ? (float) $this->tier['price'] : 0;
    }

    public function get_price_html(): string
    {
        $price = $this->get_price();
        return $this->render_price($price, LSD_Options::currency());
    }

    public function get_frequency_html(): string
    {
        $days = $this->get_frequency_days();
        if ($days) return sprintf(esc_html__('Per %d days', 'listdom'), $days);

        return esc_html__('One time', 'listdom');
    }

    public function get_price_frequency_html(): string
    {
        return $this->get_price_html() . ' / ' . $this->get_frequency_html();
    }

    public function get_frequency_days(): int
    {
        if (isset($this->tier['type']) && $this->tier['type'] === 'recurring')
        {
            return isset($this->tier['expiry']) ? (int) $this->tier['expiry'] : 0;
        }

        return 0;
    }
}
