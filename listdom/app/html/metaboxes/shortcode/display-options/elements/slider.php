<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */
/** @var array $price_components */

$slider = $options['slider'] ?? [];
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
                'id' => 'lsd_display_options_skin_slider_style',
                'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle lsd-admin-input',
                'name' => 'lsd[display][slider][style]',
                'options' => LSD_Styles::slider(),
                'value' => $slider['style'] ?? 'style1',
                'attributes' => [
                    'data-parent' => '#lsd_skin_display_options_slider'
                ]
            ]); ?>
        </div>
    </div>
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-form-row-style-needed lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4 lsd-display-options-style-dependency-style5" id="lsd_display_options_style">
            <div class="lsd-admin-section-heading">
                <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Elements", 'listdom'); ?></h3>
                <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("You can easily customize the visibility of each element on the listing card.", 'listdom'); ?> </p>
            </div>
            <div class="lsd-elements-section">
                <div class="lsd-elements-subsection">
                    <h4 class="lsd-admin-subtitle lsd-my-0"><?php echo esc_html__('Other Elements', 'listdom'); ?></h4>
                    <div class="lsd-elements-fields">
                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Labels', 'listdom'),
                                        'for' => 'lsd_display_options_skin_slider_display_labels',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_slider_display_labels',
                                        'name' => 'lsd[display][slider][display_labels]',
                                        'value' => $slider['display_labels'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Location', 'listdom'),
                                        'for' => 'lsd_display_options_skin_slider_display_location',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_slider_display_location',
                                        'name' => 'lsd[display][slider][display_location]',
                                        'value' => $slider['display_location'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (LSD_Components::map()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style5">
                                <div class="lsd-form-row">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Address', 'listdom'),
                                            'for' => 'lsd_display_options_skin_slider_display_address',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_slider_display_address',
                                            'name' => 'lsd[display][slider][display_address]',
                                            'value' => $slider['display_address'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Read Button', 'listdom'),
                                        'for' => 'lsd_display_options_skin_slider_display_read_more_button',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_slider_display_read_more_button',
                                        'name' => 'lsd[display][slider][display_read_more_button]',
                                        'value' => $slider['display_read_more_button'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style5">
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Categories', 'listdom'),
                                        'for' => 'lsd_display_options_skin_slider_display_categories',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_slider_display_categories',
                                        'name' => 'lsd[display][slider][display_categories]',
                                        'value' => $slider['display_categories'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (LSD_Components::socials()): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style5">
                                <div class="lsd-form-row lsd-display-options-builder-option">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Share Icons', 'listdom'),
                                            'for' => 'lsd_display_options_skin_slider_display_share_buttons',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_slider_display_share_buttons',
                                            'name' => 'lsd[display][slider][display_share_buttons]',
                                            'value' => $slider['display_share_buttons'] ?? '1'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (class_exists(LSDADDREV::class) || class_exists(\LSDPACREV\Base::class)): ?>
                            <div class="lsd-form-row lsd-display-options-builder-option">
                                <div class="lsd-col-8"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Review Rates', 'listdom'),
                                        'for' => 'lsd_display_options_skin_slider_review_stars',
                                    ]); ?></div>
                                <div class="lsd-col-4">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_slider_review_stars',
                                        'name' => 'lsd[display][slider][display_review_stars]',
                                        'value' => $slider['display_review_stars'] ?? '1'
                                    ]); ?>
                                </div>
                            </div>
                        <?php else: $optional_addons[] = ['reviews', esc_html__('Reviews Rate', 'listdom')]; ?>
                        <?php endif; ?>

                        <div class="lsd-form-row lsd-display-options-builder-option">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Slider Arrows', 'listdom'),
                                    'for' => 'lsd_display_options_skin_slider_display_slider_arrows',
                                ]); ?></div>
                            <div class="lsd-col-4">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_display_options_skin_slider_display_slider_arrows',
                                    'name' => 'lsd[display][slider][display_slider_arrows]',
                                    'value' => $slider['display_slider_arrows'] ?? '1'
                                ]); ?>
                            </div>
                        </div>
                        <div class="lsd-form-row lsd-display-options-builder-option">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Title', 'listdom'),
                                    'for' => 'lsd_display_options_skin_slider_title',
                                ]); ?></div>
                            <div class="lsd-col-4">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_display_options_skin_slider_title',
                                    'name' => 'lsd[display][slider][display_title]',
                                    'value' => $slider['display_title'] ?? '1',
                                    'toggle' => '#lsd_display_options_skin_slider_is_claimed_wrapper'
                                ]); ?>
                            </div>
                        </div>
                        <?php if (class_exists(LSDADDCLM::class) || class_exists(\LSDPACCLM\Base::class)): ?>
                            <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style5 <?php echo !isset($slider['display_title']) || $slider['display_title'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_slider_is_claimed_wrapper">
                                <div class="lsd-form-row">
                                    <div class="lsd-col-8"><?php echo LSD_Form::label([
                                            'class' => 'lsd-fields-label',
                                            'title' => esc_html__('Claim Status', 'listdom'),
                                            'for' => 'lsd_display_options_skin_slider_is_claimed',
                                        ]); ?></div>
                                    <div class="lsd-col-4">
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_display_options_skin_slider_is_claimed',
                                            'name' => 'lsd[display][slider][display_is_claimed]',
                                            'value' => $slider['display_is_claimed'] ?? '1',
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
