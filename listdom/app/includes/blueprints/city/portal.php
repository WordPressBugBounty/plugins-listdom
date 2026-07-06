<?php

class LSD_Blueprints_City_Portal extends LSD_Blueprints_Blueprint
{
    public function get_id(): string
    {
        return 'city_portal';
    }

    public function get_label(): string
    {
        return esc_html__('City Portal', 'listdom');
    }

    public function get_description(): string
    {
        return esc_html__('A broader portal for neighborhoods, attractions, public services, and city-focused discovery.', 'listdom');
    }

    protected function build_definition(array $options = []): array
    {
        return [
            'id' => $this->get_id(),
            'label' => $this->get_label(),
            'description' => $this->get_description(),
            'directory' => [
                'name' => esc_html__('City Portal', 'listdom'),
                'summary' => esc_html__('Organize a city-scale portal with neighborhoods, attractions, services, and curated labels.', 'listdom'),
            ],
            'generate' => [
                'categories' => [
                    $this->category('Attractions'),
                    $this->category('Government Services'),
                    $this->category('Restaurants'),
                    $this->category('Events Venues'),
                    $this->category('Community Resources'),
                ],
                'locations' => [
                    $this->location('Downtown'),
                    $this->location('Old Town'),
                    $this->location('Waterfront'),
                    $this->location('University District'),
                ],
                'labels' => [
                    $this->label('Family Friendly'),
                    $this->label('Tourist Favorite'),
                    $this->label('Open Late'),
                ],
                'custom_fields' => [
                    $this->custom_field('Neighborhood', 'text', ['slug' => 'neighborhood']),
                    $this->custom_field('Accessibility', 'checkbox', ['slug' => 'accessibility', 'values' => ['Wheelchair Access', 'Public Transit Nearby', 'Parking']]),
                    $this->custom_field('Best For', 'dropdown', ['slug' => 'best-for', 'values' => ['Families', 'Tourists', 'Students', 'Residents']]),
                ],
                'search_forms' => [
                    $this->search_form('City Portal Search', [
                        ['key' => LSD_Base::TAX_CATEGORY, 'title' => 'Categories', 'method' => 'dropdown', 'hide_empty' => '1'],
                        ['key' => LSD_Base::TAX_LOCATION, 'title' => 'Neighborhoods', 'method' => 'dropdown', 'hide_empty' => '1'],
                        ['key' => LSD_Base::TAX_LABEL, 'title' => 'Labels', 'method' => 'dropdown-multiple', 'hide_empty' => '1'],
                        ['key' => 'att-best-for', 'title' => 'Best For', 'method' => 'dropdown'],
                    ]),
                ],
                'pages' => [
                    $this->page('City Portal Dashboard', '[listdom-dashboard]', ['option_name' => 'lsd_settings', 'option_path' => 'submission_page']),
                    $this->page('Suggest a Place', '[listdom-add-listing]', ['option_name' => 'lsd_settings', 'option_path' => 'add_listing_page']),
                ],
                'demo_listings' => [
                    $this->demo_listing('Sample Riverside Museum', 'Attractions', ['address' => 'Waterfront', 'website' => 'https://example.com/museum']),
                    $this->demo_listing('Sample Community Help Desk', 'Government Services', ['address' => 'Downtown', 'website' => 'https://example.com/helpdesk']),
                ],
            ],
            'recommendations' => [
                'skins' => ['Grid', 'Masonry', 'Single Map'],
                'addons' => ['Advanced Map', 'Reviews'],
                'monetization' => esc_html__('Recommended model: sponsored local partners and featured destination listings.', 'listdom'),
                'workflows' => ['claims' => 0, 'reviews' => 1, 'booking' => 0, 'payments' => 0],
                'schema' => esc_html__('Use place-focused schema defaults and enrich important public listings with accessibility data.', 'listdom'),
            ],
            'next_steps' => [
                esc_html__('Create one or more directory shortcodes for homepage, map, and neighborhood landing pages.', 'listdom'),
                esc_html__('Review location terms and add the real neighborhoods or districts for your city.', 'listdom'),
            ],
        ];
    }
}
