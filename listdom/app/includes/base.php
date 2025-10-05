<?php

class LSD_Base
{
    const PTYPE_LISTING = 'listdom-listing';
    const PTYPE_SHORTCODE = 'listdom-shortcode';
    const PTYPE_SEARCH = 'listdom-search';
    const PTYPE_NOTIFICATION = 'listdom-notification';
    const TAX_LOCATION = 'listdom-location';
    const TAX_CATEGORY = 'listdom-category';
    const TAX_TAG = 'listdom-tag';
    const TAX_FEATURE = 'listdom-feature';
    const TAX_ATTRIBUTE = 'listdom-attribute';
    const TAX_LABEL = 'listdom-label';
    const STATUS_EXPIRED = 'expired';
    const STATUS_HOLD = 'hold';
    const STATUS_OFFLINE = 'offline';
    const EP_LISTING = 701;
    const DB_VERSION = 2;
    const REQ_HTML = '<span class="lsd-required">*</span>';
    const WELCOME_SLUG = 'listdom-welcome';

    public function include_html_file($file = '', $args = [])
    {
        // File is empty
        if (!trim($file)) return esc_html__('HTML file is empty!', 'listdom');

        // Core File
        $path = $this->get_listdom_path() . '/app/html/' . ltrim($file, '/');

        // Apply Filter
        $path = apply_filters('lsd_include_html_file', $path, $file, $args);

        // File does not exist
        if (!file_exists($path)) return esc_html__('HTML file does not exist! Check the file path.', 'listdom');

        // Return the File Path
        if (isset($args['return_path']) && $args['return_path']) return $path;

        // Parameters passed
        if (isset($args['parameters']) && is_array($args['parameters']) && count($args['parameters'])) extract($args['parameters']);

        // Start buffering
        ob_start();

        // Include Once
        if (isset($args['include_once']) && $args['include_once']) include_once $path;
        else include $path;

        // Get Buffer
        $output = ob_get_clean();

        // Return the file output
        if (isset($args['return_output']) && $args['return_output']) return $output;

        // Print the output
        echo trim($output);
        return '';
    }

    public function get_listdom_path(): string
    {
        return LSD_ABSPATH;
    }

    public function get_upload_path(): string
    {
        // Create
        LSD_Folder::create(LSD_UP_DIR);

        return LSD_UP_DIR;
    }

    public function get_upload_url(): string
    {
        // WordPress Upload Directory
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/listdom/';
    }

    public function upload_to_listdom_dir(array $file, string $filename, array $overrides = []): array
    {
        if (!function_exists('wp_handle_upload')) require_once ABSPATH . 'wp-admin/includes/file.php';

        $path = untrailingslashit($this->get_upload_path());
        $url = untrailingslashit($this->get_upload_url());

        $upload_dir_filter = static function ($dirs) use ($path, $url)
        {
            $dirs['path'] = $path;
            $dirs['url'] = $url;
            $dirs['subdir'] = '';
            $dirs['basedir'] = $path;
            $dirs['baseurl'] = $url;

            return $dirs;
        };

        add_filter('upload_dir', $upload_dir_filter);

        $mime_types = $overrides['mimes'] ?? [];
        $mime_filter = null;
        $check_filter = null;

        if (is_array($mime_types) && count($mime_types))
        {
            $mime_filter = static function ($mimes) use ($mime_types) {
                return array_merge($mimes, $mime_types);
            };

            add_filter('upload_mimes', $mime_filter);

            $check_filter = static function ($checked, $file_path, $filename, $mimes = null, $real_mime = null) use ($mime_types)
            {
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if ($extension && isset($mime_types[$extension]))
                {
                    $checked['ext'] = $extension;
                    $checked['type'] = $mime_types[$extension];
                }

                return $checked;
            };

            add_filter('wp_check_filetype_and_ext', $check_filter, 10, 5);
        }

        $overrides = array_merge([
            'test_form' => false,
        ], $overrides);

        if ($filename !== '' && !isset($overrides['unique_filename_callback']))
        {
            $overrides['unique_filename_callback'] = static function () use ($filename) {
                return $filename;
            };
        }

        $uploaded = wp_handle_upload($file, $overrides);

        if ($check_filter) remove_filter('wp_check_filetype_and_ext', $check_filter);
        if ($mime_filter) remove_filter('upload_mimes', $mime_filter);

        remove_filter('upload_dir', $upload_dir_filter);

        return $uploaded;
    }

    public function lsd_url(): string
    {
        return plugins_url() . '/' . LSD_DIRNAME;
    }

    public function lsd_asset_url($asset): string
    {
        return $this->lsd_url() . '/assets/' . trim($asset, '/ ');
    }

    public function lsd_asset_path($asset): string
    {
        return $this->get_listdom_path() . '/assets/' . trim($asset, '/ ');
    }

    public function current_ip()
    {
        foreach (['HTTP_TRUE_CLIENT_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key)
        {
            if (!isset($_SERVER[$key])) continue;

            $value = wp_unslash($_SERVER[$key]);
            if (!is_string($value)) continue;

            $value = trim($value);
            if ($value === '') continue;

            $value = explode(',', $value)[0];
            $value = trim($value);
            if ($value === '') continue;

            $ip = filter_var($value, FILTER_VALIDATE_IP);
            if ($ip !== false) return $ip;

            $sanitized = sanitize_text_field($value);
            if ($sanitized !== '') return $sanitized;
        }

        return '';
    }

    public function get_post_meta($post_id): array
    {
        $raw_data = get_post_meta($post_id, '', true);

        $data = [];
        foreach ($raw_data as $key => $val) $data[$key] = isset($val[0]) ? (!is_serialized($val[0]) ? $val[0] : unserialize($val[0])) : null;

        return $data;
    }

    public function get_term_meta($term_id): array
    {
        $raw_data = get_term_meta($term_id, '', true);

        if (!is_array($raw_data)) return [];

        $data = [];
        foreach ($raw_data as $key => $val) $data[$key] = isset($val[0]) ? (!is_serialized($val[0]) ? $val[0] : unserialize($val[0])) : null;

        return $data;
    }

    public function get_user_meta($user_id): array
    {
        $raw_data = get_user_meta($user_id, '', true);

        $data = [];
        foreach ($raw_data as $key => $val) $data[$key] = isset($val[0]) ? (!is_serialized($val[0]) ? $val[0] : unserialize($val[0])) : null;

        return $data;
    }

    public function get_map_control_positions(): array
    {
        // Positions
        return [
            'TOP_LEFT' => esc_html__('Top Left', 'listdom'),
            'TOP_CENTER' => esc_html__('Top Center', 'listdom'),
            'TOP_RIGHT' => esc_html__('Top Right', 'listdom'),
            'LEFT_TOP' => esc_html__('Left Top', 'listdom'),
            'LEFT_CENTER' => esc_html__('Left Center', 'listdom'),
            'LEFT_BOTTOM' => esc_html__('Left Bottom', 'listdom'),
            'RIGHT_TOP' => esc_html__('Right Top', 'listdom'),
            'RIGHT_CENTER' => esc_html__('Right Center', 'listdom'),
            'RIGHT_BOTTOM' => esc_html__('Right Bottom', 'listdom'),
            'BOTTOM_LEFT' => esc_html__('Bottom Left', 'listdom'),
            'BOTTOM_CENTER' => esc_html__('Bottom Center', 'listdom'),
            'BOTTOM_RIGHT' => esc_html__('Bottom Right', 'listdom'),
        ];
    }

    public function get_available_sort_options()
    {
        // Sort Options
        $options = [
            'post_date' => [
                'status' => 1,
                'name' => esc_html__('List Date', 'listdom'),
                'order' => 'DESC',
            ],
            'title' => [
                'status' => 1,
                'name' => esc_html__('Listing Title', 'listdom'),
                'order' => 'ASC',
            ],
            'modified' => [
                'status' => 0,
                'name' => esc_html__('Last Update', 'listdom'),
                'order' => 'DESC',
            ],
            'comment_count' => [
                'status' => 0,
                'name' => esc_html__('Comments', 'listdom'),
                'order' => 'DESC',
            ],
            'ID' => [
                'status' => 0,
                'name' => esc_html__('Listing ID', 'listdom'),
                'order' => 'DESC',
            ],
            'author' => [
                'status' => 1,
                'name' => esc_html__('Author', 'listdom'),
                'order' => 'ASC',
            ],
            'rand' => [
                'status' => 0,
                'name' => esc_html__('Random', 'listdom'),
                'order' => 'ASC',
            ],
            'lsd_price' => [
                'status' => 0,
                'name' => esc_html__('Price', 'listdom'),
                'order' => 'ASC',
                'orderby' => 'meta_value_num',
            ],
            'lsd_visits' => [
                'status' => 0,
                'name' => esc_html__('Most Viewed', 'listdom'),
                'order' => 'DESC',
                'orderby' => 'meta_value_num',
            ],
        ];

        if (!LSD_Components::pricing()) unset($options['lsd_price']);

        $attributes = LSD_Main::get_attributes_details();
        foreach ($attributes as $attribute)
        {
            $field_type = $attribute['field_type'] ?? '';
            if (in_array($field_type, ['separator', 'image'], true)) continue;

            $key = 'lsd_attribute_' . $attribute['slug'];
            $is_numeric = $field_type === 'number';
            $is_datetime = in_array($field_type, ['date', 'time', 'datetime'], true);

            $options[$key] = [
                'status' => 0,
                'name' => $attribute['name'],
                'order' => $is_numeric ? 'DESC' : 'ASC',
                'orderby' => $is_numeric ? 'meta_value_num' : 'meta_value',
            ];

            if ($is_datetime)
            {
                $options[$key]['order'] = 'ASC';

                switch ($field_type)
                {
                    case 'datetime':
                        $options[$key]['meta_type'] = 'DATETIME';
                        break;

                    case 'date':
                        $options[$key]['meta_type'] = 'DATE';
                        break;

                    case 'time':
                        $options[$key]['meta_type'] = 'TIME';
                        break;
                }
            }
        }

        return apply_filters('lsd_sort_options', $options);
    }

    public static function alert($message, $type = 'info'): string
    {
        if (!trim($message)) return '';
        return '<div class="lsd-alert lsd-' . esc_attr($type) . '">' . $message . '</div>';
    }

    public static function parse_args($a, $b): array
    {
        $a = (array) $a;
        $b = (array) $b;

        $result = $a;
        foreach ($b as $k => $v)
        {
            if (is_array($v) && isset($result[$k])) $result[$k] = self::parse_args($result[$k], $v);
            else if (!isset($result[$k])) $result[$k] = $v;
        }

        return $result;
    }

    public static function get_text_color($bg_color = null): string
    {
        return LSD_Color::text_color($bg_color);
    }

    public static function get_text_class($color = 'main'): string
    {
        return LSD_Color::text_class($color);
    }

    public function get_sf($default = [])
    {
        $vars = array_merge($_GET, $_POST);

        // Sanitization
        array_walk_recursive($vars, 'sanitize_text_field');

        $sf = [];
        foreach ($vars as $key => $value)
        {
            if (strpos($key, 'sf-') === false) continue;

            $parameter = substr($key, 3);

            // Attribute
            if (strpos($parameter, 'att-') !== false)
            {
                if (!isset($sf['attributes'])) $sf['attributes'] = [];

                $parameter = substr($parameter, 4);
                if (strpos($parameter, 'price-bt-') !== false)
                {
                    if (!isset($sf['attributes']['price-bt'])) $sf['attributes']['price-bt'] = '';
                    $sf['attributes']['price-bt'] .= sanitize_text_field($value) . ':';
                }
                else if (strpos($parameter, 'grb-') !== false)
                {
                    if (!isset($sf['attributes'][substr($parameter, 0, -4)])) $sf['attributes'][substr($parameter, 0, -4)] = '';
                    $sf['attributes'][substr($parameter, 0, -4)] .= sanitize_text_field($value) . ':';
                }
                else $sf['attributes'][$parameter] = is_array($value) ? $value : sanitize_text_field($value);
            }
            // ACF Fields
            else if (strpos($parameter, 'acf-') !== false)
            {
                $parameter = substr($parameter, 4);
                if (!isset($sf['acf_values'])) $sf['acf_values'] = [];

                if (strpos($parameter, '-ara-') !== false)
                {
                    if (!isset($sf['acf_values'][substr($parameter, 0, -4)])) $sf['acf_values'][substr($parameter, 0, -4)] = '';
                    $sf['acf_values'][substr($parameter, 0, -4)] .= sanitize_text_field($value) . ':';
                }
                else $sf['acf_values'][$parameter] = is_array($value) ? $value : sanitize_text_field($value);
            }
            // Radius
            else if (strpos($parameter, 'circle-') !== false)
            {
                if (!isset($sf['circle'])) $sf['circle'] = [];

                $parameter = substr($parameter, 7);
                $sf['circle'][$parameter] = sanitize_text_field($value);
            }
            else if (in_array($parameter, ['s', 'shortcode']))
            {
                $sf[$parameter] = sanitize_text_field(urldecode($value));
            }
            else if (in_array($parameter, ['adults-eq', 'children-eq']))
            {
                $sf[substr($parameter, 0, -3)] = sanitize_text_field($value);
            }
            else if ($parameter === 'period' && trim($value) !== '')
            {
                // Dates
                $ex = explode(' - ', sanitize_text_field($value));

                // Main Library
                $main = new LSD_Main();

                // Format
                $settings = LSD_Options::settings();
                $format = isset($settings['datepicker_format']) && trim($settings['datepicker_format']) ? $settings['datepicker_format'] : 'yyyy-mm-dd';

                $sf[$parameter] = [
                    isset($ex[0]) ? $main->standardize_format($ex[0], $format) : '',
                    isset($ex[1]) ? $main->standardize_format($ex[1], $format) : '',
                ];
            }
            else
            {
                if (in_array($parameter, ['label', 'location', 'tag', 'category', 'feature'])) $parameter = 'listdom-' . $parameter;

                $values = [];
                if (is_array($value))
                {
                    foreach ($value as $term)
                    {
                        if (trim($term) === '') continue;

                        if (is_numeric($term)) $values[] = sanitize_text_field($term);
                        else $values[] = LSD_Taxonomies::id(sanitize_text_field($term), $parameter);
                    }
                }

                // Force to Integer
                if (!is_array($value) && trim($value) != '' && !is_numeric($value)) $value = LSD_Taxonomies::id($value, $parameter);

                $sf[$parameter] = is_array($value) && count($values) ? $values : sanitize_text_field($value);
            }
        }

        if (isset($sf['attributes']['price-bt'])) $sf['attributes']['price-bt'] = trim($sf['attributes']['price-bt'], ': ');

        return count($sf) ? $sf : $default;
    }

    public static function taxonomies(): array
    {
        return [
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_LOCATION,
            LSD_Base::TAX_TAG,
            LSD_Base::TAX_FEATURE,
            LSD_Base::TAX_LABEL,
        ];
    }

    public static function postTypes(): array
    {
        return [
            LSD_Base::PTYPE_LISTING,
            LSD_Base::PTYPE_SHORTCODE,
            LSD_Base::PTYPE_SEARCH,
            LSD_Base::PTYPE_NOTIFICATION,
        ];
    }

    public static function isPro(): bool
    {
        return class_exists(LSDADDPRO::class) || class_exists(\LSDPACPRO\Base::class);
    }

    public static function isLite(): bool
    {
        return !LSD_Base::isPro();
    }

    public static function upgradeMessage($type = 'long'): string
    {
        // Include Function
        if (!function_exists('is_plugin_active')) require_once ABSPATH . 'wp-admin/includes/plugin.php';

        // Whether Listdom Pro installed or Not
        $installed = is_plugin_active('listdom-pro/listdom.php');
        if ($installed)
        {
            if ($type === 'short') return sprintf(
                /* translators: %s: Link to the Listdom Pro activation page. */
                esc_html__("Activate to the %s first!", 'listdom'),
                '<a href="' . LSD_Base::getActivationURL() . '"><strong>' . esc_html__('Listdom Pro', 'listdom') . '</strong></a>'
            );
            else if ($type === 'tiny') return sprintf(
                /* translators: %s: Link to the license activation page. */
                esc_html__("%s needed!", 'listdom'),
                '<a href="' . LSD_Base::getActivationURL() . '"><strong>' . esc_html__('License activation', 'listdom') . '</strong></a>'
            );
            else return sprintf(
                /* translators: 1: Current product version label, 2: Link to activate Listdom Pro. */
                esc_html__("You're using %1\$s of listdom. You should activate the %2\$s to enjoy all the features!", 'listdom'),
                '<strong>' . esc_html__('Basic Version', 'listdom') . '</strong>',
                '<a href="' . LSD_Base::getActivationURL() . '"><strong>' . esc_html__('Listdom Pro', 'listdom') . '</strong></a>'
            );
        }
        else
        {
            if ($type === 'short') return sprintf(
                /* translators: %s: Link to purchase the Listdom Pro add-on. */
                esc_html__("Upgrade to the %s first!", 'listdom'),
                '<a href="' . LSD_Base::getWebiliaShopURL() . '" target="_blank"><strong>' . esc_html__('Pro Add-on', 'listdom') . '</strong></a>'
            );
            else if ($type === 'tiny') return sprintf(
                /* translators: %s: Link to the upgrade page. */
                esc_html__("%s needed!", 'listdom'),
                '<a href="' . LSD_Base::getWebiliaShopURL() . '" target="_blank"><strong>' . esc_html__('Upgrade', 'listdom') . '</strong></a>'
            );
            else return sprintf(
                /* translators: 1: Current product version label, 2: Link to upgrade to the pro add-on. */
                esc_html__("You're using %1\$s of listdom. You should upgrade to the %2\$s to enjoy all features of listdom!", 'listdom'),
                '<strong>' . esc_html__('Basic Version', 'listdom') . '</strong>',
                '<a href="' . LSD_Base::getWebiliaShopURL() . '" target="_blank"><strong>' . esc_html__('Pro Add-on', 'listdom') . '</strong></a>'
            );
        }
    }

    public static function missFeatureMessage($feature = null, $multiple = false, $html = true): string
    {
        // Include Function
        if (!function_exists('is_plugin_active')) require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $feature_html = $feature
            ? ($html ? '<strong>' . esc_html($feature) . '</strong>' : $feature)
            : ($multiple ? esc_html__('These features', 'listdom') : esc_html__('This feature', 'listdom'));

        // Whether Listdom Pro installed or Not
        $installed = is_plugin_active('listdom-pro/listdom.php');
        if ($installed)
        {
            if ($multiple) return sprintf(
                /* translators: 1: Feature names, 2: Link to activate Listdom Pro. */
                esc_html__('%1$s are included in the pro add-on. You should activate the %2$s now to enjoy all its features!', 'listdom'),
                $feature_html,
                '<a href="' . LSD_Base::getActivationURL() . '"><strong>' . esc_html__('Listdom Pro', 'listdom') . '</strong></a>'
            );
            else return sprintf(
                /* translators: 1: Feature name, 2: Link to activate Listdom Pro. */
                esc_html__('%1$s is included in the pro add-on. You should activate the %2$s now to enjoy all its features!', 'listdom'),
                $feature_html,
                '<a href="' . LSD_Base::getActivationURL() . '"><strong>' . esc_html__('Listdom Pro', 'listdom') . '</strong></a>'
            );
        }
        else
        {
            if ($multiple) return sprintf(
                /* translators: 1: Feature names, 2: Link to purchase the pro add-on. */
                esc_html__('%1$s are included in the pro add-on. You can upgrade to the %2$s now to enjoy all of the features of the Listdom!', 'listdom'),
                $feature_html,
                '<a href="' . LSD_Base::getWebiliaShopURL() . '" target="_blank"><strong>' . esc_html__('Pro Add-on', 'listdom') . '</strong></a>'
            );
            else return sprintf(
                /* translators: 1: Feature name, 2: Link to purchase the pro add-on. */
                esc_html__('%1$s is included in the pro add-on. You can upgrade to the %2$s now to enjoy all of the features of the Listdom!', 'listdom'),
                $feature_html,
                '<a href="' . LSD_Base::getWebiliaShopURL() . '" target="_blank"><strong>' . esc_html__('Pro Add-on', 'listdom') . '</strong></a>'
            );
        }
    }

    public static function missAddonMessage($addon = '', $feature = ''): string
    {
        return sprintf(
            /* translators: 1: Add-on name link, 2: Feature name. */
            esc_html__('Activate the %1$s add-on to use the %2$s feature.', 'listdom'),
            ('<a href="' . LSD_Base::getAddonURL($addon) . '" target="_blank"><strong>' . esc_html($addon) . '</strong></a>'),
            '<strong>' . $feature . '</strong>'
        );
    }

    public static function optionalAddonsMessage(array $addon_features = []): string
    {
        $addons = '';
        $features = '';
        $count = count($addon_features);

        $a = 1;
        foreach ($addon_features as $addon_feature)
        {
            $a++;
            $addons .= '<a href="' . LSD_Base::getAddonURL($addon_feature[0]) . '" target="_blank"><strong>' . ucfirst($addon_feature[0]) . '</strong></a>' . ($a === $count ? esc_html__(', and', 'listdom') . ' ' : ', ');
            $features .= '<strong>' . $addon_feature[1] . '</strong>' . ($a === count($addon_features) ? esc_html__(', and', 'listdom') . ' ' : ', ');
        }

        return sprintf(
            esc_html(
                /* translators: 1: Feature names, 2: Add-on names. */
                _n('To use the %1$s feature, you need to install %2$s add-on.', 'To use the %1$s features, you need to install %2$s add-ons.', $count, 'listdom')
            ),
            trim($features, ', '),
            trim($addons, ', ')
        );
    }

    public static function human_comma_separate(array $words = []): string
    {
        $string = '';

        $count = count($words);
        $a = 1;
        foreach ($words as $word)
        {
            $a++;
            $string .= '<strong>' . ucfirst($word) . '</strong>' . ($a === $count ? esc_html__(', and', 'listdom') . ' ' : ', ');
        }

        return trim($string, ', ');
    }

    public static function getActivationURL(): string
    {
        return admin_url('admin.php?page=listdom&tab=activation');
    }

    public static function getAddonURL($addon): string
    {
        return 'https://listdom.net/products/listdom-' . str_replace(' ', '-', strtolower($addon)) . '-addon';
    }

    public static function getAccountURL(): string
    {
        return self::addUtmParameters('https://listdom.net/my-account/');
    }

    public static function getUpgradeURL(): string
    {
        return LSD_Base::getWebiliaShopURL();
    }

    public static function getWebiliaShopURL(): string
    {
        return 'https://api.webilia.com/go/shop';
    }

    public static function getManageLicensesURL(): string
    {
        return 'https://api.webilia.com/go/my-account';
    }

    public static function getSupportURL(): string
    {
        return self::addUtmParameters('https://listdom.net/support/');
    }

    public static function getMobileApp(): string
    {
        return self::addUtmParameters('https://listdom.net/products/listdom-mobile-app/');
    }

    public static function getListdomDocsURL(): string
    {
        return self::addUtmParameters('https://api.webilia.com/go/listdom-docs');
    }

    public static function getListdomWelcomeWizardUrl(): string
    {
        return admin_url('admin.php?page=' . LSD_Base::WELCOME_SLUG);
    }

    public static function addUtmParameters(string $url): string
    {
        // Main
        $main = new LSD_Main();

        return $main->add_qs_vars([
            'utm_source' => 'Listdom+dashboard',
            'utm_medium' => 'text',
            'utm_campaign' => 'Listdom+dashboard',
        ], $url);
    }

    /**
     * @return array
     */
    public static function addons()
    {
        return apply_filters('lsd_addons', []);
    }

    /**
     * Returns list of add-ons / products that needs license activation
     *
     * @return array
     */
    public static function products(): array
    {
        return apply_filters('lsd_products', []);
    }

    public static function getShortcodes($minified = false): array
    {
        $query = ['post_type' => LSD_Base::PTYPE_SHORTCODE, 'posts_per_page' => '-1'];
        $shortcodes = get_posts($query);

        if ($minified)
        {
            $rendered = [];
            foreach ($shortcodes as $shortcode)
            {
                $rendered[] = [
                    'id' => $shortcode->ID,
                    'title' => $shortcode->post_title,
                ];
            }

            return $rendered;
        }

        return $shortcodes;
    }

    public static function isPastFromInstallationTime($seconds = 86400): bool
    {
        $installation_time = get_option('lsd_installed_at', null);
        if (!$installation_time) return false;

        return ($installation_time + $seconds) < time();
    }

    public function current_url(): string
    {
        // get $_SERVER
        $server = wp_unslash($_SERVER);
        if (!is_array($server)) $server = [];

        // Check protocol
        $https = isset($server['HTTPS']) && is_string($server['HTTPS']) ? sanitize_text_field($server['HTTPS']) : '';
        $is_https = in_array(strtolower($https), ['on', '1'], true);
        $scheme = $is_https ? 'https' : 'http';

        // Get domain
        if (isset($server['HTTP_HOST']) && is_string($server['HTTP_HOST'])) $site_domain = sanitize_text_field($server['HTTP_HOST']);
        else if (isset($server['SERVER_NAME']) && is_string($server['SERVER_NAME'])) $site_domain = sanitize_text_field($server['SERVER_NAME']);
        else $site_domain = '';

        $request_uri = isset($server['REQUEST_URI']) && is_string($server['REQUEST_URI']) ? sanitize_text_field($server['REQUEST_URI']) : '';
        $request_uri = $request_uri !== '' ? '/' . ltrim($request_uri, '/') : '/';

        if ($site_domain === '') return home_url($request_uri, $scheme);

        // Return full URL
        return $scheme . '://' . $site_domain . $request_uri;
    }

    public function remove_qs_var($key, $url = '')
    {
        if (is_null($url) || trim($url) === '') $url = $this->current_url();

        return remove_query_arg($key, $url);
    }

    public function add_qs_var($key, $value, $url = ''): string
    {
        if (is_null($url) || trim($url) === '') $url = $this->current_url();

        return add_query_arg($key, $value, $url);
    }

    public function update_qs_var($key, $value, $url = ''): string
    {
        if (is_null($url) || trim($url) === '') $url = $this->current_url();

        return add_query_arg($key, $value, remove_query_arg($key, $url));
    }

    public function add_qs_vars($vars, $url = ''): string
    {
        if (is_null($url) || trim($url) === '') $url = $this->current_url();

        return add_query_arg($vars, $url);
    }

    public static function str_random($length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $count = strlen($characters);

        $string = '';
        for ($i = 0; $i < $length; $i++) $string .= $characters[wp_rand(0, $count - 1)];

        return $string;
    }

    public function tax_checkboxes($args): string
    {
        $taxonomy = $args['taxonomy'] ?? LSD_Base::TAX_CATEGORY;
        $hide_empty = isset($args['hide_empty']) && $args['hide_empty'];
        $parent = $args['parent'] ?? 0;
        $current = $args['current'] ?? [];
        $name = $args['name'] ?? 'lsd_tax';
        $id_prefix = $args['id_prefix'] ?? '';

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => $hide_empty,
            'parent' => $parent,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $output = '';
        foreach ($terms as $term)
        {
            $output .= '<li>';
            $output .= '<input type="checkbox" name="' . esc_attr($name) . '[' . esc_attr($term->term_id) . ']" id="lsd_categories_' . esc_attr($id_prefix . $term->term_id) . '" value="1" ' . (isset($current[$term->term_id]) && $current[$term->term_id] ? 'checked="checked"' : '') . '><label for="lsd_categories_' . esc_attr($id_prefix . $term->term_id) . '">' . esc_html($term->name) . '</label>';

            $children = get_term_children($term->term_id, $taxonomy);
            if (is_array($children) and count($children))
            {
                $output .= '<ul class="lsd-children">';
                $output .= $this->tax_checkboxes([
                    'taxonomy' => $taxonomy,
                    'parent' => $term->term_id,
                    'current' => $current,
                    'name' => $name,
                    'id_prefix' => $id_prefix,
                ]);
                $output .= '</ul>';
            }

            $output .= '</li>';
        }

        return $output;
    }

    public function admin_id()
    {
        return apply_filters('lsd_admin_id', 1);
    }

    public static function minimize($number)
    {
        if ($number < 1000) return round($number);
        else if ($number < 100000) return round($number / 1000, 1) . 'K';
        else if ($number < 1000000) return round($number / 1000) . 'K';
        else return round($number / 1000000, 2) . 'M';
    }

    public static function indexify($array): array
    {
        $trimmed = [];
        foreach ($array as $key => $value)
        {
            if (is_numeric($key)) $trimmed[$key] = $value;
        }

        return $trimmed;
    }

    public static function getPostStatusLabel($status, $override = [])
    {
        // Return From Provided Labels
        if (isset($override[$status])) return $override[$status];

        // Return WP Label
        $obj = get_post_status_object($status);
        return $obj->label;
    }

    public static function stars($stars): string
    {
        // Ensure numeric
        $stars = is_numeric($stars) ? (float) $stars : 0;

        // Rounded Stars
        $rounded = round($stars);

        $output = '<span class="lsd-stars" title="' . ($stars ? sprintf(
            /* translators: %s: Rating value. */
            esc_html__('%s from 5', 'listdom'),
            $stars
        ) : '') . '">';
        for ($i = 1; $i <= 5; $i++) $output .= '<span><i class="lsd-icon ' . ($rounded >= $i ? 'fas fa-star' : 'far fa-star') . '"></i></span>';
        $output .= '</span>';

        return $output;
    }

    public static function date_format()
    {
        return get_option('date_format');
    }

    public static function time_format()
    {
        return get_option('time_format');
    }

    public static function datetime_format(): string
    {
        return LSD_Base::date_format() . ' ' . LSD_Base::time_format();
    }

    public static function date($time, $format = null)
    {
        if (!is_numeric($time)) $time = LSD_Base::strtotime($time);
        if (is_null($format)) $format = LSD_Base::date_format();

        if (function_exists('wp_date')) return wp_date($format, $time);
        else return gmdate($format, $time);
    }

    public static function time($time, $format = null)
    {
        if (!is_numeric($time)) $time = LSD_Base::strtotime($time);
        if (is_null($format)) $format = LSD_Base::time_format();

        if (function_exists('wp_date')) return wp_date($format, $time);
        else return gmdate($format, $time);
    }

    public static function datetime($time, $format = null)
    {
        if (!is_numeric($time)) $time = LSD_Base::strtotime($time);
        if (is_null($format)) $format = LSD_Base::datetime_format();

        if (function_exists('wp_date')) return wp_date($format, $time);
        return gmdate($format, $time);
    }

    public static function strtotime($time)
    {
        try
        {
            $date = new DateTime($time, wp_timezone());
            return $date->format('U');
        }
        catch (Exception $ex)
        {
            return strtotime($time);
        }
    }

    public static function diff($start, $end, $type = 'days')
    {
        try
        {
            $s = new DateTime($start);
            $e = new DateTime($end);

            // Interval
            $interval = $s->diff($e);

            // End is before Start!
            if (isset($interval->invert) and $interval->invert) return false;

            // Return Diff
            return $interval->{$type} ?? false;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * We don't use WP get_edit_post_link function because it returns empty string
     * for guest users, but we need it to be included in the email notifications
     * @param int $id
     * @return mixed|void
     */
    public function get_edit_post_link(int $id = 0)
    {
        $post = get_post($id);
        $post_type_object = get_post_type_object($post->post_type);

        $link = admin_url(sprintf($post_type_object->_edit_link . '&action=edit', $post->ID));
        return apply_filters('get_edit_post_link', $link, $post->ID, 'display');
    }

    public static function add_uncategorized(): int
    {
        // Uncategorized
        $category = wp_create_term(
            esc_html__('Uncategorized', 'listdom'),
            LSD_Base::TAX_CATEGORY
        );

        $id = 0;
        if (is_array($category) && isset($category['term_id']))
        {
            $id = (int) $category['term_id'];

            update_term_meta($id, 'lsd_icon', 'fa fa-ban');
            update_term_meta($id, 'lsd_color', '#000000');
        }

        return $id;
    }

    public function render_price($price, $currency, $minimized = false): string
    {
        // Return Free if price is 0
        if ($price == '0') return esc_html__('Free', 'listdom');

        // General Settings
        $settings = LSD_Options::settings();

        $thousand_separator = ',';
        $decimal_separator = '.';
        $currency_sign_position = isset($settings['currency_position']) && trim($settings['currency_position']) ? $settings['currency_position'] : 'before';

        // Force to double
        if (is_string($price)) $price = (double) $price;

        // Disable decimals if not needed
        if (strpos($price, '.') === false) $decimal_separator = false;

        // Minimize
        if ($minimized) $rendered = $this->minimize($price);
        else
        {
            $rendered = number_format($price, ($decimal_separator === false ? 0 : 2), ($decimal_separator === false ? '' : $decimal_separator), $thousand_separator);
        }

        $sign = $this->get_currency_sign($currency);

        if ($currency_sign_position == 'after') $rendered = $rendered . $sign;
        else if ($currency_sign_position == 'after_ws') $rendered = $rendered . ' ' . $sign;
        else if ($currency_sign_position == 'before_ws') $rendered = $sign . ' ' . $rendered;
        else $rendered = '<span>' . $sign . '</span>' . $rendered;

        return $rendered;
    }

    public function get_currency_sign($currency)
    {
        $currencies = $this->get_currencies();

        $sign = array_search($currency, $currencies);
        $sign = (trim($sign) ? $sign : $currency);

        return apply_filters('lsd_currency_sign', $sign, $currency, $currencies);
    }

    /**
     * Returns Listdom currencies
     * @return array
     */
    public static function get_currencies(): array
    {
        $currencies = [
            '$' => 'USD',
            '€' => 'EUR',
            '£' => 'GBP',
            'CHF' => 'CHF',
            'CAD' => 'CAD',
            'AUD' => 'AUD',
            'JPY' => 'JPY',
            'SEK' => 'SEK',
            'GEL' => 'GEL',
            'AFN' => 'AFN',
            'ALL' => 'ALL',
            'DZD' => 'DZD',
            'AOA' => 'AOA',
            'ARS' => 'ARS',
            'AMD' => 'AMD',
            'AWG' => 'AWG',
            'AZN' => 'AZN',
            'BSD' => 'BSD',
            'BHD' => 'BHD',
            'BBD' => 'BBD',
            'BYR' => 'BYR',
            'BZD' => 'BZD',
            'BMD' => 'BMD',
            'BTN' => 'BTN',
            'BOB' => 'BOB',
            'BAM' => 'BAM',
            'BWP' => 'BWP',
            'BRL' => 'BRL',
            'BND' => 'BND',
            'BGN' => 'BGN',
            'BIF' => 'BIF',
            'KHR' => 'KHR',
            'CVE' => 'CVE',
            'KYD' => 'KYD',
            'XAF' => 'XAF',
            'CLP' => 'CLP',
            'COP' => 'COP',
            'KMF' => 'KMF',
            'CDF' => 'CDF',
            'NZD' => 'NZD',
            'CRC' => 'CRC',
            'HRK' => 'HRK',
            'CUC' => 'CUC',
            'CUP' => 'CUP',
            'CZK' => 'CZK',
            'DKK' => 'DKK',
            'DJF' => 'DJF',
            'RD$' => 'DOP',
            'XCD' => 'XCD',
            'EGP' => 'EGP',
            'ERN' => 'ERN',
            'EEK' => 'EEK',
            'ETB' => 'ETB',
            'FKP' => 'FKP',
            'FJD' => 'FJD',
            'GMD' => 'GMD',
            'GHS' => 'GHS',
            'GIP' => 'GIP',
            'GTQ' => 'GTQ',
            'GNF' => 'GNF',
            'GYD' => 'GYD',
            'HTG' => 'HTG',
            'HNL' => 'HNL',
            'HKD' => 'HKD',
            'HUF' => 'HUF',
            'ISK' => 'ISK',
            'INR' => 'INR',
            'IDR' => 'IDR',
            'IRR' => 'IRR',
            'IQD' => 'IQD',
            'ILS' => 'ILS',
            'JMD' => 'JMD',
            'JOD' => 'JOD',
            'KZT' => 'KZT',
            'KES' => 'KES',
            'KWD' => 'KWD',
            'KGS' => 'KGS',
            'LAK' => 'LAK',
            'LVL' => 'LVL',
            'LBP' => 'LBP',
            'LSL' => 'LSL',
            'LRD' => 'LRD',
            'LYD' => 'LYD',
            'LTL' => 'LTL',
            'MOP' => 'MOP',
            'MKD' => 'MKD',
            'MGA' => 'MGA',
            'MWK' => 'MWK',
            'MYR' => 'MYR',
            'MVR' => 'MVR',
            'MRO' => 'MRO',
            'MUR' => 'MUR',
            'MXN' => 'MXN',
            'MDL' => 'MDL',
            'MNT' => 'MNT',
            'MAD' => 'MAD',
            'MZN' => 'MZN',
            'MMK' => 'MMK',
            'NAD' => 'NAD',
            'NRs.' => 'NPR',
            'ANG' => 'ANG',
            'TWD' => 'TWD',
            'NIO' => 'NIO',
            'NGN' => 'NGN',
            'KPW' => 'KPW',
            'NOK' => 'NOK',
            'OMR' => 'OMR',
            'PKR' => 'PKR',
            'PAB' => 'PAB',
            'PGK' => 'PGK',
            'PYG' => 'PYG',
            'PEN' => 'PEN',
            'PHP' => 'PHP',
            'PLN' => 'PLN',
            'QAR' => 'QAR',
            'CNY' => 'CNY',
            'RON' => 'RON',
            'RUB' => 'RUB',
            'RWF' => 'RWF',
            'SHP' => 'SHP',
            'SVC' => 'SVC',
            'WST' => 'WST',
            'SAR' => 'SAR',
            'RSD' => 'RSD',
            'SCR' => 'SCR',
            'SLL' => 'SLL',
            'SGD' => 'SGD',
            'SBD' => 'SBD',
            'SOS' => 'SOS',
            'ZAR' => 'ZAR',
            'KRW' => 'KRW',
            'LKR' => 'LKR',
            'SDG' => 'SDG',
            'SRD' => 'SRD',
            'SZL' => 'SZL',
            'SYP' => 'SYP',
            'STD' => 'STD',
            'TJS' => 'TJS',
            'TZS' => 'TZS',
            'THB' => 'THB',
            'TOP' => 'TOP',
            'PRB' => 'PRB',
            'TTD' => 'TTD',
            'TND' => 'TND',
            'TRY' => 'TRY',
            'TMT' => 'TMT',
            'TVD' => 'TVD',
            'UGX' => 'UGX',
            'UAH' => 'UAH',
            'AED' => 'AED',
            'UYU' => 'UYU',
            'UZS' => 'UZS',
            'VUV' => 'VUV',
            'VEF' => 'VEF',
            'VND' => 'VND',
            'XOF' => 'XOF',
            'YER' => 'YER',
            'ZMK' => 'ZMK',
            'ZWL' => 'ZWL',
        ];

        return apply_filters('lsd_currencies', $currencies);
    }

    public static function get_font_icons(): array
    {
        return LSD_Icons::get();
    }


    public static function get_map_styles()
    {
        $mapstyles = [
            '' => esc_html__('Default', 'listdom'),
            'apple-maps-esque' => esc_html__('Apple Maps Esque', 'listdom'),
            'blue-essence' => esc_html__('Blue Essence', 'listdom'),
            'blue-water' => esc_html__('Blue Water', 'listdom'),
            'CDO' => esc_html__('CDO', 'listdom'),
            'facebook' => esc_html__('Facebook', 'listdom'),
            'intown-map' => esc_html__('Intown Map', 'listdom'),
            'light-dream' => esc_html__('Light Dream', 'listdom'),
            'midnight' => esc_html__('Midnight', 'listdom'),
            'pale-down' => esc_html__('Pale Down', 'listdom'),
            'shades-of-grey' => esc_html__('Shades of Grey', 'listdom'),
            'subtle-grayscale' => esc_html__('Subtle Grayscale', 'listdom'),
            'ultra-light' => esc_html__('Ultra Light', 'listdom'),
        ];

        // Apply Filters
        return apply_filters('lsd_mapstyles', $mapstyles);
    }

    public static function get_clustering_icons()
    {
        $icons = [
            'img/cluster1/m' => esc_html__('Classic Bubbles', 'listdom'),
            'img/cluster2/m' => esc_html__('Modern Bubbles', 'listdom'),
        ];

        // Apply Filters
        return apply_filters('lsd_clustering_icons', $icons);
    }

    public static function get_pagination_methods(): array
    {
        return [
            'loadmore' => esc_html__('Load More Button', 'listdom'),
            'scroll' => esc_html__('Infinite Scroll', 'listdom'),
            'numeric' => esc_html__('Numeric', 'listdom'),
            'disabled' => esc_html__('Disabled', 'listdom'),
        ];
    }

    public static function get_listing_link_methods(): array
    {
        return [
            'normal' => esc_html__('Same Window', 'listdom'),
            'blank' => esc_html__('New Window', 'listdom'),
            'lightbox' => esc_html__('Open in Lightbox', 'listdom'),
            'right-panel' => esc_html__('Right Panel', 'listdom'),
            'left-panel' => esc_html__('Left Panel', 'listdom'),
            'bottom-panel' => esc_html__('Bottom Panel', 'listdom'),
            'disabled' => esc_html__('Disabled', 'listdom'),
        ];
    }

    public static function get_fonts(): array
    {
        return [
            'lato' => [
                'label' => esc_html__('Lato', 'listdom'),
                'code' => 'Lato',
                'family' => 'Lato',
            ],
            'roboto' => [
                'label' => esc_html__('Roboto', 'listdom'),
                'code' => 'Roboto',
                'family' => 'Roboto',
            ],
            'raleway' => [
                'label' => esc_html__('Raleway', 'listdom'),
                'code' => 'Raleway',
                'family' => 'Raleway',
            ],
            'open-sans' => [
                'label' => esc_html__('Open Sans', 'listdom'),
                'code' => 'Open Sans',
                'family' => 'Open Sans',
            ],
            'poppins' => [
                'label' => esc_html__('Poppins', 'listdom'),
                'code' => 'Poppins',
                'family' => 'Poppins',
            ],
        ];
    }

    public static function get_colors(): array
    {
        return [
            '#f2cc0c',
            '#ffa600',
            '#f76e09',
            '#f43d3d',
            '#ee0218',
            '#b2093c',
            '#ff1991',
            '#d40fb7',
            '#11c35d',
            '#188437',
            '#009e96',
            '#2b93ff',
        ];
    }

    public function iframe($body)
    {
        $class = 'lsd-iframe-page';

        // Generate output
        ob_start();
        include lsd_template('iframe.php');
        return ob_get_clean();
    }

    public function response(array $response)
    {
        echo wp_json_encode($response, JSON_NUMERIC_CHECK);
        exit;
    }

    public static function log($message, $append = true)
    {
        // Log File
        $path = LSD_LOG_DIR . 'debug.log';

        // Delete File
        if (LSD_File::exists($path) && !$append) LSD_File::delete($path);

        // Create Folder
        if (!LSD_Folder::exists(LSD_LOG_DIR)) LSD_Folder::create(LSD_LOG_DIR);

        // Convert to String
        if (is_array($message) || is_object($message)) $message = print_r($message, true);

        // Write to File
        if ($append) LSD_File::append($path, $message . "\n\n");
        else LSD_File::write($path, $message . "\n\n");
    }

    /**
     * Checks a post status
     *
     * @param int $post_id The ID of the post to check.
     * @param string $status
     *
     * @return bool
     */
    public static function is_post(int $post_id, string $status): bool
    {
        $post = get_post($post_id);
        return $post && $post->post_status === $status;
    }

    /**
     * un-trash the post
     *
     * @param int $post_id The ID of the post to un-trash.
     */
    public static function untrash_post(int $post_id)
    {
        wp_untrash_post($post_id);
    }

    /**
     * updates the post status
     *
     * @param int $post_id The ID of the post to check.
     * @param string $status Optional. The status to set. Defaults to 'publish'.
     */
    public static function update_post_status(int $post_id, string $status = 'publish')
    {
        wp_update_post(['ID' => $post_id, 'post_status' => $status]);
    }

    /**
     * @param string $title
     * @param string $post_type
     * @return WP_Post|null
     */
    public static function get_post_by_title(string $title, string $post_type = 'post'): ?WP_Post
    {
        // Query
        $query = new WP_Query([
            'title' => $title,
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'inherit', 'trash'],
        ]);

        $post = null;
        if ($query->have_posts())
        {
            while ($query->have_posts())
            {
                $query->the_post();
                $post = get_post();
            }
        }

        // Reset Data
        LSD_LifeCycle::reset();

        // Return Post
        return $post;
    }

    /**
     * Disable Post Comments
     *
     * @return void
     */
    public static function disable_comments()
    {
        add_filter('comments_template', function ($template)
        {
            return lsd_template('empty.php');
        });
    }

    public static function is_theme_installed($theme): bool
    {
        $theme = wp_get_theme($theme);
        return $theme->exists();
    }

    public static function is_current_theme($theme): bool
    {
        $current_theme = wp_get_theme();
        return $current_theme->get_template() === $theme;
    }

    public static function remove_protocols(string $url = ''): string
    {
        // Remove Protocols
        $url = str_replace('http://', '', $url);
        $url = str_replace('https://', '', $url);
        $url = str_replace('tel:', '', $url);
        $url = str_replace('mailto:', '', $url);

        // Return
        return trim($url, '/ ');
    }

    public static function get_ptypes_and_tax($exclude = false): array
    {
        $post_types = get_post_types(['public' => true], 'objects');
        $taxonomies = get_taxonomies([], 'objects');

        $result = [];
        foreach ($post_types as $post_type_key => $post_type)
            $result[$post_type_key] = ['post_type' => $post_type, 'taxonomies' => []];

        // Assign taxonomies to post types
        foreach ($taxonomies as $taxonomy_key => $taxonomy)
        {
            foreach ($taxonomy->object_type as $object_type)
            {
                if (isset($result[$object_type])) $result[$object_type]['taxonomies'][$taxonomy_key] = $taxonomy;
            }
        }

        if ($exclude) $result = self::exclude_ptypes_and_tax($result);

        return $result;
    }

    private static function exclude_ptypes_and_tax(array $result): array
    {
        // Define exclusions
        $exclusions = [
            'post_types' => array_merge(['attachment'], self::postTypes()),
            'taxonomies' => array_merge(['post_format', 'product_visibility', 'product_shipping_class', 'elementor_library_type', 'elementor_library_category', 'product_type'], self::taxonomies()),
        ];

        // Exclude post types
        foreach ($exclusions['post_types'] as $excluded_post_type) unset($result[$excluded_post_type]);

        // Exclude taxonomies
        foreach ($result as $key => $data)
        {
            foreach ($data['taxonomies'] as $tax_key => $taxonomy)
            {
                if (in_array($tax_key, $exclusions['taxonomies'])) unset($result[$key]['taxonomies'][$tax_key]);
            }
        }

        return $result;
    }

    public static function is_listdom_page(): bool
    {
        $status = false;

        // Current post type
        $post_type = get_post_type();

        // Check if the post type matches any Listdom-related post types
        if ($post_type && in_array($post_type, LSD_Base::postTypes())) $status = true;

        $query = get_queried_object();

        // Determine the taxonomy if it's a taxonomy archive
        $taxonomy = null;
        if ($query && isset($query->taxonomy)) $taxonomy = $query->taxonomy;

        // Check if the current taxonomy matches any Listdom-related taxonomies
        if ($taxonomy && in_array($taxonomy, LSD_Base::taxonomies())) $status = true;

        return apply_filters('lsd_is_listdom_page', $status);
    }

    public static function is_url(string $url): bool
    {
        if (strtolower(esc_url_raw($url)) === strtolower($url)) return true;

        return false;
    }
}
