<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_IX_Mapping $mapping */
/** @var array $f_fields */
/** @var array $current_mapping */

$repeaters = $mapping->repeater_fields();
if (!is_array($repeaters) || !count($repeaters)) return;
?>
<div class="lsd-settings-fields-wrapper lsd-ix-repeaters">
    <div class="lsd-ix-repeaters-wrap">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Repeatable Fields', 'listdom'); ?></h3>
            <p class="lsd-m-0 lsd-admin-description"><?php esc_html_e('Add a row for every item you want to import and map its source fields.', 'listdom'); ?></p>
        </div>
        <?php foreach ($repeaters as $repeater_key => $repeater):
            if (!is_array($repeater) || empty($repeater['fields']) || !is_array($repeater['fields'])) continue;

            $rows = isset($current_mapping[$repeater_key]) && is_array($current_mapping[$repeater_key]) ? $current_mapping[$repeater_key] : [];
            $rows = array_filter($rows, 'is_array');
            if (!count($rows)) $rows = [[]];
        ?>
        <div class="lsd-ix-repeater" data-repeater="<?php echo esc_attr($repeater_key); ?>">
            <h4 class="lsd-admin-title"><?php echo $repeater['label'] ?? esc_html($repeater_key); ?></h4>
            <?php if (!empty($repeater['description'])): ?>
                <p class="lsd-admin-description-tiny"><?php echo $repeater['description']; ?></p>
            <?php endif; ?>
            <div class="lsd-ix-repeater-rows">
                <?php foreach ($rows as $index => $row): ?>
                <div class="lsd-ix-repeater-row">
                    <?php foreach ($repeater['fields'] as $field_key => $field): ?>
                    <div class="lsd-ix-repeater-field">
                        <label class="lsd-fields-label"><?php echo $field['label'] ?? esc_html($field_key); ?><?php echo !empty($field['required']) ? ' <span class="required">*</span>' : ''; ?></label>
                        <select class="lsd-admin-input" data-repeater-field="<?php echo esc_attr($field_key); ?>" name="ix[mapping][<?php echo esc_attr($repeater_key); ?>][<?php echo esc_attr($index); ?>][<?php echo esc_attr($field_key); ?>][map]">
                            <option value="">-----</option>
                            <?php foreach ($f_fields as $f_key => $f_field): ?>
                                <option value="<?php echo esc_attr($f_key); ?>" <?php echo isset($row[$field_key]['map']) && (string) $row[$field_key]['map'] === (string) $f_key ? 'selected="selected"' : ''; ?>><?php echo esc_html($f_field); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                    <div class="lsd-ix-repeater-actions">
                        <button type="button" class="lsd-secondary-button lsd-ix-repeater-remove" aria-label="<?php esc_attr_e('Remove item', 'listdom'); ?>" title="<?php esc_attr_e('Remove item', 'listdom'); ?>">
                            <i class="lsd-icon fas fa-trash-alt" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <script type="text/html" class="lsd-ix-repeater-template">
                <div class="lsd-ix-repeater-row">
                    <?php foreach ($repeater['fields'] as $field_key => $field): ?>
                    <div class="lsd-ix-repeater-field">
                        <label class="lsd-fields-label"><?php echo $field['label'] ?? esc_html($field_key); ?><?php echo !empty($field['required']) ? ' <span class="required">*</span>' : ''; ?></label>
                        <select class="lsd-admin-input" data-repeater-field="<?php echo esc_attr($field_key); ?>" name="ix[mapping][<?php echo esc_attr($repeater_key); ?>][:index:][<?php echo esc_attr($field_key); ?>][map]">
                            <option value="">-----</option>
                            <?php foreach ($f_fields as $f_key => $f_field): ?>
                                <option value="<?php echo esc_attr($f_key); ?>"><?php echo esc_html($f_field); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                    <div class="lsd-ix-repeater-actions">
                        <button type="button" class="lsd-secondary-button lsd-ix-repeater-remove" aria-label="<?php esc_attr_e('Remove item', 'listdom'); ?>" title="<?php esc_attr_e('Remove item', 'listdom'); ?>">
                            <i class="lsd-icon fas fa-trash-alt" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </script>
            <button type="button" class="lsd-secondary-button lsd-ix-repeater-add"><?php esc_html_e('Add Item', 'listdom'); ?></button>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
window.lsd_ix_repeaters = window.lsd_ix_repeaters || {};
window.lsd_ix_repeaters.add = function($repeater, index, values)
{
    const $row = jQuery($repeater.find('.lsd-ix-repeater-template').html().replace(/:index:/g, index));
    jQuery.each(values || {}, function(key, value)
    {
        $row.find('[data-repeater-field="' + key + '"]').val(value && value.map ? value.map : '');
    });
    $repeater.find('.lsd-ix-repeater-rows').append($row);
};
window.lsd_ix_repeaters.load = function(mappings)
{
    jQuery('.lsd-ix-repeater').each(function()
    {
        const $repeater = jQuery(this);
        const key = $repeater.data('repeater');
        const rows = mappings && mappings[key] && typeof mappings[key] === 'object' ? mappings[key] : {};

        $repeater.find('.lsd-ix-repeater-rows').empty();
        if (!Object.keys(rows).length) window.lsd_ix_repeaters.add($repeater, 0, {});
        else jQuery.each(rows, function(index, values)
        {
            window.lsd_ix_repeaters.add($repeater, index, values);
        });
    });
};
jQuery(document).off('click.lsdIxRepeaters', '.lsd-ix-repeater-add').on('click.lsdIxRepeaters', '.lsd-ix-repeater-add', function()
{
    const $repeater = jQuery(this).closest('.lsd-ix-repeater');
    window.lsd_ix_repeaters.add($repeater, Date.now(), {});
});
jQuery(document).off('click.lsdIxRepeaters', '.lsd-ix-repeater-remove').on('click.lsdIxRepeaters', '.lsd-ix-repeater-remove', function()
{
    const $repeater = jQuery(this).closest('.lsd-ix-repeater');
    jQuery(this).closest('.lsd-ix-repeater-row').remove();
    if (!$repeater.find('.lsd-ix-repeater-row').length) window.lsd_ix_repeaters.add($repeater, 0, {});
});
</script>
