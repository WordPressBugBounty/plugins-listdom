<?php

class LSD_Menus_Settings extends LSD_Menus
{
    public $subtab;

    public function __construct()
    {
        // Initialize the Menu
        $this->init();
    }

    public function init()
    {
        add_action('wp_ajax_lsd_save_settings', [$this, 'save_settings']);
        add_action('wp_ajax_lsd_save_dashboard', [$this, 'save_dashboard']);
        add_action('wp_ajax_lsd_save_customizer', [$this, 'save_customizer']);
        add_action('wp_ajax_lsd_reset_customizer', [$this, 'reset_customizer']);
        add_action('wp_ajax_lsd_save_details_page', [$this, 'save_details_page']);
        add_action('wp_ajax_lsd_save_addons', [$this, 'save_addons']);
        add_action('wp_ajax_lsd_save_advanced', [$this, 'save_advanced']);
        add_action('wp_ajax_lsd_save_auth', [$this, 'save_auth']);

        // API
        add_action('wp_ajax_lsd_api_add_token', [$this, 'token_add']);
        add_action('wp_ajax_lsd_api_remove_token', [$this, 'token_remove']);
        add_action('wp_ajax_lsd_save_api', [$this, 'save_api']);

        // AI
        add_action('wp_ajax_lsd_ai_add_profile', [$this, 'ai_add']);
        add_action('wp_ajax_lsd_ai_remove_profile', [$this, 'ai_remove']);
        add_action('wp_ajax_lsd_save_ai', [$this, 'save_ai']);
    }

    public function output()
    {
        // Get the current tab
        $this->tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        $this->subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : $this->get_default_subtab();

        // Generate output
        $this->include_html_file('menus/settings/tpl.php');
    }

    public function get_default_subtab(): string
    {
        if ($this->tab === 'frontend-dashboard') return 'pages';
        if ($this->tab === 'auth') return 'authentication';
        if ($this->tab === 'advanced') return 'assets-loading';

        return 'general';
    }


    public function addons_tab(): string
    {
        [$addons, $default] = $this->get_addons_default();
        $subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : $default;

        $output = '';
        foreach ($addons as $key => $addon)
        {
            // No Settings for Addon
            if (!apply_filters('lsd_addons_has_settings_'.$key, true)) continue;

            $name = ucwords(strtolower($addon['name']));

            $output .= '<li class="lsd-nav-tab ' . esc_attr($key === $subtab ? 'lsd-nav-tab-active' : '') . '" data-key="' . esc_attr($key) . '">';
            $output .= esc_html__($name, 'listdom-'. $key);
            $output .= '</li>';
        }

        return $output;
    }

    public function get_addons_default(): array
    {
        $addons = LSD_Base::addons();
        uasort($addons, function($a, $b)
        {
            return strcasecmp($a['name'], $b['name']);
        });

        return [$addons, array_key_first($addons)];
    }

    public function check_access(string $action = 'lsd_settings_form', string $capability = 'manage_options')
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, $action)) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can($capability)) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);
    }

    public function save_settings()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Separate social settings
        $social_settings = array_filter($lsd, function ($key)
        {
            return in_array($key, [
                'twitter',
                'pinterest',
                'linkedin',
                'facebook',
                'instagram',
                'whatsapp',
                'youtube',
                'tiktok',
                'telegram',
            ]);
        }, ARRAY_FILTER_USE_KEY);

        // Remove social settings from main settings
        $lsd = array_diff_key($lsd, $social_settings);

        // Save Settings
        LSD_Options::merge('lsd_settings', $lsd);

        // Save social settings
        update_option('lsd_socials', $social_settings);

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Add WordPress flush rewrite rules in to-do list
        LSD_RewriteRules::todo();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_advanced()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Save Styles
        LSD_Options::merge('lsd_styles', $lsd);

        // Save Settings
        LSD_Options::merge('lsd_settings', $lsd);

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Add WordPress flush rewrite rules in to do list
        LSD_RewriteRules::todo();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_dashboard()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, function (&$value)
        {
            $value = stripslashes($value);
            $value = sanitize_text_field($value);
        });

        // Get current Listdom options
        $current = get_option('lsd_settings', []);
        if (is_string($current) && trim($current) === '') $current = [];

        // Clear existing custom menus values if present and sanitize shortcode
        if (isset($current['dashboard_menu_custom'])) $current['dashboard_menu_custom'] = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save final options
        update_option('lsd_settings', $final);

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_customizer()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, function (&$value)
        {
            $value = stripslashes($value);
            $value = sanitize_text_field($value);
        });

        // Get current options
        $current = get_option('lsd_customizer', []);
        if (is_string($current) && trim($current) === '') $current = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save final options
        update_option('lsd_customizer', $final);

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function reset_customizer()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $category = $_POST['category'] ?? '';

        // Reset Only a Category
        if ($category)
        {
            // Get current options
            $current = get_option('lsd_customizer', []);
            if (is_string($current) && trim($current) === '') $current = [];

            // Category Path
            $paths = explode('.', trim($category, '. '));
            $paths = array_reverse($paths);

            // Default Options
            $default = [];
            $prev = [];

            $p = 1;
            foreach ($paths as $path)
            {
                $default = [];
                $default[$path] = $p === 1 ? LSD_Customizer::defaults($category) : $prev;

                $prev = $default;
                $p++;
            }

            // Merge new options with previous options
            $final = array_replace_recursive($current, $default);

            // Save final options
            update_option('lsd_customizer', $final);
        }
        // Reset All
        else
        {
            update_option('lsd_customizer', LSD_Customizer::defaults());
        }

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_details_page()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        $pattern = '';
        foreach ($lsd['elements'] as $key => $element)
        {
            // Element is disabled
            if (!isset($element['enabled']) || !$element['enabled']) continue;

            $pattern .= '{' . $key . '}';
        }

        // Save single listing pattern
        update_option('lsd_details_page_pattern', trim($pattern));

        // Save Settings
        LSD_Options::merge('lsd_details_page', $lsd);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_addons()
    {
        // Check Access
        $this->check_access('lsd_addons_form');

        // Get Listdom options
        $lsd = $_POST['addons'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Save options
        update_option('lsd_addons', $lsd);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function token_add()
    {
        // Check Access
        $this->check_access('lsd_api_add_token');

        // Get API Options
        $api = LSD_Options::api();

        // Add New Token
        $api['tokens'][] = ['name' => esc_html__('New Token', 'listdom'), 'key' => LSD_Base::str_random(40)];

        // Save options
        update_option('lsd_api', $api);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function token_remove()
    {
        // Check Access
        $this->check_access('lsd_api_remove_token');

        // Index
        $i = $_POST['i'] ?? '';

        // Invalid Index
        if (trim($i) === '') $this->response(['success' => 0, 'code' => 'INVALID_INDEX']);

        // Get API Options
        $api = LSD_Options::api();

        // Remove Token
        unset($api['tokens'][$i]);

        // Save options
        update_option('lsd_api', $api);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_api()
    {
        // Check Access
        $this->check_access('lsd_api_form');

        // Get API options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Save options
        update_option('lsd_api', $lsd);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function ai_add()
    {
        // Check Access
        $this->check_access('lsd_ai_add_profile');

        // Get AI Options
        $ai = LSD_Options::ai();

        // Add New Profile
        $ai['profiles'][] = [
            'id' => LSD_Base::str_random(10),
            'name' => esc_html__('New Profile', 'listdom'),
            'model' => LSD_AI_Models::def(),
            'api_key' => ''
        ];

        // Save options
        update_option('lsd_ai', $ai);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function ai_remove()
    {
        // Check Access
        $this->check_access('lsd_ai_remove_profile');

        // Index
        $i = $_POST['i'] ?? '';

        // Invalid Index
        if (trim($i) === '') $this->response(['success' => 0, 'code' => 'INVALID_INDEX']);

        // Get AI Options
        $ai = LSD_Options::ai();

        // Remove Profile
        unset($ai['profiles'][$i]);

        // Save options
        update_option('lsd_ai', $ai);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_ai()
    {
        // Check Access
        $this->check_access('lsd_ai_form');

        // Get AI options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Save options
        update_option('lsd_ai', $lsd);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_auth()
    {
        // Check Access
        $this->check_access('lsd_auth_form');

        // Get Auth
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Save Auth
        update_option('lsd_auth', $lsd);

        // Get Settings
        $settings = $_POST['settings'] ?? [];

        // Sanitization
        array_walk_recursive($settings, 'sanitize_text_field');

        // Save Settings
        LSD_Options::merge('lsd_settings', $settings);

        // Print the response
        $this->response(['success' => 1]);
    }
}
