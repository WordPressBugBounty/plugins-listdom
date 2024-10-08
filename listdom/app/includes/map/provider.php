<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Map Provider Class.
 *
 * @class LSD_Map_Provider
 * @version    1.0.0
 */
class LSD_Map_Provider extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function get($provider = null)
    {
        // Provider is Valid
        if ($provider && LSD_Map_Provider::valid($provider)) return $provider;

        // Return Default Provider
        $settings = LSD_Options::settings();
        return $settings['map_provider'] ?? LSD_MP_GOOGLE;
    }

    public static function get_providers(): array
    {
        // Map Providers of Lite Version
        if (LSD_Base::isLite())
        {
            return [
                LSD_MP_GOOGLE => esc_html__('Google Maps', 'listdom'),
            ];
        }
        // Map Providers of Full Version
        else
        {
            return [
                LSD_MP_LEAFLET => esc_html__('Leaflet (OpenStreetMap)', 'listdom'),
                LSD_MP_GOOGLE => esc_html__('Google Maps', 'listdom'),
            ];
        }
    }

    public function form($shape)
    {
        // Listdom Settings
        $settings = LSD_Options::settings();

        // Map Provider
        if (LSD_Map_Provider::def() === LSD_MP_GOOGLE) $file = 'metaboxes/listing/map/googlemap.php';
        else $file = 'metaboxes/listing/map/leaflet.php';

        // Generate output
        $output = $this->include_html_file($file, [
            'return_output' => true,
            'parameters' => [
                'settings' => $settings,
                'shape' => $shape,
            ],
        ]);

        // Add to Footer
        LSD_Assets::footer($output);
    }

    public static function valid($provider): bool
    {
        $providers = LSD_Map_Provider::get_providers();
        return isset($providers[$provider]);
    }

    public static function def()
    {
        return LSD_Map_Provider::get();
    }
}
