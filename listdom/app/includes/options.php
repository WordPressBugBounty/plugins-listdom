<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Main Class.
 *
 * @class LSD_Options
 * @version    1.0.0
 */
class LSD_Options extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function settings(): array
    {
        return self::parse_args(
            get_option('lsd_settings', []),
            self::defaults()
        );
    }

    public static function api(): array
    {
        $defaults = self::defaults('api');
        $current = get_option('lsd_api', []);

        // Add if not exists
        if (!is_array($current) or !isset($current['tokens'])) update_option('lsd_api', $defaults);

        return self::parse_args(
            $current,
            $defaults
        );
    }

    public static function auth(): array
    {
        return self::parse_args(
            get_option('lsd_auth', []),
            self::defaults('auth')
        );
    }

    public static function styles(): array
    {
        return self::parse_args(
            get_option('lsd_styles', []),
            self::defaults('styles')
        );
    }

    public static function details_page(): array
    {
        return self::parse_args(
            get_option('lsd_details_page', []),
            self::defaults('details_page')
        );
    }

    public static function dummy(): array
    {
        return self::parse_args(
            get_option('lsd_dummy', []),
            self::defaults('dummy')
        );
    }

    public static function socials()
    {
        $socials = get_option('lsd_socials', []);
        if (count($socials)) return $socials;

        return self::parse_args(
            $socials,
            self::defaults('socials')
        );
    }

    public static function details_page_pattern()
    {
        $default = self::defaults('details_page_pattern');
        $pattern = get_option('lsd_details_page_pattern', '');

        return trim($pattern) ? $pattern : $default;
    }

    public static function addons($addon = null, $values = null)
    {
        $addons = self::parse_args(
            get_option('lsd_addons', []),
            self::defaults('addons')
        );

        // Save Options
        if (is_array($values))
        {
            $addons[$addon] = $values;

            // Save options
            return update_option('lsd_addons', $addons);
        }

        return $addon ? ($addons[$addon] ?? []) : $addons;
    }

    public static function defaults($option = 'settings')
    {
        switch ($option)
        {
            case 'styles';

                $defaults = [
                    'CSS' => '',
                ];
                break;

            case 'socials';

                $defaults = [
                    'facebook' => [
                        'key' => 'facebook',
                        'profile' => 1,
                        'archive_share' => 1,
                        'single_share' => 1,
                        'listing' => 1,
                    ],
                    'twitter' => [
                        'key' => 'twitter',
                        'profile' => 1,
                        'archive_share' => 1,
                        'single_share' => 1,
                        'listing' => 1,
                    ],
                    'pinterest' => [
                        'key' => 'pinterest',
                        'profile' => 0,
                        'archive_share' => 0,
                        'single_share' => 1,
                        'listing' => 0,
                    ],
                    'linkedin' => [
                        'key' => 'linkedin',
                        'profile' => 1,
                        'archive_share' => 0,
                        'single_share' => 1,
                        'listing' => 0,
                    ],
                    'instagram' => [
                        'key' => 'instagram',
                        'profile' => 1,
                        'archive_share' => 0,
                        'single_share' => 1,
                        'listing' => 1,
                    ],
                    'whatsapp' => [
                        'key' => 'whatsapp',
                        'profile' => 1,
                        'archive_share' => 0,
                        'single_share' => 0,
                        'listing' => 1,
                    ],
                    'youtube' => [
                        'key' => 'youtube',
                        'profile' => 1,
                        'archive_share' => 0,
                        'single_share' => 0,
                        'listing' => 1,
                    ],
                    'tiktok' => [
                        'key' => 'tiktok',
                        'profile' => 1,
                        'archive_share' => 0,
                        'single_share' => 0,
                        'listing' => 1,
                    ],
                    'telegram' => [
                        'key' => 'telegram',
                        'profile' => 1,
                        'archive_share' => 0,
                        'single_share' => 0,
                        'listing' => 1,
                    ],
                ];

                break;

            case 'details_page';

                $defaults = [
                    'general' => [
                        'style' => 'style1',
                        'theme_template' => '',
                        'comments' => 1,
                        'displ' => '0',
                    ],
                    'elements' => [
                        'labels' => ['enabled' => 1, 'show_title' => 0],
                        'image' => ['enabled' => 1, 'show_title' => 0],
                        'video' => ['enabled' => 0, 'show_title' => 0],
                        'title' => ['enabled' => 1, 'show_title' => 0],
                        'categories' => ['enabled' => 1, 'show_title' => 1],
                        'price' => ['enabled' => 1, 'show_title' => 0],
                        'tags' => ['enabled' => 1, 'show_title' => 1],
                        'content' => ['enabled' => 1, 'show_title' => 0],
                        'gallery' => ['enabled' => 1, 'show_title' => 0],
                        'embed' => ['enabled' => 0, 'show_title' => 0],
                        'attributes' => ['enabled' => 1, 'show_title' => 1, 'show_icons' => 0, 'show_attribute_title' => 1],
                        'features' => ['enabled' => 1, 'show_title' => 1, 'show_icons' => 0],
                        'contact' => ['enabled' => 1, 'show_title' => 1],
                        'remark' => ['enabled' => 1, 'show_title' => 0],
                        'locations' => ['enabled' => 1, 'show_title' => 0],
                        'address' => ['enabled' => 1, 'show_title' => 0],
                        'map' => ['enabled' => 1, 'show_title' => 0],
                        'availability' => ['enabled' => 1, 'show_title' => 1],
                        'owner' => ['enabled' => 1, 'show_title' => 0],
                        'share' => ['enabled' => 1, 'show_title' => 0],
                        'abuse' => ['enabled' => 0, 'show_title' => 0],
                    ],
                ];

                break;

            case 'details_page_pattern':

                $defaults = '{labels}{image}{title}{categories}{price}{tags}{content}{gallery}{embed}{attributes}{features}{contact}{remark}{locations}{address}{map}{availability}{owner}{share}';
                break;

            case 'mapcontrols':

                $defaults = [
                    'zoom' => 'RIGHT_BOTTOM',
                    'maptype' => 'TOP_LEFT',
                    'streetview' => 'RIGHT_BOTTOM',
                    'draw' => 'TOP_CENTER',
                    'gps' => 'RIGHT_BOTTOM',
                    'scale' => '0',
                    'fullscreen' => '1',
                ];

                break;

            case 'sorts':

                $defaults = [
                    'default' => [
                        'orderby' => 'post_date',
                        'order' => 'DESC',
                    ],
                    'display' => 1,
                    'sort_style' => '',
                    'options' => [
                        'post_date' => [
                            'status' => '1',
                            'name' => esc_html__('List Date', 'listdom'),
                            'order' => 'DESC',
                        ],
                        'title' => [
                            'status' => '1',
                            'name' => esc_html__('Listing Title', 'listdom'),
                            'order' => 'ASC',
                        ],
                        'modified' => [
                            'status' => '1',
                            'name' => esc_html__('Last Update', 'listdom'),
                            'order' => 'DESC',
                        ],
                        'comment_count' => [
                            'status' => '1',
                            'name' => esc_html__('Comments', 'listdom'),
                            'order' => 'DESC',
                        ],
                        'ID' => [
                            'status' => '0',
                            'name' => esc_html__('Listing ID', 'listdom'),
                            'order' => 'DESC',
                        ],
                        'lsd_visits' => [
                            'status' => '0',
                            'name' => esc_html__('Most Viewed', 'listdom'),
                            'order' => 'DESC',
                        ],
                    ],
                ];

                break;

            case 'addons':

                $defaults = [];
                break;

            case 'api':

                $defaults = [
                    'tokens' => [
                        [
                            'name' => esc_html__('Default API', 'listdom'),
                            'key' => LSD_Base::str_random(40),
                        ],
                    ],
                ];
                break;

            case 'auth':

                $defaults = [
                    'auth' => [
                        'switch_style' => 'both',
                        'hide_login_form' => 0,
                        'hide_register_form' => 0,
                        'hide_forgot_password_form' => 0,
                        'register_tab_label' => __('Register', 'listdom'),
                        'register_link_label' => __('Not a member? Register.', 'listdom'),
                        'login_tab_label' => __('Login', 'listdom'),
                        'login_link_label' => __('Already a member? Login.', 'listdom'),
                        'forgot_password_tab_label' => __('Forgot Password', 'listdom'),
                        'forgot_password_link_label' => __('Forgot your password?', 'listdom'),
                    ],
                    'register' => [
                        'redirect' => get_option('page_on_front') ?? 0,
                        'username_label' => __('Username', 'listdom'),
                        'username_placeholder' => __('Enter your username', 'listdom'),
                        'password_label' => __('Password', 'listdom'),
                        'password_placeholder' => __('Enter your password', 'listdom'),
                        'email_label' => __('Email', 'listdom'),
                        'email_placeholder' => __('Enter your email address', 'listdom'),
                        'submit_label' => __('Register', 'listdom'),
                        'login_after_register' => 1,
                        'strong_password' => 1,
                        'password_length' => 8,
                        'contain_uppercase' => 1,
                        'contain_lowercase' => 1,
                        'contain_numbers' => 1,
                        'contain_specials' => 1,
                    ],
                    'login' => [
                        'redirect' => get_option('page_on_front') ?? 0,
                        'username_label' => __('Username', 'listdom'),
                        'password_label' => __('Password', 'listdom'),
                        'username_placeholder' => __('Enter your username', 'listdom'),
                        'password_placeholder' => __('Enter your password', 'listdom'),
                        'remember_label' => __('Remember Me', 'listdom'),
                        'submit_label' => __('Log In', 'listdom'),
                    ],
                    'forgot_password' => [
                        'redirect' => get_option('page_on_front') ?? 0,
                        'email_label' => __('Email', 'listdom'),
                        'email_placeholder' => __('Enter your Email Address', 'listdom'),
                        'submit_label' => __('Reset Password', 'listdom'),
                    ],
                    'account' => [
                        'redirect' => get_option('page_on_front') ?? 0,
                    ],
                    'logout' => [
                        'redirect' => get_option('page_on_front') ?? 0,
                    ],
                ];
                break;

            case 'dummy':
                $defaults = [
                    'dummy' => [
                        'listings' => 1,
                        'categories' => 1,
                        'locations' => 1,
                        'tags' => 1,
                        'features' => 1,
                        'labels' => 1,
                        'shortcodes' => 1,
                        'frontend_dashboard' => 1,
                        'attributes' => 1
                    ]
                ];
                break;

            default:

                $defaults = [
                    'default_currency' => 'USD',
                    'currency_position' => 'before',
                    'timepicker_format' => 24,
                    'listing_link_status' => 1,
                    'map_provider' => 'googlemap',
                    'map_gps_zl' => 13,
                    'map_gps_zl_current' => 7,
                    'map_backend_zl' => 6,
                    'map_backend_lt' => '37.0625',
                    'map_backend_ln' => '-95.677068',
                    'map_shape_fill_color' => '#1e90ff',
                    'map_shape_fill_opacity' => '0.3',
                    'map_shape_stroke_color' => '#1e74c7',
                    'map_shape_stroke_opacity' => '0.8',
                    'map_shape_stroke_weight' => '2',
                    'googlemaps_api_key' => '',
                    'mapbox_access_token' => '',
                    'dply_main_color' => '#2b93ff',
                    'dply_secondary_color' => '#f43d3d',
                    'dply_main_font' => 'lato',
                    'listings_slug' => 'listings',
                    'category_slug' => 'listing-category',
                    'location_slug' => 'listing-location',
                    'tag_slug' => 'listing-tag',
                    'feature_slug' => 'listing-feature',
                    'label_slug' => 'listing-label',
                    'grecaptcha_status' => 0,
                    'grecaptcha_sitekey' => '',
                    'grecaptcha_secretkey' => '',
                    'location_archive' => '',
                    'category_archive' => '',
                    'tag_archive' => '',
                    'feature_archive' => '',
                    'label_archive' => '',
                    'submission_tax_listdom-location_method' => 'checkboxes',
                    'submission_tax_listdom-feature_method' => 'checkboxes',
                    'submission_module' => [
                        'address' => 1,
                        'price' => 1,
                        'availability' => 1,
                        'contact' => 1,
                        'remark' => 1,
                        'gallery' => 1,
                        'attributes' => 1,
                        'locations' => 1,
                        'tags' => 1,
                        'features' => 1,
                        'labels' => 1,
                        'image' => 1,
                        'embed' => 1,
                    ],
                ];
        }

        // Default Values
        return apply_filters('lsd_default_options', $defaults, $option);
    }

    public static function slug(): string
    {
        $settings = self::settings();
        $slug = $settings['listings_slug'] ?? 'listings';

        return sanitize_title($slug);
    }

    public static function location_slug(): string
    {
        $settings = self::settings();
        $slug = $settings['location_slug'] ?? 'listing-location';

        return sanitize_title($slug);
    }

    public static function category_slug(): string
    {
        $settings = self::settings();
        $slug = $settings['category_slug'] ?? 'listing-category';

        return sanitize_title($slug);
    }

    public static function tag_slug(): string
    {
        $settings = self::settings();
        $slug = $settings['tag_slug'] ?? 'listing-tag';

        return sanitize_title($slug);
    }

    public static function feature_slug(): string
    {
        $settings = self::settings();
        $slug = $settings['feature_slug'] ?? 'listing-feature';

        return sanitize_title($slug);
    }

    public static function label_slug(): string
    {
        $settings = self::settings();
        $slug = $settings['label_slug'] ?? 'listing-label';

        return sanitize_title($slug);
    }

    public static function mapbox_token(): string
    {
        $settings = self::settings();
        $token = $settings['mapbox_access_token'] ?? '';

        return apply_filters('lsd_mapbox_token', trim($token));
    }

    public static function currency(): string
    {
        $settings = self::settings();
        $currency = $settings['default_currency'] ?? 'USD';

        return apply_filters('lsd_default_currency', trim($currency));
    }

    /**
     * @param string $key
     * @param string $type
     * @param bool $return_original
     * @return int
     */
    public static function post_id(string $key, string $type = 'page', bool $return_original = true): int
    {
        $settings = self::settings();
        $post_id = $settings[$key] ?? 0;

        return (int) apply_filters('wpml_object_id', $post_id, $type, $return_original);
    }
}

