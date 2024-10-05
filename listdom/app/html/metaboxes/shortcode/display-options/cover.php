<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$cover = $options['cover'] ?? [];
?>
<div class="lsd-form-row lsd-form-row-separator"></div>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__("Using the %s skin, you can show only 1 listing in a nice style. You can use multiple cover shortcodes in one page to show more listings.", 'listdom'), '<strong>'.esc_html__('Cover', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Style', 'listdom'),
        'for' => 'lsd_display_options_skin_cover_style',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_cover_style',
            'class' => 'lsd-display-options-style-selector',
            'name' => 'lsd[display][cover][style]',
            'options' => LSD_Styles::cover(),
            'value' => $cover['style'] ?? 'style1'
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listing', 'listdom'),
        'for' => 'lsd_display_options_skin_cover_listing',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::listings([
            'id' => 'lsd_display_options_skin_cover_listing',
            'name' => 'lsd[display][cover][listing]',
            'value' => $cover['listing'] ?? null,
            'has_post_thumbnail' => true
        ]); ?>
        <p class="description"><?php echo esc_html__("You can select only the listings that have featured image.", 'listdom'); ?></p>
    </div>
</div>

<?php if($this->isPro()): ?>
<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listing Link', 'listdom'),
        'for' => 'lsd_display_options_skin_cover_listing_link',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_cover_listing_link',
            'name' => 'lsd[display][cover][listing_link]',
            'value' => $cover['listing_link'] ?? 'normal',
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