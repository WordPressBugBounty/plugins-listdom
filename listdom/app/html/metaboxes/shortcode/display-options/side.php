<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */
/** @var array $price_components */

$single_styles = ['' => esc_html__('Inherit From Global Options', 'listdom')] + LSD_Styles::details();

$side = $options['side'] ?? [];
$optional_addons = [];
?>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__('With the %s skin, you can display a list of directories and listings on one side and the single listing page on the other.', 'listdom'), '<strong>'.esc_html__('Side by Side', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<?php if ($this->isLite()): ?>
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
            'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle',
            'options' => LSD_Styles::side(),
            'value' => $side['style'] ?? 'style1',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_side'
            ]
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Single Listing', 'listdom'),
        'for' => 'lsd_display_options_skin_side_single_listing_style',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_side_single_listing_style',
            'name' => 'lsd[display][side][single_listing_style]',
            'options' => $single_styles,
            'value' => $side['single_listing_style'] ?? '',
        ]); ?>
    </div>
</div>

<div class="lsd-form-group lsd-form-row-style-needed lsd-display-options-style-dependency lsd-display-options-style-dependency-style1" id="lsd_display_options_style">
    <h3 class="lsd-my-0"><?php echo esc_html__("Elements", 'listdom'); ?></h3>
    <p class="description lsd-mb-4"><?php echo esc_html__("You can easily customize the visibility of each element on the listing card.", 'listdom'); ?> </p>
    <div class="lsd-flex lsd-gap-2">
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Address', 'listdom'),
                'for' => 'lsd_display_options_skin_side_display_address',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_side_display_address',
                    'name' => 'lsd[display][side][display_address]',
                    'value' => $side['display_address'] ?? '1'
                ]); ?>
            </div>
        </div>

        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Price', 'listdom'),
                'for' => 'lsd_display_options_skin_side_display_price',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_side_display_price',
                    'name' => 'lsd[display][side][display_price]',
                    'value' => $side['display_price'] ?? '1'
                ]); ?>
            </div>
        </div>

        <?php if (class_exists(LSDADDFAV::class) || class_exists(\LSDPACFAV\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Favorite Icon', 'listdom'),
                'for' => 'lsd_display_options_skin_side_display_favorite_icon',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_side_display_favorite_icon',
                    'name' => 'lsd[display][side][display_favorite_icon]',
                    'value' => $side['display_favorite_icon'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['favorite', esc_html__('Favorite Icon', 'listdom')]; ?>
        <?php endif; ?>

        <?php if (class_exists(LSDADDCMP::class) || class_exists(\LSDPACCMP\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Compare Icon', 'listdom'),
                'for' => 'lsd_display_options_skin_side_display_compare_icon',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_side_display_compare_icon',
                    'name' => 'lsd[display][side][display_compare_icon]',
                    'value' => $side['display_compare_icon'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['compare', esc_html__('Compare Icon', 'listdom')]; ?>
        <?php endif; ?>

        <?php if (class_exists(LSDADDREV::class) || class_exists(\LSDPACREV\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Review Rates', 'listdom'),
                'for' => 'lsd_display_options_skin_side_review_stars',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_side_review_stars',
                    'name' => 'lsd[display][side][display_review_stars]',
                    'value' => $side['display_review_stars'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['reviews', esc_html__('Reviews Rate', 'listdom')]; ?>
        <?php endif; ?>

        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Title', 'listdom'),
                'for' => 'lsd_display_options_skin_side_title',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_side_title',
                    'name' => 'lsd[display][side][display_title]',
                    'value' => $side['display_title'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php if ($this->isPro()): ?>
        <div class="lsd-form-row">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Image', 'listdom'),
                'for' => 'lsd_display_options_skin_side_display_image',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_side_display_image',
                    'name' => 'lsd[display][side][display_image]',
                    'value' => $side['display_image'] ?? '1',
                    'toggle' => '#lsd_display_options_skin_side_image_method'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['pro', esc_html__('Display Image', 'listdom')]; ?>
        <?php endif; ?>
    </div>
    <?php if (count($optional_addons)): ?>
        <div class="lsd-alert-no-my lsd-mt-5">
            <?php echo LSD_Base::alert(LSD_Base::optionalAddonsMessage($optional_addons),'warning'); ?>
        </div>
    <?php endif; ?>
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
            'options' => LSD_Base::get_pagination_methods(),
        ]); ?>
        <p class="description"><?php esc_html_e('Choose how to load additional listings more than the default limit.', 'listdom'); ?></p>
    </div>
</div>

