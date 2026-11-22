<?php

class LSD_Security_Totp
{
    /** Shared accounts remain protected when any active member site offers 2FA. */
    private static function policies(int $user_id): array
    {
        $policies = [get_current_blog_id() => [
            'settings' => LSD_Security::settings()['sections']['totp'],
            'since' => (array) get_option('_lsd_totp_role_policy', []),
            'epoch' => (string) get_option('_lsd_totp_policy_epoch', ''),
        ]];
        if (is_multisite() && $user_id)
        {
            $network_plugins = (array) get_site_option('active_sitewide_plugins', []);
            foreach (get_blogs_of_user($user_id) as $blog)
            {
                $blog_id = (int) $blog->userblog_id;
                if ($blog_id === get_current_blog_id()) continue;
                if (!isset($network_plugins[LSD_BASENAME]) && !in_array(LSD_BASENAME, (array) get_blog_option($blog_id, 'active_plugins', []), true)) continue;
                $auth = (array) get_blog_option($blog_id, 'lsd_auth', []);
                $policies[$blog_id] = [
                    'settings' => (array) ($auth['security']['sections']['totp'] ?? []),
                    'since' => (array) get_blog_option($blog_id, '_lsd_totp_role_policy', []),
                    'epoch' => (string) get_blog_option($blog_id, '_lsd_totp_policy_epoch', ''),
                ];
            }
        }
        ksort($policies);
        return $policies;
    }

    public static function offered(int $user_id = 0): bool
    {
        foreach (self::policies($user_id) as $policy) if (!empty($policy['settings']['enabled'])) return true;
        return false;
    }

    public static function enabled(int $user_id): bool
    {
        return self::offered($user_id) && (bool) get_user_meta($user_id, '_lsd_totp_secret', true);
    }

    private static function policy_fingerprint(int $user_id): string
    {
        $state = [];
        foreach (self::policies($user_id) as $blog_id => $policy) $state[$blog_id] = [(int) !empty($policy['settings']['enabled']), $policy['epoch']];
        return hash_hmac('sha256', wp_json_encode($state), wp_salt('auth'));
    }

    public static function credential_fingerprint(int $user_id): string
    {
        return hash_hmac('sha256', (string) get_user_meta($user_id, '_lsd_totp_secret', true) . '|' . (string) get_user_meta($user_id, '_lsd_totp_recovery_generation', true) . '|' . self::policy_fingerprint($user_id), wp_salt('auth'));
    }

    public static function available(): bool
    {
        return function_exists('openssl_encrypt') && function_exists('openssl_decrypt') && in_array('aes-256-gcm', openssl_get_cipher_methods(), true);
    }

    private static function key(int $user_id): string
    {
        return hash_hmac('sha256', 'listdom-totp-v1:' . $user_id, wp_salt('auth'), true);
    }

    private static function encrypt(string $secret, int $user_id): string
    {
        if (!self::available()) return '';
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($secret, 'aes-256-gcm', self::key($user_id), OPENSSL_RAW_DATA, $iv, $tag, 'listdom-totp-v1');
        return $ciphertext === false ? '' : base64_encode($iv . $tag . $ciphertext);
    }

    private static function decrypt(string $encrypted, int $user_id): string
    {
        if (!self::available()) return '';
        $data = base64_decode($encrypted, true);
        if ($data === false || strlen($data) < 29) return '';
        $secret = openssl_decrypt(substr($data, 28), 'aes-256-gcm', self::key($user_id), OPENSSL_RAW_DATA, substr($data, 0, 12), substr($data, 12, 16), 'listdom-totp-v1');
        return $secret === false ? '' : $secret;
    }

    public static function begin(int $user_id, string $binding = '')
    {
        if (!self::offered($user_id)) return new WP_Error('lsd_totp_disabled', __('Two-factor authentication is disabled by the administrator.', 'listdom'));
        if (self::enabled($user_id)) return new WP_Error('lsd_totp_enabled', __('Two-factor authentication is already enabled.', 'listdom'));
        if (!self::available()) return new WP_Error('lsd_totp_unavailable', __('This server cannot securely encrypt authenticator secrets. Please contact the administrator.', 'listdom'));
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        $bits = [];
        foreach (str_split(random_bytes(20)) as $byte) $bits[] = str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        foreach (str_split(implode('', $bits), 5) as $chunk) $secret .= $alphabet[bindec($chunk)];
        $encrypted = self::encrypt($secret, $user_id);
        if ($encrypted === '') return new WP_Error('lsd_totp_unavailable', __('Unable to securely store the authenticator secret.', 'listdom'));
        update_user_meta($user_id, '_lsd_totp_pending', ['secret' => $encrypted, 'expires' => time() + 600, 'binding' => $binding, 'policy' => self::policy_fingerprint($user_id)]);
        $user = get_userdata($user_id);
        $issuer = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $uri = 'otpauth://totp/' . rawurlencode($issuer . ':' . $user->user_login) . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer) . '&algorithm=SHA1&digits=6&period=30';
        return ['secret' => $secret, 'uri' => $uri, 'qr_code' => LSD_QR::data_uri($uri)];
    }

    /** RFC 6238 / RFC 4226, with a big-endian 64-bit moving counter. */
    public static function code(string $secret, int $step): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($secret) as $character)
        {
            $index = strpos($alphabet, $character);
            if ($index === false) return '';
            $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }
        $key = '';
        foreach (str_split($bits, 8) as $chunk) if (strlen($chunk) === 8) $key .= chr(bindec($chunk));
        $counter = pack('N2', intdiv($step, 4294967296), $step % 4294967296);
        $hash = hash_hmac('sha1', $counter, $key, true);
        $offset = ord($hash[19]) & 15;
        $value = unpack('N', substr($hash, $offset, 4))[1] & 0x7fffffff;
        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private static function match_step(string $secret, string $code): int
    {
        if ($secret === '' || !preg_match('/^[0-9]{6}$/D', $code)) return -1;
        $current = intdiv(time(), 30);
        foreach ([$current, $current - 1, $current + 1] as $step) if (hash_equals(self::code($secret, $step), $code)) return $step;
        return -1;
    }

    public static function confirm(int $user_id, string $code, string $binding = '')
    {
        $pending = get_user_meta($user_id, '_lsd_totp_pending', true);
        if (!self::offered($user_id)) return new WP_Error('lsd_totp_disabled', __('Two-factor authentication is disabled by the administrator.', 'listdom'));
        if (self::enabled($user_id)) return new WP_Error('lsd_totp_enabled', __('Two-factor authentication is already enabled.', 'listdom'));
        if (!is_array($pending) || ($pending['expires'] ?? 0) <= time() || !hash_equals($pending['binding'] ?? '', $binding) || !hash_equals($pending['policy'] ?? '', self::policy_fingerprint($user_id))) return new WP_Error('lsd_totp_expired', __('Authenticator setup expired. Please start again.', 'listdom'));
        $step = self::match_step(self::decrypt($pending['secret'], $user_id), trim($code));
        if ($step < 0) return new WP_Error('lsd_totp_invalid', __('Enter a valid authenticator code.', 'listdom'));
        if (!delete_user_meta($user_id, '_lsd_totp_pending', $pending)) return new WP_Error('lsd_totp_expired', __('Authenticator setup has already been used.', 'listdom'));
        update_user_meta($user_id, '_lsd_totp_step', $step);
        update_user_meta($user_id, '_lsd_totp_secret', $pending['secret']);
        delete_user_meta($user_id, '_lsd_totp_force_enrollment');
        do_action('lsd_security_totp_enrolled', $user_id);
        return self::recovery_codes($user_id);
    }

    public static function verify(int $user_id, string $code): bool
    {
        global $wpdb;
        if (!self::enabled($user_id)) return false;
        $code = trim($code);
        $recovery = strtolower(str_replace('-', '', $code));
        foreach (preg_match('/^[a-f0-9]{20}$/D', $recovery) ? get_user_meta($user_id, '_lsd_totp_recovery') : [] as $hash)
        {
            if (wp_check_password($recovery, $hash) && delete_user_meta($user_id, '_lsd_totp_recovery', $hash)) return true;
        }
        $secret = self::decrypt((string) get_user_meta($user_id, '_lsd_totp_secret', true), $user_id);
        $step = self::match_step($secret, $code);
        if ($step < 0) return false;
        // One conditional update prevents the same time step succeeding concurrently.
        $row = $wpdb->get_row($wpdb->prepare("SELECT umeta_id, meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = '_lsd_totp_step' LIMIT 1", $user_id));
        if (!$row || $step <= (int) $row->meta_value) return false;
        $updated = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->usermeta} SET meta_value = %s WHERE umeta_id = %d AND meta_value = %s", (string) $step, $row->umeta_id, $row->meta_value));
        wp_cache_delete($user_id, 'user_meta');
        return $updated === 1;
    }

    public static function recovery_codes(int $user_id): array
    {
        delete_user_meta($user_id, '_lsd_totp_recovery');
        update_user_meta($user_id, '_lsd_totp_recovery_generation', bin2hex(random_bytes(16)));
        LSD_Security_API::revoke($user_id);
        $codes = [];
        for ($i = 0; $i < 10; $i++)
        {
            $code = bin2hex(random_bytes(10));
            add_user_meta($user_id, '_lsd_totp_recovery', wp_hash_password($code));
            $codes[] = implode('-', str_split($code, 5));
        }
        return $codes;
    }

    public static function enforced(int $user_id): bool
    {
        return self::deadline($user_id) > 0;
    }

    public static function deadline(int $user_id): int
    {
        $user = get_userdata($user_id);
        if (!$user) return 0;
        global $wpdb;
        $times = [];
        // Multisite cookies are shared; only enabled sites may enforce enrollment.
        foreach (self::policies($user_id) as $blog_id => $policy)
        {
            if (empty($policy['settings']['enabled'])) continue;
            $caps = (array) get_user_meta($user_id, $wpdb->get_blog_prefix($blog_id) . 'capabilities', true);
            foreach ((array) ($policy['settings']['roles'] ?? []) as $role)
            {
                if (is_string($role) && !empty($caps[$role])) $times[] = (int) ($policy['since'][$role] ?? time());
            }
        }
        return $times ? min($times) + 7 * DAY_IN_SECONDS : 0;
    }

    public static function enrollment_required(int $user_id): bool
    {
        $deadline = self::deadline($user_id);
        return !self::enabled($user_id) && $deadline > 0 && ($deadline <= time() || get_user_meta($user_id, '_lsd_totp_force_enrollment', true));
    }

    public static function reset(int $user_id): void
    {
        if (self::enforced($user_id)) update_user_meta($user_id, '_lsd_totp_force_enrollment', 1);
        foreach (['_lsd_totp_secret', '_lsd_totp_pending', '_lsd_totp_step', '_lsd_totp_recovery', '_lsd_totp_recovery_generation', '_lsd_totp_email_reset'] as $key) delete_user_meta($user_id, $key);
        LSD_Security::revoke_credentials($user_id);
        do_action('lsd_security_totp_reset', $user_id);
    }
}
