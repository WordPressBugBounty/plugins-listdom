<?php

/** Email-based authenticator recovery, shared by native and Listdom interfaces. */
class LSD_Security_Recovery extends LSD_Base
{
    private static array $frontend_result = [];

    public function init(): void
    {
        add_action('wp_ajax_lsd_auth_recovery', [$this, 'ajax']);
        add_action('wp_ajax_nopriv_lsd_auth_recovery', [$this, 'ajax']);
        add_action('template_redirect', [$this, 'frontend_request'], 0);
    }

    public static function requested(): bool
    {
        return LSD_Security_Account::input('lsd_totp_recovery', $_GET) === '1';
    }

    public static function dropdown(): bool
    {
        return self::requested() && LSD_Security_Account::input('lsd_recovery_dropdown', $_GET) === '1';
    }

    /** Mailbox bearer links must stay on this site's origin, even with custom redirect hosts. */
    private static function page(string $url): string
    {
        $url = wp_validate_redirect(esc_url_raw($url), '');
        $parts = wp_parse_url($url);
        $home = wp_parse_url(home_url('/'));
        if (!$parts || !$home || isset($parts['user']) || isset($parts['pass'])) return '';
        if (strtolower($parts['host'] ?? '') !== strtolower($home['host'] ?? '') || ($parts['scheme'] ?? '') !== ($home['scheme'] ?? '') || ($parts['port'] ?? 0) !== ($home['port'] ?? 0)) return '';
        if (strpos($parts['path'] ?? '', 'wp-login.php') !== false || strpos($parts['path'] ?? '', '/wp-admin') !== false) return '';
        return remove_query_arg(['lsd_challenge', 'lsd_recovery_key', 'lsd_totp_recovery', 'key'], preg_replace('/#.*$/', '', $url));
    }

    /** A supplied page denotes a Listdom presentation context, never authentication trust. */
    public static function url(string $key = '', string $page = ''): string
    {
        $auth = new LSD_Shortcodes_Auth();
        $frontend = $auth->get_login_url();
        if ($frontend === '' && $page !== '')
        {
            $options = LSD_Options::auth();
            $page_id = (int) ($options['auth']['login_page'] ?? 0);
            $configured = $page_id ? get_post($page_id) : null;
            if ($configured instanceof WP_Post && $configured->post_type === 'page' && $configured->post_status === 'publish') $frontend = add_query_arg('tab', 'login', get_permalink($configured));
        }
        if ($frontend !== '') $page = self::page($frontend);
        else $page = self::page($page);
        if ($page !== '')
        {
            $page = add_query_arg('lsd_totp_recovery', '1', $page);
            return $key !== '' ? add_query_arg('lsd_recovery_key', $key, $page) : $page;
        }
        $args = ['action' => 'lsd-totp-recovery'];
        // WordPress reserves the generic key parameter for its password-reset action.
        if ($key !== '') $args['lsd_recovery_key'] = $key;
        return add_query_arg($args, site_url('wp-login.php', 'login'));
    }

    public static function link(): string
    {
        $page = wp_doing_ajax() ? (string) wp_get_referer() : (new LSD_Base())->current_url();
        if (self::page($page) === '') $page = home_url('/');
        if (LSD_Security_Account::input('lsd_recovery_dropdown') === '1') $page = add_query_arg('lsd_recovery_dropdown', '1', $page);
        return self::url('', $page);
    }

    private static function state(string $key): array
    {
        $data = LSD_Security_State::read('totpreset', $key);
        $user = $data ? get_userdata($data['user']) : false;
        if (!$user || !LSD_Security_Totp::enabled($user->ID) || !hash_equals($data['fingerprint'], LSD_Security_Challenge::fingerprint($user)) || get_user_meta($user->ID, '_lsd_totp_email_reset', true) !== hash('sha256', $key)) return [];
        return $data;
    }

    public function perform(string $key, array $input)
    {
        if (!wp_verify_nonce(LSD_Security_Account::input('_wpnonce', $input), 'lsd_totp_email_recovery')) return new WP_Error('lsd_nonce', __('Security nonce is missing or invalid.', 'listdom'));
        if ($key !== '')
        {
            $data = self::state($key);
            if (!$data || !LSD_Security_State::consume('totpreset', $key)) return new WP_Error('lsd_totp_recovery_expired', __('This recovery link is invalid or expired.', 'listdom'));
            LSD_Security_Totp::reset((int) $data['user']);
            if (get_current_user_id() === (int) $data['user'])
            {
                wp_clear_auth_cookie();
                wp_set_current_user(0);
            }
            LSD_Notifications_Security::totp_recovery((int) $data['user'], 'completed');
            return ['complete' => 1, 'message' => __('Two-factor authentication was reset. Please sign in again.', 'listdom')];
        }
        $identifier = trim(LSD_Security_Account::input('user_login', $input));
        if ($identifier === '') return new WP_Error('lsd_identifier_required', __('Enter your username or email address.', 'listdom'));
        $limit = LSD_Security_Rate::check('totp_recovery', $identifier);
        if (is_wp_error($limit)) return $limit;
        $user = LSD_Security::find_user($identifier);
        $page = self::page(LSD_Security_Account::input('return_url', $input));
        LSD_Security::queue_mail('totp_reset', $user instanceof WP_User ? $user : null, $page !== '' ? ['recovery_page' => self::url('', $page)] : []);
        return ['message' => LSD_Security::recovery_message()];
    }

    public function frontend_request(): void
    {
        if (!self::requested()) return;
        if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
        nocache_headers();
        header('Referrer-Policy: no-referrer');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return;
        self::$frontend_result = ['result' => $this->perform(LSD_Security_Account::input('lsd_recovery_key', $_GET), $_POST)];
    }

    public function frontend(array $atts = [], string $form_type = 'auth'): string
    {
        (new LSD_Assets())->site(true);
        return (new LSD_Shortcodes_Auth())->security_interface($this->markup(LSD_Security_Account::input('lsd_recovery_key', $_GET), self::$frontend_result['result'] ?? [], self::link()), $atts, $form_type);
    }

    public function markup(string $key = '', $result = [], string $page = ''): string
    {
        $page = self::page($page);
        if ($page === '') $page = self::page(self::link());
        $action_url = self::url($key, $page);
        $return_url = self::page($action_url);
        $signin_url = remove_query_arg('lsd_recovery_dropdown', $return_url);
        if ($signin_url === '') $signin_url = wp_login_url();
        $complete = !is_wp_error($result) && !empty($result['complete']);
        $valid = $key === '' || self::state($key);
        if (!$valid && !$complete && !is_wp_error($result)) $result = new WP_Error('lsd_totp_recovery_expired', __('This recovery link is invalid or expired.', 'listdom'));
        // AJAX responses share a page, but not WordPress's per-request ID counter.
        $instance = uniqid('lsd-auth-recovery-');
        ob_start();
        include lsd_template('auth/recovery.php');
        return ob_get_clean();
    }

    public function ajax(): void
    {
        nocache_headers();
        header('Referrer-Policy: no-referrer');
        $key = LSD_Security_Account::input('key');
        $page = self::page(LSD_Security_Account::input('return_url'));
        if (!wp_verify_nonce(LSD_Security_Account::input('_wpnonce'), 'lsd_totp_email_recovery')) $result = new WP_Error('lsd_nonce', __('Security nonce is missing or invalid.', 'listdom'));
        else $result = LSD_Security_Account::input('operation') === 'view' ? [] : $this->perform($key, $_POST);
        if (is_wp_error($result))
        {
            $detail = $result->get_error_data();
            $this->response(['success' => 0, 'code' => $result->get_error_code(), 'message' => $result->get_error_message(), 'retry_after' => is_array($detail) ? ($detail['retry_after'] ?? 0) : 0, 'countdown' => is_array($detail) ? ($detail['countdown'] ?? null) : null]);
        }
        $this->response(['success' => 1, 'recovery_html' => $this->markup($key, $result, $page)]);
    }

    public function screen(): void
    {
        nocache_headers();
        header('Referrer-Policy: no-referrer');
        $key = LSD_Security_Account::input('lsd_recovery_key', $_REQUEST);
        $destination = self::url($key);
        if ((new LSD_Shortcodes_Auth())->get_login_url() !== '' && wp_parse_url($destination, PHP_URL_PATH) !== wp_parse_url(site_url('wp-login.php', 'login'), PHP_URL_PATH))
        {
            // Keep confirmation/request fields in the POST body when forwarding an old form.
            wp_safe_redirect($destination, ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? 307 : 302);
            exit;
        }
        $result = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? $this->perform($key, $_POST) : [];
        login_header(esc_html__('Recover Two-Factor Authentication', 'listdom'));
        // The native entry remains available when Listdom does not override login.
        if (is_wp_error($result)) echo '<p role="status">' . LSD_Countdown::error($result) . '</p>';
        else if (!empty($result['message'])) echo '<p role="status">' . esc_html($result['message']) . '</p>';
        if (is_wp_error($result) || empty($result['complete']))
        {
            echo '<form method="post">';
            wp_nonce_field('lsd_totp_email_recovery');
            if ($key !== '') echo '<input type="hidden" name="lsd_recovery_key" value="' . esc_attr($key) . '"><p>' . esc_html__('Confirm removal of your authenticator and recovery codes. Existing sign-ins will be revoked.', 'listdom') . '</p>';
            else echo '<p><label for="lsd_recovery_login">' . esc_html__('Username or email', 'listdom') . '</label><input type="text" id="lsd_recovery_login" name="user_login" required autocomplete="username"></p>';
            echo '<p><button class="button button-primary" type="submit">' . esc_html__('Confirm', 'listdom') . '</button></p></form>';
        }
        echo '<p><a href="' . esc_url(wp_login_url()) . '">' . esc_html__('Sign in', 'listdom') . '</a></p>';
        login_footer();
        exit;
    }
}
