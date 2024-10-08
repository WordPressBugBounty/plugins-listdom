<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Simplemap Shortcode Class.
 *
 * @class LSD_Shortcodes_Simplemap
 * @version    1.0.0
 */
class LSD_Shortcodes_Simplemap extends LSD_Shortcodes
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_shortcode('listdom_simplemap', [$this, 'output']);
    }

    public function output($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom_simplemap');
        if (trim($pre)) return $pre;

        $lat = isset($atts['lat']) ? (float) $atts['lat'] : 0;
        $lng = isset($atts['lng']) ? (float) $atts['lng'] : 0;

        $address = $atts['address'] ?? '';
        $title = $atts['title'] ?? $address;

        // Convert the Address to geo point
        if ((!$lat || !$lng) && trim($address))
        {
            $main = new LSD_Main();
            $geopoint = $main->geopoint($address);

            if (isset($geopoint[0])) $lat = $geopoint[0];
            if (isset($geopoint[1])) $lng = $geopoint[1];
        }

        // Return an error if the marker location couldn't find!
        if (!$lat || !$lng) return $this->alert(esc_html__("The marker location couldn't find. Please specify address or lat/lng parameters.", 'listdom'));

        $assets = new LSD_Assets();

        $zoomlevel = $atts['zoomlevel'] ?? 14;
        $map_provider = isset($atts['provider']) ? LSD_Map_Provider::get($atts['provider']) : LSD_Map_Provider::def();
        $style = $atts['style'] ?? '';
        $id = LSD_id::get($atts['id'] ?? mt_rand(100, 999));
        $icon = $atts['icon'] ?? $assets->lsd_asset_url('img/markers/blue.png');

        return lsd_map([], [
            'provider' => $map_provider,
            'clustering' => false,
            'mapstyle' => $style,
            'zoomlevel' => $zoomlevel,
            'id' => $id,
            'onclick' => 'infowindow',
            'mapcontrols' => [
                'zoom' => 'RIGHT_BOTTOM',
                'maptype' => 'TOP_LEFT',
                'streetview' => 'RIGHT_BOTTOM',
                'draw' => '0',
                'gps' => '0',
                'scale' => '0',
                'fullscreen' => '1',
            ],
            'objects' => [
                [
                    'type' => 'marker',
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'marker' => '<img src="' . esc_url($icon) . '">',
                    'infowindow' => '<div class="lsd-infowindow-wrapper"><div class="lsd-infowindow-container">' . esc_html($title) . '</div></div>',
                    'onclick' => 'infowindow',
                    'lsd' => [
                        'x_offset' => $atts['x_offset'] ?? 0,
                        'y_offset' => $atts['y_offset'] ?? -40,
                    ],
                ],
            ],
        ]);
    }
}
