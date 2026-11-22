<?php

class LSD_Auth_Registration
{
    public static function collision(): WP_Error
    {
        return new WP_Error('lsd_auth_email_conflict', __('This email is unavailable for a new account. Sign in to your existing account, then connect this provider from Account Security.', 'listdom'));
    }

    public static function valid(array $data)
    {
        $provider = $data['identity']['provider'];
        if (!LSD_Auth_Providers::enabled($provider) || !hash_equals($data['config'], LSD_Auth_Providers::fingerprint($provider))) return LSD_Auth_State::expired();
        if (!LSD_Security::registration_allowed()) return new WP_Error('lsd_registration_disabled', __('Public registration is disabled.', 'listdom'));
        if (is_user_logged_in()) return new WP_Error('lsd_auth_session_changed', __('You are already signed in. Manage connections from Account Security.', 'listdom'));
        return true;
    }

    public static function submit(string $token, array $data, array $input)
    {
        $valid = self::valid($data);
        if (is_wp_error($valid)) return $valid;
        $username = sanitize_user(LSD_Security_Account::input('username', $input), true);
        $email = sanitize_email(LSD_Security_Account::input('email', $input));
        if ($username === '' || !validate_username($username) || !is_email($email)) return new WP_Error('lsd_auth_registration_fields', __('Enter a valid username and email address.', 'listdom'));
        $password = LSD_Security_Account::input('password', $input);
        $valid = LSD_Security::password($password, LSD_Security_Account::input('password_confirmation', $input));
        if (is_wp_error($valid)) return $valid;
        $consent = LSD_Security_Account::input('lsd_privacy_consent', $input) === '1';
        if (LSD_Privacy::is_consent_enabled('register') && !$consent) return new WP_Error('lsd_auth_consent', LSD_Privacy::consent_required_text());
        if (email_exists($email)) return self::collision();
        $valid = LSD_Security::registration_check('public', $username, $email);
        if (is_wp_error($valid)) return $valid;
        if (username_exists($username)) return new WP_Error('lsd_auth_username', __('This username is already registered. Please choose another one.', 'listdom'));
        $role = LSD_Security::registration_role($data['context']['role'] ?? '');
        if (is_wp_error($role)) return $role;
        $fields = LSD_Portal_Fieldlayout::registration_values(wp_unslash($input['lsd_registration_fields'] ?? []), wp_unslash($input['lsd_user_fields'] ?? []), $role);
        if (is_wp_error($fields)) return $fields;
        if ($fields['standard'] || $fields['custom'])
        {
            $data['field_secret'] = LSD_Auth_State::seal(wp_json_encode($fields));
            if ($data['field_secret'] === '') return LSD_Auth_State::expired();
        }
        $data['username'] = $username;
        $data['email'] = $email;
        $data['consent'] = $consent;
        // Email confirmation can defer creation; never persist the submitted password in plaintext.
        $data['password_secret'] = LSD_Auth_State::seal($password);
        if ($data['password_secret'] === '') return LSD_Auth_State::expired();
        if (!LSD_Auth_State::consume('view', $token)) return LSD_Auth_State::expired();
        $verified = $data['identity']['email_verified'] && strcasecmp($data['identity']['email'], $email) === 0;
        if (!$verified)
        {
            $limit = LSD_Security_Rate::check('social_email', $email);
            if (is_wp_error($limit)) return $limit;
            $proof = LSD_Auth_State::issue('signupmail', $data, 1800);
            if (!$proof) return LSD_Auth_State::expired();
            $url = LSD_Auth::url('verify-email', ['proof' => $proof]);
            if (!LSD_Notifications_Auth::send('registration', $email, $url))
            {
                LSD_Auth_State::consume('signupmail', $proof);
                return new WP_Error('lsd_auth_email_delivery', __('The confirmation email could not be sent. Please start registration again.', 'listdom'));
            }
            return ['message' => __('Check your email in this browser to confirm your address within 30 minutes and finish registration.', 'listdom')];
        }
        return self::create($data);
    }

    public static function create(array $data)
    {
        $valid = self::valid($data);
        if (is_wp_error($valid)) return $valid;
        $role = LSD_Security::registration_role($data['context']['role'] ?? '');
        if (is_wp_error($role)) return $role;
        $fields = !empty($data['field_secret']) ? json_decode(LSD_Auth_State::open($data['field_secret']), true) : [];
        $fields = LSD_Portal_Fieldlayout::registration_values($fields['standard'] ?? [], $fields['custom'] ?? [], $role);
        if (is_wp_error($fields)) return $fields;
        $password = LSD_Auth_State::open($data['password_secret'] ?? '');
        $valid = LSD_Security::password($password);
        if (is_wp_error($valid)) return $valid;
        if (LSD_Privacy::is_consent_enabled('register') && empty($data['consent'])) return new WP_Error('lsd_auth_consent', LSD_Privacy::consent_required_text());
        // The originating submission already consumed the registration attempt quota.
        $allowed = LSD_Security::registration_check('public', $data['username'], $data['email'], false);
        if (is_wp_error($allowed)) return $allowed;
        $errors = new WP_Error();
        do_action('register_post', $data['username'], $data['email'], $errors);
        $errors = apply_filters('registration_errors', $errors, $data['username'], $data['email']);
        if ($errors->has_errors()) return $errors;
        // The immutable identity and email must both be reserved before creating a user.
        $claim = LSD_Security::reserve_email('identity:' . LSD_Auth_Store::identity_hash($data['identity']['issuer'], $data['identity']['subject']));
        if (is_wp_error($claim)) return $claim;
        try
        {
            $existing = LSD_Auth_Store::identity($data['identity']['issuer'], $data['identity']['subject']);
            if (is_wp_error($existing)) return $existing;
            if ($existing) return LSD_Auth_State::expired();
            if (email_exists($data['email'])) return self::collision();
            $user_id = LSD_Security::insert_user(['user_login' => $data['username'], 'user_email' => $data['email'], 'user_pass' => $password, 'display_name' => $data['identity']['name'] ?: $data['username'], 'role' => $role] + LSD_Portal_Fieldlayout::registration_user_data($fields));
            if (is_wp_error($user_id)) return $user_id;
            $linked = LSD_Auth_Store::link((int) $user_id, $data['identity']);
            if (is_wp_error($linked))
            {
                require_once ABSPATH . 'wp-admin/includes/user.php';
                wp_delete_user($user_id);
                return $linked;
            }
            if (!empty($data['consent'])) update_user_meta($user_id, 'lsd_privacy_consent', LSD_Privacy::create_log(['context' => 'user_registration', 'email' => $data['email'], 'user_id' => $user_id]));
            do_action('register_new_user', $user_id);
            LSD_User::maybe_send_verification_email($user_id);
            do_action('lsd_auth_registered', $user_id, $data['identity']['provider']);
        }
        finally { LSD_Security_State::delete($claim['name'], $claim['record']); }
        $auth = LSD_Options::auth();
        if (!empty($auth['register']['login_after_register']) && !LSD_User::requires_email_verification())
        {
            $context = $data['context'];
            $context['form'] = 'register';
            return LSD_Auth::complete((int) $user_id, $context, $data['identity']['provider']);
        }
        return ['message' => LSD_User::requires_email_verification() ? __('Registration successful. Please check your email to verify your account before signing in.', 'listdom') : __('Registration successful. You can now sign in with your connected provider or your email and password.', 'listdom')];
    }

    public static function email_screen(): void
    {
        $token = LSD_Security_Account::input('proof', $_GET);
        $data = LSD_Auth_State::read('signupmail', $token);
        $result = null;
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST')
        {
            if (!$data || !wp_verify_nonce(LSD_Security_Account::input('_wpnonce'), 'lsd_signup_email_' . $token) || !LSD_Auth_State::consume('signupmail', $token)) $result = LSD_Auth_State::expired();
            else $result = self::create($data);
            if (is_wp_error($result)) LSD_Auth::failure($result, $data['context'] ?? []);
            LSD_Auth::show(['stage' => 'message', 'message' => $result['message']], $data['context']);
        }
        if (!$data) LSD_Auth::failure(LSD_Auth_State::expired());
        // Redirect to the originating Listdom form; never create an account on email GET.
        LSD_Auth::show(['stage' => 'email-confirm', 'proof' => $token, 'email' => $data['email']], $data['context']);
    }
}
