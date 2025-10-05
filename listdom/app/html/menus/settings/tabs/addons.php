<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */
[, $default] = $this->get_addons_default();
?>
<div class="lsd-settings-wrap">
    <form id="lsd_addons_form">

        <?php do_action('lsd_addon_form', $default); ?>

        <div class="lsd-spacer-30"></div>
        <div class="lsd-form-row lsd-settings-submit-wrapper">
			<div class="lsd-col-12 lsd-flex lsd-gap-3 lsd-flex-content-end">
				<?php LSD_Form::nonce('lsd_addons_form'); ?>
                <button type="submit" id="lsd_addons_save_button" class="lsd-primary-button">
                    <?php esc_html_e('Save The Changes', 'listdom'); ?>
                    <i class='lsdi lsdi-checkmark-circle'></i>
                </button>
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
    const $tab = jQuery('.lsd-nav-tab-active');

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Saving', 'listdom') ); ?>");

    const addons = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_addons&" + addons,
        success: function()
        {
            $tab.attr('data-saved', 'true');

            listdom_toastify("<?php echo esc_js(esc_html__('Options saved successfully.', 'listdom')); ?>", 'lsd-success');

            // Unloading
            loading.stop();
        },
        error: function()
        {
            $tab.attr('data-saved', 'false');

            listdom_toastify("<?php echo esc_js(esc_html__('Error: Unable to save options.', 'listdom')); ?>", 'lsd-error');

            // Unloading
            loading.stop();
        }
    });
});
</script>
