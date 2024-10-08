<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Dummy Data Class.
 *
 * @class LSD_Dummy
 * @version    1.0.0
 */
class LSD_Dummy extends LSD_Base
{
    public function init()
    {
        add_action('lsd_admin_dashboard_tabs', [$this, 'tab']);
        add_action('lsd_admin_dashboard_contents', [$this, 'content']);

        // Activate
        add_action('wp_ajax_lsd_dummy', [$this, 'dummy']);
    }

    public function tab($tab)
    {
        echo '<a class="nav-tab ' . ($tab == 'dummy-data' ? 'nav-tab-active' : '') . '" href="' . esc_url(admin_url('admin.php?page=listdom&tab=dummy-data')) . '">' . esc_html__('Dummy Data', 'listdom') . '</a>';
    }

    public function content($tab)
    {
        // It's not Activation Tab
        if ($tab !== 'dummy-data') return;

        $this->include_html_file('menus/dashboard/tabs/dummy-data.php');
    }

    public function dummy()
    {
        // Include Needed Library
        if (!function_exists('post_exists')) require_once ABSPATH . 'wp-admin/includes/post.php';

        // Request
        $dummy = $_POST['lsd']['dummy'] ?? [];

        $import_listings = isset($dummy['listings']) && $dummy['listings'];
        $import_categories = isset($dummy['categories']) && $dummy['categories'];
        $import_locations = isset($dummy['locations']) && $dummy['locations'];
        $import_tags = isset($dummy['tags']) && $dummy['tags'];
        $import_features = isset($dummy['features']) && $dummy['features'];
        $import_labels = isset($dummy['labels']) && $dummy['labels'];
        $import_attributes = isset($dummy['attributes']) && $dummy['attributes'];
        $import_shortcodes = isset($dummy['shortcodes']) && $dummy['shortcodes'];
        $import_frontend_dashboard = isset($dummy['frontend_dashboard']) && $dummy['frontend_dashboard'];

        if ($import_categories)
        {
            // Categories
            foreach ([
                 ['label' => esc_html__('Beauty Salon', 'listdom'), 'color' => '#dd3333', 'icon' => 'fa fa-user'],
                 ['label' => esc_html__('Cafe', 'listdom'), 'color' => '#81d742', 'icon' => 'fa fa-coffee'],
                 ['label' => esc_html__('Hospital', 'listdom'), 'color' => '#000000', 'icon' => 'fa fa-heartbeat'],
                 ['label' => esc_html__('Hotel', 'listdom'), 'color' => '#1d7ed3', 'icon' => 'fa fa-bed'],
                 ['label' => esc_html__('Restaurant', 'listdom'), 'color' => '#8224e3', 'icon' => 'fas fa-utensil-spoon'],
                 ['label' => esc_html__('Super Market', 'listdom'), 'color' => '#dd9933', 'icon' => 'fa fa-shopping-basket'],
             ] as $category)
            {
                // Don't add it again if exists
                if (term_exists($category['label'], LSD_Base::TAX_CATEGORY)) continue;

                // Term
                $result = wp_insert_term(
                    $category['label'],
                    LSD_Base::TAX_CATEGORY
                );

                // Icon
                update_term_meta($result['term_id'], 'lsd_icon', $category['icon']);

                // Color
                update_term_meta($result['term_id'], 'lsd_color', $category['color']);
            }
        }

        if ($import_locations)
        {
            // Locations
            foreach ([
                 ['label' => esc_html__('Florida', 'listdom')],
                 ['label' => esc_html__('London', 'listdom')],
                 ['label' => esc_html__('Los Angeles', 'listdom')],
                 ['label' => esc_html__('New York', 'listdom')],
                 ['label' => esc_html__('Paris', 'listdom')],
                 ['label' => esc_html__('Rome', 'listdom')],
                 ['label' => esc_html__('Madrid', 'listdom')],
             ] as $location)
            {
                // Don't add it again if exists
                if (term_exists($location['label'], LSD_Base::TAX_LOCATION)) continue;

                // Term
                wp_insert_term(
                    $location['label'],
                    LSD_Base::TAX_LOCATION
                );
            }
        }

        if ($import_tags)
        {
            // Tags
            foreach ([
                 ['label' => esc_html__('Discount', 'listdom')],
                 ['label' => esc_html__('Easy Access', 'listdom')],
                 ['label' => esc_html__('Parking Friendly', 'listdom')],
                 ['label' => esc_html__('Recommended', 'listdom')],
             ] as $tag)
            {
                // Don't add it again if exists
                if (term_exists($tag['label'], LSD_Base::TAX_TAG)) continue;

                // Term
                wp_insert_term(
                    $tag['label'],
                    LSD_Base::TAX_TAG
                );
            }
        }

        if ($import_features)
        {
            // Features
            foreach ([
                 ['label' => esc_html__('Free Wifi', 'listdom'), 'icon' => 'fas fa-wifi'],
                 ['label' => esc_html__('Live Music', 'listdom'), 'icon' => 'fa fa-music'],
                 ['label' => esc_html__('Valet', 'listdom'), 'icon' => 'fa fa-car'],
             ] as $feature)
            {
                // Don't add it again if exists
                if (term_exists($feature['label'], LSD_Base::TAX_FEATURE)) continue;

                // Term
                $result = wp_insert_term(
                    $feature['label'],
                    LSD_Base::TAX_FEATURE
                );

                // Icon
                update_term_meta($result['term_id'], 'lsd_icon', $feature['icon']);
            }
        }

        if ($import_labels)
        {
            // Labels
            foreach ([
                 ['label' => esc_html__('Exclusive', 'listdom'), 'color' => '#1d7ed3'],
                 ['label' => esc_html__('Hot Offer', 'listdom'), 'color' => '#dd3333'],
                 ['label' => esc_html__('Must See', 'listdom'), 'color' => '#dd9933'],
                 ['label' => esc_html__('Recommended', 'listdom'), 'color' => '#000000'],
             ] as $label)
            {
                // Don't add it again if exists
                if (term_exists($label['label'], LSD_Base::TAX_LABEL)) continue;

                // Term
                $result = wp_insert_term(
                    $label['label'],
                    LSD_Base::TAX_LABEL
                );

                // Color
                update_term_meta($result['term_id'], 'lsd_color', $label['color']);
            }
        }

        if ($this->isPro() && $import_attributes)
        {
            // Attributes
            foreach ([
                 ['label' => esc_html__('Discount', 'listdom'), 'type' => 'text', 'icon' => 'far fa-money-bill-alt', 'index' => '1.00', 'values' => ''],
                 ['label' => esc_html__('Pets Allowed', 'listdom'), 'type' => 'dropdown', 'icon' => 'far fa-heart', 'index' => '2.00', 'values' => 'Yes,No,Only Cats,Only Dogs'],
                 ['label' => esc_html__('Parking Capacity', 'listdom'), 'type' => 'number', 'icon' => 'fa fa-car', 'index' => '3.00', 'values' => ''],
             ] as $attribute)
            {
                // Don't add it again if exists
                if (term_exists($attribute['label'], LSD_Base::TAX_ATTRIBUTE)) continue;

                // Term
                $result = wp_insert_term(
                    $attribute['label'],
                    LSD_Base::TAX_ATTRIBUTE
                );

                // Meta Data
                update_term_meta($result['term_id'], 'lsd_field_type', $attribute['type']);
                update_term_meta($result['term_id'], 'lsd_icon', $attribute['icon']);
                update_term_meta($result['term_id'], 'lsd_index', $attribute['index']);
                update_term_meta($result['term_id'], 'lsd_values', $attribute['values']);
                update_term_meta($result['term_id'], 'lsd_all_categories', 1);
                update_term_meta($result['term_id'], 'lsd_categories', []);
            }
        }

        if ($import_shortcodes)
        {
            // Searches
            $searches = [
                [
                    'title' => 'Default Search',
                    'meta' => [
                        'lsd_form' => [
                            'style' => 'default',
                            'page' => '',
                            'shortcode' => '',
                        ],
                        'lsd_fields' => [
                            1 => [
                                'type' => 'row',
                                'filters' => [
                                    's' => [
                                        'key' => 's',
                                        'title' => 'Text Search',
                                        'method' => 'text-input',
                                        'placeholder' => '',
                                        'max_placeholder' => '',
                                        'default_value' => '',
                                        'max_default_value' => '',
                                        'min' => '0',
                                        'max' => '100',
                                        'increment' => '10',
                                        'th_separator' => '1',
                                    ],
                                ],
                                'buttons' => '1',
                            ],
                            2 => [
                                'type' => 'more_options',
                            ],
                            3 => [
                                'type' => 'row',
                                'filters' => [
                                    'listdom-category' => [
                                        'key' => 'listdom-category',
                                        'title' => 'Categories',
                                        'method' => 'dropdown',
                                        'hide_empty' => '1',
                                        'placeholder' => '',
                                        'max_placeholder' => '',
                                        'default_value' => '',
                                        'max_default_value' => '',
                                        'min' => '0',
                                        'max' => '100',
                                        'increment' => '10',
                                        'th_separator' => '1',
                                    ],
                                    'listdom-location' => [
                                        'key' => 'listdom-location',
                                        'title' => 'Locations',
                                        'method' => 'dropdown',
                                        'hide_empty' => '1',
                                        'placeholder' => '',
                                        'max_placeholder' => '',
                                        'default_value' => '',
                                        'max_default_value' => '',
                                        'min' => '0',
                                        'max' => '100',
                                        'increment' => '10',
                                        'th_separator' => '1',
                                    ],
                                    'listdom-label' => [
                                        'key' => 'listdom-label',
                                        'title' => 'Labels',
                                        'method' => 'dropdown',
                                        'hide_empty' => '1',
                                        'placeholder' => '',
                                        'max_placeholder' => '',
                                        'default_value' => '',
                                        'max_default_value' => '',
                                        'min' => '0',
                                        'max' => '100',
                                        'increment' => '10',
                                        'th_separator' => '1',
                                    ],
                                ],
                                'buttons' => '0',
                            ],
                        ],
                    ],
                ],
            ];

            $first_search_id = 0;
            foreach ($searches as $search)
            {
                // Search Exists
                if (post_exists($search['title'], 'listdom'))
                {
                    $search_post = LSD_Base::get_post_by_title($search['title'], LSD_Base::PTYPE_SEARCH);

                    $is_trashed = LSD_Base::is_post($search_post->ID, 'trash');
                    if ($is_trashed)
                    {
                        LSD_Base::untrash_post($search_post->ID);
                        LSD_Base::update_post_status($search_post->ID);
                    }

                    if (!$first_search_id) $first_search_id = $search_post->ID;
                    continue;
                }

                $post_id = wp_insert_post([
                    'post_title' => $search['title'],
                    'post_content' => 'listdom',
                    'post_type' => LSD_Base::PTYPE_SEARCH,
                    'post_status' => 'publish',
                ]);

                if (!is_wp_error($post_id))
                {
                    if (!$first_search_id) $first_search_id = $post_id;
                    foreach ($search['meta'] as $key => $value) update_post_meta($post_id, $key, $value);
                }
            }

            // Shortcodes
            $shortcodes = [
                [
                    'title' => 'Side By Side',
                    'meta' => [
                        'lsd_skin' => 'side',
                        'lsd_display' => [
                            'skin' => 'side',
                            'list' => [
                                'style' => 'style1',
                                'map_position' => 'top',
                                'clustering' => 1,
                                'clustering_images' => 'img/cluster2/m',
                                'mapobject_onclick' => 'infowindow',
                                'mapsearch' => 1,
                                'maplimit' => 300,
                                'limit' => 12,
                                'load_more' => 1,
                                'display_labels' => 1,
                                'display_share_buttons' => 1,
                            ],
                        ],
                        'lsd_search' => [
                            'shortcode' => (string) $first_search_id,
                            'position' => 'top',
                        ],
                        'lsd_filter' => [],
                        'lsd_mapcontrols' => LSD_Options::defaults('mapcontrols'),
                        'lsd_sorts' => LSD_Options::defaults('sorts'),
                    ],
                ],
                [
                    'title' => 'List',
                    'meta' => [
                        'lsd_skin' => 'list',
                        'lsd_display' => [
                            'skin' => 'list',
                            'list' => [
                                'style' => 'style1',
                                'map_position' => 'top',
                                'clustering' => 1,
                                'clustering_images' => 'img/cluster2/m',
                                'mapobject_onclick' => 'infowindow',
                                'mapsearch' => 1,
                                'maplimit' => 300,
                                'limit' => 12,
                                'load_more' => 1,
                                'display_labels' => 1,
                                'display_share_buttons' => 1,
                            ],
                        ],
                        'lsd_search' => [
                            'shortcode' => (string) $first_search_id,
                            'position' => 'top',
                        ],
                        'lsd_filter' => [],
                        'lsd_mapcontrols' => LSD_Options::defaults('mapcontrols'),
                        'lsd_sorts' => LSD_Options::defaults('sorts'),
                    ],
                ],
                [
                    'title' => 'Grid',
                    'meta' => [
                        'lsd_skin' => 'grid',
                        'lsd_display' => [
                            'skin' => 'grid',
                            'grid' => [
                                'style' => 'style1',
                                'map_position' => 'top',
                                'clustering' => 1,
                                'clustering_images' => 'img/cluster2/m',
                                'mapobject_onclick' => 'infowindow',
                                'mapsearch' => 1,
                                'maplimit' => 300,
                                'columns' => 3,
                                'limit' => 12,
                                'load_more' => 1,
                                'display_labels' => 1,
                                'display_share_buttons' => 1,
                            ],
                        ],
                        'lsd_search' => [
                            'shortcode' => (string) $first_search_id,
                            'position' => 'top',
                        ],
                        'lsd_filter' => [],
                        'lsd_mapcontrols' => LSD_Options::defaults('mapcontrols'),
                        'lsd_sorts' => LSD_Options::defaults('sorts'),
                    ],
                ],
                [
                    'title' => 'Single Map',
                    'meta' => [
                        'lsd_skin' => 'singlemap',
                        'lsd_display' => [
                            'skin' => 'singlemap',
                            'singlemap' => [
                                'clustering' => 1,
                                'clustering_images' => 'img/cluster2/m',
                                'mapobject_onclick' => 'infowindow',
                                'mapsearch' => 1,
                                'maplimit' => 300,
                            ],
                        ],
                        'lsd_search' => [
                            'shortcode' => (string) $first_search_id,
                            'position' => 'top',
                        ],
                        'lsd_filter' => [],
                        'lsd_mapcontrols' => LSD_Options::defaults('mapcontrols'),
                        'lsd_sorts' => LSD_Options::defaults('sorts'),
                    ],
                ],
                [
                    'title' => 'List + Grid',
                    'meta' => [
                        'lsd_skin' => 'listgrid',
                        'lsd_display' => [
                            'skin' => 'listgrid',
                            'listgrid' => [
                                'style' => 'style1',
                                'map_position' => 'top',
                                'clustering' => 1,
                                'clustering_images' => 'img/cluster2/m',
                                'mapobject_onclick' => 'infowindow',
                                'mapsearch' => 1,
                                'maplimit' => 300,
                                'default_view' => 'grid',
                                'columns' => 3,
                                'limit' => 12,
                                'load_more' => 1,
                                'display_labels' => 1,
                                'display_share_buttons' => 1,
                            ],
                        ],
                        'lsd_search' => [
                            'shortcode' => (string) $first_search_id,
                            'position' => 'top',
                        ],
                        'lsd_filter' => [],
                        'lsd_mapcontrols' => LSD_Options::defaults('mapcontrols'),
                        'lsd_sorts' => LSD_Options::defaults('sorts'),
                    ],
                ],
                [
                    'title' => 'Halfmap',
                    'meta' => [
                        'lsd_skin' => 'halfmap',
                        'lsd_display' => [
                            'skin' => 'halfmap',
                            'halfmap' => [
                                'style' => 'style1',
                                'map_position' => 'left',
                                'clustering' => 1,
                                'clustering_images' => 'img/cluster2/m',
                                'mapobject_onclick' => 'infowindow',
                                'mapsearch' => 1,
                                'maplimit' => 300,
                                'map_height' => 500,
                                'columns' => 2,
                                'limit' => 12,
                                'load_more' => 1,
                                'display_labels' => 1,
                                'display_share_buttons' => 1,
                            ],
                        ],
                        'lsd_search' => [
                            'shortcode' => (string) $first_search_id,
                            'position' => 'top',
                        ],
                        'lsd_filter' => [],
                        'lsd_mapcontrols' => LSD_Options::defaults('mapcontrols'),
                        'lsd_sorts' => LSD_Options::defaults('sorts'),
                    ],
                ],
                [
                    'title' => 'Table',
                    'meta' => [
                        'lsd_skin' => 'table',
                        'lsd_display' => [
                            'skin' => 'table',
                            'table' => [
                                'style' => 'style1',
                                'limit' => 12,
                                'load_more' => 1,
                                'columns' => [
                                    'title' => ['label' => esc_html__('Listing Title', 'listdom'), 'enabled' => 1],
                                    'address' => ['label' => esc_html__('Address', 'listdom'), 'enabled' => 1],
                                    'price' => ['label' => esc_html__('Price', 'listdom'), 'enabled' => 1],
                                    'availability' => ['label' => esc_html__('Work Hours', 'listdom'), 'enabled' => 1],
                                    'phone' => ['label' => esc_html__('Phone', 'listdom'), 'enabled' => 1],
                                ],
                            ],
                        ],
                        'lsd_search' => [
                            'shortcode' => (string) $first_search_id,
                            'position' => 'top',
                        ],
                        'lsd_filter' => [],
                        'lsd_mapcontrols' => LSD_Options::defaults('mapcontrols'),
                        'lsd_sorts' => LSD_Options::defaults('sorts'),
                    ],
                ],
                [
                    'title' => 'Masonry',
                    'meta' => [
                        'lsd_skin' => 'masonry',
                        'lsd_display' => [
                            'skin' => 'masonry',
                            'masonry' => [
                                'style' => 'style1',
                                'filter_by' => 'listdom-category',
                                'columns' => 3,
                                'limit' => 12,
                                'display_labels' => 1,
                                'display_share_buttons' => 1,
                            ],
                        ],
                        'lsd_search' => [
                            'shortcode' => (string) $first_search_id,
                            'position' => 'top',
                        ],
                        'lsd_filter' => [],
                        'lsd_mapcontrols' => LSD_Options::defaults('mapcontrols'),
                        'lsd_sorts' => LSD_Options::defaults('sorts'),
                    ],
                ],
                [
                    'title' => 'Carousel',
                    'meta' => [
                        'lsd_skin' => 'carousel',
                        'lsd_display' => [
                            'skin' => 'carousel',
                            'carousel' => [
                                'style' => 'style1',
                                'columns' => 3,
                                'limit' => 8,
                                'display_labels' => 1,
                                'display_share_buttons' => 1,
                            ],
                        ],
                        'lsd_search' => [
                            'shortcode' => '',
                            'position' => 'top',
                        ],
                        'lsd_filter' => [],
                        'lsd_mapcontrols' => LSD_Options::defaults('mapcontrols'),
                        'lsd_sorts' => LSD_Options::defaults('sorts'),
                    ],
                ],
                [
                    'title' => 'Slider',
                    'meta' => [
                        'lsd_skin' => 'slider',
                        'lsd_display' => [
                            'skin' => 'slider',
                            'slider' => [
                                'style' => 'style1',
                                'limit' => 8,
                            ],
                        ],
                        'lsd_search' => [
                            'shortcode' => '',
                            'position' => 'top',
                        ],
                        'lsd_filter' => [],
                        'lsd_mapcontrols' => LSD_Options::defaults('mapcontrols'),
                        'lsd_sorts' => LSD_Options::defaults('sorts'),
                    ],
                ],
                [
                    'title' => 'Cover',
                    'meta' => [
                        'lsd_skin' => 'cover',
                        'lsd_display' => [
                            'skin' => 'cover',
                            'cover' => [
                                'style' => 'style1',
                                'listing' => null,
                            ],
                        ],
                        'lsd_form' => [
                            'style' => 'default',
                        ],
                        'lsd_search' => [
                            'shortcode' => '',
                            'position' => 'top',
                        ],
                        'lsd_filter' => [],
                        'lsd_mapcontrols' => LSD_Options::defaults('mapcontrols'),
                        'lsd_sorts' => LSD_Options::defaults('sorts'),
                    ],
                ],
            ];

            foreach ($shortcodes as $shortcode)
            {
                // Get or create shortcode
                $post_id = $this->shortcode($shortcode['title'], $shortcode['meta']);

                // Prepare associated page data
                $page_title = sprintf(esc_html__('All Listings in %s Skin', 'listdom'), $shortcode['title']);
                $page_shortcode = '[listdom id="' . $post_id . '"]';

                // Get or create page
                $this->page($page_title, $page_shortcode);
            }

        }

        // Frontend Dashboard
        if ($this->isPro() && $import_frontend_dashboard && !post_exists('Manage Listings', '[listdom-dashboard]'))
        {
            wp_insert_post([
                'post_title' => 'Manage Listings',
                'post_content' => '[listdom-dashboard]',
                'post_type' => 'page',
                'post_status' => 'publish',
            ]);
        }

        if ($import_listings)
        {
            $listings = [
                [
                    'post_title' => 'Sample Bank',
                    'post_content' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                    'post_author' => 1,
                    'post_date' => '2023-01-01',
                    'taxonomies' => [
                        'listing_category' => [
                            ['name' => 'Bank'],
                        ],
                    ],
                    'image' => 'https://demo.webilia.com/listdom/wp-content/uploads/sites/2/2020/09/bank.jpg',
                    'meta' => [
                        'lsd_object_type' => 'marker',
                        'lsd_zoomlevel' => 6,
                        'lsd_latitude' => '40.712776',
                        'lsd_longitude' => '-74.005974',
                        'lsd_address' => 'New York, NY, USA',
                        'lsd_price' => 100,
                        'lsd_currency' => 'USD',
                        'lsd_email' => 'sample@bank.com',
                        'lsd_phone' => '+1234567890',
                        'lsd_website' => 'https://samplebank.com',
                        'lsd_remark' => 'A sample bank listing.',
                        'lsd_displ' => [
                            'style' => 'style2',
                            'elements_global' => 1,
                            'elements' => [
                                'labels' => ['enabled' => 1],
                                'image' => ['enabled' => 1],
                                'gallery' => ['enabled' => 1],
                                'title' => ['enabled' => 1],
                                'categories' => ['enabled' => 1],
                                'price' => ['enabled' => 1],
                                'tags' => ['enabled' => 1],
                                'content' => ['enabled' => 1],
                                'embed' => ['enabled' => 0],
                                'attributes' => ['enabled' => 1],
                                'features' => ['enabled' => 1],
                                'contact' => ['enabled' => 1],
                                'remark' => ['enabled' => 1],
                                'locations' => ['enabled' => 1],
                                'address' => ['enabled' => 1],
                                'map' => ['enabled' => 1],
                                'availability' => ['enabled' => 1],
                                'owner' => ['enabled' => 1],
                                'share' => ['enabled' => 1],
                                'abuse' => ['enabled' => 0],
                                'ads' => ['enabled' => 0],
                                'booking' => ['enabled' => 0],
                                'acf' => ['enabled' => 1],
                                'auction' => ['enabled' => 0],
                                'franchise' => ['enabled' => 0],
                                'application' => ['enabled' => 0],
                                'discussion' => ['enabled' => 1],
                                'stats' => ['enabled' => 0],
                                'video' => ['enabled' => 0],
                                'team' => ['enabled' => 0],
                            ],
                        ],
                    ],
                    'gallery' => [
                        'https://demo.webilia.com/listdom/wp-content/uploads/sites/2/2020/09/bank-interior.jpg',
                        'https://demo.webilia.com/listdom/wp-content/uploads/sites/2/2020/09/bank-entrance.jpg',
                    ],
                ],
                [
                    'post_title' => 'Sample Business',
                    'post_content' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                    'post_author' => 1,
                    'taxonomies' => [
                        'listing_category' => [
                            ['name' => 'Business'],
                            ['name' => 'Casino'],
                            ['name' => 'Clinic'],
                        ],
                    ],
                    'image' => 'https://demo.webilia.com/listdom/wp-content/uploads/sites/2/2020/10/business-2.jpg',
                    'meta' => [
                        'lsd_object_type' => 'marker',
                        'lsd_zoomlevel' => 5,
                        'lsd_latitude' => '34.052235',
                        'lsd_longitude' => '-118.243683',
                        'lsd_address' => 'Los Angeles, CA, USA',
                        'lsd_price' => 200,
                        'lsd_currency' => 'USD',
                        'lsd_email' => 'contact@samplebusiness.com',
                        'lsd_phone' => '+1987654321',
                        'lsd_website' => 'https://samplebusiness.com',
                        'lsd_remark' => 'A sample business listing.',
                        'lsd_displ' => [
                            'style' => 'style3',
                            'elements_global' => 1,
                            'elements' => [
                                'labels' => ['enabled' => 1],
                                'image' => ['enabled' => 1],
                                'gallery' => ['enabled' => 1],
                                'title' => ['enabled' => 1],
                                'categories' => ['enabled' => 1],
                                'price' => ['enabled' => 1],
                                'tags' => ['enabled' => 1],
                                'content' => ['enabled' => 1],
                                'embed' => ['enabled' => 0],
                                'attributes' => ['enabled' => 0],
                                'features' => ['enabled' => 1],
                                'contact' => ['enabled' => 1],
                                'remark' => ['enabled' => 1],
                                'locations' => ['enabled' => 1],
                                'address' => ['enabled' => 1],
                                'map' => ['enabled' => 1],
                                'availability' => ['enabled' => 1],
                                'owner' => ['enabled' => 1],
                                'share' => ['enabled' => 1],
                                'abuse' => ['enabled' => 0],
                                'ads' => ['enabled' => 0],
                                'booking' => ['enabled' => 0],
                                'acf' => ['enabled' => 0],
                                'auction' => ['enabled' => 0],
                                'franchise' => ['enabled' => 0],
                                'application' => ['enabled' => 0],
                                'discussion' => ['enabled' => 1],
                                'stats' => ['enabled' => 0],
                                'video' => ['enabled' => 0],
                                'team' => ['enabled' => 0],
                            ],
                        ],
                    ],
                    'gallery' => [
                        'https://demo.webilia.com/listdom/wp-content/uploads/sites/2/2020/10/business-interior.jpg',
                    ],
                    'attributes' => [
                        [
                            'term' => ['name' => 'Parking'],
                            'value' => 'Available',
                        ],
                        [
                            'term' => ['name' => 'Wi-Fi'],
                            'value' => 'Free',
                        ],
                    ],
                ],
                [
                    'post_title' => 'Sample Restaurant',
                    'post_content' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                    'post_author' => 1,
                    'taxonomies' => [
                        'listing_category' => [
                            ['name' => 'Restaurant'],
                        ],
                    ],
                    'image' => 'https://demo.webilia.com/listdom/wp-content/uploads/sites/2/2023/11/pablo-merchan-montes-Orz90t6o0e4-unsplash.jpg',
                    'meta' => [
                        'lsd_object_type' => 'marker',
                        'lsd_zoomlevel' => 4,
                        'lsd_latitude' => '51.507351',
                        'lsd_longitude' => '-0.127758',
                        'lsd_address' => 'London, UK',
                        'lsd_price' => 300,
                        'lsd_currency' => 'GBP',
                        'lsd_email' => 'info@samplerestaurant.com',
                        'lsd_phone' => '+447000000000',
                        'lsd_website' => 'https://samplerestaurant.com',
                        'lsd_remark' => 'A sample restaurant listing.',
                        'lsd_displ' => [
                            'style' => 'style3',
                            'elements_global' => 1,
                            'elements' => [
                                'labels' => ['enabled' => 1],
                                'image' => ['enabled' => 1],
                                'gallery' => ['enabled' => 1],
                                'title' => ['enabled' => 1],
                                'categories' => ['enabled' => 1],
                                'price' => ['enabled' => 1],
                                'tags' => ['enabled' => 1],
                                'content' => ['enabled' => 1],
                                'embed' => ['enabled' => 0],
                                'attributes' => ['enabled' => 0],
                                'features' => ['enabled' => 1],
                                'contact' => ['enabled' => 1],
                                'remark' => ['enabled' => 1],
                                'locations' => ['enabled' => 1],
                                'address' => ['enabled' => 1],
                                'map' => ['enabled' => 1],
                                'availability' => ['enabled' => 1],
                                'owner' => ['enabled' => 1],
                                'share' => ['enabled' => 1],
                                'abuse' => ['enabled' => 0],
                                'ads' => ['enabled' => 0],
                                'booking' => ['enabled' => 0],
                                'acf' => ['enabled' => 1],
                                'auction' => ['enabled' => 0],
                                'franchise' => ['enabled' => 0],
                                'application' => ['enabled' => 0],
                                'discussion' => ['enabled' => 1],
                                'stats' => ['enabled' => 0],
                                'video' => ['enabled' => 0],
                                'team' => ['enabled' => 0],
                            ],
                        ],
                    ],
                    'gallery' => [
                        'https://demo.webilia.com/listdom/wp-content/uploads/sites/2/2023/11/priscilla-du-preez-W3SEyZODn8U-unsplash.jpg',
                        'https://demo.webilia.com/listdom/wp-content/uploads/sites/2/2023/11/pablo-merchan-montes-Orz90t6o0e4-unsplash.jpg',
                    ],
                ],
                [
                    'post_title' => 'Jim Rohn',
                    'post_content' => 'Jim Rohn was an American entrepreneur, author, and motivational speaker. He is known for his work in personal development and self-improvement.',
                    'post_author' => 1,
                    'post_date' => '2023-06-01',
                    'taxonomies' => [
                        'listing_category' => [
                            ['name' => 'Motivational Speaker'],
                        ],
                    ],
                    'image' => 'https://demo.webilia.com/listdom/wp-content/uploads/sites/2/2024/09/pexels-chlsoekalaartist-1043474-copy.jpg',
                    'meta' => [
                        'lsd_object_type' => 'marker',
                        'lsd_zoomlevel' => 1,
                        'lsd_latitude' => '33.753746',
                        'lsd_longitude' => '-84.386330',
                        'lsd_address' => 'Atlanta, GA, USA',
                        'lsd_price' => 1000,
                        'lsd_currency' => 'USD',
                        'lsd_email' => 'info@jimrohn.com',
                        'lsd_phone' => '+14041234567',
                        'lsd_website' => 'https://jimrohn.com',
                        'lsd_remark' => 'A renowned speaker with a passion for motivating others.',
                        'lsd_displ' => [
                            'style' => 'style4',
                            'elements_global' => 1,
                            'elements' => [
                                'labels' => ['enabled' => 1],
                                'image' => ['enabled' => 1],
                                'gallery' => ['enabled' => 1],
                                'title' => ['enabled' => 1],
                                'categories' => ['enabled' => 1],
                                'price' => ['enabled' => 1],
                                'tags' => ['enabled' => 1],
                                'content' => ['enabled' => 1],
                                'embed' => ['enabled' => 0],
                                'attributes' => ['enabled' => 1],
                                'features' => ['enabled' => 1],
                                'contact' => ['enabled' => 1],
                                'remark' => ['enabled' => 1],
                                'locations' => ['enabled' => 1],
                                'address' => ['enabled' => 1],
                                'map' => ['enabled' => 1],
                                'availability' => ['enabled' => 1],
                                'owner' => ['enabled' => 1],
                                'share' => ['enabled' => 1],
                                'abuse' => ['enabled' => 0],
                                'ads' => ['enabled' => 0],
                                'booking' => ['enabled' => 0],
                                'acf' => ['enabled' => 0],
                                'auction' => ['enabled' => 0],
                                'franchise' => ['enabled' => 0],
                                'application' => ['enabled' => 0],
                                'discussion' => ['enabled' => 1],
                                'stats' => ['enabled' => 0],
                                'video' => ['enabled' => 0],
                                'team' => ['enabled' => 0],
                            ],
                        ],
                    ],
                    'gallery' => [
                        'https://demo.webilia.com/listdom/wp-content/uploads/sites/2/2024/09/pexels-chlsoekalaartist-1043474-copy.jpg',
                    ],
                ],
            ];

            $ix = new LSD_IX();
            $ix->collection($listings);
        }

        $this->response(['success' => 1]);
    }

    /**
     * Retrieves an existing shortcode by title or creates a new one if it doesn't exist.
     *
     * @param string $title The title of the shortcode.
     * @param array $meta Optional. An associative array of meta key-value pairs to set.
     * @return int The post ID of the shortcode.
     */
    public static function shortcode(string $title, array $meta = []): int
    {
        // Check if shortcode exists
        $shortcode = LSD_Base::get_post_by_title($title, LSD_Base::PTYPE_SHORTCODE);

        if (!$shortcode)
        {
            // Create shortcode if it doesn't exist
            $post_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => 'listdom',
                'post_type' => LSD_Base::PTYPE_SHORTCODE,
                'post_status' => 'publish',
            ]);

            if (!is_wp_error($post_id))
            {
                foreach ($meta as $key => $value)
                {
                    update_post_meta($post_id, $key, $value);
                }
            }

            return $post_id;
        }

        // Ensure the existing shortcode is not in the trash
        $is_trashed = self::is_post($shortcode->ID, 'trash');
        if ($is_trashed)
        {
            self::untrash_post($shortcode->ID);
            self::update_post_status($shortcode->ID);
        }

        return $shortcode->ID;
    }

    /**
     * Retrieves an existing page by title or creates a new one if it doesn't exist.
     *
     * @param string $title The title of the page.
     * @param string $content The content to set for the page.
     */
    public static function page(string $title, string $content)
    {
        // Check if page exists
        $page = LSD_Base::get_post_by_title($title, 'page');
        if ($page)
        {
            $is_trashed = self::is_post($page->ID, 'trash');
            if ($is_trashed)
            {
                self::untrash_post($page->ID);
                self::update_post_status($page->ID);
            }
            else
            {
                wp_update_post(['ID' => $page->ID, 'post_content' => $content]);
            }
        }
        else
        {
            // Create page if it doesn't exist
            wp_insert_post([
                'post_title' => $title,
                'post_content' => $content,
                'post_type' => 'page',
                'post_status' => 'publish',
            ]);
        }
    }
}
