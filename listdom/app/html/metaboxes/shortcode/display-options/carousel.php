<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$carousel = $options['carousel'] ?? [];
$missAddonMessages = [];
?>
<div class="lsd-form-row lsd-form-row-separator"></div>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__('Using the %s skin, you can show a carousel of the directories and listings in different styles. Only listings with featured images are displayed.', 'listdom'), '<strong>'.esc_html__('Carousel', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Style', 'listdom'),
        'for' => 'lsd_display_options_skin_carousel_style',
    ]); ?></div>
    <div class="lsd-col-6 lsd-style-picker">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_carousel_style',
            'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle',
            'name' => 'lsd[display][carousel][style]',
            'options' => LSD_Styles::carousel(),
            'value' => $carousel['style'] ?? 'style1',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_carousel'
            ]
        ]); ?>
    </div>
</div>

<div class="lsd-form-group lsd-form-row-style-needed lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4 lsd-display-options-style-dependency-style5" id="lsd_display_options_style">
    <h3 class="lsd-mb-0 lsd-mt-1"><?php echo esc_html__("Elements Display Options", 'listdom'); ?></h3>
    <p class="description lsd-mb-4"><?php echo esc_html__("You can simply change the visibility of each element that you want on listing card", 'listdom'); ?> </p>
    <div class="lsd-flex lsd-gap-2">
        <div class="lsd-form-row lsd-display-options-style-dependency lsd-display-options-style-dependency-style1">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Contact Info', 'listdom'),
                'for' => 'lsd_display_options_skin_carousel_display_contact_info',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_carousel_display_contact_info',
                    'name' => 'lsd[display][carousel][display_contact_info]',
                    'value' => $carousel['display_contact_info'] ?? '1'
                ]); ?>
            </div>
        </div>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Location', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_location',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_location',
                        'name' => 'lsd[display][carousel][display_location]',
                        'value' => $carousel['display_location'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style5">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Price Class', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_price_class',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_price_class',
                        'name' => 'lsd[display][carousel][display_price_class]',
                        'value' => $carousel['display_price_class'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php if(class_exists('LSDADDFAV_Base')): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style5">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Favorite Icon', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_favorite_icon',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_favorite_icon',
                        'name' => 'lsd[display][carousel][display_favorite_icon]',
                        'value' => $carousel['display_favorite_icon'] ?? '0'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php else: ?>
            <?php $missAddonMessages[] = LSD_Base::missAddonMessage('Favorite', esc_html__('Favorite icon', 'listdom')); ?>
        <?php endif; ?>
        <?php if(class_exists('LSDADDCMP_Base')): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style5">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Compare Icon', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_compare_icon',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_compare_icon',
                        'name' => 'lsd[display][carousel][display_compare_icon]',
                        'value' => $carousel['display_compare_icon'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php else: ?>
            <?php $missAddonMessages[] = LSD_Base::missAddonMessage('Compare', esc_html__('Compare icon', 'listdom')); ?>
        <?php endif; ?>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style5 ">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Labels', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_labels',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_labels',
                        'name' => 'lsd[display][carousel][display_labels]',
                        'value' => $carousel['display_labels'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style4 lsd-display-options-style-dependency-style5">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Work Hours', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_availability',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_availability',
                        'name' => 'lsd[display][carousel][display_availability]',
                        'value' => $carousel['display_availability'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style4 lsd-display-options-style-dependency-style5">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Categories', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_categories',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_categories',
                        'name' => 'lsd[display][carousel][display_categories]',
                        'value' => $carousel['display_categories'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style5">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Description', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_description',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_description',
                        'name' => 'lsd[display][carousel][display_description]',
                        'value' => $carousel['display_description'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style4">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Price', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_price',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_price',
                        'name' => 'lsd[display][carousel][display_price]',
                        'value' => $carousel['display_price'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 ">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Share Buttons', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_share_buttons',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_share_buttons',
                        'name' => 'lsd[display][carousel][display_share_buttons]',
                        'value' => $carousel['display_share_buttons'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4 lsd-display-options-style-dependency-style5">
            <div class="lsd-form-row">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Address', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_address',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_address',
                        'name' => 'lsd[display][carousel][display_address]',
                        'value' => $carousel['display_address'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php if(class_exists('LSDADDREV_Base')): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Review Rates', 'listdom'),
                'for' => 'lsd_display_options_skin_carousel_review_stars',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_carousel_review_stars',
                    'name' => 'lsd[display][carousel][display_review_stars]',
                    'value' => $carousel['display_review_stars'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: ?>
            <?php $missAddonMessages[] = LSD_Base::missAddonMessage('Reviews', esc_html__('Reviews Rate', 'listdom')); ?>
        <?php endif; ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Title', 'listdom'),
                'for' => 'lsd_display_options_skin_carousel_title',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_carousel_title',
                    'name' => 'lsd[display][carousel][display_title]',
                    'value' => $carousel['display_title'] ?? '1',
                    'toggle' => '#lsd_display_options_skin_carousel_is_claimed'
                ]); ?>
            </div>
        </div>
        <?php if(class_exists('LSDADDCLM_Base')): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style5">
            <div class="lsd-form-row <?php echo (!isset($carousel['display_title']) || $carousel['display_title']) ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_carousel_is_claimed">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Is Claimed', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_is_claimed',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_is_claimed',
                        'name' => 'lsd[display][carousel][display_is_claimed]',
                        'value' => $carousel['display_is_claimed'] ?? '1',
                    ]); ?>
                </div>
            </div>
        </div>
        <?php else: ?>
            <?php $missAddonMessages[] = LSD_Base::missAddonMessage('Claim', esc_html__('Is claimed', 'listdom')); ?>
        <?php endif; ?>

        <?php if($this->isPro()): ?>
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Display Image', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_display_image',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_carousel_display_image',
                        'name' => 'lsd[display][carousel][display_image]',
                        'value' => $carousel['display_image'] ?? '1',
                        'toggle' => '#lsd_display_options_skin_carousel_image_method'
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row <?php echo (!isset($carousel['display_image']) || $carousel['display_image']) ? '1' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_carousel_image_method">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Image Method', 'listdom'),
                    'for' => 'lsd_display_options_skin_carousel_image_method',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_display_options_skin_carousel_image_method',
                        'name' => 'lsd[display][carousel][image_method]',
                        'options' => [
                            'cover' => esc_html__('Cover', 'listdom'),
                            'slider' => esc_html__('Slider', 'listdom'),
                        ],
                        'value' => $carousel['image_method'] ?? 'cover'
                    ]); ?>
                    <p class="description"><?php esc_html_e("Cover shows only featured image but slider shows all gallery images.", 'listdom'); ?></p>
                </div>
            </div>
        <?php else: ?>
            <?php $missAddonMessages[] = LSD_Base::missFeatureMessage(esc_html__('Display Image', 'listdom'), true); ?>
        <?php endif; ?>
    </div>
    <?php if(count($missAddonMessages)): ?>
    <div class="lsd-addon-alert lsd-mt-4">
        <?php foreach ($missAddonMessages as $alert) echo LSD_Base::alert($alert,'warning'); ?>
    </div>
    <?php endif; ?>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listings Per Row', 'listdom'),
        'for' => 'lsd_display_options_skin_carousel_columns',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_carousel_columns',
            'name' => 'lsd[display][carousel][columns]',
            'options' => ['1'=>1, '2'=>2, '3'=>3, '4'=>4, '6'=>6],
            'value' => $carousel['columns'] ?? '3'
        ]); ?>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Limit', 'listdom'),
        'for' => 'lsd_display_options_skin_carousel_limit',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'id' => 'lsd_display_options_skin_carousel_limit',
            'name' => 'lsd[display][carousel][limit]',
            'value' => $carousel['limit'] ?? '8'
        ]); ?>
    </div>
</div>

<?php if($this->isPro()): ?>
<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listing Link', 'listdom'),
        'for' => 'lsd_display_options_skin_carousel_listing_link',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_carousel_listing_link',
            'name' => 'lsd[display][carousel][listing_link]',
            'value' => $carousel['listing_link'] ?? 'normal',
            'options' => LSD_Base::get_listing_link_methods(),
        ]); ?>
        <p class="description"><?php esc_html_e("Link to listing detail page.", 'listdom'); ?></p>
    </div>
</div>
<?php else: ?>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-6">
        <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Listing Link', 'listdom')); ?></p>
    </div>
</div>
<?php endif; ?>
