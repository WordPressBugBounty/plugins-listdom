<?php
// no direct access
defined('ABSPATH') || die();

$dummy = LSD_Options::dummy();
$missFeatureMessages = [];
?>
<div class="lsd-dummy-data-wrap">
    <h2 class="lsd-mt-5"><?php esc_html_e('Dummy Data', 'listdom'); ?></h2>
    <form id="lsd_dummy_data_form">
        <p class="description"><?php esc_html_e("Dummy data are pre-made sample search modules, categories, tags, labels, locations, shortcodes, pages etc. that you'll be able to remove them at anytime. Do you want to import them?", 'listdom'); ?></p>

        <div class="lsd-form-group">
            <div class="lsd-form-row">
                <div class="lsd-col-6"><?php echo LSD_Form::label([
                    'title' => esc_html__('Listings', 'listdom'),
                    'for' => 'lsd_dummy_listings',
                ]); ?></div>
                <div class="lsd-col-6"><?php echo LSD_Form::switcher([
                    'id' => 'lsd_dummy_listings',
                    'name' => 'lsd[dummy][listings]',
                    'value' => $dummy['dummy']['listings'] ?? 0,
                ]); ?></div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-6"><?php echo LSD_Form::label([
                    'title' => esc_html__('Categories', 'listdom'),
                    'for' => 'lsd_dummy_categories',
                ]); ?></div>
                <div class="lsd-col-6"><?php echo LSD_Form::switcher([
                    'id' => 'lsd_dummy_categories',
                    'name' => 'lsd[dummy][categories]',
                    'value' => $dummy['dummy']['categories'] ?? 0,
                ]); ?></div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-6"><?php echo LSD_Form::label([
                    'title' => esc_html__('Locations', 'listdom'),
                    'for' => 'lsd_dummy_locations',
                ]); ?></div>
                <div class="lsd-col-6"><?php echo LSD_Form::switcher([
                    'id' => 'lsd_dummy_locations',
                    'name' => 'lsd[dummy][locations]',
                    'value' => $dummy['dummy']['locations'] ?? 0,
                ]); ?></div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-6"><?php echo LSD_Form::label([
                    'title' => esc_html__('Tags', 'listdom'),
                    'for' => 'lsd_dummy_tags',
                ]); ?></div>
                <div class="lsd-col-6"><?php echo LSD_Form::switcher([
                    'id' => 'lsd_dummy_tags',
                    'name' => 'lsd[dummy][tags]',
                    'value' => $dummy['dummy']['tags'] ?? 0,
                ]); ?></div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-6"><?php echo LSD_Form::label([
                    'title' => esc_html__('Features', 'listdom'),
                    'for' => 'lsd_dummy_features',
                ]); ?></div>
                <div class="lsd-col-6"><?php echo LSD_Form::switcher([
                    'id' => 'lsd_dummy_features',
                    'name' => 'lsd[dummy][features]',
                    'value' => $dummy['dummy']['features'] ?? 0,
                ]); ?></div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-6"><?php echo LSD_Form::label([
                    'title' => esc_html__('Labels', 'listdom'),
                    'for' => 'lsd_dummy_labels',
                ]); ?></div>
                <div class="lsd-col-6"><?php echo LSD_Form::switcher([
                    'id' => 'lsd_dummy_labels',
                    'name' => 'lsd[dummy][labels]',
                    'value' => $dummy['dummy']['labels'] ?? 0,
                ]); ?></div>
            </div>
            <?php if($this->isPro()): ?>
                <div class="lsd-form-row">
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Attributes', 'listdom'),
                        'for' => 'lsd_dummy_attributes',
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_dummy_attributes',
                        'name' => 'lsd[dummy][attributes]',
                        'value' => $dummy['dummy']['attributes'] ?? 0,
                    ]); ?></div>
                </div>
            <?php else: ?>
                <?php $missFeatureMessages[] = $this->missFeatureMessage(esc_html__('Attributes', 'listdom')); ?>
            <?php endif; ?>
            <?php if($this->isPro()): ?>
                <div class="lsd-form-row">
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Frontend Dashboard', 'listdom'),
                        'for' => 'lsd_dummy_frontend_dashboard',
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_dummy_frontend_dashboard',
                        'name' => 'lsd[dummy][frontend_dashboard]',
                        'value' => $dummy['dummy']['frontend_dashboard'] ?? 0,
                    ]); ?></div>
                </div>
            <?php else: ?>
                <?php $missFeatureMessages[] = $this->missFeatureMessage(esc_html__('Frontend Dashboard', 'listdom')); ?>
            <?php endif; ?>
            <div class="lsd-form-row">
                <div class="lsd-col-6"><?php echo LSD_Form::label([
                    'title' => esc_html__('Shortcodes & Pages', 'listdom'),
                    'for' => 'lsd_dummy_shortcodes',
                ]); ?></div>
                <div class="lsd-col-6"><?php echo LSD_Form::switcher([
                    'id' => 'lsd_dummy_shortcodes',
                    'name' => 'lsd[dummy][shortcodes]',
                    'value' => $dummy['dummy']['shortcodes'] ?? 0,
                ]); ?></div>
            </div>
        </div>

        <?php if(count($missFeatureMessages)): ?>
            <div>
                <?php foreach ($missFeatureMessages as $missFeatureMessage) echo LSD_Form::alert($missFeatureMessage, 'warning'); ?>
            </div>
        <?php endif; ?>

        <div class="lsd-form-row lsd-mt-4">
            <div class="lsd-col-12">
                <?php LSD_Form::nonce('lsd_dummy_data_form'); ?>
                <?php echo LSD_Form::submit([
                    'label' => esc_html__('Import Dummy Data', 'listdom'),
                    'id' => 'lsd_dummy_data_save_button',
                    'class' => 'button button-primary button-hero'
                ]); ?>
            </div>
        </div>
        <div class="lsd-util-hide" id="lsd_success_message">
            <div class="lsd-form-row lsd-mt-3">
                <div class="lsd-col-12 ">
                    <p class="lsd-util-hide lsd-dummy-data-message"><?php esc_html_e("Dummy Data imported completely.", 'listdom'); ?></p>
                </div>
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

    // Add loading Class to the button
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    const dummy = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_dummy&" + dummy,
        success: function()
        {
            // hide Message
            $alert.removeClass('lsd-util-hide');

            // Unloading
            loading.stop($alert, 2000);

            // Remove loading Class from the button
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Import Dummy Data', 'listdom')); ?>");
        },
        error: function()
        {
            // Unloading
            loading.stop();

            // Remove loading Class from the button
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Import Dummy Data', 'listdom')); ?>");
        }
    });
});
</script>
