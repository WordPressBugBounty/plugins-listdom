<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_IX $this */
?>
<div class="lsd-ix-wrap">

    <?php if ($this->isLite()): ?>
    <div class="lsd-settings-group-wrapper lsd-px-4">
        <div class="lsd-settings-fields-wrapper">
            <?php echo LSD_Base::alert($this->missFeatureMessage(esc_html__('JSON Import / Export', 'listdom')), 'warning'); ?>
        </div>
    </div>
    <?php else: ?>
    <div id="lsd_panel_json_import" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'import' || !$this->subtab ? ' lsd-tab-content-active' : ''; ?>">
        <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Import', 'listdom'); ?></h3>
        <form id="lsd_ix_listdom_import_form" class="lsd-mt-4" enctype="multipart/form-data">
            <div class="lsd-settings-group-wrapper">
                <div class="lsd-settings-fields-wrapper">
                    <div class="lsd-form-row">
                        <div class="lsd-col-8 lsd-admin-input-file">
                            <?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__("Select the JSON file exported from listdom", 'listdom'),
                                'for' => 'lsd_ix_json_import_file_input',
                            ]) ?>
                            <?php echo LSD_Form::file([
                                'id' => 'lsd_ix_listdom_import_file_input',
                                'class' => 'lsd-util-hide',
                            ]); ?>
                            <?php echo LSD_Form::hidden([
                                'id' => 'lsd_ix_listdom_import_file',
                                'name' => 'ix[file]',
                            ]); ?>
                            <label for="lsd_ix_listdom_import_file_input" class="lsd-neutral-button lsd-json-import-choose-file lsd-w-max"><?php echo esc_html__('Choose File', 'listdom'); ?></label>
                        </div>
                    </div>
                    <p class="lsd-util-hide lsd-m-0" id="lsd_ix_listdom_import_message"></p>

                    <?php LSD_Form::nonce('lsd_ix_listdom_import'); ?>
                    <button type="submit" id="lsd_ix_listdom_import_submit" class="lsd-primary-button lsd-util-hide">
                        <?php esc_html_e('Import', 'listdom'); ?>
                        <i class="listdom-icon lsdi-checkmark-circle"></i>
                    </button>

                </div>
            </div>
        </form>
    </div>
    <div id="lsd_panel_json_export" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'export' ? ' lsd-tab-content-active' : ''; ?>">
        <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Export', 'listdom'); ?></h3>
        <div class="lsd-settings-group-wrapper">
            <div class="lsd-settings-fields-wrapper">
                <p class="lsd-admin-description lsd-mb-1 lsd-mt-0"><?php esc_html_e("Please click the button below to download the JSON export of your listings.", 'listdom'); ?></p>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=listdom-ix&lsd-export=json'), 'lsd_ix_form')); ?>" class="lsd-primary-button"><?php esc_html_e('Export', 'listdom'); ?></a>
            </div>
        </div>
    </div>
    <?php endif;?>
</div>
<script>
// File Upload
jQuery('#lsd_ix_listdom_import_file_input').on('change', function()
{
    let fd = new FormData();
    fd.append('action', 'lsd_ix_listdom_upload');
    fd.append('_wpnonce', '<?php echo wp_create_nonce('lsd_ix_listdom_upload'); ?>');
    fd.append('file', jQuery(this).prop('files')[0]);

    const $alert = jQuery("#lsd_ix_listdom_import_message");
    const $submit = jQuery("#lsd_ix_listdom_import_submit");
    const $file = jQuery('#lsd_ix_listdom_import_file_input');

    // Remove Alert
    $alert.html('');
    $alert.removeClass('lsd-util-hide');

    // Loading Wrapper
    const loading = (new ListdomButtonLoader(jQuery('.lsd-json-import-choose-file')));
    $file.attr('disabled', 'disabled');

    // Loading
    loading.start("<?php echo esc_js( esc_html__('Uploading', 'listdom') ); ?>");

    jQuery.ajax(
    {
        url: ajaxurl,
        type: "POST",
        data: fd,
        dataType: "json",
        processData: false,
        contentType: false
    })
    .done(function(response)
    {
        if(response.success === 1)
        {
            jQuery("#lsd_ix_listdom_import_file").val(response.data.file);
            $submit.removeClass('lsd-util-hide');

            loading.stop();
            $file.removeAttr('disabled');

            // Show Alert
            $alert.html(listdom_alertify(response.message, 'lsd-success lsd-m-0'));
        }
        else
        {
            jQuery("#lsd_ix_listdom_import_input").val('');

            loading.stop();
            $file.removeAttr('disabled');
            $submit.addClass('lsd-util-hide');

            // Show Alert
            listdom_toastify(response.message, 'lsd-error');
        }
    });
});

// Form Submit
jQuery('#lsd_ix_listdom_import_form').on('submit', function(e)
{
    e.preventDefault();

    // Button
    const $button = jQuery("#lsd_ix_listdom_import_submit");
    $button.removeClass('lsd-util-hide');

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js(esc_html__('Importing', 'listdom')); ?>");

    listdom_toastify("<?php echo esc_js(esc_html__('Please wait ...', 'listdom')); ?>", 'lsd-in-progress');
    jQuery("#lsd_ix_listdom_import_message").addClass('lsd-util-hide');

    const data = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_ix_listdom_import&" + data,
        dataType: 'json',
        success: function(response)
        {
            loading.stop();

            if(response.success === 1)
            {
                listdom_toastify(response.message, 'lsd-success');
                $button.addClass('lsd-util-hide');

                jQuery('.lsd-in-progress').remove();
                window.currentToast = null;

                // hidden value
                jQuery('#lsd_ix_listdom_import_file_input').val('');
                jQuery("#lsd_ix_listdom_import_file").val('');
            }
            else
            {
                listdom_toastify(response.message, 'lsd-error');
            }
        },
        error: function()
        {
            loading.stop();
            $button.addClass('lsd-util-hide');
            listdom_toastify("<?php echo esc_js(esc_html__('An error occurred! Most probably maximum execution time reached so try increasing the maximum execution time of your server.', 'listdom')); ?>", 'lsd-error');
        }
    });
});
</script>
