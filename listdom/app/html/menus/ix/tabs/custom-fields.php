<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $f_fields */
/** @var array $field_samples */
/** @var array $custom_fields_config */

$custom_fields_config = isset($custom_fields_config) && is_array($custom_fields_config) ? $custom_fields_config : [];
$custom_field_types = LSD_IX_Custom_Fields::TYPES;
$custom_field_suggestions = [];

foreach ($f_fields as $field_index => $field_label)
{
    $custom_field_suggestions[$field_index] = LSD_IX_Custom_Fields::type_suggestion($field_samples[$field_index] ?? []);
}

$custom_fields_id = isset($custom_fields_config['id']) ? sanitize_key($custom_fields_config['id']) : 'lsd_ix_custom_fields';
$custom_fields_form = isset($custom_fields_config['form']) ? sanitize_key($custom_fields_config['form']) : '';
$custom_fields_action = isset($custom_fields_config['action']) ? sanitize_key($custom_fields_config['action']) : '';
$custom_fields_nonce = $custom_fields_config['nonce'] ?? '';
$custom_fields_mapping = isset($custom_fields_config['mapping']) ? sanitize_key($custom_fields_config['mapping']) : '';
?>
<div class="lsd-settings-fields-wrapper lsd-ix-custom-fields" id="<?php echo esc_attr($custom_fields_id); ?>">
    <div class="lsd-ix-options-wrap">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Custom Fields', 'listdom'); ?></h3>
            <p class="lsd-m-0 lsd-admin-description"><?php esc_html_e('Create custom fields from source columns before importing listings. Existing matching fields are reused without changing their configuration.', 'listdom'); ?></p>
        </div>
        <div class="lsd-ix-custom-fields-message" aria-live="polite"></div>
        <div class="lsd-ix-custom-fields-rows"></div>
        <p class="lsd-admin-description-tiny lsd-mb-0"><?php esc_html_e('Field types are suggested from sample values. Review every field before creating it.', 'listdom'); ?></p>
        <div class="lsd-flex lsd-gap-2 lsd-flex-content-between">
            <div class="lsd-flex lsd-gap-2">
                <button type="button" class="lsd-secondary-button lsd-ix-custom-field-add"><?php esc_html_e('Add Custom Field', 'listdom'); ?></button>
                <button type="button" class="lsd-primary-button lsd-ix-custom-fields-provision"><?php esc_html_e('Create Custom Fields and Continue', 'listdom'); ?></button>
            </div>
            <div>
                <button type="button" class="lsd-secondary-button lsd-ix-custom-fields-skip"><?php esc_html_e('Skip to Mapping', 'listdom'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/html" id="<?php echo esc_attr($custom_fields_id); ?>_template">
    <div class="lsd-ix-custom-field lsd-settings-fields-wrapper">
        <div class="lsd-row lsd-gap-2 lsd-flex-items-end">
            <div class="lsd-col-3">
                <label class="lsd-fields-label"><?php esc_html_e('Source Column', 'listdom'); ?></label>
                <select class="lsd-admin-input lsd-ix-custom-field-column" name="ix[custom_fields][__index__][column]">
                    <option value=""><?php esc_html_e('Select a column', 'listdom'); ?></option>
                    <?php foreach ($f_fields as $field_index => $field_label): ?>
                    <option value="<?php echo esc_attr($field_index); ?>" data-label="<?php echo esc_attr($field_label); ?>"><?php echo esc_html(sprintf('#%d — %s', ((int) $field_index) + 1, $field_label)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="lsd-col-3">
                <label class="lsd-fields-label"><?php esc_html_e('Field Name', 'listdom'); ?></label>
                <input type="text" class="lsd-admin-input lsd-ix-custom-field-name" name="ix[custom_fields][__index__][name]">
            </div>
            <div class="lsd-col-2">
                <label class="lsd-fields-label"><?php esc_html_e('Slug', 'listdom'); ?></label>
                <input type="text" class="lsd-admin-input" name="ix[custom_fields][__index__][slug]" placeholder="<?php esc_attr_e('Optional', 'listdom'); ?>">
            </div>
            <div class="lsd-col-2">
                <label class="lsd-fields-label"><?php esc_html_e('Field Type', 'listdom'); ?></label>
                <select class="lsd-admin-input lsd-ix-custom-field-type" name="ix[custom_fields][__index__][field_type]">
                    <?php foreach ($custom_field_types as $custom_field_type): ?>
                    <option value="<?php echo esc_attr($custom_field_type); ?>"><?php echo esc_html(ucwords(str_replace('_', ' ', $custom_field_type))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="lsd-col-1 lsd-flex lsd-flex-content-end">
                <button type="button" class="lsd-secondary-button lsd-ix-custom-field-remove" aria-label="<?php esc_attr_e('Remove custom field', 'listdom'); ?>" title="<?php esc_attr_e('Remove custom field', 'listdom'); ?>">
                    <i class="lsd-icon fas fa-trash-alt" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="lsd-row lsd-ix-custom-field-values-wrap lsd-util-hide">
            <div class="lsd-col-12">
                <label class="lsd-fields-label"><?php esc_html_e('Allowed Values', 'listdom'); ?></label>
                <input type="text" class="lsd-admin-input lsd-ix-custom-field-values" name="ix[custom_fields][__index__][values]" placeholder="<?php esc_attr_e('Comma-separated values', 'listdom'); ?>">
            </div>
        </div>
    </div>
</script>

<script>
(function ($)
{
    const $root = $('#<?php echo esc_js($custom_fields_id); ?>');
    const $rows = $root.find('.lsd-ix-custom-fields-rows');
    const suggestions = <?php echo wp_json_encode($custom_field_suggestions); ?>;
    let index = 0;

    const choiceTypes = ['dropdown', 'radio', 'checkbox'];

    function replaceMapping(markup)
    {
        $('#<?php echo esc_js($custom_fields_mapping); ?>').html(markup);
        if (typeof listdom_trigger_toggle === 'function') listdom_trigger_toggle();
    }

    function updateRow($row)
    {
        const column = $row.find('.lsd-ix-custom-field-column').val();
        const suggestion = suggestions[column] || {type: 'text', values: []};
        const $type = $row.find('.lsd-ix-custom-field-type');
        const $values = $row.find('.lsd-ix-custom-field-values');
        const $valuesWrap = $row.find('.lsd-ix-custom-field-values-wrap');

        $type.val(suggestion.type || 'text');
        $values.val((suggestion.values || []).join(','));
        $valuesWrap.toggleClass('lsd-util-hide', choiceTypes.indexOf($type.val()) === -1);

        const $name = $row.find('.lsd-ix-custom-field-name');
        const name = $.trim($name.val());
        const automaticName = $name.data('automatic-name');
        const selectedName = $row.find('.lsd-ix-custom-field-column option:selected').data('label') || '';
        if (!name || name === automaticName)
        {
            $name.val(selectedName);
            $name.data('automatic-name', selectedName);
        }
    }

    $root.on('click', '.lsd-ix-custom-field-add', function ()
    {
        const template = $('#<?php echo esc_js($custom_fields_id); ?>_template').html().replace(/__index__/g, index++);
        $rows.append(template);
    });

    $root.on('click', '.lsd-ix-custom-field-remove', function ()
    {
        $(this).closest('.lsd-ix-custom-field').remove();
    });

    $root.on('change', '.lsd-ix-custom-field-column', function ()
    {
        updateRow($(this).closest('.lsd-ix-custom-field'));
    });

    $root.on('change', '.lsd-ix-custom-field-type', function ()
    {
        const $row = $(this).closest('.lsd-ix-custom-field');
        $row.find('.lsd-ix-custom-field-values-wrap').toggleClass('lsd-util-hide', choiceTypes.indexOf($(this).val()) === -1);
    });

    $root.on('click', '.lsd-ix-custom-fields-skip', function ()
    {
        $root.hide();
    });

    $root.on('click', '.lsd-ix-custom-fields-provision', function ()
    {
        const $button = $(this);
        const $form = $('#<?php echo esc_js($custom_fields_form); ?>');
        const $message = $root.find('.lsd-ix-custom-fields-message');

        if (!$rows.find('.lsd-ix-custom-field').length)
        {
            $message.text('<?php echo esc_js(esc_html__('Add at least one custom field before continuing.', 'listdom')); ?>');
            return;
        }

        const loading = new ListdomButtonLoader($button);
        loading.start('<?php echo esc_js(esc_html__('Creating', 'listdom')); ?>');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: $form.serialize() + '&action=<?php echo esc_js($custom_fields_action); ?>&_wpnonce=<?php echo esc_js($custom_fields_nonce); ?>',
            dataType: 'json',
            success: function (response)
            {
                loading.stop();

                const errors = Array.isArray(response.errors) ? response.errors : [];
                const warnings = Array.isArray(response.warnings) ? response.warnings : [];
                const notices = errors.concat(warnings);

                if (response.success === 1)
                {
                    replaceMapping(response.output);
                    listdom_toastify([response.message].concat(warnings).filter(Boolean).join(' '), warnings.length ? 'lsd-warning' : 'lsd-success');
                    return;
                }

                if (response.output)
                {
                    replaceMapping(response.output);
                    listdom_toastify([response.message].concat(notices).filter(Boolean).join(' '), 'lsd-warning');
                    return;
                }

                $message.text(notices.length ? notices.join(' ') : (response.message || ''));
            },
            error: function ()
            {
                loading.stop();
                $message.text('<?php echo esc_js(esc_html__('An error occurred while creating custom fields.', 'listdom')); ?>');
            }
        });
    });
})(jQuery);
</script>
