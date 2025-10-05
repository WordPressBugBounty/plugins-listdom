<?php

class LSD_Shortcodes_Auth extends LSD_Base
{
    public function init()
    {
        // Shortcodes
        add_shortcode('listdom-login', [$this, 'login']);
        add_shortcode('listdom-register', [$this, 'register']);
        add_shortcode('listdom-forgot-password', [$this, 'forgot_password']);
        add_shortcode('listdom-user-profile', [$this, 'user_profile']);

        // Auth Shortcode
        add_shortcode('listdom-auth', [$this, 'auth']);

        // Login User
        add_action('wp_ajax_lsd_login', [$this, 'signin']);
        add_action('wp_ajax_nopriv_lsd_login', [$this, 'signin']);

        // Register User
        add_action('wp_ajax_lsd_register', [$this, 'signup']);
        add_action('wp_ajax_nopriv_lsd_register', [$this, 'signup']);

        // Forgot Password
        add_action('wp_ajax_lsd_forgot_password', [$this, 'forgot_password_request']);
        add_action('wp_ajax_nopriv_lsd_forgot_password', [$this, 'forgot_password_request']);

        // Reset Password
        add_action('wp_ajax_lsd_reset_password', [$this, 'reset_password_request']);
        add_action('wp_ajax_nopriv_lsd_reset_password', [$this, 'reset_password_request']);

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

        ob_start();
        include lsd_template('auth/profile.php');
        return ob_get_clean();
    }

    public function login($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-login');
        if (trim($pre)) return $pre;

        // User is Already Logged-in
        if (is_user_logged_in()) return $this->user_profile();

        $redirect = $atts['redirect'] ?? '';

        // Role Restriction
        $role = isset($atts['role']) ? sanitize_text_field($atts['role']) : '';
        if (!in_array($role, LSD_User::roles(true), true)) $role = '';

        // Get Redirect from Request
        if (isset($_REQUEST['redirect_to']) && trim($_REQUEST['redirect_to']))
        {
            $requested_redirect = wp_sanitize_redirect(wp_unslash($_REQUEST['redirect_to']));
            if ($requested_redirect !== '') $redirect = wp_validate_redirect($requested_redirect, $redirect);
        }

        ob_start();
        include lsd_template('auth/login.php');
        return ob_get_clean();
    }

    public function register($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-register');
        if (trim($pre)) return $pre;

        // User is Already Logged-in
        if (is_user_logged_in()) return $this->user_profile();

        // Role Restriction
        $role = isset($atts['role']) ? sanitize_text_field($atts['role']) : '';
        if (!in_array($role, LSD_User::roles(true), true)) $role = '';

        ob_start();
        include lsd_template('auth/register.php');
        return ob_get_clean();
    }

    public function forgot_password($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-forgot-password');
        if (trim($pre)) return $pre;

        // User is Already Logged-in
        if (is_user_logged_in()) return $this->user_profile();

        ob_start();
        include lsd_template('auth/forgot-password.php');
        return ob_get_clean();
    }

    public function auth($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-auth');
        if (trim($pre)) return $pre;

        // User is Already Logged-in
        if (is_user_logged_in()) return $this->user_profile();

        // Role Restriction
        $role = isset($atts['role']) ? sanitize_text_field($atts['role']) : '';
        if (!in_array($role, LSD_User::roles(true), true)) $role = '';

        ob_start();
        include lsd_template('auth/auth.php');
        return ob_get_clean();
    }

    public function signin()
    {
        // Check for nonce
        if (
            !isset($_POST['lsd_login'])
            || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lsd_login'])), 'lsd_login')
        ) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing or invalid!', 'listdom')]);

        $user_login = isset($_POST['log']) ? wp_unslash($_POST['log']) : '';
        $user_password = isset($_POST['pwd']) ? wp_unslash($_POST['pwd']) : '';

        // Check for login credentials
        if ($user_login === '' || $user_password === '') $this->response(['success' => 0, 'message' => esc_html__('Username or password is empty.', 'listdom')]);

        $credentials = [
            'user_login' => sanitize_text_field($user_login),
            'user_password' => $user_password,
            'remember' => isset($_POST['rememberme']),
        ];

        // Redirect Url
        $redirect_to = isset($_POST['redirect_to']) && $_POST['redirect_to']
            ? wp_validate_redirect(wp_sanitize_redirect(wp_unslash($_POST['redirect_to'])), '')
            : '';

        // Role Restriction
        $role = isset($_POST['lsd_role']) ? sanitize_text_field(wp_unslash($_POST['lsd_role'])) : '';
        if (!in_array($role, LSD_User::roles(true), true)) $role = '';

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
        if (is_wp_error($user)) $this->response(['success' => 0, 'message' => esc_html__('Invalid username or password.', 'listdom')]);

        $redirect_url = $this->role_based_redirect('login', $user, $redirect_to);

        // Valid Login
        $this->response([
            'success' => 1,
            'message' => esc_html__('Login Successful.', 'listdom'),
            'redirect' => $redirect_url,
        ]);
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

        // Password Policy
        $length = isset($auth['register']['password_length']) ? intval($auth['register']['password_length']) : 8;
        $contain_uppercase = isset($auth['register']['contain_uppercase']) && $auth['register']['contain_uppercase'];
        $contain_lowercase = isset($auth['register']['contain_lowercase']) && $auth['register']['contain_lowercase'];
        $contain_numbers = isset($auth['register']['contain_numbers']) && $auth['register']['contain_numbers'];
        $contain_specials = isset($auth['register']['contain_specials']) && $auth['register']['contain_specials'];

        // Sanitize form values
        $username = sanitize_text_field(wp_unslash($_POST['lsd_username']));
        $email = sanitize_email(wp_unslash($_POST['lsd_email']));
        $password = wp_unslash($_POST['lsd_password']);

        $consent = isset($_POST['lsd_privacy_consent']) ? sanitize_text_field(wp_unslash($_POST['lsd_privacy_consent'])) : '';
        $consent_enabled = LSD_Privacy::is_consent_enabled('register');
        if ($consent_enabled && $consent !== '1') $this->response(['success' => 0, 'message' => LSD_Privacy::consent_required_text()]);

        // Ensure Password Policy is Met
        if (isset($auth['register']['strong_password']) && $auth['register']['strong_password'])
        {
            // Check for minimum length
            if (strlen($password) < $length) $this->response(['success' => 0, 'message' => sprintf(
                /* translators: %s: Minimum required password length. */
                esc_html__('Password must be at-least %s characters long.', 'listdom'),
                $length
            )]);

            // Check for lowercase letters
            if ($contain_lowercase && !preg_match('/[a-z]/', $password)) $this->response(['success' => 0, 'message' => esc_html__('Password must contain at-least one lowercase letter.', 'listdom')]);

            // Check for uppercase letters
            if ($contain_uppercase && !preg_match('/[A-Z]/', $password)) $this->response(['success' => 0, 'message' => esc_html__('Password must contain at-least one uppercase letter.', 'listdom')]);

            // Check for numbers
            if ($contain_numbers && !preg_match('/\d/', $password)) $this->response(['success' => 0, 'message' => esc_html__('Password must contain at-least one number.', 'listdom')]);

            // Check for specials
            if ($contain_specials && !preg_match('/[\W_]/', $password)) $this->response(['success' => 0, 'message' => esc_html__('Password must contain at-least one special character.', 'listdom')]);
        }

        // Check if the username or email already exists
        if (username_exists($username) || email_exists($email)) $this->response(['success' => 0, 'message' => esc_html__('Username and / or email already exist.', 'listdom')]);

        // Role Restriction
        $role = isset($_POST['lsd_role']) ? sanitize_text_field(wp_unslash($_POST['lsd_role'])) : '';
        if (!in_array($role, LSD_User::roles(true), true)) $role = '';

        // Create the user
        $user_data = [
            'user_login' => $username,
            'user_pass'  => $password,
            'user_email' => $email,
        ];

        if ($role) $user_data['role'] = $role;
        $user_id = wp_insert_user($user_data);

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

        // Auto Login
        if (isset($auth['register']['login_after_register']) && $auth['register']['login_after_register'])
        {
            LSD_User::login($user_id);
        }

        // Send success message with redirect
        $redirect_url = '';
        if ($auth['register']['login_after_register'] == 1)
        {
            $user = get_user_by('id', $user_id);

            $redirect_value = isset($_POST['lsd_redirect']) ? wp_sanitize_redirect(wp_unslash($_POST['lsd_redirect'])) : '';
            $redirect_url = $this->role_based_redirect('register', $user, wp_validate_redirect($redirect_value, ''));
        }

        // Send success message with redirect
        $this->response([
            'success' => 1,
            'message' => esc_html__('Registration successful.', 'listdom'),
            'redirect' => $redirect_url,
        ]);
    }

    public function forgot_password_request()
    {
        // Check for nonce security
        if (
            !isset($_POST['lsd_forgot_password_nonce'])
            || !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['lsd_forgot_password_nonce'])),
                'lsd_forgot_password_nonce'
            )
        ) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing or invalid!', 'listdom')]);

        // Check required fields
        if (!isset($_POST['user_login']) || !trim(wp_unslash($_POST['user_login']))) $this->response(['success' => 0, 'message' => esc_html__('Email is required.', 'listdom')]);

        // Sanitize form values
        $email = sanitize_email(wp_unslash($_POST['user_login']));

        // Check if the email exists
        if (!email_exists($email)) $this->response(['success' => 0, 'message' => esc_html__('Email does not exist.', 'listdom')]);

        // Get the user associated with the email
        $user = get_user_by('email', $email);

        if ($user && LSD_User::send_forgot_password_email($user))
        {
            $this->response([
                'success' => 1,
                'message' => esc_html__('Please check your email for the reset link.', 'listdom'),
            ]);
        }

        $this->response([
            'success' => 0,
            'message' => esc_html__('Forgot password email could not be sent.', 'listdom'),
        ]);
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
        if (!isset($_POST['password']) || !isset($_POST['password_confirmation']) || !trim($_POST['password']) || !trim($_POST['password_confirmation'])) $this->response(['success' => 0, 'message' => esc_html__('Password is required.', 'listdom')]);

        $password = wp_unslash($_POST['password']);
        $password_confirmation = wp_unslash($_POST['password_confirmation']);

        // Password length
        if (strlen($password) < 6) $this->response(['success' => 0, 'message' => esc_html__("Password is too short! It should be at least 6 characters.", 'listdom')]);

        // Password confirmation
        if ($password !== $password_confirmation) $this->response(['success' => 0, 'message' => esc_html__("Password does not match its confirmation.", 'listdom')]);

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
        // Auth Options
        $auth = LSD_Options::auth();

        // No Redirect
        if (!isset($auth['logout']) || !isset($auth['logout']['redirect']) || !$auth['logout']['redirect']) return;

        $page_id = $auth['logout']['redirect'];
        $page = get_post($page_id);

        // Invalid or Draft Page
        if (!$page || $page->post_status !== 'publish') return;

        wp_redirect(get_permalink($page));
        exit;
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

        // Page ID
        $page_id = $this->option('login_page');

        // Page
        $page = get_post($page_id);

        // Not a Valid Page
        if (!$page || $page->post_status !== 'publish') return '';

        // Page URL
        $page_url = get_permalink($page_id);

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
        if (!$this->option('register_form') || $this->option('hide_register_form')) return '';

        // Page ID
        $page_id = $this->option('register_page');

        // Page
        $page = get_post($page_id);

        // Not a Valid Page
        if (!$page || $page->post_status !== 'publish') return '';

        // Page URL
        $page_url = get_permalink($page_id);

        // Add tab
        return add_query_arg('tab', 'register', $page_url);
    }

    public function get_forgot_url($redirect = ''): string
    {
        // No Page Configured
        if (!$this->option('forgot_password_form') || $this->option('hide_forgot_password_form')) return '';

        // Page ID
        $page_id = $this->option('forgot_password_page');

        // Page
        $page = get_post($page_id);

        // Not a Valid Page
        if (!$page || $page->post_status !== 'publish') return '';

        // Page URL
        $page_url = get_permalink($page_id);

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
        if ($this->option('register_form') && !$this->option('hide_register_form') && $action === 'register')
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

            if ($page_id)
            {
                $page = get_post($page_id);
                if ($page && $page->post_status === 'publish') $redirect_url = get_permalink($page_id);
            }
        }

        return $this->validate_redirect($redirect_url);
    }

    public function validate_redirect(string $url)
    {
        return wp_validate_redirect(
            $url,
            apply_filters('wp_safe_redirect_fallback', admin_url(), 302)
        );
    }

    private function option($key)
    {
        $auth = LSD_Options::auth();
        return $auth['auth'][$key] ?? null;
    }
}
