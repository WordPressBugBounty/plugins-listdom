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
            $reference = $this->logConnectFailure($e, 'Start');
            LSD_Webilia_Connect::setNotice('error', sprintf(
                /* translators: 1: reason for connection failure, 2: support reference */
                __('Unable to start the Webilia connection: %1$s Reference: %2$s', 'listdom'),
                $this->connectFailureReason($e),
                $reference
            ));
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
        catch (Throwable $e)
        {
            $reference = $this->logConnectFailure($e, 'Completion');
            LSD_Webilia_Connect::setNotice('error', sprintf(
                /* translators: 1: reason for connection failure, 2: support reference */
                __('Unable to complete the Webilia connection: %1$s Reference: %2$s', 'listdom'),
                $this->connectFailureReason($e),
                $reference
            ));
        }
        wp_safe_redirect($return_url);
        exit;
    }

    private function logConnectFailure(Throwable $e, string $step): string
    {
        $reference = substr(wp_generate_uuid4(), 0, 8);

        // Keep credentials and server paths out of logs. The exception location
        // identifies unexpected local errors without recording request data.
        error_log(sprintf('[Listdom Webilia Connect] %s failed (reference %s): %s (%d) at %s:%d', $step, $reference, get_class($e), (int) $e->getCode(), basename($e->getFile()), $e->getLine()));

        return $reference;
    }

    private function connectFailureReason(Throwable $e): string
    {
        $message = $e->getMessage();

        if (in_array($message, [
            'Your Webilia Connect request has expired. Please try again.',
            'This Webilia Connect authorization code is invalid or expired.',
        ], true)) return __('The request is missing or expired. Start again from this WordPress admin and finish within 10 minutes.', 'listdom');

        if (in_array($message, [
            'Webilia Connect returned an invalid state.',
            'The Webilia Connect PKCE verification failed.',
        ], true)) return __('The connection verification failed. Please start the connection again.', 'listdom');

        if (in_array($message, [
            'Webilia Connect can only connect the current website.',
            'This Webilia Connect request belongs to a different website.',
            'Webilia Connect returned a connection for a different website.',
        ], true)) return __('The connection did not match this website. Check the WordPress Address in Settings > General, then try again.', 'listdom');

        if ($e instanceof \Webilia\Connect\Exception\TransientException)
        {
            return sprintf(
                /* translators: %s: reason returned by the Webilia HTTP client */
                __('The request to Webilia failed: %s. Try again; if it continues, ask your host to check outbound HTTPS access to api.webilia.com.', 'listdom'),
                rtrim(sanitize_text_field($message), '. ')
            );
        }

        if ($message === 'Unable to replace the existing Webilia connection until its previous credential is revoked.')
        {
            return __('The previous connection could not be revoked. Check outgoing HTTP requests before trying again.', 'listdom');
        }

        if ($e instanceof \Webilia\Connect\Exception\RequestException)
        {
            $detail = rtrim(sanitize_text_field($message), '. ');
            if ($e->getCode() <= 0) return sprintf(
                /* translators: %s: reason returned by the Webilia API */
                __('Webilia rejected the connection request: %s. Please try again or contact Webilia support with this error.', 'listdom'),
                $detail
            );

            return sprintf(
                /* translators: 1: HTTP status code, 2: reason returned by the Webilia API */
                __('Webilia rejected the connection request (HTTP %1$d): %2$s. Please try again or contact Webilia support with this error.', 'listdom'),
                (int) $e->getCode(),
                $detail
            );
        }

        if (in_array($message, [
            'Webilia Connect requires a writable WordPress content directory.',
            'Unable to create the Webilia connection encryption key.',
            'Unable to secure the Webilia connection encryption key.',
            'The existing Webilia connection encryption key cannot be read securely.',
        ], true)) return __('This website could not create or read its private Webilia key. Ask your host to check write access to wp-content and the permissions of .webilia-connect-key.php.', 'listdom');

        if (in_array($message, [
            'Unable to save the pending Webilia Connect request.',
            'Unable to remove the pending Webilia Connect request.',
            'Unable to save the Webilia connection credential.',
        ], true)) return __('This website could not save the connection in its WordPress database. Ask your host to check database writes, then try again.', 'listdom');

        if ($message === 'Unable to obtain the Webilia Connect storage lock.')
        {
            return __('This website could not access its connection data. Try again; if it continues, ask your host to check WordPress database writes.', 'listdom');
        }

        return __('Check the WordPress and PHP error logs for the reference below.', 'listdom');
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
