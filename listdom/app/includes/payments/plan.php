<?php

class LSD_Payments_Plan extends LSD_Base
{
    private $plan;

    /**
     * @var LSD_Payments_Tier
     */
    private $tier;

    public function __construct($plan, $tier_id = null)
    {
        // Plan
        $this->plan = get_post($plan);

        // Set Tier
        $this->set_tier(
            $tier_id ?: $this->get_default_tier_id()
        );
    }

    public function get_id(): int
    {
        // Invalid Plan
        if (!$this->plan || $this->plan->post_type !== LSD_Base::PTYPE_PLAN) return 0;

        return $this->plan->ID;
    }

    public function get_name(): string
    {
        // Invalid Plan
        if (!$this->plan || $this->plan->post_type !== LSD_Base::PTYPE_PLAN) return '';

        return $this->plan->post_title;
    }

    public function get_title(): string
    {
        // Invalid Plan
        if (!$this->plan || $this->plan->post_type !== LSD_Base::PTYPE_PLAN) return '';

        $title = $this->plan->post_title;
        $tier = $this->get_tier();

        if ($tier)
        {
            $tier_name = trim($tier->get_name());
            if ($tier_name)
            {
                $tiers = $this->get_tiers();
                if (count($tiers) > 1) return $title . ' - ' . $tier_name;

                return $tier_name;
            }
        }

        return $title;
    }

    public function get_short_description(): string
    {
        // Invalid Plan
        if (!$this->plan || $this->plan->post_type !== LSD_Base::PTYPE_PLAN) return '';

        $excerpt = get_post_field('post_excerpt', $this->plan->ID);
        if (is_string($excerpt) && trim($excerpt) !== '')
        {
            return wp_kses_post(do_shortcode($excerpt));
        }

        $content = get_post_field('post_content', $this->plan->ID);
        if (!is_string($content) || trim($content) === '') return '';

        $trimmed = wp_trim_words(wp_strip_all_tags(do_shortcode($content)));

        return wp_kses_post($trimmed);
    }

    public function get_default_tier_id(): ?string
    {
        // Invalid Plan
        if (!$this->plan || $this->plan->post_type !== LSD_Base::PTYPE_PLAN) return null;

        // Tiers
        $tiers = $this->get_tiers();
        if (!count($tiers)) return null;

        // Return Default Tier
        foreach ($tiers as $tier)
        {
            if ($tier->is_default()) return $tier->get_id();
        }

        // Return First Tier
        $first = $tiers[0];
        return $first ? $first->get_id() : null;
    }

    /**
     * @return LSD_Payments_Tier[]
     */
    public function get_tiers(): array
    {
        // Tiers
        $t = get_post_meta($this->plan->ID, 'lsd_tiers', true);
        if (!is_array($t)) $t = [];

        $tiers = [];
        foreach ($t as $tier)
        {
            if (!isset($tier['id'])) continue;

            $tiers[] = $this->get_tier_by_id($tier['id']);
        }

        return $tiers;
    }

    public function get_tier_by_id(string $tier_id): ?LSD_Payments_Tier
    {
        // Invalid Plan
        if (!$this->plan || $this->plan->post_type !== LSD_Base::PTYPE_PLAN) return null;

        return new LSD_Payments_Tier($this->plan->ID, $tier_id);
    }

    public function set_tier(?string $tier_id): LSD_Payments_Plan
    {
        if ($tier_id === null)
        {
            $this->tier = null;
            return $this;
        }

        $this->tier = $this->get_tier_by_id($tier_id);
        return $this;
    }

    public function get_tier(): ?LSD_Payments_Tier
    {
        return $this->tier;
    }

    public function get_price(): float
    {
        return $this->tier ? $this->tier->get_price() : 0;
    }

    public function get_sale_price(): float
    {
        return $this->get_price();
    }

    public function get_regular_price(): float
    {
        return $this->get_price();
    }

    public function get_price_html(): string
    {
        return $this->tier ? $this->tier->get_price_html() : '';
    }

    public function get_frequency_days(): int
    {
        return $this->tier ? $this->tier->get_frequency_days() : 0;
    }

    public function get_frequency_html(): string
    {
        return $this->tier ? $this->tier->get_frequency_html() : '';
    }
}
