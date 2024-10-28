<?php
// no direct access
defined('ABSPATH') || die();

$settings = LSD_Options::settings();
?>
<div class="lsd-settings-wrap">
    <form id="lsd_settings_form">
        <div class="lsd-settings-form-group">
            <h3><?php esc_html_e('Archive Pages', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Location', 'listdom'),
                    'for' => 'lsd_settings_location_archive',
                ]); ?></div>
                <div class="lsd-col-5">
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_settings_location_archive',
                        'name' => 'lsd[location_archive]',
                        'only_archive_skins' => '1',
                        'show_empty' => '1',
                        'empty_label' => esc_html__('Current Theme Style', 'listdom'),
                        'value' => $settings['location_archive'] ?? ''
                    ]); ?>
                    <p class="description"><?php esc_html_e("If your site theme doesn't support the Listdom location template, then Listdom uses its own template file which might not be 100% compatible with your theme.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Category', 'listdom'),
                    'for' => 'lsd_settings_category_archive',
                ]); ?></div>
                <div class="lsd-col-5">
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_settings_category_archive',
                        'name' => 'lsd[category_archive]',
                        'only_archive_skins' => '1',
                        'show_empty' => '1',
                        'empty_label' => esc_html__('Current Theme Style', 'listdom'),
                        'value' => $settings['category_archive'] ?? ''
                    ]); ?>
                    <p class="description"><?php esc_html_e("If your site theme doesn't support the Listdom category template, then Listdom uses its own template file which might not be 100% compatible with your theme.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Tag', 'listdom'),
                    'for' => 'lsd_settings_tag_archive',
                ]); ?></div>
                <div class="lsd-col-5">
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_settings_tag_archive',
                        'name' => 'lsd[tag_archive]',
                        'only_archive_skins' => '1',
                        'show_empty' => '1',
                        'empty_label' => esc_html__('Current Theme Style', 'listdom'),
                        'value' => $settings['tag_archive'] ?? ''
                    ]); ?>
                    <p class="description"><?php esc_html_e("If your site theme doesn't support the Listdom tag template, then Listdom uses its own template file which might not be 100% compatible with your theme.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Feature', 'listdom'),
                    'for' => 'lsd_settings_feature_archive',
                ]); ?></div>
                <div class="lsd-col-5">
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_settings_feature_archive',
                        'name' => 'lsd[feature_archive]',
                        'only_archive_skins' => '1',
                        'show_empty' => '1',
                        'empty_label' => esc_html__('Current Theme Style', 'listdom'),
                        'value' => $settings['feature_archive'] ?? ''
                    ]); ?>
                    <p class="description"><?php esc_html_e("If your site theme doesn't support the Listdom feature template, then Listdom uses its own template file which might not be 100% compatible with your theme.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Label', 'listdom'),
                    'for' => 'lsd_settings_label_archive',
                ]); ?></div>
                <div class="lsd-col-5">
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_settings_label_archive',
                        'name' => 'lsd[label_archive]',
                        'only_archive_skins' => '1',
                        'show_empty' => '1',
                        'empty_label' => esc_html__('Current Theme Style', 'listdom'),
                        'value' => $settings['label_archive'] ?? ''
                    ]); ?>
                    <p class="description"><?php esc_html_e("If your site theme doesn't support the Listdom label template, then Listdom uses its own template file which might not be 100% compatible with your theme.", 'listdom'); ?></p>
                </div>
            </div>
        </div>
        <div class="lsd-spacer-10"></div>
        <div class="lsd-form-row">
			<div class="lsd-col-12 lsd-flex lsd-gap-3">
				<?php LSD_Form::nonce('lsd_settings_form'); ?>
				<?php echo LSD_Form::submit([
					'label' => esc_html__('Save', 'listdom'),
					'id' => 'lsd_settings_save_button',
                    'class' => 'button button-hero button-primary',
				]); ?>
                <div>
                    <p class="lsd-util-hide lsd-settings-success-message lsd-alert lsd-success lsd-m-0"><?php esc_html_e('Options saved successfully.', 'listdom'); ?></p>
                    <p class="lsd-util-hide lsd-settings-error-message lsd-alert lsd-error lsd-m-0"><?php esc_html_e('Error: Unable to save options.', 'listdom'); ?></p>
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
        data: "action=lsd_save_settings&" + settings,
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
