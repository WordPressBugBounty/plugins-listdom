<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_IX $this */

// Sub-tab
$sub = isset($_GET['sub']) && trim($_GET['sub']) ? sanitize_text_field($_GET['sub']) : 'import';

// Templates Library
$template = new LSD_IX_Templates_CSV();

// Import Jobs
$jobs = new LSD_IX_Jobs_CSV();
?>
<div class="lsd-ix-wrap">

    <ul class="lsd-sub-tabs lsd-flex lsd-gap-3 lsd-mt-4">
        <li class="<?php echo $sub === 'import' ? 'lsd-sub-tabs-active' : ''; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=csv')); ?>"><?php esc_html_e('Import', 'listdom'); ?></a></li>
        <li class="<?php echo $sub === 'templates' ? 'lsd-sub-tabs-active' : ''; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=csv&sub=templates')); ?>"><?php esc_html_e('Mapping Templates', 'listdom'); ?></a></li>
        <li class="<?php echo $sub === 'auto' ? 'lsd-sub-tabs-active' : ''; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=csv&sub=auto')); ?>"><?php esc_html_e('Auto Import', 'listdom'); ?></a></li>
        <li class="<?php echo $sub === 'export' ? 'lsd-sub-tabs-active' : ''; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=csv&sub=export')); ?>"><?php esc_html_e('Export', 'listdom'); ?></a></li>
    </ul>

    <?php if ($sub === 'export'): ?>
        <div class="lsd-form-row lsd-mt-5">
            <div class="lsd-col-12">
                <p class="description lsd-mb-3"><?php esc_html_e("Please click the button below to download the CSV export of your listings.", 'listdom'); ?></p>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=listdom-ix&lsd-export=csv'), 'lsd_ix_form')); ?>" class="button button-primary button-hero"><?php esc_html_e('Export', 'listdom'); ?></a>
            </div>
        </div>
    <?php elseif ($sub === 'auto'): ?>
        <div>
            <?php if (!class_exists(LSDPACCSV\Base::class)): echo LSD_Base::alert($this->missAddonMessage('CSV Importer', esc_html__('Auto Import', 'listdom')), 'warning'); ?>
            <?php else: ?>
                <h3><?php esc_html_e('Auto Import', 'listdom'); ?></h3>
                <form id="lsd_ix_csv_auto_import_form" class="lsd-mt-4">
                    <div class="lsd-form-row">
                        <div class="lsd-col-1">
                            <label for="lsd_ix_csv_auto_import_url">
                                <?php esc_html_e('Feed URL', 'listdom'); ?>
                            </label>
                        </div>
                        <div class="lsd-col-6">
                            <input
                                type="url"
                                name="ix[url]"
                                id="lsd_ix_csv_auto_import_url"
                                placeholder="https://docs.google.com/spreadsheets/d/document-id/export?format=csv"
                            >
                            <p class="description"><?php esc_html_e("This field should contain the URL leading to the CSV file you wish to import.", 'listdom'); ?></p>
                        </div>
                    </div>
                    <div class="lsd-form-row">
                        <div class="lsd-col-1">
                            <label for="lsd_ix_csv_auto_import_mapping">
                                <?php esc_html_e('Field Mapping', 'listdom'); ?>
                            </label>
                        </div>
                        <div class="lsd-col-6">
                            <?php echo $template->dropdown([
                                'id' => 'lsd_ix_csv_auto_import_mapping',
                                'name' => 'ix[mapping]',
                                'show_empty' => true,
                            ]); ?>
                        </div>
                    </div>
                    <div class="lsd-form-row">
                        <div class="lsd-col-1">
                            <label for="lsd_ix_csv_auto_import_interval">
                                <?php esc_html_e('Interval', 'listdom'); ?>
                            </label>
                        </div>
                        <div class="lsd-col-6">
                            <select name="ix[interval]" id="lsd_ix_csv_auto_import_interval">
                                <option value="weekly"><?php esc_html_e('Weekly', 'listdom'); ?></option>
                                <option value="daily"><?php esc_html_e('Daily', 'listdom'); ?></option>
                                <option value="twicedaily"><?php esc_html_e('Twice a Day', 'listdom'); ?></option>
                                <option value="hourly"><?php esc_html_e('Hourly', 'listdom'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="lsd-form-row">
                        <div class="lsd-col-1">
                            <label for="lsd_ix_csv_auto_import_size">
                                <?php esc_html_e('Import Size', 'listdom'); ?>
                            </label>
                        </div>
                        <div class="lsd-col-6">
                            <input type="number" name="ix[size]" id="lsd_ix_csv_auto_import_size" min="10" max="300" step="1" value="100">
                            <p class="description"><?php esc_html_e("It determines the maximum number of listings imported in each run of the import job.", 'listdom'); ?></p>
                        </div>
                    </div>
                    <div class="lsd-form-row">
                        <div class="lsd-col-12">
                            <p id="lsd_ix_csv_auto_import_message"></p>
                        </div>
                    </div>
                    <div class="lsd-form-row">
                        <div class="lsd-col-12">
                            <?php LSD_Form::nonce('lsdaddcsv_auto_add'); ?>
                            <button class="button button-primary button-hero" type="submit"><?php esc_html_e('Add Import Job', 'listdom'); ?></button>
                        </div>
                    </div>
                </form>
                <?php if (count($jobs->all())): ?>
                    <div class="lsd-ix-auto-import-existing-jobs">
                        <hr>
                        <h3 class="lsd-mt-5"><?php esc_html_e('Existing Jobs', 'listdom'); ?></h3>
                        <?php echo $jobs->manage(); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php elseif ($sub === 'templates'): ?>
        <h3 class="lsd-mb-5"><?php esc_html_e('Field Mapping Templates', 'listdom'); ?></h3>
        <?php echo $template->manage('lsd_csv_template_remove'); ?>
    <?php else: ?>
        <h3><?php esc_html_e('Import', 'listdom'); ?></h3>
        <form id="lsd_ix_csv_import_form" class="lsd-mt-4" enctype="multipart/form-data">
            <div class="lsd-form-row">
                <div class="lsd-col-6">
                    <?php echo LSD_Form::file([
                        'id' => 'lsd_ix_csv_import_file_input',
                    ]); ?>
                    <?php echo LSD_Form::hidden([
                        'id' => 'lsd_ix_csv_import_file',
                        'name' => 'ix[file]',
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-12">
                    <p id="lsd_ix_csv_import_message"></p>
                    <div id="lsd_ix_csv_import_mapping"></div>
                </div>
            </div>
            <div class="lsd-form-row" id="lsd_ix_csv_import_button_wrap">
                <div class="lsd-col-12">
                    <?php LSD_Form::nonce('lsd_ix_csv_import'); ?>
                    <?php echo LSD_Form::hidden([
                        'id' => 'lsd_ix_csv_import_size',
                        'name' => 'ix[size]',
                        'value' => '20',
                    ]); ?>
                    <?php echo LSD_Form::hidden([
                        'id' => 'lsd_ix_csv_import_offset',
                        'name' => 'ix[offset]',
                        'value' => '0',
                    ]); ?>
                    <button type="submit" id="lsd_ix_csv_import_submit" class="button button-primary button-hero" disabled><?php esc_html_e('Import', 'listdom'); ?></button>
                </div>
            </div>
        </form>
    <?php endif; ?>

</div>
<script>
// Manual File Upload
jQuery('#lsd_ix_csv_import_file_input').on('change', function()
{
    let fd = new FormData();

    fd.append('action', 'lsd_ix_csv_upload');
    fd.append('_wpnonce', '<?php echo wp_create_nonce('lsd_ix_csv_upload'); ?>');
    fd.append('file', jQuery(this).prop('files')[0]);

    const $alert = jQuery("#lsd_ix_csv_import_message");
    const $mapping = jQuery("#lsd_ix_csv_import_mapping");
    const $submit_wrap = jQuery("#lsd_ix_csv_import_button_wrap");
    const $submit = jQuery("#lsd_ix_csv_import_submit");

    // Remove Alert
    $alert.html('');

    // Mapping Form
    $mapping.html('');

    // Disable Button
    $submit.attr('disabled', 'disabled');
    $submit_wrap.removeClass('lsd-text-right');

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
            jQuery("#lsd_ix_csv_import_file").val(response.data.file);

            // Enable Button
            $submit.removeAttr('disabled');
            $submit_wrap.addClass('lsd-text-right');

            // Mapping Form
            $mapping.html(response.output);

            // Show Alert
            $alert.html(listdom_alertify(response.message, 'lsd-success'));
        }
        else
        {
            jQuery("#lsd_ix_csv_import_input").val('');

            // Show Alert
            $alert.html(listdom_alertify(response.message, 'lsd-error'));
        }
    });
});

// Manual Import Submit
jQuery('#lsd_ix_csv_import_form').on('submit', function(e)
{
    e.preventDefault();

    const $button = jQuery("#lsd_ix_csv_import_submit");
    const $alert = jQuery("#lsd_ix_csv_import_message");
    const $mapping = jQuery("#lsd_ix_csv_import_mapping");
    const $input = jQuery("#lsd_ix_csv_import_file_input");
    const $offset = jQuery("#lsd_ix_csv_import_offset");
    const $size = jQuery("#lsd_ix_csv_import_size");

    // Hide Elements
    $mapping.hide();
    $input.hide();

    // Add loading Class to the button
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>').attr('disabled', 'disabled');

    // Show Alert
    $alert.html(listdom_alertify("<?php echo esc_js(esc_attr__('Please wait ...', 'listdom')); ?>", 'lsd-info'));

    // Start the Import
    lsd_csv_chunk_import();

    // Import Function
    function lsd_csv_chunk_import()
    {
        const data = jQuery("#lsd_ix_csv_import_form").serialize();
        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: "action=lsd_ix_csv_import&" + data,
            dataType: 'json',
            success: function(response)
            {
                if(response.success === 1)
                {
                    if(response.done)
                    {
                        // Show Alert
                        $alert.html(listdom_alertify(response.message, 'lsd-success'));

                        // Hide Button
                        $button.removeClass('loading').hide()
                    }
                    else
                    {
                        // Show Alert
                        $alert.html(listdom_alertify(response.message, 'lsd-info'));

                        // New Offset
                        $offset.val(parseInt($offset.val()) + parseInt($size.val()));

                        // Run the import again
                        setTimeout(function()
                        {
                            lsd_csv_chunk_import();
                        }, 500);
                    }
                }
                else
                {
                    // Show Alert
                    $alert.html(listdom_alertify(response.message, 'lsd-error'));

                    // Show Mapping Form
                    setTimeout(function()
                    {
                        $input.show();
                        $mapping.show();

                        // Remove loading Class from the button
                        $button.removeClass('loading')
                            .html("<?php echo esc_js(esc_attr__('Import', 'listdom')); ?>")
                            .removeAttr('disabled');
                    }, 3000);
                }
            },
            error: function()
            {
                // Remove loading Class from the button
                $button.removeClass('loading')
                    .html("<?php echo esc_js(esc_attr__('Import', 'listdom')); ?>")
                    .removeAttr('disabled');

                // Show Alert
                $alert.html(listdom_alertify("<?php echo esc_js(esc_attr__('An error occurred! Most probably maximum execution time reached so try increasing the maximum execution time of your server.', 'listdom')); ?>", 'lsd-error'));

                // Show Mapping Form
                setTimeout(function()
                {
                    $mapping.show();
                }, 3000);
            }
        });
    }
});

// Auto Import Add
jQuery('#lsd_ix_csv_auto_import_form').on('submit', function(e)
{
    e.preventDefault();

    const $form = jQuery(this);
    const $button = $form.find(jQuery('button'));
    const $alert = jQuery("#lsd_ix_csv_auto_import_message");

    // Add loading Class to the button
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>').attr('disabled', 'disabled');

    // Show Alert
    $alert.html(listdom_alertify("<?php echo esc_js(esc_attr__('Please wait ...', 'listdom')); ?>", 'lsd-info'));

    const data = $form.serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsdaddcsv_auto_add&" + data,
        dataType: 'json',
        success: function(response)
        {
            // Remove loading Class from the button
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Add Import Job', 'listdom')); ?>").removeAttr('disabled');

            // Show Alert
            $alert.html(listdom_alertify(response.message, response.success === 1 ? 'lsd-success' : 'lsd-error'));
        },
        error: function()
        {
            // Remove loading Class from the button
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Add Import Job', 'listdom')); ?>").removeAttr('disabled');

            // Show Alert
            $alert.html(listdom_alertify("<?php echo esc_js(esc_attr__('An error occurred! Most probably maximum execution time reached so try increasing the maximum execution time of your server.', 'listdom')); ?>", 'lsd-error'));
        }
    });
});
</script>
