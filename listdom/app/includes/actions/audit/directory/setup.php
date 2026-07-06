<?php

class LSD_Actions_Audit_Directory_Setup extends LSD_Actions_Action
{
    public function get_id(): string
    {
        return 'audit_directory_setup';
    }

    public function get_label(): string
    {
        return esc_html__('Audit Directory Setup', 'listdom');
    }

    public function get_capability(): string
    {
        return 'manage_options';
    }

    public function is_mutating(): bool
    {
        return false;
    }

    public function get_schema(): array
    {
        return [];
    }

    public function validate(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        return $this->validated([]);
    }

    public function execute(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        $settings = LSD_Options::settings();
        $auth = LSD_Options::auth();
        $payments = LSD_Options::payments();
        $listing_counts = wp_count_posts(LSD_Base::PTYPE_LISTING, 'readable');
        $search_counts = wp_count_posts(LSD_Base::PTYPE_SEARCH, 'readable');
        $shortcode_counts = wp_count_posts(LSD_Base::PTYPE_SHORTCODE, 'readable');

        $findings = [];
        $pages = [
            'submission_page' => [
                'enabled' => true,
                'page_id' => (int) ($settings['submission_page'] ?? 0),
            ],
            'add_listing_page' => [
                'enabled' => true,
                'page_id' => (int) ($settings['add_listing_page'] ?? 0),
            ],
            'login_page' => [
                'enabled' => !empty($auth['auth']['login_form']) && empty($auth['auth']['hide_login_form']),
                'page_id' => (int) ($auth['auth']['login_page'] ?? 0),
            ],
            'register_page' => [
                'enabled' => !empty($auth['auth']['register_form']) && empty($auth['auth']['hide_register_form']),
                'page_id' => (int) ($auth['auth']['register_page'] ?? 0),
            ],
            'forgot_password_page' => [
                'enabled' => !empty($auth['auth']['forgot_password_form']) && empty($auth['auth']['hide_forgot_password_form']),
                'page_id' => (int) ($auth['auth']['forgot_password_page'] ?? 0),
            ],
            'checkout_page' => [
                'enabled' => LSD_Payments_Engine::instance()->listdom(),
                'page_id' => (int) ($payments['checkout_page'] ?? 0),
            ],
        ];

        foreach ($pages as $key => $page)
        {
            if (empty($page['enabled'])) continue;

            $page_id = (int) ($page['page_id'] ?? 0);
            if ($page_id > 0 && get_post_status($page_id) === 'publish') continue;
            $findings[] = [
                'type' => 'warning',
                'code' => 'missing_page',
                'target' => $key,
                'message' => sprintf(esc_html__('The configured %s is missing or unpublished.', 'listdom'), $key),
            ];
        }

        $search_total = is_object($search_counts) ? array_sum((array) $search_counts) : 0;
        if ($search_total <= 0)
        {
            $findings[] = [
                'type' => 'warning',
                'code' => 'missing_search_forms',
                'target' => LSD_Base::PTYPE_SEARCH,
                'message' => esc_html__('No search forms were found.', 'listdom'),
            ];
        }

        $shortcode_total = is_object($shortcode_counts) ? array_sum((array) $shortcode_counts) : 0;
        if ($shortcode_total <= 0)
        {
            $findings[] = [
                'type' => 'warning',
                'code' => 'missing_directory_views',
                'target' => LSD_Base::PTYPE_SHORTCODE,
                'message' => esc_html__('No directory shortcodes were found.', 'listdom'),
            ];
        }

        if (wp_count_terms(LSD_Base::TAX_CATEGORY) <= 0)
        {
            $findings[] = [
                'type' => 'warning',
                'code' => 'missing_categories',
                'target' => LSD_Base::TAX_CATEGORY,
                'message' => esc_html__('No listing categories were found.', 'listdom'),
            ];
        }

        $payment_enabled = LSD_Payments_Engine::instance()->listdom();
        if ($payment_enabled && empty($payments['checkout_page']))
        {
            $findings[] = [
                'type' => 'warning',
                'code' => 'missing_monetization_setup',
                'target' => 'checkout_page',
                'message' => esc_html__('Payments are enabled but the checkout page is not configured.', 'listdom'),
            ];
        }

        return $this->success(
            esc_html__('Directory audit completed.', 'listdom'),
            [
                'summary' => [
                    'listings' => is_object($listing_counts) ? array_sum((array) $listing_counts) : 0,
                    'categories' => (int) wp_count_terms(LSD_Base::TAX_CATEGORY),
                    'custom_fields' => count(LSD_Main::get_attributes()),
                    'search_forms' => $search_total,
                    'directory_views' => $shortcode_total,
                    'payments_enabled' => $payment_enabled ? 1 : 0,
                ],
                'findings' => $findings,
                'healthy' => !count($findings),
            ]
        );
    }
}
