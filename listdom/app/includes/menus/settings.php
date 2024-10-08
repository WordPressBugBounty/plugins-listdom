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

        // Initialize the menu
        $this->init();
    }

    public function init()
    {
        add_action('wp_ajax_lsd_save_settings', [$this, 'save_settings']);
        add_action('wp_ajax_lsd_save_slugs', [$this, 'save_slugs']);
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

    public function save_settings()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_settings_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

        // Get Listdom options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Get current Listdom options
        $current = get_option('lsd_settings', []);
        if (is_string($current) and trim($current) == '') $current = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save final options
        update_option('lsd_settings', $final);

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_slugs()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_settings_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

        // Get Listdom options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        foreach ($lsd as $key => $slug) $lsd[$key] = sanitize_title($slug);

        // Get current Listdom options
        $current = get_option('lsd_settings', []);
        if (is_string($current) and trim($current) == '') $current = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save final options
        update_option('lsd_settings', $final);

        // Add WordPress flush rewrite rules in to do list
        LSD_RewriteRules::todo();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_socials()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_socials_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

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
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_settings_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

        // Get Listdom options
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Get current Listdom options
        $current = get_option('lsd_styles', []);
        if (is_string($current) && trim($current) == '') $current = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save final options
        update_option('lsd_styles', $final);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_details_page()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_settings_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

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
        if (is_string($current) and trim($current) == '') $current = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save final options
        update_option('lsd_details_page', $final);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_addons()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_addons_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

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
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_api_add_token')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

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
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_api_remove_token')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

        // Index
        $i = $_POST['i'] ?? null;

        // Invalid Index
        if (trim($i) == '') $this->response(['success' => 0, 'code' => 'INVALID_INDEX']);

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
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_api_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

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
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_auth_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

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
