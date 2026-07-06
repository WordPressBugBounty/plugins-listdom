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
                        ['key' => LSD_Base::TAX_CATEGORY, 'title' => 'Services', 'method' => 'dropdown', 'hide_empty' => '1'],
                        ['key' => LSD_Base::TAX_LABEL, 'title' => 'Provider Labels', 'method' => 'dropdown-multiple', 'hide_empty' => '1'],
                        ['key' => 'att-pricing-model', 'title' => 'Pricing Model', 'method' => 'dropdown'],
                        ['key' => 'att-remote-service', 'title' => 'Remote Service', 'method' => 'radio'],
                    ]),
                ],
                'pages' => [
                    $this->page('Provider Dashboard', '[listdom-dashboard]', ['option_name' => 'lsd_settings', 'option_path' => 'submission_page']),
                    $this->page('Apply as a Provider', '[listdom-add-listing]', ['option_name' => 'lsd_settings', 'option_path' => 'add_listing_page']),
                ],
                'demo_listings' => [
                    $this->demo_listing('Sample Home Repair Pro', 'Home Services', ['address' => 'Metro Area', 'website' => 'https://example.com/repair']),
                    $this->demo_listing('Sample Business Consultant', 'Professional Services', ['address' => 'Remote', 'website' => 'https://example.com/consulting']),
                ],
            ],
            'recommendations' => [
                'skins' => ['List', 'Cover', 'Grid'],
                'addons' => ['Claims', 'Payments', 'Reviews', 'Booking'],
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
