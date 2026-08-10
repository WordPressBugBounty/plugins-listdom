<?php

class LSD_AI_Visibility extends LSD_Base
{
    protected static ?LSD_AI_Visibility $instance = null;
    protected LSD_AI_Visibility_Settings $settings;
    protected LSD_AI_Visibility_Payload $payload;
    protected LSD_AI_Visibility_Schema $schema;
    protected LSD_AI_Visibility_Public_API $public_api;
    protected LSD_AI_Visibility_Discovery $discovery;

    /**
     * Create AI visibility service dependencies.
     * @return void
     */
    public function __construct()
    {
        // Store the singleton and service instances.
        self::$instance = $this;
        $this->settings = new LSD_AI_Visibility_Settings();
        $this->payload = new LSD_AI_Visibility_Payload($this, $this->settings);
        $this->schema = new LSD_AI_Visibility_Schema($this, $this->settings, $this->payload);
        $this->public_api = new LSD_AI_Visibility_Public_API($this, $this->settings, $this->payload);
        $this->discovery = new LSD_AI_Visibility_Discovery($this, $this->settings, $this->public_api);
    }

    /**
     * Register AI visibility hooks.
     * @return void
     */
    public function init(): void
    {
        // Initialize each AI visibility service.
        $this->schema->init();
        $this->public_api->init();
        $this->discovery->init();
    }

    /**
     * Return the settings service.
     * @return LSD_AI_Visibility_Settings
     */
    public function settings_service(): LSD_AI_Visibility_Settings
    {
        // Return the cached settings service.
        return $this->settings;
    }

    /**
     * Return the payload service.
     * @return LSD_AI_Visibility_Payload
     */
    public function payload_service(): LSD_AI_Visibility_Payload
    {
        // Return the cached payload service.
        return $this->payload;
    }

    /**
     * Return the schema service.
     * @return LSD_AI_Visibility_Schema
     */
    public function schema_service(): LSD_AI_Visibility_Schema
    {
        // Return the cached schema service.
        return $this->schema;
    }

    /**
     * Return the public API service.
     * @return LSD_AI_Visibility_Public_API
     */
    public function public_api_service(): LSD_AI_Visibility_Public_API
    {
        // Return the cached public API service.
        return $this->public_api;
    }

    /**
     * Return the discovery service.
     * @return LSD_AI_Visibility_Discovery
     */
    public function discovery_service(): LSD_AI_Visibility_Discovery
    {
        // Return the cached discovery service.
        return $this->discovery;
    }

    /**
     * Resolve the archive shortcode for a term.
     * @param WP_Term $term
     * @return int
     */
    public function archive_shortcode_id(WP_Term $term): int
    {
        // Delegate archive shortcode lookup to taxonomies.
        return LSD_Taxonomies::archive_shortcode_id($term->taxonomy, (int) $term->term_id);
    }

    /**
     * Return the active AI visibility instance.
     * @return LSD_AI_Visibility|null
     */
    public static function instance(): ?LSD_AI_Visibility
    {
        // Return the stored singleton instance.
        return self::$instance;
    }

    /**
     * Return public feed field options.
     * @return array
     */
    public static function field_options(): array
    {
        // Delegate field options to settings.
        return LSD_AI_Visibility_Settings::field_options();
    }

    /**
     * Return public feed field defaults.
     * @return array
     */
    public static function field_defaults(): array
    {
        // Delegate field defaults to settings.
        return LSD_AI_Visibility_Settings::field_defaults();
    }

    /**
     * Return AI visibility setting defaults.
     * @return array
     */
    public static function defaults(): array
    {
        // Delegate setting defaults to settings.
        return LSD_AI_Visibility_Settings::defaults();
    }

    /**
     * Return normalized AI visibility settings.
     * @return array
     */
    public static function settings(): array
    {
        // Delegate normalized settings to settings.
        return LSD_AI_Visibility_Settings::settings();
    }

    /**
     * Return public feed URLs for a context.
     * @param array $context
     * @return array
     */
    public static function public_feed_urls(array $context = []): array
    {
        // Use the service when it has been initialized.
        $instance = self::instance();
        if ($instance instanceof self) return $instance->public_api_service()->feed_urls($context);

        $urls = [
            'listings' => rest_url('listdom/v1/public/listings'),
            'categories' => rest_url('listdom/v1/public/categories'),
            'locations' => rest_url('listdom/v1/public/locations'),
        ];

        return (array) apply_filters('lsd_ai_public_feed_urls', $urls, $context, $instance);
    }

    /**
     * Return labels for public feed URLs.
     * @return array
     */
    public static function public_feed_url_labels(): array
    {
        // Build the default feed URL labels.
        $labels = [
            'listing' => esc_html__('Listing', 'listdom'),
            'listings' => esc_html__('Listings', 'listdom'),
            'categories' => esc_html__('Categories', 'listdom'),
            'locations' => esc_html__('Locations', 'listdom'),
            'category_listings' => esc_html__('Category Listings', 'listdom'),
            'location_listings' => esc_html__('Location Listings', 'listdom'),
            'tag_listings' => esc_html__('Tag Listings', 'listdom'),
            'feature_listings' => esc_html__('Feature Listings', 'listdom'),
            'label_listings' => esc_html__('Label Listings', 'listdom'),
        ];

        return (array) apply_filters('lsd_ai_public_feed_url_labels', $labels, self::instance());
    }

    /**
     * Return one public feed URL label.
     * @param string $key
     * @return string
     */
    public static function public_feed_url_label(string $key): string
    {
        // Fall back to a readable version of the key.
        $labels = self::public_feed_url_labels();

        return $labels[$key] ?? ucwords(str_replace(['_', '-'], ' ', $key));
    }
}
