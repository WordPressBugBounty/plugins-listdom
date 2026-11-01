<?php

/** Configuration-only frontend readiness and server upload evidence. */
class LSD_Diagnostics_Submission
{
    public function inspect(LSD_Diagnostics_Report $report)
    {
        $settings = LSD_Options::settings();
        $helper = new LSD_Checklist_Helper();
        foreach (['submission_page' => 'listdom/dashboard', 'add_listing_page' => 'listdom/add-listing'] as $key => $purpose)
        {
            $id = (int) LSD_Options::post_id($key);
            $ref = 'submission:' . $key;
            $state = $helper->page_state($id);
            $report->fact($ref, 'page_id', $id);
            foreach ($state as $name => $value) $report->fact($ref, $name, $value);
            $page = $state['exists'] ? get_post($id) : null;
            $password_protected = $page instanceof WP_Post && $page->post_password !== '';
            $report->fact($ref, 'password_protected', $password_protected);
            $found = false;
            $usable = false;
            if ($state['exists'])
            {
                $scan = (new LSD_Diagnostics_References($report, $purpose))->submission($id);
                if (is_wp_error($scan)) return $scan;
                $found = $scan['found'];
                $usable = $scan['usable'];
            }
            $enabled = $key !== 'add_listing_page' || !array_key_exists('add_listing_page_status', $settings) || (bool) $settings['add_listing_page_status'];
            $report->fact($ref, 'enabled', $enabled);
            $report->fact($ref, 'saved_form_reference_found', $found);
            $renderer_available = shortcode_exists($key === 'add_listing_page' ? 'listdom-add-listing' : 'listdom-dashboard');
            $report->fact($ref, 'renderer_available', $renderer_available);
            $prerequisites = $state['published'] && !$password_protected && $enabled && $renderer_available;
            $ready = $prerequisites ? $usable : false;
            $report->fact($ref, 'configuration_ready', $ready);
            if ($ready === false) $report->finding('submission_page_not_ready', 'warning', __('A configured frontend page is missing, unpublished, password-protected, disabled, lacks a supported saved form reference or has no available form renderer.', 'listdom'), [$ref]);
        }
        $restrictions = LSD_Shortcodes_Dashboard::restriction_rules($settings);
        foreach ($restrictions as $key => $value)
        {
            $ref = 'restriction:' . $key;
            $valid = $value === '' || (is_numeric($value) && $value >= 0);
            $report->fact($ref, 'value', $valid ? ($value === '' ? '' : (int) $value) : null);
            $report->fact($ref, 'unit', $key === 'max_upload_size' ? 'KiB' : ($key === 'description_length' ? 'bytes' : 'count'));
            $report->fact($ref, 'unlimited', $valid ? ($value === '' || ($key === 'max_upload_size' && (float) $value === 0.0)) : null);
            if (!$valid) $report->finding('invalid_submission_restriction', 'warning', __('A stored submission restriction is invalid.', 'listdom'), [$ref]);
        }
        $report->fact('restriction:image_aspect_ratio', 'configured_value', (string) ($settings['submission_image_aspect_ratio'] ?? ''));
        $report->fact('submission:guest', 'enabled', LSD_Base::isPro() && !empty($settings['submission_guest']));
        $report->fact('submission:guest', 'registration_method', (string) ($settings['submission_guest_registration'] ?? 'approval'));
        $report->fact('submission:guest', 'wordpress_registration_enabled', (bool) get_option('users_can_register'));
        $catalog = new LSD_Settings_Catalog();
        foreach ($catalog->definitions() as $key => $field)
        {
            if (strpos($key, 'submission_module.') !== 0) continue;
            $ref = 'module:' . substr($key, strlen('submission_module.'));
            $value = $catalog->value($settings, $field);
            $report->fact($ref, 'configured_mode', $value);
            $report->fact($ref, 'available', (bool) $field['available']);
            $report->fact($ref, 'guest_enabled', $field['available'] && $value === 1);
            if ($value && !$field['available']) $report->finding('submission_module_unavailable', 'warning', __('An enabled submission module is unavailable with the current components or packages.', 'listdom'), [$ref]);
        }
        $report->fact('server:uploads', 'file_uploads_enabled', filter_var(ini_get('file_uploads'), FILTER_VALIDATE_BOOLEAN));
        foreach (['upload_max_filesize', 'post_max_size'] as $key)
        {
            $limit = self::ini_limit((string) ini_get($key), $key === 'post_max_size');
            foreach ($limit as $name => $value) $report->fact('server:' . $key, $name, $value);
        }
        $quota_unverified = function_exists('is_multisite') && is_multisite() && !get_site_option('upload_space_check_disabled');
        // Core's multisite filter scans uploads and writes dirsize_cache when
        // usage is cold. Reuse its configured policy with zero usage only as an
        // explicitly labelled upper bound; never scan or populate that cache.
        $wp_limit = (int) LSD_Diagnostics_Report::isolated(static function ()
        {
            if (function_exists('is_multisite') && is_multisite())
            {
                add_filter('upload_size_limit', 'upload_size_limit_filter');
                add_filter('pre_get_space_used', static function () { return 0; });
            }
            return wp_max_upload_size();
        });
        $report->fact('server:wordpress', 'configured_upper_bound_only', $quota_unverified);
        if ($quota_unverified) $report->limit('storage_quota_unverified', __('The WordPress limit is a configured upper bound. Remaining multisite storage was not scanned or verified.', 'listdom'));
        $report->limit('custom_upload_limits', __('Custom upload-size filters were not executed; first-party PHP and network configuration is reported.', 'listdom'));
        $report->fact('server:wordpress', 'upload_bytes', $wp_limit > 0 ? $wp_limit : null);
        $report->fact('server:wordpress', 'unit', 'bytes');
        $report->fact('server:wordpress', 'limit_state', $wp_limit > 0 ? 'bounded' : 'unknown');
        $report->limit('submission_configuration_only', __('No submission or upload was attempted. User capabilities, membership-specific limits, web-server limits and rendered forms require separate acceptance checks.', 'listdom'));
        if (has_filter('lsd_dashboard_restriction_rules') !== false)
            $report->limit('personalized_submission_limits', __('Request-specific restriction filters were not executed; core effective limits are shown.', 'listdom'));
        return true;
    }

    /** PHP quantities use powers of 1024; only post_max_size documents zero as unlimited. */
    public static function ini_limit(string $raw, bool $zero_unlimited): array
    {
        $raw = trim($raw);
        if (!preg_match('/^([0-9]+)\s*([KMG]?)$/i', $raw, $match)) return ['bytes' => null, 'unit' => 'bytes', 'limit_state' => 'unknown'];
        $powers = ['' => 0, 'K' => 1, 'M' => 2, 'G' => 3];
        $bytes = (int) $match[1] * pow(1024, $powers[strtoupper($match[2])]);
        return ['bytes' => $bytes > 0 ? (int) $bytes : null, 'unit' => 'bytes', 'limit_state' => $bytes > 0 ? 'bounded' : ($zero_unlimited ? 'unlimited' : 'unknown')];
    }
}
