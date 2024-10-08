<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Activation $this */
/** @var array $products */
?>
<div class="lsd-activation-wrap">
    <?php foreach($products as $key => $product): ?>
    <?php
        $licensing = $product['licensing'] ?? null;
        if(!$licensing) continue;

        $basename = $licensing->getBasename();
        $prefix = $licensing->getPrefix();

        $license_key = $licensing->getLicenseKey();

        $valid = LSD_Licensing::isValid(
            $basename,
            $prefix
        );

        $trial = $valid === 2;
        $grace = $valid === 3;
    ?>
    <div class="lsd-accordion-title <?php echo $valid === 1 ? 'lsd-activation-valid' : ($valid === 3 ? 'lsd-activation-grace' : ''); ?>">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php echo esc_html($product['name']); ?><i class="lsd-icon fa fa-check lsd-ml-3"></i><i class="lsd-icon fa fa-window-close lsd-ml-3"></i></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-activation-form-group lsd-accordion-panel <?php echo $valid === 1 ? 'lsd-activation-valid' : ''; ?>">
        <?php if($valid !== 1): ?>
            <div class="lsd-form-row lsd-activation-guide">
                <div class="lsd-col-12">
                    <?php
                    if(trim($license_key) && $valid === 0)
                    {
                        echo $this->alert(
                            sprintf(
                                esc_html__("It appears that your license key has expired or is invalid. To continue using the %s addon, you will need to renew your existing license or obtain a new one. You can easily renew your license or get a new one from the %s website.", 'listdom'),
                                '<strong>'.$product['name'].'</strong>',
                                '<a href="'.$this->getWebiliaShopURL().'" target="_blank"><strong>Webilia</strong></a>'
                            ),
                            'error'
                        );
                    }
                    elseif($valid === 0)
                    {
                        echo $this->alert(
                            sprintf(
                                esc_html__("To use %s addon you need to activate it first. If you don't have a valid license key or yours has expired, you can obtain one from the %s website.", 'listdom'),
                                '<strong>'.$product['name'].'</strong>',
                                '<a href="'.$this->getWebiliaShopURL().'" target="_blank"><strong>Webilia</strong></a>'
                            ),
                            'warning'
                        );
                    }
                    elseif($grace)
                    {
                        echo $this->alert(
                            sprintf(
                                esc_html__('There seems to be an issue verifying your license, which may be due to a connection problem between our server and yours, or because your license has expired. You are now in a 7-day grace period. If your license is expired, please renew or activate your license within the next %s days to avoid any disruption in using %s. If you believe this is an error, kindly check your server connection or contact Webilia support for assistance.', 'listdom'),
                                '<strong style="color: red;">'.esc_html(LSD_Licensing::remainingGracePeriod($prefix)).'</strong>',
                                '<strong>'.esc_html($product['name']).'</strong>'
                            ),
                            'warning'
                        );
                    }
                    ?>
                </div>
            </div>
            <form class="lsd-activation-form" data-key="<?php echo esc_attr($key); ?>">
                <div class="lsd-form-row lsd-mt-0">
                    <div class="lsd-col-1"><?php echo LSD_Form::label([
                        'title' => esc_html__('License Key', 'listdom'),
                        'for' => $key.'_license_key',
                    ]); ?></div>
                    <div class="lsd-col-6">
                        <?php echo LSD_Form::text([
                            'id' => $key.'_license_key',
                            'name' => 'license_key',
                            'value' => $license_key
                        ]); ?>
                        <p class="description"><?php esc_html_e("License Key / Purchase Code is required for functionality, auto update, and customer service!", 'listdom'); ?></p>
                    </div>
                    <div class="lsd-col-3 lsd-text-left">
                        <?php echo LSD_Form::hidden([
                            'name' => 'key',
                            'value' => $key
                        ]); ?>
                        <?php echo LSD_Form::hidden([
                            'name' => 'basename',
                            'value' => $licensing->getBasename()
                        ]); ?>
                        <?php LSD_Form::nonce($key.'_activation_form'); ?>
                        <?php echo LSD_Form::submit([
                            'label' => esc_html__('Activate', 'listdom'),
                            'id' => $key.'_activation_button'
                        ]); ?>
                        <a class="button button-secondary" href="<?php echo esc_url_raw($this->getWebiliaShopUrl()); ?>" target="_blank"><?php esc_html_e('Get License Key', 'listdom'); ?></a>
                    </div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-12"><div id="<?php echo esc_attr($key); ?>_activation_alert"></div></div>
                </div>
            </form>
            <?php if($trial): ?>
            <div class="lsd-form-row lsd-mb-0">
                <div class="lsd-col-12">
                    <p class="lsd-mb-0"><?php echo sprintf(
                        esc_html__('Please activate your license promptly. You have less than %1$s days remaining to activate %2$s; after that, %2$s will no longer be operational.', 'listdom'),
                        '<strong style="color: red;">'.esc_html(LSD_Licensing::remainingTrialPeriod($prefix)).'</strong>',
                        '<strong>'.esc_html($product['name']).'</strong>'
                    ); ?></p>
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div>
                <h3 class="lsd-mt-0 lsd-mb-2"><?php echo sprintf(esc_html__('License Key: %s', 'listdom'), '<code>'.$licensing->getLicenseKey().'</code>'); ?></h3>
                <p class="description lsd-mb-0"><?php esc_html_e("This installation is activated so you will receive automatic updates on your website!", 'listdom'); ?></p>
            </div>
            <div class="lsd-flex lsd-flex-row lsd-mt-3 lsd-gap-3">
                <div>
                    <a class="button button-secondary" href="<?php echo esc_url_raw($this->getManageLicensesURL()); ?>" target="_blank"><?php esc_html_e('Manage Your Licenses', 'listdom'); ?></a>
                </div>
                <form class="lsd-deactivation-form" data-key="<?php echo esc_attr($key); ?>">
                    <div class="lsd-form-row lsd-deactivation-wrapper">
                        <div class="lsd-col-12 lsd-flex lsd-flex-row lsd-gap-3">
                            <div>
                                <?php echo LSD_Form::text([
                                    'name' => 'confirmation',
                                    'id' => $key.'_deactivation_confirm',
                                    'placeholder' => esc_attr__('Type deactivate here to confirm ...', 'listdom'),
                                ]); ?>
                            </div>
                            <div>
                                <?php echo LSD_Form::hidden([
                                    'name' => 'license_key',
                                    'value' => $licensing->getLicenseKey()
                                ]); ?>
                                <?php echo LSD_Form::hidden([
                                    'name' => 'key',
                                    'value' => $key
                                ]); ?>
                                <?php echo LSD_Form::hidden([
                                    'name' => 'basename',
                                    'value' => $licensing->getBasename()
                                ]); ?>
                                <?php LSD_Form::nonce($key.'_deactivation_form'); ?>
                                <?php echo LSD_Form::submit([
                                    'label' => esc_html__('Deactivate', 'listdom'),
                                    'id' => $key.'_deactivation_button'
                                ]); ?>
                            </div>
                        </div>
                    </div>
                    <div class="lsd-form-row lsd-mb-0">
                        <div class="lsd-col-12"><div class="lsd-my-0" id="<?php echo esc_attr($key); ?>_deactivation_alert"></div></div>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<script>
// Activation Form
jQuery('.lsd-activation-form').on('submit', function(event)
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
            if(response.success)
            {
                $alert.removeClass('lsd-error lsd-success lsd-alert').addClass('lsd-alert lsd-success').html(response.message);
                $button.hide();

                const $panel = $alert.closest('.lsd-accordion-panel');

                $panel.removeClass('lsd-activation-grace').addClass('lsd-activation-valid');
                $panel.prev().removeClass('lsd-activation-grace').addClass('lsd-activation-valid');

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
jQuery('.lsd-deactivation-form').on('submit', function(event)
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
    const $wrapper = $form.find(jQuery('.lsd-deactivation-wrapper'));

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
                $alert.removeClass('lsd-error lsd-success lsd-alert').addClass('lsd-alert lsd-success').html(response.message);
                $wrapper.hide();

                const $panel = $alert.closest('.lsd-accordion-panel');

                $panel.removeClass('lsd-activation-grace').removeClass('lsd-activation-valid');
                $panel.prev().removeClass('lsd-activation-grace').removeClass('lsd-activation-valid');
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
</script>
