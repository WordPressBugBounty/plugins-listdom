<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $file */
/** @var LSD_Menus_IX_CSV $this */

// Main Library
$main = new LSD_Main();
$csv = LSD_Options::addons('csv');
$method = $csv['method'] ?? 'load-template';

// Feed Path / URL
$path = $main->get_upload_path().$file;
$url = $main->get_upload_url().$file;

// Mapping Library
$mapping = new LSD_IX_Mapping();
$f_fields = $mapping->feed_fields($path);

// Templates Library
$template = new LSD_IX_Templates_CSV();

// AI
$ai = new \LSD_AI();

$auto_mapping = true;
if (!$ai->has_access(LSD_AI::TASK_MAPPING))
{
    $method = 'load-template';
    $auto_mapping = false;
}
?>
<div class="lsd-settings-fields-wrapper">
    <div class="lsd-ix-mapping-wrap">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Mapping', 'listdom'); ?></h3>
            <p class="lsd-m-0 lsd-admin-description"><?php echo sprintf(
                /* translators: %s: Link to the imported file name. */
                esc_html__("You're importing %s file. If you have already saved some mapping templates, you can apply them by selecting from the list or use th smart mapping.", 'listdom'),
                '<a href="'.$url.'" target="_blank"><strong>'.$file.'</strong></a>'
            ); ?></p>
        </div>
        <div class="lsd-radio-toggle">
            <input class="lsd-toggle" data-for="#lsd_addons_csv_load-template" data-all=".lsd-addons-csv-method-wrapper" type="radio" name="addons[csv][method]" value="load-template" id="lsd_addons_csv_method_load-template" <?php echo $method === 'load-template' ? 'checked="checked"' : ''; ?>>
            <label for="lsd_addons_csv_method_load-template"><?php esc_html_e('Load Template', 'listdom'); ?></label>
            <input class="lsd-toggle" data-for="#lsd_addons_csv_smart-mapping" data-all=".lsd-addons-csv-method-wrapper" type="radio" name="addons[csv][method]" value="smart-mapping" id="lsd_addons_csv_method_smart-mapping" <?php echo $method === 'smart-mapping' ? 'checked="checked"' : ''; ?>>
            <label for="lsd_addons_csv_method_smart-mapping"><?php esc_html_e('Smart Mappings', 'listdom'); ?></label>
        </div>

        <div class="lsd-ix-mapping-template lsd-row">
            <div class="lsd-addons-csv-method-wrapper <?php echo $method !== 'load-template' ? 'lsd-util-hide' : ''; ?>" id="lsd_addons_csv_load-template">
                <div class="lsd-col-3">
                    <?php echo LSD_Form::label([
                        'for' => 'lsd_ix_template',
                        'title' => esc_html__("Select the Template", 'listdom'),
                        'class' => 'lsd-d-block lsd-fields-label'
                    ]); ?>
                </div>
                <div class="lsd-col-6">
                    <?php echo $template->dropdown([
                        'id' => 'lsd_ix_template',
                        'class' => 'lsd-admin-input',
                        'show_empty' => true,
                    ]); ?>
                </div>
                <button id="lsd_ix_csv_load_template_map" data-file="<?php echo esc_attr($file); ?>" type="button" class="lsd-secondary-button"><?php esc_html_e('Map', 'listdom'); ?><i class="listdom-icon lsdi-right-arrow"></i></button>
            </div>

            <div class="lsd-addons-csv-method-wrapper <?php echo $method !== 'smart-mapping' ? 'lsd-util-hide' : ''; ?>" id="lsd_addons_csv_smart-mapping">
                <div class="lsd-col-3">
                    <?php echo LSD_Form::label([
                        'for' => 'ix_ai_profile',
                        'title' => esc_html__("Select AI Profile", 'listdom'),
                        'class' => 'lsd-d-block lsd-fields-label'
                    ]); ?>
                </div>
                <div class="lsd-flex lsd-flex-col lsd-gap-1">
                    <div class="lsd-col-12 lsd-flex lsd-flex-items-center lsd-gap-3">
                        <?php if ($auto_mapping): ?>
                            <div class="lsd-col-9">
                                <?php echo LSD_Form::ai_profiles([
                                    'id' => 'ix_ai_profile',
                                        'class' => 'lsd-admin-input',
                                    'name' => 'ix[ai_profile]',
                                ]); ?>
                            </div>
                        <?php endif; ?>
                        <button id="lsd_ix_csv_auto_map" data-file="<?php echo esc_attr($file); ?>" type="button" class="lsd-secondary-button"><?php esc_html_e('Map', 'listdom'); ?><i class="listdom-icon lsdi-right-arrow"></i></button>
                    </div>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('You can use the auto-map feature powered by AI. You need to configure your AI models first in Listdom settings.', 'listdom');?></p>
                </div>
            </div>
        </div>

        <div class="lsd-ix-mapping-fields-wrap">
            <table class="lsd-admin-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Listdom Field', 'listdom'); ?></th>
                        <th><?php esc_html_e('Type', 'listdom'); ?></th>
                        <th>
                            <div class="lsd-th-icon-wrapper">
                                <?php esc_html_e('Mapping', 'listdom'); ?>
                                <span class="lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Map as many CSV fields as possible, but only map correct ones to avoid corrupted data after import.', 'listdom'); ?>">
                                    <i class="far fa-question-circle listdom-icon"></i>
                                </span>
                            </div>
                        </th>
                        <th>
                            <div class="lsd-th-icon-wrapper">
                                <?php esc_html_e('Default Value', 'listdom'); ?>
                                <span class="lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('If your CSV feed lacks certain fields, you can assign default values (e.g., set currency to USD if not provided).', 'listdom'); ?>">
                                    <i class="far fa-question-circle listdom-icon"></i>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mapping->listdom_fields() as $key => $l_field): ?>
                    <tr class="lsd-ix-mapping-field" id="lsd_ix_mapping_field_<?php echo esc_attr($key); ?>">
                        <td class="lsd-ix-mapping-field-name-col">
                            <div class="lsd-ix-mapping-field-name lsd-admin-table-body-title">
                                <?php echo $l_field['label'] ?? 'N/A'; ?> <?php echo isset($l_field['mandatory']) && $l_field['mandatory'] ? '<span class="required">*</span>' : ''; ?>
                            </div>
                            <?php echo isset($l_field['description']) ? '<p class="lsd-admin-table-body">'.$l_field['description'].'</p>' : ''; ?>
                        </td>
                        <td class="lsd-ix-mapping-field-type-col lsd-admin-table-body"><?php echo isset($l_field['type']) ? ucfirst($l_field['type']) : ''; ?></td>
                        <td class="lsd-ix-mapping-field-map-col">
                            <select class="lsd-admin-input" id="lsd_ix_mapping_field_<?php echo esc_attr($key); ?>_map" name="ix[mapping][<?php echo esc_attr($key); ?>][map]" title="<?php esc_attr_e('Map', 'listdom'); ?>">
                                <option value="">-----</option>
                                <?php foreach ($f_fields as $f_key => $f_field): ?>
                                <option value="<?php echo esc_attr($f_key); ?>" <?php echo isset($mapping_ai[$key]) && $mapping_ai[$key] == $f_key ? 'selected' : ''; ?>><?php echo esc_html($f_field); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td class="lsd-ix-mapping-field-default-col">
                            <?php if (isset($l_field['default']) && $l_field['default'] && is_callable($l_field['default'])) call_user_func($l_field['default'], [
                                'key' => $key,
                                'field' => $l_field,
                                'class' => 'lsd-admin-input',
                                'name' => 'ix[mapping]['.$key.'][default]',
                            ]); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
// Template Change
jQuery('#lsd_ix_csv_load_template_map').on('click', function ()
{
    const template = jQuery('#lsd_ix_template').val();
    const $button = jQuery(this);
    const $submit = jQuery("#lsd_ix_csv_import_submit");

    // Disable New Template
    jQuery('#ix_template_new').val('');

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Mapping', 'listdom') ); ?>");

    // Disable Button
    $submit.attr('disabled', 'disabled');

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_ix_csv_load_template&template=" + template + "&_wpnonce=<?php echo wp_create_nonce('lsd_ix_csv_load_template'); ?>",
        dataType: "json",
        success: function (response)
        {
            loading.stop();

            // Enable Button
            $submit.removeAttr('disabled');

            if (response.success === 1)
            {
                const template = response.template;
                for (const key in template)
                {
                    if (template.hasOwnProperty(key))
                    {
                        let mapping = template[key];

                        jQuery('#lsd_ix_mapping_field_'+key+'_map').val(mapping.map);
                        jQuery('#lsd_ix_mapping_field_'+key+'_default').val(mapping.default);
                    }
                }

                listdom_toastify("<?php echo esc_js(esc_html__('Template mapping was successful', 'listdom')) ?>", 'lsd-success');

                jQuery('#lsd_csv_template_name_toggle').trigger('change');
            }
        },
        error: function ()
        {
            loading.stop();
            // Enable Button
            $submit.removeAttr('disabled');
        }
    });
});

// AI Mapping
jQuery('#lsd_ix_csv_auto_map').on('click', function ()
{
    const $button = jQuery(this);
    const $submit = jQuery("#lsd_ix_csv_import_submit");
    const file = $button.data('file');
    const ai_profile = jQuery("#ix_ai_profile").val();

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Mapping', 'listdom') ); ?>");

    // Disable Buttons
    $submit.attr('disabled', 'disabled');

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_ix_csv_ai_mapping&file=" + file + "&ai_profile=" + ai_profile + "&_wpnonce=<?php echo wp_create_nonce('lsd_ix_csv_ai_mapping'); ?>",
        dataType: "json",
        success: function (response)
        {
            loading.stop();
            $submit.removeAttr('disabled');

            if (response.success === 1)
            {
                const template = response.template;
                for (const key in template)
                {
                    if (template.hasOwnProperty(key))
                    {
                        let mapping = template[key];
                        jQuery('#lsd_ix_mapping_field_'+key+'_map').val(mapping);
                    }
                }

                listdom_toastify(response.message, 'lsd-success');
            }
            else
            {
                listdom_toastify(response.message, 'lsd-warning');
            }
        },
        error: function ()
        {
            loading.stop();
            $submit.removeAttr('disabled');
        }
    });
});
</script>
