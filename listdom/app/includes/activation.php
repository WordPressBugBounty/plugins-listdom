<?php

class LSD_Activation extends LSD_Base
{
    public function init()
    {
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

    public function content()
    {
        // List of Products
        $products = LSD_Base::products();

        // Display Activation Tab?
        if (!apply_filters('lsd_display_activation_tab', true)) return;

        $this->include_html_file('menus/activation/tpl.php', [
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
        [$status, $message] = $licensing->activate($license_key);

        // Reset Transient
        LSD_Licensing::reset($basename);

        $products = LSD_Base::products();
        $product = $products[$key] ?? null;

        $content = '';
        if ($product)
        {
            $content = $this->include_html_file('menus/activation/addon.php', [
                'parameters' => [
                    'key' => $key,
                    'product' => $product,
                ],
                'return_output' => true,
            ]);
        }

        // Print the response
        $this->response(['success' => $status, 'message' => $message, 'content' => $content]);
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
        [$status, $message] = $licensing->deactivate($license_key);

        // Reset Transient
        LSD_Licensing::reset($basename);

        $products = LSD_Base::products();
        $product = $products[$key] ?? null;

        $content = '';
        if ($product)
        {
            $content = $this->include_html_file('menus/activation/addon.php', [
                'parameters' => [
                    'key' => $key,
                    'product' => $product,
                ],
                'return_output' => true,
            ]);
        }

        // Print the response
        $this->response(['success' => $status, 'message' => $message, 'content' => $content]);
    }

    public static function getLicenseActivationRequiredCount(): int
    {
        return (int) apply_filters('lsd_license_activation_required', 0);
    }
}
