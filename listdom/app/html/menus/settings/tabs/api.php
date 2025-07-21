<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

$api = LSD_Options::api();
?>
<div class="lsd-settings-wrap">
    <form id="lsd_api_form">
        <div class="lsd-tab-content-active lsd-tab-content">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('API Tokens', 'listdom'); ?></h3>
            <?php if ($this->isLite()): ?>
                <div class="lsd-m-4"><?php echo LSD_Base::alert($this->missFeatureMessage(esc_html__('API', 'listdom')), 'warning'); ?></div>
            <?php else: ?>
                <div class="lsd-settings-form-group lsd-box-white lsd-rounded">
                    <div class="lsd-settings-group-wrapper">
                        <div class="lsd-settings-fields-wrapper">
                            <div class="lsd-mt-0 lsd-mb-5">
                                <p class="description lsd-mt-0 lsd-mb-0"><?php esc_html_e("Do not remove a token if an application is using it because it will destroy the functionality of that application. Insert a descriptive name for any token.", 'listdom'); ?></p>
                                <div class="lsd-alert lsd-info"><?php echo sprintf(esc_html__('You can use the %s URL as the API base URL.', 'listdom'), '<code>'.get_rest_url().'</code>'); ?></div>
                                <button type="button" class="button" id="lsd_settings_api_add_token"><?php esc_html_e('Add Token', 'listdom'); ?></button>
                            </div>
                            <?php foreach($api['tokens'] as $i => $token): ?>
                            <div class="lsd-form-row" id="lsd_settings_api_tokens_<?php echo esc_attr($i); ?>">
                                <div class="lsd-col-2"><?php echo LSD_Form::text([
                                    'id' => 'lsd_settings_api_tokens_'.esc_attr($i).'_name',
                                    'name' => 'lsd[tokens]['.esc_attr($i).'][name]',
                                    'value' => $token['name'],
                                    'placeholder' => esc_html__('Token Name', 'listdom'),
                                ]); ?></div>
                                <div class="lsd-col-4">
                                    <input title="" type="text" name="lsd[tokens][<?php echo esc_attr($i); ?>][key]" id="lsd_settings_api_tokens_<?php echo esc_attr($i); ?>_key" value="<?php echo esc_attr($token['key']); ?>" placeholder="<?php esc_attr_e('Token Key', 'listdom'); ?>" readonly>
                                </div>
                                <div class="lsd-col-1">
                                    <div class="lsd-api-remove-token lsd-pt-2 lsd-cursor-pointer" data-i="<?php echo esc_attr($i); ?>" data-confirm="0"><i class="lsd-icon fas fa-trash-alt"></i></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($this->isPro()): ?>
        <div class="lsd-spacer-10"></div>
        <div class="lsd-form-row lsd-settings-submit-wrapper">
			<div class="lsd-col-12 lsd-flex lsd-flex-content-end">
				<?php LSD_Form::nonce('lsd_api_form'); ?>
                <button type="submit" id="lsd_api_save_button" class="lsd-primary-button">
                    <?php esc_html_e('Save The Changes', 'listdom'); ?>
                    <i class='lsdi lsdi-checkmark-circle'></i>
                </button>
			</div>
        </div>
        <?php endif; ?>
    </form>
</div>
<script>
// Add Token
jQuery('#lsd_settings_api_add_token').on('click', function()
{
    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_api_add_token&_wpnonce=<?php echo wp_create_nonce('lsd_api_add_token'); ?>",
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

// Remove Token
jQuery('.lsd-api-remove-token').on('click', function()
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
        data: "action=lsd_api_remove_token&_wpnonce=<?php echo wp_create_nonce('lsd_api_remove_token'); ?>&i="+i,
        dataType: "json",
        success: function(response)
        {
            if(response.success === 1)
            {
                // Remove Token
                jQuery('#lsd_settings_api_tokens_'+i).remove();

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
jQuery('#lsd_api_form').on('submit', function(event)
{
    event.preventDefault();

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());
    const $tab = jQuery('.nav-tab-active');

    // Loading
    loading.start();

    const settings = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_api&" + settings,
        success: function()
        {
            $tab.attr('data-saved', 'true');

            // Unloading
            loading.stop(null, 1000);
        },
        error: function()
        {
            $tab.attr('data-saved', 'false');

            // Unloading
            loading.stop(null, 1000);
        }
    });
});
</script>
