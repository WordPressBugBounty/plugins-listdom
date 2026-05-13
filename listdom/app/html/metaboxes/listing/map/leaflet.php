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
const mapbox_token = '<?php echo esc_js($settings['mapbox_access_token']); ?>';
const address_autosuggest_enabled = <?php echo isset($settings['address_autosuggest']) && !$settings['address_autosuggest'] ? 'false' : 'true'; ?>;

jQuery(document).ready(function($)
{
    const object_type = $('#lsd_object_type').val();
    const latitude = $('#lsd_object_type_latitude').val();
    const longitude = $('#lsd_object_type_longitude').val();
    const zoomlevel = parseInt($('#lsd_object_type_zoomlevel').val());
    const $addressField = $('#lsd_object_type_address');
    const $latitudeField = $('#lsd_object_type_latitude');
    const $longitudeField = $('#lsd_object_type_longitude');
    const $locateButton = $('#lsd_object_type_address_locate');
    const dropdownTriggers = $locateButton.length ? $addressField.add($locateButton) : $addressField;
    const dropdown = address_autosuggest_enabled ? (function ()
    {
        const $dropdownElement = $('#lsd_object_type_address_dropdown');
        const loadingText =
            ($dropdownElement.length && $dropdownElement.data('loading-text'))
                ? $dropdownElement.data('loading-text')
                : '<?php echo esc_js(__('Loading addresses…', 'listdom')); ?>';

        if (typeof window.lsdGetAddressDropdownController === 'function')
        {
            return window.lsdGetAddressDropdownController($dropdownElement, {
                trigger: dropdownTriggers,
                storeElement: $dropdownElement,
                dropdownOptions: {loadingText: loadingText}
            });
        }

        if (typeof window.lsdCreateAddressDropdown === 'function')
        {
            const instance = window.lsdCreateAddressDropdown($dropdownElement, {loadingText: loadingText});
            if (instance && instance.$element && instance.$element.length)
            {
                instance.$element.data('lsdDropdownTrigger', dropdownTriggers);
            }
            return instance;
        }

        return {
            $element: $dropdownElement,
            reset: function () {},
            populate: function () {},
            markSelected: function () {},
            isUpdating: function ()
            {
                return false;
            },
            selectItem: function () {},
            hide: function () {},
            showLoading: function () {},
            show: function () {},
        };
    })() : {
        $element: $('#lsd_object_type_address_dropdown'),
        reset: function () {},
        populate: function () {},
        markSelected: function () {},
        isUpdating: function ()
        {
            return false;
        },
        selectItem: function () {},
        hide: function () {},
        showLoading: function () {},
        show: function () {},
    };

    if ($addressField.length)
    {
        dropdown.markSelected($addressField.val(), {lat: $latitudeField.val(), lon: $longitudeField.val()});
        $addressField.off('input.lsd-dropdown-reset').on('input.lsd-dropdown-reset', function ()
        {
            dropdown.reset();
        });
    }

    if (address_autosuggest_enabled && dropdown.$element.length)
    {
        dropdown.$element
            .off('lsd-autocomplete-select.lsd-dropdown')
            .on('lsd-autocomplete-select.lsd-dropdown', function (event, item)
            {
                if (dropdown.isUpdating()) return;

                const hasItem = item && typeof item === 'object';
                const value = hasItem ? item.value || item.label || '' : '';
                const lat = hasItem && item.lat ? item.lat : '';
                const lon = hasItem && item.lon ? item.lon : '';

                if (!value)
                {
                    $addressField.val('');
                    dropdown.markSelected('', null);
                    return;
                }

                const label = item.label || value;
                $addressField.val(label);

                if (lat && lon)
                {
                    applyCoordinates(lat, lon, {address: label, reverse: false});
                }
                else
                {
                    $latitudeField.val('');
                    $longitudeField.val('').trigger('change');
                    dropdown.markSelected(label, null);
                }
            });
    }

    let overlaysArray = [];
    const center = new L.LatLng(latitude, longitude);
    let overlay;
    let latlngs = [];

    // Init map
    const map = L.map(document.getElementById('lsd_address_map'), {
        scrollWheelZoom: false,
    }).setView(center, zoomlevel);

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

    let osmRequest = null;

    if (address_autosuggest_enabled && $addressField.length && typeof window.lsdAddressAutocompleteSources !== 'undefined')
    {
        $addressField.off('input.lsd-osm-dropdown').on('input.lsd-osm-dropdown', function ()
        {
            if (osmRequest && typeof osmRequest.cancel === 'function') osmRequest.cancel();

            const term = $addressField.val();

            if (!term || term.length < 3)
            {
                dropdown.reset();
                return;
            }

            dropdown.showLoading();

            const requestTerm = term;
            osmRequest = window.lsdAddressAutocompleteSources.fetch('openstreetmap', requestTerm, {
                limit: 5,
                minLength: 3
            });

            if (!osmRequest || !osmRequest.promise) return;

            osmRequest.promise
                .done(function (response)
                {
                    if ($addressField.val() !== requestTerm) return;

                    const items = response && response.items ? response.items : [];
                    if (!items.length)
                    {
                        dropdown.reset();
                        return;
                    }

                    dropdown.populate(items);
                })
                .fail(function ()
                {
                    dropdown.reset();
                });
        });
    }
    else
    {
        dropdown.reset();
    }

    const reverseGeocode = function(lat, lng, callback)
    {
        const latNum = parseFloat(lat);
        const lonNum = parseFloat(lng);

        if (isNaN(latNum) || isNaN(lonNum))
        {
            if (typeof callback === 'function') callback('');
            return;
        }

        $.getJSON('https://nominatim.openstreetmap.org/reverse', {format: 'json', lat: latNum, lon: lonNum})
            .done(function(data)
            {
                const label = (data && data.display_name) ? data.display_name : '';
                if (typeof callback === 'function') callback(label);
            })
            .fail(function()
            {
                if (typeof callback === 'function') callback('');
            });
    };

    // Draw Toolbar
    const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // Shape Display Options
    const displayOptions = {
        color: stroke_color,
        weight: stroke_weight,
        opacity: stroke_opacity,
        fillColor: fill_color,
        fillOpacity: fill_opacity
    };

    // Draw Control
    const drawControl = new L.Control.Draw(
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

    latlngs = [['<?php echo esc_js($shape['north']); ?>', '<?php echo esc_js($shape['east']); ?>'], ['<?php echo esc_js($shape['south']); ?>', '<?php echo esc_js($shape['west']); ?>']];
    overlay = L.rectangle(latlngs, displayOptions).addTo(map);

    <?php elseif(isset($shape['type']) and $shape['type'] == 'polygon'): ?>

    <?php foreach($shape['boundaries'] as $point) echo 'latlngs.push(['.esc_js($point->lat).', '.esc_js($point->lng).']);'; ?>
    overlay = L.polygon(latlngs, displayOptions).addTo(map);

    <?php elseif(isset($shape['type']) and $shape['type'] == 'circle'): ?>

    overlay = L.circle(['<?php echo esc_js($shape['center']->lat); ?>', '<?php echo esc_js($shape['center']->lng); ?>'], {radius: <?php echo esc_js($shape['radius']); ?>}).addTo(map);

    <?php endif; ?>

    overlaysArray.push(overlay);
    map.fitBounds(overlay.getBounds());
    drawnItems.addLayer(overlay);

    <?php endif; ?>

    map.on(L.Draw.Event.CREATED, function(e)
    {
        let type = e.layerType;
        overlay = e.layer;

        // Set the boundaries
        lsd_set_boundaries(overlay, type);

        // Delete Overlays
        for(let i = 0; i < overlaysArray.length; i++) overlaysArray[i].remove();
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
            let type;

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
    const marker = L.marker(center, {
        draggable: true
    }).addTo(map);

    let coordinateInputsAreUpdating = false;

    const applyCoordinates = function(latValue, lonValue, options = {})
    {
        if ((latValue === '' || typeof latValue === 'undefined') || (lonValue === '' || typeof lonValue === 'undefined')) return null;

        const latNum = parseFloat(latValue);
        const lonNum = parseFloat(lonValue);
        if (isNaN(latNum) || isNaN(lonNum)) return null;

        coordinateInputsAreUpdating = true;
        $latitudeField.val(latNum).trigger('change');
        $longitudeField.val(lonNum).trigger('change');
        coordinateInputsAreUpdating = false;

        if (!options.skipMarker) marker.setLatLng({ lat: latNum, lng: lonNum });

        if (!options.skipCenter)
        {
            const desiredZoom = (typeof options.zoom !== 'undefined') ? options.zoom : map.getZoom();
            map.setView([latNum, lonNum], desiredZoom);
        }

        if (options.address && options.address.length) $addressField.val(options.address);

        dropdown.markSelected($addressField.val(), {lat: latNum, lon: lonNum});

        return {lat: latNum, lon: lonNum};
    };

    const finalizeAddressFromCoordinates = function(latValue, lonValue, fallbackLabel, done)
    {
        const latNum = parseFloat(latValue);
        const lonNum = parseFloat(lonValue);
        if (isNaN(latNum) || isNaN(lonNum))
        {
            if (typeof done === 'function') done();
            return;
        }

        reverseGeocode(latNum, lonNum, function(addressText)
        {
            if (addressText) $addressField.val(addressText);
            else if (!$addressField.val() && fallbackLabel) $addressField.val(fallbackLabel);

            dropdown.markSelected($addressField.val(), {lat: latNum, lon: lonNum});

            if (typeof done === 'function') done();
        });
    };

    const locateMessages = {
        unsupported: $locateButton.data('error-unsupported') || '',
        denied: $locateButton.data('error-denied') || '',
        failed: $locateButton.data('error-failed') || ''
    };
    const locateGpsLabel = $locateButton.data('gps-label') || '';

    const setLocateLoading = function(loading)
    {
        if (!$locateButton.length) return;
        $locateButton.toggleClass('lsd-is-loading', !!loading);
    };

    const showLocateError = function(message)
    {
        if (!message) return;

        if (typeof window.listdom_toastify === 'function') window.listdom_toastify(message, 'lsd-error');
        else window.alert(message);
    };

    if ($locateButton.length && typeof window.lsdBindLocateControl === 'function')
    {
        window.lsdBindLocateControl($locateButton, {
            messages: locateMessages,
            expectsFinalize: true,
            setLoading: setLocateLoading,
            showError: showLocateError,
            onDenied: function ()
            {
                $latitudeField.val('');
                $longitudeField.val('').trigger('change');
            },
            onError: function ()
            {
                $latitudeField.val('');
                $longitudeField.val('').trigger('change');
            },
            onSuccess: function(lat, lng, done)
            {
                const coords = applyCoordinates(lat, lng, {reverse: false, zoom: Math.max(map.getZoom(), 12)});
                const latNum = coords ? coords.lat : parseFloat(lat) || 0;
                const lonNum = coords ? coords.lon : parseFloat(lng) || 0;

                finalizeAddressFromCoordinates(latNum, lonNum, locateGpsLabel, done);
            }
        });
    }

    /**
     * Marker Event Listeners
     * Marker Drag End
     */
    marker.on('dragend', function()
    {
        const position = marker.getLatLng();
        applyCoordinates(position.lat, position.lng, {reverse: false, skipCenter: true});
        finalizeAddressFromCoordinates(position.lat, position.lng);
    });

    // Latitude and Longitude Changed Manually
    $('#lsd_object_type_latitude, #lsd_object_type_longitude').on('change', function()
    {
        if (coordinateInputsAreUpdating) return;

        const lat = $('#lsd_object_type_latitude').val();
        const lng = $('#lsd_object_type_longitude').val();
        const coords = applyCoordinates(lat, lng, {reverse: false});
        if (coords) finalizeAddressFromCoordinates(coords.lat, coords.lon);
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
    const paths = [];
    let radius;

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
        const ne = overlay.getBounds().getNorthEast();
        const sw = overlay.getBounds().getSouthWest();

        paths.push(L.latLng(ne.lat, ne.lng));
        paths.push(L.latLng(sw.lat, sw.lng));
    }
    else if(type === 'circle')
    {
        const center = overlay.getLatLng();
        paths.push(L.latLng(center.lat, center.lng));

        radius = overlay.getRadius();
    }

    jQuery('#lsd_shape_type').val(type);
    jQuery('#lsd_shape_paths').val(paths.toString());
    jQuery('#lsd_shape_radius').val(radius).trigger('change');
}
</script>
