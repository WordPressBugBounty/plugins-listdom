<?php

class LSD_Auth_Passkeys
{
    public static function available(): bool
    {
        return function_exists('openssl_verify') && extension_loaded('mbstring') && is_ssl();
    }

    public static function enabled(): bool
    {
        return LSD_Base::isPro() && !empty(LSD_Options::auth()['passwordless']['passkeys']) && self::available() && !empty(LSD_Security::settings()['sections']['passkeys']['enabled']);
    }

    public static function library(): \lbuchs\WebAuthn\WebAuthn
    {
        $rp = wp_parse_url(home_url('/'), PHP_URL_HOST);
        return new \lbuchs\WebAuthn\WebAuthn(wp_strip_all_tags(get_bloginfo('name')), $rp, ['none'], true);
    }

    public static function origin(): string
    {
        $parts = wp_parse_url(home_url('/'));
        return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    }

    public static function request(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !wp_verify_nonce(LSD_Security_Account::input('_wpnonce'), 'lsd_native_auth') || !self::enabled()) self::respond(LSD_Auth_State::expired());
        $limited = LSD_Security_Rate::check('native_passkey', LSD_Auth_State::browser(true));
        if (is_wp_error($limited)) self::respond($limited);
        $operation = LSD_Security_Account::input('operation');
        try
        {
            if ($operation === 'login_options' || $operation === 'create_options')
            {
                $context = LSD_Auth::unsign(LSD_Security_Account::input('context'));
                if (!$context) self::respond(LSD_Auth_State::expired());
                $create = $operation === 'create_options';
                if (!$create && is_user_logged_in()) self::respond(LSD_Auth_State::expired());
                $data = ['create' => $create, 'context' => $context];
                $library = self::library();
                if ($create)
                {
                    $proof = LSD_Auth_State::read('reauth', LSD_Security_Account::input('proof'));
                    $user = wp_get_current_user();
                    if (!$proof || !$user->ID || $proof['operation'] !== 'passkey_create:new' || (int) $proof['user'] !== $user->ID || !hash_equals($proof['session'], hash('sha256', wp_get_session_token())) || !hash_equals($proof['fingerprint'], LSD_Security_Challenge::fingerprint($user))) self::respond(LSD_Auth_State::expired());
                    if (!LSD_Auth_State::consume('reauth', LSD_Security_Account::input('proof'))) self::respond(LSD_Auth_State::expired());
                    $handle = hash_hmac('sha256', $user->ID . '|' . get_current_blog_id(), wp_salt('auth'));
                    $ids = array_map(static function (array $row): string { return LSD_Auth_Providers::decode($row['credential_id']); }, LSD_Auth_Store::passkeys($user->ID));
                    $options = $library->getCreateArgs($handle, $user->user_login, $user->display_name, 60, 'required', 'required', null, $ids);
                    $data += ['user' => $user->ID, 'handle' => $handle, 'fingerprint' => LSD_Security_Challenge::fingerprint($user), 'session' => hash('sha256', wp_get_session_token()), 'reauth_expires' => $proof['expires']];
                }
                else $options = $library->getGetArgs([], 60, true, true, true, true, 'required');
                $data['challenge'] = LSD_Auth_Providers::encode($library->getChallenge()->getBinaryString());
                $token = LSD_Auth_State::issue('passkey', $data, 300);
                if (!$token) self::respond(LSD_Auth_State::expired());
                wp_send_json(['success' => 1, 'options' => $options, 'challenge' => $token]);
            }
            if ($operation === 'finish')
            {
                $token = LSD_Security_Account::input('challenge');
                $data = LSD_Auth_State::read('passkey', $token);
                if (!$data) self::respond(LSD_Auth_State::expired());
                $credential = json_decode(LSD_Security_Account::input('credential'), true);
                if (!is_array($credential)) self::respond(LSD_Auth_State::expired());
                $result = self::verify($token, $data, $credential, sanitize_text_field(LSD_Security_Account::input('label')));
                if (is_wp_error($result)) self::respond($result);
                if (!$data['create'])
                {
                    // Session creation stays in the shared HTTP completion service.
                    $finish = LSD_Auth_State::issue('passkeylogin', ['user' => $result['user'], 'fingerprint' => $result['fingerprint'], 'context' => $data['context']], 60);
                    if (!$finish) self::respond(LSD_Auth_State::expired());
                    wp_send_json(['success' => 1, 'redirect' => LSD_Auth::url('passkey-complete', ['proof' => $finish])]);
                }
                if (!empty($data['context']['inline_passkey']))
                {
                    $user = wp_get_current_user();
                    $passkey_context = $data['context'];
                    unset($passkey_context['inline_passkey'], $passkey_context['credential_label']);
                    $passkey_view = [];
                    ob_start();
                    include lsd_template('auth/security/passkey-controls.php');
                    wp_send_json(['success' => 1, 'html' => ob_get_clean(), 'message' => __('Passkey added.', 'listdom')]);
                }
                wp_send_json(['success' => 1, 'redirect' => $data['context']['return']]);
            }
        }
        catch (Throwable $e) { self::respond(new WP_Error('lsd_passkey_invalid', __('The passkey could not be verified. Please try again or use another sign-in method.', 'listdom'))); }
        self::respond(LSD_Auth_State::expired());
    }

    public static function verify(string $token, array $data, array $credential, string $label = '')
    {
        global $wpdb;
        $response = $credential['response'] ?? [];
        if (!is_array($response) || ($credential['type'] ?? '') !== 'public-key') return LSD_Auth_State::expired();
        $client = LSD_Auth_Providers::decode(is_string($response['clientDataJSON'] ?? null) ? $response['clientDataJSON'] : '');
        $client_data = json_decode($client, true);
        if (!$client_data || ($client_data['origin'] ?? '') !== self::origin() || !empty($client_data['crossOrigin'])) return LSD_Auth_State::expired();
        $library = self::library();
        $challenge = LSD_Auth_Providers::decode($data['challenge']);
        if ($data['create'])
        {
            $user = wp_get_current_user();
            if (!$user->ID || (int) $data['user'] !== $user->ID || ($data['reauth_expires'] ?? 0) <= time() || !hash_equals($data['session'], hash('sha256', wp_get_session_token())) || !hash_equals($data['fingerprint'], LSD_Security_Challenge::fingerprint($user))) return LSD_Auth_State::expired();
            $attestation = LSD_Auth_Providers::decode(is_string($response['attestationObject'] ?? null) ? $response['attestationObject'] : '');
            $verified = $library->processCreate($client, $attestation, $challenge, true, true);
            $id = LSD_Auth_Providers::encode($verified->credentialId);
            if (!LSD_Auth_State::consume('passkey', $token)) return LSD_Auth_State::expired();
            $previous = $wpdb->suppress_errors(true);
            try
            {
                $saved = $wpdb->insert($wpdb->prefix . 'lsd_passkeys', ['user_id' => $user->ID, 'credential_hash' => hash('sha256', $id), 'credential_id' => $id, 'public_key' => $verified->credentialPublicKey, 'user_handle' => $data['handle'], 'label' => substr($label ?: __('Passkey', 'listdom'), 0, 191), 'counter' => (int) $verified->signatureCounter, 'created_at' => current_time('mysql', true)]);
            }
            finally { $wpdb->suppress_errors($previous); }
            if (!$saved) return new WP_Error('lsd_passkey_duplicate', __('This passkey is already registered.', 'listdom'));
            do_action('lsd_auth_passkey_created', $user->ID);
            return ['user' => $user->ID];
        }
        $id = is_string($credential['id'] ?? null) ? $credential['id'] : '';
        $row = LSD_Auth_Store::credential($id);
        if (!$row || is_user_logged_in()) return LSD_Auth_State::expired();
        $handle = LSD_Auth_Providers::decode(is_string($response['userHandle'] ?? null) ? $response['userHandle'] : '');
        if (!hash_equals($row['user_handle'], $handle)) return LSD_Auth_State::expired();
        $user = LSD_Auth::eligible((int) $row['user_id'], $data['context'], 'passkey');
        if (is_wp_error($user)) return $user;
        $auth_data = LSD_Auth_Providers::decode(is_string($response['authenticatorData'] ?? null) ? $response['authenticatorData'] : '');
        $signature = LSD_Auth_Providers::decode(is_string($response['signature'] ?? null) ? $response['signature'] : '');
        $library->processGet($client, $auth_data, $signature, $row['public_key'], $challenge, (int) $row['counter'], true, true);
        if (!LSD_Auth_State::consume('passkey', $token)) return LSD_Auth_State::expired();
        $counter = (int) $library->getSignatureCounter();
        // Compare stored credential data too: revocation wins over an in-flight assertion.
        $changed = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}lsd_passkeys SET counter = %d, last_used = %s WHERE id = %d AND counter = %d AND public_key = %s", $counter, current_time('mysql', true), $row['id'], $row['counter'], $row['public_key']));
        // A zero-counter authenticator may legitimately leave the row unchanged in this second.
        $current = LSD_Auth_Store::credential($id);
        if ($changed === false || !$current || ($counter !== 0 && $changed !== 1)) return LSD_Auth_State::expired();
        return ['user' => $user->ID, 'fingerprint' => LSD_Security_Challenge::fingerprint($user)];
    }

    public static function complete(): void
    {
        $data = LSD_Auth_State::consume('passkeylogin', LSD_Security_Account::input('proof', $_GET));
        $user = $data ? get_userdata($data['user']) : false;
        if (!$data || !$user || !self::enabled() || is_user_logged_in() || !hash_equals($data['fingerprint'], LSD_Security_Challenge::fingerprint($user))) LSD_Auth::failure(LSD_Auth_State::expired());
        $result = LSD_Auth::complete($user->ID, $data['context'], 'passkey');
        LSD_Auth::failure($result, $data['context']);
    }

    private static function respond(WP_Error $error): void
    {
        $detail = $error->get_error_data();
        wp_send_json(['success' => 0, 'code' => $error->get_error_code(), 'message' => $error->get_error_message(), 'countdown' => is_array($detail) ? ($detail['countdown'] ?? null) : null]);
    }
}
