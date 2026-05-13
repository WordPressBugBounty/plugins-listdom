<?php

class LSD_Plugin_Report extends LSD_Base
{
    const LAST_REPORT_OPTION = 'lsd_site_report_last_sent';

    public function init()
    {
        add_action('admin_init', [$this, 'maybe_send']);
    }

    public function maybe_send(): void
    {
        if (!is_admin() || wp_doing_ajax() || defined('DOING_CRON')) return;
        if (!class_exists(\Webilia\WP\SiteReport::class)) return;

        $settings = LSD_Options::settings();
        if (!isset($settings['help_improve_listdom']) || !$settings['help_improve_listdom']) return;

        $now = current_time('timestamp');
        $last_run = (int) get_option(self::LAST_REPORT_OPTION, 0);
        if ($last_run && ($now - $last_run) < WEEK_IN_SECONDS) return;

        $report = $this->build_report($settings);
        $result = \Webilia\WP\SiteReport::send(LSD_BASENAME, home_url('/'), $report);

        if ($result !== false) update_option(self::LAST_REPORT_OPTION, $now, false);
    }

    protected function build_report(array $settings): array
    {
        return [
            'site' => $this->collect_site(),
            'plugins' => $this->collect_plugins(),
            'payment' => $this->collect_payment(),
            'components' => $this->collect_components(),
            'ai' => $this->collect_ai(),
            'features' => $this->collect_features($settings),
            'listings' => $this->collect_listings(),
            'shortcodes' => $this->collect_shortcodes(),
        ];
    }

    protected function collect_site(): array
    {
        return [
            'wp' => get_bloginfo('version'),
            'php' => PHP_VERSION,
            'multisite' => is_multisite() ? 1 : 0,
            'db' => $this->get_db_server_info(),
            'theme' => $this->get_theme_info(),
            'memory_limit' => $this->get_memory_limit(),
            'max_execution_time' => (int) ini_get('max_execution_time'),
        ];
    }

    protected function collect_plugins(): array
    {
        if (!function_exists('get_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugins = [];
        foreach (get_plugins() as $basename => $data)
        {
            $plugins[$basename] = [
                'status' => (is_plugin_active($basename) || is_plugin_active_for_network($basename)) ? 'active' : 'inactive',
                'version' => $data['Version'] ?? '',
            ];
        }

        return $plugins;
    }

    protected function collect_payment(): array
    {
        return [
            'engine' => LSD_Payments_Engine::instance()->engine(),
        ];
    }

    protected function collect_components(): array
    {
        $components = [];
        foreach (LSD_Components::get() as $component => $enabled)
        {
            $components[$component] = [
                'status' => $enabled ? 1 : 0,
            ];
        }

        return $components;
    }

    protected function collect_ai(): array
    {
        return [
            'number_of_profiles' => $this->count_ai_profiles(),
        ];
    }

    protected function collect_features(array $settings): array
    {
        return [
            'rss' => !empty($settings['rss_status']) ? 1 : 0,
            'currency' => $settings['default_currency'] ?? '',
            'gdpr' => !empty($settings['privacy_consent']['enabled']) ? 1 : 0,
            'powered_by_message' => !empty($settings['powered_by_message']) ? 1 : 0,
            'advanced_slug' => LSD_Slug::is_advanced() ? 1 : 0,
            'grecaptcha' => LSD_Main::is_grecaptcha_enabled($settings) ? 1 : 0,
            'block_editor' => !empty($settings['blockeditor_status']) ? 1 : 0,
            'mailchimp' => !empty($settings['mailchimp_status']) ? 1 : 0,
            'custom_style' => $this->has_custom_styles() ? 1 : 0,
        ];
    }

    protected function collect_listings(): array
    {
        $counts = wp_count_posts(LSD_Base::PTYPE_LISTING, 'readable');
        $listings = is_object($counts) ? array_sum((array) $counts) : 0;

        return [
            'count' => [
                'listings' => $listings,
                'categories' => $this->count_terms(LSD_Base::TAX_CATEGORY),
                'labels' => $this->count_terms(LSD_Base::TAX_LABEL),
                'features' => $this->count_terms(LSD_Base::TAX_FEATURE),
                'tags' => $this->count_terms(LSD_Base::TAX_TAG),
                'locations' => $this->count_terms(LSD_Base::TAX_LOCATION),
                'custom_fields' => $this->count_attributes_by_type(),
            ]
        ];
    }

    protected function collect_shortcodes(): array
    {
        $shortcodes = [];
        $default_provider = LSD_Map_Provider::def();
        $posts = get_posts([
            'post_type' => LSD_Base::PTYPE_SHORTCODE,
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => -1,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        foreach ($posts as $post_id)
        {
            $meta = $this->get_post_meta($post_id);
            $display = isset($meta['lsd_display']) && is_array($meta['lsd_display']) ? $meta['lsd_display'] : [];
            $skin = (string) ($meta['lsd_skin'] ?? ($display['skin'] ?? ''));
            $skin_settings = ($skin && isset($display[$skin]) && is_array($display[$skin])) ? $display[$skin] : [];
            $provider = $this->get_shortcode_map_provider($display, $skin_settings, $default_provider);

            $shortcodes[] = [
                'skin' => $skin,
                'style' => isset($skin_settings['style']) ? (string) $skin_settings['style'] : '',
                'map' => [
                    'status' => $this->shortcode_has_map($skin, $skin_settings) ? 1 : 0,
                    'provider' => $provider,
                ],
            ];
        }

        return $shortcodes;
    }

    protected function shortcode_has_map(string $skin, array $settings): bool
    {
        if (!LSD_Components::map()) return false;
        if ($skin === 'singlemap') return true;

        if (isset($settings['map_position']))
        {
            $position = strtolower((string) $settings['map_position']);
            if ($position !== '' && $position !== 'none') return true;
        }

        if (!empty($settings['mapsearch'])) return true;
        return false;
    }

    protected function get_shortcode_map_provider(array $display, array $settings, string $default): string
    {
        $provider = $settings['map_provider'] ?? ($display['map_provider'] ?? $default);

        return LSD_Map_Provider::get($provider);
    }

    protected function count_ai_profiles(): int
    {
        $ai = LSD_Options::ai();
        $profiles = $ai['profiles'] ?? [];

        return is_array($profiles) ? count($profiles) : 0;
    }

    protected function count_terms(string $taxonomy): int
    {
        $count = wp_count_terms($taxonomy);
        return is_wp_error($count) ? 0 : (int) $count;
    }

    protected function get_db_server_info(): string
    {
        global $wpdb;

        if (method_exists($wpdb, 'db_server_info'))
        {
            $server_info = $wpdb->db_server_info();
            if (is_string($server_info) && trim($server_info) !== '') return $server_info;
        }

        $version = method_exists($wpdb, 'db_version') ? $wpdb->db_version() : '';
        return is_string($version) ? $version : '';
    }

    protected function get_theme_info(): array
    {
        $theme = wp_get_theme();

        return [
            'name' => (string) $theme->get('Name'),
            'version' => (string) $theme->get('Version'),
        ];
    }

    protected function get_memory_limit(): string
    {
        if (defined('WP_MEMORY_LIMIT')) return (string) WP_MEMORY_LIMIT;
        $limit = ini_get('memory_limit');

        return is_string($limit) ? $limit : '';
    }

    protected function has_custom_styles(): bool
    {
        $styles = LSD_Options::styles();
        $css = $styles['CSS'] ?? '';

        return trim((string) $css) !== '';
    }

    protected function count_attributes_by_type(): array
    {
        $attributes = LSD_Main::get_attributes_details();
        $counts = [];

        foreach ($attributes as $attribute)
        {
            $type = isset($attribute['field_type']) ? (string) $attribute['field_type'] : '';
            if ($type === '') continue;

            if (!isset($counts[$type])) $counts[$type] = 0;
            $counts[$type]++;
        }

        return $counts;
    }
}
