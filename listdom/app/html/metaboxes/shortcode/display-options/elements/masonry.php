<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */
/** @var array $price_components */

$masonry = $options['masonry'] ?? [];
$optional_addons = [];
?>
<div>
    <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Select the Style", 'listdom'); ?></h3>
    <p class="description lsd-mb-4 lsd-mt-3"><?php echo esc_html__("Pick a style variation of the selected skin or adjust listing elements (if not using Elementor/Divi).", 'listdom'); ?> </p>
    <div class="lsd-col-12 lsd-p-0">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_masonry_style',
            'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle',
            'name' => 'lsd[display][masonry][style]',
            'options' => LSD_Styles::masonry(),
            'value' => $masonry['style'] ?? 'style1',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_masonry'
            ]
        ]); ?>
    </div>
</div>

<div class="lsd-form-group lsd-form-row-style-needed lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4" id="lsd_display_options_style">
    <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Elements", 'listdom'); ?></h3>
    <p class="description lsd-mb-4"><?php echo esc_html__("You can easily customize the visibility of each element on the listing card.", 'listdom'); ?> </p>
    <div class="lsd-flex lsd-gap-2">
        <div class="lsd-form-row lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style4">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Contact Info', 'listdom'),
                'for' => 'lsd_display_options_skin_masonry_display_contact_info',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_masonry_display_contact_info',
                    'name' => 'lsd[display][masonry][display_contact_info]',
                    'value' => $masonry['display_contact_info'] ?? '1'
                ]); ?>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Location', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_display_location',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_masonry_display_location',
                        'name' => 'lsd[display][masonry][display_location]',
                        'value' => $masonry['display_location'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <?php if (LSD_Components::pricing() && (!isset($price_components['class']) || $price_components['class'])): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Price Class', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_display_price_class',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_masonry_display_price_class',
                        'name' => 'lsd[display][masonry][display_price_class]',
                        'value' => $masonry['display_price_class'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Content', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_display_description',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_masonry_display_description',
                        'name' => 'lsd[display][masonry][display_description]',
                        'value' => $masonry['display_description'] ?? '1',
                        'toggle' => '#lsd_display_options_skin_masonry_description_length_wrapper, #lsd_display_options_skin_masonry_content_type_wrapper'
                    ]); ?>
                </div>
            </div>
        </div>

        <?php if (LSD_Components::map()): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4">
            <div class="lsd-form-row">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Address', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_display_address',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_masonry_display_address',
                        'name' => 'lsd[display][masonry][display_address]',
                        'value' => $masonry['display_address'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (LSD_Components::work_hours()): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Work Hours', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_display_availability',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_masonry_display_availability',
                        'name' => 'lsd[display][masonry][display_availability]',
                        'value' => $masonry['display_availability'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Categories', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_display_categories',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_masonry_display_categories',
                        'name' => 'lsd[display][masonry][display_categories]',
                        'value' => $masonry['display_categories'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <?php if (LSD_Components::pricing()): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style4">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Price', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_display_price',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_masonry_display_price',
                        'name' => 'lsd[display][masonry][display_price]',
                        'value' => $masonry['display_price'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (LSD_Components::socials()): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style4">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Share Buttons', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_display_share_buttons',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_masonry_display_share_buttons',
                        'name' => 'lsd[display][masonry][display_share_buttons]',
                        'value' => $masonry['display_share_buttons'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Labels', 'listdom'),
                'for' => 'lsd_display_options_skin_masonry_display_labels',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_masonry_display_labels',
                    'name' => 'lsd[display][masonry][display_labels]',
                    'value' => $masonry['display_labels'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php if (class_exists(LSDADDFAV::class) || class_exists(\LSDPACFAV\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Favorite Icon', 'listdom'),
                'for' => 'lsd_display_options_skin_masonry_display_favorite_icon',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_masonry_display_favorite_icon',
                    'name' => 'lsd[display][masonry][display_favorite_icon]',
                    'value' => $masonry['display_favorite_icon'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['favorite', esc_html__('Favorite Icon', 'listdom')]; ?>
        <?php endif; ?>

        <?php if (class_exists(LSDADDCMP::class) || class_exists(\LSDPACCMP\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Compare Icon', 'listdom'),
                'for' => 'lsd_display_options_skin_masonry_display_compare_icon',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_masonry_display_compare_icon',
                    'name' => 'lsd[display][masonry][display_compare_icon]',
                    'value' => $masonry['display_compare_icon'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['compare', esc_html__('Compare Icon', 'listdom')]; ?>
        <?php endif; ?>

        <?php if (class_exists(LSDADDREV::class) || class_exists(\LSDPACREV\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Review Rates', 'listdom'),
                'for' => 'lsd_display_options_skin_masonry_review_stars',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_masonry_review_stars',
                    'name' => 'lsd[display][masonry][display_review_stars]',
                    'value' => $masonry['display_review_stars'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['reviews', esc_html__('Reviews Rate', 'listdom')]; ?>
        <?php endif; ?>

        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Title', 'listdom'),
                'for' => 'lsd_display_options_skin_masonry_title',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_masonry_title',
                    'name' => 'lsd[display][masonry][display_title]',
                    'value' => $masonry['display_title'] ?? '1',
                    'toggle' => '#lsd_display_options_skin_masonry_is_claimed_wrapper'
                ]); ?>
            </div>
        </div>
        <?php if (class_exists(LSDADDCLM::class) || class_exists(\LSDPACCLM\Base::class)): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4 <?php echo !isset($masonry['display_title']) || $masonry['display_title'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_masonry_is_claimed_wrapper">
            <div class="lsd-form-row">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Claim Status', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_is_claimed',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_masonry_is_claimed',
                        'name' => 'lsd[display][masonry][display_is_claimed]',
                        'value' => $masonry['display_is_claimed'] ?? '1',
                    ]); ?>
                </div>
            </div>
        </div>
        <?php else: $optional_addons[] = ['claim', esc_html__('Claim Status', 'listdom')]; ?>
        <?php endif; ?>

        <?php if ($this->isPro()): ?>
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Image', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_display_image',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_masonry_display_image',
                        'name' => 'lsd[display][masonry][display_image]',
                        'value' => $masonry['display_image'] ?? '1',
                        'toggle' => '.lsd-display-options-skin-masonry-image-options'
                    ]); ?>
                </div>
            </div>
            <div class="lsd-display-options-skin-masonry-image-options <?php echo !isset($masonry['display_image']) || $masonry['display_image'] ? '' : 'lsd-util-hide'; ?>">
                <div class="lsd-form-row">
                    <div class="lsd-col-5"><?php echo LSD_Form::label([
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
            </div>
            <div class="lsd-display-options-skin-masonry-image-options <?php echo !isset($masonry['display_image']) || $masonry['display_image'] ? '' : 'lsd-util-hide'; ?>">
                <div class="lsd-form-row">
                    <div class="lsd-col-5"><?php echo LSD_Form::label([
                        'title' => esc_html__('Image fit', 'listdom'),
                        'for' => 'lsd_display_options_skin_masonry_image_fit',
                    ]); ?></div>
                    <div class="lsd-col-6">
                        <?php echo LSD_Form::select([
                            'id' => 'lsd_display_options_skin_masonry_image_fit',
                            'name' => 'lsd[display][masonry][image_fit]',
                            'options' => [
                                'cover' => esc_html__('Cover', 'listdom'),
                                'contain' => esc_html__('Contain', 'listdom'),
                            ],
                            'value' => $masonry['image_fit'] ?? 'cover'
                        ]); ?>
                        <p class="description"><?php esc_html_e("Cover shows featured image as object fit cover.", 'listdom'); ?></p>
                    </div>
                </div>
            </div>
        <?php else: $optional_addons[] = ['pro', esc_html__('Display Image', 'listdom')]; ?>
        <?php endif; ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row <?php echo !isset($masonry['display_description']) || $masonry['display_description'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_masonry_description_length_wrapper">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Content Length', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_description_length',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::number([
                        'id' => 'lsd_display_options_skin_masonry_description_length',
                        'name' => 'lsd[display][masonry][description_length]',
                        'value' => $masonry['description_length'] ?? 10
                    ]); ?>
                    <p class="description"><?php esc_html_e("Content length is measured in words, so 10 means a 10-word limit.", 'listdom'); ?></p>
                </div>
            </div>
        </div>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row <?php echo !isset($masonry['display_description']) || $masonry['display_description'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_masonry_content_type_wrapper">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Content Type', 'listdom'),
                    'for' => 'lsd_display_options_skin_masonry_content_type',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_display_options_skin_masonry_content_type',
                        'name' => 'lsd[display][masonry][content_type]',
                        'value' => $masonry['content_type'] ?? 'excerpt',
                        'options' => [
                            'description' => esc_html__('Description', 'listdom'),
                            'excerpt' => esc_html__('Excerpt', 'listdom'),
                        ]
                    ]); ?>
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
