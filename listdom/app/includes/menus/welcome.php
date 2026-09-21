<?php

class LSD_Menus_Welcome extends LSD_Menus
{
    public function __construct()
    {
        // Initialize the Menu
        $this->init();
    }

    public function init()
    {
        // Newsletter Subscription
        add_action('wp_ajax_lsd_submit_newsletter', [$this, 'newsletter']);

        add_filter('admin_body_class', [$this, 'listdom_welcome_class']);
        add_filter('lsd_blueprints_apply_markup', [$this, 'completion'], 10, 3);
    }

    public function listdom_welcome_class($classes)
    {
        if (isset($_GET['page']) && $_GET['page'] === LSD_Base::WELCOME_SLUG) $classes .= ' lsd-welcome-wizard-page';
        return $classes;
    }

    public function output()
    {
        $this->assets();
        $this->include_html_file('menus/welcome/tpl.php', [
            'parameters' => [
                'directory_types' => $this->directory_types(),
                'questions' => $this->questions(),
                'answers' => $this->answers(),
            ],
        ]);
    }

    protected function assets(): void
    {
        wp_enqueue_script(
            'lsd-setup-wizard',
            $this->lsd_asset_url('js/wizard.min.js'),
            [],
            LSD_Assets::version(),
            true
        );

        wp_localize_script('lsd-setup-wizard', 'lsdSetupWizard', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'applyNonce' => wp_create_nonce('lsd_blueprints_apply'),
            'storageKey' => 'lsdSetupWizardState_' . (int) get_current_blog_id() . '_' . (int) get_current_user_id(),
            'dashboardUrl' => admin_url('admin.php?page=listdom'),
            'listingsUrl' => admin_url('edit.php?post_type=' . LSD_Base::PTYPE_LISTING),
            'settingsUrl' => admin_url('admin.php?page=listdom-settings'),
            'applyError' => esc_html__('There was an issue applying your setup. Please try again.', 'listdom'),
            'newsletterError' => esc_html__('There was an issue with the subscription, try again later.', 'listdom'),
        ]);
    }

    public function directory_types(): array
    {
        return [
            [
                'id' => 'business_directory',
                'icon' => 'wbli-list-view',
                'label' => esc_html__('Business Directory', 'listdom'),
                'description' => esc_html__('A local directory for businesses, shops, and venues.', 'listdom'),
            ],
            [
                'id' => 'city_portal',
                'icon' => 'wbli-location',
                'label' => esc_html__('City Portal', 'listdom'),
                'description' => esc_html__('A city-scale portal for places and public services.', 'listdom'),
            ],
            [
                'id' => 'service_marketplace',
                'icon' => 'wbli-group-items',
                'label' => esc_html__('Service Marketplace', 'listdom'),
                'description' => esc_html__('A marketplace for providers, services, and leads.', 'listdom'),
            ],
        ];
    }

    public function questions(): array
    {
        return [
            [
                'key' => 'location',
                'visual' => 'location',
                'title' => esc_html__('Are you going to use addresses and locations in your directory?', 'listdom'),
                'copy' => esc_html__('Add maps, addresses, and location-based filters to help visitors find listings nearby.', 'listdom'),
                'yes' => esc_html__('Enable addresses, maps, and location filters.', 'listdom'),
                'no' => esc_html__('Leave location fields off for now.', 'listdom'),
            ],
            [
                'key' => 'pricing',
                'visual' => 'pricing',
                'title' => esc_html__('Will your listings have prices or rates?', 'listdom'),
                'copy' => esc_html__('Show prices, price ranges, hourly rates, or other values on your listings.', 'listdom'),
                'yes' => esc_html__('Show prices and rates on listings.', 'listdom'),
                'no' => esc_html__('Leave pricing fields off for now.', 'listdom'),
            ],
            [
                'key' => 'work_hours',
                'visual' => 'hours',
                'title' => esc_html__('Do your listings need opening hours?', 'listdom'),
                'copy' => esc_html__('Let visitors see when a business or service is open and available.', 'listdom'),
                'yes' => esc_html__('Show when listings are open.', 'listdom'),
                'no' => esc_html__('Leave opening hours off for now.', 'listdom'),
            ],
            [
                'key' => 'reviews',
                'visual' => 'reviews',
                'title' => esc_html__('Should visitors be able to leave reviews?', 'listdom'),
                'copy' => esc_html__('Add ratings and reviews to build trust around your listings.', 'listdom'),
                'yes' => esc_html__('Recommend reviews for this directory.', 'listdom'),
                'no' => esc_html__('Keep reviews off for now.', 'listdom'),
            ],
            [
                'key' => 'booking',
                'blueprints' => $this->addon_blueprints('Booking'),
                'visual' => 'booking',
                'title' => esc_html__('Should visitors be able to contact, book, or request a quote?', 'listdom'),
                'copy' => esc_html__('Prepare clear conversion actions for visitors and listing providers.', 'listdom'),
                'yes' => esc_html__('Recommend booking or lead actions.', 'listdom'),
                'no' => esc_html__('Keep visitor actions off for now.', 'listdom'),
            ],
            [
                'key' => 'frontend_dashboard',
                'visual' => 'owners',
                'title' => esc_html__('Should listing owners manage listings from a frontend dashboard?', 'listdom'),
                'copy' => esc_html__('Give listing owners a simple place to submit and manage their listings without entering WordPress admin.', 'listdom'),
                'yes' => esc_html__('Create frontend submission and dashboard pages.', 'listdom'),
                'no' => esc_html__('Manage listings from the WordPress admin area.', 'listdom'),
            ],
            [
                'key' => 'memberships',
                'visual' => 'memberships',
                'title' => esc_html__('Would you like to offer memberships for listing owners?', 'listdom'),
                'copy' => esc_html__('Start with Listdom Memberships for recurring access, listing packages, and paid participation.', 'listdom'),
                'yes' => esc_html__('Recommend membership plans and listing packages.', 'listdom'),
                'no' => esc_html__('Keep memberships off for now.', 'listdom'),
                'conditional' => 'frontend_dashboard',
            ],
            [
                'key' => 'ai_visibility',
                'visual' => 'ai',
                'title' => esc_html__('Should search engines and AI tools understand your listings?', 'listdom'),
                'copy' => esc_html__('Enable structured data and AI-readable discovery settings for your directory.', 'listdom'),
                'yes' => esc_html__('Enable structured data and AI-readable discovery.', 'listdom'),
                'no' => esc_html__('Keep AI visibility settings off for now.', 'listdom'),
            ],
            [
                'key' => 'usage_data',
                'visual' => 'heart',
                'title' => esc_html__('Would you like to help improve Listdom?', 'listdom'),
                'copy' => esc_html__('Share anonymous usage data so we can improve compatibility and prioritize useful features.', 'listdom'),
                'yes' => esc_html__('Share anonymous usage data to help improve Listdom.', 'listdom'),
                'no' => esc_html__('Keep usage data private for now.', 'listdom'),
            ],
        ];
    }

    protected function addon_blueprints(string $addon): array
    {
        $blueprints = [];
        foreach (LSD_Blueprints::instance()->all() as $blueprint)
        {
            $definition = LSD_Blueprints::instance()->definition((string) $blueprint['id']);
            $addons = $definition['recommendations']['addons'] ?? [];
            if (is_array($addons) && in_array($addon, $addons, true)) $blueprints[] = (string) $blueprint['id'];
        }

        return $blueprints;
    }

    public function answers(): array
    {
        return [
            'location' => true,
            'pricing' => true,
            'work_hours' => true,
            'reviews' => true,
            'booking' => false,
            'frontend_dashboard' => false,
            'memberships' => false,
            'include_demo' => true,
            'ai_visibility' => true,
            'usage_data' => true,
        ];
    }

    public function review_groups(): array
    {
        return [
            'pages' => esc_html__('Pages to create', 'listdom'),
            'categories' => esc_html__('Categories to create', 'listdom'),
            'locations' => esc_html__('Locations to create', 'listdom'),
            'labels' => esc_html__('Labels to create', 'listdom'),
            'custom_fields' => esc_html__('Fields to create', 'listdom'),
            'search_forms' => esc_html__('Forms and search to configure', 'listdom'),
            'demo_listings' => esc_html__('Demo listings to create', 'listdom'),
        ];
    }

    public function review(string $markup, array $preview, array $options): string
    {
        return $this->include_html_file('menus/welcome/review.php', [
            'return_output' => true,
            'parameters' => [
                'preview' => $preview,
                'options' => $options,
            ],
        ]);
    }

    public function completion(string $markup, array $application, array $options): string
    {
        return $this->include_html_file('menus/welcome/completion.php', [
            'return_output' => true,
            'parameters' => [
                'application' => $application,
                'options' => $options,
            ],
        ]);
    }

    public function directory_url(array $application): string
    {
        foreach ($application['items'] ?? [] as $item)
        {
            if (empty($item['directory_page']) || empty($item['url'])) continue;

            return (string) $item['url'];
        }

        return '';
    }

    public function newsletter()
    {
        $nonce = isset($_POST['lsd_submit_newsletter_nonce'])
            ? sanitize_text_field(wp_unslash($_POST['lsd_submit_newsletter_nonce']))
            : '';

        if (!$nonce || !wp_verify_nonce($nonce, 'lsd_submit_newsletter'))
        {
            $this->response(['success' => 0, 'message' => esc_html__('Security check failed.', 'listdom')]);
        }

        if (!current_user_can('manage_options'))
        {
            $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to perform this action.', 'listdom')]);
        }

        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        if (!is_email($email)) $this->response(['success' => 0, 'message' => esc_html__('Please enter a valid email address.', 'listdom')]);

        if (!class_exists('Webilia\\WP\\EmailSubscription'))
        {
            $this->response(['success' => 0, 'message' => esc_html__('The subscription service is unavailable.', 'listdom')]);
        }

        try
        {
            $response = \Webilia\WP\EmailSubscription::subscribe([
                'basename' => LSD_BASENAME,
                'email' => $email,
            ]);
        }
        catch (\Throwable $error)
        {
            $this->response(['success' => 0, 'message' => esc_html__('The subscription service could not be reached.', 'listdom')]);
        }

        $this->response([
            'success' => isset($response['success']) ? (int) $response['success'] : 0,
            'message' => $response['message'] ?? esc_html__('Something went wrong', 'listdom')
        ]);
    }
}
