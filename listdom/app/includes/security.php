<?php

/** Shared security policy. Presentation and transport adapters never grant trust. */
class LSD_Security extends LSD_Base
{
    private static bool $api_push_authorized = false;
    private static bool $paid_order_authorized = false;

    public function init(): void
    {
        add_action('init', [self::class, 'upgrade'], 0);
        add_action('switch_blog', [self::class, 'upgrade']);
        add_action('lsd_security_cleanup', [LSD_Security_State::class, 'cleanup']);
        add_action('lsd_security_mail', [self::class, 'send_mail']);
        add_action('lsd_security_verification_migration', [LSD_User::class, 'migrate_verification_tokens']);
        add_filter('pre_update_option_lsd_auth', [self::class, 'sanitize_settings'], 10, 2);
        add_action('update_option_lsd_auth', [self::class, 'policy_changed'], 10, 2);
        add_action('add_option_lsd_auth', [self::class, 'policy_added'], 10, 2);
        add_filter('authenticate', [self::class, 'login_limit'], 5, 3);
        add_filter('authenticate', [self::class, 'login_limit'], 100, 3);
        add_filter('authenticate', [self::class, 'login_result'], 101, 3);
        add_action('validate_password_reset', [self::class, 'validate_reset'], 10, 2);
        add_action('after_password_reset', [self::class, 'password_reset']);
        add_action('lostpassword_form', [self::class, 'native_recovery_nonce']);
        add_action('login_form_lostpassword', [self::class, 'native_recovery']);
        add_action('login_enqueue_scripts', [LSD_Countdown::class, 'enqueue']);
        add_filter('wp_login_errors', [self::class, 'countdown_errors']);
        (new LSD_Security_Account())->init();
        (new LSD_Security_Challenge())->init();
        (new LSD_Security_Recovery())->init();
    }

    public static function defaults(): array
    {
        return [
            'page' => '',
            'rate_enabled' => 1,
            'login_pair_limit' => 10,
            'login_ip_limit' => 60,
            'register_limit' => 5,
            'mail_ip_limit' => 5,
            'mail_identifier_limit' => 3,
            'resend_cooldown' => 60,
            'section_order' => array_keys(LSD_Security_Sections::definitions()),
            'sections' => LSD_Security_Sections::defaults(),
        ];
    }

    public static function upgrade(): void
    {
        // WordPress switches to new multisite sites before creating their tables.
        if (is_multisite() && function_exists('wp_is_site_initialized') && !wp_is_site_initialized(get_current_blog_id())) return;
        if (get_option('lsd_security_version')) return;
        $auth = get_option('lsd_auth', []);
        if (!is_array($auth)) $auth = [];
        $installed = (bool) get_option('lsd_settings');
        $auth['register']['policy'] = $auth['register']['policy'] ?? ($installed ? 'enabled' : 'wordpress');
        $roles = $installed ? ['subscriber', 'contributor', 'listdom_author', 'listdom_publisher'] : ['subscriber'];
        $auth['register']['public_roles'] = $auth['register']['public_roles'] ?? array_values(array_filter($roles, [self::class, 'safe_role']));
        $default = get_option('default_role', 'subscriber');
        $auth['register']['default_role'] = $auth['register']['default_role'] ?? (in_array($default, $auth['register']['public_roles'], true) ? $default : 'subscriber');
        update_option('lsd_auth', $auth);
        add_option('lsd_security_version', 1, '', false);
        add_option('lsd_security_upgraded_at', time(), '', false);
        LSD_User::migrate_verification_tokens();
        if (!wp_next_scheduled('lsd_security_cleanup')) wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', 'lsd_security_cleanup');
    }

    public static function settings(): array
    {
        $auth = LSD_Options::auth();
        return self::normalize_security((array) ($auth['security'] ?? []));
    }

    public static function normalize_security(array $values, array $previous = []): array
    {
        $defaults = self::defaults();
        $security = array_replace($defaults, array_intersect_key($previous, $defaults), array_intersect_key($values, $defaults));
        foreach (['login_pair_limit', 'login_ip_limit', 'register_limit', 'mail_ip_limit', 'mail_identifier_limit', 'resend_cooldown'] as $key) $security[$key] = max(1, min(3600, (int) $security[$key]));
        $security['rate_enabled'] = (int) !empty($security['rate_enabled']);
        $security['page'] = absint($security['page']);
        $security['section_order'] = LSD_Security_Sections::order($security['section_order']);
        $security['sections'] = LSD_Security_Sections::sanitize(
            isset($values['sections']) && is_array($values['sections']) ? $values['sections'] : [],
            isset($previous['sections']) && is_array($previous['sections']) ? $previous['sections'] : []
        );
        return $security;
    }

    public static function safe_role(string $role): bool
    {
        $definition = get_role($role);
        if (!$definition || in_array($role, ['administrator', 'editor'], true)) return false;
        // A capability ceiling also catches a renamed or modified administrative role.
        $ceiling = ['read', 'level_0', 'level_1', 'level_2', 'edit_posts', 'delete_posts', 'publish_posts', 'edit_published_posts', 'delete_published_posts', 'upload_files', 'edit_listings', 'delete_listings', 'edit_listing'];
        $ceiling = apply_filters('lsd_security_public_role_capabilities', $ceiling, $role);
        $administrative = ['manage_options', 'edit_users', 'create_users', 'delete_users', 'promote_users', 'list_users', 'edit_others_posts', 'delete_others_posts', 'edit_pages', 'manage_categories', 'unfiltered_html', 'activate_plugins', 'install_plugins', 'edit_plugins', 'switch_themes', 'edit_theme_options', 'update_core', 'manage_network', 'manage_sites'];
        foreach ($definition->capabilities as $capability => $granted)
        {
            if ($granted && (in_array($capability, $administrative, true) || !in_array($capability, $ceiling, true))) return false;
        }
        return true;
    }

    public static function public_roles(): array
    {
        $auth = LSD_Options::auth();
        return array_values(array_filter((array) ($auth['register']['public_roles'] ?? ['subscriber']), static function ($role): bool
        {
            return is_string($role) && self::safe_role($role);
        }));
    }

    public static function registration_role(string $requested = '', string $context = 'public')
    {
        $auth = LSD_Options::auth();
        $role = $requested !== '' ? $requested : ($auth['register']['default_role'] ?? 'subscriber');
        if ($requested === '' && $context !== 'public' && self::registration_allowed($context) && !in_array($role, self::public_roles(), true)) $role = 'subscriber';
        if ($requested === '' && $context !== 'public' && $role === 'subscriber' && self::safe_role($role)) return $role;
        if (!in_array($role, self::public_roles(), true)) return new WP_Error('lsd_registration_role', __('This registration role is not available. Please contact the site administrator.', 'listdom'));
        return $role;
    }

    public static function registration_allowed(string $context = 'public'): bool
    {
        if (is_multisite() && in_array($context, ['public', 'guest_submission'], true) && !in_array(get_site_option('registration', 'none'), ['user', 'all'], true)) return false;
        if ($context === 'import' && (current_user_can('create_users') || wp_doing_cron())) return true;
        if ($context === 'approval' && (current_user_can('edit_others_posts') || wp_doing_cron())) return true;
        if ($context === 'api_push' && self::$api_push_authorized) return true;
        if ($context === 'paid_order' && self::$paid_order_authorized) return true;
        if ($context === 'guest_submission')
        {
            $settings = LSD_Options::settings();
            if (self::isPro() && !empty($settings['submission_guest'])) return true;
        }
        if ($context !== 'public') return false;
        $auth = LSD_Options::auth();
        $policy = $auth['register']['policy'] ?? 'wordpress';
        if ($policy === 'disabled') return false;
        return $policy === 'enabled' || (bool) get_option('users_can_register');
    }

    public static function registration_check(string $context = 'public', string $username = '', string $email = '', bool $rate_limit = true)
    {
        if (!self::registration_allowed($context)) return new WP_Error('lsd_registration_disabled', __('Public registration is disabled.', 'listdom'));
        if ($rate_limit && ($context === 'public' || $context === 'guest_submission'))
        {
            $limited = LSD_Security_Rate::check('register', $email);
            if (is_wp_error($limited)) return $limited;
        }
        if (is_multisite() && in_array($context, ['public', 'guest_submission'], true))
        {
            $result = wpmu_validate_user_signup($username, $email);
            if ($result['errors']->has_errors()) return $result['errors'];
        }
        return true;
    }

    public static function password(string $password, ?string $confirmation = null)
    {
        $rules = self::password_rules();
        $length = preg_match_all('/./us', $password);
        if ($length === false || $length < $rules['length']) return new WP_Error('lsd_password_length', sprintf(
            /* translators: %d: minimum password length. */
            __('Password must be at least %d characters long.', 'listdom'), $rules['length']
        ));
        foreach (['lowercase' => '/[a-z]/', 'uppercase' => '/[A-Z]/', 'numbers' => '/[0-9]/', 'specials' => '/[^a-zA-Z0-9]/'] as $key => $pattern)
        {
            if (!empty($rules[$key]) && !preg_match($pattern, $password)) return new WP_Error('lsd_password_' . $key, self::password_description());
        }
        if ($confirmation !== null && $password !== $confirmation) return new WP_Error('lsd_password_confirmation', __('Passwords do not match.', 'listdom'));
        return true;
    }

    /** Serialize Listdom registration and email-change claims for the same address. */
    public static function reserve_email(string $email)
    {
        $name = LSD_Security_State::key('emailclaim', strtolower($email));
        $previous = get_option($name, []);
        if (is_array($previous) && $previous && ($previous['expires'] ?? 0) <= time()) LSD_Security_State::delete($name, $previous);
        $record = ['expires' => time() + 60, 'owner' => bin2hex(random_bytes(16))];
        if (!add_option($name, $record, '', false)) return new WP_Error('lsd_email_busy', __('This email address is being processed. Please try again shortly.', 'listdom'));
        return ['name' => $name, 'record' => $record];
    }

    public static function insert_user(array $data)
    {
        $claim = self::reserve_email($data['user_email']);
        if (is_wp_error($claim)) return $claim;
        try { return wp_insert_user($data); }
        finally { LSD_Security_State::delete($claim['name'], $claim['record']); }
    }

    public static function password_rules(): array
    {
        $auth = LSD_Options::auth();
        $config = $auth['register'];
        $strong = !empty($config['strong_password']);
        $rules = ['length' => $strong ? max(8, min(128, (int) $config['password_length'])) : 6];
        foreach (['lowercase', 'uppercase', 'numbers', 'specials'] as $key) $rules[$key] = $strong && !empty($config['contain_' . $key]);
        return $rules;
    }

    public static function password_description(): string
    {
        $rules = self::password_rules();
        $parts = [sprintf(
            /* translators: %d: minimum password length. */
            __('At least %d characters', 'listdom'), $rules['length']
        )];
        foreach (['lowercase' => __('a lowercase letter', 'listdom'), 'uppercase' => __('an uppercase letter', 'listdom'), 'numbers' => __('a number', 'listdom'), 'specials' => __('a special character', 'listdom')] as $key => $label)
        {
            if ($rules[$key]) $parts[] = $label;
        }
        return implode(', ', $parts) . '.';
    }

    public static function generated_password(): string
    {
        // WordPress trims passwords before hashing. Keep random spaces away from the boundaries.
        return 'aA1!' . wp_generate_password(max(20, self::password_rules()['length']), true, true) . '!';
    }

    public static function reauthenticate(int $user_id, string $password, string $code = '', bool $second_factor = true)
    {
        $limited = LSD_Security_Rate::check('reauth', (string) $user_id, false);
        if (is_wp_error($limited)) return $limited;
        $user = get_userdata($user_id);
        if (!$user || !wp_check_password($password, $user->user_pass, $user_id))
        {
            LSD_Security_Rate::check('reauth', (string) $user_id);
            return new WP_Error('lsd_reauthentication', __('Your current password is incorrect.', 'listdom'));
        }
        if ($second_factor && LSD_Security_Totp::enabled($user_id))
        {
            $limited = LSD_Security_Rate::second_factor($user_id, 'reauth:' . $user_id);
            if (is_wp_error($limited)) return $limited;
            if (!LSD_Security_Totp::verify($user_id, $code)) return new WP_Error('lsd_totp_invalid', __('Enter a valid authenticator or recovery code.', 'listdom'));
        }
        return true;
    }

    /** Configured API push authorization permits its named registration context only. */
    public static function api_push(callable $callback)
    {
        $previous = self::$api_push_authorized;
        self::$api_push_authorized = true;
        try { return $callback(); }
        finally { self::$api_push_authorized = $previous; }
    }

    /** Only trusted fulfillment code may authorize the named paid-order context. */
    public static function paid_order(callable $callback)
    {
        $previous = self::$paid_order_authorized;
        self::$paid_order_authorized = true;
        try { return $callback(); }
        finally { self::$paid_order_authorized = $previous; }
    }

    public static function login_limit($user, $username, $password)
    {
        if (!is_string($username) || $username === '' || $password === '') return $user;
        // Core password callbacks can replace an earlier error; reapply the quota afterward.
        $limited = LSD_Security_Rate::check('login', $username, false);
        return is_wp_error($limited) ? $limited : $user;
    }

    public static function login_result($user, $username, $password)
    {
        // Second-factor continuations have already passed primary authentication.
        if (is_wp_error($user)
            && !in_array($user->get_error_code(), ['lsd_rate_limited', 'lsd_totp_required', 'lsd_api_challenge_required'], true)
            && is_string($username) && $username !== '' && $password !== '')
        {
            LSD_Security_Rate::check('login', $username);
        }
        return $user;
    }

    public static function validate_reset($errors, $user): void
    {
        if (!isset($_POST['pass1']) || !is_string($_POST['pass1'])) return;
        $result = self::password(wp_unslash($_POST['pass1']));
        if (is_wp_error($result)) $errors->add($result->get_error_code(), $result->get_error_message());
    }

    public static function password_reset($user): void
    {
        if ($user instanceof WP_User) self::revoke_credentials($user->ID);
    }

    public static function revoke_credentials(int $user_id): void
    {
        WP_Session_Tokens::get_instance($user_id)->destroy_all();
        LSD_Security_API::revoke($user_id);
        update_user_meta($user_id, '_lsd_security_epoch', wp_generate_password(32, false));
    }

    public static function recovery_message(): string
    {
        return __('If the account is eligible, you will receive an email with the next steps. Please check your inbox.', 'listdom');
    }

    public static function find_user(string $identifier)
    {
        return get_user_by('login', $identifier) ?: get_user_by('email', $identifier);
    }

    public static function recover_password(string $identifier, string $recovery_page = '')
    {
        if (trim($identifier) === '') return new WP_Error('lsd_identifier_required', __('Enter your username or email address.', 'listdom'));
        $limit = LSD_Security_Rate::check('recovery', $identifier);
        if (is_wp_error($limit)) return $limit;
        $user = self::find_user($identifier);
        self::queue_mail('password', $user instanceof WP_User ? $user : null, ['recovery_page' => $recovery_page]);
        return true;
    }

    /** Eligible and unknown accounts take the same queued delivery path. */
    public static function queue_mail(string $purpose, ?WP_User $user, array $context = []): void
    {
        $data = ['purpose' => $purpose, 'user' => $user ? $user->ID : 0, 'fingerprint' => $user ? LSD_Security_Challenge::fingerprint($user) : ''];
        if (in_array($purpose, ['password', 'totp_reset'], true) && !empty($context['recovery_page']) && is_string($context['recovery_page'])) $data['recovery_page'] = $context['recovery_page'];
        $token = LSD_Security_State::issue('mail', $data, HOUR_IN_SECONDS);
        wp_schedule_single_event(time(), 'lsd_security_mail', [$token]);
        if ((!defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON) && !wp_doing_cron()) spawn_cron();
    }

    public static function send_mail(string $token): void
    {
        $data = LSD_Security_State::consume('mail', $token);
        $user = $data && $data['user'] ? get_userdata($data['user']) : false;
        if (!$user || !hash_equals($data['fingerprint'], LSD_Security_Challenge::fingerprint($user))) return;
        if ($data['purpose'] === 'password') LSD_User::send_forgot_password_email($user, $data['recovery_page'] ?? '');
        else if ($data['purpose'] === 'verification')
        {
            if (LSD_User::requires_email_verification() && !LSD_User::is_email_verified($user->ID)) LSD_User::maybe_send_verification_email($user->ID, true);
        }
        else if ($data['purpose'] === 'totp_reset' && LSD_Security_Totp::enabled($user->ID))
        {
            $key = LSD_Security_State::issue('totpreset', ['user' => $user->ID, 'fingerprint' => LSD_Security_Challenge::fingerprint($user)], 1800);
            update_user_meta($user->ID, '_lsd_totp_email_reset', hash('sha256', $key));
            $url = LSD_Security_Recovery::url($key, $data['recovery_page'] ?? '');
            LSD_Notifications_Security::totp_recovery($user->ID, 'requested', $url);
        }
    }

    public static function sanitize_settings($value, $old = []): array
    {
        $value = is_array($value) ? $value : [];
        $value['social_login'] = LSD_Auth_Providers::sanitize((array) ($value['social_login'] ?? ($old['social_login'] ?? [])));
        $value['social_login_buttons'] = LSD_Auth_Buttons::sanitize((array) ($value['social_login_buttons'] ?? ($old['social_login_buttons'] ?? [])));
        $passwordless = (array) ($value['passwordless'] ?? ($old['passwordless'] ?? []));
        $value['passwordless'] = ['magic' => (int) !empty($passwordless['magic']), 'passkeys' => (int) !empty($passwordless['passkeys'])];
        $register = isset($value['register']) && is_array($value['register']) ? $value['register'] : [];
        $register['policy'] = in_array($register['policy'] ?? '', ['wordpress', 'enabled', 'disabled'], true) ? $register['policy'] : ($old['register']['policy'] ?? 'wordpress');
        $roles = (array) ($register['public_roles'] ?? ($old['register']['public_roles'] ?? ['subscriber']));
        $register['public_roles'] = array_values(array_unique(array_filter($roles, static function ($role): bool { return is_string($role) && self::safe_role($role); })));
        $default = $register['default_role'] ?? ($old['register']['default_role'] ?? 'subscriber');
        $register['default_role'] = in_array($default, $register['public_roles'], true) ? $default : ($register['public_roles'][0] ?? '');
        $value['register'] = $register;
        $value['security'] = self::normalize_security((array) ($value['security'] ?? []), (array) ($old['security'] ?? []));
        // Raw option updates, including imports, must not store unenforceable policies.
        if (is_wp_error(self::validate_settings($value))) return is_array($old) ? $old : [];
        return $value;
    }

    public static function validate_settings(array $value)
    {
        $register = (array) ($value['register'] ?? []);
        $roles = array_values(array_filter((array) ($register['public_roles'] ?? [])));
        foreach ($roles as $role)
        {
            if (!is_string($role) || !self::safe_role($role)) return new WP_Error('lsd_registration_role', __('An unsafe or unsupported public registration role was selected.', 'listdom'));
        }
        if (isset($register['default_role']) && $register['default_role'] !== '' && !in_array($register['default_role'], $roles, true)) return new WP_Error('lsd_registration_role', __('The default registration role must be approved for public registration.', 'listdom'));
        $sections = (array) ($value['security']['sections'] ?? []);
        if (array_diff(array_keys($sections), array_keys(LSD_Security_Sections::definitions()))) return new WP_Error('lsd_security_sections', __('Only built-in security sections are supported.', 'listdom'));
        if (isset($sections['password']['enabled']) && (int) $sections['password']['enabled'] !== 1) return new WP_Error('lsd_security_password_required', __('Password Change must always be enabled.', 'listdom'));
        if (!empty($sections['totp']['enabled']) && !empty(array_filter((array) ($sections['totp']['roles'] ?? []))) && !LSD_Security_Totp::available()) return new WP_Error('lsd_totp_unavailable', __('Secure authenticator encryption is required before enabling role enforcement.', 'listdom'));
        return true;
    }

    public static function policy_added($option, $value): void
    {
        self::policy_changed([], $value);
    }

    public static function policy_changed($old, $value): void
    {
        $enabled = !empty($value['security']['sections']['totp']['enabled']);
        $was_enabled = !empty($old['security']['sections']['totp']['enabled']);
        if ($enabled !== $was_enabled) update_option('_lsd_totp_policy_epoch', wp_generate_password(32, false), false);
        $previous = $was_enabled ? (array) get_option('_lsd_totp_role_policy', []) : [];
        $since = [];
        foreach ($enabled ? (array) ($value['security']['sections']['totp']['roles'] ?? []) : [] as $role)
        {
            if (is_string($role) && $role !== '') $since[$role] = $previous[$role] ?? time();
        }
        update_option('_lsd_totp_role_policy', $since, false);
    }

    public static function countdown_errors(WP_Error $errors): WP_Error
    {
        foreach ($errors->get_error_codes() as $code)
        {
            $data = $errors->get_error_data($code);
            if (is_array($data) && !empty($data['countdown'])) $errors->errors[$code] = [LSD_Countdown::html($data['countdown'])];
        }
        return $errors;
    }

    public static function native_recovery_nonce(): void
    {
        wp_nonce_field('lsd_native_recovery', 'lsd_recovery_nonce');
    }

    public static function native_recovery(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return;
        if (!wp_verify_nonce(LSD_Security_Account::input('lsd_recovery_nonce'), 'lsd_native_recovery')) wp_die(esc_html__('Security nonce is missing or invalid.', 'listdom'));
        $result = self::recover_password(sanitize_text_field(LSD_Security_Account::input('user_login')));
        if (is_wp_error($result))
        {
            if ($result->get_error_code() === 'lsd_rate_limited')
            {
                login_header(esc_html__('Password Recovery', 'listdom'), '', self::countdown_errors($result));
                login_footer();
                exit;
            }
            wp_die(esc_html($result->get_error_message()));
        }
        wp_safe_redirect(add_query_arg('checkemail', 'confirm', site_url('wp-login.php', 'login')));
        exit;
    }
}
