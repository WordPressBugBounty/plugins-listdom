<?php

class LSD_Blueprints_Business_Directory extends LSD_Blueprints_Blueprint
{
    public function get_id(): string
    {
        return 'business_directory';
    }

    public function get_label(): string
    {
        return esc_html__('Business Directory', 'listdom');
    }

    public function get_description(): string
    {
        return esc_html__('A classic local directory for businesses, shops, venues, and public listings.', 'listdom');
    }

    protected function build_definition(array $options = []): array
    {
        return [
            'id' => $this->get_id(),
            'label' => $this->get_label(),
            'description' => $this->get_description(),
            'directory' => [
                'name' => esc_html__('Business Directory', 'listdom'),
                'summary' => esc_html__('Showcase local businesses with categories, locations, practical filters, and sample listings.', 'listdom'),
            ],
            'generate' => [
                'categories' => [
                    $this->category('Restaurant'),
                    $this->category('Hotel'),
                    $this->category('Medical Clinic'),
                    $this->category('Beauty Salon'),
                    $this->category('Auto Services'),
                ],
                'labels' => [
                    $this->label('Featured'),
                    $this->label('Open Now'),
                ],
                'custom_fields' => [
                    $this->custom_field('Price Range', 'dropdown', ['slug' => 'price-range', 'values' => ['$', '$$', '$$$']]),
                    $this->custom_field('Amenities', 'checkbox', ['slug' => 'amenities', 'values' => ['Parking', 'WiFi', 'Delivery', 'Family Friendly']]),
                    $this->custom_field('Reservation Required', 'radio', ['slug' => 'reservation-required', 'values' => ['Yes', 'No']]),
                ],
                'search_forms' => [
                    $this->search_form('Business Directory Search', [
                        ['key' => LSD_Base::TAX_CATEGORY, 'title' => 'Categories', 'method' => 'dropdown', 'hide_empty' => '1'],
                        ['key' => LSD_Base::TAX_LOCATION, 'title' => 'Locations', 'method' => 'dropdown', 'hide_empty' => '1'],
                        ['key' => LSD_Base::TAX_LABEL, 'title' => 'Labels', 'method' => 'dropdown', 'hide_empty' => '1'],
                        ['key' => 'att-price-range', 'title' => 'Price Range', 'method' => 'dropdown'],
                    ]),
                ],
                'pages' => [
                    $this->page('Manage Listings', '[listdom-dashboard]', ['option_name' => 'lsd_settings', 'option_path' => 'submission_page']),
                    $this->page('Submit Listing', '[listdom-add-listing]', ['option_name' => 'lsd_settings', 'option_path' => 'add_listing_page']),
                ],
                'demo_listings' => [
                    $this->demo_listing('Sample Neighborhood Bistro', 'Restaurant', ['address' => 'Downtown', 'website' => 'https://example.com/bistro']),
                    $this->demo_listing('Sample City Hotel', 'Hotel', ['address' => 'Central District', 'website' => 'https://example.com/hotel']),
                ],
            ],
            'recommendations' => [
                'skins' => ['List', 'Grid', 'Half Map'],
                'addons' => ['Reviews', 'Claims', 'Advanced Map'],
                'monetization' => esc_html__('Recommended model: featured listings, paid claims, and premium category placement.', 'listdom'),
                'workflows' => ['claims' => 1, 'reviews' => 1, 'booking' => 0, 'payments' => 1],
                'schema' => esc_html__('Use Organization or LocalBusiness-oriented schema defaults for directory entries.', 'listdom'),
            ],
            'next_steps' => [
                esc_html__('Create a listing shortcode for your preferred skin and connect it to the new search form.', 'listdom'),
                esc_html__('Review price and claim settings if you want paid upgrades or ownership claims.', 'listdom'),
            ],
        ];
    }
}
