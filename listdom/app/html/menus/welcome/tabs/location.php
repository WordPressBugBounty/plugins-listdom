<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Welcome $this */

// Settings
$settings = LSD_Options::settings();

// Map Assets
LSD_Assets::map();
?>
<div class="lsd-welcome-step-content lsd-util-hide" id="step-2">
    <div class="lsd-welcome-content-header">
        <div class="lsd-admin-section-heading">
            <div class="lsd-admin-title-icon">
                <i class="listdom-icon lsdi-map-pinpoint"></i>
                <h2 class="lsd-admin-title lsd-m-0"><?php echo esc_html__('Location Setting', 'listdom'); ?></h2>
            </div>
            <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Set the default address of your directory. Type the address or use the GPS button to set your current location.' , 'listdom'); ?></p>
        </div>
    </div>

    <div class="lsd-welcome-content-wrapper">
        <form id="lsd_settings_form">
            <div class="lsd-location-settings">
                <div class="lsd-form-row">
                    <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Type the Address', 'listdom'),
                            'for' => 'lsd_settings_map_backend_lt',
                        ]); ?></div>
                    <div class="lsd-col-9">
                        <div class="lsd-wizard-address-field">
                            <?php echo LSD_Form::text([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_address',
                                'placeholder' => esc_attr__('Type at least 3 characters of the location ...', 'listdom'),
                                'attributes' => [
                                    'data-map-provider' => LSD_Map_Provider::get(),
                                    'autocomplete' => 'off',
                                ],
                            ]); ?>
                            <?php if (LSD_Base::is_ssl_active()): ?>
                            <button type="button" class="lsd-secondary-button lsd-wizard-locate-button" id="lsd_wizard_locate"
                                    data-label="<?php echo esc_attr__('Locate me', 'listdom'); ?>"
                                    data-loading-label="<?php echo esc_html__('Locating...', 'listdom'); ?>"
                                    data-gps-label="<?php echo esc_html__('Your location', 'listdom'); ?>"
                                    data-error-unsupported="<?php echo esc_html__('Geolocation is not supported by your browser.', 'listdom'); ?>"
                                    data-error-denied="<?php echo esc_html__('Please allow location access to use this feature.', 'listdom'); ?>"
                                    data-error-failed="<?php echo esc_html__('Unable to retrieve your location.', 'listdom'); ?>">
                                <span class="lsd-wizard-locate-spinner" aria-hidden="true"></span>
                                <i class="lsd-icon fa-solid fa-crosshairs" aria-hidden="true"></i>
                                <span class="lsd-wizard-locate-label"><?php esc_html_e('Locate me', 'listdom'); ?></span>
                            </button>
                            <?php endif; ?>
                            <div id="lsd_address_dropdown" class="lsd-address-autocomplete-dropdown lsd-address-autocomplete-popup" data-placeholder="<?php echo esc_html__('Select an address', 'listdom'); ?>" data-loading-text="<?php echo esc_attr__('Loading addresses…', 'listdom'); ?>" aria-label="<?php echo esc_html__('Select an address', 'listdom'); ?>" role="listbox" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>
                <div class="lsd-form-row lsd-util-hide">
                    <div class="lsd-col-7">
                        <?php echo LSD_Form::hidden([
                            'id' => 'lsd_settings_map_backend_lt',
                            'name' => 'lsd[map_backend_lt]',
                            'value' => $settings['map_backend_lt'] ?? '',
                            'placeholder' => esc_attr__("It's for Google Maps in Add/Edit Map Objects menu.", 'listdom'),
                        ]); ?>
                    </div>
                </div>
                <div class="lsd-form-row lsd-util-hide">
                    <div class="lsd-col-7">
                        <?php echo LSD_Form::hidden([
                            'id' => 'lsd_settings_map_backend_ln',
                            'name' => 'lsd[map_backend_ln]',
                            'value' => $settings['map_backend_ln'] ?? '',
                            'placeholder' => esc_attr__("It's for Google Maps in Add/Edit Listing menu.", 'listdom')
                        ]); ?>
                    </div>
                </div>
                <?php LSD_Form::nonce('lsd_settings_form'); ?>

                <p class="lsd-m-0 lsd-admin-description"><?php esc_html_e(' Drag the map on marker to the center of your default town/city.', 'listdom'); ?></p>
                <div class="lsd-map">
                    <div id="lsd_wizard_location_map"></div>
                </div>
            </div>
        </form>
    </div>
    <div class="lsd-welcome-button-wrapper">
        <button class="lsd-skip-step lsd-text-button"><?php echo esc_html__('Skip', 'listdom'); ?></button>
        <button class="lsd-step-link lsd-primary-button" id="lsd_settings_location_save_button">
            <?php echo esc_html__('Next', 'listdom'); ?>
            <i class="listdom-icon lsdi-right-arrow"></i>
        </button>
    </div>
</div>

<script>
jQuery(function($)
{
    const ltField = $('#lsd_settings_map_backend_lt');
    const lnField = $('#lsd_settings_map_backend_ln');
    const latitude = parseFloat(ltField.val()) || 0;
    const longitude = parseFloat(lnField.val()) || 0;
    const zoom = <?php echo (int) ($settings['map_backend_zl'] ?? 6); ?>;

    const $address = $('#lsd_address');
    const $locate = $('#lsd_wizard_locate');
    const dropdown = (function ()
    {
        const $dropdownElement = $('#lsd_address_dropdown');
        const loadingText =
            ($dropdownElement.length && $dropdownElement.data('loading-text'))
                ? $dropdownElement.data('loading-text')
                : '<?php echo esc_js(__('Loading addresses…', 'listdom')); ?>';

        if (typeof window.lsdGetAddressDropdownController === 'function')
        {
            return window.lsdGetAddressDropdownController($dropdownElement, {
                trigger: $address.add($locate),
                storeElement: $dropdownElement,
                dropdownOptions: {loadingText: loadingText}
            });
        }

        if (typeof window.lsdCreateAddressDropdown === 'function')
        {
            const instance = window.lsdCreateAddressDropdown($dropdownElement, {loadingText: loadingText});
            if (instance && instance.$element && instance.$element.length)
            {
                instance.$element.data('lsdDropdownTrigger', $address.add($locate));
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
    })();

    if ($address.length)
    {
        dropdown.markSelected($address.val(), {lat: ltField.val(), lon: lnField.val()});
        $address.off('input.lsd-dropdown-reset').on('input.lsd-dropdown-reset', function ()
        {
            dropdown.reset();
        });
    }

    let applyDropdownSelection = null;

    if (dropdown.$element.length)
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
                const placeId = hasItem && item.placeId ? item.placeId : '';

                if (!value)
                {
                    $address.val('');
                    if (typeof applyDropdownSelection === 'function') applyDropdownSelection(null);
                    return;
                }

                $address.val(item.label || value);

                if (typeof applyDropdownSelection === 'function')
                {
                    applyDropdownSelection({
                        lat: lat,
                        lon: lon,
                        placeId: placeId,
                        value: value
                    });
                }
            });
    }

    const locateMessages =
    {
        unsupported: $locate.data('error-unsupported'),
        denied: $locate.data('error-denied'),
        failed: $locate.data('error-failed')
    };

    const locateLabel = $locate.length ? ($locate.data('label') || $locate.find('.lsd-wizard-locate-label').text() || '') : '';
    const locateLoadingLabel = $locate.length ? ($locate.data('loading-label') || locateLabel) : '';
    const locateGpsLabel = $locate.length ? $locate.data('gps-label') : '';

    const setLocateLoading = function(loading)
    {
        if (!$locate.length) return;

        $locate.toggleClass('lsd-is-loading', !!loading);
        const $label = $locate.find('.lsd-wizard-locate-label');
        if ($label.length) $label.text(loading ? locateLoadingLabel : locateLabel);
    };

    const showLocateError = function(message)
    {
        if (!message) return;

        if (typeof listdom_toastify === 'function') listdom_toastify(message, 'lsd-error');
        else window.alert(message);
    };

    const attachLocateControl = function(onSuccess)
    {
        if (!$locate.length || typeof window.lsdBindLocateControl !== 'function') return null;

        return window.lsdBindLocateControl($locate, {
            messages: locateMessages,
            expectsFinalize: true,
            setLoading: setLocateLoading,
            showError: showLocateError,
            onSuccess: function(lat, lng, done)
            {
                if (typeof onSuccess !== 'function')
                {
                    done();
                    return;
                }

                onSuccess(lat, lng, function(resolvedAddress)
                {
                    if (resolvedAddress) $address.val(resolvedAddress);
                    else if (!$address.val()) $address.val(locateGpsLabel);

                    dropdown.markSelected($address.val(), {lat: ltField.val(), lon: lnField.val()});
                    done();
                });
            }
        });
    };

    <?php if (LSD_Map_Provider::get() === LSD_MP_GOOGLE): ?>
    listdom_add_googlemaps_callbacks(function()
    {
        const center = { lat: latitude, lng: longitude };
        const map = new google.maps.Map(document.getElementById('lsd_wizard_location_map'),
        {
            center: center,
            zoom: zoom,
            scrollwheel: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        const marker = new google.maps.Marker({ position: center, draggable: true, map: map });

        const geocoder = (typeof google.maps.Geocoder === 'function') ? new google.maps.Geocoder() : null;

        const reverseGeocode = function(lat, lng, callback)
        {
            const latNum = parseFloat(lat);
            const lngNum = parseFloat(lng);

            if (isNaN(latNum) || isNaN(lngNum))
            {
                callback('');
                return;
            }

            const fallback = function()
            {
                $.getJSON('https://nominatim.openstreetmap.org/reverse', {format: 'json', lat: latNum, lon: lngNum})
                    .done(function(data)
                    {
                        callback((data && data.display_name) ? data.display_name : '');
                    })
                    .fail(function()
                    {
                        callback('');
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
                        callback(formatted || '');
                        return;
                    }

                    fallback();
                });

                return;
            }

            fallback();
        };

        const geocodeAddress = function(address, callback)
        {
            if (!address || !geocoder)
            {
                if (typeof callback === 'function') callback(null);
                return;
            }

            geocoder.geocode({address: address}, function(results, status)
            {
                if (status === 'OK' && results && results.length)
                {
                    if (typeof callback === 'function') callback(results[0]);
                    return;
                }

                if (typeof callback === 'function') callback(null);
            });
        };

        google.maps.event.addListener(map, 'click', function(e)
        {
            marker.setPosition(e.latLng);
            const latValue = e.latLng.lat();
            const lonValue = e.latLng.lng();

            ltField.val(latValue);
            lnField.val(lonValue);

            reverseGeocode(latValue, lonValue, function(addressText)
            {
                if (addressText) $address.val(addressText);
                else if (!$address.val()) $address.val(locateGpsLabel);

                dropdown.markSelected($address.val(), {lat: latValue, lon: lonValue});
            });
        });

        google.maps.event.addListener(marker, 'dragend', function(e)
        {
            const latValue = e.latLng.lat();
            const lonValue = e.latLng.lng();

            ltField.val(latValue);
            lnField.val(lonValue);

            reverseGeocode(latValue, lonValue, function(addressText)
            {
                if (addressText) $address.val(addressText);
                else if (!$address.val()) $address.val(locateGpsLabel);

                dropdown.markSelected($address.val(), {lat: latValue, lon: lonValue});
            });
        });

        attachLocateControl(function(lat, lng, finalize)
        {
            const latNum = parseFloat(lat) || 0;
            const lngNum = parseFloat(lng) || 0;
            const location = { lat: latNum, lng: lngNum };

            marker.setPosition(location);
            map.setCenter(location);
            if (typeof map.getZoom === 'function') map.setZoom(Math.max(map.getZoom(), zoom));

            ltField.val(latNum);
            lnField.val(lngNum);

            reverseGeocode(latNum, lngNum, function(addressText)
            {
                finalize(addressText);
            });
        });

        let googleServices = null;
        let googlePredictionRequest = null;

        if (typeof google.maps.places !== 'undefined')
        {
            const inputElement = $address.length ? $address.get(0) : document.getElementById('lsd_address');

            if (inputElement)
            {
                inputElement.setAttribute('autocomplete', 'off');
                inputElement.setAttribute('aria-autocomplete', 'list');
            }

            if (typeof window.lsdAddressAutocompleteSources !== 'undefined')
            {
                googleServices = window.lsdAddressAutocompleteSources.ensureGoogleServices({
                    services: googleServices || {},
                    map: map
                }) || googleServices;
            }

            if ($address.length && typeof window.lsdAddressAutocompleteSources !== 'undefined')
            {
                $address.off('input.lsd-google-dropdown').on('input.lsd-google-dropdown', function ()
                {
                    const term = $address.val();

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
                            if ($address.val() !== requestTerm) return;

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

                applyDropdownSelection = function (data)
                {
                    if (!data)
                    {
                        ltField.val('');
                        lnField.val('');
                        dropdown.markSelected('', null);
                        return;
                    }

                    const value = data.value || data.label || $address.val() || '';
                    const lat = data.lat || '';
                    const lon = data.lon || '';
                    const placeId = data.placeId || '';

                    const statusEnum = (googleServices && googleServices.statusEnum)
                        ? googleServices.statusEnum
                        : ((typeof google.maps.places !== 'undefined' && google.maps.places.PlacesServiceStatus)
                            ? google.maps.places.PlacesServiceStatus
                            : {});

                    const updateFromCoordinates = function (latValue, lonValue, formatted, resolvedPlaceId)
                    {
                        if (!latValue || !lonValue) return;

                        ltField.val(latValue);
                        lnField.val(lonValue);

                        const latNum = parseFloat(latValue);
                        const lonNum = parseFloat(lonValue);
                        if (!isNaN(latNum) && !isNaN(lonNum))
                        {
                            const position = new google.maps.LatLng(latNum, lonNum);
                            marker.setPosition(position);
                            map.setCenter(position);
                        }

                        const label = formatted || value;
                        dropdown.markSelected(label, {
                            lat: latValue,
                            lon: lonValue,
                            placeId: resolvedPlaceId || placeId
                        });
                    };

                    if (lat && lon)
                    {
                        updateFromCoordinates(lat, lon, data.label || value, placeId);
                        return;
                    }

                    const fallbackGeocode = function ()
                    {
                        geocodeAddress(value, function (result)
                        {
                            if (!result) return;

                            const location = result.geometry && result.geometry.location;
                            if (!location) return;

                            const latFn = typeof location.lat === 'function' ? location.lat : null;
                            const lngFn = typeof location.lng === 'function' ? location.lng : null;
                            const latValue = latFn ? latFn.call(location) : location.lat || '';
                            const lonValue = lngFn ? lngFn.call(location) : location.lng || '';
                            if (!latValue || !lonValue) return;

                            const formatted = result.formatted_address || result.formattedAddress || (result.name ? result.name : value);

                            updateFromCoordinates(latValue, lonValue, formatted, placeId);
                        });
                    };

                    if (placeId && googleServices.places)
                    {
                        const request = {
                            placeId: placeId,
                            fields: ['geometry', 'formatted_address', 'name', 'place_id']
                        };

                        if (typeof google.maps.places.AutocompleteSessionToken === 'function' && googleServices.sessionToken)
                        {
                            request.sessionToken = googleServices.sessionToken;
                        }

                        googleServices.places.getDetails(request, function (result, status)
                        {
                            if (status === statusEnum.OK && result)
                            {
                                const location = result.geometry && result.geometry.location;
                                if (location)
                                {
                                    const latFn = typeof location.lat === 'function' ? location.lat : null;
                                    const lngFn = typeof location.lng === 'function' ? location.lng : null;
                                    const latValue = latFn ? latFn.call(location) : location.lat || '';
                                    const lonValue = lngFn ? lngFn.call(location) : location.lng || '';

                                    if (latValue && lonValue)
                                    {
                                        const formatted = result.formatted_address || result.name || value;
                                        updateFromCoordinates(latValue, lonValue, formatted, result.place_id || placeId);
                                        googleServices.sessionToken = null;
                                        return;
                                    }
                                }
                            }

                            fallbackGeocode();
                        });

                        return;
                    }

                    fallbackGeocode();
                };
            }
        }

        $address.off('change.lsd-google-geocode').on('change.lsd-google-geocode', function ()
        {
            const addressValue = $address.val();
            if (!addressValue || (dropdown && typeof dropdown.isUpdating === 'function' && dropdown.isUpdating())) return;

            geocodeAddress(addressValue, function (result)
            {
                if (!result) return;

                const location = result.geometry && result.geometry.location;
                if (!location) return;

                const latFn = typeof location.lat === 'function' ? location.lat : null;
                const lngFn = typeof location.lng === 'function' ? location.lng : null;
                const latValue = latFn ? latFn.call(location) : location.lat || '';
                const lonValue = lngFn ? lngFn.call(location) : location.lng || '';
                if (!latValue || !lonValue) return;

                ltField.val(latValue);
                lnField.val(lonValue);

                const latNum = parseFloat(latValue);
                const lonNum = parseFloat(lonValue);
                if (!isNaN(latNum) && !isNaN(lonNum))
                {
                    const resolved = new google.maps.LatLng(latNum, lonNum);
                    marker.setPosition(resolved);
                    map.setCenter(resolved);
                }

                dropdown.markSelected(result.formatted_address || result.formattedAddress || addressValue, {
                    lat: latValue,
                    lon: lonValue
                });
            });
        });
    });
    <?php else: ?>
    const leafletInit = setInterval(lsd_wizard_init_leaflet, 300);
    function lsd_wizard_init_leaflet()
    {
        if (!$('#lsd_wizard_location_map').is(':visible')) return;

        clearInterval(leafletInit);

        const accessToken = '<?php echo esc_js($settings['mapbox_access_token'] ?? ''); ?>';
        const map = L.map('lsd_wizard_location_map', { scrollWheelZoom: false }).setView([latitude, longitude], 8);

        if (accessToken)
        {
            L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}',
                {
                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                    maxZoom: 18,
                    id: 'mapbox.streets',
                    accessToken: accessToken
                }).addTo(map);
        }
        else
        {
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    attribution: 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
                    maxZoom: 18
                }).addTo(map);
        }

        const marker = L.marker([latitude, longitude], { draggable: true }).addTo(map);

        applyDropdownSelection = function (data)
        {
            if (!data || !data.lat || !data.lon)
            {
                ltField.val('');
                lnField.val('');
                return;
            }

            const latNum = parseFloat(data.lat);
            const lonNum = parseFloat(data.lon);

            if (isNaN(latNum) || isNaN(lonNum))
            {
                return;
            }

            ltField.val(latNum);
            lnField.val(lonNum);

            marker.setLatLng({ lat: latNum, lng: lonNum });
            map.setView([latNum, lonNum], 8);
        };

        marker.on('dragend', function()
        {
            const pos = marker.getLatLng();
            const latValue = pos.lat;
            const lonValue = pos.lng;

            ltField.val(latValue);
            lnField.val(lonValue);

            reverseGeocode(latValue, lonValue, function(addressText)
            {
                if (addressText) $address.val(addressText);
                else if (!$address.val()) $address.val(locateGpsLabel);

                dropdown.markSelected($address.val(), {lat: latValue, lon: lonValue});
            });
        });

        map.on('click', function(e)
        {
            marker.setLatLng(e.latlng);

            const latValue = e.latlng.lat;
            const lonValue = e.latlng.lng;

            ltField.val(latValue);
            lnField.val(lonValue);

            reverseGeocode(latValue, lonValue, function(addressText)
            {
                if (addressText) $address.val(addressText);
                else if (!$address.val()) $address.val(locateGpsLabel);

                dropdown.markSelected($address.val(), {lat: latValue, lon: lonValue});
            });
        });

        const reverseGeocode = function(lat, lng, callback)
        {
            const latNum = parseFloat(lat);
            const lngNum = parseFloat(lng);

            if (isNaN(latNum) || isNaN(lngNum))
            {
                callback('');
                return;
            }

            $.getJSON('https://nominatim.openstreetmap.org/reverse', {format: 'json', lat: latNum, lon: lngNum})
                .done(function(data)
                {
                    callback((data && data.display_name) ? data.display_name : '');
                })
                .fail(function()
                {
                    callback('');
                });
        };

        attachLocateControl(function(lat, lng, finalize)
        {
            const latNum = parseFloat(lat) || 0;
            const lngNum = parseFloat(lng) || 0;

            marker.setLatLng([latNum, lngNum]);
            map.setView([latNum, lngNum], Math.max(map.getZoom(), 12));

            ltField.val(latNum);
            lnField.val(lngNum);

            reverseGeocode(latNum, lngNum, function(addressText)
            {
                finalize(addressText);
            });
        });

        let wizardOsmRequest = null;

        if ($address.length && typeof window.lsdAddressAutocompleteSources !== 'undefined')
        {
            $address.off('input.lsd-osm-dropdown').on('input.lsd-osm-dropdown', function ()
            {
                if (wizardOsmRequest && typeof wizardOsmRequest.cancel === 'function') wizardOsmRequest.cancel();

                const term = $(this).val();

                if (!term || term.length < 3)
                {
                    dropdown.reset();
                    return;
                }

                dropdown.showLoading();

                const requestTerm = term;
                wizardOsmRequest = window.lsdAddressAutocompleteSources.fetch('openstreetmap', requestTerm, {
                    limit: 5,
                    minLength: 3
                });

                if (!wizardOsmRequest || !wizardOsmRequest.promise) return;

                wizardOsmRequest.promise
                    .done(function(response)
                    {
                        if ($address.val() !== requestTerm) return;

                        const items = response && response.items ? response.items : [];
                        if (!items.length)
                        {
                            dropdown.reset();
                            return;
                        }

                        dropdown.populate(items);
                    })
                    .fail(function()
                    {
                        dropdown.reset();
                    });
            });
        }
    }
    <?php endif; ?>

    // Disable Form Submit on Enter of Address Field
    $address.on('keyup keypress', function(e)
    {
        const keyCode = e.keyCode || e.which;
        if(keyCode === 13)
        {
            e.preventDefault();
            return false;
        }
    });

    $('#lsd_settings_location_save_button').on('click', function (e)
    {
        e.preventDefault();
        const $button = $(this);

        const loading = new ListdomButtonLoader($button);
        loading.start("<?php echo esc_js( esc_html__('Saving', 'listdom') ); ?>");

        const settings = $("#lsd_settings_form").serialize();
        $.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: "action=lsd_save_settings&" + settings,
            success: function ()
            {
                // Loading Styles
                loading.stop();

                setTimeout(function()
                {
                    handleStepNavigation(3);
                }, 1000);

            },
            error: function ()
            {
                loading.stop();
                listdom_toastify("<?php echo esc_js(esc_html__('There was an issue', 'listdom')); ?>", 'lsd-error');
            }
        });
    });
});
</script>
