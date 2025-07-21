<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$slider = $options['slider'] ?? [];
?>
<h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Layout", 'listdom'); ?></h3>
<p class="description lsd-mb-4 lsd-mt-3"><?php echo esc_html__("Control listing limit, pagination, and how single listing pages open from the results.", 'listdom'); ?> </p>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Autoplay', 'listdom'),
        'for' => 'lsd_display_options_skin_slider_autoplay',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_slider_autoplay',
            'name' => 'lsd[display][slider][autoplay]',
            'value' => !isset($slider['autoplay']) || $slider['autoplay'] ? 1 : 0
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Limit', 'listdom'),
        'for' => 'lsd_display_options_skin_slider_limit',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'id' => 'lsd_display_options_skin_slider_limit',
            'name' => 'lsd[display][slider][limit]',
            'value' => $slider['limit'] ?? '6'
        ]); ?>
    </div>
</div>

<?php $this->field_listing_link('slider', $slider);
