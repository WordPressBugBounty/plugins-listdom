<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */
/** @var array $price_components */

$listgrid = $options['listgrid'] ?? [];
$optional_addons = [];
?>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__('With the %s skin, you can display directories and listings in either Grid or List format using a single shortcode. Additionally, you have the option to include a map.', 'listdom'), '<strong>'.esc_html__('List+Grid', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Style', 'listdom'),
        'for' => 'lsd_display_options_skin_listgrid_style',
    ]); ?></div>
    <div class="lsd-col-6">
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
    <h3 class="lsd-my-0"><?php echo esc_html__("Elements", 'listdom'); ?></h3>
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

        <?php if (!isset($price_components['class']) || $price_components['class']): ?>
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

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Description', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_display_description',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_listgrid_display_description',
                        'name' => 'lsd[display][listgrid][display_description]',
                        'value' => $listgrid['display_description'] ?? '1',
                        'toggle' => '#lsd_display_options_skin_listgrid_description_length_wrapper'
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
                    'title' => esc_html__('Description Length', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_description_length',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::number([
                        'id' => 'lsd_display_options_skin_listgrid_description_length',
                        'name' => 'lsd[display][listgrid][description_length]',
                        'value' => $listgrid['description_length'] ?? 12
                    ]); ?>
                    <p class="description"><?php esc_html_e("Description length is measured in words, so 10 means a 10-word limit.", 'listdom'); ?></p>
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

<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Map Provider', 'listdom'),
        'for' => 'lsd_display_options_skin_listgrid_map_provider',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::providers([
            'id' => 'lsd_display_options_skin_listgrid_map_provider',
            'name' => 'lsd[display][listgrid][map_provider]',
            'value' => $listgrid['map_provider'] ?? LSD_Map_Provider::def(),
            'disabled' => true,
            'class' => 'lsd-map-provider-toggle',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_listgrid'
            ]
        ]); ?>
    </div>
</div>
<div class="lsd-form-group lsd-form-row-map-needed <?php echo isset($listgrid['map_provider']) && $listgrid['map_provider'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_listgrid_map_options">
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Position', 'listdom'),
            'for' => 'lsd_display_options_skin_listgrid_map_position',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin_listgrid_map_position',
                'name' => 'lsd[display][listgrid][map_position]',
                'options' => [
                    'top' => esc_html__('Show before the List + Grid view', 'listdom'),
                    'bottom' => esc_html__('Show after the List + Grid view', 'listdom'),
                    'left' => esc_html__('Show on left', 'listdom'),
                    'right' => esc_html__('Show on right', 'listdom')
                ],
                'value' => $listgrid['map_position'] ?? 'top'
            ]); ?>
        </div>
    </div>
    <div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Style', 'listdom'),
            'for' => 'lsd_display_options_skin_listgrid_mapstyle',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::mapstyle([
                'id' => 'lsd_display_options_skin_listgrid_mapstyle',
                'name' => 'lsd[display][listgrid][mapstyle]',
                'value' => $listgrid['mapstyle'] ?? ''
            ]); ?>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Clustering', 'listdom'),
            'for' => 'lsd_display_options_skin_listgrid_clustering',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_display_options_skin_listgrid_clustering',
                'toggle' => '#lsd_display_options_skin_listgrid_clustering_options',
                'name' => 'lsd[display][listgrid][clustering]',
                'value' => $listgrid['clustering'] ?? '1'
            ]); ?>
        </div>
    </div>
    <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
        <div id="lsd_display_options_skin_listgrid_clustering_options" <?php echo ((!isset($listgrid['clustering']) || $listgrid['clustering']) ? '' : 'style="display: none;"'); ?>>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Bubbles', 'listdom'),
                    'for' => 'lsd_display_options_skin_listgrid_clustering_images',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_display_options_skin_listgrid_clustering_images',
                        'name' => 'lsd[display][listgrid][clustering_images]',
                        'options' => LSD_Base::get_clustering_icons(),
                        'value' => $listgrid['clustering_images'] ?? 'img/cluster1/m'
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Marker/Shape On Click', 'listdom'),
            'for' => 'lsd_display_options_skin_listgrid_mapobject_onclick',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin_listgrid_mapobject_onclick',
                'name' => 'lsd[display][listgrid][mapobject_onclick]',
                'options' => [
                    'infowindow' => esc_html__('Open Infowindow', 'listdom'),
                    'redirect' => esc_html__('Redirect to Single Listing Page', 'listdom'),
                    'lightbox' => esc_html__('Open Single Listing in a Lightbox', 'listdom'),
                    'none' => esc_html__('None', 'listdom')
                ],
                'value' => $listgrid['mapobject_onclick'] ?? 'infowindow'
            ]); ?>
            <p class="description"><?php esc_html_e("You can choose to display an info window when someone clicks on a marker or shape on the map, open the single listing page directly, or show the details in a lightbox without reloading the page.", 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Search', 'listdom'),
            'for' => 'lsd_display_options_skin_listgrid_mapsearch',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php if ($this->isPro()): ?>
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_listgrid_mapsearch',
                    'name' => 'lsd[display][listgrid][mapsearch]',
                    'value' => $listgrid['mapsearch'] ?? '1',
                ]); ?>
                <p class="description"><?php esc_html_e("Provide ability to filter listings based on current map position.", 'listdom'); ?></p>
            <?php else: ?>
                <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Map Search', 'listdom')); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Limit', 'listdom'),
            'for' => 'lsd_display_options_skin_listgrid_maplimit',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::text([
                'id' => 'lsd_display_options_skin_listgrid_maplimit',
                'name' => 'lsd[display][listgrid][maplimit]',
                'value' => $listgrid['maplimit'] ?? '300'
            ]); ?>
            <p class="description"><?php esc_html_e("This option controls the number of items displayed on the map. Increasing the limit beyond 300 may significantly slow down the page loading time. We recommend using filter options to include only the listings you want in this shortcode.", 'listdom'); ?></p>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-2"><?php echo LSD_Form::label([
            'title' => esc_html__('Map Height', 'listdom'),
            'for' => 'lsd_display_options_skin_listgrid_map_height',
        ]); ?></div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::text([
                'id' => 'lsd_display_options_skin_listgrid_map_height',
                'name' => 'lsd[display][listgrid][map_height]',
                'value' => $listgrid['map_height'] ?? ''
            ]); ?>
            <p class="description"><?php esc_html_e("Use this option to set the map height. Enter a value with units, such as 500px or 100vh. If you're unsure, leave it blank.", 'listdom'); ?></p>
        </div>
    </div>

    <?php
        // Action for Third Party Plugins
        do_action('lsd_shortcode_map_options', 'listgrid', $options);
    ?>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Default View', 'listdom'),
        'for' => 'lsd_display_options_skin_listgrid_default_view',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_listgrid_default_view',
            'name' => 'lsd[display][listgrid][default_view]',
            'options' => [
                'grid' => esc_html__('Grid View', 'listdom'),
                'list' => esc_html__('List View', 'listdom')
            ],
            'value' => $listgrid['default_view'] ?? 'grid',
        ]); ?>
        <p class="description"><?php esc_html_e("Choose the default view of the shortcode.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row lsd-display-options-style-not-for lsd-display-options-style-not-for-style4">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listings Per Row (Grid)', 'listdom'),
        'for' => 'lsd_display_options_skin_listgrid_columns',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_listgrid_columns',
            'name' => 'lsd[display][listgrid][columns]',
            'options' => ['2' => 2, '3' => 3, '4' => 4, '6' => 6],
            'value' => $listgrid['columns'] ?? '3'
        ]); ?>
        <p class="description"><?php esc_html_e("Set the count of the listing cards per row for the Grid view.", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Limit', 'listdom'),
        'for' => 'lsd_display_options_skin_listgrid_limit',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'id' => 'lsd_display_options_skin_listgrid_limit',
            'name' => 'lsd[display][listgrid][limit]',
            'value' => $listgrid['limit'] ?? '12'
        ]); ?>
        <p class="description"><?php echo sprintf(esc_html__("Number of the Listings per page. It should be a multiple of the %s option. For example if the %s is set to 3, then you should set the limit to 3, 6, 9, 12, 30, etc.", 'listdom'), '<strong>'.esc_html__('Listings Per Row', 'listdom').'</strong>', '<strong>'.esc_html__('Listings Per Row', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Pagination Method', 'listdom'),
        'for' => 'lsd_display_options_skin_listgrid_pagination',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_listgrid_pagination',
            'name' => 'lsd[display][listgrid][pagination]',
            'value' => $listgrid['pagination'] ?? (isset($listgrid['load_more']) && $listgrid['load_more'] == 0 ? 'disabled' : 'loadmore'),
            'options' => [
                'loadmore' => esc_html__('Load More Button', 'listdom'),
                'scroll' => esc_html__('Infinite Scroll', 'listdom'),
                'disabled' => esc_html__('Disabled', 'listdom'),
            ],
        ]); ?>
        <p class="description"><?php esc_html_e('Choose how to load additional listings more than the default limit.', 'listdom'); ?></p>
    </div>
</div>

<?php $this->field_listing_link('listgrid', $listgrid);
