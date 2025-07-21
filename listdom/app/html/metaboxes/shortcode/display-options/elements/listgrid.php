<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */
/** @var array $price_components */

$listgrid = $options['listgrid'] ?? [];
$optional_addons = [];
?>
<div>
    <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Select the Style", 'listdom'); ?></h3>
    <p class="description lsd-mb-4 lsd-mt-3"><?php echo esc_html__("Pick a style variation of the selected skin or adjust listing elements (if not using Elementor/Divi).", 'listdom'); ?> </p>
    <div class="lsd-col-12 lsd-p-0">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_listgrid_style',
            'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle',
            'name' => 'lsd[display][listgrid][style]',
            'options' => LSD_Styles::listgrid(),
            'value' => $listgrid['style'] ?? 'style1',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_listgrid'
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
                'for' => 'lsd_display_options_skin_listgrid_display_contact_info',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_listgrid_display_contact_info',
                    'name' => 'lsd[display][listgrid][display_contact_info]',
                    'value' => $listgrid['display_contact_info'] ?? '1'
                ]); ?>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Location', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_display_location',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_display_location',
                        'name' => 'lsd[display][listgrid][display_location]',
                        'value' => $listgrid['display_location'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <?php if (LSD_Components::pricing() && (!isset($price_components['class']) || $price_components['class'])): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Price Class', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_display_price_class',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_display_price_class',
                        'name' => 'lsd[display][listgrid][display_price_class]',
                        'value' => $listgrid['display_price_class'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (LSD_Components::map()): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4">
            <div class="lsd-form-row">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Address', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_display_address',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_display_address',
                        'name' => 'lsd[display][listgrid][display_address]',
                        'value' => $listgrid['display_address'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (LSD_Components::work_hours()): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row ">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Work Hours', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_display_availability',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_display_availability',
                        'name' => 'lsd[display][listgrid][display_availability]',
                        'value' => $listgrid['display_availability'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Content', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_display_description',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_display_description',
                        'name' => 'lsd[display][listgrid][display_description]',
                        'value' => $listgrid['display_description'] ?? '1',
                        'toggle' => '#lsd_display_options_skin_listgrid_description_length_wrapper, #lsd_display_options_skin_listgrid_content_type_wrapper'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Categories', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_display_categories',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_display_categories',
                        'name' => 'lsd[display][listgrid][display_categories]',
                        'value' => $listgrid['display_categories'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <?php if (LSD_Components::pricing()): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style4">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Price', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_display_price',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_display_price',
                        'name' => 'lsd[display][listgrid][display_price]',
                        'value' => $listgrid['display_price'] ?? '1'
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
                    'for' => 'lsd_display_options_skin_listgrid_display_share_buttons',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_display_share_buttons',
                        'name' => 'lsd[display][listgrid][display_share_buttons]',
                        'value' => $listgrid['display_share_buttons'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Labels', 'listdom'),
                'for' => 'lsd_display_options_skin_listgrid_display_labels',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_listgrid_display_labels',
                    'name' => 'lsd[display][listgrid][display_labels]',
                    'value' => $listgrid['display_labels'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php if (class_exists(LSDADDFAV::class) || class_exists(\LSDPACFAV\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Favorite Icon', 'listdom'),
                'for' => 'lsd_display_options_skin_listgrid_display_favorite_icon',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_listgrid_display_favorite_icon',
                    'name' => 'lsd[display][listgrid][display_favorite_icon]',
                    'value' => $listgrid['display_favorite_icon'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['favorite', esc_html__('Favorite Icon', 'listdom')]; ?>
        <?php endif; ?>

        <?php if (class_exists(LSDADDCMP::class) || class_exists(\LSDPACCMP\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Compare Icon', 'listdom'),
                'for' => 'lsd_display_options_skin_listgrid_display_compare_icon',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_listgrid_display_compare_icon',
                    'name' => 'lsd[display][listgrid][display_compare_icon]',
                    'value' => $listgrid['display_compare_icon'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['compare', esc_html__('Compare Icon', 'listdom')]; ?>
        <?php endif; ?>

        <?php if (class_exists(LSDADDREV::class) || class_exists(\LSDPACREV\Base::class)): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Review Rates', 'listdom'),
                'for' => 'lsd_display_options_skin_listgrid_review_stars',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_listgrid_review_stars',
                    'name' => 'lsd[display][listgrid][display_review_stars]',
                    'value' => $listgrid['display_review_stars'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: $optional_addons[] = ['reviews', esc_html__('Reviews Rate', 'listdom')]; ?>
        <?php endif; ?>

        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Title', 'listdom'),
                'for' => 'lsd_display_options_skin_listgrid_title',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_listgrid_title',
                    'name' => 'lsd[display][listgrid][display_title]',
                    'value' => $listgrid['display_title'] ?? '1',
                    'toggle' => '#lsd_display_options_skin_listgrid_is_claimed_wrapper'
                ]); ?>
            </div>
        </div>
        <?php if (class_exists(LSDADDCLM::class) || class_exists(\LSDPACCLM\Base::class)): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4 <?php echo !isset($listgrid['display_title']) || $listgrid['display_title'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_listgrid_is_claimed_wrapper">
            <div class="lsd-form-row">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Claim Status', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_is_claimed',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_is_claimed',
                        'name' => 'lsd[display][listgrid][display_is_claimed]',
                        'value' => $listgrid['display_is_claimed'] ?? '1',
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
                    'for' => 'lsd_display_options_skin_listgrid_display_image',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_display_image',
                        'name' => 'lsd[display][listgrid][display_image]',
                        'value' => $listgrid['display_image'] ?? '1',
                        'toggle' => '.lsd-display-options-skin-listgrid-image-options'
                    ]); ?>
                </div>
            </div>
            <div class="lsd-display-options-skin-listgrid-image-options <?php echo !isset($listgrid['display_image']) || $listgrid['display_image'] ? '' : 'lsd-util-hide'; ?>">
                <div class="lsd-form-row">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Image Method', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_image_method',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_display_options_skin_listgrid_image_method',
                        'name' => 'lsd[display][listgrid][image_method]',
                        'options' => [
                            'cover' => esc_html__('Cover', 'listdom'),
                            'slider' => esc_html__('Slider', 'listdom'),
                        ],
                        'value' => $listgrid['image_method'] ?? 'cover'
                    ]); ?>
                    <p class="description"><?php esc_html_e("Cover shows only featured image but slider shows all gallery images.", 'listdom'); ?></p>
                </div>
            </div>
            </div>
            <div class="lsd-display-options-skin-listgrid-image-options">
                <div class="lsd-form-row <?php echo (!isset($listgrid['display_image']) || $listgrid['display_image']) ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-col-5"><?php echo LSD_Form::label([
                        'title' => esc_html__('Image fit', 'listdom'),
                        'for' => 'lsd_display_options_skin_listgrid_image_fit',
                    ]); ?></div>
                    <div class="lsd-col-6">
                        <?php echo LSD_Form::select([
                            'id' => 'lsd_display_options_skin_listgrid_image_fit',
                            'name' => 'lsd[display][listgrid][image_fit]',
                            'options' => [
                                'cover' => esc_html__('Cover', 'listdom'),
                                'contain' => esc_html__('Contain', 'listdom'),
                            ],
                            'value' => $listgrid['image_fit'] ?? 'cover'
                        ]); ?>
                        <p class="description"><?php esc_html_e("Cover shows featured image as object fit cover.", 'listdom'); ?></p>
                    </div>
                </div>
            </div>
        <?php else: $optional_addons[] = ['pro', esc_html__('Display Image', 'listdom')]; ?>
        <?php endif; ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row <?php echo !isset($listgrid['display_description']) || $listgrid['display_description'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_listgrid_description_length_wrapper">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Content Length', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_description_length',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::number([
                        'id' => 'lsd_display_options_skin_listgrid_description_length',
                        'name' => 'lsd[display][listgrid][description_length]',
                        'value' => $listgrid['description_length'] ?? 12
                    ]); ?>
                    <p class="description"><?php esc_html_e("Content length is measured in words, so 10 means a 10-word limit.", 'listdom'); ?></p>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row <?php echo !isset($listgrid['display_description']) || $listgrid['display_description'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_listgrid_content_type_wrapper">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Content Type', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_content_type',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_display_options_skin_listgrid_content_type',
                        'name' => 'lsd[display][listgrid][content_type]',
                        'value' => $listgrid['content_type'] ?? 'excerpt',
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
