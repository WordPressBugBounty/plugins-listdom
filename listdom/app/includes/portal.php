<?php

class LSD_Portal
{
    private const AUTH_KEYS = ['login' => 'login_page', 'register' => 'register_page', 'forgot' => 'forgot_password_page'];
    private const AUTH_TABS = ['login' => 'login', 'register' => 'register', 'forgot' => 'lostpassword'];

    public static function assignments(): array
    {
        $auth = LSD_Options::auth();
        return [
            'login' => (int) ($auth['auth']['login_page'] ?? 0),
            'register' => (int) ($auth['auth']['register_page'] ?? 0),
            'forgot' => (int) ($auth['auth']['forgot_password_page'] ?? 0),
            'account' => LSD_Options::post_id('submission_page'),
            'profile' => (int) ($auth['profile']['page'] ?? 0),
        ];
    }

    private static function valid_page(int $id): bool
    {
        if (!$id) return false;
        $page = get_post($id);
        return $page instanceof WP_Post && $page->post_type === 'page' && $page->post_status === 'publish' && $page->post_password === '';
    }

    public static function page_id(string $key): int
    {
        $id = self::assignments()[$key] ?? 0;
        return self::valid_page($id) ? $id : 0;
    }

    public static function url(string $key, string $redirect = ''): string
    {
        $id = self::page_id($key);
        if (!$id) return '';
        $url = (string) get_permalink($id);
        if (isset(self::AUTH_TABS[$key]))
        {
            $url = add_query_arg('tab', self::AUTH_TABS[$key], $url);
            $redirect = wp_validate_redirect(wp_sanitize_redirect($redirect), '');
            if ($redirect !== '') $url = add_query_arg('redirect_to', rawurlencode($redirect), $url);
        }
        return $url;
    }

    public static function is_account_page(int $id = 0): bool
    {
        return self::is_page('account', $id);
    }

    public static function is_page(string $key, int $id = 0): bool
    {
        $id = $id ?: get_queried_object_id();
        return $id && $id === self::page_id($key);
    }

    public static function hide_listings(): bool
    {
        $statuses = LSD_Options::settings()['dashboard_menu_builtin_status'] ?? [];
        return isset($statuses['manage'], $statuses['form']) && (int) $statuses['manage'] === 0 && (int) $statuses['form'] === 0;
    }

    public static function public_profiles(): bool
    {
        return (LSD_Options::auth()['profile']['visibility'] ?? 'public') !== 'private';
    }

    public static function directory_roles(): array
    {
        $roles = LSD_Options::auth()['profile']['directory_roles'] ?? ['listdom_author', 'listdom_publisher', 'administrator'];
        return array_values(array_intersect(LSD_Dashboard_Access::keys($roles), array_keys(wp_roles()->roles)));
    }

    public static function current_page_key(): string
    {
        if (!is_page()) return '';
        $id = get_queried_object_id();
        $key = array_search($id, self::assignments(), true);
        return $key !== false && self::valid_page($id) ? $key : '';
    }

    public static function private_response(): void
    {
        if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
        if (defined('REST_REQUEST') && REST_REQUEST) add_filter('rest_send_nocache_headers', '__return_true');
        if (!headers_sent())
        {
            nocache_headers();
            header('Cache-Control: private, no-store, max-age=0');
        }
    }

    public static function definitions(): array
    {
        return [
            'login' => [__('Login', 'listdom'), 'listdom-login'],
            'register' => [__('Register', 'listdom'), 'listdom-register'],
            'forgot' => [__('Forgot / Reset Password', 'listdom'), 'listdom-forgot-password'],
            'account' => [__('My Account', 'listdom'), 'listdom-dashboard'],
            'profile' => [__('Profile', 'listdom'), 'listdom-profile'],
        ];
    }

    public static function admin_tabs(): array
    {
        return [
            'account-setup' => [
                'label' => __('Account Setup', 'listdom'),
                'sections' => [
                    'pages' => [__('Pages', 'listdom'), 'auth-setup-pages.php'],
                    'navigation' => [__('Account Links & Logout', 'listdom'), 'auth-setup-navigation.php'],
                ],
            ],
            'login-registration' => [
                'label' => __('Login & Registration', 'listdom'),
                'sections' => [
                    'full-auth' => [__('Full Auth', 'listdom'), 'auth-full.php'],
                    'login' => [__('Login', 'listdom'), 'auth-login.php'],
                    'registration' => [__('Registration', 'listdom'), 'auth-registration.php'],
                    'password-recovery' => [__('Password Recovery', 'listdom'), 'auth-recovery.php'],
                    'social-login' => [__('Social Login', 'listdom'), 'auth-social-login.php'],
                    'passwordless' => [__('Passwordless', 'listdom'), 'auth-passwordless.php'],
                ],
            ],
            'security' => [
                'label' => __('Account Security', 'listdom'),
                'sections' => [
                    'account-sections' => [__('Account Sections', 'listdom'), 'auth-security.php'],
                    'password-policy' => [__('Password Policy', 'listdom'), 'auth-password-policy.php'],
                    'abuse-protection' => [__('Abuse Protection', 'listdom'), 'auth-abuse-protection.php'],
                    'admin-access' => [__('Admin Access', 'listdom'), 'auth-admin-access.php'],
                ],
            ],
            'profiles-directory' => [
                'label' => __('Profiles & Directory', 'listdom'),
                'sections' => [
                    'profiles' => [__('Profiles', 'listdom'), 'auth-profiles.php'],
                    'fields' => [__('Fields', 'listdom'), 'portal-fields.php'],
                    'directory' => [__('Directory', 'listdom'), 'auth-directory.php'],
                ],
            ],
        ];
    }

    public static function admin_route(string $subtab, string $section = ''): array
    {
        $legacy = [
            'authentication' => ['login-registration', 'full-auth'],
            'login' => ['login-registration', 'login'],
            'register' => ['login-registration', 'registration'],
            'forgot-password' => ['login-registration', 'password-recovery'],
            'social-login' => ['login-registration', 'social-login'],
            'passwordless' => ['login-registration', 'passwordless'],
            'logged-in-users' => ['account-setup', 'navigation'],
            'user-directory' => ['profiles-directory', 'profiles'],
            'profile-user-directory' => ['profiles-directory', 'profiles'],
            'public-profile' => ['profiles-directory', 'profiles'],
            'profile' => ['profiles-directory', 'profiles'],
            'fields' => ['profiles-directory', 'fields'],
            'edit-profile' => ['profiles-directory', 'fields'],
            'options' => ['profiles-directory', 'profiles'],
            'block-admin-access' => ['security', 'admin-access'],
        ];
        if (isset($legacy[$subtab])) [$subtab, $section] = $legacy[$subtab];

        $tabs = self::admin_tabs();
        if (!isset($tabs[$subtab])) $subtab = 'account-setup';
        if (!isset($tabs[$subtab]['sections'][$section])) $section = array_key_first($tabs[$subtab]['sections']);
        return [$subtab, $section];
    }

    public static function admin_url(string $subtab = 'account-setup', string $section = ''): string
    {
        [$subtab, $section] = self::admin_route($subtab, $section);
        return add_query_arg(['tab' => 'auth', 'subtab' => $subtab, 'section' => $section], admin_url('admin.php?page=listdom-settings'));
    }

    public static function setup_plan(): array
    {
        $plan = [];
        $assigned = self::assignments();
        $shared = 0;
        foreach (array_keys(self::AUTH_KEYS) as $key)
        {
            $id = $assigned[$key];
            if (self::valid_page($id) && has_shortcode((string) get_post_field('post_content', $id), 'listdom-auth'))
            {
                $shared = $id;
                break;
            }
        }

        foreach (self::definitions() as $key => [$label, $shortcode])
        {
            $id = $assigned[$key];
            $status = $id ? (self::valid_page($id) ? 'assigned' : 'invalid') : 'create';
            $generated = isset(self::AUTH_KEYS[$key]) ? 'authentication' : $key;
            $content = $generated === 'authentication' ? 'listdom-auth' : $shortcode;
            if (!$id)
            {
                $id = isset(self::AUTH_KEYS[$key]) ? $shared : 0;
                if (!$id) $id = self::generated_page($generated, $content);
                if ($id) $status = 'reuse';
            }
            $plan[$key] = compact('id', 'status', 'generated', 'content');
        }
        return $plan;
    }

    private static function generated_page(string $key, string $shortcode): int
    {
        $ids = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'has_password' => false,
            'fields' => 'ids',
            'numberposts' => -1,
            'meta_key' => '_lsd_portal_page',
            'meta_value' => $key,
        ]);
        foreach ($ids as $id)
        {
            if (has_shortcode((string) get_post_field('post_content', $id), $shortcode)) return (int) $id;
        }
        return 0;
    }

    public static function save(): void
    {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Access denied.', 'listdom'), '', ['response' => 403]);
        check_admin_referer('lsd_account_portal');

        $operation = isset($_POST['portal_operation']) && is_string($_POST['portal_operation']) ? sanitize_key(wp_unslash($_POST['portal_operation'])) : 'pages';
        if ($operation === 'apply') $status = self::apply_preset();
        elseif ($operation === 'restore') $status = self::restore_preset();
        elseif ($operation === 'pages') $status = self::setup_pages() ? 'saved' : 'incomplete';
        else $status = 'incomplete';

        wp_safe_redirect(add_query_arg('portal_status', $status, self::admin_url()));
        exit;
    }

    public static function setup_pages(): bool
    {
        if (!current_user_can('manage_options')) return false;

        $created = [];
        $incomplete = false;
        foreach (self::setup_plan() as $key => $item)
        {
            if ($item['status'] === 'invalid') $incomplete = true;
            if (!in_array($item['status'], ['reuse', 'create'], true)) continue;
            $id = $item['id'];
            $group = $item['generated'];
            if (!$id)
            {
                if (!array_key_exists($group, $created))
                {
                    $title = $group === 'authentication' ? __('Authentication', 'listdom') : self::definitions()[$key][0];
                    $created[$group] = wp_insert_post([
                        'post_type' => 'page',
                        'post_status' => 'publish',
                        'post_title' => $title,
                        'post_content' => '[' . $item['content'] . ']',
                        'meta_input' => ['_lsd_portal_page' => $group],
                    ]);
                }
                $id = $created[$group];
            }
            if ($id) self::assign_if_empty($key, $id);
            else $incomplete = true;
        }

        return !$incomplete;
    }

    public static function apply_preset(): string
    {
        if (!current_user_can('manage_options') || get_option('lsd_account_portal_preset')) return 'incomplete';

        // Store only the settings this preset changes. Pages and their content are never deleted on restore.
        $changes = [
            ['lsd_settings', ['dashboard_menu_builtin_status', 'manage'], 0],
            ['lsd_settings', ['dashboard_menu_builtin_status', 'form'], 0],
            ['lsd_settings', ['dashboard_menu_builtin_status', 'account-overview'], 1],
            ['lsd_settings', ['dashboard_menu_builtin_status', 'profile'], 1],
            ['lsd_settings', ['dashboard_menu_builtin_status', 'logout'], 1],
            ['lsd_settings', ['dashboard_menu_builtin', 'profile', 'show_security'], 1],
            ['lsd_auth', ['profile', 'visibility'], 'private'],
        ];
        $settings = (array) get_option('lsd_settings', []);
        $order = array_values(array_filter((array) ($settings['dashboard_menus'] ?? []), static function ($id): bool { return $id !== 'lsd_dashboard_menus_account_overview'; }));
        array_unshift($order, 'lsd_dashboard_menus_account_overview');
        $changes[] = ['lsd_settings', ['dashboard_menus'], $order];
        foreach (self::AUTH_KEYS as $field) $changes[] = ['lsd_auth', ['auth', $field]];
        $changes[] = ['lsd_auth', ['profile', 'page']];
        $changes[] = ['lsd_settings', ['submission_page']];

        $snapshot = [];
        foreach ($changes as $change)
        {
            [$option, $path] = $change;
            $snapshot[] = ['option' => $option, 'path' => $path, 'before' => self::preset_value($option, $path)];
        }
        // Persist the original values before making any configuration changes.
        update_option('lsd_account_portal_preset', $snapshot, false);
        $complete = self::setup_pages();
        foreach ($changes as $change)
        {
            if (array_key_exists(2, $change)) self::preset_write($change[0], $change[1], ['present' => true, 'value' => $change[2]]);
        }
        foreach ($snapshot as &$item) $item['after'] = self::preset_value($item['option'], $item['path']);
        unset($item);
        update_option('lsd_account_portal_preset', $snapshot, false);
        LSD_Options::clear_settings_cache();
        return $complete ? 'preset-applied' : 'incomplete';
    }

    public static function restore_preset(): string
    {
        if (!current_user_can('manage_options')) return 'incomplete';
        $snapshot = get_option('lsd_account_portal_preset', []);
        if (!$snapshot || !is_array($snapshot)) return 'incomplete';
        $changed = false;
        foreach ($snapshot as $item)
        {
            // Retain settings the administrator changed after applying the preset.
            $current = self::preset_value($item['option'], $item['path']);
            $after = $item['after'] ?? null;
            $same = $current === $after;
            // Form saves represent page IDs and switches as strings.
            if (!$same && !empty($current['present']) && !empty($after['present']) && is_scalar($current['value']) && is_scalar($after['value']))
                $same = (string) $current['value'] === (string) $after['value'];
            if (!$same)
            {
                $changed = true;
                continue;
            }
            self::preset_write($item['option'], $item['path'], $item['before']);
        }
        delete_option('lsd_account_portal_preset');
        delete_option('rewrite_rules');
        LSD_Options::clear_settings_cache();
        return $changed ? 'preset-restored-changed' : 'preset-restored';
    }

    private static function preset_value(string $option, array $path): array
    {
        $value = get_option($option, []);
        foreach ($path as $key)
        {
            if (!is_array($value) || !array_key_exists($key, $value)) return ['present' => false, 'value' => null];
            $value = $value[$key];
        }
        return ['present' => true, 'value' => $value];
    }

    private static function preset_write(string $option, array $path, array $entry): void
    {
        $values = (array) get_option($option, []);
        $leaf = array_pop($path);
        $branch = &$values;
        foreach ($path as $key)
        {
            if (!isset($branch[$key]) || !is_array($branch[$key])) $branch[$key] = [];
            $branch = &$branch[$key];
        }
        if ($entry['present']) $branch[$leaf] = $entry['value'];
        else unset($branch[$leaf]);
        update_option($option, $values);
    }

    private static function assign_if_empty(string $key, int $id): void
    {
        if ($key === 'account')
        {
            $settings = (array) get_option('lsd_settings', []);
            if (empty($settings['submission_page'])) LSD_Options::merge('lsd_settings', ['submission_page' => $id]);
            return;
        }

        $auth = (array) get_option('lsd_auth', []);
        $section = $key === 'profile' ? 'profile' : 'auth';
        $field = $key === 'profile' ? 'page' : self::AUTH_KEYS[$key];
        if (!empty($auth[$section][$field])) return;
        $auth[$section][$field] = $id;
        update_option('lsd_auth', $auth);
        if ($key === 'profile') delete_option('rewrite_rules');
    }
}
