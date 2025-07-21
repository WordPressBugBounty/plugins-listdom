<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$carousel = $options['carousel'] ?? [];
?>
<h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Layout", 'listdom'); ?></h3>
<p class="description lsd-mb-4 lsd-mt-3"><?php echo esc_html__("Control listing limit, pagination, and how single listing pages open from the results.", 'listdom'); ?> </p>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Autoplay', 'listdom'),
        'for' => 'lsd_display_options_skin_carousel_autoplay',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_carousel_autoplay',
            'name' => 'lsd[display][carousel][autoplay]',
            'value' => !isset($carousel['autoplay']) || $carousel['autoplay'] ? 1 : 0
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listings Per Row', 'listdom'),
        'for' => 'lsd_display_options_skin_carousel_columns',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_carousel_columns',
            'name' => 'lsd[display][carousel][columns]',
            'options' => ['1'=>1, '2'=>2, '3'=>3, '4'=>4, '6'=>6],
            'value' => $carousel['columns'] ?? '3'
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Limit', 'listdom'),
        'for' => 'lsd_display_options_skin_carousel_limit',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'id' => 'lsd_display_options_skin_carousel_limit',
            'name' => 'lsd[display][carousel][limit]',
            'value' => $carousel['limit'] ?? '8'
        ]); ?>
    </div>
</div>

<?php $this->field_listing_link('carousel', $carousel);
