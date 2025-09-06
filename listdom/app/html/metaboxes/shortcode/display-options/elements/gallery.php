<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */
/** @var array $price_components */

$gallery = $options['gallery'] ?? [];
$optional_addons = [];
?>
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Select the Style", 'listdom'); ?></h3>
            <p class="description lsd-m-0"><?php echo esc_html__("Pick a style variation of the selected skin or adjust listing elements (if not using Elementor/Divi).", 'listdom'); ?> </p>
        </div>
        <div class="lsd-col-12 lsd-p-0">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin_gallery_style',
                'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle lsd-admin-input',
                'name' => 'lsd[display][gallery][style]',
                'options' => LSD_Styles::gallery(),
                'value' => $gallery['style'] ?? 'style1',
                'attributes' => [
                    'data-parent' => '#lsd_skin_display_options_gallery'
                ]
            ]); ?>
        </div>
    </div>

    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-form-row-style-needed lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3" id="lsd_display_options_style">
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
                                        'title' => esc_html__('Enable', 'listdom'),
                                        'for' => 'lsd_display_options_skin_gallery_display_image',
                                    ]); ?></div>
                                <div>
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_gallery_display_image',
                                        'name' => 'lsd[display][gallery][display_image]',
                                        'value' => $gallery['display_image'] ?? '1',
                                        'toggle' => '.lsd-display-options-skin-gallery-image-options'
                                    ]); ?>
                                </div>
                            </div>
                            <div class="lsd-image-row lsd-display-options-skin-gallery-image-options <?php echo !isset($gallery['display_image']) || $gallery['display_image'] ? '' : 'lsd-util-hide'; ?>">
                                <div><?php echo LSD_Form::label([
                                        'title' => esc_html__('Image Method', 'listdom'),
                                        'for' => 'lsd_display_options_skin_gallery_image_method',
                                    ]); ?></div>
                                <div>
                                    <?php echo LSD_Form::select([
                                        'class' => 'lsd-admin-input',
                                        'id' => 'lsd_display_options_skin_gallery_image_method',
                                        'name' => 'lsd[display][gallery][image_method]',
                                        'options' => [
                                            'gallery' => esc_html__('Cover', 'listdom'),
                                            'slider' => esc_html__('Slider', 'listdom'),
                                        ],
                                        'value' => $gallery['image_method'] ?? 'gallery'
                                    ]); ?>
                                    <p class="description"><?php esc_html_e("Cover shows only featured image but slider shows all gallery images.", 'listdom'); ?></p>
                                </div>
                            </div>
                            <div class="lsd-image-row lsd-display-options-skin-gallery-image-options <?php echo !isset($gallery['display_image']) || $gallery['display_image'] ? '' : 'lsd-util-hide'; ?>">
                                <div><?php echo LSD_Form::label([
                                        'title' => esc_html__('Image fit', 'listdom'),
                                        'for' => 'lsd_display_options_skin_gallery_image_fit',
                                    ]); ?></div>
                                <div>
                                    <?php echo LSD_Form::select([
                                        'class' => 'lsd-admin-input',
                                        'id' => 'lsd_display_options_skin_gallery_image_fit',
                                        'name' => 'lsd[display][gallery][image_fit]',
                                        'options' => [
                                            'cover' => esc_html__('Cover', 'listdom'),
                                            'contain' => esc_html__('Contain', 'listdom'),
                                        ],
                                        'value' => $gallery['image_fit'] ?? 'cover'
                                    ]); ?>
                                    <p class="description"><?php esc_html_e("Cover shows featured image as object fit cover.", 'listdom'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: $optional_addons[] = ['pro', esc_html__('Display Image', 'listdom')]; ?>
                <?php endif; ?>

                <div class="lsd-elements-subsection">
                    <h4 class="lsd-admin-subtitle lsd-m-0"><?php esc_html_e('Other Elements', 'listdom'); ?></h4>
                    <div class="lsd-elements-fields">
                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Categories', 'listdom'),
                                        'for' => 'lsd_display_options_skin_gallery_display_categories',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_gallery_display_categories',
                                        'name' => 'lsd[display][gallery][display_categories]',
                                        'value' => $gallery['display_categories'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (LSD_Components::socials()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
                                <div class="lsd-form-row lsd-display-options-builder-option">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Share Buttons', 'listdom'),
                                            'for' => 'lsd_display_options_skin_gallery_display_share_buttons',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_gallery_display_share_buttons',
                                            'name' => 'lsd[display][gallery][display_share_buttons]',
                                            'value' => $gallery['display_share_buttons'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="lsd-form-row lsd-display-options-builder-option">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Title', 'listdom'),
                                    'for' => 'lsd_display_options_skin_gallery_title',
                                ]); ?></div>
                            <div class="lsd-col-4">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_display_options_skin_gallery_title',
                                    'name' => 'lsd[display][gallery][display_title]',
                                    'value' => $gallery['display_title'] ?? '1',
                                    'toggle' => '#lsd_display_options_skin_gallery_is_claimed_wrapper'
                                ]); ?>
                            </div>
                        </div>
                        <?php if (class_exists(LSDADDCLM::class) || class_exists(\LSDPACCLM\Base::class)): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3 <?php echo !isset($gallery['display_title']) || $gallery['display_title'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_gallery_is_claimed_wrapper">
                                <div class="lsd-form-row">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Claim Status', 'listdom'),
                                            'for' => 'lsd_display_options_skin_gallery_is_claimed',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_gallery_is_claimed',
                                            'name' => 'lsd[display][gallery][display_is_claimed]',
                                            'value' => $gallery['display_is_claimed'] ?? '1',
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
                                    'for' => 'lsd_display_options_skin_gallery_display_labels',
                                ]); ?></div>
                            <div class="lsd-col-4">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_display_options_skin_gallery_display_labels',
                                    'name' => 'lsd[display][gallery][display_labels]',
                                    'value' => $gallery['display_labels'] ?? '1'
                                ]); ?>
                            </div>
                        </div>

                        <?php if (class_exists(LSDADDFAV::class) || class_exists(\LSDPACFAV\Base::class)): ?>
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Favorite Icon', 'listdom'),
                                        'for' => 'lsd_display_options_skin_gallery_display_favorite_icon',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_gallery_display_favorite_icon',
                                        'name' => 'lsd[display][gallery][display_favorite_icon]',
                                        'value' => $gallery['display_favorite_icon'] ?? '1'
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
                                        'for' => 'lsd_display_options_skin_gallery_display_compare_icon',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_gallery_display_compare_icon',
                                        'name' => 'lsd[display][gallery][display_compare_icon]',
                                        'value' => $gallery['display_compare_icon'] ?? '1'
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
                                        'for' => 'lsd_display_options_skin_gallery_review_stars',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_gallery_review_stars',
                                        'name' => 'lsd[display][gallery][display_review_stars]',
                                        'value' => $gallery['display_review_stars'] ?? '1'
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
