<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$masonry = $options['masonry'] ?? [];
?>
<div class="lsd-form-row lsd-form-row-separator"></div>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__('Using the %s skin, you can show a Grid or List view of the listings with a handy filtering option above them.', 'listdom'), '<strong>'.esc_html__('Masonry', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Style', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_style',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_masonry_style',
            'class' => 'lsd-display-options-style-selector',
            'name' => 'lsd[display][masonry][style]',
            'options' => LSD_Styles::masonry(),
            'value' => $masonry['style'] ?? 'style1'
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Filter By', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_filter_by',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_masonry_filter_by',
            'name' => 'lsd[display][masonry][filter_by]',
            'options' => [
                LSD_Base::TAX_CATEGORY=>esc_html__('Categories', 'listdom'),
                LSD_Base::TAX_LOCATION=>esc_html__('Locations', 'listdom'),
                LSD_Base::TAX_FEATURE=>esc_html__('Features', 'listdom'),
                LSD_Base::TAX_LABEL=>esc_html__('Labels', 'listdom'),
            ],
            'value' => $masonry['filter_by'] ?? LSD_Base::TAX_CATEGORY
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Limit', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_limit',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'id' => 'lsd_display_options_skin_masonry_limit',
            'name' => 'lsd[display][masonry][limit]',
            'value' => $masonry['limit'] ?? '12'
        ]); ?>
        <p class="description"><?php echo sprintf(esc_html__("Number of the Listings per page. It should be a multiple of the %s option. For example if the %s is set to 3, then you should set the limit to 3, 6, 9, 12, 30, etc.", 'listdom'), '<strong>'.esc_html__('Listings Per Row', 'listdom').'</strong>', '<strong>'.esc_html__('Listings Per Row', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('List View', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_list_view',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_masonry_list_view',
            'name' => 'lsd[display][masonry][list_view]',
            'value' => $masonry['list_view'] ?? '0',
            'toggle' => '#lsd_display_options_skin_masonry_listing_per_row_option'
        ]); ?>
        <p class="description"><?php esc_html_e("Display listings in the List view.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row <?php echo isset($masonry['list_view']) && $masonry['list_view'] ? 'lsd-util-hide' : ''; ?>" id="lsd_display_options_skin_masonry_listing_per_row_option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listings Per Row', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_columns',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_masonry_columns',
            'name' => 'lsd[display][masonry][columns]',
            'options' => ['2' => 2, '3' => 3, '4' => 4, '6' => 6],
            'value' => $masonry['columns'] ?? '3'
        ]); ?>
    </div>
</div>

<?php if($this->isPro()): ?>
<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listing Link', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_listing_link',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_masonry_listing_link',
            'name' => 'lsd[display][masonry][listing_link]',
            'value' => $masonry['listing_link'] ?? 'normal',
            'options' => LSD_Base::get_listing_link_methods(),
        ]); ?>
        <p class="description"><?php esc_html_e("Link to listing detail page.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Image', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_display_image',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_masonry_display_image',
            'name' => 'lsd[display][masonry][display_image]',
            'value' => $masonry['display_image'] ?? '1',
            'toggle' => '#lsd_display_options_skin_masonry_image_method'
        ]); ?>
        <p class="description"><?php esc_html_e("Display listing image.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row lsd-display-options-builder-option <?php echo !isset($masonry['display_image']) || $masonry['display_image'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_masonry_image_method">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Image Method', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_image_method',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_masonry_image_method',
            'name' => 'lsd[display][masonry][image_method]',
            'options' => [
                'cover' => esc_html__('Cover', 'listdom'),
                'slider' => esc_html__('Slider', 'listdom'),
            ],
            'value' => $masonry['image_method'] ?? 'cover'
        ]); ?>
        <p class="description"><?php esc_html_e("Cover shows only featured image but slider shows all gallery images.", 'listdom'); ?></p>
    </div>
</div>
<?php else: ?>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-6">
        <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Listing Link & Display Image', 'listdom'), true); ?></p>
    </div>
</div>
<?php endif; ?>

<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Labels', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_display_labels',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_masonry_display_labels',
            'name' => 'lsd[display][masonry][display_labels]',
            'value' => $masonry['display_labels'] ?? '0'
        ]); ?>
        <p class="description"><?php esc_html_e("Activate to display the listing labels on the image.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Display Share Buttons', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_display_share_buttons',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_display_options_skin_masonry_display_share_buttons',
            'name' => 'lsd[display][masonry][display_share_buttons]',
            'value' => $masonry['display_share_buttons'] ?? '0'
        ]); ?>
        <p class="description"><?php esc_html_e("Activate to display the share buttons.", 'listdom'); ?></p>
    </div>
</div>