<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

// Dashboard
$dashboard = new LSD_Dashboard();

// Settings
$settings = LSD_Options::settings();

$SN = new LSD_Socials();
$networks = LSD_Options::socials();
?>
<div class="lsd-settings-wrap">
    <form id="lsd_settings_form">
        <div class="lsd-accordion-title lsd-accordion-active">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('General', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel lsd-accordion-open">
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Date Picker Format', 'listdom'),
                    'for' => 'lsd_settings_datepicker_format',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_settings_datepicker_format',
                        'name' => 'lsd[datepicker_format]',
                        'options' => [
                            'yyyy-mm-dd' => esc_html(current_time('Y-m-d').' '.esc_html__('(Y-m-d)', 'listdom')),
                            'dd-mm-yyyy' => esc_html(current_time('d-m-Y').' '.esc_html__('(d-m-Y)', 'listdom')),
                            'yyyy/mm/dd' => esc_html(current_time('Y/m/d').' '.esc_html__('(Y/m/d)', 'listdom')),
                            'dd/mm/yyyy' => esc_html(current_time('d/m/Y').' '.esc_html__('(d/m/Y)', 'listdom')),
                            'yyyy.mm.dd' => esc_html(current_time('Y.m.d').' '.esc_html__('(Y.m.d)', 'listdom')),
                            'dd.mm.yyyy' => esc_html(current_time('d.m.Y').' '.esc_html__('(d.m.Y)', 'listdom')),
                        ],
                        'value' => $settings['datepicker_format'] ?? 'yyyy-mm-dd'
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Time Picker Format', 'listdom'),
                        'for' => 'lsd_settings_timepicker_format',
                    ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_settings_timepicker_format',
                        'name' => 'lsd[timepicker_format]',
                        'options' => [
                            24 => esc_html__('24 Hours', 'listdom'),
                            12 => esc_html__('12 Hours (AM / PM)', 'listdom'),
                        ],
                        'value' => $settings['timepicker_format'] ?? 24
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Listing Custom Link', 'listdom'),
                        'for' => 'lsd_settings_listing_link_status',
                    ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_settings_listing_link_status',
                        'name' => 'lsd[listing_link_status]',
                        'value' => $settings['listing_link_status'] ?? '1',
                    ]); ?>
                    <p class="description"><?php esc_html_e("You can disable the listing custom link field.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Currency Position', 'listdom'),
                        'for' => 'lsd_settings_currency_position',
                    ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_settings_currency_position',
                        'name' => 'lsd[currency_position]',
                        'value' => $settings['currency_position'] ?? 'before',
                        'options' => [
                            'before' => esc_html__('Before ($100)', 'listdom'),
                            'before_ws' => esc_html__('Before With Space ($ 100)', 'listdom'),
                            'after' => esc_html__('After (100$)', 'listdom'),
                            'after_ws' => esc_html__('After With Space (100 $)', 'listdom'),
                        ],
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Default Currency', 'listdom'),
                        'for' => 'lsd_settings_default_currency',
                    ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::currency([
                        'id' => 'lsd_settings_default_currency',
                        'name' => 'lsd[default_currency]',
                        'value' => $settings['default_currency'] ?? ''
                    ]); ?>
                    <p class="description"><?php esc_html_e("Select the default currency used in the listings.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Address Placeholder', 'listdom'),
                        'for' => 'lsd_settings_address_placeholder',
                    ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_settings_address_placeholder',
                        'name' => 'lsd[address_placeholder]',
                        'value' => $settings['address_placeholder'] ?? __('123 Main St, Unit X, City, State, Zipcode', 'listdom')
                    ]); ?>
                    <p class="description"><?php esc_html_e("This will appear as the placeholder for address fields in the listing editor.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('No Listing Message', 'listdom'),
                        'for' => 'lsd_settings_no_listings_message',
                    ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::textarea([
                        'id' => 'lsd_settings_no_listings_message',
                        'name' => 'lsd[no_listings_message]',
                        'rows' => 7,
                        'placeholder' => esc_html__('No Listing Found!', 'listdom'),
                        'value' => $settings['no_listings_message'] ?? ''
                    ]); ?>
                    <p class="description lsd-mb-0"><?php esc_html_e("It will be displayed instead of the search results when nothing is found. You can insert a text, HTML, or shortcodes.", 'listdom'); ?></p>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Map Module', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel">

            <?php if($this->isLite()): ?>
                <div class="lsd-mb-5 lsd-alert-no-mt"><?php echo LSD_Base::alert($this->missFeatureMessage(esc_html__('OpenStreetMap & Mapbox', 'listdom'), true), 'warning'); ?></div>
            <?php endif; ?>

            <h3 class="lsd-mb-4 lsd-mt-0"><?php esc_html_e('Map', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Default Map Provider', 'listdom'),
                        'for' => 'lsd_settings_map_provider',
                    ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::providers([
                        'id' => 'lsd_settings_map_provider',
                        'name' => 'lsd[map_provider]',
                        'value' => $settings['map_provider'] ?? 'leaflet'
                    ]); ?>
                    <p class="description"><?php esc_html_e("You can change the map provider in each shortcodes too.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                            'title' => esc_html__('Google Maps API key', 'listdom'),
                            'for' => 'lsd_settings_googlemaps_api_key',
                        ]); ?></div>
                    <div class="lsd-col-10">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_settings_googlemaps_api_key',
                            'name' => 'lsd[googlemaps_api_key]',
                            'value' => $settings['googlemaps_api_key'] ?? ''
                        ]); ?>
                        <p class="description"><?php esc_html_e("Google API Key is mandatory. Otherwise the Google Maps doesn't work. You can get an API key from the Google.", 'listdom'); ?></p>
                    </div>
                </div>
            </div>

            <?php if($this->isPro()): ?>
                <div class="lsd-map-provider-dependency lsd-map-provider-dependency-leaflet">
                    <div class="lsd-form-row">
                        <div class="lsd-col-2"><?php echo LSD_Form::label([
                                'title' => esc_html__('Mapbox Access Token', 'listdom'),
                                'for' => 'lsd_settings_mapbox_access_token',
                            ]); ?></div>
                        <div class="lsd-col-10">
                            <?php echo LSD_Form::text([
                                'id' => 'lsd_settings_mapbox_access_token',
                                'name' => 'lsd[mapbox_access_token]',
                                'value' => $settings['mapbox_access_token'] ?? ''
                            ]); ?>
                            <p class="description"><?php echo sprintf(esc_html__("If you want to use mapbox tiles you can get one from %s website otherwise the OSM tiles will be loaded in the maps.", 'listdom'), '<a href="https://mapbox.com" target="_blank">mapbox.com</a>'); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Default Zoom Level', 'listdom'),
                        'for' => 'lsd_settings_map_backend_zl',
                    ]); ?></div>
                <div class="lsd-col-1">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_settings_map_backend_zl',
                        'name' => 'lsd[map_backend_zl]',
                        'value' => $settings['map_backend_zl'] ?? '',
                        'options' => ['4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10, '11' => 11, '12' => 12, '13' => 13, '14' => 14]
                    ]); ?>
                </div>
                <div class="lsd-col-9">
                    <p class="description"><?php esc_html_e("It's for Google Maps in Add/Edit Map Objects menu.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('GPS Zoom Level', 'listdom'),
                        'for' => 'lsd_settings_map_gps_zl',
                    ]); ?></div>
                <div class="lsd-col-10 lsd-col-inline">
                    <p class="description"><?php echo sprintf(__("Change map zoom level to %s after detecting the geo point by GPS if current zoom level was less or equal to %s", 'listdom'), LSD_Form::select([
                            'id' => 'lsd_settings_map_gps_zl',
                            'name' => 'lsd[map_gps_zl]',
                            'class' => 'lsd-d-inline',
                            'value' => $settings['map_gps_zl'] ?? '',
                            'options' => ['4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10, '11' => 11, '12' => 12, '13' => 13, '14' => 14]
                        ]), LSD_Form::select([
                            'id' => 'lsd_settings_map_gps_zl_current',
                            'name' => 'lsd[map_gps_zl_current]',
                            'value' => $settings['map_gps_zl_current'] ?? '',
                            'options' => ['4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10, '11' => 11, '12' => 12, '13' => 13, '14' => 14]
                        ])); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Default Latitude', 'listdom'),
                        'for' => 'lsd_settings_map_backend_lt',
                    ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_settings_map_backend_lt',
                        'name' => 'lsd[map_backend_lt]',
                        'value' => $settings['map_backend_lt'] ?? '',
                        'placeholder' => esc_html__("It's for Google Maps in Add/Edit Map Objects menu.", 'listdom')
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Default Longitude', 'listdom'),
                        'for' => 'lsd_settings_map_backend_ln',
                    ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_settings_map_backend_ln',
                        'name' => 'lsd[map_backend_ln]',
                        'value' => $settings['map_backend_ln'] ?? '',
                        'placeholder' => esc_html__("It's for Google Maps in Add/Edit Listing menu.", 'listdom')
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row lsd-form-row-shape-display-options lsd-mt-5">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Shape Display Options', 'listdom'),
                        'for' => 'lsd_settings_map_shape_options_fill_color',
                    ]); ?></div>
                <div class="lsd-col-10">
                    <div class="lsd-form-row">
                        <div class="lsd-col-3">
                            <?php echo LSD_Form::label([
                                'title' => esc_html__('Fill Color', 'listdom'),
                                'for' => 'lsd_settings_map_shape_options_fill_color',
                            ]); ?>
                            <?php echo LSD_Form::colorpicker([
                                'id' => 'lsd_settings_map_shape_options_fill_color',
                                'name' => 'lsd[map_shape_fill_color]',
                                'default' => '#1e90ff',
                                'value' => $settings['map_shape_fill_color'] ?? '#1e90ff',
                            ]); ?>
                        </div>
                        <div class="lsd-col-2">
                            <?php echo LSD_Form::label([
                                'title' => esc_html__('Fill Opacity', 'listdom'),
                                'for' => 'lsd_settings_map_shape_options_fill_opacity',
                            ]); ?>
                            <?php echo LSD_Form::select([
                                'id' => 'lsd_settings_map_shape_options_fill_opacity',
                                'name' => 'lsd[map_shape_fill_opacity]',
                                'options' => ['0.3'=>'0.3', '0.4'=>'0.4', '0.5'=>'0.5', '0.6'=>'0.6', '0.7'=>'0.7', '0.8'=>'0.8'],
                                'value' => $settings['map_shape_fill_opacity'] ?? '0.3',
                            ]); ?>
                        </div>
                        <div class="lsd-col-3 lsd-pl-3">
                            <?php echo LSD_Form::label([
                                'title' => esc_html__('Border Color', 'listdom'),
                                'for' => 'lsd_settings_map_shape_options_stroke_color',
                            ]); ?>
                            <?php echo LSD_Form::colorpicker([
                                'id' => 'lsd_settings_map_shape_options_stroke_color',
                                'name' => 'lsd[map_shape_stroke_color]',
                                'default' => '#1e74c7',
                                'value' => $settings['map_shape_stroke_color'] ?? '#1e74c7',
                            ]); ?>
                        </div>
                        <div class="lsd-col-2">
                            <?php echo LSD_Form::label([
                                'title' => esc_html__('Border Opacity', 'listdom'),
                                'for' => 'lsd_settings_map_shape_options_stroke_opacity',
                            ]); ?>
                            <?php echo LSD_Form::select([
                                'id' => 'lsd_settings_map_shape_options_stroke_opacity',
                                'name' => 'lsd[map_shape_stroke_opacity]',
                                'options' => ['0.3'=>'0.3', '0.4'=>'0.4', '0.5'=>'0.5', '0.6'=>'0.6', '0.7'=>'0.7', '0.8'=>'0.8'],
                                'value' => $settings['map_shape_stroke_opacity'] ?? '0.8',
                            ]); ?>
                        </div>
                        <div class="lsd-col-2 lsd-pl-3">
                            <?php echo LSD_Form::label([
                                'title' => esc_html__('Border Weight', 'listdom'),
                                'for' => 'lsd_settings_map_shape_options_stroke_weight',
                            ]); ?>
                            <?php echo LSD_Form::select([
                                'id' => 'lsd_settings_map_shape_options_stroke_weight',
                                'name' => 'lsd[map_shape_stroke_weight]',
                                'options' => ['1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5'],
                                'value' => $settings['map_shape_stroke_weight'] ?? '2',
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="lsd-mb-4 lsd-mt-0"><?php esc_html_e('Geo-point', 'listdom'); ?></h3>
            <div>
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                            'title' => esc_html__('Google Geo-coding API key', 'listdom'),
                            'for' => 'lsd_settings_google_geocoding_api_key',
                        ]); ?></div>
                    <div class="lsd-col-10">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_settings_google_geocoding_api_key',
                            'name' => 'lsd[google_geocoding_api_key]',
                            'value' => $settings['google_geocoding_api_key'] ?? ''
                        ]); ?>
                        <p class="description lsd-mt-2 lsd-mb-0"><?php esc_html_e("If you don't provide the latitude and longitude when editing a listing, Listdom will automatically attempt to convert the address into a geo-point using OSM and Google Geocoding APIs. To ensure this functionality works smoothly, avoid restricting your API key by domain, as it is used in the backend of your website. Instead, consider using IP restrictions for better security.", 'listdom'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Price Components', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel">
            <?php if ($this->isLite()): ?>
                <div class="lsd-alert lsd-warning lsd-mt-0 lsd-mb-4">
                    <?php echo LSD_Base::missFeatureMessage(
                        sprintf(esc_html__('The ability to modify the %s', 'listdom'), '<strong>'.esc_html__('Price Components', 'listdom').'</strong>'),
                        false,
                        false
                    ); ?>
                </div>
            <?php endif; ?>

            <h3 class="lsd-my-0"><?php esc_html_e('Price Components', 'listdom'); ?></h3>
            <p class="description"><?php esc_html_e('You can disable certain price components if they are not needed.', 'listdom'); ?></p>

            <?php foreach ([
                   'currency' => esc_html__('Currency', 'listdom'),
                   'max' => esc_html__('Price Max', 'listdom'),
                   'after' => esc_html__('Price Description', 'listdom'),
                   'class' => esc_html__('Price Class', 'listdom')
               ] as $component => $label): ?>
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'for' => 'lsd_price_component_'.$component,
                        'title' => $label
                    ]); ?></div>
                    <div class="lsd-col-10"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_price_component_'.$component,
                        'name' => 'lsd[price_component_'.$component.']',
                        'value' => $settings['price_component_'.$component] ?? 1
                    ]); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="lsd-accordion-title">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Display Options', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel">
            <?php if(!LSD_Base::is_current_theme('listdomer')): ?>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Main Color', 'listdom'),
                    'for' => 'lsd_settings_display_options_main_color',
                ]); ?></div>
                <div class="lsd-col-10">
                    <div class="lsd-color-palette">
                        <?php echo LSD_Form::colorpalette([
                            'for' => '#lsd_settings_display_options_main_color',
                            'value' => $settings['dply_main_color'] ?? '#2b93ff'
                        ]); ?>
                        <p class="description"><?php esc_html_e("Choose from one of predefined colors in order to match it with your theme and acheive your desired look.", 'listdom'); ?></p>
                    </div>
                    <h3><?php esc_html_e('Or', 'listdom'); ?></h3>
                    <div>
                        <p class="description lsd-mb-2"><?php esc_html_e("Manually select your desired color.", 'listdom'); ?></p>
                        <?php echo LSD_Form::colorpicker([
                            'id' => 'lsd_settings_display_options_main_color',
                            'name' => 'lsd[dply_main_color]',
                            'default' => '#2b93ff',
                            'value' => $settings['dply_main_color'] ?? '#2b93ff'
                        ]); ?>
                    </div>
                </div>
            </div>
            <div class="lsd-form-row lsd-mt-4 lsd-mb-4">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Secondary Color', 'listdom'),
                    'for' => 'lsd_settings_display_options_secondary_color',
                ]); ?></div>
                <div class="lsd-col-10">
                    <div class="lsd-color-palette">
                        <?php echo LSD_Form::colorpalette([
                            'for' => '#lsd_settings_display_options_secondary_color',
                            'value' => $settings['dply_secondary_color'] ?? '#f43d3d'
                        ]); ?>
                        <p class="description"><?php esc_html_e("Choose secondary color from one of predefined colors.", 'listdom'); ?></p>
                    </div>
                    <h3><?php esc_html_e('Or', 'listdom'); ?></h3>
                    <div>
                        <p class="description lsd-mb-2"><?php esc_html_e("Manually select your desired color.", 'listdom'); ?></p>
                        <?php echo LSD_Form::colorpicker([
                            'id' => 'lsd_settings_display_options_secondary_color',
                            'name' => 'lsd[dply_secondary_color]',
                            'default' => '#f43d3d',
                            'value' => $settings['dply_secondary_color'] ?? '#f43d3d'
                        ]); ?>
                    </div>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Main Font', 'listdom'),
                        'for' => 'lsd_settings_display_options_main_font',
                    ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::fontpicker([
                        'id' => 'lsd_settings_display_options_main_font',
                        'name' => 'lsd[dply_main_font]',
                        'value' => $settings['dply_main_font'] ?? 'lato'
                    ]); ?>
                    <p class="description"><?php esc_html_e("Use your theme / desired font if you don't like the default font.", 'listdom'); ?></p>
                </div>
            </div>
            <?php else: ?>
                <p class="description lsd-alert lsd-info">
                    <?php
                    printf(
                        esc_html__('You have listdomer theme installed. Use %s to manage the Listdom display options.', 'listdom'),
                        '<a href="?page=listdomer-settings">' . esc_html__('Theme Settings', 'listdom') . '</a>'
                    );
                    ?>
                </p>
            <?php endif; ?>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Font Awesome Status', 'listdom'),
                    'for' => 'lsd_settings_fontawesome_status',
                ]); ?></div>
                <div class="lsd-col-10">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_settings_fontawesome_status',
                        'value' => $settings['fontawesome_status'] ?? 1,
                        'name' => 'lsd[fontawesome_status]',
                    ]); ?>
                    <p class="description lsd-mb-0"><?php esc_html_e("Disable Listdom icon file (Font-awesome) if it's loaded by another third party!", 'listdom'); ?></p>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Socials', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel">
            <h3 class="lsd-mt-0"><?php esc_html_e('Social Networks', 'listdom'); ?></h3>
            <div class="lsd-form-row lsd-mb-3">
                <div class="lsd-col-2"></div>
                <div class="lsd-col-1"></div>
                <div class="lsd-col-1"><?php esc_html_e('Profile', 'listdom'); ?></div>
                <div class="lsd-col-1"><?php esc_html_e('Archive', 'listdom'); ?></div>
                <div class="lsd-col-1"><?php esc_html_e('Details', 'listdom'); ?></div>
                <div class="lsd-col-1"><?php esc_html_e('Contact', 'listdom'); ?></div>
            </div>
            <div class="lsd-social-networks lsd-sortable">
                <?php foreach($networks as $network=>$values): $obj = $SN->get($network, $values); if(!$obj) continue; ?>
                    <div class="lsd-form-row lsd-social-network">
                        <div class="lsd-col-2 lsd-cursor-move">
                            <i class="lsd-icon fas fa-arrows-alt"></i>
                            <span class="lsd-ml-4">
                                    <strong><?php echo esc_html($obj->label()); ?></strong>
                                    <input type="hidden" name="lsd[<?php echo esc_attr($obj->key()); ?>][key]" value="<?php echo esc_attr($obj->key()); ?>">
                                </span>
                        </div>
                        <div class="lsd-col-1"></div>
                        <div class="lsd-col-1">
                            <label class="lsd-switch">
                                <input type="hidden" name="lsd[<?php echo esc_attr($obj->key()); ?>][profile]" value="0">
                                <input type="checkbox" name="lsd[<?php echo esc_attr($obj->key()); ?>][profile]" value="1" <?php echo $obj->option('profile') == 1 ? 'checked="checked"' : ''; ?>>
                                <span class="lsd-slider"></span>
                            </label>
                        </div>
                        <div class="lsd-col-1">
                            <label class="lsd-switch">
                                <input type="hidden" name="lsd[<?php echo esc_attr($obj->key()); ?>][archive_share]" value="0">
                                <input type="checkbox" name="lsd[<?php echo esc_attr($obj->key()); ?>][archive_share]" value="1" <?php echo $obj->option('archive_share') == 1 ? 'checked="checked"' : ''; ?>>
                                <span class="lsd-slider"></span>
                            </label>
                        </div>
                        <div class="lsd-col-1">
                            <label class="lsd-switch">
                                <input type="hidden" name="lsd[<?php echo esc_attr($obj->key()); ?>][single_share]" value="0">
                                <input type="checkbox" name="lsd[<?php echo esc_attr($obj->key()); ?>][single_share]" value="1" <?php echo $obj->option('single_share') == 1 ? 'checked="checked"' : ''; ?>>
                                <span class="lsd-slider"></span>
                            </label>
                        </div>
                        <div class="lsd-col-1">
                            <label class="lsd-switch">
                                <input type="hidden" name="lsd[<?php echo esc_attr($obj->key()); ?>][listing]" value="0">
                                <input type="checkbox" name="lsd[<?php echo esc_attr($obj->key()); ?>][listing]" value="1" <?php echo $obj->option('listing') == 1 ? 'checked="checked"' : ''; ?>>
                                <span class="lsd-slider"></span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="lsd-accordion-title">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Google reCAPTCHA', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel">
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Status', 'listdom'),
                    'for' => 'lsd_settings_grecaptcha_status',
                ]); ?></div>
                <div class="lsd-col-10">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_settings_grecaptcha_status',
                        'value' => $settings['grecaptcha_status'] ?? false,
                        'name' => 'lsd[grecaptcha_status]',
                        'toggle' => '#lsd_settings_grecaptcha_options'
                    ]); ?>
                    <p class="description"><?php esc_html_e("Protect Listdom forms against spammer robots using Google reCAPTCHA V2.", 'listdom'); ?></p>
                </div>
            </div>
            <div id="lsd_settings_grecaptcha_options" <?php echo isset($settings['grecaptcha_status']) && $settings['grecaptcha_status'] ? '' : 'style="display: none;"'; ?>>
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Site Key', 'listdom'),
                        'for' => 'lsd_settings_grecaptcha_sitekey',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_settings_grecaptcha_sitekey',
                            'value' => $settings['grecaptcha_sitekey'] ?? '',
                            'name' => 'lsd[grecaptcha_sitekey]'
                        ]); ?>
                    </div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Secret Key', 'listdom'),
                        'for' => 'lsd_settings_grecaptcha_secretkey',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_settings_grecaptcha_secretkey',
                            'value' => $settings['grecaptcha_secretkey'] ?? '',
                            'name' => 'lsd[grecaptcha_secretkey]'
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Integrations', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel">
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Block Editor', 'listdom'),
                    'for' => 'lsd_settings_blockeditor_status',
                ]); ?></div>
                <div class="lsd-col-10">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_settings_blockeditor_status',
                        'value' => $settings['blockeditor_status'] ?? false,
                        'name' => 'lsd[blockeditor_status]',
                    ]); ?>
                    <p class="description lsd-mb-0"><?php esc_html_e("Enable block editor for listings", 'listdom'); ?></p>
                </div>
            </div>
        </div>

        <?php
            // Third Party Options
            do_action('lsd_settings_form_general', $settings);
        ?>

        <div class="lsd-spacer-10"></div>
        <div class="lsd-form-row">
            <div class="lsd-col-12 lsd-flex lsd-gap-3">
                <?php LSD_Form::nonce('lsd_settings_form'); ?>
                <?php echo LSD_Form::submit([
                    'label' => esc_html__('Save', 'listdom'),
                    'id' => 'lsd_settings_save_button',
                    'class' => 'button button-hero button-primary',
                ]); ?>
                <div>
                    <p class="lsd-util-hide lsd-settings-success-message lsd-alert lsd-success lsd-m-0"><?php esc_html_e('Options saved successfully.', 'listdom'); ?></p>
                    <p class="lsd-util-hide lsd-settings-error-message lsd-alert lsd-error lsd-m-0"><?php esc_html_e('Error: Unable to save options.', 'listdom'); ?></p>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
jQuery('#lsd_settings_form').on('submit', function(e)
{
    e.preventDefault();

    // Elements
    const $button = jQuery('#lsd_settings_save_button');
    const $success = jQuery('.lsd-settings-success-message');
    const $error = jQuery('.lsd-settings-error-message');

    // Loading Styles
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    const settings = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: 'POST',
        url: ajaxurl,
        data: 'action=lsd_save_settings&' + settings,
        success: function()
        {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($success, 2000);
        },
        error: function()
        {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($error, 2000);
        }
    });
});
</script>
