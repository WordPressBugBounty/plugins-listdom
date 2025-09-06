<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$carousel = $options['carousel'] ?? [];
?>
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Layout", 'listdom'); ?></h3>
            <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("Control listing limit, pagination, and how single listing pages open from the results.", 'listdom'); ?> </p>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Autoplay', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_autoplay',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_carousel_autoplay',
                    'name' => 'lsd[display][carousel][autoplay]',
                    'value' => !isset($carousel['autoplay']) || $carousel['autoplay'] ? 1 : 0
                ]); ?>
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Listings Per Row', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_columns',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::select([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_carousel_columns',
                    'name' => 'lsd[display][carousel][columns]',
                    'options' => ['1'=>1, '2'=>2, '3'=>3, '4'=>4, '6'=>6],
                    'value' => $carousel['columns'] ?? '3'
                ]); ?>
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Limit', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_limit',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_carousel_limit',
                    'name' => 'lsd[display][carousel][limit]',
                    'value' => $carousel['limit'] ?? '8'
                ]); ?>
            </div>
        </div>

        <?php $this->field_listing_link('carousel', $carousel); ?>
    </div>
</div>
