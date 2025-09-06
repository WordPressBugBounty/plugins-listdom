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
                        <?php echo LSD_Form::text([
                            'class' => 'lsd-admin-input',
                            'id' => 'lsd_address',
                            'placeholder' => esc_html__('Type at least 3 characters of the location ...', 'listdom'),
                        ]); ?>
                    </div>
                </div>
                <div class="lsd-form-row lsd-util-hide">
                    <div class="lsd-col-7">
                        <?php echo LSD_Form::hidden([
                            'id' => 'lsd_settings_map_backend_lt',
                            'name' => 'lsd[map_backend_lt]',
                            'value' => $settings['map_backend_lt'] ?? '',
                            'placeholder' => esc_html__("It's for Google Maps in Add/Edit Map Objects menu.", 'listdom'),
                        ]); ?>
                    </div>
                </div>
                <div class="lsd-form-row lsd-util-hide">
                    <div class="lsd-col-7">
                        <?php echo LSD_Form::hidden([
                            'id' => 'lsd_settings_map_backend_ln',
                            'name' => 'lsd[map_backend_ln]',
                            'value' => $settings['map_backend_ln'] ?? '',
                            'placeholder' => esc_html__("It's for Google Maps in Add/Edit Listing menu.", 'listdom')
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

        google.maps.event.addListener(map, 'click', function(e)
        {
            marker.setPosition(e.latLng);
            ltField.val(e.latLng.lat());
            lnField.val(e.latLng.lng());
        });

        google.maps.event.addListener(marker, 'dragend', function(e)
        {
            ltField.val(e.latLng.lat());
            lnField.val(e.latLng.lng());
        });

        // Initialize Google Places Autocomplete if Places library is available
        if (typeof google.maps.places !== 'undefined')
        {
            const input = document.getElementById('lsd_address');
            const autocomplete = new google.maps.places.Autocomplete(input);

            autocomplete.addListener('place_changed', function ()
            {
                const place = autocomplete.getPlace();
                if (place.geometry)
                {
                    ltField.val(place.geometry.location.lat());
                    lnField.val(place.geometry.location.lng());

                    marker.setPosition(place.geometry.location);
                    map.setCenter(place.geometry.location);
                }
            });
        }
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

        marker.on('dragend', function()
        {
            const pos = marker.getLatLng();
            ltField.val(pos.lat);
            lnField.val(pos.lng);
        });

        map.on('click', function(e)
        {
            marker.setLatLng(e.latlng);
            ltField.val(e.latlng.lat);
            lnField.val(e.latlng.lng);
        });

        // OSM Autocomplete
        $address.autocomplete(
        {
            source: function (request, response)
            {
                $.getJSON('https://nominatim.openstreetmap.org/search', {format: 'json', limit: 5, q: request.term}, function (data)
                {
                    response($.map(data, function (item)
                    {
                        return {
                            label: item.display_name,
                            value: item.display_name,
                            lat: item.lat,
                            lon: item.lon
                        };
                    }));
                });
            },
            minLength: 3,
            select: function (event, ui)
            {
                marker.setLatLng({
                    lat: ui.item.lat,
                    lng: ui.item.lon
                });

                map.setView([ui.item.lat, ui.item.lon], 8);

                ltField.val(ui.item.lat);
                lnField.val(ui.item.lon);
            }
        });
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

        const loading = (new ListdomButtonLoader($button));
        loading.start("<?php echo esc_js( __('Saving', 'listdom') ); ?>");

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
                listdom_toastify("<?php echo esc_js(__('There was an issue', 'listdom')); ?>", 'lsd-error');
            }
        });
    });
});
</script>
