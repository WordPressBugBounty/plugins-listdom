<?php

/** Shared second-factor policy with separate browser and API transport adapters. */
class LSD_Security_Challenge extends LSD_Base
{
    private static array $frontend_result = [];

    public function init(): void
    {
        add_filter('authenticate', [$this, 'authenticate'], 102, 3);
        add_action('login_form_lsd-security', [$this, 'screen']);
        add_action('login_form_lsd-totp-recovery', [$this, 'recovery_screen']);
        add_action('wp_ajax_lsd_auth_challenge', [$this, 'ajax']);
        add_action('wp_ajax_nopriv_lsd_auth_challenge', [$this, 'ajax']);
        add_action('template_redirect', [$this, 'frontend_request'], 0);
    }

    private static function browser(bool $create = false): string
    {
        $value = isset($_COOKIE['lsd_security_browser']) && is_string($_COOKIE['lsd_security_browser']) ? $_COOKIE['lsd_security_browser'] : '';
        if (preg_match('/^[a-f0-9]{64}$/D', $value)) return $value;
        if (!$create || headers_sent()) return '';
        $value = bin2hex(random_bytes(32));
        setcookie('lsd_security_browser', $value, ['expires' => time() + HOUR_IN_SECONDS, 'path' => COOKIEPATH ?: '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax']);
        $_COOKIE['lsd_security_browser'] = $value;
        return $value;
    }

    public static function primary_fingerprint(WP_User $user): string
    {
        return hash_hmac('sha256', $user->user_pass . '|' . $user->user_email . '|' . get_user_meta($user->ID, '_lsd_security_epoch', true), wp_salt('auth'));
    }

    public static function fingerprint(WP_User $user): string
    {
        return hash_hmac('sha256', self::primary_fingerprint($user) . '|' . LSD_Security_Totp::credential_fingerprint($user->ID), wp_salt('auth'));
    }

    public function authenticate($user, $username, $password)
    {
        if (LSD_Auth::authenticating_primary()) return $user;
        if (!$user instanceof WP_User || !LSD_Security_API::protected_account($user->ID)) return $user;
        $request = LSD_Security_API::request();
        if ($request) return LSD_Security_API::challenge($user, $request);
        if ((defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) || (defined('REST_REQUEST') && REST_REQUEST)) return new WP_Error('lsd_totp_required', __('This account requires an integrated second-factor authentication flow.', 'listdom'));
        $error = self::issue_browser($user, wp_validate_redirect(LSD_Security_Account::input('redirect_to'), admin_url()), !empty($_POST['rememberme']));
        if (!wp_doing_ajax() && isset($GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php' && $error->get_error_code() === 'lsd_totp_required')
        {
            wp_safe_redirect(self::url($error));
            exit;
        }
        return $error;
    }

    public static function issue_browser(WP_User $user, string $redirect, bool $remember = false, array $handoff = []): WP_Error
    {
        $browser = self::browser(true);
        if ($browser === '') return new WP_Error('lsd_totp_cookies', __('Enable cookies and try signing in again.', 'listdom'));
        $data = [
            'purpose' => 'browser_login',
            'user' => $user->ID,
            'fingerprint' => self::fingerprint($user),
            'primary' => self::primary_fingerprint($user),
            'browser' => hash('sha256', $browser),
            'remember' => $remember,
            'redirect' => wp_validate_redirect($redirect, admin_url()),
        ];
        if ($handoff) $data['handoff'] = $handoff;
        $token = LSD_Security_State::issue('challenge', $data, 300);
        if ($token === '') return new WP_Error('lsd_challenge_unavailable', __('Unable to start authentication. Please try again.', 'listdom'));
        return new WP_Error('lsd_totp_required', __('Continue to verify your identity.', 'listdom'), ['challenge' => $token]);
    }

    public static function url(WP_Error $error, string $redirect = ''): string
    {
        $token = $error->get_error_data()['challenge'] ?? '';
        if (!$token) return '';
        if ($redirect !== '')
        {
            $data = LSD_Security_State::read('challenge', $token);
            if (!$data) return '';
            $data['redirect'] = wp_validate_redirect($redirect, admin_url());
            update_option(LSD_Security_State::key('challenge', $token), $data, false);
        }
        $frontend = (new LSD_Shortcodes_Auth())->get_login_url();
        return $frontend !== ''
            ? add_query_arg('lsd_challenge', $token, $frontend)
            : add_query_arg(['action' => 'lsd-security', 'challenge' => $token], site_url('wp-login.php', 'login'));
    }

    private function challenge(string $token): array
    {
        $data = LSD_Security_State::read('challenge', $token);
        $browser = self::browser();
        if (!$data || ($data['purpose'] ?? '') !== 'browser_login' || !$browser || !hash_equals($data['browser'], hash('sha256', $browser))) return [];
        $user = get_userdata($data['user']);
        if (!$user || !hash_equals($data['fingerprint'], self::fingerprint($user)) || !LSD_User::is_email_verified($user->ID)) return [];
        if (is_multisite() && (!empty($user->spam) || !empty($user->deleted))) return [];
        if (!empty($data['handoff']) && !LSD_Security_API::valid_handoff_source($data['handoff'])) return [];
        return $data;
    }

    /** One browser verifier for the native screen, frontend POST, and AJAX. */
    public function perform(string $token, array $input)
    {
        $data = $this->challenge($token);
        if (!$data) return self::expired();
        if (!wp_verify_nonce(LSD_Security_Account::input('_wpnonce', $input), 'lsd_challenge_' . $token)) return new WP_Error('lsd_nonce', __('Security nonce is missing or invalid.', 'listdom'));
        $user_id = (int) $data['user'];
        $enroll = !LSD_Security_Totp::enabled($user_id);
        $begin = $enroll && LSD_Security_Account::input('operation', $input) === 'begin';
        $limited = $begin ? LSD_Security_Rate::check('totp', 'setup:' . $token) : LSD_Security_Rate::second_factor($user_id, $token);
        if (is_wp_error($limited)) return $limited;
        if ($begin)
        {
            $setup = LSD_Security_Totp::begin($user_id);
            return is_wp_error($setup) ? $setup : ['setup' => $setup];
        }
        $code = LSD_Security_Account::input('code', $input);
        $verified = $enroll ? LSD_Security_Totp::confirm($user_id, $code) : LSD_Security_Totp::verify($user_id, $code);
        if (is_wp_error($verified)) return $verified;
        if (!$verified) return new WP_Error('lsd_totp_invalid', __('Invalid or already used code. Enter one authenticator code or one unused recovery code.', 'listdom'));
        if (!LSD_Security_State::consume('challenge', $token)) return self::expired();
        $user = get_userdata($user_id);
        if (!LSD_Security_API::valid_user($user) || !hash_equals($data['primary'], self::primary_fingerprint($user)) || (!$enroll && !hash_equals($data['fingerprint'], self::fingerprint($user)))) return self::expired();
        if (!empty($data['native_auth']))
        {
            $native = $data['native_auth'];
            $eligible = LSD_Auth::eligible($user_id, $native['context'], $native['method']);
            if (is_wp_error($eligible)) return $eligible;
            if (isset(LSD_Auth_Providers::all()[$native['method']]) && !LSD_Auth_Providers::enabled($native['method'])) return self::expired();
            if (isset($native['context']['_provider_config']) && !hash_equals($native['context']['_provider_config'], LSD_Auth_Providers::fingerprint($native['method']))) return self::expired();
            if ($native['method'] === 'magic' && !LSD_Auth_Magic::enabled()) return self::expired();
            if ($native['method'] === 'passkey' && !LSD_Auth_Passkeys::enabled()) return self::expired();
        }
        if (!empty($data['handoff']) && !LSD_Security_API::refresh_handoff_proof($data['handoff'])) return new WP_Error('lsd_handoff_invalid', __('The originating API sign-in is no longer valid. Please sign in again.', 'listdom'));
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, (bool) $data['remember'], is_ssl());
        do_action('wp_login', $user->user_login, $user);
        if (!empty($data['native_auth'])) do_action('lsd_auth_completed', $user_id, $data['native_auth']['method']);
        $redirect = wp_validate_redirect(apply_filters('login_redirect', $data['redirect'], $data['redirect'], $user), admin_url());
        return $enroll ? ['redirect' => $redirect, 'codes' => $verified] : ['redirect' => $redirect];
    }

    private static function expired(): WP_Error
    {
        return new WP_Error('lsd_challenge_expired', __('This sign-in challenge has expired. Please sign in again.', 'listdom'));
    }

    /** Frontend forms without JavaScript must complete before theme output starts. */
    public function frontend_request(): void
    {
        $token = LSD_Security_Account::input('lsd_challenge', $_GET);
        if ($token === '') return;
        if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
        nocache_headers();
        header('Referrer-Policy: no-referrer');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return;
        self::$frontend_result = ['token' => $token, 'data' => $this->challenge($token), 'result' => $this->perform($token, $_POST)];
        $result = self::$frontend_result['result'];
        if (!is_wp_error($result) && !empty($result['redirect']) && empty($result['codes']))
        {
            wp_safe_redirect($result['redirect']);
            exit;
        }
    }

    public function frontend(string $token, array $atts = [], string $form_type = 'auth'): string
    {
        (new LSD_Assets())->site(true);
        $data = $this->challenge($token);
        $result = [];
        if ((self::$frontend_result['token'] ?? '') === $token)
        {
            $data = self::$frontend_result['data'];
            $result = self::$frontend_result['result'];
        }
        return (new LSD_Shortcodes_Auth())->security_interface($this->markup($token, $data, $result), $atts, $form_type);
    }

    public function markup(string $token, ?array $data = null, $result = []): string
    {
        $data = $data ?? $this->challenge($token);
        if (!$data && !is_wp_error($result)) $result = self::expired();
        $enroll = $data && !LSD_Security_Totp::enabled((int) $data['user']);
        $setup = !is_wp_error($result) ? ($result['setup'] ?? null) : null;
        $codes = !is_wp_error($result) ? ($result['codes'] ?? null) : null;
        $pending = $data ? get_user_meta($data['user'], '_lsd_totp_pending', true) : [];
        $has_setup = is_array($pending) && ($pending['expires'] ?? 0) > time();
        // AJAX responses share a page, but not WordPress's per-request ID counter.
        $instance = uniqid('lsd-auth-challenge-');
        $restart = (new LSD_Shortcodes_Auth())->get_login_url();
        if ($restart === '') $restart = wp_login_url();
        $action_url = self::url(new WP_Error('lsd_totp_required', '', ['challenge' => $token]));
        ob_start();
        include lsd_template('auth/challenge.php');
        return ob_get_clean();
    }

    public function ajax(): void
    {
        nocache_headers();
        $token = LSD_Security_Account::input('challenge');
        $data = $this->challenge($token);
        $result = $this->perform($token, $_POST);
        if (is_wp_error($result))
        {
            $detail = $result->get_error_data();
            $this->response(['success' => 0, 'code' => $result->get_error_code(), 'message' => $result->get_error_message(), 'retry_after' => is_array($detail) ? ($detail['retry_after'] ?? 0) : 0, 'countdown' => is_array($detail) ? ($detail['countdown'] ?? null) : null]);
        }
        // Enrollment codes stay in this one response; never put them in a redirect.
        if (!empty($result['setup']) || !empty($result['codes'])) $this->response(['success' => 1, 'challenge_html' => $this->markup($token, $data, $result)]);
        $this->response(['success' => 1, 'message' => esc_html__('Login Successful.', 'listdom'), 'redirect' => $result['redirect']]);
    }

    public function screen(): void
    {
        nocache_headers();
        header('Referrer-Policy: no-referrer');
        $token = LSD_Security_Account::input('challenge', $_REQUEST);
        if ((new LSD_Shortcodes_Auth())->get_login_url() !== '')
        {
            wp_safe_redirect(self::url(new WP_Error('lsd_totp_required', '', ['challenge' => $token])), ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? 307 : 302);
            exit;
        }
        $data = $this->challenge($token);
        if (!$data) wp_die(esc_html__('This sign-in challenge has expired. Please sign in again.', 'listdom'));
        $user_id = (int) $data['user'];
        $enroll = !LSD_Security_Totp::enabled($user_id);
        $message = '';
        $setup = null;
        $codes = null;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')
        {
            $result = $this->perform($token, $_POST);
            if (is_wp_error($result)) $message = LSD_Countdown::error($result);
            else
            {
                $setup = $result['setup'] ?? null;
                $codes = $result['codes'] ?? null;
                $redirect = $result['redirect'] ?? '';
                if ($redirect !== '' && !$codes)
                {
                    wp_safe_redirect($redirect);
                    exit;
                }
            }
        }
        login_header(esc_html__('Account Security', 'listdom'));
        echo '<div class="wui lsd-account-security">';
        if ($message) echo '<div class="lsd-alert lsd-error" role="alert">' . $message . '</div>';
        if ($codes)
        {
            echo '<p>' . esc_html__('Save these recovery codes now. They are shown only once. Each line is a separate, single-use code; enter only one when signing in.', 'listdom') . '</p><pre>' . esc_html(implode("\n", $codes)) . '</pre>';
            echo '<p><a class="button button-primary" href="' . esc_url($redirect) . '">' . esc_html__('Continue', 'listdom') . '</a></p>';
        }
        else
        {
            echo '<form method="post" class="lsd-form">';
            wp_nonce_field('lsd_challenge_' . $token);
            echo '<input type="hidden" name="challenge" value="' . esc_attr($token) . '">';
            if ($enroll)
            {
                echo '<p>' . esc_html__('Your user role requires authenticator enrollment before sign-in.', 'listdom') . '</p>';
                if (is_array($setup))
                {
                    if ($setup['qr_code'] !== '')
                    {
                        echo '<p>' . esc_html__('Scan this QR code with your preferred authenticator app, then enter its code below.', 'listdom') . '</p>';
                        echo '<img src="' . esc_attr($setup['qr_code']) . '" width="256" height="256" style="max-width:100%;height:auto" alt="' . esc_attr__('Authenticator setup QR code', 'listdom') . '">';
                    }
                    echo '<p>' . esc_html__('You can also enter this setup key manually in your authenticator app:', 'listdom') . '</p><code>' . esc_html($setup['secret']) . '</code>';
                    echo '<p><a href="' . esc_attr($setup['uri']) . '">' . esc_html__('Open authenticator', 'listdom') . '</a></p>';
                }
                $pending = get_user_meta($user_id, '_lsd_totp_pending', true);
                if (!$pending || ($pending['expires'] ?? 0) <= time()) echo '<input type="hidden" name="operation" value="begin">';
                else
                {
                    self::code_field(true);
                    echo '<p><button type="submit" name="operation" value="begin" class="button" formnovalidate>' . esc_html__('Start setup again', 'listdom') . '</button></p>';
                }
            }
            else self::code_field();
            echo '<p><button class="button button-primary lsd-general-button" type="submit">' . esc_html__('Continue', 'listdom') . '</button></p></form>';
            echo '<p><a href="' . esc_url(LSD_Security_Recovery::url()) . '">' . esc_html__('Recover 2FA by email', 'listdom') . '</a></p>';
        }
        echo '</div>';
        login_footer();
        exit;
    }

    private static function code_field(bool $authenticator_only = false): void
    {
        $label = $authenticator_only ? __('Code from your authenticator app', 'listdom') : __('Authenticator code or one recovery code', 'listdom');
        echo '<p><label for="lsd_totp_code">' . esc_html($label) . '</label><input id="lsd_totp_code" name="code" type="text" autocomplete="one-time-code"' . ($authenticator_only ? '' : ' aria-describedby="lsd_totp_code_help"') . ' required></p>';
        if (!$authenticator_only) echo '<p id="lsd_totp_code_help">' . esc_html__('Enter the six-digit code from your authenticator app or one unused recovery code.', 'listdom') . '</p>';
    }

    public function recovery_screen(): void
    {
        (new LSD_Security_Recovery())->screen();
    }
}
