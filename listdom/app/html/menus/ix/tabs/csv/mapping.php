<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $file */
/** @var LSD_Menus_IX_CSV $this */

// Main Library
$main = new LSD_Main();

// Feed Path / URL
$path = $main->get_upload_path().$file;
$url = $main->get_upload_url().$file;

// Mapping Library
$mapping = new LSD_IX_Mapping();
$f_fields = $mapping->feed_fields($path);

// Templates Library
$template = new LSD_IX_Templates_CSV();
?>
<div class="lsd-ix-mapping-wrap">
    <h3><?php esc_html_e('Mapping', 'listdom'); ?></h3>
    <p><?php echo sprintf(esc_html__("You're importing %s file.", 'listdom'), '<a href="'.$url.'" target="_blank"><strong>'.$file.'</strong></a>'); ?></p>

    <div class="lsd-my-4">
        <div class="lsd-ix-mapping-guide lsd-my-3">
            <div class="lsd-alert lsd-info">
                <ul class="lsd-m-0">
                    <li><?php esc_html_e("Try to map as many fields as you can.", 'listdom'); ?></li>
                    <li><?php esc_html_e("You should only map correct fields to avoid corrupted data after import.", 'listdom'); ?></li>
                    <li><?php esc_html_e("You can insert a default value for some fields if your CSV feed does not contain a value for them. For example, select USD for currency if it's not included in your CSV feed.", 'listdom'); ?></li>
                    <li><?php esc_html_e("You can save the mapping template by inserting a name during import so you can apply the mappings later and save time.", 'listdom'); ?></li>
                    <li><?php esc_html_e("If you have already saved some mapping templates, you can apply them by selecting from the dropdown below.", 'listdom'); ?></li>
                    <li><?php esc_html_e("You can use the auto-map feature powered by AI. You need to configure your AI models first in Listdom settings.", 'listdom'); ?></li>
                </ul>
            </div>
            <div class="lsd-ix-csv-ai-mapping-message"></div>
        </div>
    </div>

    <div class="lsd-ix-mapping-template lsd-row">
        <div class="lsd-col-3">
            <?php echo LSD_Form::label([
                'for' => 'lsd_ix_template',
                'title' => esc_html__("Load Template", 'listdom'),
                'class' => 'lsd-d-block lsd-mb-3'
            ]); ?>
            <?php echo $template->dropdown([
                'id' => 'lsd_ix_template',
                'show_empty' => true,
            ]); ?>
        </div>
        <div class="lsd-col-3">
            <?php echo LSD_Form::label([
                'for' => 'ix_template_new',
                'title' => esc_html__("Mapping Name", 'listdom'),
                'class' => 'lsd-d-block lsd-mb-3'
            ]); ?>
            <?php echo LSD_Form::text([
                'name' => 'ix[template]',
                'id' => 'ix_template_new',
                'value' => sprintf(esc_html__('CSV (%s)', 'listdom'), date('Y-m-d-H-i')),
            ]); ?>
        </div>
        <div class="lsd-col-3"></div>
        <div class="lsd-col-3 lsd-flex lsd-flex-items-end lsd-flex-content-end lsd-gap-3">
            <?php if ((new LSD_AI())->has_profile()): ?>
                <div>
                    <?php echo LSD_Form::label([
                        'for' => 'ix_ai_profile',
                        'title' => esc_html__("AI Profile", 'listdom'),
                        'class' => 'lsd-d-block lsd-mb-3'
                    ]); ?>
                    <?php echo LSD_Form::ai_profiles([
                        'id' => 'ix_ai_profile',
                        'name' => 'ix[ai_profile]',
                    ]); ?>
                </div>
            <?php endif; ?>
            <button id="lsd_ix_csv_auto_map" data-file="<?php echo esc_attr($file); ?>" type="button" class="button button-primary"><i class="listdom-icon lsdi-stars lsd-mr-3"></i><?php esc_html_e('Smart Mapping', 'listdom'); ?></button>
        </div>
    </div>

    <div class="lsd-ix-mapping-fields-wrap lsd-my-4">
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Listdom Field', 'listdom'); ?></th>
                    <th><?php esc_html_e('Type', 'listdom'); ?></th>
                    <th><?php esc_html_e('Mapping', 'listdom'); ?></th>
                    <th><?php esc_html_e('Default Value', 'listdom'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mapping->listdom_fields() as $key => $l_field): ?>
                <tr class="lsd-ix-mapping-field" id="lsd_ix_mapping_field_<?php echo esc_attr($key); ?>">
                    <td class="lsd-ix-mapping-field-name-col">
                        <div class="lsd-ix-mapping-field-name">
                            <strong>
                                <?php echo $l_field['label'] ?? 'N/A'; ?> <?php echo isset($l_field['mandatory']) && $l_field['mandatory'] ? '<span class="required">*</span>' : ''; ?>
                            </strong>
                        </div>
                        <?php echo isset($l_field['description']) ? '<p class="description">'.$l_field['description'].'</p>' : ''; ?>
                    </td>
                    <td class="lsd-ix-mapping-field-type-col"><?php echo isset($l_field['type']) ? ucfirst($l_field['type']) : ''; ?></td>
                    <td class="lsd-ix-mapping-field-map-col">
                        <select id="lsd_ix_mapping_field_<?php echo esc_attr($key); ?>_map" name="ix[mapping][<?php echo esc_attr($key); ?>][map]" title="<?php esc_attr_e('Map', 'listdom'); ?>">
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
                            'name' => 'ix[mapping]['.$key.'][default]',
                        ]); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
// Template Change
jQuery('#lsd_ix_template').on('change', function ()
{
    const template = jQuery(this).val();
    const $submit = jQuery("#lsd_ix_csv_import_submit");

    // Disable New Template
    jQuery('#ix_template_new').val('');

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
            }
        },
        error: function ()
        {
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
    const $message = jQuery(".lsd-ix-csv-ai-mapping-message");
    const file = $button.data('file');
    const ai_profile = jQuery("#ix_ai_profile").val();

    // Disable Buttons
    $submit.attr('disabled', 'disabled');
    $button.attr('disabled', 'disabled');

    // Remove Message
    $message.html('');

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_ix_csv_ai_mapping&file=" + file + "&ai_profile=" + ai_profile + "&_wpnonce=<?php echo wp_create_nonce('lsd_ix_csv_ai_mapping'); ?>",
        dataType: "json",
        success: function (response)
        {
            // Enable Buttons
            $submit.removeAttr('disabled');
            $button.removeAttr('disabled');

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
            }
            else
            {
                // Display Message
                $message.html(`<div class="lsd-alert lsd-warning">${response.message}</div>`);

                // Remove Message
                setTimeout(() => $message.html(''), 5000);
            }
        },
        error: function ()
        {
            // Enable Buttons
            $submit.removeAttr('disabled');
            $button.removeAttr('disabled');
        }
    });
});
</script>
