<?php
// no direct access
defined('ABSPATH') || die();
?>
<div class="lsd-settings-wrap">
    <form id="lsd_addons_form">

        <?php do_action('lsd_addon_form'); ?>

        <div class="lsd-spacer-10"></div>
        <div class="lsd-form-row lsd-settings-submit-wrapper">
			<div class="lsd-col-12 lsd-flex lsd-gap-3 lsd-flex-content-end">
				<?php LSD_Form::nonce('lsd_addons_form'); ?>
                <button type="submit" id="lsd_addons_save_button" class="lsd-primary-button">
                    <?php esc_html_e('Save The Changes', 'listdom'); ?>
                    <i class='lsdi lsdi-checkmark-circle'></i>
                </button>
                <div>
                    <p class="lsd-util-hide lsd-settings-success-message lsd-alert lsd-success lsd-m-0"><?php esc_html_e('Options saved successfully.', 'listdom'); ?></p>
                    <p class="lsd-util-hide lsd-settings-error-message lsd-alert lsd-error lsd-m-0"><?php esc_html_e('Error: Unable to save options.', 'listdom'); ?></p>
                </div>
			</div>
        </div>
    </form>
</div>
<script>
jQuery('#lsd_addons_form').on('submit', function(e)
{
    e.preventDefault();

    // Elements
    const $button = jQuery("#lsd_addons_save_button");
    const $success = jQuery(".lsd-settings-success-message");
    const $error = jQuery(".lsd-settings-error-message");
    const $tab = jQuery('.nav-tab-active');

    // Loading Styles
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');
    $success.addClass('lsd-util-hide');
    $error.addClass('lsd-util-hide');

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    const addons = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_addons&" + addons,
        success: function()
        {
            $tab.attr('data-saved', 'true');

            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save The Changes', 'listdom')); ?><i class='lsdi lsdi-checkmark-circle'></i>");
            $success.removeClass('lsd-util-hide');

            // Unloading
            loading.stop($success, 2000);
        },
        error: function()
        {
            $tab.attr('data-saved', 'false');

            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save The Changes', 'listdom')); ?><i class='lsdi lsdi-checkmark-circle'></i>");
            $error.removeClass('lsd-util-hide');

            // Unloading
            loading.stop($error, 2000);
        }
    });
});
</script>
