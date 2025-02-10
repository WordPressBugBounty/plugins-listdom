<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $row **/
/** @var int $i **/
?>
<div class="lsd-search-row-params">
    <?php echo LSD_Form::switcher([
        'id' => 'lsd_fields_'.$i.'_buttons',
        'name' => 'lsd[fields]['.$i.'][buttons][status]',
        'value' => (is_string($row['buttons']) && $row['buttons']) || (is_array($row['buttons']) && isset($row['buttons']['status']) && $row['buttons']['status']) ? 1 : 0,
    ]); ?>
    <label for="lsd_fields_<?php echo esc_attr($i); ?>_buttons"><?php esc_html_e('Search Buttons', 'listdom'); ?></label>
    <div class="lsd-select-search-width">
        <?php echo LSD_Form::select([
            'id' => 'lsd_fields_'.$i.'_buttons_width',
            'name' => 'lsd[fields]['.($i).'][buttons][width]',
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

    <div class="lsd-select-search-alignment">
        <?php echo LSD_Form::select([
            'id' => 'lsd_fields_'.$i.'_buttons_alignment',
            'name' => 'lsd[fields]['.$i.'][buttons][alignment]',
            'options' => [
                'left' => esc_html__('Left', 'listdom'),
                'center' => esc_html__('Center', 'listdom'),
                'right' => esc_html__('Right', 'listdom'),
            ],
            'value' => $row['buttons']['alignment'] ?? 'left',
        ]); ?>
    </div>
</div>
