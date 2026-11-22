<?php

class LSD_Security_Account extends LSD_Base
{
    public function init(): void
    {
        add_shortcode('listdom-account-security', [$this, 'render']);
        add_action('wp_ajax_lsd_account_security', [$this, 'ajax']);
        add_action('wp_ajax_nopriv_lsd_account_security', [$this, 'ajax']);
        add_action('login_form_lsd-email-change', [$this, 'confirm_email_screen']);
        add_action('manage_export-personal-data_custom_column', [$this, 'privacy_column'], 10, 2);
        add_action('manage_erase-personal-data_custom_column', [$this, 'privacy_column'], 10, 2);
        add_filter('manage_export-personal-data_columns', [$this, 'privacy_columns']);
        add_filter('manage_erase-personal-data_columns', [$this, 'privacy_columns']);
    }

    public static function input(string $key, ?array $source = null): string
    {
        $source = $source ?? $_POST;
        return isset($source[$key]) && is_string($source[$key]) ? wp_unslash($source[$key]) : '';
    }

    public function render($atts = []): string
    {
        if (LSD_Auth::requested($atts)) return LSD_Auth::frontend($atts);
        if (LSD_Security_Recovery::requested() && !LSD_Security_Recovery::dropdown()) return (new LSD_Security_Recovery())->frontend();

        if (!is_user_logged_in()) return (new LSD_Shortcodes_Auth())->auth(['redirect' => self::url(), 'explicit_redirect' => true]);
        (new LSD_Assets())->site(true);
        nocache_headers();
        $user = wp_get_current_user();
        $security = LSD_Security::settings();
        $instance = wp_unique_id('lsd-security-');
        $pending_email = $security['sections']['email']['enabled'] ? get_user_meta($user->ID, '_lsd_email_change', true) : [];
        $totp_enabled = LSD_Security_Totp::enabled($user->ID);
        $deadline = LSD_Security_Totp::deadline($user->ID);
        $sessions = $security['sections']['sessions']['enabled'] ? wp_get_all_sessions() : [];
        $current_session = $security['sections']['sessions']['enabled'] ? WP_Session_Tokens::get_instance($user->ID)->get(wp_get_session_token()) : null;
        ob_start();
        include lsd_template('auth/security.php');
        return ob_get_clean();
    }

    public static function url(): string
    {
        $page_id = (int) LSD_Security::settings()['page'];
        $page = $page_id ? get_post($page_id) : null;
        if (!$page || $page->post_type !== 'page' || $page->post_status !== 'publish') return '';
        return (string) get_permalink($page);
    }

    public function ajax(): void
    {
        nocache_headers();
        $user_id = get_current_user_id();
        if (!$user_id) $this->response(['success' => 0, 'code' => 'lsd_authentication_required', 'message' => __('Please sign in again.', 'listdom')]);
        if (!wp_verify_nonce(self::input('_wpnonce'), 'lsd_account_security')) $this->response(['success' => 0, 'code' => 'lsd_nonce', 'message' => __('Security nonce is missing or invalid.', 'listdom')]);
        $result = $this->perform($user_id, $_POST);
        if (is_wp_error($result))
        {
            $data = $result->get_error_data();
            $this->response(['success' => 0, 'code' => $result->get_error_code(), 'message' => $result->get_error_message(), 'retry_after' => is_array($data) ? ($data['retry_after'] ?? 0) : 0, 'countdown' => is_array($data) ? ($data['countdown'] ?? null) : null]);
        }
        $this->response(['success' => 1] + $result);
    }

    /** Internal own-account operation; AJAX derives user_id only from the session. */
    public function perform(int $user_id, array $input)
    {
        $operation = self::input('operation', $input);
        $allowed = ['password', 'email_request', 'email_resend', 'email_cancel', 'totp_begin', 'totp_confirm', 'totp_disable', 'totp_codes', 'sessions_others', 'sessions_current', 'privacy_export', 'privacy_erasure', 'privacy_delete'];
        if (!in_array($operation, $allowed, true)) return new WP_Error('lsd_security_operation', __('Invalid account security action.', 'listdom'));
        $user = get_userdata($user_id);
        if (!$user) return new WP_Error('lsd_authentication_required', __('Please sign in again.', 'listdom'));
        $settings = LSD_Security::settings();
        if (strpos($operation, 'email_') === 0 && empty($settings['sections']['email']['enabled'])) return new WP_Error('lsd_email_change_disabled', __('Email changes are disabled by the administrator.', 'listdom'));
        if (strpos($operation, 'privacy_') === 0 && empty($settings['sections']['privacy']['enabled'])) return new WP_Error('lsd_privacy_disabled', __('Privacy requests are disabled by the administrator.', 'listdom'));
        if (strpos($operation, 'sessions_') === 0 && empty($settings['sections']['sessions']['enabled'])) return new WP_Error('lsd_sessions_disabled', __('Session management is disabled by the administrator.', 'listdom'));
        if (strpos($operation, 'totp_') === 0 && !LSD_Security_Totp::offered($user_id)) return new WP_Error('lsd_totp_disabled', __('Two-factor authentication is disabled by the administrator.', 'listdom'));
        if ($operation !== 'sessions_current')
        {
            $auth = LSD_Security::reauthenticate($user_id, self::input('current_password', $input), self::input('code', $input));
            if (is_wp_error($auth)) return $auth;
        }
        if ($operation === 'password')
        {
            $password = self::input('password', $input);
            $valid = LSD_Security::password($password, self::input('password_confirmation', $input));
            if (is_wp_error($valid)) return $valid;
            wp_set_password($password, $user_id);
            LSD_Security::revoke_credentials($user_id);
            wp_clear_auth_cookie();
            do_action('lsd_user_password_reset', $user_id);
            return ['message' => __('Password updated. Please sign in again.', 'listdom'), 'redirect' => wp_login_url()];
        }
        if ($operation === 'email_request') return self::request_email($user_id, self::input('email', $input));
        if ($operation === 'email_resend')
        {
            $pending = get_user_meta($user_id, '_lsd_email_change', true);
            if (!is_array($pending)) return new WP_Error('lsd_email_change_missing', __('There is no pending email change.', 'listdom'));
            return self::request_email($user_id, $pending['email']);
        }
        if ($operation === 'email_cancel')
        {
            delete_user_meta($user_id, '_lsd_email_change');
            return ['message' => __('Pending email change cancelled.', 'listdom'), 'refresh' => 1];
        }
        if (strpos($operation, 'totp_') === 0)
        {
            // Enrolled accounts already consumed the same budget during reauthentication.
            if ($operation !== 'totp_begin' && !LSD_Security_Totp::enabled($user_id))
            {
                $limit = LSD_Security_Rate::second_factor($user_id, 'account:' . $user_id);
                if (is_wp_error($limit)) return $limit;
            }
            if ($operation === 'totp_begin')
            {
                $setup = LSD_Security_Totp::begin($user_id);
                return is_wp_error($setup) ? $setup : ['message' => __('Scan the QR code or enter the setup key in your authenticator, then confirm its code.', 'listdom'), 'setup' => $setup];
            }
            if ($operation === 'totp_confirm')
            {
                $codes = LSD_Security_Totp::confirm($user_id, self::input('enrollment_code', $input));
                return is_wp_error($codes) ? $codes : ['message' => __('Authenticator enabled. Save these recovery codes; they are shown only once. Each line is a separate, single-use code; enter only one when signing in.', 'listdom'), 'codes' => $codes, 'refresh' => 1];
            }
            if (!LSD_Security_Totp::enabled($user_id)) return new WP_Error('lsd_totp_disabled', __('Two-factor authentication is not enabled.', 'listdom'));
            if ($operation === 'totp_codes') return ['message' => __('Save your replacement recovery codes. Previous codes no longer work. Each line is a separate, single-use code; enter only one when signing in.', 'listdom'), 'codes' => LSD_Security_Totp::recovery_codes($user_id)];
            if (LSD_Security_Totp::enforced($user_id)) return new WP_Error('lsd_totp_enforced', __('Your user role requires two-factor authentication.', 'listdom'));
            LSD_Security_Totp::reset($user_id);
            wp_clear_auth_cookie();
            return ['message' => __('Authenticator disabled. Please sign in again.', 'listdom'), 'redirect' => wp_login_url()];
        }
        if ($operation === 'sessions_others')
        {
            wp_destroy_other_sessions();
            LSD_Security_API::revoke($user_id);
            update_user_meta($user_id, '_lsd_security_epoch', wp_generate_password(32, false));
            return ['message' => __('Other sessions and Listdom API credentials have been revoked.', 'listdom'), 'refresh' => 1];
        }
        if ($operation === 'sessions_current')
        {
            wp_logout();
            do_action('lsd_security_logged_out', $user_id);
            return ['message' => __('You are signed out.', 'listdom'), 'redirect' => wp_login_url()];
        }
        $export = $operation === 'privacy_export';
        if (empty($settings['sections']['privacy'][$export ? 'export' : 'erasure'])) return new WP_Error('lsd_privacy_disabled', __('This request type is disabled by the administrator.', 'listdom'));
        if (self::input('confirm', $input) !== '1') return new WP_Error('lsd_confirmation_required', __('Please confirm this request.', 'listdom'));
        $limit = LSD_Security_Rate::check('privacy', (string) $user_id);
        if (is_wp_error($limit)) return $limit;
        $request = wp_create_user_request($user->user_email, $export ? 'export_personal_data' : 'remove_personal_data', ['lsd_delete_account' => $operation === 'privacy_delete', 'lsd_user_id' => $user_id]);
        if (is_wp_error($request)) return $request;
        if (!function_exists('wp_send_user_request')) require_once ABSPATH . 'wp-admin/includes/user.php';
        $sent = wp_send_user_request($request);
        if (is_wp_error($sent)) return $sent;
        do_action('lsd_security_privacy_requested', $request, $user_id, $operation);
        return ['message' => __('Check your email to confirm the request. An administrator will review it; your account and business records have not been deleted.', 'listdom')];
    }

    public static function validate_email(int $user_id, string $email)
    {
        if (empty(LSD_Security::settings()['sections']['email']['enabled'])) return new WP_Error('lsd_email_change_disabled', __('Email changes are disabled by the administrator.', 'listdom'));
        if (!is_email($email)) return new WP_Error('lsd_email_invalid', __('Enter a valid email address.', 'listdom'));
        $owner = email_exists($email);
        if ($owner && (int) $owner !== $user_id) return new WP_Error('lsd_email_conflict', __('This email address is unavailable.', 'listdom'));
        if (is_multisite() && is_email_address_unsafe($email)) return new WP_Error('lsd_email_invalid', __('This email domain is not allowed by the network.', 'listdom'));
        return true;
    }

    /** Call only after password/second-factor reauthentication in the adapter. */
    public static function request_email(int $user_id, string $email)
    {
        $email = sanitize_email($email);
        $valid = self::validate_email($user_id, $email);
        if (is_wp_error($valid)) return $valid;
        $user = get_userdata($user_id);
        if (strcasecmp($user->user_email, $email) === 0) return new WP_Error('lsd_email_unchanged', __('This is already your current email address.', 'listdom'));
        $limit = LSD_Security_Rate::check('email_change', (string) $user_id);
        if (is_wp_error($limit)) return $limit;
        $data = ['user' => $user_id, 'email' => $email, 'old_email' => $user->user_email, 'fingerprint' => LSD_Security_Challenge::fingerprint($user)];
        $token = LSD_Security_State::issue('email', $data, DAY_IN_SECONDS);
        $data['hash'] = hash('sha256', $token);
        $data['expires'] = time() + DAY_IN_SECONDS;
        $previous = get_user_meta($user_id, '_lsd_email_change', true);
        update_user_meta($user_id, '_lsd_email_change', $data);
        $url = add_query_arg(['action' => 'lsd-email-change', 'key' => $token], site_url('wp-login.php', 'login'));
        if (!LSD_Notifications_Security::email_change($user_id, 'confirmation', $user->user_email, $email, $url))
        {
            if ($previous) update_user_meta($user_id, '_lsd_email_change', $previous);
            else delete_user_meta($user_id, '_lsd_email_change', $data);
            return new WP_Error('lsd_email_delivery', __('The confirmation email could not be sent. Please try again later.', 'listdom'));
        }
        LSD_Notifications_Security::email_change($user_id, 'requested', $user->user_email, $email);
        return ['message' => __('Check your new address to confirm the change. Your current address is still active.', 'listdom'), 'refresh' => 1];
    }

    public static function confirm_email(string $token)
    {
        $data = LSD_Security_State::read('email', $token);
        $user = $data ? get_userdata($data['user']) : false;
        $pending = $user ? get_user_meta($user->ID, '_lsd_email_change', true) : [];
        if (!$user || !is_array($pending) || !hash_equals($pending['hash'] ?? '', hash('sha256', $token)) || !hash_equals($data['fingerprint'], LSD_Security_Challenge::fingerprint($user))) return new WP_Error('lsd_email_change_expired', __('This email-change link is invalid, expired, or superseded.', 'listdom'));
        $valid = self::validate_email($user->ID, $data['email']);
        if (is_wp_error($valid)) return $valid;
        // WordPress emails have no unique database index. Serialize competing claims.
        $claim = LSD_Security::reserve_email($data['email']);
        if (is_wp_error($claim)) return $claim;
        try
        {
            $valid = self::validate_email($user->ID, $data['email']);
            if (is_wp_error($valid)) return $valid;
            if (!LSD_Security_State::consume('email', $token) || !delete_user_meta($user->ID, '_lsd_email_change', $pending)) return new WP_Error('lsd_email_change_expired', __('This email-change link has already been used or cancelled.', 'listdom'));
            $saved = wp_update_user(['ID' => $user->ID, 'user_email' => $data['email']]);
            if (is_wp_error($saved)) return $saved;
            LSD_Security_API::revoke($user->ID);
            update_user_meta($user->ID, LSD_User::META_VERIFICATION_STATUS, 'verified');
            LSD_Notifications_Security::email_change($user->ID, 'completed', $data['old_email'], $data['email']);
            do_action('lsd_security_email_changed', $user->ID, $data['old_email'], $data['email']);
            return true;
        }
        finally { LSD_Security_State::delete($claim['name'], $claim['record']); }
    }

    public function confirm_email_screen(): void
    {
        nocache_headers();
        header('Referrer-Policy: no-referrer');
        $token = self::input('key', $_REQUEST);
        $enabled = !empty(LSD_Security::settings()['sections']['email']['enabled']);
        $message = $enabled ? __('Confirm your new email address. Your previous address remains active until you confirm.', 'listdom') : __('Email changes are disabled by the administrator.', 'listdom');
        $complete = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            check_admin_referer('lsd_email_change_' . $token);
            $result = self::confirm_email($token);
            $complete = !is_wp_error($result);
            $message = $complete ? __('Your email address has been updated.', 'listdom') : $result->get_error_message();
        }
        login_header(esc_html__('Confirm Email Change', 'listdom'));
        echo '<p role="status">' . esc_html($message) . '</p>';
        if (!$complete && $enabled)
        {
            echo '<form method="post">';
            wp_nonce_field('lsd_email_change_' . $token);
            echo '<input type="hidden" name="key" value="' . esc_attr($token) . '"><button type="submit" class="button button-primary">' . esc_html__('Confirm email address', 'listdom') . '</button></form>';
        }
        echo '<p><a href="' . esc_url(wp_login_url()) . '">' . esc_html__('Sign in', 'listdom') . '</a></p>';
        login_footer();
        exit;
    }

    public function privacy_columns(array $columns): array
    {
        $columns['lsd_account_intent'] = __('Listdom Account', 'listdom');
        return $columns;
    }

    public function privacy_column(string $column, $request): void
    {
        if ($column !== 'lsd_account_intent') return;
        if ($request && !empty($request->request_data['lsd_delete_account'])) echo esc_html__('Account deletion requested. Review ownership, subscriptions, payments, and retained records before deleting the user manually.', 'listdom');
    }
}
