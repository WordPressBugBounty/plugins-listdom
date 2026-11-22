<?php

class LSD_Shortcodes_Auth extends LSD_Base
{
    private static bool $rendering_dropdown = false;
    private static bool $rendering_signin = false;

    public static function inside_dropdown(): bool
    {
        return self::$rendering_dropdown;
    }

    public function init()
    {
        // Shortcodes
        add_shortcode('listdom-login', [$this, 'login']);
        add_shortcode('listdom-register', [$this, 'register']);
        add_shortcode('listdom-forgot-password', [$this, 'forgot_password']);
        add_shortcode('listdom-user-profile', [$this, 'user_profile']);

        // Auth Shortcode
        add_shortcode('listdom-auth', [$this, 'auth']);

        // Email Verification Handler
        add_action('init', [$this, 'handle_verification_request']);

        // Login User
        add_action('wp_ajax_lsd_login', [$this, 'signin']);
        add_action('wp_ajax_nopriv_lsd_login', [$this, 'signin']);

        // Resend Verification Email
        add_action('wp_ajax_lsd_resend_verification', [$this, 'resend_verification_email']);
        add_action('wp_ajax_nopriv_lsd_resend_verification', [$this, 'resend_verification_email']);

        // Register User
        add_action('wp_ajax_lsd_register', [$this, 'signup']);
        add_action('wp_ajax_nopriv_lsd_register', [$this, 'signup']);

        // Forgot Password
        add_action('wp_ajax_lsd_forgot_password', [$this, 'forgot_password_request']);
        add_action('wp_ajax_nopriv_lsd_forgot_password', [$this, 'forgot_password_request']);

        // Reset Password
        add_action('wp_ajax_lsd_reset_password', [$this, 'reset_password_request']);
        add_action('wp_ajax_nopriv_lsd_reset_password', [$this, 'reset_password_request']);

        // Block Unverified Users
        add_filter('authenticate', [$this, 'block_unverified_users'], 30, 3);

        // Redirect After Logout
        add_action('wp_logout', [$this, 'logout_redirect']);

        // Default URLs
        add_filter('login_url', [$this, 'replace_login_url'], 99, 3);
        add_filter('register_url', [$this, 'replace_register_url'], 99);
        add_filter('lostpassword_url', [$this, 'replace_forgot_url'], 99, 2);

        // Redirect WordPress Pages
        add_action('login_init', [$this, 'wp_redirect']);
    }

    public function user_profile($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-user-profile');
        if (trim($pre)) return $pre;

        $show_security_link = $this->is_truthy($atts['show_security_link'] ?? false);

        ob_start();
        include lsd_template('auth/profile.php');
        return ob_get_clean();
    }

    public function login($atts = [])
    {
        if (LSD_Auth::requested($atts)) return LSD_Auth::frontend($atts, 'login');
        if (!self::$rendering_signin && !self::$rendering_dropdown && LSD_Security_Recovery::requested() && (strtolower((string) ($atts['layout'] ?? '')) === 'dropdown' ? LSD_Security_Recovery::dropdown() : !LSD_Security_Recovery::dropdown())) return (new LSD_Security_Recovery())->frontend($atts, 'login');

        $challenge = LSD_Security_Account::input('lsd_challenge', $_GET);
        if ($challenge !== '' && !self::$rendering_signin && !self::$rendering_dropdown && strtolower((string) ($atts['layout'] ?? '')) !== 'dropdown') return (new LSD_Security_Challenge())->frontend($challenge, $atts, 'login');

        return $this->login_form($atts);
    }

    /** Render a fresh login form without reopening a challenge from the page URL. */
    public function login_form($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-login');
        if (trim($pre)) return $pre;


        $redirect = isset($atts['redirect']) ? wp_validate_redirect(wp_sanitize_redirect((string) $atts['redirect']), '') : '';
        $explicit_redirect = $this->is_truthy($atts['explicit_redirect'] ?? false);

        // Role Restriction
        $role = isset($atts['role']) ? sanitize_text_field($atts['role']) : '';
        if (!in_array($role, array_unique(array_merge(LSD_Roles::supported(true), LSD_Security::public_roles())), true)) $role = '';

        $layout = isset($atts['layout']) ? strtolower(sanitize_text_field($atts['layout'])) : 'inline';
        if ($layout === 'dropdown') return $this->render_auth_dropdown($atts, $role, 'login', $redirect, $explicit_redirect);

        // Get Redirect from Request
        if (isset($_REQUEST['redirect_to']) && trim($_REQUEST['redirect_to']))
        {
            $requested_redirect = wp_sanitize_redirect(wp_unslash($_REQUEST['redirect_to']));
            if ($requested_redirect !== '')
            {
                $redirect = wp_validate_redirect($requested_redirect, $redirect);
                $explicit_redirect = true;
            }
        }

        // User is Already Logged-in
        if (is_user_logged_in()) return $this->user_profile($atts);

        ob_start();
        include lsd_template('auth/login.php');
        return ob_get_clean();
    }

    /** Preserve the complete originating interface, ready for use after recovery logs out. */
    public function security_interface(string $markup, array $atts = [], string $form_type = 'auth'): string
    {
        $previous = self::$rendering_signin;
        $user_id = get_current_user_id();
        $cookie = $_COOKIE[LOGGED_IN_COOKIE] ?? null;
        self::$rendering_signin = true;
        wp_set_current_user(0);
        unset($_COOKIE[LOGGED_IN_COOKIE]);
        try
        {
            $backup = $form_type === 'login' ? $this->login_form($atts) : $this->auth($atts);
            if (strtolower((string) ($atts['layout'] ?? '')) === 'dropdown') $markup = $this->render_auth_dropdown($atts, '', $form_type, '', false, $markup);
        }
        finally
        {
            self::$rendering_signin = $previous;
            if ($cookie !== null) $_COOKIE[LOGGED_IN_COOKIE] = $cookie;
            wp_set_current_user($user_id);
        }
        return '<div class="lsd-auth-security-interface"><div class="lsd-auth-login-backup" hidden>' . $backup . '</div><div class="lsd-auth-security-content">' . $markup . '</div></div>';
    }

    public function register($atts = [])
    {
        if (LSD_Auth::requested($atts)) return LSD_Auth::frontend($atts);
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-register');
        if (trim($pre)) return $pre;

        if (is_user_logged_in()) return $this->user_profile($atts);
        if (!LSD_Security::registration_allowed()) return $this->alert(esc_html__('Public registration is disabled.', 'listdom'), 'info');

        $redirect = isset($atts['redirect']) ? wp_validate_redirect(wp_sanitize_redirect((string) $atts['redirect']), '') : '';
        $explicit_redirect = $this->is_truthy($atts['explicit_redirect'] ?? false);

        if (isset($_REQUEST['redirect_to']) && trim((string) wp_unslash($_REQUEST['redirect_to'])))
        {
            $requested_redirect = wp_sanitize_redirect(wp_unslash($_REQUEST['redirect_to']));
            if ($requested_redirect !== '')
            {
                $redirect = wp_validate_redirect($requested_redirect, $redirect);
                $explicit_redirect = true;
            }
        }

        // Role Restriction
        $role = isset($atts['role']) ? sanitize_text_field($atts['role']) : '';
        if ($role !== '' && !in_array($role, LSD_Security::public_roles(), true)) return $this->alert(esc_html__('This registration role is unavailable.', 'listdom'), 'warning');

        $layout = isset($atts['layout']) ? strtolower(sanitize_text_field($atts['layout'])) : 'inline';
        if ($layout === 'dropdown') return $this->render_auth_dropdown($atts, $role, 'register', $redirect, $explicit_redirect);

        // User is Already Logged-in
        if (is_user_logged_in()) return $this->user_profile($atts);

        ob_start();
        include lsd_template('auth/register.php');
        return ob_get_clean();
    }

    public function forgot_password($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-forgot-password');
        if (trim($pre)) return $pre;

        $layout = isset($atts['layout']) ? strtolower(sanitize_text_field($atts['layout'])) : 'inline';
        if ($layout === 'dropdown') return $this->render_auth_dropdown($atts, '', 'forgot-password');

        // User is Already Logged-in
        if (is_user_logged_in()) return $this->user_profile($atts);

        ob_start();
        include lsd_template('auth/forgot-password.php');
        return ob_get_clean();
    }

    public function auth($atts = [])
    {
        if (LSD_Auth::requested($atts)) return LSD_Auth::frontend($atts);
        if (!self::$rendering_signin && !self::$rendering_dropdown && LSD_Security_Recovery::requested() && (strtolower((string) ($atts['layout'] ?? '')) === 'dropdown' ? LSD_Security_Recovery::dropdown() : !LSD_Security_Recovery::dropdown())) return (new LSD_Security_Recovery())->frontend($atts, 'auth');

        $challenge = LSD_Security_Account::input('lsd_challenge', $_GET);
        if ($challenge !== '' && !self::$rendering_signin && !self::$rendering_dropdown && strtolower((string) ($atts['layout'] ?? '')) !== 'dropdown') return (new LSD_Security_Challenge())->frontend($challenge, $atts, 'auth');

        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-auth');
        if (trim($pre)) return $pre;

        $redirect = isset($atts['redirect']) ? wp_validate_redirect(wp_sanitize_redirect((string) $atts['redirect']), '') : '';
        $explicit_redirect = $this->is_truthy($atts['explicit_redirect'] ?? false);

        if (isset($_REQUEST['redirect_to']) && trim((string) wp_unslash($_REQUEST['redirect_to'])))
        {
            $requested_redirect = wp_sanitize_redirect(wp_unslash($_REQUEST['redirect_to']));
            if ($requested_redirect !== '')
            {
                $redirect = wp_validate_redirect($requested_redirect, $redirect);
                $explicit_redirect = true;
            }
        }

        $layout = isset($atts['layout']) ? strtolower(sanitize_text_field($atts['layout'])) : 'inline';
        $layout = $layout === 'dropdown' ? 'dropdown' : 'inline';

        // Role Restriction
        $role = isset($atts['role']) ? sanitize_text_field($atts['role']) : '';
        if (!in_array($role, array_unique(array_merge(LSD_Roles::supported(true), LSD_Security::public_roles())), true)) $role = '';

        if ($layout === 'dropdown') return $this->render_auth_dropdown($atts, $role, 'auth', $redirect, $explicit_redirect);

        // User is Already Logged-in
        if (is_user_logged_in()) return $this->user_profile($atts);

        if ($this->is_truthy($atts['role_tabs'] ?? false))
        {
            $role_tabs = $this->auth_dropdown_role_tabs(
                $role,
                $this->parse_pipe_list($atts['role_tabs_labels'] ?? ''),
                array_map('trim', explode('|', (string) ($atts['tabs_roles'] ?? ''))),
                true
            );
            $id = uniqid('lsd-auth-inline-');
            $form_type = 'auth';

            ob_start();
            include lsd_template('auth/auth-inline.php');
            return ob_get_clean();
        }

        ob_start();
        include lsd_template('auth/auth.php');
        return ob_get_clean();
    }

    private function render_auth_dropdown($atts = [], string $role = '', string $form_type = 'auth', string $redirect = '', bool $explicit_redirect = false, ?string $security_markup = null): string
    {
        $defaults = [
            'label' => esc_html__('Sign in', 'listdom'),
            'layout' => 'dropdown',
            'role' => '',
            'redirect' => '',
            'explicit_redirect' => '',
            'align' => 'right',
            'hover' => '',
            'role_tabs' => '',
            'role_tabs_labels' => '',
            'tabs_roles' => '',
            'show_security_link' => false,
        ];

        $atts = shortcode_atts($defaults, $atts, 'listdom-auth');

        $show_security_link = $this->is_truthy($atts['show_security_link']);

        $button_label = sanitize_text_field($atts['label']);
        if (!trim($button_label)) $button_label = esc_html__('Sign in', 'listdom');

        $hover = in_array(strtolower((string) $atts['hover']), ['1', 'true', 'yes', 'hover', 'on'], true);
        $align_value = strtolower((string) $atts['align']);
        $align = in_array($align_value, ['left', 'center', 'right'], true) ? $align_value : 'right';

        $role_tabs_enabled = in_array(strtolower((string) $atts['role_tabs']), ['1', 'true', 'yes', 'on'], true);
        $role_tab_labels = $this->parse_pipe_list($atts['role_tabs_labels'] ?? '');
        $role_tab_roles = explode('|', (string) ($atts['tabs_roles'] ?? ''));
        $role_tab_roles = array_map('trim', $role_tab_roles);
        $role_tabs = $this->auth_dropdown_role_tabs($atts['role'] ?: $role, $role_tab_labels, $role_tab_roles, $role_tabs_enabled);

        $id = 'lsd-auth-dropdown-' . uniqid();

        $form_type = in_array($form_type, ['auth', 'login', 'register', 'forgot-password'], true) ? $form_type : 'auth';

        $auth = LSD_Options::auth();

        // Dashboard Page
        $dashboard_page = LSD_Options::post_id('submission_page');

        $logout_redirect = $this->page_url($auth['logout']['redirect'] ?? 0);
        if ($logout_redirect === '') $logout_redirect = home_url();

        $account_redirect = $this->page_url($auth['account']['redirect'] ?? 0);
        if ($account_redirect === '') $account_redirect = $dashboard_page ? get_page_link($dashboard_page) : home_url();

        ob_start();
        $previous = self::$rendering_dropdown;
        self::$rendering_dropdown = true;
        try { include lsd_template('auth/auth-dropdown.php'); }
        finally { self::$rendering_dropdown = $previous; }
        return ob_get_clean();
    }

    private function auth_dropdown_role_tabs(string $fallback_role = '', array $custom_labels = [], array $custom_roles = [], bool $custom_enabled = false): array
    {
        $tabs = [];
        $roles = array_unique(array_merge(LSD_Roles::supported(true), LSD_Security::public_roles()));

        $fallback_role = sanitize_text_field($fallback_role);
        if (!in_array($fallback_role, $roles, true)) $fallback_role = '';

        $custom_labels = array_values(array_filter(array_map('sanitize_text_field', $custom_labels), 'strlen'));
        $custom_roles = array_values(array_map('sanitize_text_field', $custom_roles));

        if ($custom_enabled && (count($custom_labels) || count($custom_roles) > 1))
        {
            $tab_count = max(count($custom_labels), count($custom_roles));

            for ($i = 0; $i < $tab_count; $i++)
            {
                $label = $custom_labels[$i] ?? '';
                $role = $custom_roles[$i] ?? '';

                if (!in_array($role, $roles, true)) $role = '';
                if ($label === '')
                {
                    if ($role)
                    {
                        $label = ucwords(str_replace(['_', '-'], ' ', $role));
                    }
                    else
                    {
                        $label = esc_html__('Users', 'listdom');
                    }
                }

                $tabs[] = [
                    'label' => $label,
                    'role' => $role ?: $fallback_role,
                ];
            }

            if ($tabs) return $tabs;
        }

        $tabs[] = [
            'label' => esc_html__('Users', 'listdom'),
            'role' => $fallback_role,
        ];

        return $tabs;
    }

    private function parse_pipe_list($value): array
    {
        if (!is_string($value)) return [];

        $parts = array_map('trim', explode('|', $value));
        return array_values(array_filter($parts, 'strlen'));
    }

    public function role_signature(string $context, string $role): string
    {
        $role = $context === 'register' ? (in_array($role, LSD_Security::public_roles(), true) ? $role : '') : $this->sanitize_supported_role($role);
        if ($role === '') return '';

        return wp_create_nonce($this->role_signature_action($context, $role));
    }

    private function sanitize_supported_role($role): string
    {
        $role = is_string($role) ? sanitize_text_field($role) : '';
        if (!in_array($role, array_unique(array_merge(LSD_Roles::supported(true), LSD_Security::public_roles())), true)) return '';

        return $role;
    }

    private function verify_role_signature(string $context, string $role, string $signature): bool
    {
        $role = $context === 'register' ? (in_array($role, LSD_Security::public_roles(), true) ? $role : '') : $this->sanitize_supported_role($role);
        if ($role === '' || $signature === '') return false;

        return (bool) wp_verify_nonce($signature, $this->role_signature_action($context, $role));
    }

    private function role_signature_action(string $context, string $role): string
    {
        return 'lsd_' . sanitize_key($context) . '_role_' . $role;
    }

    public function signin()
    {
        // Check for nonce
        if (
            !isset($_POST['lsd_login'])
            || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lsd_login'])), 'lsd_login')
        ) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing or invalid!', 'listdom')]);

        $user_login = LSD_Security_Account::input('log');
        $user_password = LSD_Security_Account::input('pwd');

        // Check for login credentials
        if ($user_login === '' || $user_password === '') $this->response(['success' => 0, 'message' => esc_html__('Username or password is empty.', 'listdom')]);

        $credentials = [
            'user_login' => sanitize_text_field($user_login),
            'user_password' => $user_password,
            'remember' => isset($_POST['rememberme']),
        ];

        // Redirect Url
        $redirect_to = isset($_POST['redirect_to']) && $_POST['redirect_to']
            ? wp_validate_redirect(wp_sanitize_redirect(wp_unslash($_POST['redirect_to'])))
            : '';
        $explicit_redirect = $this->is_truthy($_POST['lsd_explicit_redirect'] ?? false);

        // Role Restriction
        $role = isset($_POST['lsd_role']) ? sanitize_text_field(wp_unslash($_POST['lsd_role'])) : '';
        $role_signature = isset($_POST['lsd_role_signature']) ? sanitize_text_field(wp_unslash($_POST['lsd_role_signature'])) : '';
        $role = $this->verify_role_signature('login', $role, $role_signature) ? $this->sanitize_supported_role($role) : '';

        if ($role)
        {
            $user_data = get_user_by('login', $credentials['user_login']);
            if (!$user_data) $user_data = get_user_by('email', $credentials['user_login']);

            if (!$user_data) $this->response(['success' => 0, 'message' => esc_html__('User not found!', 'listdom')]);
            if (!in_array('administrator', $user_data->roles) && !in_array($role, $user_data->roles))
            {
                $this->response(['success' => 0, 'message' => esc_html__('User not found!', 'listdom')]);
            }
        }

        // Attempt to log in
        $user = wp_signon($credentials, is_ssl());

        // Invalid Login
        if (is_wp_error($user))
        {
            $message = $user->get_error_message();
            if (!trim($message)) $message = esc_html__('Invalid username or password.', 'listdom');

            $response = [
                'success' => 0,
                'message' => wp_kses_post($message),
                'code' => $user->get_error_code(),
                'retry_after' => (int) ($user->get_error_data()['retry_after'] ?? 0),
                'countdown' => $user->get_error_data()['countdown'] ?? null,
            ];

            if ($user->get_error_code() === 'lsd_totp_required')
            {
                $challenge = LSD_Security_State::read('challenge', $user->get_error_data()['challenge'] ?? '');
                $challenge_user = $challenge ? get_userdata($challenge['user']) : false;
                $destination = ($explicit_redirect && $redirect_to !== '') ? $this->validate_redirect($redirect_to) : $this->role_based_redirect('login', $challenge_user, $redirect_to);
                $response['challenge_url'] = LSD_Security_Challenge::url($user, $destination);
                $response['challenge_html'] = (new LSD_Security_Challenge())->markup($user->get_error_data()['challenge']);
            }

            if ($user->get_error_code() === 'lsd_email_verification_required')
            {
                $response['allow_resend'] = 1;
                $response['user_login'] = $credentials['user_login'];
            }

            $this->response($response);
        }

        $redirect_url = ($explicit_redirect && $redirect_to !== '')
            ? $this->validate_redirect($redirect_to)
            : $this->role_based_redirect('login', $user, $redirect_to);

        // Valid Login
        $this->response([
            'success' => 1,
            'message' => esc_html__('Login Successful.', 'listdom'),
            'redirect' => $redirect_url,
        ]);
    }

    public function resend_verification_email()
    {
        if (!wp_verify_nonce(LSD_Security_Account::input('lsd_resend_verification'), 'lsd_resend_verification')) $this->security_error(new WP_Error('lsd_nonce', __('Security nonce is missing or invalid.', 'listdom')));
        $identifier = sanitize_text_field(LSD_Security_Account::input('log'));
        if ($identifier === '') $identifier = sanitize_text_field(LSD_Security_Account::input('user_login'));
        if ($identifier === '') $this->security_error(new WP_Error('lsd_identifier_required', __('Enter your username or email address.', 'listdom')));
        $limit = LSD_Security_Rate::check('verification', $identifier);
        if (is_wp_error($limit)) $this->security_error($limit);
        $user = LSD_Security::find_user($identifier);
        LSD_Security::queue_mail('verification', $user instanceof WP_User ? $user : null);
        $this->response(['success' => 1, 'message' => LSD_Security::recovery_message(), 'allow_resend' => 1]);
    }

    public function signup()
    {
        // Check for nonce security
        if (
            !isset($_POST['lsd_register_nonce'])
            || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lsd_register_nonce'])), 'lsd_register_nonce')
        ) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing or invalid!', 'listdom')]);

        // Check required fields
        if (empty($_POST['lsd_username']) || empty($_POST['lsd_email']) || empty($_POST['lsd_password'])) $this->response(['success' => 0, 'message' => esc_html__('All fields are required.', 'listdom')]);

        // Auth Options
        $auth = LSD_Options::auth();

        // Sanitize form values
        $username = sanitize_text_field(LSD_Security_Account::input('lsd_username'));
        $email = sanitize_email(LSD_Security_Account::input('lsd_email'));
        $password = LSD_Security_Account::input('lsd_password');

        $consent = isset($_POST['lsd_privacy_consent']) ? sanitize_text_field(wp_unslash($_POST['lsd_privacy_consent'])) : '';
        $consent_enabled = LSD_Privacy::is_consent_enabled('register');
        if ($consent_enabled && $consent !== '1') $this->response(['success' => 0, 'message' => LSD_Privacy::consent_required_text()]);

        $allowed = LSD_Security::registration_check('public', $username, $email);
        if (is_wp_error($allowed)) $this->security_error($allowed);
        if (!is_email($email)) $this->security_error(new WP_Error('lsd_email_invalid', __('Enter a valid email address.', 'listdom')));
        $valid = LSD_Security::password($password);
        if (is_wp_error($valid)) $this->security_error($valid);

        // Check if the username or email already exists
        if (username_exists($username) || email_exists($email)) $this->response(['success' => 0, 'message' => esc_html__('Username and / or email already exist.', 'listdom')]);

        // Role Restriction
        $role = isset($_POST['lsd_role']) ? sanitize_text_field(wp_unslash($_POST['lsd_role'])) : '';
        $role_signature = isset($_POST['lsd_role_signature']) ? sanitize_text_field(wp_unslash($_POST['lsd_role_signature'])) : '';
        if ($role !== '' && !$this->verify_role_signature('register', $role, $role_signature)) $this->security_error(new WP_Error('lsd_registration_role', __('This registration role is not available.', 'listdom')));
        $role = LSD_Security::registration_role($role);
        if (is_wp_error($role)) $this->security_error($role);

        $fields = LSD_Portal_Fieldlayout::registration_values(wp_unslash($_POST['lsd_registration_fields'] ?? []), wp_unslash($_POST['lsd_user_fields'] ?? []), $role);
        if (is_wp_error($fields)) $this->security_error($fields);

        // Create the user
        $user_data = [
            'user_login' => $username,
            'user_pass' => $password,
            'user_email' => $email,
        ];

        if ($role) $user_data['role'] = $role;
        $user_id = LSD_Security::insert_user($user_data + LSD_Portal_Fieldlayout::registration_user_data($fields));

        // Check for errors
        if (is_wp_error($user_id)) $this->response(['success' => 0, 'message' => esc_html($user_id->get_error_message())]);

        if ($consent_enabled && $consent === '1')
        {
            update_user_meta($user_id, 'lsd_privacy_consent', LSD_Privacy::create_log([
                'context' => 'user_registration',
                'email' => $email,
                'user_id' => $user_id,
            ]));
        }

        LSD_User::maybe_send_verification_email($user_id);

        $auto_login_enabled = isset($auth['register']['login_after_register'])
            && $auth['register']['login_after_register']
            && !LSD_User::requires_email_verification()
            && !LSD_Security_Totp::enrollment_required($user_id);

        // Auto Login
        if ($auto_login_enabled)
        {
            LSD_User::login($user_id);
        }

        // Send success message with redirect
        $redirect_url = '';
        if ($auto_login_enabled)
        {
            $user = get_user_by('id', $user_id);

            $redirect_value = isset($_POST['lsd_redirect']) ? wp_sanitize_redirect(wp_unslash($_POST['lsd_redirect'])) : '';
            $redirect_value = wp_validate_redirect($redirect_value);
            $explicit_redirect = $this->is_truthy($_POST['lsd_explicit_redirect'] ?? false);

            $redirect_url = ($explicit_redirect && $redirect_value !== '')
                ? $this->validate_redirect($redirect_value)
                : $this->role_based_redirect('register', $user, $redirect_value);
        }

        $success_message = esc_html__('Registration successful.', 'listdom');
        if (LSD_User::requires_email_verification())
        {
            $success_message = esc_html__('Registration successful. Please check your email to verify your account.', 'listdom');
        }

        // Send success message with redirect
        $this->response([
            'success' => 1,
            'message' => $success_message,
            'redirect' => $redirect_url,
        ]);
    }

    public function handle_verification_request()
    {
        if (wp_doing_ajax()) return;

        $user_id = isset($_GET['lsd_verify']) ? absint($_GET['lsd_verify']) : 0;
        $token = isset($_GET['lsd_key']) ? sanitize_text_field(wp_unslash($_GET['lsd_key'])) : '';

        if (!$user_id || !$token) return;

        $status = LSD_User::verify_email($user_id, $token) ? '1' : '0';

        $redirect = $this->get_login_url();
        if (!$redirect) $redirect = wp_login_url();
        if (!$redirect) $redirect = home_url('/');

        $redirect = add_query_arg('lsd-verified', $status, $redirect);
        $redirect = apply_filters('lsd_email_verification_redirect', $redirect, $status, $user_id);

        wp_safe_redirect($redirect);
        exit;
    }

    public function forgot_password_request()
    {
        if (!wp_verify_nonce(LSD_Security_Account::input('lsd_forgot_password_nonce'), 'lsd_forgot_password_nonce')) $this->security_error(new WP_Error('lsd_nonce', __('Security nonce is missing or invalid.', 'listdom')));
        $portal = wp_verify_nonce(LSD_Security_Account::input('lsd_portal_recovery'), 'lsd_portal_recovery');
        $result = LSD_Security::recover_password(sanitize_text_field(LSD_Security_Account::input('user_login')), $portal ? LSD_Portal::url('forgot') : '');
        if (is_wp_error($result)) $this->security_error($result);
        $this->response(['success' => 1, 'message' => LSD_Security::recovery_message()]);
    }

    public function reset_password_request()
    {
        // Check for nonce security
        if (
            !isset($_POST['lsd_reset_password_nonce'])
            || !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['lsd_reset_password_nonce'])),
                'lsd_reset_password_nonce'
            )
        ) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing or invalid!', 'listdom')]);

        // Required fields
        if (LSD_Security_Account::input('password') === '' || LSD_Security_Account::input('password_confirmation') === '') $this->response(['success' => 0, 'message' => esc_html__('Password is required.', 'listdom')]);

        $password = LSD_Security_Account::input('password');
        $password_confirmation = LSD_Security_Account::input('password_confirmation');

        $valid = LSD_Security::password($password, $password_confirmation);
        if (is_wp_error($valid)) $this->security_error($valid);

        $login = isset($_POST['user_login']) ? sanitize_user(wp_unslash($_POST['user_login'])) : '';
        $key = isset($_POST['reset_key']) ? sanitize_text_field(wp_unslash($_POST['reset_key'])) : '';

        // Validate reset key
        $user = check_password_reset_key($key, $login);
        if ($user instanceof WP_Error) $this->response(['success' => 0, 'message' => esc_html__('This password reset link is invalid or has expired.', 'listdom')]);

        // Reset password
        reset_password($user, $password);

        // Trigger Action
        do_action('lsd_user_password_reset', $user->ID);

        // Redirect URL
        $redirect_url = $this->get_login_url();

        $this->response([
            'success' => 1,
            'message' => esc_html__('Your password has been reset.', 'listdom'),
            'redirect' => $redirect_url,
        ]);
    }

    public function logout_redirect()
    {
        // Preserve the account-security action's JSON response while retaining wp_logout hooks.
        if (doing_action('wp_ajax_lsd_account_security')) return;
        // Auth Options
        $auth = LSD_Options::auth();

        // No Redirect
        if (!isset($auth['logout']) || !isset($auth['logout']['redirect']) || !$auth['logout']['redirect']) return;

        $redirect_url = $this->page_url($auth['logout']['redirect'] ?? 0);
        if ($redirect_url === '') return;

        wp_redirect($redirect_url);
        exit;
    }

    public function block_unverified_users($user, $username, $password)
    {
        if (is_wp_error($user)) return $user;

        if (!$user instanceof WP_User) return $user;

        if (!LSD_User::requires_email_verification()) return $user;

        if (LSD_User::is_email_verified($user->ID)) return $user;

        return new WP_Error('lsd_email_verification_required', LSD_User::verification_required_message());
    }

    public function replace_login_url(string $login_url, $redirect, $force_reauth): string
    {
        // New URL
        $url = $this->get_login_url($redirect, $force_reauth);

        // Return URL
        return trim($url) ? $url : $login_url;
    }

    public function replace_register_url(string $register_url): string
    {
        // New URL
        $url = $this->get_register_url();

        // Return URL
        return trim($url) ? $url : $register_url;
    }

    public function replace_forgot_url(string $forgot_url, $redirect): string
    {
        // New URL
        $url = $this->get_forgot_url($redirect);

        // Return URL
        return trim($url) ? $url : $forgot_url;
    }

    public function get_login_url($redirect = '', $force_reauth = 0): string
    {
        // No Page Configured
        if (!$this->option('login_form') || $this->option('hide_login_form')) return '';

        // Page URL
        $page_url = $this->page_url($this->option('login_page'));
        if ($page_url === '') return '';

        // Add Redirect
        if ($redirect && trim($redirect)) $page_url = add_query_arg('redirect_to', urlencode($redirect), $page_url);

        // Add Re-auth
        if ($force_reauth) $page_url = add_query_arg('reauth', '1', $page_url);

        // Add tab
        return add_query_arg('tab', 'login', $page_url);
    }

    public function get_register_url(): string
    {
        // No Page Configured
        if (!LSD_Security::registration_allowed() || !$this->option('register_form') || $this->option('hide_register_form')) return '';

        // Page URL
        $page_url = $this->page_url($this->option('register_page'));
        if ($page_url === '') return '';

        // Add tab
        return add_query_arg('tab', 'register', $page_url);
    }

    public function get_forgot_url($redirect = ''): string
    {
        // No Page Configured
        if (!$this->option('forgot_password_form') || $this->option('hide_forgot_password_form')) return '';

        // Page URL
        $page_url = $this->page_url($this->option('forgot_password_page'));
        if ($page_url === '') return '';

        // Add Redirect
        if ($redirect && trim($redirect)) $page_url = add_query_arg('redirect_to', urlencode($redirect), $page_url);

        // Add tab
        return add_query_arg('tab', 'lostpassword', $page_url);
    }

    public function wp_redirect()
    {
        $request_uri = isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI'])
            ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']))
            : '';

        if (strpos($request_uri, 'wp-login.php') === false) return;

        // Login Page Redirect
        $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
        if ($this->option('login_form') && !$this->option('hide_login_form') && !$action)
        {
            $redirect_to = isset($_GET['redirect_to']) ? sanitize_text_field(wp_unslash($_GET['redirect_to'])) : '';
            $reauth = isset($_GET['reauth']) ? sanitize_text_field(wp_unslash($_GET['reauth'])) : '';
            wp_redirect($this->get_login_url($redirect_to, $reauth));
            exit;
        }

        // Registration Page Redirect
        if (LSD_Security::registration_allowed() && $this->option('register_form') && !$this->option('hide_register_form') && $action === 'register')
        {
            wp_redirect($this->get_register_url());
            exit;
        }

        // Forgot Password Page Redirect
        if ($this->option('forgot_password_form') && !$this->option('hide_forgot_password_form') && $action === 'lostpassword')
        {
            $redirect_to = isset($_GET['redirect_to']) ? sanitize_text_field(wp_unslash($_GET['redirect_to'])) : '';
            $url = $this->get_forgot_url($redirect_to);
            if (trim($url))
            {
                wp_redirect($url);
                exit;
            }
        }

        // Reset Password Page Redirect
        if ($this->option('forgot_password_form') && !$this->option('hide_forgot_password_form') && in_array($action, ['rp', 'resetpass'], true))
        {
            $redirect_to = isset($_GET['redirect_to']) ? sanitize_text_field(wp_unslash($_GET['redirect_to'])) : '';
            $url = $this->get_forgot_url($redirect_to);

            if (trim($url))
            {
                $key = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';
                $login = isset($_GET['login']) ? sanitize_user(wp_unslash($_GET['login'])) : '';
                $url = add_query_arg([
                    'action' => $action,
                    'key' => $key,
                    'login' => $login,
                ], $url);

                wp_redirect($url);
                exit;
            }
        }
    }

    public function resolve_redirect(string $section, $user, string $current = '', bool $explicit = false): string
    {
        if ($explicit && $current !== '') return $this->validate_redirect($current);
        return $this->role_based_redirect($section, $user, $current !== '' ? $current : home_url('/'));
    }

    private function role_based_redirect(string $section, $user, string $current): string
    {
        $auth = LSD_Options::auth();

        $redirect_url = $current;

        if (isset($auth[$section]))
        {
            $page_id = $auth[$section]['redirect'] ?? 0;

            if ($user instanceof WP_User)
            {
                foreach ((array) $user->roles as $role)
                {
                    $role_key = 'redirect_' . $role;
                    if (($auth[$section][$role_key] ?? 0) == 1)
                    {
                        $page_id = $auth[$section][$role]['redirect'] ?? $page_id;
                        break;
                    }
                }
            }

            $page_url = $this->page_url($page_id);
            if ($page_url !== '') $redirect_url = $page_url;
        }

        return $this->validate_redirect($redirect_url);
    }

    private function security_error(WP_Error $error): void
    {
        $data = $error->get_error_data();
        $this->response(['success' => 0, 'code' => $error->get_error_code(), 'message' => $error->get_error_message(), 'retry_after' => is_array($data) ? ($data['retry_after'] ?? 0) : 0, 'countdown' => is_array($data) ? ($data['countdown'] ?? null) : null]);
    }

    public function validate_redirect(string $url)
    {
        return wp_validate_redirect(
            $url,
            apply_filters('wp_safe_redirect_fallback', admin_url(), 302)
        );
    }

    private function is_truthy($value): bool
    {
        if (is_bool($value)) return $value;

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }

    private function page_url($page_id): string
    {
        $page = get_post(absint($page_id));
        if (!$page instanceof WP_Post) return '';
        if ($page->post_type !== 'page' || $page->post_status !== 'publish') return '';

        $url = get_permalink($page);
        return is_string($url) ? $url : '';
    }

    private function option($key)
    {
        $auth = LSD_Options::auth();
        return $auth['auth'][$key] ?? null;
    }
}
