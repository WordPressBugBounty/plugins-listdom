<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $settings */
/** @var array $shape */
?>
<script>
var bounds;
var fill_color = '<?php echo esc_js($settings['map_shape_fill_color']); ?>';
var fill_opacity = '<?php echo esc_js($settings['map_shape_fill_opacity']); ?>';
var stroke_color = '<?php echo esc_js($settings['map_shape_stroke_color']); ?>';
var stroke_opacity = '<?php echo esc_js($settings['map_shape_stroke_opacity']); ?>';
var stroke_weight = '<?php echo esc_js($settings['map_shape_stroke_weight']); ?>';
var mapbox_token = '<?php echo esc_js($settings['mapbox_access_token']); ?>';

jQuery(document).ready(function($)
{
    var object_type = $('#lsd_object_type').val();
    var latitude = $('#lsd_object_type_latitude').val();
    var longitude = $('#lsd_object_type_longitude').val();
    var zoomlevel = parseInt($('#lsd_object_type_zoomlevel').val());
    var overlaysArray = [];
    var center = new L.LatLng(latitude, longitude);
    var overlay;
    var latlngs = [];

    // Init map
    var map = L.map(document.getElementById('lsd_address_map')).setView(center, zoomlevel);

    if(mapbox_token)
    {
        // Mapbox Tile Server
        L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            maxZoom: 18,
            id: 'mapbox.streets',
            accessToken: mapbox_token
        }).addTo(map);
    }
    else
    {
        // OSM (OpenStreetMaps)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
            maxZoom: 18
        }).addTo(map);
    }

    // Draw Toolbar
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // Shape Display Options
    var displayOptions = {
        color: stroke_color,
        weight: stroke_weight,
        opacity: stroke_opacity,
        fillColor: fill_color,
        fillOpacity: fill_opacity
    };

    // Draw Control
    var drawControl = new L.Control.Draw(
    {
        draw: {
            marker: false,
            circlemarker: false,
            polyline: {
                shapeOptions: displayOptions
            },
            polygon: {
                shapeOptions: displayOptions
            },
            circle: {
                shapeOptions: displayOptions
            },
            rectangle: {
                shapeOptions: displayOptions
            }
        },
        edit: {
            featureGroup: drawnItems,
            remove: false
        }
    });

    /**
     * Draw previous shape if any
     */
    <?php if(is_array($shape)): ?>

    <?php if(isset($shape['type']) and $shape['type'] == 'polyline'): ?>

    <?php foreach($shape['boundaries'] as $point) echo 'latlngs.push(['.esc_js($point->lat).', '.esc_js($point->lng).']);'; ?>
    overlay = L.polyline(latlngs, displayOptions).addTo(map);

    <?php elseif(isset($shape['type']) and $shape['type'] == 'rectangle'): ?>

    latlngs = [[<?php echo esc_js($shape['north']); ?>, <?php echo esc_js($shape['east']); ?>], [<?php echo esc_js($shape['south']); ?>, <?php echo esc_js($shape['west']); ?>]];
    overlay = L.rectangle(latlngs, displayOptions).addTo(map);

    <?php elseif(isset($shape['type']) and $shape['type'] == 'polygon'): ?>

    <?php foreach($shape['boundaries'] as $point) echo 'latlngs.push(['.esc_js($point->lat).', '.esc_js($point->lng).']);'; ?>
    overlay = L.polygon(latlngs, displayOptions).addTo(map);

    <?php elseif(isset($shape['type']) and $shape['type'] == 'circle'): ?>

    overlay = L.circle([<?php echo esc_js($shape['center']->lat); ?>, <?php echo esc_js($shape['center']->lng); ?>], {radius: <?php echo esc_js($shape['radius']); ?>}).addTo(map);

    <?php endif; ?>

    overlaysArray.push(overlay);
    map.fitBounds(overlay.getBounds());
    drawnItems.addLayer(overlay);

    <?php endif; ?>

    map.on(L.Draw.Event.CREATED, function(e)
    {
        var type = e.layerType;
        overlay = e.layer;

        // Set the boundaries
        lsd_set_boundaries(overlay, type);

        // Delete Overlays
        for(var i = 0; i < overlaysArray.length; i++) overlaysArray[i].remove();
        overlaysArray = [];

        // Push to array
        overlaysArray.push(overlay);

        drawnItems.addLayer(overlay);
        map.fitBounds(overlay.getBounds());
    });

    map.on(L.Draw.Event.EDITED, function(e)
    {
        e.layers.eachLayer(function(overlay)
        {
            var type;

            if(overlay instanceof L.Circle) type = 'circle';
            else if(overlay instanceof L.Marker) type = 'marker';
            else if((overlay instanceof L.Polyline) && !(overlay instanceof L.Polygon)) type = 'polyline';
            else if((overlay instanceof L.Polygon) && !(overlay instanceof L.Rectangle)) type = 'polygon';
            else if(overlay instanceof L.Rectangle) type = 'rectangle';

            // Set the boundaries
            lsd_set_boundaries(overlay, type);
            map.fitBounds(overlay.getBounds());
        });
    });

    // Define Marker
    var marker = L.marker(center, {
        draggable: true
    }).addTo(map);

    /**
     * Marker Event Listeners
     * Marker Drag End
     */
    marker.on('dragend', function()
    {
        $('#lsd_object_type_latitude').val(marker.getLatLng().lat);
        $('#lsd_object_type_longitude').val(marker.getLatLng().lng).trigger('change');
    });

    // Address Changed Manually
    $('#lsd_object_type_address').on('change', function()
    {
        var address = $('#lsd_object_type_address').val();

        $.get(location.protocol + '//nominatim.openstreetmap.org/search?format=json&q='+address, function(data)
        {
            if(data.length > 0)
            {
                var latitude = $('#lsd_object_type_latitude');
                var longitude = $('#lsd_object_type_longitude');

                latitude.val(data[0].lat);
                longitude.val(data[0].lon);

                latitude.trigger('change');
            }
        });
    });

    // Latitude and Longitude Changed Manually
    $('#lsd_object_type_latitude, #lsd_object_type_longitude').on('change', function()
    {
        var lat = $('#lsd_object_type_latitude').val();
        var lng = $('#lsd_object_type_longitude').val();
        var position = new L.LatLng(lat, lng);

        marker.setLatLng(position);
        map.flyTo(position, map.getZoom());
    });

    // Object Type is shape so hide the marker
    if(object_type === 'shape')
    {
        marker.remove();

        map.addControl(drawControl);
        if(overlay) overlay.addTo(map);
    }
    // Object Type is marker so hide drawing tools
    else if(object_type === 'marker')
    {
        drawControl.remove();
        if(overlay) overlay.remove();

        marker.addTo(map);
    }

    // Object Marker Type Selected
    $('#lsd_metabox_object_type_marker').on('click', function()
    {
        $('#lsd_object_type').val('marker');

        marker.addTo(map);

        drawControl.remove();
        if(overlay) overlay.remove();
    });

    // Object Shape Type Selected
    $('#lsd_metabox_object_type_shape').on('click', function()
    {
        $('#lsd_object_type').val('shape');

        marker.remove();

        map.addControl(drawControl);
        if(overlay) overlay.addTo(map);
    });

    // Load Tiles on Block Editor
    setTimeout(function()
    {
        map.invalidateSize();
        if(overlay) map.fitBounds(overlay.getBounds());
    }, 1000);
});

function lsd_set_boundaries(overlay, type)
{
    var paths = [];
    var radius;

    if(type === 'polygon')
    {
        overlay.getLatLngs().forEach(function(points)
        {
            points.forEach(function(point)
            {
                paths.push(L.latLng(point.lat, point.lng));
            });
        });
    }
    else if(type === 'polyline')
    {
        overlay.getLatLngs().forEach(function(point)
        {
            paths.push(L.latLng(point.lat, point.lng));
        });
    }
    else if(type === 'rectangle')
    {
        var ne = overlay.getBounds().getNorthEast();
        var sw = overlay.getBounds().getSouthWest();

        paths.push(L.latLng(ne.lat, ne.lng));
        paths.push(L.latLng(sw.lat, sw.lng));
    }
    else if(type === 'circle')
    {
        var center = overlay.getLatLng();
        paths.push(L.latLng(center.lat, center.lng));

        radius = overlay.getRadius();
    }

    jQuery('#lsd_shape_type').val(type);
    jQuery('#lsd_shape_paths').val(paths.toString());
    jQuery('#lsd_shape_radius').val(radius).trigger('change');
}
</script>