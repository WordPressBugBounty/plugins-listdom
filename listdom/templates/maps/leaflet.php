<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $listings */
/** @var array $args */

// Include Leaflet Assets to the page
$assets = new LSD_Assets();
$assets->leaflet();

// Listdom Settings
$settings = LSD_Options::settings();

$latitude = $args['default_lt'] ?? $settings['map_backend_lt'];
$longitude = $args['default_ln'] ?? $settings['map_backend_ln'];
$style = $args['mapstyle'] ?? '';
$zoomlevel = $args['zoomlevel'] ?? 14;
$gps_zl = $settings['map_gps_zl'] ?? 13;
$gps_zl_current = $settings['map_gps_zl_current'] ?? 7;
$canvas_height = $args['canvas_height'] ?? null;
$atts = isset($args['atts']) && is_array($args['atts']) ? $args['atts'] : [];
$mapsearch = isset($args['mapsearch']) && $args['mapsearch'];
$gplaces = isset($args['gplaces']) && $args['gplaces'];
$max_bounds = isset($args['max_bounds']) && is_array($args['max_bounds']) ? $args['max_bounds'] : [];
$access_token = LSD_Options::mapbox_token();

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

if(count($objects) === 1 && $objects[0]["latitude"] === $latitude && $objects[0]["longitude"] === $longitude) return;
    
// Add Leaflet JS codes to footer
$assets->footer('<script>
jQuery(document).ready(function()
{
    const lsdMap = jQuery("#lsd_map'.$id.'").listdomLeaflet(
    {
        latitude: "'.$latitude.'",
        longitude: "'.$longitude.'",
        id: '.$id.',
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        zoom: '.$zoomlevel.',
        objects: '.json_encode($objects, JSON_NUMERIC_CHECK).',
        args: "'.http_build_query(['args'=>$args], '', '&').'",
        richmarker: "",
        infobox: "",
        clustering: '.(isset($args['clustering']) && $args['clustering'] ? 'true' : 'false').',
        clustering_images: "'.$assets->lsd_asset_url(isset($args['clustering_images']) && trim($args['clustering_images']) ? $args['clustering_images'] : 'img/cluster1/m').'",
        styles: "",
        mapcontrols: "",
        fill_color: "'.$settings['map_shape_fill_color'].'",
        fill_opacity: '.$settings['map_shape_fill_opacity'].',
        stroke_color: "'.$settings['map_shape_stroke_color'].'",
        stroke_opacity: '.$settings['map_shape_stroke_opacity'].',
        stroke_weight: '.$settings['map_shape_stroke_weight'].',
        atts: "'.http_build_query(['atts'=>$atts], '', '&').'",
        mapsearch: '.($mapsearch ? 'true' : 'false').',
        gplaces: false,
        max_bounds: '.json_encode($max_bounds, JSON_NUMERIC_CHECK).',
        gps_zoom: {
            zl: '.$gps_zl.',
            current: '.$gps_zl_current.'
        },
        access_token: "'.$access_token.'",
        tileserver: '.apply_filters('lsd_leaflet_tileserver', '""').',
        layers: '.json_encode(apply_filters('lsd_map_layers', [], LSD_MP_LEAFLET), JSON_NUMERIC_CHECK).',
    });
    
    // Listdom Maps
    (new ListdomMaps(lsdMap.id)).set(lsdMap);
});
</script>');
?>
<div class="lsd-listing-leaflet">
    <div id="lsd_map<?php echo esc_attr($id); ?>" class="<?php echo $args['canvas_class'] ?? 'lsd-map-canvas'; ?>" <?php if($canvas_height) echo 'style="height: '.esc_attr($canvas_height).'px;"'; ?>></div>
</div>
