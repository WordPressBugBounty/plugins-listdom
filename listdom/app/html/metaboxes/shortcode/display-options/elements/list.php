<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */
/** @var array $price_components */

$list = $options['list'] ?? [];
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
                'id' => 'lsd_display_options_skin_list_style',
                'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle lsd-admin-input',
                'name' => 'lsd[display][list][style]',
                'options' => LSD_Styles::list(),
                'value' => $list['style'] ?? 'style1',
                'attributes' => [
                    'data-parent' => '#lsd_skin_display_options_list'
                ]
            ]); ?>
        </div>
    </div>

    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-form-row-style-needed lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style4 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3" id="lsd_display_options_style">
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
                                <div><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Enable', 'listdom'),
                                        'for' => 'lsd_display_options_skin_list_display_image',
                                    ]); ?></div>
                                <div>
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_list_display_image',
                                        'name' => 'lsd[display][list][display_image]',
                                        'value' => $list['display_image'] ?? '1',
                                        'toggle' => '.lsd-display-options-skin-list-image-options'
                                    ]); ?>
                                </div>
                            </div>
                            <div class="lsd-image-row lsd-display-options-skin-list-image-options <?php echo !isset($list['display_image']) || $list['display_image'] ? '' : 'lsd-util-hide'; ?>">
                                <div><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Image Method', 'listdom'),
                                        'for' => 'lsd_display_options_skin_list_image_method',
                                    ]); ?></div>
                                <div>
                                    <?php echo LSD_Form::select([
                                        'class' => 'lsd-admin-input',
                                        'id' => 'lsd_display_options_skin_list_image_method',
                                        'name' => 'lsd[display][list][image_method]',
                                        'options' => [
                                            'cover' => esc_html__('Cover', 'listdom'),
                                            'slider' => esc_html__('Slider', 'listdom'),
                                        ],
                                        'value' => $list['image_method'] ?? 'cover'
                                    ]); ?>
                                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Cover shows only featured image but slider shows all gallery images.", 'listdom'); ?></p>
                                </div>
                            </div>
                            <div class="lsd-image-row lsd-display-options-skin-list-image-options <?php echo !isset($list['display_image']) || $list['display_image'] ? '' : 'lsd-util-hide'; ?>">
                                <div><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Image fit', 'listdom'),
                                        'for' => 'lsd_display_options_skin_list_image_fit',
                                    ]); ?></div>
                                <div>
                                    <?php echo LSD_Form::select([
                                        'class' => 'lsd-admin-input',
                                        'id' => 'lsd_display_options_skin_list_image_fit',
                                        'name' => 'lsd[display][list][image_fit]',
                                        'options' => [
                                            'cover' => esc_html__('Cover', 'listdom'),
                                            'contain' => esc_html__('Contain', 'listdom'),
                                        ],
                                        'value' => $list['image_fit'] ?? 'cover'
                                    ]); ?>
                                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Cover shows featured image as object fit cover.", 'listdom'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: $optional_addons[] = ['pro', esc_html__('Display Image', 'listdom')]; ?>
                <?php endif; ?>

                <div class="lsd-elements-subsection lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
                    <h4 class="lsd-admin-subtitle lsd-m-0"><?php esc_html_e('Content', 'listdom'); ?></h4>
                    <div class="lsd-elements-fields lsd-elements-3-column">
                        <div class="lsd-content-row lsd-display-options-builder-option">
                            <div><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Enable', 'listdom'),
                                    'for' => 'lsd_display_options_skin_list_display_description',
                                ]); ?></div>
                            <div>
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_display_options_skin_list_display_description',
                                    'name' => 'lsd[display][list][display_description]',
                                    'value' => $list['display_description'] ?? '1',
                                    'toggle' => '#lsd_display_options_skin_list_description_length_wrapper, #lsd_display_options_skin_list_content_type_wrapper'
                                ]); ?>
                            </div>
                        </div>

                        <div class="lsd-content-row <?php echo !isset($list['display_description']) || $list['display_description'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_list_description_length_wrapper">
                            <div><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Content Length', 'listdom'),
                                    'for' => 'lsd_display_options_skin_list_description_length',
                                ]); ?></div>
                            <div>
                                <?php echo LSD_Form::number([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_display_options_skin_list_description_length',
                                    'name' => 'lsd[display][list][description_length]',
                                    'value' => $list['description_length'] ?? 12
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Content length is measured in words, so 10 means a 10-word limit.", 'listdom'); ?></p>
                            </div>
                        </div>
                        <div class="lsd-content-row <?php echo !isset($list['display_description']) || $list['display_description'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_list_content_type_wrapper">
                            <div><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Content Type', 'listdom'),
                                    'for' => 'lsd_display_options_skin_list_content_type',
                                ]); ?></div>
                            <div>
                                <?php echo LSD_Form::select([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_display_options_skin_list_content_type',
                                    'name' => 'lsd[display][list][content_type]',
                                    'options' => [
                                        'description' => esc_html__('Description', 'listdom'),
                                        'excerpt' => esc_html__('Excerpt', 'listdom'),
                                    ],
                                    'value' => $list['content_type'] ?? 'excerpt',
                                ]); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lsd-elements-subsection">
                    <h4 class="lsd-admin-subtitle lsd-m-0"><?php esc_html_e('Other Elements', 'listdom'); ?></h4>
                    <div class="lsd-elements-fields">
                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style4">
                            <div class="lsd-form-row">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Contact Info', 'listdom'),
                                        'for' => 'lsd_display_options_skin_list_display_contact_info',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_list_display_contact_info',
                                        'name' => 'lsd[display][list][display_contact_info]',
                                        'value' => $list['display_contact_info'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Location', 'listdom'),
                                        'for' => 'lsd_display_options_skin_list_display_location',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_list_display_location',
                                        'name' => 'lsd[display][list][display_location]',
                                        'value' => $list['display_location'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (LSD_Components::pricing() && (!isset($price_components['class']) || $price_components['class'])): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3">
                                <div class="lsd-form-row lsd-display-options-builder-option">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Price Class', 'listdom'),
                                            'for' => 'lsd_display_options_skin_list_display_price_class',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_list_display_price_class',
                                            'name' => 'lsd[display][list][display_price_class]',
                                            'value' => $list['display_price_class'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (LSD_Components::pricing()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style4 lsd-display-options-style-dependency-style2">
                                <div class="lsd-form-row lsd-display-options-builder-option">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Price', 'listdom'),
                                            'for' => 'lsd_display_options_skin_list_display_price',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_list_display_price',
                                            'name' => 'lsd[display][list][display_price]',
                                            'value' => $list['display_price'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (LSD_Components::socials()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style4 lsd-display-options-style-dependency-style2">
                                <div class="lsd-form-row lsd-display-options-builder-option">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Share Buttons', 'listdom'),
                                            'for' => 'lsd_display_options_skin_list_display_share_buttons',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_list_display_share_buttons',
                                            'name' => 'lsd[display][list][display_share_buttons]',
                                            'value' => $list['display_share_buttons'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (LSD_Components::map()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style3">
                                <div class="lsd-form-row">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Address', 'listdom'),
                                            'for' => 'lsd_display_options_skin_list_display_address',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_list_display_address',
                                            'name' => 'lsd[display][list][display_address]',
                                            'value' => $list['display_address'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (LSD_Components::work_hours()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
                                <div class="lsd-form-row lsd-display-options-builder-option">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Work Hours', 'listdom'),
                                            'for' => 'lsd_display_options_skin_list_display_availability',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_list_display_availability',
                                            'name' => 'lsd[display][list][display_availability]',
                                            'value' => $list['display_availability'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Categories', 'listdom'),
                                        'for' => 'lsd_display_options_skin_list_display_categories',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_list_display_categories',
                                        'name' => 'lsd[display][list][display_categories]',
                                        'value' => $list['display_categories'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <div class="lsd-form-row lsd-display-options-builder-option">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Labels', 'listdom'),
                                    'for' => 'lsd_display_options_skin_list_display_labels',
                                ]); ?></div>
                            <div class="lsd-col-4">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_display_options_skin_list_display_labels',
                                    'name' => 'lsd[display][list][display_labels]',
                                    'value' => $list['display_labels'] ?? '1'
                                ]); ?>
                            </div>
                        </div>
                        <?php if (class_exists(LSDADDFAV::class) || class_exists(\LSDPACFAV\Base::class)): ?>
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Favorite Icon', 'listdom'),
                                        'for' => 'lsd_display_options_skin_list_display_favorite_icon',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_list_display_favorite_icon',
                                        'name' => 'lsd[display][list][display_favorite_icon]',
                                        'value' => $list['display_favorite_icon'] ?? '1'
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
                                        'for' => 'lsd_display_options_skin_list_display_compare_icon',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_list_display_compare_icon',
                                        'name' => 'lsd[display][list][display_compare_icon]',
                                        'value' => $list['display_compare_icon'] ?? '1'
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
                                        'for' => 'lsd_display_options_skin_list_display_review_stars',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_list_display_review_stars',
                                        'name' => 'lsd[display][list][display_review_stars]',
                                        'value' => $list['display_review_stars'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        <?php else: $optional_addons[] = ['reviews', esc_html__('Reviews Rate', 'listdom')]; ?>
                        <?php endif; ?>

                        <div class="lsd-form-row lsd-display-options-builder-option">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Title', 'listdom'),
                                    'for' => 'lsd_display_options_skin_list_title',
                                ]); ?></div>
                            <div class="lsd-col-4">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_display_options_skin_list_title',
                                    'name' => 'lsd[display][list][display_title]',
                                    'value' => $list['display_title'] ?? '1',
                                    'toggle' => '#lsd_display_options_skin_list_is_claimed_wrapper'
                                ]); ?>
                            </div>
                        </div>
                        <?php if (class_exists(LSDADDCLM::class) || class_exists(\LSDPACCLM\Base::class)): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4 <?php echo !isset($list['display_title']) || $list['display_title'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_list_is_claimed_wrapper">
                                <div class="lsd-form-row">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Claim Status', 'listdom'),
                                            'for' => 'lsd_display_options_skin_list_is_claimed',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_list_is_claimed',
                                            'name' => 'lsd[display][list][display_is_claimed]',
                                            'value' => $list['display_is_claimed'] ?? '1',
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: $optional_addons[] = ['claim', esc_html__('Claim Status', 'listdom')]; ?>
                        <?php endif; ?>


                    </div>
                    <?php if (count($optional_addons)): ?>
                        <div class="lsd-alert-no-my lsd-mt-5">
                            <?php echo LSD_Base::alert(LSD_Base::optionalAddonsMessage($optional_addons),'warning'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

