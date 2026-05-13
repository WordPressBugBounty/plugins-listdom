<?php
// no direct access
defined('ABSPATH') || die();
?>
<script>
jQuery(document).on('submit', '.lsd-addons-form', function(e)
{
    e.preventDefault();

    const $form = jQuery(this);
    const $button = $form.find('.lsd-addons-save-button');
    const $tab = jQuery('.lsd-nav-tab-active');

    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js(__('Saving', 'listdom')); ?>");

    const addons = $form.serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_addons&" + addons,
        success: function()
        {
            $tab.attr('data-saved', 'true');

            listdom_toastify("<?php echo esc_js(__('Options saved successfully.', 'listdom')); ?>", 'lsd-success');
            loading.stop();
        },
        error: function()
        {
            $tab.attr('data-saved', 'false');

            listdom_toastify("<?php echo esc_js(__('Error: Unable to save options.', 'listdom')); ?>", 'lsd-error');
            loading.stop();
        }
    });
});
</script>
