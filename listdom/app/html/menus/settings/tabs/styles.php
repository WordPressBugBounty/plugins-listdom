<?php
// no direct access
defined('ABSPATH') || die();

$styles = LSD_Options::styles();
?>
<div class="lsd-settings-wrap">
    <h3><?php esc_html_e('Custom Styles', 'listdom'); ?></h3>
    <form id="lsd_settings_form">
        <div class="lsd-form-row">
            <div class="lsd-col-12">
                <?php echo LSD_Form::textarea([
                    'id' => 'lsd_settings_custom_styles',
                    'name' => 'lsd[CSS]',
                    'value' => $styles['CSS'] ?? ''
                ]); ?>
            </div>
        </div>
        <div class="lsd-spacer-10"></div>
        <div class="lsd-form-row">
			<div class="lsd-col-12 lsd-flex lsd-gap-3">
                <div>
                    <?php LSD_Form::nonce('lsd_settings_form'); ?>
                    <?php echo LSD_Form::submit([
                        'label' => esc_html__('Save', 'listdom'),
                        'id' => 'lsd_settings_save_button',
                        'class' => 'button button-hero button-primary',
                    ]); ?>
                </div>
                <div>
                    <p class="lsd-util-hide lsd-settings-success-message lsd-alert lsd-success lsd-m-0"><?php esc_html_e('Options saved successfully.', 'listdom'); ?></p>
                    <p class="lsd-util-hide lsd-settings-error-message lsd-alert lsd-error lsd-m-0"><?php esc_html_e('Error: Unable to save options', 'listdom'); ?></p>
                </div>
			</div>
        </div>
    </form>
</div>
<script>
jQuery('#lsd_settings_form').on('submit', function(e)
{
    e.preventDefault();

    // Elements
    const $button = jQuery("#lsd_settings_save_button");
    const $success = jQuery(".lsd-settings-success-message");
    const $error = jQuery(".lsd-settings-error-message");

    // Loading Styles
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    const settings = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_styles&" + settings,
        success: function()
        {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($success, 2000);
        },
        error: function()
        {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($error, 2000);
        }
    });
});
</script>
