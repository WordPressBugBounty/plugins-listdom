<?php

class LSD_Dashboard_Access
{
    public static function keys($value): array
    {
        $values = is_string($value) ? preg_split('/[\s,]+/', $value, -1, PREG_SPLIT_NO_EMPTY) : (array) $value;
        return array_values(array_unique(array_filter(array_map('sanitize_key', array_filter($values, 'is_string')))));
    }

    public static function rules(array $menu): array
    {
        return [
            'roles' => self::keys($menu['roles'] ?? []),
            'entitlement' => sanitize_key($menu['entitlement'] ?? ''),
        ];
    }

    public static function allows(array $menu): bool
    {
        $rules = self::rules($menu);
        if ($rules['roles']) LSD_Portal::private_response();
        $user = wp_get_current_user();
        if (!$user->exists()) return ($menu['login_status'] ?? 'required') === 'optional' && !$rules['roles'];
        if ($rules['roles'] && !array_intersect($rules['roles'], $user->roles)) return false;
        return true;
    }

    public static function locked(array $menu): ?string
    {
        $entitlement = sanitize_key($menu['entitlement'] ?? '');
        if ($entitlement === '' || LSD_Portal_Access::allows($entitlement)) return null;
        return LSD_Portal_Access::prompt($entitlement);
    }

    public static function role_options(): array
    {
        return wp_list_pluck(wp_roles()->roles, 'name');
    }

    public static function entitlement_options(array $selected = []): array
    {
        $keys = array_values(array_unique(array_merge(self::keys(apply_filters('lsd_portal_entitlement_keys', [])), self::keys($selected))));
        sort($keys);
        $options = ['' => __('No membership restriction', 'listdom')];
        foreach ($keys as $key) $options[$key] = $key;
        return $options;
    }
}
