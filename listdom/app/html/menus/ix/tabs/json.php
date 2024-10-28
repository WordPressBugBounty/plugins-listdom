<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_IX $this */
?>
<div class="lsd-ix-wrap">

    <?php if($this->isLite()): echo LSD_Base::alert($this->missFeatureMessage(esc_html__('JSON Import / Export', 'listdom')), 'warning'); ?>
    <?php else: ?>
    <h3><?php esc_html_e('Export', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-12">
            <ul>
                <li><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=listdom-ix&lsd-export=json'), 'lsd_ix_form')); ?>" class="button button-primary"><?php esc_html_e('JSON', 'listdom'); ?></a></li>
            </ul>
        </div>
    </div>

    <h3><?php esc_html_e('Import', 'listdom'); ?></h3>
    <form id="lsd_ix_listdom_import_form" class="lsd-mt-4" enctype="multipart/form-data">
        <div class="lsd-form-row">
            <div class="lsd-col-4">
                <?php echo LSD_Form::file([
                    'id' => 'lsd_ix_listdom_import_file_input',
                ]); ?>
            </div>
            <div class="lsd-col-1 lsd-text-left">
                <?php echo LSD_Form::hidden([
                    'id' => 'lsd_ix_listdom_import_file',
                    'name' => 'ix[file]',
                ]); ?>
                <?php LSD_Form::nonce('lsd_ix_listdom_import'); ?>
                <button type="submit" id="lsd_ix_listdom_import_submit" class="button button-primary" disabled><?php esc_html_e('Import', 'listdom'); ?></button>
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-12">
                <p id="lsd_ix_listdom_import_message"></p>
                <?php echo LSD_Base::alert(esc_html__("You should upload only JSON file exported from Listdom!", 'listdom')); ?>
            </div>
        </div>
    </form>
    <?php endif; ?>

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

    // Remove Alert
    $alert.html('');

    // Disable Button
    $submit.attr('disabled', 'disabled');

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
            $submit.removeAttr('disabled');

            // Show Alert
            $alert.html(listdom_alertify(response.message, 'lsd-success'));
        }
        else
        {
            jQuery("#lsd_ix_listdom_import_input").val('');

            // Show Alert
            $alert.html(listdom_alertify(response.message, 'lsd-error'));
        }
    });
});

// Form Submit
jQuery('#lsd_ix_listdom_import_form').on('submit', function(e)
{
    e.preventDefault();

    // Button
    const $button = jQuery("#lsd_ix_listdom_import_submit");

    // Add loading Class to the button
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>').attr('disabled', 'disabled');

    // Alert
    const $alert = jQuery("#lsd_ix_listdom_import_message");

    // Show Alert
    $alert.html(listdom_alertify("<?php echo esc_js(esc_attr__('Please wait ...', 'listdom')); ?>", 'lsd-info'));

    const data = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_ix_listdom_import&" + data,
        dataType: 'json',
        success: function(response)
        {
            // Remove loading Class from the button
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Import', 'listdom')); ?>").removeAttr('disabled');

            if(response.success === 1)
            {
                // Show Alert
                $alert.html(listdom_alertify(response.message, 'lsd-success'));
            }
            else
            {
                // Show Alert
                $alert.html(listdom_alertify(response.message, 'lsd-error'));
            }
        },
        error: function()
        {
            // Remove loading Class from the button
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Import', 'listdom')); ?>").removeAttr('disabled');

            // Show Alert
            $alert.html(listdom_alertify("<?php echo esc_js(esc_attr__('An error occurred! Most probably maximum execution time reached so try increasing the maximum execution time of your server.', 'listdom')); ?>", 'lsd-error'));
        }
    });
});
</script>
