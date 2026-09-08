<?php

class LSD_Activation extends LSD_Base
{
    public function init()
    {
        // Activate
        add_action('wp_ajax_lsd_activation', [$this, 'activate']);

        // Deactivate
        add_action('wp_ajax_lsd_deactivation', [$this, 'deactivate']);
        add_action('admin_post_lsd_webilia_connect_start', [$this, 'startConnect']);
        add_action('wp_ajax_lsd_webilia_connect_disconnect', [$this, 'disconnectConnect']);
        add_action('admin_init', [$this, 'completeConnect']);

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

        $this->include_html_file('menus/activation/tpl.php', [
            'parameters' => [
                'products' => $products,
                'connect_notice' => LSD_Webilia_Connect::notice(),
            ],
        ]);
    }

    public function startConnect(): void
    {
        if (!current_user_can('manage_options')) wp_die(esc_html__('You are not allowed to connect this website.', 'listdom'));
        check_admin_referer('lsd_webilia_connect_start');
        $return_url = LSD_Webilia_Connect::returnUrl(isset($_GET['return_to']) ? esc_url_raw(wp_unslash($_GET['return_to'])) : '');

        if (!LSD_Webilia_Connect::enabled())
        {
            LSD_Webilia_Connect::setNotice('info', esc_html__('Webilia Connect is temporarily unavailable for this website.', 'listdom'));
            wp_safe_redirect($return_url);
            exit;
        }
        try { wp_redirect(LSD_Webilia_Connect::begin($return_url)); exit; }
        catch (Throwable $e)
        {
            LSD_Webilia_Connect::setNotice('error', esc_html__('Unable to start the Webilia connection. Please try again.', 'listdom'));
            wp_safe_redirect($return_url);
            exit;
        }
    }

    public function completeConnect(): void
    {
        if (!is_admin() || !current_user_can('manage_options')) return;
        $code = isset($_GET['webilia_connect_code']) ? sanitize_text_field(wp_unslash($_GET['webilia_connect_code'])) : '';
        $state = isset($_GET['webilia_connect_state']) ? sanitize_text_field(wp_unslash($_GET['webilia_connect_state'])) : '';
        if ($code === '' || $state === '') return;
        $return_url = LSD_Webilia_Connect::pendingReturnUrl();

        if (!LSD_Webilia_Connect::enabled())
        {
            LSD_Webilia_Connect::setNotice('info', esc_html__('Webilia Connect is temporarily unavailable for this website.', 'listdom'));
        }
        else try
        {
            LSD_Webilia_Connect::complete($code, $state);
            LSD_Webilia_Connect::setNotice('success', esc_html__('This website is now connected to Webilia. Eligible Listdom products will activate automatically.', 'listdom'));
        }
        catch (Throwable $e) { LSD_Webilia_Connect::setNotice('error', esc_html__('Unable to complete the Webilia connection. Please start the connection again.', 'listdom')); }
        wp_safe_redirect($return_url);
        exit;
    }

    public function disconnectConnect(): void
    {
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to disconnect this website.', 'listdom')]);
        check_ajax_referer('lsd_webilia_connect_disconnect');
        $confirmation = isset($_POST['confirmation']) ? sanitize_text_field(wp_unslash($_POST['confirmation'])) : '';
        if (strtolower($confirmation) !== 'disconnect') $this->response(['success' => 0, 'message' => esc_html__('Type disconnect to confirm.', 'listdom')]);
        try
        {
            LSD_Webilia_Connect::disconnect();
            $this->response(['success' => 1, 'message' => esc_html__('This website has been disconnected from Webilia. Existing license-key activations were not changed.', 'listdom')]);
        }
        catch (Throwable $e)
        {
            // The connection remains intact unless the SDK has confirmed that
            // the API revoked the credential. Give the administrator a useful,
            // safe next step without exposing the opaque site credential.
            $this->response([
                'success' => 0,
                'message' => esc_html__('Webilia could not confirm that this website was disconnected. It remains connected; please try again.', 'listdom'),
            ]);
        }
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
        $reduce_license_badges = !LSD_Webilia_Connect::isAllowed($basename);

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
        $this->response(['success' => $status, 'message' => $message, 'content' => $content, 'reduce_license_badges' => $reduce_license_badges]);
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
