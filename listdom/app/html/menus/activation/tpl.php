<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Activation $this */
/** @var array $products */
?>
<div class="wrap lsd-wrap lsd-licenses-wrap">
    <?php LSD_Menus::header(esc_html__('Licenses', 'listdom')); ?>

    <div class="lsd-admin-main-wrapper">
        <?php echo lsd_ads('licenses-top'); ?>
        <div class="lsd-activation-wrap lsd-admin-wrapper">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php echo esc_html__('All Licenses', 'listdom'); ?></h3>
            <div class="lsd-licenses">
                <?php if (!count($products)): ?>
                    <h3 class="lsd-mt-0"><?php esc_html_e('No Premium Add-ons Activated Yet', 'listdom'); ?></h3>
                    <div class="lsd-alert lsd-info lsd-my-0"><?php esc_html_e("This is where you'll activate your paid Listdom add-ons using their license keys. Activating an add-on unlocks its premium features and extended functionality. Once you install a paid add-on, you can register its license here.", 'listdom'); ?></div>
                <?php else: ?>
                    <?php foreach ($products as $key => $product) $this->include_html_file('menus/activation/addon.php', ['parameters' => ['key' => $key, 'product' => $product]]); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php echo lsd_ads('licenses-bottom'); ?>
    </div>
</div>
<script>
function lsdBindLicenseForms()
{
    // Activation Form
    jQuery('.lsd-activation-form').off('submit').on('submit', function(event)
    {
        event.preventDefault();

        // Form
        const $form = jQuery(this);

        // Product Key
        const key = $form.data('key');

        // DOM Elements
        const $alert = jQuery(`#${key}_activation_alert`);
        const $button = jQuery(`#${key}_activation_button`);
        const $badge = jQuery('.lsd-wrap .update-plugins .update-count');

        // Remove Existing Alert
        $alert.removeClass('lsd-error lsd-success lsd-alert').html('');

        // Add loading Class to the button
        $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

        const activation = $form.serialize();
        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: "action=lsd_activation&" + activation,
            dataType: "json",
            success: function(response)
            {
                if (response.success)
                {
                    const $panel = jQuery(`#lsd-license-card-${key}`);
                    $panel.replaceWith(response.content);

                    setTimeout(function () {
                        lsdBindLicenseForms();
                    }, 3000)

                    // New Badge
                    const new_badge = parseInt($badge.html()) - 1;

                    // Update Badges
                    if(new_badge > 0) jQuery('.update-plugins .update-count').html(new_badge);
                    else jQuery('.update-plugins').remove();
                }
                else
                {
                    $alert.removeClass('lsd-error lsd-success lsd-alert').addClass('lsd-alert lsd-error').html(response.message);
                }

                // Remove loading Class from the button
                $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Activate', 'listdom')); ?>");
            },
            error: function()
            {
                // Remove loading Class from the button
                $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Activate', 'listdom')); ?>");
            }
        });
    });

    // Deactivation Form
    jQuery('.lsd-deactivation-form').off('submit').on('submit', function(event)
    {
        event.preventDefault();

        // Form
        const $form = jQuery(this);

        // Product Key
        const key = $form.data('key');

        // DOM Elements
        const $alert = jQuery(`#${key}_deactivation_alert`);
        const $button = jQuery(`#${key}_deactivation_button`);
        const $confirm = jQuery(`#${key}_deactivation_confirm`);

        // Not confirmed
        if($confirm.val().toLowerCase() !== 'deactivate') return;

        // Remove Existing Alert
        $alert.removeClass('lsd-error lsd-success lsd-alert').html('');

        // Add loading Class to the button
        $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

        const deactivation = $form.serialize();
        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: "action=lsd_deactivation&" + deactivation,
            dataType: "json",
            success: function(response)
            {
                if(response.success)
                {
                    const $panel = jQuery(`#lsd-license-card-${key}`);
                    $panel.replaceWith(response.content);

                    setTimeout(function () {
                        lsdBindLicenseForms();
                    }, 3000)
                }
                else
                {
                    $alert.removeClass('lsd-error lsd-success lsd-alert').addClass('lsd-alert lsd-error').html(response.message);
                }

                // Remove loading Class from the button
                $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Deactivate', 'listdom')); ?>");
            },
            error: function()
            {
                // Remove loading Class from the button
                $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Deactivate', 'listdom')); ?>");
            }
        });
    });
}

lsdBindLicenseForms();
</script>
