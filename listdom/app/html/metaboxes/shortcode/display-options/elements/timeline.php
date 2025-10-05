<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */
/** @var array $price_components */

$timeline = $options['timeline'] ?? [];
$optional_addons = [];
?>
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Select the Style", 'listdom'); ?></h3>
            <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("Pick a style variation of the selected skin or adjust listing elements (if not using Elementor/Divi).", 'listdom'); ?> </p>
        </div>
        <div class="lsd-col-12 lsd-p-0">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin_timeline_style',
                'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle lsd-admin-input',
                'name' => 'lsd[display][timeline][style]',
                'options' => LSD_Styles::timeline(),
                'value' => $timeline['style'] ?? 'style1',
                'attributes' => [
                    'data-parent' => '#lsd_skin_display_options_timeline, #lsd_skin_display_options_layout_timeline'
                ]
            ]); ?>
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
                            <div class="lsd-image-row lsd-display-options-builder-option">
                                <div class="lsd-col-5"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Enable', 'listdom'),
                                        'for' => 'lsd_display_options_skin_timeline_display_image',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_timeline_display_image',
                                        'name' => 'lsd[display][timeline][display_image]',
                                        'value' => $timeline['display_image'] ?? '1',
                                        'toggle' => '.lsd-display-options-skin-timeline-image-options'
                                    ]); ?>
                                </div>
                            </div>
                            <div class="lsd-image-row lsd-display-options-skin-timeline-image-options <?php echo !isset($timeline['display_image']) || $timeline['display_image'] ? '' : 'lsd-util-hide'; ?>">
                                <div><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Image Method', 'listdom'),
                                        'for' => 'lsd_display_options_skin_timeline_image_method',
                                    ]); ?></div>
                                <div>
                                    <?php echo LSD_Form::select([
                                        'class' => 'lsd-admin-input',
                                        'id' => 'lsd_display_options_skin_timeline_image_method',
                                        'name' => 'lsd[display][timeline][image_method]',
                                        'options' => [
                                            'timeline' => esc_html__('Cover', 'listdom'),
                                            'slider' => esc_html__('Slider', 'listdom'),
                                        ],
                                        'value' => $timeline['image_method'] ?? 'timeline'
                                    ]); ?>
                                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Cover shows only featured image but slider shows all gallery images.", 'listdom'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: $optional_addons[] = ['pro', esc_html__('Display Image', 'listdom')]; ?>
                <?php endif; ?>

                <div class="lsd-elements-subsection">
                    <h4 class="lsd-admin-subtitle lsd-m-0"><?php esc_html_e('Other Elements', 'listdom'); ?></h4>
                    <div class="lsd-elements-fields">
                        <div class="lsd-form-row lsd-display-options-style-dependency lsd-display-options-style-dependency-style1">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Contact Info', 'listdom'),
                                    'for' => 'lsd_display_options_skin_timeline_display_contact_info',
                                ]); ?></div>
                            <div class="lsd-col-4">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_display_options_skin_timeline_display_contact_info',
                                    'name' => 'lsd[display][timeline][display_contact_info]',
                                    'value' => $timeline['display_contact_info'] ?? '1'
                                ]); ?>
                            </div>
                        </div>

                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Content', 'listdom'),
                                        'for' => 'lsd_display_options_skin_timeline_display_description',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_timeline_display_description',
                                        'name' => 'lsd[display][timeline][display_description]',
                                        'value' => $timeline['display_description'] ?? '1',
                                        'toggle' => '#lsd_display_options_skin_timeline_description_length_wrapper, #lsd_display_options_skin_timeline_content_type_wrapper'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (LSD_Components::map()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1">
                                <div class="lsd-form-row">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Address', 'listdom'),
                                            'for' => 'lsd_display_options_skin_timeline_display_address',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_timeline_display_address',
                                            'name' => 'lsd[display][timeline][display_address]',
                                            'value' => $timeline['display_address'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (LSD_Components::work_hours()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1">
                                <div class="lsd-form-row ">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Work Hours', 'listdom'),
                                            'for' => 'lsd_display_options_skin_timeline_display_availability',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_timeline_display_availability',
                                            'name' => 'lsd[display][timeline][display_availability]',
                                            'value' => $timeline['display_availability'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Categories', 'listdom'),
                                        'for' => 'lsd_display_options_skin_timeline_display_categories',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_timeline_display_categories',
                                        'name' => 'lsd[display][timeline][display_categories]',
                                        'value' => $timeline['display_categories'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (LSD_Components::socials()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1">
                                <div class="lsd-form-row lsd-display-options-builder-option">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Share Buttons', 'listdom'),
                                            'for' => 'lsd_display_options_skin_timeline_display_share_buttons',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_timeline_display_share_buttons',
                                            'name' => 'lsd[display][timeline][display_share_buttons]',
                                            'value' => $timeline['display_share_buttons'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (LSD_Components::pricing()): ?>
                            <div class="lsd-form-row lsd-display-options-style-dependency lsd-display-options-style-dependency-style1">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Price', 'listdom'),
                                        'for' => 'lsd_display_options_skin_timeline_display_price',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_timeline_display_price',
                                        'name' => 'lsd[display][timeline][display_price]',
                                        'value' => $timeline['display_price'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="lsd-form-row lsd-display-options-builder-option">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Title', 'listdom'),
                                    'for' => 'lsd_display_options_skin_timeline_title',
                                ]); ?></div>
                            <div class="lsd-col-4">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_display_options_skin_timeline_title',
                                    'name' => 'lsd[display][timeline][display_title]',
                                    'value' => $timeline['display_title'] ?? '1',
                                    'toggle' => '#lsd_display_options_skin_timeline_is_claimed_wrapper'
                                ]); ?>
                            </div>
                        </div>
                        <?php if (class_exists(LSDADDCLM::class) || class_exists(\LSDPACCLM\Base::class)): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 <?php echo !isset($timeline['display_title']) || $timeline['display_title'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_timeline_is_claimed_wrapper">
                                <div class="lsd-form-row">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Claim Status', 'listdom'),
                                            'for' => 'lsd_display_options_skin_timeline_is_claimed',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_timeline_is_claimed',
                                            'name' => 'lsd[display][timeline][display_is_claimed]',
                                            'value' => $timeline['display_is_claimed'] ?? '1',
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: $optional_addons[] = ['claim', esc_html__('Claim Status', 'listdom')]; ?>
                        <?php endif; ?>

                        <div class="lsd-form-row lsd-display-options-builder-option">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Labels', 'listdom'),
                                    'for' => 'lsd_display_options_skin_timeline_display_labels',
                                ]); ?></div>
                            <div class="lsd-col-4">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_display_options_skin_timeline_display_labels',
                                    'name' => 'lsd[display][timeline][display_labels]',
                                    'value' => $timeline['display_labels'] ?? '1'
                                ]); ?>
                            </div>
                        </div>

                        <?php if (class_exists(LSDADDFAV::class) || class_exists(\LSDPACFAV\Base::class)): ?>
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Favorite Icon', 'listdom'),
                                        'for' => 'lsd_display_options_skin_timeline_display_favorite_icon',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_timeline_display_favorite_icon',
                                        'name' => 'lsd[display][timeline][display_favorite_icon]',
                                        'value' => $timeline['display_favorite_icon'] ?? '1'
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
                                        'for' => 'lsd_display_options_skin_timeline_display_compare_icon',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_timeline_display_compare_icon',
                                        'name' => 'lsd[display][timeline][display_compare_icon]',
                                        'value' => $timeline['display_compare_icon'] ?? '1'
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
                                        'for' => 'lsd_display_options_skin_timeline_review_stars',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_timeline_review_stars',
                                        'name' => 'lsd[display][timeline][display_review_stars]',
                                        'value' => $timeline['display_review_stars'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        <?php else: $optional_addons[] = ['reviews', esc_html__('Reviews Rate', 'listdom')]; ?>
                        <?php endif; ?>
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
