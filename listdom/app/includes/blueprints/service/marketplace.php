<?php

class LSD_Blueprints_Service_Marketplace extends LSD_Blueprints_Blueprint
{
    public function get_id(): string
    {
        return 'service_marketplace';
    }

    public function get_label(): string
    {
        return esc_html__('Service Marketplace', 'listdom');
    }

    public function get_description(): string
    {
        return esc_html__('A marketplace structure for providers, service categories, leads, and premium upgrade flows.', 'listdom');
    }

    protected function build_definition(array $options = []): array
    {
        return [
            'id' => $this->get_id(),
            'label' => $this->get_label(),
            'description' => $this->get_description(),
            'directory' => [
                'name' => esc_html__('Service Marketplace', 'listdom'),
                'summary' => esc_html__('Connect customers with providers using service categories, qualification fields, and lead-ready structure.', 'listdom'),
            ],
            'generate' => [
                'categories' => [
                    $this->category('Home Services'),
                    $this->category('Professional Services'),
                    $this->category('Beauty & Wellness'),
                    $this->category('Coaching & Training'),
                    $this->category('Tech Support'),
                ],
                'locations' => [
                    $this->location('Metro Area'),
                    $this->location('Central District'),
                ],
                'labels' => [
                    $this->label('Verified'),
                    $this->label('Top Rated'),
                    $this->label('Available This Week'),
                ],
                'custom_fields' => [
                    $this->custom_field('Service Area', 'text', ['slug' => 'service-area']),
                    $this->custom_field('Response Time', 'dropdown', ['slug' => 'response-time', 'values' => ['Within 1 hour', 'Same day', 'Within 24 hours']]),
                    $this->custom_field('Pricing Model', 'dropdown', ['slug' => 'pricing-model', 'values' => ['Hourly', 'Fixed Price', 'Custom Quote']]),
                    $this->custom_field('Remote Service Available', 'radio', ['slug' => 'remote-service', 'values' => ['Yes', 'No']]),
                ],
                'search_forms' => [
                    $this->search_form('Service Marketplace Search', [
                        ['key' => LSD_Base::TAX_CATEGORY, 'title' => __('Services', 'listdom'), 'method' => 'dropdown', 'hide_empty' => '1'],
                        ['key' => LSD_Base::TAX_LOCATION, 'title' => __('Service Areas', 'listdom'), 'method' => 'dropdown', 'hide_empty' => '1'],
                        ['key' => LSD_Base::TAX_LABEL, 'title' => __('Provider Labels', 'listdom'), 'method' => 'dropdown-multiple', 'hide_empty' => '1'],
                        ['key' => 'att-pricing-model', 'title' => __('Pricing Model', 'listdom'), 'method' => 'dropdown'],
                        ['key' => 'att-remote-service', 'title' => __('Remote Service', 'listdom'), 'method' => 'radio'],
                    ]),
                ],
                'shortcodes' => [
                    $this->directory_shortcode('Service Marketplace Search'),
                ],
                'pages' => [
                    $this->page(esc_html__('Listings Directory', 'listdom'), '', ['slug' => 'listings-directory', 'search_form_title' => 'Service Marketplace Search', 'shortcode_title' => esc_html__('List + Grid Directory Shortcode', 'listdom'), 'directory_page' => true]),
                    $this->page('Provider Dashboard', '[listdom-dashboard]', ['option_name' => 'lsd_settings', 'option_path' => 'submission_page']),
                    $this->page('Apply as a Provider', '[listdom-add-listing]', ['option_name' => 'lsd_settings', 'option_path' => 'add_listing_page']),
                ],
                'demo_listings' => [
                    $this->demo_listing('Sample Home Repair Pro', 'Home Services', ['location_name' => 'Metro Area', 'address' => 'Metro Area', 'website' => 'https://example.com/repair', 'attributes' => ['pricing-model' => 'Hourly', 'remote-service' => 'No']]),
                    $this->demo_listing('Sample Business Consultant', 'Professional Services', ['location_name' => 'Central District', 'address' => 'Central District', 'website' => 'https://example.com/consulting', 'attributes' => ['pricing-model' => 'Custom Quote', 'remote-service' => 'Yes']]),
                ],
            ],
            'recommendations' => [
                'skins' => ['List', 'Cover', 'Grid'],
                'addons' => ['Claims', 'Payments', 'Reviews', 'Booking', 'Memberships'],
                'addon_options' => ['Reviews' => 'reviews', 'Booking' => 'booking', 'Memberships' => 'memberships'],
                'monetization' => esc_html__('Recommended model: paid packages, featured providers, quote-request upsells, and booking or lead fees.', 'listdom'),
                'workflows' => ['claims' => 1, 'reviews' => 1, 'booking' => 1, 'payments' => 1],
                'schema' => esc_html__('Use service-oriented schema defaults and make pricing and response-time fields visible in search and listing views.', 'listdom'),
            ],
            'next_steps' => [
                esc_html__('Configure package or pricing settings if you want paid provider submissions.', 'listdom'),
                esc_html__('Review claim, review, and booking-related addons based on how providers should accept leads.', 'listdom'),
            ],
        ];
    }
}
