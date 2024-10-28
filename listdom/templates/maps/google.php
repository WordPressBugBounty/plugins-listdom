<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $listings */
/** @var array $args */

// Include Google Maps Assets to the page
$assets = new LSD_Assets();
$assets->googlemaps();

// Listdom
$main = new LSD_Main();

// Listdom Settings
$settings = LSD_Options::settings();

$latitude = $args['default_lt'] ?? $settings['map_backend_lt'];
$longitude = $args['default_ln'] ?? $settings['map_backend_ln'];
$style = $args['mapstyle'] ?? '';
$zoomlevel = $args['zoomlevel'] ?? 14;
$canvas_height = $args['canvas_height'] ?? null;
$atts = isset($args['atts']) && is_array($args['atts']) ? $args['atts'] : [];
$mapsearch = isset($args['mapsearch']) && $args['mapsearch'];
$autoGPS = isset($args['autoGPS']) && $args['autoGPS'];
$gps_zl = $settings['map_gps_zl'] ?? 13;
$gps_zl_current = $settings['map_gps_zl_current'] ?? 7;
$max_bounds = isset($args['max_bounds']) && is_array($args['max_bounds']) ? $args['max_bounds'] : [];
$gplaces = isset($args['gplaces']) && $args['gplaces'];
$direction = isset($args['direction']) && $args['direction'];

// Map Controls
$mapcontrols = $args['mapcontrols'] ?? [];
if(!is_array($mapcontrols) || !count($mapcontrols)) $mapcontrols = LSD_Options::defaults('mapcontrols');

// The Unique ID
$id = $args['id'] ?? mt_rand(100, 999);

if(isset($args['objects']) && is_array($args['objects']))
{
    $objects = $args['objects'];
}
else
{
    $archive = new LSD_PTypes_Listing_Archive();
    $objects = $archive->render_map_objects($listings, $args);
}

// No Objects to show or only one object with default location
if(!count($objects) || (count($objects) === 1 && $objects[0]['latitude'] === $latitude && $objects[0]['longitude'] === $longitude)) return;

// Add Google Maps JS codes to footer
$assets->footer('<script>
jQuery(document).ready(function()
{
    listdom_add_googlemaps_callbacks(function()
    {
        const lsdMap = jQuery("#lsd_map'.$id.'").listdomGoogleMaps(
        {
            latitude: "'.$latitude.'",
            longitude: "'.$longitude.'",
            id: '.$id.',
            ajax_url: "'.admin_url('admin-ajax.php', null).'",
            zoom: '.$zoomlevel.',
            objects: '.json_encode($objects, JSON_NUMERIC_CHECK).',
            args: "'.http_build_query(['args'=>$args], '', '&').'",
            richmarker: "'.$assets->lsd_asset_url('packages/richmarker/richmarker.min.js').'",
            infobox: "'.$assets->lsd_asset_url('js/infobox.min.js').'",
            clustering: '.(isset($args['clustering']) && $args['clustering'] ? '"'.$assets->lsd_asset_url('packages/clusterer/markerclusterer.min.js').'"' : 'false').',
            clustering_images: "'.$assets->lsd_asset_url(isset($args['clustering_images']) && trim($args['clustering_images']) ? $args['clustering_images'] : 'img/cluster1/m').'",
            styles: '.(trim($style) != '' ? $assets->get_googlemap_style($style) : "''").',
            mapcontrols: '.json_encode($mapcontrols, JSON_NUMERIC_CHECK).',
            fill_color: "'.$settings['map_shape_fill_color'].'",
            fill_opacity: '.$settings['map_shape_fill_opacity'].',
            stroke_color: "'.$settings['map_shape_stroke_color'].'",
            stroke_opacity: '.$settings['map_shape_stroke_opacity'].',
            stroke_weight: '.$settings['map_shape_stroke_weight'].',
            atts: "'.http_build_query(['atts'=>$atts], '', '&').'",
            mapsearch: '.($mapsearch ? 'true' : 'false').',
            autoGPS: '.($autoGPS ? 'true' : 'false').',
            geo_request: '.($main->is_geo_request() ? 'true' : 'false').',
            gps_zoom: {
                zl: '.$gps_zl.',
                current: '.$gps_zl_current.'
            },
            max_bounds: '.json_encode($max_bounds, JSON_NUMERIC_CHECK).',
            gplaces: '.($gplaces ? 'true' : 'false').',
            layers: '.json_encode(apply_filters('lsd_map_layers', [], LSD_MP_GOOGLE), JSON_NUMERIC_CHECK).',
            direction:
            {
                status: '.($direction ? 'true' : 'false').',
                destination:
                {
                    latitude: "'.($objects[0]['latitude'] ?? 0).'",
                    longitude: "'.($objects[0]['longitude'] ?? 0).'",
                },
                start_marker: "'.apply_filters('lsd_direction_start_icon', $assets->lsd_asset_url('img/markers/green.png')).'",
                end_marker: "'.apply_filters('lsd_direction_end_icon', $assets->lsd_asset_url('img/markers/red.png')).'"
            }
        });
        
        // Listdom Maps
        (new ListdomMaps(lsdMap.id)).set(lsdMap);
    });
});
</script>');
?>
<div class="lsd-listing-googlemap">
    <div id="lsd_map<?php echo esc_attr($id); ?>" class="<?php echo (isset($args['canvas_class']) ? esc_attr($args['canvas_class']) : 'lsd-map-canvas'); ?>" <?php if($canvas_height) echo 'style="height: '.esc_attr($canvas_height).'px;"'; ?>></div>

    <?php if($direction): ?>
    <div class="lsd-direction">
        <form method="post" action="#" id="lsd_direction_form<?php echo esc_attr($id); ?>">
			<div class="lsd-row">
				<div class="lsd-col-9 lsd-direction-address-wrapper">
					<input class="lsd-direction-address" type="text" placeholder="<?php esc_attr_e('Address from ...', 'listdom') ?>" title="<?php esc_attr_e('Address from ...', 'listdom') ?>" id="lsd_direction_address<?php echo esc_attr($id); ?>">
					<span class="lsd-direction-reset lsd-util-hide" id="lsd_direction_reset<?php echo esc_attr($id); ?>">X</span>
					<div class="lsd-direction-position-wrapper">
						<input type="hidden" id="lsd_direction_latitude<?php echo esc_attr($id); ?>">
						<input type="hidden" id="lsd_direction_longitude<?php echo esc_attr($id); ?>">
						<span class="lsd-direction-gps" id="lsd_direction_gps<?php echo esc_attr($id); ?>" title="<?php esc_attr_e('Your current location', 'listdom') ?>"><i class="lsd-icon fa fa-location-arrow"></i></span>
					</div>
				</div>
				<div class="lsd-col-3">
					<div class="lsd-direction-button-wrapper">
						<input type="submit" value="<?php esc_html_e('Get Directions', 'listdom'); ?>">
					</div>
				</div>
			</div>
        </form>
    </div>
    <?php endif; ?>

</div>
