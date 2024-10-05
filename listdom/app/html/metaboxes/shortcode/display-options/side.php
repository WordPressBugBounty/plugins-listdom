<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$side = $options['side'] ?? [];
?>
<div class="lsd-form-row lsd-form-row-separator"></div>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__('Using the %s skin allows you to display a list of the directories and listings on one side and the details page on the other side.', 'listdom'), '<strong>'.esc_html__('Side by Side', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<?php if($this->isLite()): ?>
<div class="lsd-form-row lsd-mb-3">
    <div class="lsd-col-12">
        <p class="lsd-alert lsd-warning"><?php echo LSD_Base::missFeatureMessage(esc_html__('Side by Side Skin', 'listdom')); ?></p>
    </div>
</div>
<?php endif; ?>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Style', 'listdom'),
        'for' => 'lsd_display_options_skin_side_style',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_side_style',
            'name' => 'lsd[display][side][style]',
            'class' => 'lsd-display-options-style-selector',
            'options' => LSD_Styles::side(),
            'value' => $side['style'] ?? 'style1'
        ]); ?>
    </div>
</div>
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
            'options' => [
                'loadmore' => esc_html__('Load More Button', 'listdom'),
                'scroll' => esc_html__('Infinite Scroll', 'listdom'),
                'disabled' => esc_html__('Disabled', 'listdom'),
            ],
        ]); ?>
        <p class="description"><?php esc_html_e('Choose how to load additional listings more than the default limit.', 'listdom'); ?></p>
    </div>
</div>

<?php if($this->isPro()): ?>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Image', 'listdom'),
        'for' => 'lsd_display_options_skin_side_display_image',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_side_display_image',
            'name' => 'lsd[display][side][display_image]',
            'value' => $side['display_image'] ?? '1',
            'toggle' => '#lsd_display_options_skin_side_image_method'
        ]); ?>
        <p class="description"><?php esc_html_e("Display listing image.", 'listdom'); ?></p>
    </div>
</div>
<?php else: ?>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-6">
        <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Display Image', 'listdom')); ?></p>
    </div>
</div>
<?php endif; ?>