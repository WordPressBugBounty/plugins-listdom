<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$side = $options['side'] ?? [];
?>
<h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Layout", 'listdom'); ?></h3>
<p class="description lsd-mb-4 lsd-mt-3"><?php echo esc_html__("Control listing limit, pagination, and how single listing pages open from the results.", 'listdom'); ?> </p>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Layout Width', 'listdom'),
        'for' => 'lsd_display_options_skin_side_layout_width',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_side_layout_width',
            'name' => 'lsd[display][side][layout_width]',
            'options' => [
                '5050' => esc_html__('50-50 %', 'listdom'),
                '4555' => esc_html__('45-55 %', 'listdom'),
                '4060' => esc_html__('40-60 %', 'listdom'),
                '3565' => esc_html__('35-65 %', 'listdom'),
                '3070' => esc_html__('30-70 %', 'listdom')
            ],
            'value' => $side['layout_width'] ?? '4060'
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Limit', 'listdom'),
        'for' => 'lsd_display_options_skin_side_limit',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'id' => 'lsd_display_options_skin_side_limit',
            'name' => 'lsd[display][side][limit]',
            'value' => $side['limit'] ?? '12'
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Pagination Method', 'listdom'),
        'for' => 'lsd_display_options_skin_side_pagination',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_side_pagination',
            'name' => 'lsd[display][side][pagination]',
            'value' => $side['pagination'] ?? (isset($side['load_more']) && $side['load_more'] == 0 ? 'disabled' : 'loadmore'),
            'options' => LSD_Base::get_pagination_methods(),
        ]); ?>
        <p class="description"><?php esc_html_e('Choose how to load additional listings more than the default limit.', 'listdom'); ?></p>
    </div>
</div>
