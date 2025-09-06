<?php
// no direct access
defined('ABSPATH') || die();

// Settings
$settings = LSD_Options::settings();
?>
<div class="lsd-welcome-step-content lsd-util-hide" id="step-1">
    <div class="lsd-welcome-content-header">
        <div class="lsd-admin-section-heading">
            <div class="lsd-admin-title-icon">
                <i class="listdom-icon lsdi-frontend-dashboard"></i>
                <h2 class="lsd-admin-title lsd-m-0"><?php echo esc_html__('Match Listdom To Your Needs', 'listdom'); ?></h2>
            </div>
            <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Disable the features you don\'t need to simplify your website. You will always be able to change them in Listdom → Settings → Advanced.' , 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-welcome-content-wrapper">
        <form id="lsd_features_settings_form">
            <div class="lsd-features-settings">
                <div class="lsd-form-row lsd-flex-align-items-center lsd-m-0">
                    <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Map & Address', 'listdom'),
                        'for' => 'lsd_component_map',
                    ]); ?></div>
                    <div class="lsd-col-7">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_component_map',
                            'name' => 'lsd[components][map]',
                            'value' => $settings['components']['map'] ?? '1',
                        ]); ?>
                    </div>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Useful for directories with location-based listings.', 'listdom'); ?></p>
                </div>
                <div class="lsd-form-row lsd-flex-align-items-center lsd-m-0">
                    <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Pricing', 'listdom'),
                        'for' => 'lsd_component_pricing',
                    ]); ?></div>
                    <div class="lsd-col-7">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_component_pricing',
                            'name' => 'lsd[components][pricing]',
                            'value' => $settings['components']['pricing'] ?? '1',
                        ]); ?>
                    </div>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Useful for directories that have a specified value per item e.g. per night, ticket, month, visit, etc.', 'listdom'); ?></p>

                </div>
                <div class="lsd-form-row lsd-flex-align-items-center lsd-m-0">
                    <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Work Hours', 'listdom'),
                        'for' => 'lsd_component_work_hours',
                    ]); ?></div>
                    <div class="lsd-col-7">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_component_work_hours',
                            'name' => 'lsd[components][work_hours]',
                            'value' => $settings['components']['work_hours'] ?? '1',
                        ]); ?>
                    </div>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Useful for directories with location-based listings.', 'listdom'); ?></p>
                </div>
                <div class="lsd-form-row lsd-flex-align-items-center lsd-m-0">
                    <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Visibility', 'listdom'),
                        'for' => 'lsd_component_visibility',
                    ]); ?></div>
                    <div class="lsd-col-7">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_component_visibility',
                            'name' => 'lsd[components][visibility]',
                            'value' => $settings['components']['visibility'] ?? '1',
                        ]); ?>
                    </div>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Useful for directories with location-based listings.', 'listdom'); ?></p>
                </div>
                <div class="lsd-form-row lsd-flex-align-items-center lsd-m-0">
                    <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Social Networks', 'listdom'),
                        'for' => 'lsd_component_socials',
                    ]); ?></div>
                    <div class="lsd-col-7">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_component_socials',
                            'name' => 'lsd[components][socials]',
                            'value' => $settings['components']['socials'] ?? '1',
                        ]); ?>
                    </div>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Useful for directories with location-based listings.', 'listdom'); ?></p>
                </div>
            </div>
            <?php LSD_Form::nonce('lsd_settings_form'); ?>
        </form>
    </div>

    <div class="lsd-welcome-button-wrapper">
        <a class="lsd-text-button" href="<?php echo admin_url('/admin.php?page=listdom'); ?>"><?php echo esc_html__('Not Now, Exit to Dashboard', 'listdom'); ?></a>
        <button class="lsd-step-link lsd-primary-button" id="lsd_settings_features_save_button">
            <?php echo esc_html__('Next', 'listdom'); ?>
            <i class="listdom-icon lsdi-right-arrow"></i>
        </button>
    </div>
</div>

<script>
function lsdToggleLocationStep()
{
    const enabled = $map_component.is(':checked');
    const $locationStep = jQuery('.lsd-stepper-tabs .step').eq(1).parent();
    const $lineBefore = $locationStep.prev('.stepper-line');

    if (enabled)
    {
        $locationStep.removeClass('lsd-util-hide');
        $lineBefore.removeClass('lsd-util-hide');
    }
    else
    {
        $locationStep.addClass('lsd-util-hide');
        $lineBefore.addClass('lsd-util-hide');
        jQuery('#step-2').addClass('lsd-util-hide');
    }

    jQuery('.lsd-stepper-tabs .step:visible').each(function(index)
    {
        jQuery(this).text(index + 1);
    });
}

const $map_component = jQuery('#lsd_component_map');
$map_component.on('change', lsdToggleLocationStep).trigger('change');

jQuery('#lsd_settings_features_save_button').on('click', function (event)
{
    event.preventDefault();
    const $button = jQuery(this);

    const loading = (new ListdomButtonLoader($button));
    loading.start("<?php echo esc_js( __('Saving', 'listdom') ); ?>");

    const settings = jQuery("#lsd_features_settings_form").serialize();
    jQuery.ajax(
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
                const nextStep = jQuery('#lsd_component_map').is(':checked') ? 2 : 3;
                handleStepNavigation(nextStep);
            }, 1000);
        },
        error: function ()
        {
            loading.stop();
            listdom_toastify("<?php echo esc_js(__('There was an issue', 'listdom')); ?>", 'lsd-error');
        }
    });
});
</script>
