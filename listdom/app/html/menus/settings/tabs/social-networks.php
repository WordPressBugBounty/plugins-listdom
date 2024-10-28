<?php
// no direct access
defined('ABSPATH') || die();

// Listdom Social Networks
$SN = new LSD_Socials();
$networks = LSD_Options::socials();
?>
<div class="lsd-settings-wrap">
    <form id="lsd_socials_form">
        <div class="lsd-settings-form-group">
            <h3><?php esc_html_e('Social Networks', 'listdom'); ?></h3>
            <div class="lsd-form-row lsd-mb-3">
                <div class="lsd-col-2"></div>
                <div class="lsd-col-1"></div>
                <div class="lsd-col-1"><?php esc_html_e('Profile', 'listdom'); ?></div>
                <div class="lsd-col-1"><?php esc_html_e('Archive', 'listdom'); ?></div>
                <div class="lsd-col-1"><?php esc_html_e('Details', 'listdom'); ?></div>
                <div class="lsd-col-1"><?php esc_html_e('Contact', 'listdom'); ?></div>
            </div>
            <div class="lsd-social-networks lsd-sortable">
                <?php foreach($networks as $network=>$values): $obj = $SN->get($network, $values); if(!$obj) continue; ?>
                <div class="lsd-form-row lsd-social-network">
                    <div class="lsd-col-2 lsd-cursor-move">
                        <i class="lsd-icon fas fa-arrows-alt"></i>
                        <span class="lsd-ml-4">
                            <strong><?php echo esc_html($obj->label()); ?></strong>
                            <input type="hidden" name="lsd[<?php echo esc_attr($obj->key()); ?>][key]" value="<?php echo esc_attr($obj->key()); ?>">
                        </span>
                    </div>
                    <div class="lsd-col-1"></div>
                    <div class="lsd-col-1">
                        <label class="lsd-switch">
                            <input type="hidden" name="lsd[<?php echo esc_attr($obj->key()); ?>][profile]" value="0">
                            <input type="checkbox" name="lsd[<?php echo esc_attr($obj->key()); ?>][profile]" value="1" <?php echo $obj->option('profile') == 1 ? 'checked="checked"' : ''; ?>>
                            <span class="lsd-slider"></span>
                        </label>
                    </div>
                    <div class="lsd-col-1">
                        <label class="lsd-switch">
                            <input type="hidden" name="lsd[<?php echo esc_attr($obj->key()); ?>][archive_share]" value="0">
                            <input type="checkbox" name="lsd[<?php echo esc_attr($obj->key()); ?>][archive_share]" value="1" <?php echo $obj->option('archive_share') == 1 ? 'checked="checked"' : ''; ?>>
                            <span class="lsd-slider"></span>
                        </label>
                    </div>
                    <div class="lsd-col-1">
                        <label class="lsd-switch">
                            <input type="hidden" name="lsd[<?php echo esc_attr($obj->key()); ?>][single_share]" value="0">
                            <input type="checkbox" name="lsd[<?php echo esc_attr($obj->key()); ?>][single_share]" value="1" <?php echo $obj->option('single_share') == 1 ? 'checked="checked"' : ''; ?>>
                            <span class="lsd-slider"></span>
                        </label>
                    </div>
                    <div class="lsd-col-1">
                        <label class="lsd-switch">
                            <input type="hidden" name="lsd[<?php echo esc_attr($obj->key()); ?>][listing]" value="0">
                            <input type="checkbox" name="lsd[<?php echo esc_attr($obj->key()); ?>][listing]" value="1" <?php echo $obj->option('listing') == 1 ? 'checked="checked"' : ''; ?>>
                            <span class="lsd-slider"></span>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="lsd-spacer-10"></div>
        <div class="lsd-form-row">
			<div class="lsd-col-12 lsd-flex lsd-gap-3">
                <div>
                    <?php LSD_Form::nonce('lsd_socials_form'); ?>
                    <?php echo LSD_Form::submit([
                        'label' => esc_html__('Save', 'listdom'),
                        'id' => 'lsd_socials_save_button',
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
jQuery('#lsd_socials_form').on('submit', function(event)
{
    event.preventDefault();

    // Elements
    const $button = jQuery("#lsd_socials_save_button");
    const $success = jQuery(".lsd-settings-success-message");
    const $error = jQuery(".lsd-settings-error-message");

    // Loading Styles
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    const socials = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_socials&" + socials,
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
