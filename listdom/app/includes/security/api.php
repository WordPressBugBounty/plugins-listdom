<?php

/** API authentication, restricted challenges, and proof attached to full user tokens. */
class LSD_Security_API
{
    private static ?WP_REST_Request $request = null;

    public static function request(): ?WP_REST_Request
    {
        return self::$request;
    }

    public static function authenticate(WP_REST_Request $request, string $username, string $password)
    {
        if (!self::api_key_valid((string) $request->get_header('lsd-token'))) return new WP_Error('invalid_api_token', __('Invalid API Token!', 'listdom'), ['status' => 403]);
        $previous = self::$request;
        self::$request = $request;
        try
        {
            $user = wp_authenticate($username, $password);
            return $user instanceof WP_User && self::protected_account($user->ID) ? self::challenge($user, $request) : $user;
        }
        finally { self::$request = $previous; }
    }

    public static function protected_account(int $user_id): bool
    {
        return LSD_Security_Totp::enabled($user_id) || LSD_Security_Totp::enrollment_required($user_id);
    }

    public static function valid_user($user): bool
    {
        return $user instanceof WP_User && LSD_User::is_email_verified($user->ID)
            && (!is_multisite() || (empty($user->spam) && empty($user->deleted)));
    }

    public static function api_key_valid(string $key): bool
    {
        foreach (LSD_Options::api()['tokens'] as $token)
        {
            if (isset($token['key']) && is_string($token['key']) && $token['key'] !== '' && hash_equals($token['key'], $key)) return true;
        }
        return false;
    }

    private static function api_hash_valid(string $hash): bool
    {
        foreach (LSD_Options::api()['tokens'] as $token)
        {
            if (!empty($token['key']) && is_string($token['key']) && hash_equals(hash('sha256', $token['key']), $hash)) return true;
        }
        return false;
    }

    public static function challenge(WP_User $user, WP_REST_Request $request, string $purpose = 'login')
    {
        $key = (string) $request->get_header('lsd-token');
        if (!self::api_key_valid($key)) return new WP_Error('invalid_api_token', __('Invalid API Token!', 'listdom'), ['status' => 403]);
        if (!self::valid_user($user)) return new WP_Error('lsd_email_verification_required', LSD_User::verification_required_message(), ['status' => 403]);
        $action = LSD_Security_Totp::enabled($user->ID) ? 'verify' : 'enroll';
        $token = LSD_Security_State::issue('api_challenge', [
            'purpose' => $purpose,
            'user' => $user->ID,
            'api_hash' => hash('sha256', $key),
            'fingerprint' => LSD_Security_Challenge::fingerprint($user),
            'primary' => LSD_Security_Challenge::primary_fingerprint($user),
            'api_epoch' => (string) get_user_meta($user->ID, '_lsd_api_epoch', true),
            'required_action' => $action,
        ], 300);
        if ($token === '') return new WP_Error('lsd_challenge_unavailable', __('Unable to start authentication. Please try again.', 'listdom'), ['status' => 503]);
        $payload = ['success' => 0, 'code' => 'lsd_api_challenge_required', 'challenge' => $token, 'expires_in' => 300, 'required_action' => $action];
        if ($purpose === 'registration') $payload['registration_complete'] = 1;
        return new WP_Error('lsd_api_challenge_required', __('Complete account security verification to sign in.', 'listdom'), $payload);
    }

    public static function pending($result): bool
    {
        return is_wp_error($result) && $result->get_error_code() === 'lsd_api_challenge_required' && !empty($result->get_error_data()['challenge']);
    }

    private static function state(WP_REST_Request $request)
    {
        $key = (string) $request->get_header('lsd-token');
        if (!self::api_key_valid($key)) return new WP_Error('invalid_api_token', __('Invalid API Token!', 'listdom'), ['status' => 403]);
        $token = $request->get_param('challenge');
        $state = is_string($token) ? LSD_Security_State::read('api_challenge', $token) : [];
        if (!$state || !in_array($state['purpose'] ?? '', ['login', 'registration'], true) || !hash_equals($state['api_hash'], hash('sha256', $key))) return self::expired();
        $user = get_userdata($state['user']);
        if (!self::valid_user($user) || !hash_equals($state['fingerprint'], LSD_Security_Challenge::fingerprint($user)) || !hash_equals($state['api_epoch'], (string) get_user_meta($user->ID, '_lsd_api_epoch', true))) return self::expired();
        return $state;
    }

    private static function expired(): WP_Error
    {
        return new WP_Error('lsd_api_challenge_expired', __('This authentication challenge is invalid, expired, or already used. Please sign in again.', 'listdom'), ['status' => 403]);
    }

    public static function enroll(WP_REST_Request $request)
    {
        $state = self::state($request);
        if (is_wp_error($state)) return $state;
        if ($state['required_action'] !== 'enroll' || LSD_Security_Totp::enabled($state['user'])) return new WP_Error('lsd_api_enrollment_not_allowed', __('This challenge does not permit authenticator enrollment.', 'listdom'), ['status' => 403]);
        $token = $request->get_param('challenge');
        // Generating a key is not a code guess; keep its general quota separate.
        $limited = LSD_Security_Rate::check('totp', 'setup:' . $token);
        if (is_wp_error($limited)) return $limited;
        $setup = LSD_Security_Totp::begin((int) $state['user'], hash('sha256', $token));
        if (is_wp_error($setup)) return $setup;
        return ['success' => 0, 'code' => 'lsd_api_enrollment_pending', 'challenge' => $token, 'required_action' => 'enroll', 'expires_in' => max(0, $state['expires'] - time()), 'secret' => $setup['secret'], 'uri' => $setup['uri'], 'qr_code' => $setup['qr_code']];
    }

    public static function finish(WP_REST_Request $request, bool $enroll = false)
    {
        $state = self::state($request);
        if (is_wp_error($state)) return $state;
        if ($state['required_action'] !== ($enroll ? 'enroll' : 'verify')) return new WP_Error('lsd_api_challenge_action', __('Use the action required by this authentication challenge.', 'listdom'), ['status' => 403]);
        $token = $request->get_param('challenge');
        $limited = LSD_Security_Rate::second_factor((int) $state['user'], $token);
        if (is_wp_error($limited)) return $limited;
        $code = $request->get_param('code');
        $code = is_string($code) ? $code : '';
        $result = $enroll ? LSD_Security_Totp::confirm((int) $state['user'], $code, hash('sha256', $token)) : LSD_Security_Totp::verify((int) $state['user'], $code);
        if (is_wp_error($result)) return $result;
        if (!$result) return new WP_Error('lsd_totp_invalid', __('Invalid or already used authenticator or recovery code.', 'listdom'), ['status' => 403]);
        if (!LSD_Security_State::consume('api_challenge', $token)) return self::expired();
        $user = get_userdata($state['user']);
        if (!self::valid_user($user) || !hash_equals($state['primary'], LSD_Security_Challenge::primary_fingerprint($user))) return self::expired();
        if (!$enroll && !hash_equals($state['fingerprint'], LSD_Security_Challenge::fingerprint($user))) return self::expired();
        if (!$enroll && !hash_equals($state['api_epoch'], (string) get_user_meta($user->ID, '_lsd_api_epoch', true))) return self::expired();
        $data = self::complete($user, $request, true);
        if (!is_wp_error($data) && $enroll) $data['recovery_codes'] = $result;
        return $data;
    }

    public static function complete(WP_User $user, WP_REST_Request $request, bool $verified = false)
    {
        $token = self::issue_token($user->ID, $verified);
        if (is_wp_error($token)) return $token;
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, false, is_ssl());
        do_action('wp_login', $user->user_login, $user);
        do_action('lsd_api_user_loggedin', $user->ID, $request);
        return ['success' => 1, 'id' => $user->ID, 'token' => $token];
    }

    public static function issue_token(int $user_id, bool $verified = false)
    {
        $user = get_userdata($user_id);
        if (!self::valid_user($user)) return new WP_Error('lsd_authentication_required', __('Account authentication is required.', 'listdom'), ['status' => 403]);
        if (self::protected_account($user_id) && (!$verified || !LSD_Security_Totp::enabled($user_id))) return new WP_Error('lsd_api_challenge_required', __('Complete account security verification before receiving a user token.', 'listdom'), ['status' => 403]);
        $token = wp_generate_password(40, false);
        $proof = ['token_hash' => hash('sha256', $token), 'fingerprint' => LSD_Security_Challenge::fingerprint($user), 'api_epoch' => (string) get_user_meta($user_id, '_lsd_api_epoch', true), 'verified_at' => $verified ? time() : 0];
        if (!update_user_meta($user_id, '_lsd_api_assurance', $proof) || !update_user_meta($user_id, 'lsd_token', $token)) return new WP_Error('lsd_api_token_unavailable', __('Unable to complete authentication. Please try again.', 'listdom'), ['status' => 503]);
        delete_user_meta($user_id, 'lsd_login');
        return $token;
    }

    public static function valid_token(int $user_id, string $token): bool
    {
        $stored = (string) get_user_meta($user_id, 'lsd_token', true);
        $user = get_userdata($user_id);
        if ($token === '' || !hash_equals($stored, $token) || !self::valid_user($user)) return false;
        $proof = get_user_meta($user_id, '_lsd_api_assurance', true);
        if (!$proof) return !self::protected_account($user_id);
        if (!is_array($proof) || !hash_equals($proof['token_hash'] ?? '', hash('sha256', $token)) || !hash_equals($proof['fingerprint'] ?? '', LSD_Security_Challenge::fingerprint($user))) return false;
        if (!hash_equals($proof['api_epoch'] ?? '', (string) get_user_meta($user_id, '_lsd_api_epoch', true))) return false;
        return !self::protected_account($user_id) || (LSD_Security_Totp::enabled($user_id) && !empty($proof['verified_at']) && $proof['verified_at'] <= time());
    }

    public static function revoke(int $user_id): void
    {
        foreach (['lsd_token', 'lsd_login', '_lsd_api_assurance'] as $key) delete_user_meta($user_id, $key);
        update_user_meta($user_id, '_lsd_api_epoch', bin2hex(random_bytes(16)));
    }

    public static function handoff(int $user_id, WP_REST_Request $request)
    {
        $token = (string) $request->get_header('lsd-user');
        $key = (string) $request->get_header('lsd-token');
        if (!self::api_key_valid($key) || !self::valid_token($user_id, $token)) return new WP_Error('invalid_user_token', __('Please sign in again before requesting a browser login.', 'listdom'), ['status' => 403]);
        $user = get_userdata($user_id);
        $proof = get_user_meta($user_id, '_lsd_api_assurance', true);
        $handoff = LSD_Security_State::issue('handoff', ['purpose' => 'browser_handoff', 'user' => $user_id, 'token_hash' => hash('sha256', $token), 'api_hash' => hash('sha256', $key), 'fingerprint' => LSD_Security_Challenge::fingerprint($user), 'verified_at' => is_array($proof) ? ($proof['verified_at'] ?? 0) : 0], 60);
        if ($handoff === '') return new WP_Error('lsd_handoff_unavailable', __('Unable to start browser login. Please try again.', 'listdom'), ['status' => 503]);
        // A new key supersedes the previous key; the plaintext is never persisted.
        if (!update_user_meta($user_id, 'lsd_login', hash('sha256', $handoff))) return new WP_Error('lsd_handoff_unavailable', __('Unable to start browser login. Please try again.', 'listdom'), ['status' => 503]);
        return $handoff;
    }

    public static function valid_handoff_source(array $data): bool
    {
        $user = get_userdata($data['user']);
        $token = (string) get_user_meta($data['user'], 'lsd_token', true);
        return self::valid_user($user) && self::api_hash_valid($data['api_hash'])
            && hash_equals($data['token_hash'], hash('sha256', $token))
            && hash_equals($data['fingerprint'], LSD_Security_Challenge::fingerprint($user))
            && self::valid_token($user->ID, $token);
    }

    public static function consume_handoff(string $key)
    {
        $data = LSD_Security_State::read('handoff', $key);
        if ($data)
        {
            if (($data['purpose'] ?? '') !== 'browser_handoff' || !self::valid_handoff_source($data) || !hash_equals((string) get_user_meta($data['user'], 'lsd_login', true), hash('sha256', $key))) return self::handoff_error();
            if (!LSD_Security_State::consume('handoff', $key) || !delete_user_meta($data['user'], 'lsd_login', hash('sha256', $key))) return self::handoff_error();
            return $data;
        }
        // Only the old 40-character format is eligible for the unprotected legacy transition.
        if (!preg_match('/^[a-zA-Z0-9]{40}$/D', $key)) return self::handoff_error();
        $users = get_users(['meta_key' => 'lsd_login', 'meta_value' => $key, 'number' => 1, 'count_total' => false]);
        $user = reset($users);
        if (!self::valid_user($user) || self::protected_account($user->ID) || !delete_user_meta($user->ID, 'lsd_login', $key)) return self::handoff_error();
        return ['user' => $user->ID, 'verified_at' => 0, 'legacy' => true];
    }

    public static function recent(array $data): bool
    {
        return !empty($data['verified_at']) && $data['verified_at'] <= time() && $data['verified_at'] >= time() - 300;
    }

    public static function refresh_handoff_proof(array $data): bool
    {
        if (!self::valid_handoff_source($data)) return false;
        $old = get_user_meta($data['user'], '_lsd_api_assurance', true);
        if (!is_array($old)) return false;
        $updated = $old;
        $updated['verified_at'] = time();
        return $updated === $old || update_user_meta($data['user'], '_lsd_api_assurance', $updated, $old);
    }

    private static function handoff_error(): WP_Error
    {
        return new WP_Error('lsd_handoff_invalid', __('This browser login link is invalid, expired, or already used. Please sign in again.', 'listdom'), ['status' => 403]);
    }
}
