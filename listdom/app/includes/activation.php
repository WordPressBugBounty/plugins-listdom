<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom License Activation Class.
 *
 * @class LSD_Activation
 * @version    1.0.0
 */
class LSD_Activation extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_action('lsd_admin_dashboard_tabs', [$this, 'tab']);
        add_action('lsd_admin_dashboard_contents', [$this, 'content']);

        // Activate
        add_action('wp_ajax_lsd_activation', [$this, 'activate']);

        // Deactivate
        add_action('wp_ajax_lsd_deactivation', [$this, 'deactivate']);

        // Add License Required Badges
        add_filter('lsd_backend_main_badge', function (int $counter)
        {
            return $counter + self::getLicenseActivationRequiredCount();
        });
    }

    public function tab($tab)
    {
        // List of Products
        $products = LSD_Base::products();

        // No products
        if (!count($products)) return;

        // Display Activation Tab?
        if (!apply_filters('lsd_display_activation_tab', true)) return;

        // Tab Title
        $title = esc_html__('License Activation', 'listdom');
        if ($b = apply_filters('lsd_backend_main_badge', 0)) $title .= ' <span class="update-plugins count-' . $b . '"><span class="update-count">' . $b . '</span></span>';

        echo '<a class="nav-tab ' . ($tab === 'activation' ? 'nav-tab-active' : '') . '" href="' . esc_url(admin_url('admin.php?page=listdom&tab=activation')) . '">' . $title . '</a>';
    }

    public function content($tab)
    {
        // It's not Activation Tab
        if ($tab !== 'activation') return;

        // List of Products
        $products = LSD_Base::products();

        // No products
        if (!count($products)) return;

        // Display Activation Tab?
        if (!apply_filters('lsd_display_activation_tab', true)) return;

        $this->include_html_file('menus/dashboard/tabs/activation.php', [
            'parameters' => [
                'products' => $products,
            ],
        ]);
    }

    /**
     * @return void
     */
    public function activate()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Product Key
        $key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, $key . '_activation_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Data
        $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
        $basename = isset($_POST['basename']) ? sanitize_text_field($_POST['basename']) : '';

        // Licensing Handler
        $licensing = new LSD_Plugin_Licensing([
            'basename' => $basename,
            'prefix' => $key,
        ]);

        // Activation
        list($status, $message) = $licensing->activate($license_key);

        // Reset Transient
        LSD_Licensing::reset($basename);

        // Print the response
        $this->response(['success' => $status, 'message' => $message]);
    }

    /**
     * @return void
     */
    public function deactivate()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Product Key
        $key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, $key . '_deactivation_form')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Data
        $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
        $basename = isset($_POST['basename']) ? sanitize_text_field($_POST['basename']) : '';

        // Licensing Handler
        $licensing = new LSD_Plugin_Licensing([
            'basename' => $basename,
            'prefix' => $key,
        ]);

        // Activation
        list($status, $message) = $licensing->deactivate($license_key);

        // Reset Transient
        LSD_Licensing::reset($basename);

        // Print the response
        $this->response(['success' => $status, 'message' => $message]);
    }

    public static function getLicenseActivationRequiredCount(): int
    {
        return (int) apply_filters('lsd_license_activation_required', 0);
    }
}
