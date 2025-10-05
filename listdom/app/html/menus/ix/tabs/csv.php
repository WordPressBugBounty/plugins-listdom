<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_IX $this */

// Templates Library
$template = new LSD_IX_Templates_CSV();

// Import Jobs
$jobs = new LSD_IX_Jobs_CSV();
?>
<div class="lsd-ix-wrap">
    <div id="lsd_panel_csv_import" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'import' || !$this->subtab ? ' lsd-tab-content-active' : ''; ?>">
        <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Import', 'listdom'); ?></h3>
        <form id="lsd_ix_csv_import_form" class="lsd-mt-4" enctype="multipart/form-data">
            <div class="lsd-settings-group-wrapper">
                <div class="lsd-settings-fields-wrapper">
                    <div class="lsd-form-row">
                        <div class="lsd-col-8 lsd-admin-input-file">
                            <?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Select the CSV file to import', 'listdom'),
                                'for' => 'lsd_ix_csv_import_file_input',
                            ]); ?>
                            <?php echo LSD_Form::file([
                                'id' => 'lsd_ix_csv_import_file_input',
                                'class' => 'lsd-util-hide'
                            ]); ?>
                            <?php echo LSD_Form::hidden([
                                'id' => 'lsd_ix_csv_import_file',
                                'name' => 'ix[file]',
                            ]); ?>
                            <label for="lsd_ix_csv_import_file_input" class="lsd-neutral-button lsd-csv-import-choose-file lsd-w-max"><?php echo esc_html__('Choose File', 'listdom'); ?></label>
                        </div>
                    </div>
                </div>

                <div class="lsd-form-row">
                    <div class="lsd-col-12">
                        <p id="lsd_ix_csv_import_message"></p>
                        <div id="lsd_ix_csv_import_mapping"></div>
                    </div>
                </div>
            </div>
            <div class="lsd-form-row lsd-settings-submit-wrapper lsd-util-hide" id="lsd_ix_csv_import_button_wrap">
                <div class="lsd-csv-import-button">
                    <div class="lsd-flex lsd-flex-align-items-center lsd-flex-content-end lsd-gap-3">
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Save as a new Template', 'listdom'),
                            ]); ?>
                        </div>
                        <div class="lsd-col-2">
                            <?php echo LSD_Form::switcher([
                                'id' => 'lsd_csv_template_name_toggle',
                                'name' => 'ix[template_new_toggle]',
                                'toggle' => '#lsd_csv_template_name',
                                'value' => 1,
                            ]); ?>
                        </div>
                    </div>
                    <div id="lsd_csv_template_name">
                        <?php echo LSD_Form::text([
                            'name' => 'ix[template]',
                            'class' => 'lsd-admin-input',
                            'id' => 'ix_template_new',
                            'value' => sprintf(
                                /* translators: %s: Current date formatted as Y-m-d-H-i. */
                                esc_html__('CSV (%s)', 'listdom'),
                                lsd_date('Y-m-d-H-i')
                            ),
                        ]); ?>
                    </div>
                    <div>
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
                        <button type="submit" id="lsd_ix_csv_import_submit" class="lsd-primary-button">
                            <?php esc_html_e('Import', 'listdom'); ?>
                            <i class="listdom-icon lsdi-checkmark-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div id="lsd_panel_csv_auto-import" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'auto-import' ? ' lsd-tab-content-active' : ''; ?>">
        <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Auto Import', 'listdom'); ?></h3>
        <div class="lsd-settings-group-wrapper">
            <div class="lsd-settings-fields-wrapper">
                <h3 class="lsd-admin-title"><?php esc_html_e('Add a New Job', 'listdom'); ?></h3>
                <?php if (!class_exists(LSDPACCSV\Base::class)): echo LSD_Base::alert($this->missAddonMessage('CSV Importer', esc_html__('Auto Import', 'listdom')), 'warning'); ?>
                <?php else: ?>
                    <form class="lsd-settings-fields-sub-wrapper" id="lsd_ix_csv_auto_import_form">
                        <div class="lsd-form-row">
                            <div class="lsd-col-2">
                                <label for="lsd_ix_csv_auto_import_url" class="lsd-fields-label">
                                    <?php esc_html_e('Feed URL', 'listdom'); ?>
                                </label>
                            </div>
                            <div class="lsd-col-6">
                                <input
                                    type="url"
                                    name="ix[url]"
                                    class="lsd-admin-input"
                                    id="lsd_ix_csv_auto_import_url"
                                    placeholder="https://docs.google.com/spreadsheets/d/document-id/export?format=csv"
                                >
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("This field should contain the URL leading to the CSV file you wish to import.", 'listdom'); ?></p>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-2">
                                <label for="lsd_ix_csv_auto_import_mapping" class="lsd-fields-label">
                                    <?php esc_html_e('Field Mapping', 'listdom'); ?>
                                </label>
                            </div>
                            <div class="lsd-col-6">
                                <?php echo $template->dropdown([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_ix_csv_auto_import_mapping',
                                    'name' => 'ix[mapping]',
                                    'show_empty' => true,
                                ]); ?>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-2">
                                <label for="lsd_ix_csv_auto_import_interval" class="lsd-fields-label">
                                    <?php esc_html_e('Interval', 'listdom'); ?>
                                </label>
                            </div>
                            <div class="lsd-col-6">
                                <select name="ix[interval]" id="lsd_ix_csv_auto_import_interval" class="lsd-admin-input">
                                    <option value="weekly"><?php esc_html_e('Weekly', 'listdom'); ?></option>
                                    <option value="daily"><?php esc_html_e('Daily', 'listdom'); ?></option>
                                    <option value="twicedaily"><?php esc_html_e('Twice a Day', 'listdom'); ?></option>
                                    <option value="hourly"><?php esc_html_e('Hourly', 'listdom'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-2">
                                <label for="lsd_ix_csv_auto_import_size" class="lsd-fields-label">
                                    <?php esc_html_e('Import Size', 'listdom'); ?>
                                </label>
                            </div>
                            <div class="lsd-col-6">
                                <input class="lsd-admin-input" type="number" name="ix[size]" id="lsd_ix_csv_auto_import_size" min="10" max="300" step="1" value="100">
                                <p class="lsd-admin-description-tiny lsd-mt-2"><?php esc_html_e("It determines the maximum number of listings imported in each run of the import job.", 'listdom'); ?></p>
                            </div>
                        </div>
                        <div class="lsd-form-row lsd-settings-submit-wrapper">
                            <div class="lsd-col-12">
                                <?php LSD_Form::nonce('lsdaddcsv_auto_add'); ?>
                                <button class="lsd-primary-button" type="submit">
                                    <?php esc_html_e('Add Import Job', 'listdom'); ?>
                                    <i class="listdom-icon lsdi-checkmark-circle"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <?php if (count($jobs->all())): ?>
                <?php echo $jobs->manage(); ?>
            <?php endif; ?>
        </div>
    </div>
    <div id="lsd_panel_csv_mapping-templates" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'mapping-templates' ? ' lsd-tab-content-active' : ''; ?>">
        <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Field Mapping Templates', 'listdom'); ?></h3>
        <div class="lsd-settings-group-wrapper">
            <div class="lsd-settings-fields-wrapper">
                <?php echo $template->manage('lsd_csv_template_remove'); ?>
            </div>
        </div>
    </div>
    <div id="lsd_panel_csv_export" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'export' ? ' lsd-tab-content-active' : ''; ?>">
        <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Export', 'listdom'); ?></h3>
        <div class="lsd-settings-group-wrapper">
            <div class="lsd-settings-fields-wrapper">
                <p class="lsd-admin-description lsd-mb-1 lsd-mt-0"><?php esc_html_e("Please click the button below to download the CSV export of your listings.", 'listdom'); ?></p>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=listdom-ix&lsd-export=csv'), 'lsd_ix_form')); ?>" class="lsd-primary-button"><?php esc_html_e('Export', 'listdom'); ?></a>
            </div>
        </div>
    </div>
</div>
<script>
// Manual File Upload
jQuery('#lsd_ix_csv_import_file_input').on('change', function()
{
    let fd = new FormData();

    fd.append('action', 'lsd_ix_csv_upload');
    fd.append('_wpnonce', '<?php echo wp_create_nonce('lsd_ix_csv_upload'); ?>');
    fd.append('file', jQuery(this).prop('files')[0]);

    const $mapping = jQuery("#lsd_ix_csv_import_mapping");
    const $file = jQuery('#lsd_ix_csv_import_file_input');

    // Mapping Form
    $mapping.html('');

    // Loading Wrapper
    const loading = (new ListdomButtonLoader(jQuery('.lsd-csv-import-choose-file')));
    $file.attr('disabled', 'disabled');
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
            jQuery("#lsd_ix_csv_import_file").val(response.data.file);
            jQuery(".lsd-settings-submit-wrapper").removeClass('lsd-util-hide');

            loading.stop();
            $file.removeAttr('disabled');

            // Render Mapping Form
            $mapping.show().html(response.output);
            listdom_trigger_toggle();

            // Template Name Handler
            const $toggle = jQuery('#lsd_csv_template_name_toggle');
            const $templateInput = jQuery('#ix_template_new');
            const $templateSelect = jQuery('#lsd_ix_template');
            const defaultName = $templateInput.val();

            const lsdTemplateName = function()
            {
                if ($toggle.is(':checked'))
                {
                    // Restore default/new name
                    if(!$templateInput.val()) $templateInput.val(defaultName);
                }
                else
                {
                    // Use selected template name
                    const name = $templateSelect.find('option:selected').text();
                    $templateInput.val(name !== '-----' ? name : '');
                }
            };

            $toggle.on('change', lsdTemplateName);
            $templateSelect.on('change', function(){
                if(!$toggle.is(':checked')) lsdTemplateName();
            });
            lsdTemplateName();

            // Show Alert
            listdom_toastify(response.message, 'lsd-success');
        }
        else
        {
            jQuery("#lsd_ix_csv_import_input").val('');

            loading.stop();
            $file.removeAttr('disabled');

            // Show Alert
            listdom_toastify(response.message, 'lsd-error');
        }
    });
});

// Auto Import Add
jQuery('#lsd_ix_csv_auto_import_form').on('submit', function(e)
{
    e.preventDefault();

    const $form = jQuery(this);
    const $button = $form.find(jQuery('button'));

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Adding Import Job', 'listdom') ); ?>");

    const data = $form.serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsdaddcsv_auto_add&" + data,
        dataType: 'json',
        success: function(response)
        {
            loading.stop();

            // Show Alert
            listdom_toastify(response.message, response.success === 1 ? 'lsd-success' : 'lsd-error');

            // Update Jobs Section
            if (response.success === 1 && response.content)
            {
                $form.trigger("reset");

                const $existing = jQuery('.lsd-ix-auto-import-existing-jobs-wrapper');
                if ($existing.length)
                {
                    $existing.replaceWith(response.content);
                }
                else
                {
                    jQuery('#lsd_panel_csv_auto-import .lsd-settings-group-wrapper').append('<div class="lsd-settings-fields-wrapper">' + response.content + '</div>');
                }

                // Update templates section
                if (response.templates)
                {
                    jQuery('#lsd_panel_csv_mapping-templates .lsd-settings-fields-wrapper').html(response.templates);
                }
            }
        },
        error: function()
        {
            loading.stop();

            // Show Alert
            listdom_toastify("<?php echo esc_js(esc_html__('An error occurred! Most probably maximum execution time reached so try increasing the maximum execution time of your server.', 'listdom')); ?>", 'lsd-error');
        }
    });
});

// Manual Import Submit
jQuery('#lsd_ix_csv_import_form').on('submit', function(e)
{
    e.preventDefault();

    const $button = jQuery('#lsd_ix_csv_import_submit');
    const $alert = jQuery("#lsd_ix_csv_import_message");
    const $mapping = jQuery("#lsd_ix_csv_import_mapping");
    const $input = jQuery("#lsd_ix_csv_import_file_input");
    const $offset = jQuery("#lsd_ix_csv_import_offset");
    const $size = jQuery("#lsd_ix_csv_import_size");
    const $form = jQuery(this);

    // Hide Elements
    $mapping.hide();
    $input.hide();

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Importing', 'listdom') ); ?>");

    $alert.html('');
    $alert.removeClass('lsd-util-hide');

    // Start the Import
    lsd_csv_chunk_import();

    // Import Function
    function lsd_csv_chunk_import()
    {
        const data = $form.serialize();
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
                    if(response.templates) jQuery('#lsd_panel_csv_mapping-templates .lsd-settings-fields-wrapper').html(response.templates);
                    if(response.dropdown) jQuery('#lsd_ix_csv_auto_import_mapping').replaceWith(response.dropdown);

                    if(response.done)
                    {
                        // Show Alert
                        listdom_toastify(response.message, 'lsd-success');
                        $alert.addClass('lsd-util-hide');

                        listdom_trigger_toggle();

                        loading.stop();
                        $form.find(".lsd-settings-submit-wrapper").addClass('lsd-util-hide');

                        // Reset file input and hidden value
                        $input.val('');
                        jQuery("#lsd_ix_csv_import_file").val('');
                        $offset.val('0');

                        // Show form elements for next import
                        $input.show();
                        $mapping.html('').show();
                    }
                    else
                    {
                        // Show Alert
                        $alert.html(listdom_alertify(response.message, 'lsd-info'));

                        // New Offset
                        $offset.val(parseInt($offset.val()) + parseInt($size.val()));

                        loading.stop();
                        $form.find(".lsd-settings-submit-wrapper").addClass('lsd-util-hide');

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
                    listdom_toastify(response.message, 'lsd-error');

                    loading.stop();

                    // Show Mapping Form
                    setTimeout(function()
                    {
                        $input.show();
                        $mapping.show();
                    }, 3000);
                }
            },
            error: function()
            {
                // Show Alert
                listdom_toastify("<?php echo esc_js(esc_html__('An error occurred! Most probably maximum execution time reached so try increasing the maximum execution time of your server.', 'listdom')); ?>", 'lsd-error');

                loading.stop();

                // Show Mapping Form
                setTimeout(function()
                {
                    $mapping.show();
                }, 3000);
            }
        });
    }
});
</script>
