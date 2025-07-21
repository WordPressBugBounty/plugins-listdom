<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$masonry = $options['masonry'] ?? [];
?>
<h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Layout", 'listdom'); ?></h3>
<p class="description lsd-mb-4 lsd-mt-3"><?php echo esc_html__("Control listing limit, pagination, and how single listing pages open from the results.", 'listdom'); ?> </p>
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
            'title' => esc_html__('All Listings Tab Label', 'listdom'),
            'for' => 'lsd_display_options_skin_masonry_filter_all_label',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::text([
                'id' => 'lsd_display_options_skin_masonry_filter_all_label',
                'name' => 'lsd[display][masonry][filter_all_label]',
                'value' => $masonry['filter_all_label'] ??  esc_html__('All', 'listdom')
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
<div class="lsd-form-row lsd-display-options-style-not-for lsd-display-options-style-not-for-style4 lsd-display-options-style-not-for-custom">
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
<div class="lsd-form-row lsd-display-options-style-not-for lsd-display-options-style-not-for-style4 lsd-display-options-style-not-for-custom <?php echo isset($masonry['list_view']) && $masonry['list_view'] ? 'lsd-util-hide' : ''; ?>" id="lsd_display_options_skin_masonry_listing_per_row_option">
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

<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Animation Duration', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_duration',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::number([
            'id' => 'lsd_display_options_skin_masonry_duration',
            'name' => 'lsd[display][masonry][duration]',
            'value' => $masonry['duration'] ?? '400',
            'attributes' => [
                'min' => 0,
                'max' => 1000,
                'step' => 1,
            ]
        ]); ?>
        <p class="description"><?php esc_html_e("You can set the animation duration in milliseconds, with a default value of 400. Setting it to 0 will disable the animation.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Pagination Method', 'listdom'),
        'for' => 'lsd_display_options_skin_masonry_pagination',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_masonry_pagination',
            'name' => 'lsd[display][masonry][pagination]',
            'value' => $masonry['pagination'] ?? 'disabled',
            'options' => LSD_Base::get_pagination_methods(),
        ]); ?>
        <p class="description"><?php esc_html_e('Choose how to load additional listings more than the default limit.', 'listdom'); ?></p>
    </div>
</div>

<?php $this->field_listing_link('masonry', $masonry);
