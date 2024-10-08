<?php
// no direct access
defined('ABSPATH') || die();

$dummy = LSD_Options::dummy();
?>
<div class="lsd-welcome-step-content lsd-util-hide" id="step-3">
    <h2 class="text-xl mb-4"><?php esc_html_e('Dummy Data', 'listdom'); ?></h2>
    <div class="lsd-dummy-data-wrap">
        <form id="lsd_dummy_data_form">
            <p class="description"><?php esc_html_e("Dummy data are pre-made sample search modules, categories, tags, labels, locations, shortcodes, pages etc. that you'll be able to remove them at anytime. Do you want to import them?", 'listdom'); ?></p>
            <div class="lsd-dummy-settings">
                <div class="lsd-form-row">
                    <div class="lsd-col-1"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_dummy_listings',
                        'name' => 'lsd[dummy][listings]',
                        'value' => $dummy['dummy']['listings'] ?? 0,
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Listings', 'listdom'),
                        'for' => 'lsd_dummy_listings',
                    ]); ?></div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-1"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_dummy_categories',
                        'name' => 'lsd[dummy][categories]',
                        'value' => $dummy['dummy']['categories'] ?? 0,
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Categories', 'listdom'),
                        'for' => 'lsd_dummy_categories',
                    ]); ?></div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-1"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_dummy_locations',
                        'name' => 'lsd[dummy][locations]',
                        'value' => $dummy['dummy']['locations'] ?? 0,
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Locations', 'listdom'),
                        'for' => 'lsd_dummy_locations',
                    ]); ?></div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-1"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_dummy_tags',
                        'name' => 'lsd[dummy][tags]',
                        'value' => $dummy['dummy']['tags'] ?? 0,
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Tags', 'listdom'),
                        'for' => 'lsd_dummy_tags',
                    ]); ?></div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-1"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_dummy_features',
                        'name' => 'lsd[dummy][features]',
                        'value' => $dummy['dummy']['features'] ?? 0,
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Features', 'listdom'),
                        'for' => 'lsd_dummy_features',
                    ]); ?></div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-1"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_dummy_labels',
                        'name' => 'lsd[dummy][labels]',
                        'value' => $dummy['dummy']['labels'] ?? 0,
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Labels', 'listdom'),
                        'for' => 'lsd_dummy_labels',
                    ]); ?></div>
                </div>
                <?php if ($this->isPro()): ?>
                    <div class="lsd-form-row">
                        <div class="lsd-col-1"><?php echo LSD_Form::switcher([
                            'id' => 'lsd_dummy_attributes',
                            'name' => 'lsd[dummy][attributes]',
                            'value' => $dummy['dummy']['attributes'] ?? 0,
                        ]); ?></div>
                        <div class="lsd-col-6"><?php echo LSD_Form::label([
                            'title' => esc_html__('Attributes', 'listdom'),
                            'for' => 'lsd_dummy_attributes',
                        ]); ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($this->isPro()): ?>
                <div class="lsd-form-row">
                    <div class="lsd-col-1"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_dummy_frontend_dashboard',
                        'name' => 'lsd[dummy][frontend_dashboard]',
                        'value' => $dummy['dummy']['frontend_dashboard'] ?? 0,
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Frontend Dashboard', 'listdom'),
                        'for' => 'lsd_dummy_frontend_dashboard',
                    ]); ?></div>
                </div>
                <?php endif; ?>
                <div class="lsd-form-row">
                    <div class="lsd-col-1"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_dummy_shortcodes',
                        'name' => 'lsd[dummy][shortcodes]',
                        'value' => $dummy['dummy']['shortcodes'] ?? 0,
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Skin Shortcodes & Pages', 'listdom'),
                        'for' => 'lsd_dummy_shortcodes',
                    ]); ?></div>
                </div>
            </div>

            <?php LSD_Form::nonce('lsd_dummy_data_form'); ?>

            <div class="lsd-skip-wizard lsd-mt-2">
                <button class="lsd-skip-step"><?php echo esc_html__('Skip This step', 'listdom'); ?></button>
                <div class="lsd-flex lsd-gap-2">
                    <button type="button" class="lsd-prev-step-link button button-hero button-primary">
                        <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
                        <?php echo esc_html__('Prev Step', 'listdom'); ?>
                    </button>
                    <button type="submit" class="lsd-step-link button button-hero button-primary" id="lsd_dummy_data_save_button">
                        <?php echo esc_html__('Import', 'listdom'); ?>
                        <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
<script>
jQuery('#lsd_dummy_data_form').on('submit', function (event)
{
    event.preventDefault();

    jQuery(".lsd-welcome-wizard").addClass('lsd-loading');
    jQuery('head').append('<style>.lsd-loading:after { content: "\\f56e" !important; }</style>');

    const dummy = jQuery("#lsd_dummy_data_form").serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_dummy&" + dummy,
        success: function ()
        {
            jQuery(".lsd-welcome-wizard").removeClass('lsd-loading');
            handleStepNavigation(4);
        },
        error: function ()
        {
            jQuery(".lsd-welcome-wizard").removeClass('lsd-loading');
        }
    });
});
</script>