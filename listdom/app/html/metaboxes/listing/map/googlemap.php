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
const address_autosuggest_enabled = <?php echo isset($settings['address_autosuggest']) && !$settings['address_autosuggest'] ? 'false' : 'true'; ?>;

jQuery(document).ready(function($)
{
    listdom_add_googlemaps_callbacks(function()
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
        let coordinateInputsAreUpdating = false;

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

        let googleServices = null;
        let googlePredictionRequest = null;
        const geocoder = (typeof google.maps.Geocoder === 'function') ? new google.maps.Geocoder() : null;

        const geocodeAddress = function (address, callback)
        {
            if (!geocoder || !address)
            {
                if (typeof callback === 'function') callback(null);
                return;
            }

            geocoder.geocode({address: address}, function (results, status)
            {
                if (status === 'OK' && results && results.length)
                {
                    if (typeof callback === 'function') callback(results[0]);
                    return;
                }

                if (typeof callback === 'function') callback(null);
            });
        };

        const reverseGeocode = function(lat, lng, callback)
        {
            const latNum = parseFloat(lat);
            const lngNum = parseFloat(lng);

            if (isNaN(latNum) || isNaN(lngNum))
            {
                if (typeof callback === 'function') callback('');
                return;
            }

            const fallback = function()
            {
                jQuery.getJSON('https://nominatim.openstreetmap.org/reverse', {format: 'json', lat: latNum, lon: lngNum})
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

            if (geocoder)
            {
                geocoder.geocode({location: {lat: latNum, lng: lngNum}}, function(results, status)
                {
                    if (status === 'OK' && results && results.length)
                    {
                        const result = results[0];
                        const formatted = result.formatted_address || result.formattedAddress || (result.name ? result.name : '');
                        if (typeof callback === 'function') callback(formatted || '');
                        return;
                    }

                    fallback();
                });

                return;
            }

            fallback();
        };

        const handleDropdownChange = function (item)
        {
            if (dropdown.isUpdating()) return;

            const hasItem = item && typeof item === 'object';
            const value = hasItem ? item.value || item.label || '' : '';
            const label = hasItem ? item.label || item.value || '' : '';
            const lat = hasItem && item.lat ? item.lat : '';
            const lon = hasItem && item.lon ? item.lon : '';
            const placeId = hasItem && item.placeId ? item.placeId : '';

            if (!value)
            {
                $addressField.val('');
                dropdown.markSelected('', null);
                return;
            }

            $addressField.val(label || value);

            if (lat && lon)
            {
                applyCoordinates(lat, lon, {address: label || value, placeId: placeId, reverse: false});
                return;
            }

            if (placeId && googleServices && googleServices.places)
            {
                const statusEnum = (typeof google.maps.places !== 'undefined' && google.maps.places.PlacesServiceStatus) ? google.maps.places.PlacesServiceStatus : {};
                googleServices.places.getDetails({placeId: placeId, fields: ['geometry', 'formatted_address', 'name', 'place_id']}, function (result, status)
                {
                    if (status !== statusEnum.OK || !result)
                    {
                        geocodeAddress(value, function (geocodeResult)
                        {
                            if (!geocodeResult) return;

                            const location = geocodeResult.geometry && geocodeResult.geometry.location;
                            if (!location) return;

                            const latFn = typeof location.lat === 'function' ? location.lat : null;
                            const lngFn = typeof location.lng === 'function' ? location.lng : null;
                            const latValue = latFn ? latFn.call(location) : location.lat || '';
                            const lonValue = lngFn ? lngFn.call(location) : location.lng || '';

                            const formatted = geocodeResult.formatted_address || value;
                            applyCoordinates(latValue, lonValue, {address: formatted, placeId: placeId, reverse: false});
                        });
                        return;
                    }

                    const location = result.geometry && result.geometry.location;
                    if (!location) return;

                    const latFn = typeof location.lat === 'function' ? location.lat : null;
                    const lngFn = typeof location.lng === 'function' ? location.lng : null;
                    const latValue = latFn ? latFn.call(location) : location.lat || '';
                    const lonValue = lngFn ? lngFn.call(location) : location.lng || '';

                    const formatted = result.formatted_address || result.name || value;
                    applyCoordinates(latValue, lonValue, {address: formatted, placeId: result.place_id || placeId, reverse: false});

                    if (googleServices) googleServices.sessionToken = null;
                });

                return;
            }

            geocodeAddress(value, function (geocodeResult)
            {
                if (!geocodeResult) return;

                const location = geocodeResult.geometry && geocodeResult.geometry.location;
                if (!location) return;

                const latFn = typeof location.lat === 'function' ? location.lat : null;
                const lngFn = typeof location.lng === 'function' ? location.lng : null;
                const latValue = latFn ? latFn.call(location) : location.lat || '';
                const lonValue = lngFn ? lngFn.call(location) : location.lng || '';

                applyCoordinates(latValue, lonValue, {address: value, placeId: placeId, reverse: false});
            });
        };

        if (address_autosuggest_enabled && dropdown.$element.length)
        {
            dropdown.$element
                .off('lsd-autocomplete-select.lsd-dropdown')
                .on('lsd-autocomplete-select.lsd-dropdown', function (event, item)
                {
                    handleDropdownChange(item);
                });
        }
        else
        {
            dropdown.reset();
        }
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

        // Initialize Google Places Autocomplete if Places library is available
        if (address_autosuggest_enabled && typeof google.maps.places !== 'undefined')
        {
            const input = $addressField.length ? $addressField.get(0) : null;

            if (input)
            {
                input.setAttribute('autocomplete', 'off');
                input.setAttribute('aria-autocomplete', 'list');
            }

            if (typeof window.lsdAddressAutocompleteSources !== 'undefined')
            {
                googleServices = window.lsdAddressAutocompleteSources.ensureGoogleServices({
                    services: googleServices || {},
                    map: map
                }) || googleServices;
            }

            if ($addressField.length && typeof window.lsdAddressAutocompleteSources !== 'undefined')
            {
                $addressField.off('input.lsd-google-dropdown').on('input.lsd-google-dropdown', function ()
                {
                    const term = $addressField.val();

                    googleServices = window.lsdAddressAutocompleteSources.ensureGoogleServices({
                        services: googleServices || {},
                        map: map
                    }) || googleServices;

                    if (!googleServices || !googleServices.autocomplete)
                    {
                        dropdown.reset();
                        return;
                    }

                    if (googlePredictionRequest && typeof googlePredictionRequest.cancel === 'function')
                    {
                        googlePredictionRequest.cancel();
                    }

                    if (!term || term.length < 3)
                    {
                        dropdown.reset();
                        return;
                    }

                    dropdown.showLoading();

                    const requestTerm = term;
                    googlePredictionRequest = window.lsdAddressAutocompleteSources.fetch('googlemap', requestTerm, {
                        services: googleServices,
                        map: map,
                        minLength: 3
                    });

                    if (!googlePredictionRequest || !googlePredictionRequest.promise) return;

                    googlePredictionRequest.promise
                        .done(function (response)
                        {
                            if ($addressField.val() !== requestTerm) return;

                            if (response && response.services) googleServices = response.services;

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
        }

        $addressField.off('change.lsd-google-geocode').on('change.lsd-google-geocode', function ()
        {
            const address = $addressField.val();
            if (!address || dropdown.isUpdating()) return;

            geocodeAddress(address, function (result)
            {
                if (!result) return;

                const location = result.geometry && result.geometry.location;
                if (!location) return;

                const latFn = typeof location.lat === 'function' ? location.lat : null;
                const lngFn = typeof location.lng === 'function' ? location.lng : null;
                const latValue = latFn ? latFn.call(location) : location.lat || '';
                const lonValue = lngFn ? lngFn.call(location) : location.lng || '';

                const formatted = result.formatted_address || result.formattedAddress || address;
                applyCoordinates(latValue, lonValue, {address: formatted, reverse: false});
            });
        });

        // Define the Marker
        const marker = new google.maps.Marker(
        {
            position: center,
            draggable: true,
            map: map
        });

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

            if (!options.skipMarker)
            {
                const position = new google.maps.LatLng(latNum, lonNum);
                marker.setPosition(position);
                if (!options.skipCenter) map.setCenter(position);
            }

            if (options.address && options.address.length) $addressField.val(options.address);

            dropdown.markSelected($addressField.val(), {
                lat: latNum,
                lon: lonNum,
                placeId: options.placeId || ''
            });

            return {lat: latNum, lon: lonNum};
        };

        const finalizeAddressFromCoordinates = function(latValue, lonValue, fallbackLabel, done, placeId)
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

                dropdown.markSelected($addressField.val(), {
                    lat: latNum,
                    lon: lonNum,
                    placeId: placeId || ''
                });

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
                    const coords = applyCoordinates(lat, lng, {reverse: false});
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
        marker.addListener('dragend', function(event)
        {
            const latValue = event.latLng.lat();
            const lonValue = event.latLng.lng();

            applyCoordinates(latValue, lonValue, {reverse: false, skipCenter: true});
            finalizeAddressFromCoordinates(latValue, lonValue);
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

        let overlay;
        <?php if(isset($shape['type']) and $shape['type'] == 'polyline'): ?>
        overlay = new google.maps.Polyline(
        {
            path: <?php echo wp_json_encode($shape['boundaries'], JSON_NUMERIC_CHECK); ?>,
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
            paths: <?php echo wp_json_encode($shape['boundaries'], JSON_NUMERIC_CHECK); ?>,
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
            center: <?php echo wp_json_encode($shape['center'], JSON_NUMERIC_CHECK); ?>,
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

        // Latitude and Longitude Changed Manually
        $('#lsd_object_type_latitude, #lsd_object_type_longitude').on('change', function()
        {
            if (coordinateInputsAreUpdating) return;

            const lat = $('#lsd_object_type_latitude').val();
            const lng = $('#lsd_object_type_longitude').val();
            const coords = applyCoordinates(lat, lng, {reverse: false});
            if (coords) finalizeAddressFromCoordinates(coords.lat, coords.lon);
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
