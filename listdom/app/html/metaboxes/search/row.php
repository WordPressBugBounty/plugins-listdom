<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $row **/
/** @var string $device_key **/
/** @var int $i **/
/** @var int $post_id **/

$legacy = get_post_meta($post_id, 'lsd_more_options', true);
if (!is_array($legacy)) $legacy = [];
?>
<?php if ($row['type'] === 'more_options'): $type = isset($row['more_options']['type']) && in_array($row['more_options']['type'], ['normal', 'popup']) ? $row['more_options']['type'] : ($legacy['type'] ?? 'normal'); ?>
<div class="lsd-search-more-options-row-params">
    <div>
        <?php echo LSD_Form::label([
            'title' => esc_html__('Type', 'listdom'),
            'for' => 'lsd_'.$device_key.'_'.$i.'_more_options_type',
        ]); ?>
        <?php echo LSD_Form::select([
            'name' => 'lsd['.$device_key.']['.$i.'][more_options][type]',
            'id' => 'lsd_'.$device_key.'_'.$i.'_more_options_type',
            'class' => 'lsd-more-options-type-toggle',
            'value' => $type,
            'options' => [
                'normal' => esc_html__('Normal', 'listdom'),
                'popup' => esc_html__('Popup', 'listdom'),
            ],
            'attributes' => [
                'data-for' => '.lsd-'.$device_key.'-'.$i.'-more-options-width',
            ]
        ]); ?>
    </div>
    <div class="lsd-<?php echo esc_attr($device_key); ?>-<?php echo esc_attr($i); ?>-more-options-width lsd-tooltip lsd-tooltip-box <?php echo $type === 'normal' ? 'lsd-util-hide' : ''; ?>" data-lsd-tooltip="<?php esc_attr_e('Based on vw (viewport width), meaning the popup width is a percentage of the browser window.', 'listdom'); ?>">
        <?php echo LSD_Form::label([
            'title' => esc_html__('Popup width', 'listdom'),
            'for' => 'lsd_'.$device_key.'_'.$i.'_more_options_width',
        ]); ?>
        <?php echo LSD_Form::number([
            'name' => 'lsd['.$device_key.']['.$i.'][more_options][width]',
            'id' => 'lsd_'.$device_key.'_'.$i.'_more_options_width',
            'value' => isset($row['more_options']['width']) && $row['more_options']['width'] ? $row['more_options']['width'] : ($legacy['width'] ?? 60),
            'attributes' => [
                'min' => 0,
                'max' => 100,
                'increment' => 10
            ],
        ]); ?>
    </div>
    <div>
        <?php echo LSD_Form::label([
            'title' => esc_html__('Button Text', 'listdom'),
            'for' => 'lsd_'.$device_key.'_'.$i.'_more_options_button',
        ]); ?>
        <?php echo LSD_Form::text([
            'name' => 'lsd['.$device_key.']['.$i.'][more_options][button]',
            'id' => 'lsd_'.$device_key.'_'.$i.'_more_options_button',
            'value' => isset($row['more_options']['button']) && $row['more_options']['button'] ? $row['more_options']['button'] : ($legacy['button'] ?? esc_html__('More Options', 'listdom')),
        ]); ?>
    </div>
</div>
<?php else: $button_status = (is_string($row['buttons']) && $row['buttons']) || (is_array($row['buttons']) && isset($row['buttons']['status']) && $row['buttons']['status']) ? 1 : 0; ?>
<div class="lsd-search-row-params <?php echo $button_status ? 'lsd-search-row-button-enabled' : ''; ?>">
    <?php echo LSD_Form::switcher([
        'id' => 'lsd_'.$device_key.'_'.$i.'_buttons',
        'name' => 'lsd['.$device_key.']['.$i.'][buttons][status]',
        'value' => $button_status,
    ]); ?>
    <label for="lsd_<?php echo esc_attr($device_key); ?>_<?php echo esc_attr($i); ?>_buttons"><?php esc_html_e('Search Buttons', 'listdom'); ?></label>
    <div class="lsd-search-row-button-params">
        <div class="lsd-select-search-width lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Button Width', 'listdom'); ?>">
            <?php echo LSD_Form::select([
                'id' => 'lsd_'.$device_key.'_'.$i.'_buttons_width',
                'name' => 'lsd['.$device_key.']['.$i.'][buttons][width]',
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
                'value' => $row['buttons']['width'] ?? '2',
            ]); ?>
        </div>
        <div class="lsd-select-search-alignment lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Button Alignment', 'listdom'); ?>">
            <?php echo LSD_Form::select([
                'id' => 'lsd_'.$device_key.'_'.$i.'_buttons_alignment',
                'name' => 'lsd['.$device_key.']['.$i.'][buttons][alignment]',
                'options' => [
                    'left' => esc_html__('Left', 'listdom'),
                    'center' => esc_html__('Center', 'listdom'),
                    'right' => esc_html__('Right', 'listdom'),
                ],
                'value' => $row['buttons']['alignment'] ?? 'left',
            ]); ?>
        </div>
        <div class="lsd-search-button-label lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Button Label', 'listdom'); ?>">
            <?php echo LSD_Form::text([
                'id' => 'lsd_'.$device_key.'_'.$i.'_buttons_label',
                'name' => 'lsd['.$device_key.']['.$i.'][buttons][label]',
                'value' => $row['buttons']['label'] ?? esc_html__('Search', 'listdom'),
            ]); ?>
        </div>
    </div>
</div>
<?php endif;
