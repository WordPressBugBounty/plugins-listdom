<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */
/** @var array $price_components */

$counts = (array) wp_count_posts(LSD_Base::PTYPE_LISTING);

unset($counts['auto-draft']);
$number_of_listings = array_sum($counts);

$cover = $options['cover'] ?? [];

$listing = isset($cover['listing']) && is_array($cover['listing'])
    ? $cover['listing'][0]
    : ($cover['listing'] ?? 0);

$optional_addons = [];
?>
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Select the Style", 'listdom'); ?></h3>
            <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("Pick a style variation of the selected skin or adjust listing elements (if not using Elementor/Divi).", 'listdom'); ?> </p>
        </div>
         <div class="lsd-col-12">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin_cover_style',
                'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle lsd-admin-input',
                'name' => 'lsd[display][cover][style]',
                'options' => LSD_Styles::cover(),
                'value' => $cover['style'] ?? 'style1',
                'attributes' => [
                    'data-parent' => '#lsd_skin_display_options_cover'
                ]
            ]); ?>
        </div>
    </div>

    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-form-row-style-needed lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4" id="lsd_display_options_style">
            <div class="lsd-admin-section-heading">
                <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Elements", 'listdom'); ?></h3>
                <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("You can easily customize the visibility of each element on the listing card.", 'listdom'); ?> </p>
            </div>

            <div class="lsd-elements-section">
                <div class="lsd-elements-subsection">
                    <h4 class="lsd-admin-subtitle lsd-m-0"><?php esc_html_e('Other Elements', 'listdom'); ?></h4>
                    <div class="lsd-elements-fields">
                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-5"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Location', 'listdom'),
                                        'for' => 'lsd_display_options_skin_cover_display_location',
                                    ]); ?></div>
                                <div class="lsd-col-6">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_cover_display_location',
                                        'name' => 'lsd[display][cover][display_location]',
                                        'value' => $cover['display_location'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (LSD_Components::map()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style4">
                                <div class="lsd-form-row">
                                    <div class="lsd-col-5"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Address', 'listdom'),
                                            'for' => 'lsd_display_options_skin_cover_display_address',
                                        ]); ?></div>
                                    <div class="lsd-col-6">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_cover_display_address',
                                            'name' => 'lsd[display][cover][display_address]',
                                            'value' => $cover['display_address'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-5"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Read More Button', 'listdom'),
                                        'for' => 'lsd_display_options_skin_cover_read_more_button',
                                    ]); ?></div>
                                <div class="lsd-col-6">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_cover_read_more_button',
                                        'name' => 'lsd[display][cover][display_read_more_button]',
                                        'value' => $cover['display_read_more_button'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-5"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Labels', 'listdom'),
                                        'for' => 'lsd_display_options_skin_cover_display_labels',
                                    ]); ?></div>
                                <div class="lsd-col-6">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_cover_display_labels',
                                        'name' => 'lsd[display][cover][display_labels]',
                                        'value' => $cover['display_labels'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style4">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-5"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Categories', 'listdom'),
                                        'for' => 'lsd_display_options_skin_cover_display_categories',
                                    ]); ?></div>
                                <div class="lsd-col-6">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_cover_display_categories',
                                        'name' => 'lsd[display][cover][display_categories]',
                                        'value' => $cover['display_categories'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (LSD_Components::socials()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style4">
                                <div class="lsd-form-row lsd-display-options-builder-option">
                                    <div class="lsd-col-5"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Share Icons', 'listdom'),
                                            'for' => 'lsd_display_options_skin_cover_display_share_buttons',
                                        ]); ?></div>
                                    <div class="lsd-col-6">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_cover_display_share_buttons',
                                            'name' => 'lsd[display][cover][display_share_buttons]',
                                            'value' => $cover['display_share_buttons'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(class_exists(LSDADDREV::class) || class_exists(\LSDPACREV\Base::class)): ?>
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-5"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Review Rates', 'listdom'),
                                        'for' => 'lsd_display_options_skin_cover_review_stars',
                                    ]); ?></div>
                                <div class="lsd-col-6">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_cover_review_stars',
                                        'name' => 'lsd[display][cover][display_review_stars]',
                                        'value' => $cover['display_review_stars'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        <?php else: $optional_addons[] = ['reviews', esc_html__('Reviews Rate', 'listdom')]; ?>
                        <?php endif; ?>
                        <div class="lsd-form-row lsd-display-options-builder-option">
                            <div class="lsd-col-5"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Title', 'listdom'),
                                    'for' => 'lsd_display_options_skin_cover_title',
                                ]); ?></div>
                            <div class="lsd-col-6">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_display_options_skin_cover_title',
                                    'name' => 'lsd[display][cover][display_title]',
                                    'value' => $cover['display_title'] ?? '1',
                                    'toggle' => '#lsd_display_options_skin_cover_is_claimed_wrapper'
                                ]); ?>
                            </div>
                        </div>
                        <?php if(class_exists(LSDADDCLM::class) || class_exists(\LSDPACCLM\Base::class)): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style4 <?php echo !isset($cover['display_title']) || $cover['display_title'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_cover_is_claimed_wrapper">
                                <div class="lsd-form-row">
                                    <div class="lsd-col-5"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Claim Status', 'listdom'),
                                            'for' => 'lsd_display_options_skin_cover_is_claimed',
                                        ]); ?></div>
                                    <div class="lsd-col-6">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_cover_is_claimed',
                                            'name' => 'lsd[display][cover][display_is_claimed]',
                                            'value' => $cover['display_is_claimed'] ?? '1',
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: $optional_addons[] = ['claim', esc_html__('Claim Status', 'listdom')]; ?>
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

