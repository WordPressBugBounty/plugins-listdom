<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Welcome $this */
$settings = LSD_Options::settings();
?>
<div class="lsd-welcome-step-content lsd-util-hide" id="step-1">
    <h2 class="text-xl"><?php echo esc_html__('Location Setting', 'listdom'); ?></h2>
    <div class="lsd-flex lsd-flex-row">
        <h3><?php esc_html_e('Set the default location:', 'listdom'); ?></h3>
    </div>
    <p><?php echo esc_html__('Not sure? No worries. You can modify the settings later.', 'listdom'); ?></p>
    <form id="lsd_settings_form">
        <div class="lsd-location-settings">
            <div class="lsd-form-row">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Default Latitude', 'listdom'),
                    'for' => 'lsd_settings_map_backend_lt',
                ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_settings_map_backend_lt',
                        'name' => 'lsd[map_backend_lt]',
                        'value' => $settings['map_backend_lt'] ?? '',
                        'placeholder' => esc_html__("It's for Google Maps in Add/Edit Map Objects menu.", 'listdom'),
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Default Longitude', 'listdom'),
                    'for' => 'lsd_settings_map_backend_ln',
                ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_settings_map_backend_ln',
                        'name' => 'lsd[map_backend_ln]',
                        'value' => $settings['map_backend_ln'] ?? '',
                        'placeholder' => esc_html__("It's for Google Maps in Add/Edit Listing menu.", 'listdom')
                    ]); ?>
                </div>
            </div>
            <?php LSD_Form::nonce('lsd_settings_form'); ?>
        </div>
        <div class="lsd-map-settings">
            <h3 class="lsd-my-4"><?php esc_html_e('Choose the Map provider:', 'listdom'); ?></h3>
            <div class="lsd-maps">
                <div class="lsd-col-6 lsd-map-item">
                    <h3 class="lsd-my-3"><?php echo esc_html__('Open Street', 'listdom'); ?></h3>
                    <div class="lsd-image-wrapper">
                        <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/check-green.svg')); ?>" alt="Selected Icon" class="lsd-icon <?php echo $settings['map_provider'] == 'leaflet' ? '' : 'lsd-util-hide'; ?>"">
                        <img class="<?php echo $settings['map_provider'] == 'leaflet' ? 'lsd-selected' : ''; ?>" data-map="leaflet" width="100%" src="<?php echo esc_url_raw($this->lsd_asset_url('img/open-street.png')); ?>" alt="">
                    </div>
                </div>
                <div class="lsd-col-6 lsd-map-item">
                    <h3 class="lsd-my-3"><?php echo esc_html__('Google map', 'listdom'); ?></h3>
                    <div class="lsd-image-wrapper">
                        <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/check-green.svg')); ?>" alt="Selected Icon" class="lsd-icon <?php echo $settings['map_provider'] == 'googlemap' ? '' : 'lsd-util-hide'; ?>">
                        <img class="<?php echo $settings['map_provider'] == 'googlemap' ? 'lsd-selected' : ''; ?>" data-map="googlemap" width="100%" src="<?php echo esc_url_raw($this->lsd_asset_url('img/google-map.png')); ?>" alt="">
                    </div>
                </div>
            </div>
            <div class="lsd-alert-message"></div>

            <div class="lsd-form-row">
                <div class="lsd-col-4">
                    <?php echo LSD_Form::providers([
                        'id' => 'lsd_settings_map_provider',
                        'name' => 'lsd[map_provider]',
                        'value' => $settings['map_provider'] ?? 'leaflet',
                        'class' => 'lsd-util-hide lsd-map-provider-toggle',
                    ]); ?>
                </div>
            </div>
            <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
                <div class="lsd-form-row">
                    <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'title' => esc_html__('Google Maps API key', 'listdom'),
                        'for' => 'lsd_settings_googlemaps_api_key',
                    ]); ?></div>
                    <div class="lsd-col-9">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_settings_googlemaps_api_key',
                            'name' => 'lsd[googlemaps_api_key]',
                            'value' => $settings['googlemaps_api_key'] ?? ''
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>

    </form>

    <div class="lsd-skip-wizard">
        <button class="lsd-skip-step"><?php echo esc_html__('Skip This step', 'listdom'); ?></button>
        <button class="lsd-step-link button button-hero button-primary" id="lsd_settings_location_save_button">
            <?php echo esc_html__('Continue', 'listdom'); ?>
            <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
        </button>
    </div>
</div>

<script>
jQuery(document).ready(function ($)
{
    $('.lsd-maps img').on('click', function ()
    {
        const $alert = $('.lsd-alert-message');
        $alert.html('');

        const mapProvider = $(this).data('map');
        const isGooglemap = mapProvider === 'googlemap';

        const selectMap = () => {
            $('.lsd-maps img').removeClass('lsd-selected');
            $('.lsd-icon').addClass('lsd-util-hide');
            $(this).addClass('lsd-selected');
            $(this).siblings('.lsd-icon').removeClass('lsd-util-hide');
            $('#lsd_settings_map_provider').val(mapProvider).trigger('change');
        };

        <?php if (LSD_Base::isPro()) : ?>
            selectMap();
        <?php else: ?>
            if (isGooglemap) selectMap();
            else $alert.html(listdom_alertify('<?php echo esc_js(__('The Open Street Map is available in pro addon only.', 'listdom')); ?>', 'lsd-warning'));
        <?php endif; ?>
    });
});

jQuery('#lsd_settings_location_save_button').on('click', function (e)
{
    e.preventDefault();
    const $welcomeWizard = jQuery(".lsd-welcome-wizard");

    // Loading Styles
    $welcomeWizard.addClass('lsd-loading-wrapper');
    jQuery('head').append('<style>.lsd-loading-wrapper:after { content: "\\f279" !important; }</style>');

    const settings = jQuery("#lsd_settings_form").serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_settings&" + settings,
        success: function ()
        {
            // Loading Styles
            $welcomeWizard.removeClass('lsd-loading-wrapper');
            handleStepNavigation(2);
        },
        error: function ()
        {
            // Loading Styles
            $welcomeWizard.removeClass('lsd-loading-wrapper');
        }
    });
});

jQuery(document).ready(function ($)
{
    function handleStepNavigation(stepNumber = 1)
    {
        // Update the steps, show content, and update the URL
        updateSteps(stepNumber);
        showContent(stepNumber);
        updateURL(stepNumber);
        activeStep = stepNumber;

        if (stepNumber === 4) $('.lsd-stepper-tabs .step').eq(stepNumber - 1).addClass('completed').html('');
    }

    // Function to get query parameters from the URL
    const getQueryParam = param => new URLSearchParams(window.location.search).get(param);

    // Get the step from the URL or default to '1'
    let activeStep = parseInt(getQueryParam('step')) || 1;

    // Initialize the active step manually based on the URL
    handleStepNavigation(activeStep);

    // Handle clicks on the "Let's start" button and skip button manually
    $('.lsd-skip-wizard .lsd-skip-step').on('click', function (e)
    {
        e.preventDefault();
        if (activeStep < 4) handleStepNavigation(activeStep + 1);
    });

    $('.lsd-skip-wizard .lsd-prev-step-link').on('click', function (e)
    {
        e.preventDefault();
        handleStepNavigation(activeStep - 1);
    });

    // Update steps' classes and content
    function updateSteps(stepNumber)
    {
        const $step = $('.lsd-stepper-tabs .step');

        $step.removeClass('active completed');
        $step.slice(0, stepNumber - 1).addClass('completed').html('');
        $step.eq(stepNumber - 1).addClass('active');
    }

    // Show the corresponding content for the step
    function showContent(stepNumber)
    {
        $('.lsd-welcome-step-content').addClass('lsd-util-hide');
        $('#step-' + stepNumber).removeClass('lsd-util-hide');
    }

    // Update the URL without reloading the page
    function updateURL(stepNumber)
    {
        const newUrl = `${window.location.protocol}//${window.location.host}${window.location.pathname}?page=listdom-welcome&tab=setup&step=${stepNumber}`;
        window.history.pushState({path: newUrl}, '', newUrl);
    }

    // Expose the function globally if you want to call it from other scripts
    window.handleStepNavigation = handleStepNavigation;
});
</script>
