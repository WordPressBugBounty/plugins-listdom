<?php
// no direct access
defined('ABSPATH') || die();

$dummy = LSD_Options::dummy();
?>
<div class="lsd-welcome-step-content lsd-util-hide" id="step-3">
    <div class="lsd-welcome-content-header">
        <div class="lsd-admin-section-heading">
            <div class="lsd-admin-title-icon">
                <i class="listdom-icon lsdi-database"></i>
                <h2 class="lsd-admin-title lsd-m-0"><?php echo esc_html__('Import Dummy Data', 'listdom'); ?></h2>
            </div>
            <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Boost your directory building by importing pre-made listings and start modifying them.' , 'listdom'); ?></p>
        </div>
    </div>

    <div class="lsd-welcome-content-wrapper">
        <div class="lsd-dummy-data-content">
            <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/wizard-dummy.png')); ?>" alt="">
            <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('The dummy data includes several listings, categories, locations, labels, etc.', 'listdom'); ?></p>
            <form id="lsd_dummy_data_form" class="lsd-util-hide">
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
                    <div class="lsd-form-row">
                        <div class="lsd-col-1"><?php echo LSD_Form::switcher([
                            'id' => 'lsd_dummy_attributes',
                            'name' => 'lsd[dummy][attributes]',
                            'value' => $dummy['dummy']['attributes'] ?? 0,
                        ]); ?></div>
                        <div class="lsd-col-6"><?php echo LSD_Form::label([
                            'title' => esc_html__('Custom Fields', 'listdom'),
                            'for' => 'lsd_dummy_attributes',
                        ]); ?></div>
                    </div>
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
                    <div class="lsd-form-row">
                        <div class="lsd-col-1"><?php echo LSD_Form::switcher([
                            'id' => 'lsd_dummy_profile',
                            'name' => 'lsd[dummy][profile]',
                            'value' => $dummy['dummy']['profile'] ?? 0,
                        ]); ?></div>
                        <div class="lsd-col-6"><?php echo LSD_Form::label([
                            'title' => esc_html__('Profile', 'listdom'),
                            'for' => 'lsd_dummy_profile',
                        ]); ?></div>
                    </div>
                </div>

                <?php LSD_Form::nonce('lsd_dummy_data_form'); ?>
            </form>
        </div>
    </div>
    <div class="lsd-welcome-button-wrapper">
        <button class="lsd-skip-step lsd-text-button"><?php echo esc_html__('Skip', 'listdom'); ?></button>
        <button type="submit" class="lsd-step-link lsd-primary-button" id="lsd_dummy_data_save_button">
            <?php echo esc_html__('Import', 'listdom'); ?>
            <i class="listdom-icon lsdi-right-arrow"></i>
        </button>
    </div>

</div>
<script>
jQuery('#lsd_dummy_data_save_button').on('click', function (event)
{
    event.preventDefault();

    const $button = jQuery(this);

    // Loading Wrapper
    const loading = (new ListdomButtonLoader($button));
    loading.start("<?php echo esc_js( __('Importing', 'listdom') ); ?>");

    const dummy = jQuery('#lsd_dummy_data_form').serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_dummy&" + dummy,
        success: function ()
        {
            // Loading Styles
            loading.stop();

            setTimeout(function()
            {
                handleStepNavigation(4);
            }, 1000);

        },
        error: function ()
        {
            loading.stop();
        }
    });
});
</script>
