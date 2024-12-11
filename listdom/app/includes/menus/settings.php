<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Settings Menu Class.
 *
 * @class LSD_Menus_Settings
 * @version    1.0.0
 */
class LSD_Menus_Settings extends LSD_Menus
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize the Menu
        $this->init();
    }

    public function init()
    {
        add_action('wp_ajax_lsd_save_settings', [$this, 'save_settings']);
        add_action('wp_ajax_lsd_save_dashboard', [$this, 'save_dashboard']);
        add_action('wp_ajax_lsd_save_socials', [$this, 'save_socials']);
        add_action('wp_ajax_lsd_save_styles', [$this, 'save_styles']);
        add_action('wp_ajax_lsd_save_details_page', [$this, 'save_details_page']);
        add_action('wp_ajax_lsd_save_addons', [$this, 'save_addons']);

        // API
        add_action('wp_ajax_lsd_api_add_token', [$this, 'token_add']);
        add_action('wp_ajax_lsd_api_remove_token', [$this, 'token_remove']);
        add_action('wp_ajax_lsd_save_api', [$this, 'save_api']);
        add_action('wp_ajax_lsd_save_auth', [$this, 'save_auth']);
    }

    public function output()
    {
        // Get the current tab
        $this->tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';

        // Generate output
        $this->include_html_file('menus/settings/tpl.php');
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

        // Get current Listdom options
        $current = get_option('lsd_settings', []);
        if (is_string($current) && trim($current) === '') $current = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save final options
        update_option('lsd_settings', $final);

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

    public function save_socials()
    {
        // Check Access
        $this->check_access('lsd_socials_form');

        // Get Listdom options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Save options
        update_option('lsd_socials', $lsd);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_styles()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Get current Listdom options
        $current = get_option('lsd_styles', []);
        if (is_string($current) && trim($current) === '') $current = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save final options
        update_option('lsd_styles', $final);

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

        // Save details page pattern
        update_option('lsd_details_page_pattern', trim($pattern));

        // Get current Listdom options
        $current = get_option('lsd_details_page', []);
        if (is_string($current) && trim($current) === '') $current = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save final options
        update_option('lsd_details_page', $final);

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

    public function save_auth()
    {
        // Check Access
        $this->check_access('lsd_auth_form');

        // Get Auth options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Save options
        update_option('lsd_auth', $lsd);

        // Print the response
        $this->response(['success' => 1]);
    }
}
