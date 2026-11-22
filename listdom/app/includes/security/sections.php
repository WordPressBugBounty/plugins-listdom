<?php

class LSD_Security_Sections
{
    public static function definitions(): array
    {
        return [
            'password' => [
                'heading' => __('Password Change', 'listdom'),
                'description' => __('Choose a new password for your account.', 'listdom'),
                'icon' => 'fas fa-key',
                'settings' => [],
            ],
            'email' => [
                'heading' => __('Email Change', 'listdom'),
                'description' => __('Request a new email address. Your current address stays active until you confirm the new one.', 'listdom'),
                'icon' => 'fas fa-envelope',
                'settings' => [],
            ],
            'totp' => [
                'heading' => __('Two-Factor Authentication', 'listdom'),
                'description' => __('Add an authenticator app to protect browser and Listdom API sign-in.', 'listdom') . "\n\n" . __('Two-factor authentication can be reset through your current email address.', 'listdom'),
                'icon' => 'fas fa-shield-alt',
                'settings' => ['roles' => [], 'challenge_limit' => 5, 'ip_limit' => 20],
            ],
            'sessions' => [
                'heading' => __('Active Sessions', 'listdom'),
                'description' => __('Review your active sign-ins and sign out other devices.', 'listdom'),
                'icon' => 'fas fa-desktop',
                'settings' => [],
            ],
            'privacy' => [
                'heading' => __('Privacy and Account Requests', 'listdom'),
                'description' => __('Request a copy of your personal data, personal data erasure, or account deletion.', 'listdom') . "\n\n" . __('Requests require email confirmation and administrator review. Requesting deletion does not automatically delete your account, transfer listings, cancel subscriptions, or erase payments and retained business records.', 'listdom'),
                'icon' => 'fas fa-user-lock',
                'settings' => ['export' => 1, 'erasure' => 1],
            ],
            'connections' => [
                'heading' => __('Connected Accounts', 'listdom'),
                'description' => __('Connect or disconnect identity providers. Confirm your identity before changing connections. Password recovery is always available.', 'listdom'),
                'icon' => 'fas fa-link',
                'settings' => [],
            ],
            'passkeys' => [
                'heading' => __('Passkeys', 'listdom'),
                'description' => __('Add and manage passkeys for this site. Keep another sign-in or recovery method available.', 'listdom'),
                'icon' => 'fas fa-fingerprint',
                'settings' => [],
            ],
        ];
    }

    public static function defaults(): array
    {
        $sections = [];
        foreach (self::definitions() as $id => $definition)
        {
            $sections[$id] = [
                'enabled' => $id === 'password' ? 1 : 0,
                'heading' => $definition['heading'],
                'description' => $definition['description'],
            ] + $definition['settings'];
        }
        return $sections;
    }

    public static function allowed_html(): array
    {
        return [
            'p' => [], 'br' => [], 'strong' => [], 'em' => [], 'b' => [], 'i' => [],
            'ul' => [], 'ol' => [], 'li' => [], 'a' => ['href' => true, 'title' => true],
        ];
    }

    public static function order($order): array
    {
        $ids = array_keys(self::definitions());
        $ordered = array_values(array_unique(array_intersect(array_filter((array) $order, 'is_string'), $ids)));
        return array_merge($ordered, array_values(array_diff($ids, $ordered)));
    }

    public static function sanitize(array $values, array $previous = []): array
    {
        $sections = [];
        foreach (self::defaults() as $id => $defaults)
        {
            $old = isset($previous[$id]) && is_array($previous[$id]) ? $previous[$id] : [];
            $new = isset($values[$id]) && is_array($values[$id]) ? $values[$id] : [];
            $section = array_replace($defaults, array_intersect_key($old, $defaults), array_intersect_key($new, $defaults));
            $section['enabled'] = $id === 'password' ? 1 : (int) !empty($section['enabled']);
            $section['heading'] = is_string($section['heading']) ? sanitize_text_field($section['heading']) : '';
            $section['description'] = is_string($section['description']) ? wp_kses($section['description'], self::allowed_html()) : '';
            if ($id === 'totp')
            {
                $section['roles'] = array_values(array_unique(array_intersect(array_filter((array) $section['roles'], 'is_string'), array_keys(wp_roles()->roles))));
                foreach (['challenge_limit', 'ip_limit'] as $key) $section[$key] = max(1, min($key === 'challenge_limit' ? 5 : 3600, (int) $section[$key]));
            }
            if ($id === 'privacy')
            {
                foreach (['export', 'erasure'] as $key) $section[$key] = (int) !empty($section[$key]);
            }
            $sections[$id] = $section;
        }
        return $sections;
    }

    public static function heading(string $id, array $section): string
    {
        return trim($section['heading']) !== '' ? $section['heading'] : self::definitions()[$id]['heading'];
    }
}
