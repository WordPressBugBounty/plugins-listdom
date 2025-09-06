<?php
// no direct access
defined('ABSPATH') || die();

// Settings
$settings = LSD_Options::settings();
?>
<div class="lsd-welcome-step-content lsd-util-hide" id="step-4">
    <div class="lsd-welcome-content-header">
        <div class="lsd-admin-section-heading">
            <div class="lsd-admin-title-icon">
                <i class="listdom-icon lsdi-add-plus"></i>
                <h2 class="lsd-admin-title lsd-m-0"><?php echo esc_html__('Stay Updated & Improve Listdom', 'listdom'); ?></h2>
            </div>
            <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Add your email for updates and optionally share anonymous usage data to help us improve.' , 'listdom'); ?></p>
        </div>
    </div>

    <div class="lsd-welcome-content-wrapper">
        <form id="lsd_collect_settings_form">
            <div class="lsd-collect-settings">
                <div class="lsd-form-row lsd-m-0">
                    <p class="lsd-admin-description-tiny lsd-mb-2 lsd-mt-0"><?php esc_html_e('Enter your email to get the latest features and updates.', 'listdom');?></p>
                    <div class="lsd-col-9 lsd-p-0">
                        <?php echo LSD_Form::email([
                            'class' => 'lsd-admin-input',
                            'id' => 'lsd_subscription_email',
                            'placeholder' => esc_html__("Enter your email here", 'listdom'),
                        ]); ?>
                    </div>
                    <div class="lsd-col-3">
                        <button id="lsd_submit_newsletter" type="button" class="lsd-secondary-button"><?php esc_html_e('Submit', 'listdom'); ?><i class="listdom-icon lsdi-right-arrow"></i></button>
                    </div>
                </div>

                <div class="lsd-form-row lsd-flex-align-items-center lsd-m-0">
                    <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Help Improve Listdom', 'listdom'),
                        'for' => 'lsd_help_improve_listdom',
                    ]); ?></div>
                    <div class="lsd-col-7">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_help_improve_listdom',
                            'name' => 'lsd[help_improve_listdom]',
                            'value' => $settings['help_improve_listdom'] ?? '1',
                        ]); ?>
                    </div>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Send anonymous usage data to help improve Listdom.', 'listdom'); ?></p>
                </div>
            </div>
            <?php LSD_Form::nonce('lsd_settings_form'); ?>
        </form>
    </div>

    <div class="lsd-welcome-button-wrapper">
        <button class="lsd-skip-step lsd-text-button"><?php echo esc_html__('Skip', 'listdom'); ?></button>
        <button type="submit" class="lsd-step-link lsd-primary-button" id="lsd_settings_collect_data_button">
            <?php echo esc_html__('Continue', 'listdom'); ?>
            <i class="listdom-icon lsdi-right-arrow"></i>
        </button>
    </div>
</div>
<script>
jQuery('#lsd_settings_collect_data_button').on('click', function (event)
{
    event.preventDefault();
    const $button = jQuery(this);

    const loading = (new ListdomButtonLoader($button));
    loading.start("<?php echo esc_js( __('Saving', 'listdom') ); ?>");

    const settings = jQuery("#lsd_collect_settings_form").serialize();
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
                handleStepNavigation(5);
            }, 1000);

        },
        error: function ()
        {
            loading.stop();
        }
    });
});

jQuery('#lsd_submit_newsletter').on('click', function (event)
{
    event.preventDefault();
    const $button = jQuery(this);
    const email = jQuery('#lsd_subscription_email').val();

    if (!email)
    {
        listdom_toastify("<?php echo esc_js(__('Please enter a valid email address.', 'listdom')); ?>", 'lsd-error');
        return;
    }

    const loading = (new ListdomButtonLoader($button));
    loading.start("<?php echo esc_js(__('Submitting', 'listdom')); ?>");

    jQuery.ajax(
    {
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'lsd_submit_newsletter',
            email: email
        },
        dataType: 'json',
        success: function(response)
        {
            loading.stop();

            if(response.success)
            {
                if (response.success) jQuery('#lsd_subscription_email').val('');
                listdom_toastify(response.message, 'lsd-success');
            }
            else
            {
                listdom_toastify(response.message, 'lsd-error');
            }
        },
        error: function ()
        {
            loading.stop();
            listdom_toastify("<?php echo esc_js(__('There was an issue with the subscription, try again later.', 'listdom')); ?>", 'lsd-error');
        }
    });
});
</script>
