<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Search_Helper $helper */
/** @var string $key */
/** @var int $i */
/** @var string $type */
/** @var array $methods */

$title = $data['title'] ?? '';

$placeholder = $data['placeholder'] ?? '';
$max_placeholder = $data['max_placeholder'] ?? '';

$default_value = $data['default_value'] ?? '';
$max_default_value = $data['max_default_value'] ?? '';

$radius = $data['radius'] ?? 5000;
$radius_values = $data['radius_values'] ?? '';
$radius_display_unit = $data['radius_display_unit'] ?? 'm';
$radius_display = $data['radius_display'] ?? 0;
$hide_empty = $data['hide_empty'] ?? 1;
$display_all_terms = $data['all_terms'] ?? 1;
$terms = (isset($data['terms']) and is_array($data['terms'])) ? $data['terms'] : [];

$min = $data['min'] ?? 0;
$max = $data['max'] ?? 100;
$increment = $data['increment'] ?? 10;
$th_separator = $data['th_separator'] ?? 1;
?>
<div class="lsd-search-field" id="lsd_search_field_<?php echo esc_attr($i); ?>_<?php echo esc_attr($key); ?>" data-row="<?php echo esc_attr($i); ?>" data-key="<?php echo esc_attr($key); ?>">
    <ul class="lsd-search-field-actions">
        <li class="lsd-search-field-actions-sort lsd-field-handler" data-key="<?php echo esc_attr($key); ?>"><i class="lsd-icon fas fa-arrows-alt"></i></li>
        <li class="lsd-search-field-actions-delete lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Click twice to delete', 'listdom'); ?>" data-confirm="0" data-i="<?php echo esc_attr($i); ?>" data-key="<?php echo esc_attr($key); ?>"><i class="lsd-icon fas fa-trash-alt"></i></li>
    </ul>
    <div class="lsd-row">
        <div class="lsd-col-9">
            <h4><?php echo esc_html($title); ?></h4>
        </div>
    </div>
    <div class="lsd-row">
        <div class="lsd-col-12">

            <input type="hidden" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][key]" value="<?php echo esc_attr($key); ?>">
            <div class="lsd-search-field-param">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_title"><?php esc_html_e('Title', 'listdom'); ?></label>
                <input class="widefat" id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_title" type="text" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][title]" value="<?php echo esc_attr($title); ?>" placeholder="<?php esc_attr_e('Field Title', 'listdom'); ?>">
            </div>
            <div class="lsd-search-field-param">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_method"><?php esc_html_e('Method', 'listdom'); ?></label>
                <select class="widefat lsd-search-method" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][method]" title="<?php esc_attr_e('Display Method', 'listdom'); ?>">
                    <?php foreach($methods as $method => $method_title): ?>
                    <option value="<?php echo esc_attr($method); ?>" <?php echo isset($data['method']) && $data['method'] === $method ? 'selected="selected"' : ''; ?>><?php echo esc_html($method_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if($type === 'taxonomy' or strpos($key, 'att') !== false): ?>
            <div class="lsd-search-field-param">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_hide_empty"><?php esc_html_e('Hide Empty Terms', 'listdom'); ?></label>
                <select class="widefat" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][hide_empty]" title="<?php esc_attr_e('Only display those terms that have listings', 'listdom'); ?>">
                    <option value="1" <?php echo ($hide_empty == 1 ? 'selected="selected"' : ''); ?>><?php echo esc_html__('Yes', 'listdom'); ?></option>
                    <option value="0" <?php echo ($hide_empty == 0 ? 'selected="selected"' : ''); ?>><?php echo esc_html__('No', 'listdom'); ?></option>
                </select>
            </div>
            <?php endif; ?>

            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-text-input lsd-search-method-number-input lsd-search-method-dropdown lsd-search-method-dropdown-multiple lsd-search-method-dropdown-plus lsd-search-method-mm-input lsd-search-method-hierarchical lsd-search-method-radius lsd-search-method-date-range-picker">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_placeholder"><?php esc_html_e('Placeholder', 'listdom'); ?></label>
                <input class="widefat" id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_placeholder" type="text" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][placeholder]" value="<?php echo esc_attr($placeholder); ?>" placeholder="<?php esc_attr_e('Optional Placeholder', 'listdom'); ?>">
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-mm-input">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_max_placeholder"><?php esc_html_e('Max Placeholder', 'listdom'); ?></label>
                <input class="widefat" id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_max_placeholder" type="text" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][max_placeholder]" value="<?php echo esc_attr($max_placeholder); ?>" placeholder="<?php esc_attr_e('Optional Max Placeholder', 'listdom'); ?>">
            </div>

            <div class="lsd-search-field-param">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_default_value"><?php esc_html_e('Default Value', 'listdom'); ?></label>
                <input class="widefat" id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_default_value" type="text" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][default_value]" value="<?php echo esc_attr($default_value); ?>" placeholder="<?php esc_attr_e('Optional', 'listdom'); ?>">
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-mm-input">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_max_default_value"><?php esc_html_e('Max Default Value', 'listdom'); ?></label>
                <input class="widefat" id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_max_default_value" type="text" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][max_default_value]" value="<?php echo esc_attr($max_default_value); ?>" placeholder="<?php esc_attr_e('Optional', 'listdom'); ?>">
            </div>

            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-radius">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius"><?php esc_html_e('Radius (Meters)', 'listdom'); ?></label>
                <input class="widefat" id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius" type="text" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][radius]" value="<?php echo esc_attr($radius); ?>" placeholder="<?php esc_attr_e('5000 means 5 KM', 'listdom'); ?>">
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-radius-dropdown">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_values"><?php esc_html_e('Radius Values (Meters)', 'listdom'); ?></label>
                <input class="widefat" id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_values" type="text" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][radius_values]" value="<?php echo esc_attr($radius_values); ?>" placeholder="<?php esc_attr_e('100,200,500,1000,2000', 'listdom'); ?>">
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-radius-dropdown">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_display_unit"><?php esc_html_e('Radius Display Unit', 'listdom'); ?></label>
                <select class="widefat" id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_display_unit" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][radius_display_unit]">
                    <option value="m" <?php echo ($radius_display_unit === 'm' ? 'selected' : ''); ?>><?php esc_html_e('Meters', 'listdom'); ?></option>
                    <option value="km" <?php echo ($radius_display_unit === 'km' ? 'selected' : ''); ?>><?php esc_html_e('Kilo Meters (KM)', 'listdom'); ?></option>
                    <option value="mile" <?php echo ($radius_display_unit === 'mile' ? 'selected' : ''); ?>><?php esc_html_e('Miles', 'listdom'); ?></option>
                </select>
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-radius">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_display"><?php esc_html_e('Display Radius Field', 'listdom'); ?></label>
                <select class="widefat" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][radius_display]" id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_display">
                    <option value="1" <?php echo ($radius_display == 1 ? 'selected="selected"' : ''); ?>><?php echo esc_html__('Yes', 'listdom'); ?></option>
                    <option value="0" <?php echo ($radius_display == 0 ? 'selected="selected"' : ''); ?>><?php echo esc_html__('No', 'listdom'); ?></option>
                </select>
            </div>

            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-dropdown-plus">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_min"><?php esc_html_e('Min / Max / Increment', 'listdom'); ?></label>
                <div class="lsd-input-group">
                    <input id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_min" type="number" min="0" step="0.01" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][min]" value="<?php echo esc_attr($min); ?>" title="<?php esc_attr_e('Min', 'listdom'); ?>">
                    <input id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_max" type="number" min="0" step="0.01" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][max]" value="<?php echo esc_attr($max); ?>" title="<?php esc_attr_e('Max', 'listdom'); ?>">
                    <input id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_increment" type="number" min="0" step="0.01" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][increment]" value="<?php echo esc_attr($increment); ?>" title="<?php esc_attr_e('Increment', 'listdom'); ?>">
                </div>
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-dropdown-plus">
                <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_th_separator"><?php esc_html_e('Thousands Separator', 'listdom'); ?></label>
                <select class="widefat" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][th_separator]" id="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_th_separator">
                    <option value="1" <?php echo ($th_separator == 1 ? 'selected="selected"' : ''); ?>><?php echo esc_html__('Yes', 'listdom'); ?></option>
                    <option value="0" <?php echo ($th_separator == 0 ? 'selected="selected"' : ''); ?>><?php echo esc_html__('No', 'listdom'); ?></option>
                </select>
            </div>

            <?php if($type === 'taxonomy'): ?>
            <div class="lsd-search-method-dependant lsd-search-method-dropdown lsd-search-method-dropdown-multiple lsd-search-method-hierarchical lsd-search-method-checkboxes lsd-search-method-radio">
                <div class="lsd-search-field-param lsd-search-field-all-terms">
                    <label for="lsd_fields_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_all_terms"><?php esc_html_e('Terms Method', 'listdom'); ?></label>
                    <select class="widefat" name="lsd[fields][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][all_terms]" title="<?php esc_attr_e('You can display certain rms if you like.', 'listdom'); ?>">
                        <option value="1" <?php echo ($display_all_terms == 1 ? 'selected="selected"' : ''); ?>><?php echo esc_html__('Display All Terms', 'listdom'); ?></option>
                        <option value="0" <?php echo ($display_all_terms == 0 ? 'selected="selected"' : ''); ?>><?php echo esc_html__('Display Selected Terms', 'listdom'); ?></option>
                    </select>
                </div>
                <div class="lsd-search-field-param lsd-search-field-terms <?php echo ($display_all_terms == 1 ? 'lsd-util-hide' : ''); ?>">
                    <label><?php esc_html_e('Terms', 'listdom'); ?></label>
                    <ul>
                        <?php echo $helper->tax_checkboxes([
                            'taxonomy' => $key,
                            'hide_empty' => $hide_empty,
                            'current' => $terms,
                            'id_prefix' => $key,
                            'name' => 'lsd[fields]['.esc_attr($i).'][filters]['.esc_attr($key).'][terms]',
                        ]); ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
