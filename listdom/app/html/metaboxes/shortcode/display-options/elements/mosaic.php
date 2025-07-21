<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */
/** @var array $price_components */

$mosaic = $options['mosaic'] ?? [];
$optional_addons = [];
?>
<div>
    <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Select the Style", 'listdom'); ?></h3>
    <p class="description lsd-mb-4 lsd-mt-3"><?php echo esc_html__("Pick a style variation of the selected skin or adjust listing elements (if not using Elementor/Divi).", 'listdom'); ?> </p>
    <div class="lsd-col-12 lsd-p-0">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_mosaic_style',
            'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle',
            'name' => 'lsd[display][mosaic][style]',
            'options' => LSD_Styles::mosaic(),
            'value' => $mosaic['style'] ?? 'style1',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_mosaic'
            ]
        ]); ?>
    </div>
</div>

<div class="lsd-form-group lsd-form-row-style-needed lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style1" id="lsd_display_options_style">
    <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Elements", 'listdom'); ?></h3>
    <p class="description lsd-mb-4"><?php echo esc_html__("You can easily customize the visibility of each element on the listing card.", 'listdom'); ?> </p>
    <div class="lsd-flex lsd-gap-2">
        <div class="lsd-form-row lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style1">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Contact Info', 'listdom'),
                'for' => 'lsd_display_options_skin_mosaic_display_contact_info',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_mosaic_display_contact_info',
                    'name' => 'lsd[display][mosaic][display_contact_info]',
                    'value' => $mosaic['display_contact_info'] ?? '1'
                ]); ?>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Location', 'listdom'),
                    'for' => 'lsd_display_options_skin_mosaic_display_location',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_mosaic_display_location',
                        'name' => 'lsd[display][mosaic][display_location]',
                        'value' => $mosaic['display_location'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Categories', 'listdom'),
                    'for' => 'lsd_display_options_skin_mosaic_display_categories',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_mosaic_display_categories',
                        'name' => 'lsd[display][mosaic][display_categories]',
                        'value' => $mosaic['display_categories'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <?php if (LSD_Components::socials()): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Share Buttons', 'listdom'),
                    'for' => 'lsd_display_options_skin_mosaic_display_share_buttons',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_mosaic_display_share_buttons',
                        'name' => 'lsd[display][mosaic][display_share_buttons]',
                        'value' => $mosaic['display_share_buttons'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (LSD_Components::pricing()): ?>
        <div class="lsd-form-row lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style1">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Price', 'listdom'),
                'for' => 'lsd_display_options_skin_mosaic_display_price',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_mosaic_display_price',
                    'name' => 'lsd[display][mosaic][display_price]',
                    'value' => $mosaic['display_price'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Title', 'listdom'),
                'for' => 'lsd_display_options_skin_mosaic_title',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_mosaic_title',
                    'name' => 'lsd[display][mosaic][display_title]',
                    'value' => $mosaic['display_title'] ?? '1',
                    'toggle' => '#lsd_display_options_skin_mosaic_is_claimed_wrapper'
                ]); ?>
            </div>
        </div>
        <?php if (class_exists(LSDADDCLM::class) || class_exists(\LSDPACCLM\Base::class)): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style1 <?php echo !isset($mosaic['display_title']) || $mosaic['display_title'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_mosaic_is_claimed_wrapper">
            <div class="lsd-form-row">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Claim Status', 'listdom'),
                    'for' => 'lsd_display_options_skin_mosaic_is_claimed',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_mosaic_is_claimed',
                        'name' => 'lsd[display][mosaic][display_is_claimed]',
                        'value' => $mosaic['display_is_claimed'] ?? '1',
                    ]); ?>
                </div>
            </div>
        </div>
        <?php else: $optional_addons[] = ['claim', esc_html__('Claim Status', 'listdom')]; ?>
        <?php endif; ?>

        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Labels', 'listdom'),
                'for' => 'lsd_display_options_skin_mosaic_display_labels',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_mosaic_display_labels',
                    'name' => 'lsd[display][mosaic][display_labels]',
                    'value' => $mosaic['display_labels'] ?? '1'
                ]); ?>
            </div>
        </div>

        <?php if (class_exists(LSDADDFAV::class) || class_exists(\LSDPACFAV\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Favorite Icon', 'listdom'),
                'for' => 'lsd_display_options_skin_mosaic_display_favorite_icon',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_mosaic_display_favorite_icon',
                    'name' => 'lsd[display][mosaic][display_favorite_icon]',
                    'value' => $mosaic['display_favorite_icon'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['favorite', esc_html__('Favorite Icon', 'listdom')]; ?>
        <?php endif; ?>

        <?php if (class_exists(LSDADDCMP::class) || class_exists(\LSDPACCMP\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Compare Icon', 'listdom'),
                'for' => 'lsd_display_options_skin_mosaic_display_compare_icon',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_mosaic_display_compare_icon',
                    'name' => 'lsd[display][mosaic][display_compare_icon]',
                    'value' => $mosaic['display_compare_icon'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['compare', esc_html__('Compare Icon', 'listdom')]; ?>
        <?php endif; ?>

        <?php if (class_exists(LSDADDREV::class) || class_exists(\LSDPACREV\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Review Rates', 'listdom'),
                'for' => 'lsd_display_options_skin_mosaic_review_stars',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_mosaic_review_stars',
                    'name' => 'lsd[display][mosaic][display_review_stars]',
                    'value' => $mosaic['display_review_stars'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['reviews', esc_html__('Reviews Rate', 'listdom')]; ?>
        <?php endif; ?>

        <?php if ($this->isPro()): ?>
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Image', 'listdom'),
                    'for' => 'lsd_display_options_skin_mosaic_display_image',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_mosaic_display_image',
                        'name' => 'lsd[display][mosaic][display_image]',
                        'value' => $mosaic['display_image'] ?? '1',
                        'toggle' => '.lsd-display-options-skin-mosaic-image-options'
                    ]); ?>
                </div>
            </div>
            <div class="lsd-display-options-skin-mosaic-image-options <?php echo !isset($mosaic['display_image']) || $mosaic['display_image'] ? '' : 'lsd-util-hide'; ?>">
                <div class="lsd-form-row">
                    <div class="lsd-col-5"><?php echo LSD_Form::label([
                        'title' => esc_html__('Image Method', 'listdom'),
                        'for' => 'lsd_display_options_skin_mosaic_image_method',
                    ]); ?></div>
                    <div class="lsd-col-6">
                        <?php echo LSD_Form::select([
                            'id' => 'lsd_display_options_skin_mosaic_image_method',
                            'name' => 'lsd[display][mosaic][image_method]',
                            'options' => [
                                'mosaic' => esc_html__('Cover', 'listdom'),
                                'slider' => esc_html__('Slider', 'listdom'),
                            ],
                            'value' => $mosaic['image_method'] ?? 'mosaic'
                        ]); ?>
                        <p class="description"><?php esc_html_e("Cover shows only featured image but slider shows all gallery images.", 'listdom'); ?></p>
                    </div>
                </div>
            </div>
            <div class="lsd-display-options-skin-mosaic-image-options <?php echo !isset($mosaic['display_image']) || $mosaic['display_image'] ? '' : 'lsd-util-hide'; ?>">
                <div class="lsd-form-row">
                    <div class="lsd-col-5"><?php echo LSD_Form::label([
                        'title' => esc_html__('Image fit', 'listdom'),
                        'for' => 'lsd_display_options_skin_mosaic_image_fit',
                    ]); ?></div>
                    <div class="lsd-col-6">
                        <?php echo LSD_Form::select([
                            'id' => 'lsd_display_options_skin_mosaic_image_fit',
                            'name' => 'lsd[display][mosaic][image_fit]',
                            'options' => [
                                'cover' => esc_html__('Cover', 'listdom'),
                                'contain' => esc_html__('Contain', 'listdom'),
                            ],
                            'value' => $mosaic['image_fit'] ?? 'cover'
                        ]); ?>
                        <p class="description"><?php esc_html_e("Cover shows featured image as object fit cover.", 'listdom'); ?></p>
                    </div>
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
