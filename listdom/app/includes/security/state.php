<?php

/** Short-lived server state; conditional deletion provides one-time consumption. */
class LSD_Security_State
{
    public static function key(string $purpose, string $token): string
    {
        return '_lsd_sec_' . $purpose . '_' . hash_hmac('sha256', $token, wp_salt('auth'));
    }

    public static function issue(string $purpose, array $data, int $ttl): string
    {
        $token = bin2hex(random_bytes(32));
        $data['expires'] = time() + $ttl;
        $name = self::key($purpose, $token);
        if (!add_option($name, $data, '', false)) return '';
        return $token;
    }

    public static function read(string $purpose, string $token): array
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) return [];
        $data = get_option(self::key($purpose, $token), []);
        return is_array($data) && ($data['expires'] ?? 0) > time() ? $data : [];
    }

    public static function consume(string $purpose, string $token): array
    {
        global $wpdb;
        $data = self::read($purpose, $token);
        if (!$data) return [];
        $name = self::key($purpose, $token);
        return self::delete($name, $data) ? $data : [];
    }

    public static function delete(string $name, array $data): bool
    {
        global $wpdb;
        // Compare the full value so competing confirmations cannot consume it twice.
        $deleted = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s", $name, maybe_serialize($data)));
        wp_cache_delete($name, 'options');
        return $deleted === 1;
    }

    public static function cleanup(): void
    {
        global $wpdb;
        // A member site's cron also cleans shared counters when the root site is inactive.
        $shared = $wpdb->base_prefix . 'options';
        if (is_multisite() && $wpdb->options !== $shared)
        {
            $rates = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$shared} WHERE option_name LIKE %s LIMIT 1000", $wpdb->esc_like('_lsd_sec_rate_') . '%'));
            foreach ($rates as $name)
            {
                $parts = explode('_', $name);
                if ((int) ($parts[4] ?? 0) <= time()) $wpdb->delete($shared, ['option_name' => $name]);
            }
        }
        $names = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 1000", $wpdb->esc_like('_lsd_sec_') . '%'));
        foreach ($names as $name)
        {
            if (strpos($name, '_lsd_sec_rate_') === 0)
            {
                $parts = explode('_', $name);
                if ((int) ($parts[4] ?? 0) <= time()) delete_option($name);
                continue;
            }
            $data = get_option($name);
            if (is_array($data) && ($data['expires'] ?? 0) <= time()) delete_option($name);
        }
    }
}
