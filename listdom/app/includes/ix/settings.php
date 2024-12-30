<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom IX Settings.
 *
 * @class LSD_IX_Settings
 * @version    1.0.0
 */
class LSD_IX_Settings extends LSD_Base
{
    public function init()
    {
        add_action('wp_ajax_lsd_settings_export', [$this, 'export_request']);
        add_action('wp_ajax_lsd_settings_import', [$this, 'import_request']);
    }

    public function export_request()
    {
        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'ADMIN_ONLY']);

        // Verify that the nonce is valid
        if (!wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'lsd_settings_export')) $this->response(['success' => 0, 'code' => 'INVALID_REQUEST']);

        // Settings
        $settings = LSD_Options::settings();

        // Unset Private Data
        if (isset($settings['googlemaps_api_key'])) unset($settings['googlemaps_api_key']);
        if (isset($settings['mapbox_access_token'])) unset($settings['mapbox_access_token']);
        if (isset($settings['grecaptcha_sitekey'])) unset($settings['grecaptcha_sitekey']);
        if (isset($settings['grecaptcha_secretkey'])) unset($settings['grecaptcha_secretkey']);

        // Settings Content
        $content = [
            'settings' => $settings,
            'auth' => LSD_Options::auth(),
            'styles' => LSD_Options::styles(),
            'details_page' => LSD_Options::details_page(),
            'socials' => LSD_Options::socials(),
            'details_page_pattern' => LSD_Options::details_page_pattern(),
            'addons' => LSD_Options::addons(),
        ];

        // JSON
        $JSON = json_encode(apply_filters('lsd_ix_settings_export', $content));

        // Headers
        header('Content-type: application/txt');
        header('Content-Description: Listdom Settings');
        header('Content-Disposition: attachment; filename="lsd_settings_' . date('Y-m-d-H-i') . '.json"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');

        // Content
        print_r($JSON);
        exit;
    }

    public function import_request()
    {
        // Current User is not Permitted
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'ADMIN_ONLY']);

        // Verify that the nonce is valid
        if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'lsd_settings_import')) $this->response(['success' => 0, 'code' => 'INVALID_REQUEST']);

        $file = $_FILES['import'] ?? [];

        // No file
        if (!$file) $this->response(['success' => 0, 'message' => esc_html__('Please upload a file to import!', 'listdom')]);

        $ex = explode('.', $file['name']);
        $extension = end($ex);

        // Invalid Extension
        if (strtolower($extension) !== 'json') $this->response(['success' => 0, 'message' => esc_html__('Only JSON files are allowed!', 'listdom')]);

        // Check file size
        if ($file['size'] > 512000)
        {
            $this->response([
                'success' => 0,
                'message' => esc_html__('The uploaded json file exceeds the maximum allowed size of 500 KB.', 'listdom'),
            ]);
        }

        // Main Library
        $main = new LSD_Main();

        // Upload File
        $name = time() . '.' . $extension;
        $destination = $main->get_upload_path() . $name;

        if (move_uploaded_file($file['tmp_name'], $destination))
        {
            LSD_IX_Settings::import($destination);

            // Response
            $this->response(['success' => 1, 'code' => 'SUCCESS']);
        }

        // Response
        $this->response(['success' => 0, 'code' => 'UPLOAD_ERROR']);
    }

    public static function import(string $file, bool $delete = true): bool
    {
        // Read the Content
        $JSON = LSD_File::read($file);

        // Options
        $options = json_decode($JSON, true);

        // Merge Settings
        update_option('lsd_settings', array_merge(
            LSD_Options::settings(), isset($options['settings']) && is_array($options['settings']) ? $options['settings'] : []
        ));

        // Merge Auth
        update_option('lsd_auth', array_merge(
            LSD_Options::auth(), isset($options['auth']) && is_array($options['auth']) ? $options['auth'] : []
        ));

        // Merge Styles
        update_option('lsd_styles', array_merge(
            LSD_Options::styles(), isset($options['styles']) && is_array($options['styles']) ? $options['styles'] : []
        ));

        // Merge Details Page
        update_option('lsd_details_page', array_merge(
            LSD_Options::details_page(), isset($options['details_page']) && is_array($options['details_page']) ? $options['details_page'] : []
        ));

        // Merge Socials
        update_option('lsd_socials', array_merge(
            LSD_Options::socials(), isset($options['socials']) && is_array($options['socials']) ? $options['socials'] : []
        ));

        // Merge Details Page Pattern
        if (isset($options['details_page_pattern']) && trim($options['details_page_pattern'])) update_option('lsd_details_page_pattern', $options['details_page_pattern']);

        // Merge Addons
        update_option('lsd_addons', array_merge(
            LSD_Options::addons(), isset($options['addons']) && is_array($options['addons']) ? $options['addons'] : []
        ));

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Add WordPress flush rewrite rules in to do list
        LSD_RewriteRules::todo();

        // Delete Temp File
        if ($delete) LSD_File::delete($file);

        return true;
    }

    public static function get_export_url(): string
    {
        return admin_url('admin-ajax.php').'?action=lsd_settings_export&_wpnonce='.wp_create_nonce('lsd_settings_export');
    }
}
