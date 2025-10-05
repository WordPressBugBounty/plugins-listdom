<?php
// no direct access
defined('ABSPATH') || die();

$dummy = LSD_Options::dummy();
$missFeatureMessages = [];
?>
<div class="lsd-dummy-data-wrap">
    <form id="lsd_dummy_data_form">
        <div class="lsd-tab-content-active lsd-tab-content">
            <h3 class="lsd-admin-title lsd-mt-0"><?php echo esc_html__('Dummy Data', 'listdom'); ?></h3>
            <div class="lsd-settings-group-wrapper">
                <div class="lsd-settings-fields-wrapper">
                    <div class="lsd-admin-section-heading">
                        <h3 class="lsd-admin-title lsd-my-0"><?php echo esc_html__('Data Types', 'listdom'); ?></h3>
                        <p class="lsd-admin-description lsd-my-0"><?php esc_html_e("Dummy data are pre-made sample listings, search forms, categories, tags, labels, locations, shortcodes, pages, etc. that help you set up the site faster. You'll be able to remove them at any time. Select the items you want to import:", 'listdom'); ?></p>
                    </div>
                    <div class="lsd-settings-form-group lsd-mt-4">
                        <div class="lsd-form-row">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                'class' => 'lsd-toggle-text',
                                'title' => esc_html__('Listings', 'listdom'),
                                'for' => 'lsd_dummy_listings',
                            ]); ?></div>
                            <div class="lsd-col-4"><?php echo LSD_Form::switcher([
                                'id' => 'lsd_dummy_listings',
                                'name' => 'lsd[dummy][listings]',
                                'value' => $dummy['dummy']['listings'] ?? 0,
                            ]); ?></div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                'class' => 'lsd-toggle-text',
                                'title' => esc_html__('Categories', 'listdom'),
                                'for' => 'lsd_dummy_categories',
                            ]); ?></div>
                            <div class="lsd-col-4"><?php echo LSD_Form::switcher([
                                'id' => 'lsd_dummy_categories',
                                'name' => 'lsd[dummy][categories]',
                                'value' => $dummy['dummy']['categories'] ?? 0,
                            ]); ?></div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                'class' => 'lsd-toggle-text',
                                'title' => esc_html__('Locations', 'listdom'),
                                'for' => 'lsd_dummy_locations',
                            ]); ?></div>
                            <div class="lsd-col-4"><?php echo LSD_Form::switcher([
                                'id' => 'lsd_dummy_locations',
                                'name' => 'lsd[dummy][locations]',
                                'value' => $dummy['dummy']['locations'] ?? 0,
                            ]); ?></div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                'class' => 'lsd-toggle-text',
                                'title' => esc_html__('Tags', 'listdom'),
                                'for' => 'lsd_dummy_tags',
                            ]); ?></div>
                            <div class="lsd-col-4"><?php echo LSD_Form::switcher([
                                'id' => 'lsd_dummy_tags',
                                'name' => 'lsd[dummy][tags]',
                                'value' => $dummy['dummy']['tags'] ?? 0,
                            ]); ?></div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                'class' => 'lsd-toggle-text',
                                'title' => esc_html__('Features', 'listdom'),
                                'for' => 'lsd_dummy_features',
                            ]); ?></div>
                            <div class="lsd-col-4"><?php echo LSD_Form::switcher([
                                'id' => 'lsd_dummy_features',
                                'name' => 'lsd[dummy][features]',
                                'value' => $dummy['dummy']['features'] ?? 0,
                            ]); ?></div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                'class' => 'lsd-toggle-text',
                                'title' => esc_html__('Labels', 'listdom'),
                                'for' => 'lsd_dummy_labels',
                            ]); ?></div>
                            <div class="lsd-col-4"><?php echo LSD_Form::switcher([
                                'id' => 'lsd_dummy_labels',
                                'name' => 'lsd[dummy][labels]',
                                'value' => $dummy['dummy']['labels'] ?? 0,
                            ]); ?></div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                'class' => 'lsd-toggle-text',
                                'title' => esc_html__('Custom Fields', 'listdom'),
                                'for' => 'lsd_dummy_attributes',
                            ]); ?></div>
                            <div class="lsd-col-4"><?php echo LSD_Form::switcher([
                                'id' => 'lsd_dummy_attributes',
                                'name' => 'lsd[dummy][attributes]',
                                'value' => $dummy['dummy']['attributes'] ?? 0,
                            ]); ?></div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                'class' => 'lsd-toggle-text',
                                'title' => esc_html__('Frontend Dashboard', 'listdom'),
                                'for' => 'lsd_dummy_frontend_dashboard',
                            ]); ?></div>
                            <div class="lsd-col-4"><?php echo LSD_Form::switcher([
                                'id' => 'lsd_dummy_frontend_dashboard',
                                'name' => 'lsd[dummy][frontend_dashboard]',
                                'value' => $dummy['dummy']['frontend_dashboard'] ?? 0,
                            ]); ?></div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                'class' => 'lsd-toggle-text',
                                'title' => esc_html__('Shortcodes & Pages', 'listdom'),
                                'for' => 'lsd_dummy_shortcodes',
                            ]); ?></div>
                            <div class="lsd-col-4"><?php echo LSD_Form::switcher([
                                'id' => 'lsd_dummy_shortcodes',
                                'name' => 'lsd[dummy][shortcodes]',
                                'value' => $dummy['dummy']['shortcodes'] ?? 0,
                            ]); ?></div>
                        </div>

                        <div class="lsd-form-row">
                            <div class="lsd-col-8"><?php echo LSD_Form::label([
                                'class' => 'lsd-toggle-text',
                                'title' => esc_html__('Profile', 'listdom'),
                                'for' => 'lsd_dummy_profile',
                            ]); ?></div>
                            <div class="lsd-col-4"><?php echo LSD_Form::switcher([
                                'id' => 'lsd_dummy_profile',
                                'name' => 'lsd[dummy][profile]',
                                'value' => $dummy['dummy']['profile'] ?? 1,
                            ]); ?></div>
                        </div>
                    </div>

                    <?php if(count($missFeatureMessages)): ?>
                        <div>
                            <?php foreach ($missFeatureMessages as $missFeatureMessage) echo LSD_Form::alert($missFeatureMessage, 'warning'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="lsd-spacer-10"></div>

        <div class="lsd-form-row lsd-settings-submit-wrapper">
            <div class="lsd-col-12 lsd-flex lsd-gap-3 lsd-flex-content-end">
                <?php LSD_Form::nonce('lsd_dummy_data_form'); ?>
                <button type="submit" id="lsd_dummy_data_save_button" class="lsd-primary-button">
                    <?php esc_html_e('Import Dummy Data', 'listdom'); ?>
                    <i class="lsdi lsdi-checkmark-circle"></i>
                </button>
            </div>
        </div>
    </form>
</div>
<script>
jQuery('#lsd_dummy_data_form').on('submit', function(event)
{
    event.preventDefault();

    const $alert = jQuery(".lsd-dummy-data-message");
    const $button = jQuery("#lsd_dummy_data_save_button");

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js(esc_html__('Importing Dummy Data', 'listdom') ); ?>");

    const dummy = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_dummy&" + dummy,
        success: function()
        {
            listdom_toastify("<?php echo esc_js(esc_html__("Dummy Data imported completely.", 'listdom')); ?>", 'lsd-success');

            // Unloading
            loading.stop();
        },
        error: function()
        {
            // Unloading
            loading.stop();
        }
    });
});
</script>
