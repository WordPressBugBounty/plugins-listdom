<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Template $this */
/** @var int|string $id */
/** @var array  $field */
/** @var string $type */
/** @var mixed  $value */

$field_label = isset($field['label']) ? (string) $field['label'] : '';
$description = isset($field['description']) ? (string) $field['description'] : '';
$default = $field['default'] ?? null;
$responsive = !empty($field['responsive']);

$id = (string) $id;
$type = isset($type) ? (string) $type : 'text';

$condition_attrs = $this->control_wrapper_attrs($id, $field);
$base = $this->template_field_base_args($id, $field, $value, $default);
$is_iconset = $this->template_field_is_iconset($type);

if ($type === 'repeater')
{
    $items = $this->template_field_repeater_items($value, $default);
    $subfields = isset($field['fields']) && is_array($field['fields']) ? $field['fields'] : [];
    $base_name = $this->template_field_repeater_base_name($id);
    $base_id = $this->template_field_repeater_base_id($id);
    ?>
    <div class="lsd-template-editor-settings-field"<?php echo $condition_attrs; ?>>
        <?php echo $this->template_field_label_html($field_label); ?>

        <div
            class="lsd-template-editor-repeater"
            data-lsd-repeater
            data-lsd-repeater-name="<?php echo esc_attr($base_name); ?>"
            data-lsd-repeater-id-base="<?php echo esc_attr($base_id); ?>"
        >
            <div class="lsd-template-editor-repeater__items">
                <?php foreach ($items as $index => $item_value): ?>
                    <div class="lsd-template-editor-repeater__item" data-lsd-repeater-item>
                        <div class="lsd-template-editor-repeater__fields">
                            <?php foreach ($subfields as $subfield): ?>
                                <?php echo $this->template_field_repeater_subfield_html($subfield, $item_value, (int) $index, $base_name, $base_id); ?>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" class="lsd-text-button lsd-template-editor-repeater__remove" data-lsd-repeater-remove>
                            <?php esc_html_e('Remove', 'listdom'); ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="lsd-secondary-button lsd-template-editor-repeater__add" data-lsd-repeater-add>
                <?php esc_html_e('Add Item', 'listdom'); ?>
            </button>

            <div class="lsd-template-editor-repeater__template lsd-util-hide" data-lsd-repeater-template>
                <div class="lsd-template-editor-repeater__item" data-lsd-repeater-item>
                    <div class="lsd-template-editor-repeater__fields">
                        <?php foreach ($subfields as $subfield): ?>
                            <?php echo $this->template_field_repeater_subfield_html($subfield, [], '__INDEX__', $base_name, $base_id); ?>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="lsd-text-button lsd-template-editor-repeater__remove" data-lsd-repeater-remove>
                        <?php esc_html_e('Remove', 'listdom'); ?>
                    </button>
                </div>
            </div>
        </div>

        <?php echo $this->template_field_description_html($description); ?>
    </div>
    <?php
    return;
}
?>

<div class="lsd-template-editor-settings-field"<?php echo $condition_attrs; ?>>
    <?php if ($responsive): ?>
        <?php foreach ($this->template_field_devices() as $device_key => $device_label): ?>
            <?php
            $device_context = $this->template_field_device_context($id, $base, $value, $default, $device_key);
            $device_base = $device_context['args'];
            $device_has_explicit = !empty($device_context['has_explicit_value']);
            ?>

            <div
                class="lsd-template-editor-settings-field__device"
                data-lsd-responsive-field
                data-lsd-responsive="<?php echo esc_attr($device_label); ?>"
                <?php echo $device_has_explicit ? 'data-lsd-responsive-explicit="1"' : ''; ?>
            >
                <div class="lsd-template-editor-settings-field-<?php echo esc_attr($type); ?>">
                    <?php if ($field_label !== '' && !$is_iconset): ?>
                        <?php echo $this->template_field_label_html($field_label, $device_base['id'], 'lsd-fields-label-tiny lsd-field-label-divider'); ?>
                    <?php endif; ?>

                    <?php echo $this->template_field_form_html($type, $device_base); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="lsd-template-editor-settings-field-<?php echo esc_attr($type); ?>">
            <?php if ($field_label !== '' && !$is_iconset): ?>
                <?php echo $this->template_field_label_html($field_label, $base['id']); ?>
            <?php endif; ?>

            <?php echo $this->template_field_form_html($type, $base); ?>
        </div>
    <?php endif; ?>

    <?php echo $this->template_field_description_html($description); ?>
</div>
