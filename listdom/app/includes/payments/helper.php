<?php

class LSD_Payments_Helper extends LSD_Base
{
    private $engine;

    public function __construct()
    {
        $this->engine = LSD_Payments_Engine::instance()->engine();
    }

    /**
     * Get plan depending on the current payment engine.
     *
     * @param int $plan_id
     * @return LSD_Payments_Plan|WC_Product|null
     */
    public function plan(int $plan_id)
    {
        // Listdom Engine
        if ($this->engine === LSD_Payments_Engine::LISTDOM) return new LSD_Payments_Plan($plan_id);

        // Woo isn't Installed
        if (!function_exists('wc_get_product')) return null;

        $product = wc_get_product($plan_id);
        return $product ?: null;
    }

    /**
     * Render plan selection field depending on payment engine.
     */
    public function field_plan(array $args = []): string
    {
        $max_items = isset($args['max_items']) && is_numeric($args['max_items']) && $args['max_items'] > 0
            ? $args['max_items']
            : 1;

        $label = strtolower($this->plan_label());
        $defaults = [
            'source' => $this->engine === LSD_Payments_Engine::WC ? 'product' : 'listdom-plan',
            'name' => 'lsd_product',
            'id' => 'lsd_product',
            'input_id' => 'lsd_product_input',
            'suggestions' => 'lsd_product_suggestions',
            'max_items' => $max_items,
            'values' => [],
            'placeholder' => sprintf(esc_html__('Type at least 3 characters of the %s name ...', 'listdom'), $label),
            'description' => sprintf(esc_html__('Only %s %s can be selected.', 'listdom'), $max_items, $label),
        ];

        return LSD_Form::autosuggest(
            wp_parse_args($args, $defaults)
        );
    }

    public function plan_label(): string
    {
        return $this->engine === LSD_Payments_Engine::WC
            ? esc_html__('Product', 'listdom')
            : esc_html__('Plan', 'listdom');
    }

    public function currency(): string
    {
        if ($this->engine === LSD_Payments_Engine::WC)
        {
            return function_exists('get_woocommerce_currency')
                ? get_woocommerce_currency()
                : '';
        }

        return LSD_Options::currency();
    }

    public function currency_sign(?string $currency = null): string
    {
        $currency = $currency ?: $this->currency();

        if ($this->engine === LSD_Payments_Engine::WC)
        {
            return function_exists('get_woocommerce_currency_symbol')
                ? get_woocommerce_currency_symbol($currency)
                : '';
        }

        return (string) (new LSD_Base())->get_currency_sign($currency);
    }

    /**
     * Add plan/product to cart depending on payment engine.
     *
     * @param int $plan_id
     * @param array $args
     */
    public function add_to_cart(int $plan_id, array $args = []): void
    {
        $fees = isset($args['fees']) && is_array($args['fees']) ? $args['fees'] : [];
        $append_fees = isset($args['append_fees']) && $args['append_fees'];

        if ($this->engine === LSD_Payments_Engine::LISTDOM)
        {
            $tier_id = $args['tier_id'] ?? null;
            $meta = isset($args['meta']) && is_array($args['meta']) ? $args['meta'] : [];

            $checkout = new LSD_Checkout();
            $cart = $checkout->cart();

            if ($plan_id > 0) $cart->add($plan_id, $tier_id, $meta);

            if (!count($fees)) $cart->clear_fees();
            else
            {
                if ($append_fees) $fees = array_merge($cart->get_fees(), $fees);
                $cart->set_fees($fees);
            }

            return;
        }

        $meta = isset($args['meta']) && is_array($args['meta']) ? $args['meta'] : [];

        if (function_exists('WC') && WC()->cart)
        {
            $cart = WC()->cart->get_cart();
            foreach ($cart as $item)
            {
                if ($plan_id > 0 && isset($item['product_id']) && (int) $item['product_id'] === $plan_id)
                {
                    $plan_id = 0;
                    break;
                }
            }

            if ($plan_id > 0)
            {
                try
                {
                    WC()->cart->add_to_cart($plan_id, 1, 0, [], $meta);
                }
                catch (Exception $e) {}
            }
        }
    }

    public function cart_clear(): void
    {
        if ($this->engine === LSD_Payments_Engine::LISTDOM)
        {
            $checkout = new LSD_Checkout();
            $checkout->cart()->clear();

            return;
        }

        if (function_exists('WC') && WC()->cart)
        {
            WC()->cart->empty_cart();
        }
    }

    /**
     * Retrieve checkout URL depending on payment engine.
     */
    public function checkout_url(): string
    {
        if ($this->engine === LSD_Payments_Engine::LISTDOM)
        {
            $payments = LSD_Options::payments();
            $page_id = $payments['checkout_page'] ?? 0;

            return $page_id ? get_permalink($page_id) : home_url('/');
        }

        return function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/');
    }

    public function requirements_meet(): bool
    {
        // Listdom Engine
        if ($this->engine === LSD_Payments_Engine::LISTDOM) return true;

        // WooCommerce Engine
        return class_exists(WooCommerce::class);
    }

    public function requirements_message(): string
    {
        return esc_html__('To use payment features, either select the Listdom engine in Listdom → Settings → Payments → Engine, or install and activate WooCommerce to use the WooCommerce payment system.', 'listdom');
    }

    public function is_recurring_enabled(): bool
    {
        return (bool) apply_filters('lsd_payments_recurring_enabled', false);
    }

    public function is_recurring_available(): bool
    {
        if (!$this->is_recurring_enabled()) return false;
        if (!class_exists('LSD_Payments_Gateways_Stripe')) return false;

        $stripe = new LSD_Payments_Gateways_Stripe();
        return $stripe->enabled();
    }

    public static function bar_menus()
    {
        $bar = LSD_Bar::instance();

        $bar->menu(
            admin_url('admin.php?page=listdom-settings&tab=payments'),
            esc_html__('Payment Settings', 'listdom')
        );

        $bar->menu(
            admin_url('edit.php?post_type=' . LSD_Base::PTYPE_ORDER),
            esc_html__('Orders', 'listdom')
        );

        $bar->menu(
            admin_url('edit.php?post_type=' . LSD_Base::PTYPE_PLAN),
            esc_html__('Plans', 'listdom')
        );

        $bar->menu(
            admin_url('edit.php?post_type=' . LSD_Base::PTYPE_COUPON),
            esc_html__('Coupons', 'listdom')
        );

        $tax = new LSD_Payments_Tax();
        if ($tax->enabled())
        {
            $bar->menu(
                admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_TAX . '&post_type=' . LSD_Base::PTYPE_ORDER),
                esc_html__('Tax', 'listdom')
            );
        }
    }
}
