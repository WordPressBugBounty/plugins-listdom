<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$carousel = $options['carousel'] ?? [];

?>
<div class="lsd-form-row lsd-form-row-separator"></div>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__('Using the %s skin, you can show a carousel of the directories and listings in different styles.', 'listdom'), '<strong>'.esc_html__('Carousel', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Style', 'listdom'),
        'for' => 'lsd_display_options_skin_carousel_style',
    ]); ?></div>
    <div class="lsd-col-6 lsd-style-picker">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_carousel_style',
            'class' => 'lsd-display-options-style-selector',
            'name' => 'lsd[display][carousel][style]',
            'options' => LSD_Styles::carousel(),
            'value' => $carousel['style'] ?? 'style1'
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

<?php if($this->isPro()): ?>
<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listing Link', 'listdom'),
        'for' => 'lsd_display_options_skin_carousel_listing_link',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_carousel_listing_link',
            'name' => 'lsd[display][carousel][listing_link]',
            'value' => $carousel['listing_link'] ?? 'normal',
            'options' => LSD_Base::get_listing_link_methods(),
        ]); ?>
        <p class="description"><?php esc_html_e("Link to listing detail page.", 'listdom'); ?></p>
    </div>
</div>
<?php else: ?>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-6">
        <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Listing Link', 'listdom')); ?></p>
    </div>
</div>
<?php endif; ?>

<div class="lsd-form-row lsd-display-options-builder-option" id="lsd-display-label-buttons-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Labels', 'listdom'),
        'for' => 'lsd_display_options_skin_carousel_display_labels',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_carousel_display_labels',
            'name' => 'lsd[display][carousel][display_labels]',
            'value' => $carousel['display_labels'] ?? '0'
        ]); ?>
        <p class="description"><?php esc_html_e("Activate to display the listing labels on the image.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row lsd-display-options-builder-option" id="lsd-share-buttons-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Share Buttons', 'listdom'),
        'for' => 'lsd_display_options_skin_carousel_display_share_buttons',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_carousel_display_share_buttons',
            'name' => 'lsd[display][carousel][display_share_buttons]',
            'value' => $carousel['display_share_buttons'] ?? '0'
        ]); ?>
        <p class="description"><?php esc_html_e("Activate to display the share buttons.", 'listdom'); ?></p>
    </div>
</div>
