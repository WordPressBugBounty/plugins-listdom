<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $settings */
/** @var array $shape */
?>
<script>
let bounds;
const fill_color = '<?php echo esc_js($settings['map_shape_fill_color']); ?>';
const fill_opacity = '<?php echo esc_js($settings['map_shape_fill_opacity']); ?>';
const stroke_color = '<?php echo esc_js($settings['map_shape_stroke_color']); ?>';
const stroke_opacity = '<?php echo esc_js($settings['map_shape_stroke_opacity']); ?>';
const stroke_weight = '<?php echo esc_js($settings['map_shape_stroke_weight']); ?>';

jQuery(document).ready(function($)
{
    listdom_add_googlemaps_callbacks(function()
    {
        const object_type = $('#lsd_object_type').val();
        const latitude = $('#lsd_object_type_latitude').val();
        const longitude = $('#lsd_object_type_longitude').val();
        const zoomlevel = parseInt($('#lsd_object_type_zoomlevel').val());
        let overlaysArray = [];

        bounds = new google.maps.LatLngBounds();
        const center = new google.maps.LatLng(latitude, longitude);

        // Init map
        const map = new google.maps.Map(document.getElementById('lsd_address_map'),
        {
            scrollwheel: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: center,
            zoom: zoomlevel
        });

        // Define the Marker
        const marker = new google.maps.Marker(
        {
            position: center,
            draggable: true,
            map: map
        });

        /**
         * Marker Event Listeners
         * Marker Drag End
         */
        marker.addListener('dragend', function(event)
        {
            $('#lsd_object_type_latitude').val(event.latLng.lat());
            $('#lsd_object_type_longitude').val(event.latLng.lng()).trigger('change');
        });

        // Drawing Tools
        const drawingManager = new google.maps.drawing.DrawingManager(
        {
            drawingMode: google.maps.drawing.OverlayType.MARKER,
            drawingControl: true,
            drawingControlOptions: {
                position: google.maps.ControlPosition.TOP_CENTER,
                drawingModes: ['circle', 'polygon', 'polyline', 'rectangle']
            },
            circleOptions:
            {
                fillColor: fill_color,
                fillOpacity: fill_opacity,
                strokeOpacity: stroke_opacity,
                strokeColor: stroke_color,
                strokeWeight: stroke_weight,
                clickable: false,
                editable: true
            },
            polygonOptions:
            {
                fillColor: fill_color,
                fillOpacity: fill_opacity,
                strokeOpacity: stroke_opacity,
                strokeColor: stroke_color,
                strokeWeight: stroke_weight,
                editable: true,
            },
            polylineOptions:
            {
                strokeOpacity: stroke_opacity,
                strokeColor: stroke_color,
                strokeWeight: stroke_weight,
                editable: true
            },
            rectangleOptions:
            {
                fillColor: fill_color,
                fillOpacity: fill_opacity,
                strokeOpacity: stroke_opacity,
                strokeColor: stroke_color,
                strokeWeight: stroke_weight,
                editable: true,
            }
        });

        drawingManager.setMap(map);
        drawingManager.setOptions({drawingMode: null});

        /**
         * Drawing Event Listeners
         * Overlay Complete
         */
        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event)
        {
            overlay = event.overlay;
            drawingManager.setOptions({drawingMode: null});

            // Set the boundaries
            lsd_set_boundaries(overlay, event.type);

            // Delete Overlays
            for(let i = 0; i < overlaysArray.length; i++) overlaysArray[i].setMap(null);
            overlaysArray = [];

            // Push to array
            overlaysArray.push(overlay);

            // Set the listeners
            lsd_set_listeners(overlay, event.type);

            // Extend Map Bounds
            lsd_extend_map_bounds(overlay, event.type);
            map.fitBounds(bounds);
        });

        /**
         * Draw previous shape if any
         */
        <?php if(is_array($shape)): ?>

        const overlay;
        <?php if(isset($shape['type']) and $shape['type'] == 'polyline'): ?>
        overlay = new google.maps.Polyline(
        {
            path: <?php echo json_encode($shape['boundaries'], JSON_NUMERIC_CHECK); ?>,
            strokeOpacity: stroke_opacity,
            strokeColor: stroke_color,
            strokeWeight: stroke_weight,
            editable: true
        });

        overlay.setMap(map);
        overlaysArray.push(overlay);

        // Set the listeners
        lsd_set_listeners(overlay, google.maps.drawing.OverlayType.POLYLINE);

        // Extend Map Bounds
        lsd_extend_map_bounds(overlay, google.maps.drawing.OverlayType.POLYLINE);
        <?php elseif(isset($shape['type']) and $shape['type'] == 'rectangle'): ?>
        overlay = new google.maps.Rectangle(
        {
            fillColor: fill_color,
            fillOpacity: fill_opacity,
            strokeOpacity: stroke_opacity,
            strokeColor: stroke_color,
            strokeWeight: stroke_weight,
            editable: true,
            bounds: {
                north: <?php echo esc_js($shape['north']); ?>,
                south: <?php echo esc_js($shape['south']); ?>,
                east: <?php echo esc_js($shape['east']); ?>,
                west: <?php echo esc_js($shape['west']); ?>
            }
        });

        overlay.setMap(map);
        overlaysArray.push(overlay);

        // Set the listeners
        lsd_set_listeners(overlay, google.maps.drawing.OverlayType.RECTANGLE);

        // Extend Map Bounds
        lsd_extend_map_bounds(overlay, google.maps.drawing.OverlayType.RECTANGLE);
        <?php elseif(isset($shape['type']) and $shape['type'] == 'polygon'): ?>
        overlay = new google.maps.Polygon(
        {
            paths: <?php echo json_encode($shape['boundaries'], JSON_NUMERIC_CHECK); ?>,
            fillColor: fill_color,
            fillOpacity: fill_opacity,
            strokeOpacity: stroke_opacity,
            strokeColor: stroke_color,
            strokeWeight: stroke_weight,
            editable: true,
        });

        overlay.setMap(map);
        overlaysArray.push(overlay);

        // Set the listeners
        lsd_set_listeners(overlay, google.maps.drawing.OverlayType.POLYGON);

        // Extend Map Bounds
        lsd_extend_map_bounds(overlay, google.maps.drawing.OverlayType.POLYGON);
        <?php elseif(isset($shape['type']) and $shape['type'] == 'circle'): ?>
        overlay = new google.maps.Circle(
        {
            fillColor: fill_color,
            fillOpacity: fill_opacity,
            strokeOpacity: stroke_opacity,
            strokeColor: stroke_color,
            strokeWeight: stroke_weight,
            clickable: false,
            editable: true,
            center: <?php echo json_encode($shape['center'], JSON_NUMERIC_CHECK); ?>,
            radius: <?php echo esc_js($shape['radius']); ?>
        });

        overlay.setMap(map);
        overlaysArray.push(overlay);

        // Set the listeners
        lsd_set_listeners(overlay, google.maps.drawing.OverlayType.CIRCLE);

        // Extend Map Bounds
        lsd_extend_map_bounds(overlay, google.maps.drawing.OverlayType.CIRCLE);
        <?php endif; ?>

        map.fitBounds(bounds);
        <?php endif; ?>

        // Object Type is shape so hide the marker
        if(object_type === 'shape') marker.setMap(null);

        // Object Type is marker so hide drawing tools
        if(object_type === 'marker')
        {
            drawingManager.setMap(null);
            if(typeof overlay !== 'undefined' && typeof overlay.setMap === 'function') overlay.setMap(null);
        }

        // Address Changed Manually
        $('#lsd_object_type_address').on('change', function ()
        {
            const geocoder = new google.maps.Geocoder();
            const address = $('#lsd_object_type_address').val();
            geocoder.geocode({'address': address}, function(results, status)
            {
                if(status === 'OK')
                {
                    const latitude = $('#lsd_object_type_latitude');
                    const longitude = $('#lsd_object_type_longitude');

                    latitude.val(results[0].geometry.location.lat());
                    longitude.val(results[0].geometry.location.lng());

                    latitude.trigger('change');
                }
            });
        });

        // Latitude and Longitude Changed Manually
        $('#lsd_object_type_latitude, #lsd_object_type_longitude').on('change', function()
        {
            const lat = $('#lsd_object_type_latitude').val();
            const lng = $('#lsd_object_type_longitude').val();
            const position = new google.maps.LatLng(lat, lng);

            marker.setPosition(position);
            map.setCenter(position);
        });

        // Object Marker Type Selected
        $('#lsd_metabox_object_type_marker').on('click', function()
        {
            $('#lsd_object_type').val('marker');

            marker.setMap(map);
            drawingManager.setMap(null);
            if(typeof overlay !== 'undefined' && typeof overlay.setMap === 'function') overlay.setMap(null);
        });

        // Object Shape Type Selected
        $('#lsd_metabox_object_type_shape').on('click', function ()
        {
            $('#lsd_object_type').val('shape');

            marker.setMap(null);
            drawingManager.setMap(map);
            if(typeof overlay !== 'undefined' && typeof overlay.setMap === 'function') overlay.setMap(map);
        });
    });
});

function lsd_set_boundaries(overlay, type)
{
    let paths = [];
    let radius;

    if(type === google.maps.drawing.OverlayType.POLYGON)
    {
        overlay.getPaths().forEach(function(path)
        {
            const points = path.getArray();
            for(let b in points)
            {
                paths.push(new google.maps.LatLng(points[b].lat(), points[b].lng()));
            }
        });
    }
    else if(type === google.maps.drawing.OverlayType.POLYLINE)
    {
        overlay.getPath().forEach(function(path)
        {
            paths.push(new google.maps.LatLng(path.lat(), path.lng()));
        });
    }
    else if(type === google.maps.drawing.OverlayType.RECTANGLE)
    {
        const ne = overlay.getBounds().getNorthEast();
        const sw = overlay.getBounds().getSouthWest();

        paths.push(new google.maps.LatLng(ne.lat(), ne.lng()));
        paths.push(new google.maps.LatLng(sw.lat(), sw.lng()));
    }
    else if(type === google.maps.drawing.OverlayType.CIRCLE)
    {
        paths.push(new google.maps.LatLng(overlay.getCenter().lat(), overlay.getCenter().lng()));
        radius = overlay.getRadius();
    }

    jQuery('#lsd_shape_type').val(type);
    jQuery('#lsd_shape_paths').val(paths.toString());
    jQuery('#lsd_shape_radius').val(radius).trigger('change');
}

function lsd_set_listeners(overlay, type)
{
    // POLYGON
    if(type === google.maps.drawing.OverlayType.POLYGON)
    {
        overlay.getPaths().forEach(function(path)
        {
            google.maps.event.addListener(path, 'insert_at', function()
            {
                lsd_set_boundaries(overlay, type);
            });

            google.maps.event.addListener(path, 'remove_at', function()
            {
                lsd_set_boundaries(overlay, type);
            });

            google.maps.event.addListener(path, 'set_at', function()
            {
                lsd_set_boundaries(overlay, type);
            });
        });
    }
    // POLYLINE
    else if(type === google.maps.drawing.OverlayType.POLYLINE)
    {
        google.maps.event.addListener(overlay.getPath(), 'insert_at', function()
        {
            lsd_set_boundaries(overlay, type);
        });

        google.maps.event.addListener(overlay.getPath(), 'remove_at', function()
        {
            lsd_set_boundaries(overlay, type);
        });

        google.maps.event.addListener(overlay.getPath(), 'set_at', function()
        {
            lsd_set_boundaries(overlay, type);
        });
    }
    // RECTANGLE
    else if(type === google.maps.drawing.OverlayType.RECTANGLE)
    {
        google.maps.event.addListener(overlay, 'bounds_changed', function()
        {
            lsd_set_boundaries(overlay, type);
        });
    }
    // CIRCLE
    else if(type === google.maps.drawing.OverlayType.CIRCLE)
    {
        google.maps.event.addListener(overlay, 'radius_changed', function()
        {
            lsd_set_boundaries(overlay, type);
        });

        google.maps.event.addListener(overlay, 'center_changed', function()
        {
            lsd_set_boundaries(overlay, type);
        });
    }
}

function lsd_extend_map_bounds(overlay, type)
{
    // POLYGON
    if(type === google.maps.drawing.OverlayType.POLYGON)
    {
        overlay.getPaths().forEach(function(path)
        {
            const points = path.getArray();
            for(let p in points) bounds.extend(points[p]);
        });
    }
    // POLYLINE
    else if(type === google.maps.drawing.OverlayType.POLYLINE)
    {
        const path = overlay.getPath();

        let slat, blat;
        slat = blat = path.getAt(0).lat();

        let slng, blng;
        slng = blng = path.getAt(0).lng();

        for(let i = 1; i < path.getLength(); i++)
        {
            const e = path.getAt(i);

            slat = (slat < e.lat()) ? slat : e.lat();
            blat = (blat > e.lat()) ? blat : e.lat();
            slng = (slng < e.lng()) ? slng : e.lng();
            blng = (blng > e.lng()) ? blng : e.lng();
        }

        bounds.extend(new google.maps.LatLng(slat, slng));
        bounds.extend(new google.maps.LatLng(blat, blng));
    }
    // RECTANGLE
    else if(type === google.maps.drawing.OverlayType.RECTANGLE)
    {
        bounds.union(overlay.getBounds());
    }
    // CIRCLE
    else if(type === google.maps.drawing.OverlayType.CIRCLE)
    {
        bounds.union(overlay.getBounds());
    }
}
</script>