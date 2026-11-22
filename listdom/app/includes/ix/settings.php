<?php

class LSD_IX_Settings extends LSD_Base
{
    private const MERGED_OPTIONS = [
        'settings' => 'lsd_settings',
        'customizer' => 'lsd_customizer',
        'auth' => 'lsd_auth',
        'payments' => 'lsd_payments',
        'styles' => 'lsd_styles',
        'details_page' => 'lsd_details_page',
        'socials' => 'lsd_socials',
        'addons' => 'lsd_addons',
        'ai' => 'lsd_ai',
    ];

    private const USER_FIELD_OPTIONS = [
        'user_field_layout' => 'lsd_user_field_layout',
        'portal_user_fields' => 'lsd_portal_user_fields',
    ];

    public function init()
    {
        add_action('wp_ajax_lsd_settings_export', [$this, 'export_request']);
        add_action('wp_ajax_lsd_settings_import', [$this, 'import_request']);
    }

    public function export_request()
    {
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'ADMIN_ONLY']);

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'lsd_settings_export'))
            $this->response(['success' => 0, 'code' => 'INVALID_REQUEST']);

        $JSON = wp_json_encode(self::export());

        header('Content-Type: application/json');
        header('Content-Description: Listdom Settings');
        header('Content-Disposition: attachment; filename="lsd_settings_' . lsd_date('Y-m-d-H-i') . '.json"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');

        echo $JSON;
        exit;
    }

    public static function export(): array
    {
        $content = [
            'settings' => LSD_Options::settings(),
            'customizer' => LSD_Options::customizer(),
            'auth' => LSD_Options::auth(),
            'payments' => LSD_Options::payments(),
            'styles' => LSD_Options::styles(),
            'details_page' => LSD_Options::details_page(true),
            'socials' => (array) get_option('lsd_socials', []) ?: LSD_Options::socials(),
            'details_page_pattern' => LSD_Options::details_page_pattern(),
            'addons' => LSD_Options::addons(),
        ];

        // Read saved AI profiles without initializing options on export.
        foreach (['ai' => self::MERGED_OPTIONS['ai']] + self::USER_FIELD_OPTIONS as $section => $option)
        {
            $value = get_option($option);
            if (is_array($value)) $content[$section] = $value;
        }

        // Keep credentials out of the export filter input and scrub sections added by extensions.
        return self::without_credentials((array) apply_filters('lsd_ix_settings_export', self::without_credentials($content)));
    }

    private static function without_credentials(array $values, string $path = ''): array
    {
        foreach ($values as $key => $value)
        {
            if (self::private_field((string) $key, $path)) unset($values[$key]);
            else if (is_array($value)) $values[$key] = self::without_credentials($value, $path . '.' . $key);
            else if ($value instanceof stdClass) $values[$key] = (object) self::without_credentials((array) $value, $path . '.' . $key);
        }

        return $values;
    }

    private static function private_field(string $key, string $path): bool
    {
        $key = strtolower($key);

        // The REST API option contains only token records. Provider secrets are stored separately.
        if ($path === '' && in_array($key, ['api', 'lsd_api'], true)) return true;
        if ($path === '.auth.security.sections' && $key === 'password') return false;
        if (strpos($path, '.payments.gateways.') === 0 && $key === 'client_id') return true;

        // Network connections are site-specific. Tile scripts can embed provider keys.
        if ($path === '.addons.network' && in_array($key, ['clients', 'server_url'], true)) return true;
        if ($path === '.addons.am' && $key === 'leaflet_tile_server') return true;
        if (in_array($key, ['grecaptcha_sitekey', 'grecaptcha_secretkey', 'twilio_sid', 'twilio_sender'], true)) return true;

        return (bool) preg_match('/(?:^|[_-])(?:api[_-]?key|private[_-]?key|signing[_-]?key|publishable[_-]?key|secret[_-]?key|secrets?|token|credentials?|password|license[_-]?key|purchase[_-]?code)$/D', $key)
            || $key === 'tokens';
    }

    private static function preserve_credentials(array $incoming, array $current, string $path): array
    {
        if ($path === '.ai.profiles') $current = array_column($current, null, 'id');

        foreach ($incoming as $key => $value)
        {
            if (!is_array($value)) continue;
            $match = $key;

            if ($path === '.ai.profiles')
            {
                // Profile indexes can move and a different model may use another provider.
                $match = $value['id'] ?? '';
                if (!is_string($match) || $match === '' || ($value['model'] ?? '') !== ($current[$match]['model'] ?? '')) continue;
            }

            $previous = $current[$match] ?? null;
            if (!is_array($previous)) continue;
            $incoming[$key] = self::preserve_credentials($value, $previous, $path . '.' . $key);
        }

        // Preserve only omitted credentials so ordinary values and lists retain import semantics.
        foreach (array_diff_key($current, $incoming) as $key => $value)
        {
            if (self::private_field((string) $key, $path)) $incoming[$key] = $value;
        }

        return $incoming;
    }

    public function import_request()
    {
        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'ADMIN_ONLY']);

        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'lsd_settings_import'))
            $this->response(['success' => 0, 'code' => 'INVALID_REQUEST']);

        $file = $_FILES['import'] ?? [];
        if (!$file) $this->response(['success' => 0, 'message' => esc_html__('Please upload a file to import!', 'listdom')]);

        // Read the PHP upload directly so legacy backups stay out of public uploads.
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']))
        {
            $this->response([
                'success' => 0,
                'code' => 'UPLOAD_ERROR',
                'message' => esc_html__('An error occurred during uploading the file!', 'listdom'),
            ]);
        }

        if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'json')
            $this->response(['success' => 0, 'message' => esc_html__('Only JSON files are allowed!', 'listdom')]);

        if ($file['size'] > 512000)
        {
            $this->response([
                'success' => 0,
                'message' => esc_html__('The uploaded json file exceeds the maximum allowed size of 500 KB.', 'listdom'),
            ]);
        }

        if (!self::import($file['tmp_name'], false)) $this->response([
            'success' => 0,
            'code' => 'INVALID_SETTINGS',
            'message' => esc_html__('Unable to import settings. Check the JSON file and its authentication policy.', 'listdom'),
        ]);

        $this->response(['success' => 1, 'code' => 'SUCCESS']);
    }

    public static function import(string $file, bool $delete = true): bool
    {
        $JSON = LSD_File::read($file);
        if ($delete) LSD_File::delete($file);

        $options = is_string($JSON) ? json_decode($JSON, true) : null;
        if (!is_array($options)) return false;

        $sections = array_filter(array_intersect_key($options, self::MERGED_OPTIONS + self::USER_FIELD_OPTIONS), 'is_array');
        $pattern = $options['details_page_pattern'] ?? '';
        $has_pattern = is_string($pattern) && trim($pattern) !== '';
        if (!$sections && !$has_pattern) return false;

        // Validate the effective auth policy before writing any section.
        if (isset($sections['auth']))
        {
            $auth = array_replace(LSD_Options::auth(), $sections['auth']);
            if (is_wp_error(LSD_Security::validate_settings($auth))) return false;
            $sections['auth'] = $auth;
        }

        foreach (self::MERGED_OPTIONS as $section => $option)
        {
            if (!isset($sections[$section])) continue;
            $current = (array) get_option($option, []);
            $values = self::preserve_credentials($sections[$section], $current, '.' . $section);

            // The AI reader initializes options without a profiles entry.
            if ($section === 'ai') $values['profiles'] = $values['profiles'] ?? $current['profiles'] ?? [];

            LSD_Options::merge($option, $values);
        }

        // The layout and definitions use stable field IDs and preserve the imported row order.
        foreach (self::USER_FIELD_OPTIONS as $section => $option)
        {
            if (isset($sections[$section])) update_option($option, $sections[$section]);
        }

        if ($has_pattern) update_option('lsd_details_page_pattern', $pattern);

        do_action('lsd_ix_settings_imported', $options);
        LSD_Settings_Service::after_save(true);

        return true;
    }

    public static function get_export_url(): string
    {
        return admin_url('admin-ajax.php').'?action=lsd_settings_export&_wpnonce='.wp_create_nonce('lsd_settings_export');
    }
}
