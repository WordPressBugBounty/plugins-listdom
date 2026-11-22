<?php

/** Browser- and site-bound wrappers around the shared atomic state store. */
class LSD_Auth_State
{
    public static function browser(bool $create = false): string
    {
        $value = $_COOKIE['lsd_native_browser'] ?? '';
        if (is_string($value) && preg_match('/^[a-f0-9]{64}$/D', $value)) return $value;
        if (!$create || headers_sent()) return '';
        $value = bin2hex(random_bytes(32));
        setcookie('lsd_native_browser', $value, ['expires' => time() + HOUR_IN_SECONDS, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax']);
        $_COOKIE['lsd_native_browser'] = $value;
        return $value;
    }

    public static function issue(string $purpose, array $data, int $ttl = 300): string
    {
        $browser = self::browser(true);
        if ($browser === '') return '';
        return LSD_Security_State::issue('native_' . $purpose, $data + ['site' => get_current_blog_id(), 'browser' => hash('sha256', $browser)], $ttl);
    }

    public static function read(string $purpose, string $token): array
    {
        $data = LSD_Security_State::read('native_' . $purpose, $token);
        $browser = self::browser();
        return $data && $browser !== '' && (int) $data['site'] === get_current_blog_id() && hash_equals($data['browser'], hash('sha256', $browser)) ? $data : [];
    }

    public static function consume(string $purpose, string $token): array
    {
        return self::read($purpose, $token) ? LSD_Security_State::consume('native_' . $purpose, $token) : [];
    }

    public static function expired(): WP_Error
    {
        return new WP_Error('lsd_auth_expired', __('This authentication request is invalid, expired or already used. Please start again in the same browser.', 'listdom'));
    }

    public static function seal(string $secret): string
    {
        if (!LSD_Security_Totp::available()) return '';
        $iv = random_bytes(12);
        $key = hash_hmac('sha256', 'listdom-native-auth|' . get_current_blog_id(), wp_salt('auth'), true);
        $cipher = openssl_encrypt($secret, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, 'listdom-native-v1');
        return $cipher === false ? '' : base64_encode($iv . $tag . $cipher);
    }

    public static function open(string $secret): string
    {
        if (!LSD_Security_Totp::available()) return '';
        $raw = base64_decode($secret, true);
        if ($raw === false || strlen($raw) < 29) return '';
        $key = hash_hmac('sha256', 'listdom-native-auth|' . get_current_blog_id(), wp_salt('auth'), true);
        $value = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16), 'listdom-native-v1');
        return $value === false ? '' : $value;
    }
}
