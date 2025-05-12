<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */
/** @var string $device */

// Device Fields
$device_fields = LSD_Search_Helper::get_search_fields($post->ID, $device);

// Add a default row
if (!count($device_fields)) $device_fields[] = ['type' => 'row', 'buttons' => 1];

// Devices
$devices = get_post_meta($post->ID, 'lsd_devices', true);

// Inherit
$inherit = $devices[$device]['inherit'] ?? 1;

// Device Key
$device_key = 'fields';
if (in_array($device, ['tablet', 'mobile'])) $device_key = $device;

// Reset Keys
$device_fields = array_values($device_fields);

$builder = new LSD_Search_Builder();
$fields = $builder->getAvailableFields($device_fields);
?>
<div class="lsd-search-fields-device lsd-search-fields-device-<?php echo esc_attr($device); ?> <?php echo $device !== 'desktop' ? 'lsd-util-hide' : ''; ?>" data-device="<?php echo esc_attr($device); ?>">
    <?php if (in_array($device, ['tablet', 'mobile'])): ?>
    <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4 lsd-my-3">
        <div><?php echo LSD_Form::switcher([
            'id' => 'lsd-devices-'.$device.'-inherit',
            'name' => 'lsd[devices]['.$device.'][inherit]',
            'toggle' => '#lsd-search-sandbox-'.$device.'-wrapper',
            'value' => $inherit
        ]); ?></div>
        <label for="<?php echo esc_attr('lsd-devices-'.$device.'-inherit'); ?>"><?php esc_html_e('Inherit from Desktop.', 'listdom'); ?></label>
    </div>
    <?php endif; ?>

    <div id="lsd-search-sandbox-<?php echo esc_attr($device); ?>-wrapper" class="lsd-row <?php echo in_array($device, ['tablet', 'mobile']) && $inherit ? 'lsd-util-hide' : ''; ?>">
        <div class="lsd-col-9 lsd-search-sandbox">
            <?php foreach($device_fields as $i => $row): $i = $i + 1; ?>
            <div class="<?php echo $row['type'] === 'more_options' ? 'lsd-search-more-options' : 'lsd-search-row'; ?>" id="lsd_<?php echo esc_attr($device_key); ?>_search_row_<?php echo esc_attr($i); ?>" data-i="<?php echo esc_attr($i); ?>">
                <?php echo $row['type'] === 'more_options' ? '<span class="lsd-search-more-options-label">'. esc_html__('Add the “More Options” fields in the row below.', 'listdom') .'</span>' : ''; ?>

                <ul class="lsd-search-row-actions">
                    <li class="lsd-search-row-actions-sort lsd-row-handler"><i class="lsd-icon fas fa-arrows-alt"></i></li>
                    <li class="lsd-search-row-actions-delete lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Click twice to delete', 'listdom'); ?>" data-confirm="0" data-i="<?php echo esc_attr($i); ?>"><i class="lsd-icon fas fa-trash-alt"></i></li>
                </ul>

                <input type="hidden" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][type]" value="<?php echo isset($row['type']) && trim($row['type']) ? $row['type'] : 'row'; ?>">

                <?php if ($row['type'] === 'row'): ?>
                <div class="lsd-search-filters">
                    <?php if (isset($row['filters']) && is_array($row['filters'])) foreach ($row['filters'] as $key => $data) echo LSD_Kses::form($builder->params($device_key, $key, $data, $i)); ?>
                </div>
                <?php endif; ?>

                <?php echo LSD_Kses::form($builder->row($post->ID, $device_key, $row, $i)); ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="lsd-col-3">
            <div class="lsd-search-available-fields">
                <h3><?php esc_html_e('Available Fields', 'listdom'); ?></h3>
                <div class="lsd-search-available-fields-container">
                    <?php foreach ($fields as $field): ?>
                        <div class="lsd-search-field" id="lsd_search_available_<?php echo esc_html($device_key); ?>_<?php echo esc_attr($field['key']); ?>" data-key="<?php echo esc_attr($field['key']); ?>">
                            <strong><?php echo esc_html($field['title']); ?></strong>
                            <?php if (isset($field['description'])): ?>
                                <p class="description lsd-mb-0"><?php echo esc_html($field['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
