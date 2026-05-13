<?php

class LSD_Payments extends LSD_Base
{
    const STATUS_PENDING = 'pending';
    const STATUS_ON_HOLD = 'on-hold';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';
    const STATUS_FAILED = 'failed';
    const STATUS_DRAFT = 'draft';
    const STATUS_REFUNDED = 'refunded';

    public function init()
    {
        (new LSD_PTypes_Plan())->init();
        (new LSD_PTypes_Coupon())->init();

        // Orders
        (new LSD_PTypes_Order())->init();
        (new LSD_Payments_Statuses())->init();

        // Taxes
        (new LSD_Payments_Taxonomy())->init();

        // Invoice
        (new LSD_Payments_Invoice())->init();

        // Recurring
        (new LSD_PTypes_Recurring())->init();
        (new LSD_Payments_Recurring_Statuses())->init();

        add_filter('lsd_backend_header_post_types', [$this, 'backend_header_post_types']);
        add_filter('lsd_should_include_backend', [$this, 'should_include']);
        add_filter('lsd_post_type_header_menus', [$this, 'header_menus'], 10, 2);
        add_filter('lsd_taxonomy_header_menus', [$this, 'taxonomy_header_menus'], 10, 2);
        add_filter('lsd_backend_header_taxonomies', [$this, 'backend_header_taxonomies']);
    }

    public function backend_header_post_types($post_types)
    {
        $post_types[] = LSD_Base::PTYPE_ORDER;
        $post_types[] = LSD_Base::PTYPE_RECURRING;
        $post_types[] = LSD_Base::PTYPE_PLAN;
        $post_types[] = LSD_Base::PTYPE_COUPON;

        return $post_types;
    }

    public function backend_header_taxonomies(array $taxonomies): array
    {
        if ($this->taxes_enabled()) $taxonomies[] = LSD_Base::TAX_TAX;

        return $taxonomies;
    }

    public function should_include($should): bool
    {
        $screen = get_current_screen();
        $types = [LSD_Base::PTYPE_ORDER, LSD_Base::PTYPE_PLAN, LSD_Base::PTYPE_COUPON, LSD_Base::PTYPE_RECURRING];

        if ($screen->post_type && in_array($screen->post_type, $types, true)) return true;

        return $should;
    }

    public function header_menus(array $menus, $screen): array
    {
        $post_type = $screen->post_type ?? '';

        $types = [LSD_Base::PTYPE_ORDER, LSD_Base::PTYPE_PLAN, LSD_Base::PTYPE_COUPON, LSD_Base::PTYPE_RECURRING];

        if (!in_array($post_type, $types, true)) return $menus;

        $menus = [];

        $menus = $this->payments_menus('post_type', $post_type);

        return $menus;
    }

    public function taxonomy_header_menus(array $menus, $screen): array
    {
        $taxonomy = $screen->taxonomy ?? '';
        if ($taxonomy !== LSD_Base::TAX_TAX) return $menus;

        return $this->payments_menus('taxonomy', $taxonomy);
    }

    protected function payments_menus(string $context, string $selected): array
    {
        $menus = [];

        $menus[] = [
            'title' => esc_html__('Plans', 'listdom'),
            'url' => admin_url('edit.php?post_type=' . LSD_Base::PTYPE_PLAN),
            'selected' => $context === 'post_type' && $selected === LSD_Base::PTYPE_PLAN,
        ];

        $menus[] = [
            'title' => esc_html__('Coupons', 'listdom'),
            'url' => admin_url('edit.php?post_type=' . LSD_Base::PTYPE_COUPON),
            'selected' => $context === 'post_type' && $selected === LSD_Base::PTYPE_COUPON,
        ];

        $menus[] = [
            'title' => esc_html__('Recurring Payments', 'listdom'),
            'url' => admin_url('edit.php?post_type=' . LSD_Base::PTYPE_RECURRING),
            'selected' => $context === 'post_type' && $selected === LSD_Base::PTYPE_RECURRING,
        ];

        $menus[] = [
            'title' => esc_html__('Orders', 'listdom'),
            'url' => admin_url('edit.php?post_type=' . LSD_Base::PTYPE_ORDER),
            'selected' => $context === 'post_type' && $selected === LSD_Base::PTYPE_ORDER,
        ];

        if ($this->taxes_enabled())
        {
            $menus[] = [
                'title' => esc_html__('Tax', 'listdom'),
                'url' => admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_TAX . '&post_type=' . LSD_Base::PTYPE_ORDER),
                'selected' => $context === 'taxonomy' && $selected === LSD_Base::TAX_TAX,
            ];
        }

        return $menus;
    }

    protected function taxes_enabled(): bool
    {
        $payments = LSD_Options::payments();
        $taxes = $payments['taxes'] ?? [];

        return !empty($taxes['enable']);
    }

    public static function gateways(): array
    {
        $gateways = apply_filters('lsd_payments_gateways', [
            new LSD_Payments_Gateways_Free(),
            new LSD_Payments_Gateways_Onsite(),
            new LSD_Payments_Gateways_Wiretransfer(),
            new LSD_Payments_Gateways_Paypal()
        ]);

        usort($gateways, function ($a, $b)
        {
            $aPos = (int) ($a->options()['position'] ?? 0);
            $bPos = (int) ($b->options()['position'] ?? 0);

            if ($aPos === $bPos) return 0;
            return ($aPos < $bPos) ? -1 : 1;
        });

        return $gateways;
    }

    public static function gateway(string $key): ?LSD_Payments_Gateway
    {
        foreach (self::gateways() as $gateway)
        {
            if ($gateway->key() === $key) return $gateway;
        }

        return null;
    }

    public static function pending_count(): int
    {
        $counts = wp_count_posts(LSD_Base::PTYPE_ORDER);
        return isset($counts->{self::STATUS_PENDING}) ? (int) $counts->{self::STATUS_PENDING} : 0;
    }
}
