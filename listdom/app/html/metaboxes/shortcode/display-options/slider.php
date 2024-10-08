<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$slider = $options['slider'] ?? [];
$missAddonMessages = [];
?>
<div class="lsd-form-row lsd-form-row-separator"></div>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__('Using the %s skin, you can show a slider of the directories and istings in different styles. Only listings with featured images are displayed.', 'listdom'), '<strong>'.esc_html__('Slider', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Style', 'listdom'),
        'for' => 'lsd_display_options_skin_slider_style',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_slider_style',
            'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle',
            'name' => 'lsd[display][slider][style]',
            'options' => LSD_Styles::slider(),
            'value' => $slider['style'] ?? 'style1',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_slider'
            ]
        ]); ?>
    </div>
</div>

<div class="lsd-form-group lsd-form-row-style-needed lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4 lsd-display-options-style-dependency-style5" id="lsd_display_options_style">
    <h3 class="lsd-mb-0 lsd-mt-1"><?php echo esc_html__("Elements Display Options", 'listdom'); ?></h3>
    <p class="description lsd-mb-4"><?php echo esc_html__("You can simply change the visibility of each element that you want on listing card", 'listdom'); ?> </p>
    <div class="lsd-flex lsd-gap-4">
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Labels', 'listdom'),
                    'for' => 'lsd_display_options_skin_slider_display_labels',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_slider_display_labels',
                        'name' => 'lsd[display][slider][display_labels]',
                        'value' => $slider['display_labels'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4 lsd-display-options-style-dependency-style5">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Location', 'listdom'),
                    'for' => 'lsd_display_options_skin_slider_display_location',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_slider_display_location',
                        'name' => 'lsd[display][slider][display_location]',
                        'value' => $slider['display_location'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2">
            <div class="lsd-form-row">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Address', 'listdom'),
                    'for' => 'lsd_display_options_skin_slider_display_address',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_slider_display_address',
                        'name' => 'lsd[display][slider][display_address]',
                        'value' => $slider['display_address'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2">
            <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Read More Button', 'listdom'),
                'for' => 'lsd_display_options_skin_slider_display_read_more_button',
            ]); ?></div>
            <div class="lsd-col-6">
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
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Categories', 'listdom'),
                    'for' => 'lsd_display_options_skin_slider_display_categories',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_slider_display_categories',
                        'name' => 'lsd[display][slider][display_categories]',
                        'value' => $slider['display_categories'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style5">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Share Icons', 'listdom'),
                    'for' => 'lsd_display_options_skin_slider_display_share_buttons',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_slider_display_share_buttons',
                        'name' => 'lsd[display][slider][display_share_buttons]',
                        'value' => $slider['display_share_buttons'] ?? '1'
                    ]); ?>
                </div>
            </div>
        </div>

        <?php if(class_exists('LSDADDREV_Base')): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Review Rates', 'listdom'),
                'for' => 'lsd_display_options_skin_slider_review_stars',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_slider_review_stars',
                    'name' => 'lsd[display][slider][display_review_stars]',
                    'value' => $slider['display_review_stars'] ?? '1'
                ]); ?>
            </div>
        </div>
        <?php else: ?>
            <?php $missAddonMessages[] = LSD_Base::missAddonMessage('Reviews', esc_html__('Reviews Rate', 'listdom')); ?>
        <?php endif; ?>

        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Slider Arrows', 'listdom'),
                'for' => 'lsd_display_options_skin_slider_display_slider_arrows',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_slider_display_slider_arrows',
                    'name' => 'lsd[display][slider][display_slider_arrows]',
                    'value' => $slider['display_slider_arrows'] ?? '1'
                ]); ?>
            </div>
        </div>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Title', 'listdom'),
                'for' => 'lsd_display_options_skin_slider_title',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_slider_title',
                    'name' => 'lsd[display][slider][display_title]',
                    'value' => $slider['display_title'] ?? '1',
                    'toggle' => '#lsd_display_options_skin_slider_is_claimed'
                ]); ?>
            </div>
        </div>
        <?php if(class_exists('LSDADDCLM_Base')): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style5">
            <div class="lsd-form-row <?php echo (!isset($slider['display_title']) || $slider['display_title']) ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_slider_is_claimed">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Is Claimed', 'listdom'),
                    'for' => 'lsd_display_options_skin_slider_is_claimed',
                ]); ?></div>
                <div class="lsd-col-6">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_slider_is_claimed',
                        'name' => 'lsd[display][slider][display_is_claimed]',
                        'value' => $slider['display_is_claimed'] ?? '1',
                    ]); ?>
                </div>
            </div>
        </div>
        <?php else: ?>
            <?php $missAddonMessages[] = LSD_Base::missAddonMessage('Claim', esc_html__('Is claimed', 'listdom')); ?>
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
        'title' => esc_html__('Limit', 'listdom'),
        'for' => 'lsd_display_options_skin_slider_limit',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'id' => 'lsd_display_options_skin_slider_limit',
            'name' => 'lsd[display][slider][limit]',
            'value' => $slider['limit'] ?? '6'
        ]); ?>
    </div>
</div>

<?php if($this->isPro()): ?>
<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listing Link', 'listdom'),
        'for' => 'lsd_display_options_skin_slider_listing_link',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_slider_listing_link',
            'name' => 'lsd[display][slider][listing_link]',
            'value' => $slider['listing_link'] ?? 'normal',
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
