<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$cover = $options['cover'] ?? [];
$missAddonMessages = [];
?>
<div class="lsd-form-row lsd-form-row-separator"></div>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__("Using the %s skin, you can show only 1 listing in a nice style. You can use multiple cover shortcodes in one page to show more listings.", 'listdom'), '<strong>'.esc_html__('Cover', 'listdom').'</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Style', 'listdom'),
        'for' => 'lsd_display_options_skin_cover_style',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_cover_style',
            'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle',
            'name' => 'lsd[display][cover][style]',
            'options' => LSD_Styles::cover(),
            'value' => $cover['style'] ?? 'style1',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_cover'
            ]
        ]); ?>
    </div>
</div>

<div class="lsd-form-group lsd-form-row-style-needed lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2 lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4" id="lsd_display_options_style">
    <h3 class="lsd-mb-0 lsd-mt-1"><?php echo esc_html__("Elements Display Options", 'listdom'); ?></h3>
    <p class="description lsd-mb-4"><?php echo esc_html__("You can simply change the visibility of each element that you want on listing card", 'listdom'); ?> </p>
    <div class="lsd-flex lsd-gap-2">
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style3 lsd-display-options-style-dependency-style4">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
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

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2">
            <div class="lsd-form-row">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
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

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style1 lsd-display-options-style-dependency-style2">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
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

        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style4">
            <div class="lsd-form-row lsd-display-options-builder-option">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
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

        <?php if(class_exists('LSDADDREV_Base')): ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
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
        <?php else: ?>
            <?php $missAddonMessages[] = LSD_Base::missAddonMessage('Reviews', esc_html__('Reviews Rate', 'listdom')); ?>
        <?php endif; ?>
        <div class="lsd-form-row lsd-display-options-builder-option">
            <div class="lsd-col-5"><?php echo LSD_Form::label([
                'title' => esc_html__('Title', 'listdom'),
                'for' => 'lsd_display_options_skin_cover_title',
            ]); ?></div>
            <div class="lsd-col-6">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_cover_title',
                    'name' => 'lsd[display][cover][display_title]',
                    'value' => $cover['display_title'] ?? '1',
                    'toggle' => '#lsd_display_options_skin_cover_is_claimed'
                ]); ?>
            </div>
        </div>
        <?php if(class_exists('LSDADDCLM_Base')): ?>
        <div class="lsd-display-options-style-dependency lsd-display-options-style-dependency-style4">
            <div class="lsd-form-row <?php echo (!isset($cover['display_title']) || $cover['display_title']) ? '1' : 'lsd-util-hide'; ?>" id="lsd_display_options_skin_cover_is_claimed">
                <div class="lsd-col-5"><?php echo LSD_Form::label([
                    'title' => esc_html__('Is Claimed', 'listdom'),
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
        'title' => esc_html__('Listing', 'listdom'),
        'for' => 'lsd_display_options_skin_cover_listing',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::listings([
            'id' => 'lsd_display_options_skin_cover_listing',
            'name' => 'lsd[display][cover][listing]',
            'value' => $cover['listing'] ?? null,
            'has_post_thumbnail' => true
        ]); ?>
        <p class="description"><?php echo esc_html__("You can select only the listings that have featured image.", 'listdom'); ?></p>
    </div>
</div>

<?php if($this->isPro()): ?>
<div class="lsd-form-row lsd-display-options-builder-option">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listing Link', 'listdom'),
        'for' => 'lsd_display_options_skin_cover_listing_link',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_cover_listing_link',
            'name' => 'lsd[display][cover][listing_link]',
            'value' => $cover['listing_link'] ?? 'normal',
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
