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
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Select the Style", 'listdom'); ?></h3>
            <p class="lsd-admin-description lsd-my-0"><?php echo esc_html__("Pick a style variation of the selected skin or adjust listing elements (if not using Elementor/Divi).", 'listdom'); ?> </p>
        </div>
        <div class="lsd-col-12 lsd-p-0">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin_side_style',
                'name' => 'lsd[display][side][style]',
                'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle lsd-admin-input',
                'options' => LSD_Styles::side(),
                'value' => $side['style'] ?? 'style1',
                'attributes' => [
                    'data-parent' => '#lsd_skin_display_options_side'
                ]
            ]); ?>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Single Listing', 'listdom'),
                    'for' => 'lsd_display_options_skin_side_single_listing_style',
                ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::select([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_side_single_listing_style',
                    'name' => 'lsd[display][side][single_listing_style]',
                    'options' => $single_styles,
                    'value' => $side['single_listing_style'] ?? '',
                ]); ?>
            </div>
        </div>
    </div>
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-form-row-style-needed lsd-display-options-style-dependency lsd-display-options-style-dependency-style1" id="lsd_display_options_style">
            <div class="lsd-admin-section-heading">
                <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Elements", 'listdom'); ?></h3>
                <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("You can easily customize the visibility of each element on the listing card.", 'listdom'); ?> </p>
            </div>
            <div class="lsd-elements-section">
                    <?php if ($this->isPro()): ?>
                        <div class="lsd-elements-subsection">
                            <h4 class="lsd-admin-subtitle lsd-m-0"><?php esc_html_e('Image', 'listdom'); ?></h4>
                            <div class="lsd-elements-fields lsd-elements-3-column">
                                <div class="lsd-image-row">
                                    <div><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Enable', 'listdom'),
                                        'for' => 'lsd_display_options_skin_side_display_image',
                                    ]); ?></div>
                                    <div>
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_side_display_image',
                                            'name' => 'lsd[display][side][display_image]',
                                            'value' => $side['display_image'] ?? '1',
                                            'toggle' => '#lsd_display_options_skin_side_image_method'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: $optional_addons[] = ['pro', esc_html__('Display Image', 'listdom')]; ?>
                    <?php endif; ?>

                    <div class="lsd-elements-subsection">
                        <h4 class="lsd-admin-subtitle lsd-m-0"><?php esc_html_e('Other Elements', 'listdom'); ?></h4>
                        <div class="lsd-elements-fields">
                            <?php if (LSD_Components::map()): ?>
                                <div class="lsd-form-row lsd-display-options-builder-option">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Address', 'listdom'),
                                        'for' => 'lsd_display_options_skin_side_display_address',
                                    ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_side_display_address',
                                            'name' => 'lsd[display][side][display_address]',
                                            'value' => $side['display_address'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (LSD_Components::pricing()): ?>
                                <div class="lsd-form-row lsd-display-options-builder-option">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Price', 'listdom'),
                                            'for' => 'lsd_display_options_skin_side_display_price',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_side_display_price',
                                            'name' => 'lsd[display][side][display_price]',
                                            'value' => $side['display_price'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (class_exists(LSDADDFAV::class) || class_exists(\LSDPACFAV\Base::class)): ?>
                                <div class="lsd-form-row lsd-display-options-builder-option">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Favorite Icon', 'listdom'),
                                            'for' => 'lsd_display_options_skin_side_display_favorite_icon',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
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
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Compare Icon', 'listdom'),
                                            'for' => 'lsd_display_options_skin_side_display_compare_icon',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
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
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Review Rates', 'listdom'),
                                            'for' => 'lsd_display_options_skin_side_review_stars',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
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
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Title', 'listdom'),
                                        'for' => 'lsd_display_options_skin_side_title',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_side_title',
                                        'name' => 'lsd[display][side][display_title]',
                                        'value' => $side['display_title'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php if (count($optional_addons)): ?>
                <div class="lsd-alert-no-my lsd-mt-5">
                    <?php echo LSD_Base::alert(LSD_Base::optionalAddonsMessage($optional_addons),'warning'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>



