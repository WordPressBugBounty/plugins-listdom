<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

// AI Settings
$ai = LSD_Options::ai();
?>
<div class="lsd-settings-wrap">
    <form id="lsd_ai_form">
        <div class="lsd-tab-content-active lsd-tab-content">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('AI Profiles', 'listdom'); ?></h3>
            <div class="lsd-settings-form-group lsd-box-white lsd-rounded">
                <div class="lsd-settings-group-wrapper">
                    <div class="lsd-settings-fields-wrapper">
                        <div class="lsd-mt-0 lsd-mb-4">
                            <p class="description lsd-mt-0"><?php esc_html_e("You can create and manage multiple AI profiles, each configured with a specific model and settings. This allows you to choose the best AI for different tasks, such as low-cost models for auto-mapping or powerful models for content and image generation.", 'listdom'); ?></p>
                            <button type="button" class="button" id="lsd_settings_ai_add_profile"><?php esc_html_e('Add New Profile', 'listdom'); ?></button>
                        </div>

                        <?php if (!isset($ai['profiles']) || !is_array($ai['profiles']) || !count($ai['profiles'])): ?>
                            <div class="lsd-alert-no-my"><div class="lsd-alert lsd-info"><?php esc_html_e('Unlock AI capabilities by creating your first profile. You can then assign specific AI models to various tasks.', 'listdom'); ?></div></div>
                        <?php else: ?>
                            <div class="lsd-ai-profiles lsd-flex lsd-flex-row lsd-flex-content-start lsd-flex-wrap lsd-gap-4">
                                <?php foreach($ai['profiles'] as $i => $profile): ?>
                                    <div class="lsd-box-white lsd-settings-fields-wrapper lsd-rounded lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-4" id="lsd_settings_ai_profiles_<?php echo esc_attr($i); ?>">
                                        <div class="lsd-flex lsd-flex-row">
                                            <h4 class="lsd-my-0"><?php echo esc_html($profile['name'] ?? 'N/A'); ?></h4>
                                            <?php echo LSD_Form::hidden([
                                                'name' => 'lsd[profiles]['.esc_attr($i).'][id]',
                                                'value' => $profile['id'] ?? LSD_Base::str_random(10),
                                            ]); ?>
                                            <div class="lsd-ai-remove-profile lsd-pt-2 lsd-cursor-pointer" data-i="<?php echo esc_attr($i); ?>" data-confirm="0"><i class="lsd-icon fas fa-trash-alt"></i></div>
                                        </div>
                                        <div>
                                            <?php echo LSD_Form::label([
                                                'for' => 'lsd_settings_ai_profile_'.esc_attr($i).'_name',
                                                'title' => esc_html__('Descriptive Name', 'listdom'),
                                            ]); ?>
                                            <?php echo LSD_Form::text([
                                                'id' => 'lsd_settings_ai_profile_'.esc_attr($i).'_name',
                                                'name' => 'lsd[profiles]['.esc_attr($i).'][name]',
                                                'value' => $profile['name'] ?? '',
                                                'placeholder' => esc_html__('Profile Name', 'listdom'),
                                            ]); ?>
                                        </div>
                                        <div>
                                            <?php echo LSD_Form::label([
                                                'for' => 'lsd_settings_ai_profile_'.esc_attr($i).'_model',
                                                'title' => esc_html__('Model', 'listdom'),
                                            ]); ?>
                                            <?php echo LSD_Form::ai_providers([
                                                'id' => 'lsd_settings_ai_profile_'.esc_attr($i).'_model',
                                                'value' => $profile['model'] ?? '',
                                                'name' => 'lsd[profiles]['.esc_attr($i).'][model]',
                                            ]); ?>
                                        </div>
                                        <div>
                                            <?php echo LSD_Form::label([
                                                'for' => 'lsd_settings_ai_profile_'.esc_attr($i).'_api_key',
                                                'title' => esc_html__('API Key', 'listdom'),
                                            ]); ?>
                                            <?php echo LSD_Form::text([
                                                'id' => 'lsd_settings_ai_profile_'.esc_attr($i).'_api_key',
                                                'value' => $profile['api_key'] ?? '',
                                                'name' => 'lsd[profiles]['.esc_attr($i).'][api_key]',
                                                'placeholder' => esc_html__('API Key (Required)', 'listdom'),
                                            ]); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="lsd-spacer-10"></div>
        <div class="lsd-form-row lsd-settings-submit-wrapper">
			<div class="lsd-col-12 lsd-flex lsd-flex-content-end">
				<?php LSD_Form::nonce('lsd_ai_form'); ?>
                <button type="submit" id="lsd_ai_save_button" class="lsd-primary-button">
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
// Add Profile
jQuery('#lsd_settings_ai_add_profile').on('click', function ()
{
    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_ai_add_profile&_wpnonce=<?php echo wp_create_nonce('lsd_ai_add_profile'); ?>",
        dataType: "json",
        success: function(response)
        {
            // Unloading
            loading.stop(null, 200);

            if(response.success === 1) location.reload();
        },
        error: function()
        {
            // Unloading
            loading.stop(null, 200);
        }
    });
});

// Remove Profile
jQuery('.lsd-ai-remove-profile').on('click', function()
{
    const $button = jQuery(this);

    const i = $button.data('i');
    const confirm = $button.data('confirm');

    if(!confirm)
    {
        $button.data('confirm', 1);
        $button.addClass('lsd-need-confirm');

        setTimeout(function()
        {
            $button.data('confirm', 0);
            $button.removeClass('lsd-need-confirm');
        }, 5000);

        return false;
    }

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_ai_remove_profile&_wpnonce=<?php echo wp_create_nonce('lsd_ai_remove_profile'); ?>&i="+i,
        dataType: "json",
        success: function(response)
        {
            if(response.success === 1)
            {
                // Remove Profile
                jQuery('#lsd_settings_ai_profiles_'+i).remove();

                // Unloading
                loading.stop(null, 200);
            }
        },
        error: function()
        {
            // Unloading
            loading.stop(null, 200);
        }
    });
});

// Save
jQuery('#lsd_ai_form').on('submit', function (event)
{
    event.preventDefault();

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());
    const $success = jQuery('.lsd-settings-success-message');
    const $error = jQuery('.lsd-settings-error-message');
    const $tab = jQuery('.nav-tab-active');

    // Loading
    loading.start();

    const settings = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_ai&" + settings,
        success: function ()
        {
            $tab.attr('data-saved', 'true');

            // Unloading
            loading.stop($success, 1000);
        },
        error: function ()
        {
            $tab.attr('data-saved', 'false');

            // Unloading
            loading.stop($error, 1000);
        }
    });
});
</script>
