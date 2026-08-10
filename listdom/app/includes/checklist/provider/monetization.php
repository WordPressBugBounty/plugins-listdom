<?php

class LSD_Checklist_Provider_Monetization extends LSD_Checklist_Provider_Base
{
    public function register(LSD_Checklist_Registry $registry): void
    {
        $category = $this->category(esc_html__('Monetization', 'listdom'));

        $this->register_checks($registry, [
            $this->check([
                'id' => 'pricing_packages',
                'title' => esc_html__('Pricing and packages', 'listdom'),
                'description' => esc_html__('If you plan to monetize submissions, package setup should be ready before launch.', 'listdom'),
                'category' => $category,
                'importance' => 'high',
                'optional' => true,
                'action_label' => esc_html__('View', 'listdom'),
                'action_url' => $this->packages_action_url(),
                'action_focus_target' => '',
            ], [$this, 'pricing_packages']),
            $this->check([
                'id' => 'payments_setup',
                'title' => esc_html__('Listdom Payment Settings', 'listdom'),
                'description' => esc_html__('Checkout and payment gateways should be ready before you charge users.', 'listdom'),
                'category' => $category,
                'importance' => 'high',
                'optional' => true,
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->helper->settings_action_url('payments', 'options'),
                'action_focus_target' => '#lsd_payments_checkout_page',
            ], [$this, 'payments_setup']),
        ]);
    }

    protected function pricing_packages(?LSD_Checklist_Check_Interface $check = null): array
    {
        if (!LSD_Payments_Engine::instance()->listdom()) return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('Listdom Engine Payments are disabled on this site.', 'listdom'),];

        $post_type = $this->packages_post_type();
        if (!post_type_exists($post_type)) return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('Activate Listdom Membership or enable it through an active toolkit to create pricing packages.', 'listdom'), 'action_label' => esc_html__('View', 'listdom'), 'action_url' => admin_url('admin.php?page=listdom-addons'),];

        $packages = $this->helper->published_posts_count($post_type);

        return [
            'status' => $packages > 0 ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_INCOMPLETE,
            'message' => $packages > 0 ? sprintf(esc_html__('%d package(s) are published.', 'listdom'), $packages) : esc_html__('Create at least one pricing package.', 'listdom'),
            'action_url' => $this->packages_action_url(),
            'action_focus_target' => '',
        ];
    }

    protected function payments_setup(?LSD_Checklist_Check_Interface $check = null): array
    {
        // Checkout Page
        $checkout_page = (int) (LSD_Options::payments()['checkout_page'] ?? 0);

        $checkout_ready = $this->helper->published_post_has_shortcodes($checkout_page, ['listdom-checkout']);

        $gateways = array_filter(LSD_Payments::gateways(), static function ($gateway): bool
        {
            return $gateway instanceof LSD_Payments_Gateway && !$gateway->is_system() && $gateway->enabled();
        });

        $action_url = $this->helper->settings_action_url('payments', 'options');
        $action_focus_target = '#lsd_payments_checkout_page';

        // Payment Engine
        if (!LSD_Payments_Engine::instance()->listdom()) return ['status' => LSD_Checklist_Result::STATUS_OPTIONAL, 'message' => esc_html__('Payment engine is not active.', 'listdom'), 'action_url' => $this->helper->settings_action_url('payments', 'engine'), 'action_focus_target' => '#lsd_payments_system',];

        // Checkout Ready
        if ($checkout_ready && count($gateways)) return ['status' => LSD_Checklist_Result::STATUS_COMPLETE, 'message' => esc_html__('Checkout page and payment gateway settings are in place.  Note: You can use WooCommerce instead of the Listdom Payment Engine.', 'listdom'),];

        // Checkout Ready No GateWays
        if ($checkout_ready && !count($gateways))
        {
            $action_url = $this->helper->settings_action_url('payments', 'gateways');
            $action_focus_target = $this->gateway_focus_target();
        }

        return [
            'status' => LSD_Checklist_Result::STATUS_WARNING,
            'message' => esc_html__('Checkout page or gateway configuration still needs attention. Note: You can use WooCommerce instead of the Listdom Payment Engine.', 'listdom'),
            'action_url' => $action_url,
            'action_focus_target' => $action_focus_target,
        ];
    }

    protected function gateway_focus_target(): string
    {
        foreach (LSD_Payments::gateways() as $gateway)
        {
            if ($gateway->is_system()) continue;
            return '#lsd_gateway_' . $gateway->key() . '_status';
        }

        return '#lsd_panel_payments_gateways';
    }

    protected function packages_post_type(): string
    {
        if (class_exists(\LSDPACSUB\Base::class) && post_type_exists(\LSDPACSUB\Base::PTYPE_PACKAGE)) return \LSDPACSUB\Base::PTYPE_PACKAGE;

        return LSD_Base::PTYPE_PLAN;
    }

    protected function packages_action_url(): string
    {
        $post_type = $this->packages_post_type();

        if ($post_type === '' || !post_type_exists($post_type)) return admin_url('admin.php?page=listdom-addons');
        $post_id = $this->helper->first_published_post_id($post_type);

        return $post_id > 0 ? $this->helper->edit_post_url($post_id) : $this->helper->post_new_url($post_type);
    }
}
