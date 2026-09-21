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
                'locations' => [
                    $this->location('Downtown'),
                    $this->location('Central District'),
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
                        ['key' => LSD_Base::TAX_CATEGORY, 'title' => __('Categories', 'listdom'), 'method' => 'dropdown', 'hide_empty' => '1'],
                        ['key' => LSD_Base::TAX_LOCATION, 'title' => __('Locations', 'listdom'), 'method' => 'dropdown', 'hide_empty' => '1'],
                        ['key' => LSD_Base::TAX_LABEL, 'title' => __('Labels', 'listdom'), 'method' => 'dropdown', 'hide_empty' => '1'],
                        ['key' => 'att-price-range', 'title' => __('Price Range', 'listdom'), 'method' => 'dropdown'],
                    ]),
                ],
                'shortcodes' => [
                    $this->directory_shortcode('Business Directory Search'),
                ],
                'pages' => [
                    $this->page(esc_html__('Listings Directory', 'listdom'), '', ['slug' => 'listings-directory', 'search_form_title' => 'Business Directory Search', 'shortcode_title' => esc_html__('List + Grid Directory Shortcode', 'listdom'), 'directory_page' => true]),
                    $this->page('Manage Listings', '[listdom-dashboard]', ['option_name' => 'lsd_settings', 'option_path' => 'submission_page']),
                    $this->page('Submit Listing', '[listdom-add-listing]', ['option_name' => 'lsd_settings', 'option_path' => 'add_listing_page']),
                ],
                'demo_listings' => [
                    $this->demo_listing('Sample Neighborhood Bistro', 'Restaurant', ['location_name' => 'Downtown', 'address' => 'Downtown', 'website' => 'https://example.com/bistro', 'attributes' => ['price-range' => '$$']]),
                    $this->demo_listing('Sample City Hotel', 'Hotel', ['location_name' => 'Central District', 'address' => 'Central District', 'website' => 'https://example.com/hotel', 'attributes' => ['price-range' => '$$$']]),
                ],
            ],
            'recommendations' => [
                'skins' => ['List', 'Grid', 'Half Map'],
                'addons' => ['Reviews', 'Claims', 'Advanced Map'],
                'addon_options' => ['Reviews' => 'reviews', 'Advanced Map' => 'location'],
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
