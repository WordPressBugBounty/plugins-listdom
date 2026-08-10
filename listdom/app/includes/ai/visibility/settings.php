<?php

class LSD_AI_Visibility_Settings extends LSD_Base
{
    protected const INTRODUCED_VERSION = '5.7.0';

    /**
     * Return available public feed field keys.
     * @return array
     */
    protected static function field_keys(): array
    {
        return [
            'description',
            'excerpt',
            'address',
            'coordinates',
            'contact',
            'categories',
            'locations',
            'price',
            'images',
            'opening_hours',
            'faqs',
        ];
    }

    /**
     * Return available public feed fields.
     * @return array
     */
    public static function field_options(): array
    {
        // Field Labels
        return [
            'description' => esc_html__('Description', 'listdom'),
            'excerpt' => esc_html__('Excerpt', 'listdom'),
            'address' => esc_html__('Address', 'listdom'),
            'coordinates' => esc_html__('Coordinates', 'listdom'),
            'contact' => esc_html__('Contact Details', 'listdom'),
            'categories' => esc_html__('Categories', 'listdom'),
            'locations' => esc_html__('Locations', 'listdom'),
            'price' => esc_html__('Price', 'listdom'),
            'images' => esc_html__('Images', 'listdom'),
            'opening_hours' => esc_html__('Opening Hours', 'listdom'),
            'faqs' => esc_html__('FAQs', 'listdom'),
        ];
    }

    /**
     * Return default field visibility.
     * @return array
     */
    public static function field_defaults(): array
    {
        // Field Defaults
        $defaults = array_fill_keys(self::field_keys(), 1);

        // Contact Opt-In
        $defaults['contact'] = 0;

        return (array) apply_filters('lsd_ai_visibility_field_defaults', $defaults);
    }

    /**
     * Return AI visibility defaults.
     * @return array
     */
    public static function defaults(): array
    {
        // Setting Defaults
        $defaults = [
            'structured_data' => 1,
            'public_feed' => 0,
            'llms_txt' => 1,
            'robots_txt' => 1,
            'html_links' => 1,
            'fields' => self::field_defaults(),
        ];

        return (array) apply_filters('lsd_ai_visibility_settings_defaults', $defaults);
    }

    /**
     * Return normalized AI visibility settings.
     * @return array
     */
    public static function settings(): array
    {
        // Stored Settings
        $settings = LSD_Options::settings();
        $current = $settings['ai_visibility'] ?? [];
        $defaults = self::defaults();

        // Legacy Upgrade
        if (self::legacy_install()) $defaults = self::legacy_defaults($defaults);

        return self::parse_args(is_array($current) ? $current : [], $defaults);
    }

    /**
     * Check if the current site predates AI visibility.
     * @return bool
     */
    protected static function legacy_install(): bool
    {
        // Installed Version
        $version = get_option('lsd_version', '0');
        if (!is_scalar($version)) return true;

        return version_compare(trim((string) $version), self::INTRODUCED_VERSION, '<');
    }

    /**
     * Apply legacy defaults for upgraded installs.
     * @param array $defaults
     * @return array
     */
    protected static function legacy_defaults(array $defaults): array
    {
        $defaults['structured_data'] = 0;
        $defaults['llms_txt'] = 0;
        $defaults['robots_txt'] = 0;
        $defaults['html_links'] = 0;
        $defaults['include_verified_status'] = 0;
        $defaults['include_reviews'] = 0;
        $defaults['include_booking_summary'] = 0;

        return $defaults;
    }

    /**
     * Return normalized field settings.
     * @return array
     */
    public function fields(): array
    {
        // Field Settings
        $settings = self::settings();
        return self::parse_args($settings['fields'] ?? [], self::field_defaults());
    }

    /**
     * Check if a setting is registered.
     * @param string $key
     * @return bool
     */
    public static function setting_registered(string $key): bool
    {
        // Default Check
        return array_key_exists($key, self::defaults());
    }

    /**
     * Check if a setting is enabled.
     * @param string $key
     * @return bool
     */
    public function enabled(string $key): bool
    {
        // Registered Key
        if (!self::setting_registered($key)) return false;

        $settings = self::settings();

        return !empty($settings[$key]);
    }

    /**
     * Check if a public field is enabled.
     * @param string $field
     * @return bool
     */
    public function field_enabled(string $field): bool
    {
        // Field Status
        $fields = $this->fields();

        return !empty($fields[$field]);
    }

    /**
     * Check if structured data is enabled.
     * @return bool
     */
    public function structured_data_enabled(): bool
    {
        // Structured Data
        $settings = self::settings();

        return !empty($settings['structured_data']);
    }

    /**
     * Check if the public feed is enabled.
     * @return bool
     */
    public function public_feed_enabled(): bool
    {
        // Public Feed
        $settings = self::settings();

        return !empty($settings['public_feed']);
    }

    /**
     * Check if the public feed can be served.
     * @return bool
     */
    public function public_feed_available(): bool
    {
        // Feed Availability
        return $this->public_feed_enabled() && (bool) get_option('blog_public');
    }

    /**
     * Check if llms.txt is enabled.
     * @return bool
     */
    public function llms_txt_enabled(): bool
    {
        // LLMS Setting
        $settings = self::settings();

        return !empty($settings['llms_txt']);
    }

    /**
     * Check if robots.txt hints are enabled.
     * @return bool
     */
    public function robots_txt_enabled(): bool
    {
        // Robots Setting
        $settings = self::settings();

        return !empty($settings['robots_txt']);
    }

    /**
     * Check if HTML discovery links are enabled.
     * @return bool
     */
    public function html_links_enabled(): bool
    {
        // HTML Links
        $settings = self::settings();

        return !empty($settings['html_links']);
    }
}
