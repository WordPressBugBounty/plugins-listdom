<?php

class LSD_Auth_Settings
{
    /** Secrets are deliberately separate from Auth settings, exports and catalog snapshots. */
    public static function prepare_secrets(array $input)
    {
        $secrets = get_option('lsd_auth_secrets', []);
        if (!is_array($secrets)) $secrets = [];
        foreach (LSD_Auth_Providers::all() as $provider => $definition)
        {
            $value = isset($input[$provider]) && is_array($input[$provider]) ? $input[$provider] : [];
            if (!empty($value['remove'])) unset($secrets[$provider]);
            $secret = is_string($value['secret'] ?? null) ? trim($value['secret']) : '';
            if ($secret === '') continue;
            if (!LSD_Security_Totp::available()) return new WP_Error('lsd_auth_encryption', __('Secure credential storage requires OpenSSL with AES-256-GCM support.', 'listdom'));
            if ($provider === 'apple')
            {
                $key = openssl_pkey_get_private($secret);
                $details = $key ? openssl_pkey_get_details($key) : [];
                if (!$key || ($details['type'] ?? null) !== OPENSSL_KEYTYPE_EC || ($details['bits'] ?? 0) !== 256) return new WP_Error('lsd_apple_key', __('Apple requires a valid P-256 private signing key in PEM format.', 'listdom'));
            }
            $encrypted = LSD_Auth_State::seal($secret);
            if ($encrypted === '') return new WP_Error('lsd_auth_encryption', __('Unable to securely store the provider credential.', 'listdom'));
            $secrets[$provider] = $encrypted;
        }
        return $secrets;
    }

    public static function diagnose(): void
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce(LSD_Security_Account::input('_wpnonce', $_GET), 'lsd_native_diagnose')) wp_send_json(['success' => 0, 'message' => __('You cannot inspect provider connections.', 'listdom')], 403);
        $provider = sanitize_key(LSD_Security_Account::input('provider', $_GET));
        if (!isset(LSD_Auth_Providers::all()[$provider])) wp_send_json(['success' => 0, 'message' => __('Unknown provider.', 'listdom')], 400);
        if (!LSD_Auth_Providers::available() || !is_ssl()) wp_send_json(['success' => 0, 'message' => __('HTTPS, OpenSSL signing, verification and AES-256-GCM, and Multibyte String are required.', 'listdom')]);
        $config = LSD_Auth_Providers::config($provider);
        if ($config['client_id'] === '' || LSD_Auth_Providers::secret($provider) === '' || ($provider === 'apple' && ($config['team_id'] === '' || $config['key_id'] === ''))) wp_send_json(['success' => 0, 'message' => __('Save the client ID and required credentials first.', 'listdom')]);
        $discovery = LSD_Auth_Providers::discovery($provider);
        if (is_wp_error($discovery)) wp_send_json(['success' => 0, 'message' => $discovery->get_error_message()]);
        $keys = LSD_Auth_Providers::request($discovery['jwks_uri'], LSD_Auth_Providers::all()[$provider]);
        if (is_wp_error($keys) || empty($keys['keys'])) wp_send_json(['success' => 0, 'message' => __('The provider signing keys could not be retrieved.', 'listdom')]);
        try { LSD_Auth_Providers::client_secret($provider); }
        catch (Throwable $e) { wp_send_json(['success' => 0, 'message' => __('The stored signing credential is invalid. Replace it and try again.', 'listdom')]); }
        wp_send_json(['success' => 1, 'message' => __('Connection checks passed. Provider discovery and signing keys are reachable, and credentials are saved. Complete a real sign-in to verify client credentials, permissions and the callback URL.', 'listdom')]);
    }
}
