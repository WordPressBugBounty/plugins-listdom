<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Search_Helper $helper */
/** @var string $key */
/** @var string $device_key **/
/** @var int $i */
/** @var string $type */
/** @var array $methods */
/** @var array $data */
/** @var array $field */

$title = $data['title'] ?? '';
$title_visibility = !isset($data['title_visibility']) || $data['title_visibility'] ? 1 : 0;
$visibility = !isset($data['visibility']) || $data['visibility'] ? 1 : 0;

$form = get_post_meta(get_the_ID(), 'lsd_form', true);
$style = $form['style'] ?? 'default';

$placeholder = $data['placeholder'] ?? '';
$max_placeholder = $data['max_placeholder'] ?? '';

$default_value = $data['default_value'] ?? '';
$max_default_value = $data['max_default_value'] ?? '';

$radius = $data['radius'] ?? 5000;
$radius_values = $data['radius_values'] ?? '';
$prepend = $data['prepend'] ?? '';
$radius_display_unit = $data['radius_display_unit'] ?? 'm';
$radius_display = $data['radius_display'] ?? 0;
$hide_empty = $data['hide_empty'] ?? 1;
$dropdown_style = $data['dropdown_style'] ?? 'enhanced';
$display_all_terms = $data['all_terms'] ?? 1;
$terms = isset($data['terms']) && is_array($data['terms']) ? $data['terms'] : [];
$width = $data['width'] ?? '3';
$items_per_row = $data['items_per_row'] ?? '12';

$min = $data['min'] ?? 0;
$max = $data['max'] ?? 100;
$increment = $data['increment'] ?? 10;
$th_separator = $data['th_separator'] ?? 1;
$level_status = $data['level_status'] ?? 'dependant';

$label = isset($field['title']) && trim($field['title']) ? $field['title'] : ($data['title'] ?? 'N/A');
?>
<div class="lsd-search-field <?php echo !$visibility ? 'lsd-search-field-hidden' : ''; ?> <?php echo !$title_visibility ? 'lsd-search-field-title-hidden' : ''; ?>" id="lsd_<?php echo esc_attr($device_key); ?>_search_field_<?php echo esc_attr($i); ?>_<?php echo esc_attr($key); ?>" data-row="<?php echo esc_attr($i); ?>" data-key="<?php echo esc_attr($key); ?>" data-label="<?php echo esc_attr($label); ?>">
    <div class="lsd-row">
        <div class="lsd-col-9">
            <h4><?php echo esc_html($label); ?> <code class="lsd-ml-3 lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Key', 'listdom'); ?>"><?php echo $field['key'] ?? ''; ?></code></h4>
        </div>
        <div class="lsd-col-3">
            <ul class="lsd-search-field-actions">
                <li class="lsd-search-field-actions-width lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Field Width', 'listdom'); ?>" data-key="<?php echo esc_attr($key); ?>" data-i="<?php echo esc_attr($i); ?>">
                    <div class="lsd-select-search-width">
                        <?php echo LSD_Form::select([
                            'id' => 'width-' . esc_attr($key),
                            'name' => 'lsd['.$device_key.']['.esc_attr($i).'][filters]['.esc_attr($key).'][width]',
                            'options' => [
                                '1' => esc_html__('1/12', 'listdom'),
                                '2' => esc_html__('2/12', 'listdom'),
                                '3' => esc_html__('3/12', 'listdom'),
                                '4' => esc_html__('4/12', 'listdom'),
                                '5' => esc_html__('5/12', 'listdom'),
                                '6' => esc_html__('6/12', 'listdom'),
                                '7' => esc_html__('7/12', 'listdom'),
                                '8' => esc_html__('8/12', 'listdom'),
                                '9' => esc_html__('9/12', 'listdom'),
                                '10' => esc_html__('10/12', 'listdom'),
                                '11' => esc_html__('11/12', 'listdom'),
                                '12' => esc_html__('Full Width', 'listdom'),
                            ],
                            'value' => $width ?? ($style === 'default' ? '3' : '12'),
                            'attributes' => [
                                'data-i' => esc_attr($i),
                                'data-key' => esc_attr($key),
                            ],
                        ]); ?>
                    </div>
                </li>
                <li class="lsd-search-field-actions-sort lsd-field-handler" data-key="<?php echo esc_attr($key); ?>"><i class="lsd-icon fas fa-arrows-alt"></i></li>
                <li class="lsd-search-field-actions-visibility lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Toggle Field Visibility', 'listdom'); ?>" data-i="<?php echo esc_attr($i); ?>" data-key="<?php echo esc_attr($key); ?>"><i class="lsd-icon fa <?php echo $visibility ? 'fa-eye' : 'fa-eye-slash'; ?>"></i></li>
                <li class="lsd-search-field-actions-delete lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Click twice to delete', 'listdom'); ?>" data-confirm="0" data-i="<?php echo esc_attr($i); ?>" data-key="<?php echo esc_attr($key); ?>"><i class="lsd-icon fas fa-trash-alt"></i></li>
            </ul>
        </div>
    </div>
    <div class="lsd-row lsd-mt-2">
        <div class="lsd-col-12">

            <input type="hidden" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][key]" value="<?php echo esc_attr($key); ?>">
            <input type="hidden" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_visibility" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][visibility]" value="<?php echo esc_attr($visibility); ?>">
            <input type="hidden" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_title_visibility" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][title_visibility]" value="<?php echo esc_attr($title_visibility); ?>">

            <div class="lsd-alert lsd-info lsd-search-field-visibility-alert"><?php esc_html_e("The field is now hidden, but it still affects the search request. If you don’t want a search field to influence the search results, you can remove it entirely.", 'listdom'); ?></div>

            <div class="lsd-search-field-param">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_title"><?php esc_html_e('Title', 'listdom'); ?></label>
                <div class="lsd-flex lsd-flex-row lsd-flex-align-items-stretch lsd-gap-3">
                    <span class="lsd-search-field-param-title-visibility lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Toggle Visibility of Title', 'listdom'); ?>" data-i="<?php echo esc_attr($i); ?>" data-key="<?php echo esc_attr($key); ?>"><i class="lsd-icon fa <?php echo $title_visibility ? 'fa-eye' : 'fa-eye-slash'; ?>"></i></span>
                    <input class="lsd-search-field-param-title widefat lsd-flex-1" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_title" type="text" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][title]" value="<?php echo esc_attr($title); ?>" placeholder="<?php esc_attr_e('Field Title', 'listdom'); ?>">
                </div>
            </div>

            <div class="lsd-search-field-param">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_method"><?php esc_html_e('Method', 'listdom'); ?></label>
                <select id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_method" class="widefat lsd-search-method" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][method]" title="<?php esc_attr_e('Display Method', 'listdom'); ?>">
                    <?php foreach ($methods as $method => $method_title): ?>
                    <option value="<?php echo esc_attr($method); ?>" <?php echo isset($data['method']) && $data['method'] === $method ? 'selected="selected"' : ''; ?>><?php echo esc_html($method_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (($type === 'taxonomy' || strpos($key, 'att-') !== false) && $type !== 'number'): ?>
            <div class="lsd-search-field-param">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_hide_empty"><?php esc_html_e('Hide Empty Terms', 'listdom'); ?></label>
                <select id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_hide_empty" class="widefat" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][hide_empty]" title="<?php esc_attr_e('Only display those terms that have listings', 'listdom'); ?>">
                    <option value="1" <?php echo $hide_empty == 1 ? 'selected' : ''; ?>><?php echo esc_html__('Yes', 'listdom'); ?></option>
                    <option value="0" <?php echo $hide_empty == 0 ? 'selected' : ''; ?>><?php echo esc_html__('No', 'listdom'); ?></option>
                </select>
            </div>
            <?php endif; ?>

            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-dropdown lsd-search-method-dropdown-multiple lsd-search-method-hierarchical lsd-search-method-dropdown-plus">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_dropdown_style"><?php esc_html_e('Dropdown Style', 'listdom'); ?></label>
                <select id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_dropdown_style" class="widefat" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][dropdown_style]">
                    <option value="enhanced" <?php echo $dropdown_style === 'enhanced' ? 'selected' : ''; ?>><?php echo esc_html__('Enhanced', 'listdom'); ?></option>
                    <option value="standard" <?php echo $dropdown_style === 'standard' ? 'selected' : ''; ?>><?php echo esc_html__('Standard', 'listdom'); ?></option>
                </select>
            </div>

            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-text-input lsd-search-method-number-input lsd-search-method-dropdown lsd-search-method-dropdown-multiple lsd-search-method-dropdown-plus lsd-search-method-mm-input lsd-search-method-hierarchical lsd-search-method-radius lsd-search-method-date-range-picker">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_placeholder"><?php esc_html_e('Placeholder', 'listdom'); ?></label>
                <input class="lsd-search-field-param-placeholder widefat" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_placeholder" type="text" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][placeholder]" value="<?php echo esc_attr($placeholder); ?>" placeholder="<?php esc_attr_e('Optional Placeholder', 'listdom'); ?>">
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-mm-input">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_max_placeholder"><?php esc_html_e('Max Placeholder', 'listdom'); ?></label>
                <input class="widefat" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_max_placeholder" type="text" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][max_placeholder]" value="<?php echo esc_attr($max_placeholder); ?>" placeholder="<?php esc_attr_e('Optional Max Placeholder', 'listdom'); ?>">
            </div>

            <?php if ($type !== 'acf_true_false'): ?>
                <div class="lsd-search-field-param">
                    <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_default_value"><?php esc_html_e('Default Value', 'listdom'); ?></label>
                    <input class="widefat" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_default_value" type="text" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][default_value]" value="<?php echo esc_attr($default_value); ?>" placeholder="<?php esc_attr_e('Optional', 'listdom'); ?>">
                </div>
            <?php endif; ?>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-mm-input lsd-search-method-range lsd-search-method-acf_range">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_max_default_value"><?php esc_html_e('Max Default Value', 'listdom'); ?></label>
                <input class="widefat" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_max_default_value" type="text" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][max_default_value]" value="<?php echo esc_attr($max_default_value); ?>" placeholder="<?php esc_attr_e('Optional', 'listdom'); ?>">
            </div>

            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-radius">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius"><?php esc_html_e('Radius (Meters)', 'listdom'); ?></label>
                <input class="widefat" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius" type="text" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][radius]" value="<?php echo esc_attr($radius); ?>" placeholder="<?php esc_attr_e('5000 means 5 KM', 'listdom'); ?>">
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-radius-dropdown">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_values"><?php esc_html_e('Radius Values (Meters)', 'listdom'); ?></label>
                <input class="widefat" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_values" type="text" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][radius_values]" value="<?php echo esc_attr($radius_values); ?>" placeholder="<?php esc_attr_e('100,200,500,1000,2000', 'listdom'); ?>">
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-range lsd-search-method-acf_range">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_prepend"><?php esc_html_e('Prepend', 'listdom'); ?></label>
                <input class="widefat" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_prepend" type="text" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][prepend]" value="<?php echo esc_attr($prepend); ?>" placeholder="<?php esc_attr_e('Optional', 'listdom'); ?>">
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-radius-dropdown">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_display_unit"><?php esc_html_e('Radius Display Unit', 'listdom'); ?></label>
                <select class="widefat" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_display_unit" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][radius_display_unit]">
                    <option value="m" <?php echo $radius_display_unit === 'm' ? 'selected' : ''; ?>><?php esc_html_e('Meters', 'listdom'); ?></option>
                    <option value="km" <?php echo $radius_display_unit === 'km' ? 'selected' : ''; ?>><?php esc_html_e('Kilo Meters (KM)', 'listdom'); ?></option>
                    <option value="mile" <?php echo $radius_display_unit === 'mile' ? 'selected' : ''; ?>><?php esc_html_e('Miles', 'listdom'); ?></option>
                </select>
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-radius">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_display"><?php esc_html_e('Display Radius Field', 'listdom'); ?></label>
                <select id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_radius_display" class="widefat" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][radius_display]">
                    <option value="1" <?php echo $radius_display == 1 ? 'selected' : ''; ?>><?php echo esc_html__('Yes', 'listdom'); ?></option>
                    <option value="0" <?php echo $radius_display == 0 ? 'selected' : ''; ?>><?php echo esc_html__('No', 'listdom'); ?></option>
                </select>
            </div>

            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-dropdown-plus lsd-search-method-range lsd-search-method-acf_range">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_min"><?php esc_html_e('Min / Max / Increment', 'listdom'); ?></label>
                <div class="lsd-input-group">
                    <input id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_min" type="number" min="0" step="0.01" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][min]" value="<?php echo esc_attr($min); ?>" title="<?php esc_attr_e('Min', 'listdom'); ?>">
                    <input id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_max" type="number" min="0" step="0.01" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][max]" value="<?php echo esc_attr($max); ?>" title="<?php esc_attr_e('Max', 'listdom'); ?>">
                    <input id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_increment" type="number" min="0" step="0.01" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][increment]" value="<?php echo esc_attr($increment); ?>" title="<?php esc_attr_e('Increment', 'listdom'); ?>">
                </div>
            </div>
            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-dropdown-plus">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_th_separator"><?php esc_html_e('Thousands Separator', 'listdom'); ?></label>
                <select class="widefat" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][th_separator]" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_th_separator">
                    <option value="1" <?php echo $th_separator == 1 ? 'selected' : ''; ?>><?php echo esc_html__('Yes', 'listdom'); ?></option>
                    <option value="0" <?php echo $th_separator == 0 ? 'selected' : ''; ?>><?php echo esc_html__('No', 'listdom'); ?></option>
                </select>
            </div>

            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-hierarchical">
                <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_level_status"><?php esc_html_e('Level Status', 'listdom'); ?></label>
                <select class="widefat" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][level_status]" id="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_level_status">
                    <option value="all" <?php echo $level_status === 'all' ? 'selected' : ''; ?>><?php echo esc_html__('Show All Options', 'listdom'); ?></option>
                    <option value="dependant" <?php echo $level_status === 'dependant' ? 'selected' : ''; ?>><?php echo esc_html__('Dependent on First Selection', 'listdom'); ?></option>
                </select>
            </div>

            <?php if ($type === 'taxonomy'): ?>
            <div class="lsd-search-method-dependant lsd-search-method-dropdown lsd-search-method-dropdown-multiple lsd-search-method-hierarchical lsd-search-method-checkboxes lsd-search-method-radio">
                <div class="lsd-search-field-param lsd-search-field-all-terms">
                    <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_all_terms"><?php esc_html_e('Terms Method', 'listdom'); ?></label>
                    <select class="widefat" name="lsd[<?php echo esc_attr($device_key); ?>][<?php echo esc_attr($i); ?>][filters][<?php echo esc_attr($key); ?>][all_terms]" title="<?php esc_attr_e('You can display certain rms if you like.', 'listdom'); ?>">
                        <option value="1" <?php echo $display_all_terms == 1 ? 'selected' : ''; ?>><?php echo esc_html__('Display All Terms', 'listdom'); ?></option>
                        <option value="0" <?php echo $display_all_terms == 0 ? 'selected' : ''; ?>><?php echo esc_html__('Display Selected Terms', 'listdom'); ?></option>
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
                            'name' => 'lsd['.esc_attr($device_key).']['.esc_attr($i).'][filters]['.esc_attr($key).'][terms]',
                        ]); ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="lsd-search-field-param lsd-search-method-dependant lsd-search-method-checkboxes lsd-search-method-radio">
                <div class="lsd-search-field-param">
                    <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_filters_<?php echo esc_attr($key); ?>_items_per_row"><?php esc_html_e('Columns', 'listdom'); ?></label>
                    <?php echo LSD_Form::select([
                        'id' => 'items-per-row-' . esc_attr($key),
                        'name' => 'lsd['.esc_attr($device_key).']['.esc_attr($i).'][filters]['.esc_attr($key).'][items_per_row]',
                        'options' => [
                            '12' => esc_html__('1 Columns', 'listdom'),
                            '6' => esc_html__('2 Columns', 'listdom'),
                            '4' => esc_html__('3 Columns', 'listdom'),
                            '3' => esc_html__('4 Columns', 'listdom'),
                            '2' => esc_html__('6 Columns', 'listdom'),
                        ],
                        'value' => $items_per_row ?: '12',
                        'class' => 'lsd-m-0',
                        'attributes' => [
                            'data-i' => esc_attr($i),
                            'data-key' => esc_attr($key),
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
