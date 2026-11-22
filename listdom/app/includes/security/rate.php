<?php

class LSD_Security_Rate
{
    public static function ip(): string
    {
        $remote = $_SERVER['REMOTE_ADDR'] ?? '';
        $ip = is_string($remote) && filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '';
        return (string) apply_filters('lsd_security_client_ip', $ip);
    }

    public static function check(string $operation, string $identifier = '', bool $increment = true)
    {
        $s = LSD_Security::settings();
        // The account-wide second-factor budget cannot be disabled with general throttling.
        if (!$s['rate_enabled'] && $operation !== 'totp_account') return true;
        $ip = self::ip();
        $identifier = strtolower(trim($identifier));
        if ($operation === 'login') $buckets = [[$ip, $s['login_ip_limit'], 900], [$ip . '|' . $identifier, $s['login_pair_limit'], 900]];
        else if ($operation === 'register') $buckets = [[$ip, $s['register_limit'], 3600]];
        else if ($operation === 'totp') $buckets = [[$ip, $s['sections']['totp']['ip_limit'], 900], [$identifier, $s['sections']['totp']['challenge_limit'], 300]];
        else if ($operation === 'totp_account') $buckets = [[$identifier, min(5, (int) $s['sections']['totp']['challenge_limit']), 300]];
        else if ($operation === 'reauth') $buckets = [[$ip . '|' . $identifier, $s['login_pair_limit'], 900]];
        else if (in_array($operation, ['native_start', 'native_passkey'], true)) $buckets = [[$ip, $s['login_ip_limit'], 900], [$ip . '|' . $identifier, $s['login_pair_limit'] * 3, 900]];
        else $buckets = [[$ip, $s['mail_ip_limit'], 3600], [$identifier, $s['mail_identifier_limit'], 3600]];
        foreach ($buckets as $index => [$identity, $limit, $window])
        {
            $retry = self::bucket($operation . ':' . $index, $identity, (int) $limit, (int) $window, $increment);
            if ($retry) return new WP_Error('lsd_rate_limited', sprintf(
                /* translators: %d: seconds until another request is allowed. */
                __('Too many attempts. Please try again in %d seconds.', 'listdom'), $retry
            ), ['status' => 429, 'retry_after' => $retry, 'countdown' => self::countdown($retry)]);
        }
        if (!in_array($operation, ['login', 'register', 'totp', 'totp_account', 'reauth', 'native_start', 'native_passkey'], true) && $increment)
        {
            $name = LSD_Security_State::key('cooldown', $operation . '|' . $identifier);
            $old = get_option($name, []);
            if (is_array($old) && ($old['expires'] ?? 0) <= time() && $old)
            {
                global $wpdb;
                $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s", $name, maybe_serialize($old)));
                wp_cache_delete($name, 'options');
            }
            $expires = time() + (int) $s['resend_cooldown'];
            if (!add_option($name, ['expires' => $expires], '', false))
            {
                $retry = max(1, (int) ($old['expires'] ?? $expires) - time());
                return new WP_Error('lsd_rate_limited', __('A request was recently submitted. Please wait before trying again.', 'listdom'), ['status' => 429, 'retry_after' => $retry, 'countdown' => self::countdown($retry, true)]);
            }
        }
        return true;
    }

    private static function countdown(int $seconds, bool $cooldown = false): array
    {
        /* translators: {time} is a live hours, minutes, and seconds countdown. */
        $message = $cooldown ? __('A request was recently submitted. Please try again in {time}.', 'listdom') : __('Too many attempts. Please try again in {time}.', 'listdom');
        return LSD_Countdown::data($seconds, $message, __('Please try again.', 'listdom'));
    }

    public static function second_factor(int $user_id, string $challenge)
    {
        $account = self::check('totp_account', (string) $user_id);
        return is_wp_error($account) ? $account : self::check('totp', $challenge);
    }

    private static function bucket(string $purpose, string $identity, int $limit, int $window, bool $increment): int
    {
        global $wpdb;
        // WordPress users span sites. Keep the mandatory account ceiling in one shared table.
        $table = is_multisite() && strpos($purpose, 'totp_account:') === 0 ? $wpdb->base_prefix . 'options' : $wpdb->options;
        $expires = (intdiv(time(), $window) + 1) * $window;
        $name = '_lsd_sec_rate_' . $expires . '_' . hash_hmac('sha256', $purpose . '|' . $identity, wp_salt('auth'));
        if (!$increment)
        {
            $count = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$table} WHERE option_name = %s", $name));
            return (int) $count >= $limit ? max(1, $expires - time()) : 0;
        }
        // Database arbitration is required: read/modify/write transients lose parallel attempts.
        $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$table} (option_name, option_value, autoload) VALUES (%s, '0', 'no')", $name));
        $updated = $wpdb->query($wpdb->prepare("UPDATE {$table} SET option_value = CAST(option_value AS UNSIGNED) + 1 WHERE option_name = %s AND CAST(option_value AS UNSIGNED) < %d", $name, $limit));
        return $updated === 1 ? 0 : max(1, $expires - time());
    }
}
