<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

// Pro version
$is_pro = LSD_Base::isPro();

// General Settings
$settings = LSD_Options::settings();

// Advanced Slug
$advanced_slug_status = $is_pro && isset($settings['advanced_slug_status']) && $settings['advanced_slug_status'] ? 1 : 0;
?>
<div class="lsd-settings-wrap">
    <form id="lsd_settings_form">
        <div class="lsd-settings-form-group lsd-box-white lsd-rounded lsd-mt-4 lsd-p-5">
            <h3 class="lsd-mt-0 lsd-mb-5"><?php esc_html_e('Archive Pages', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Location', 'listdom'),
                    'for' => 'lsd_settings_location_archive',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_settings_location_archive',
                        'name' => 'lsd[location_archive]',
                        'only_archive_skins' => '1',
                        'show_empty' => '1',
                        'empty_label' => esc_html__('Current Theme Style', 'listdom'),
                        'value' => $settings['location_archive'] ?? ''
                    ]); ?>
                    <p class="description"><?php esc_html_e("If your site theme doesn't support the Listdom location template, then Listdom uses its own template file which might not be 100% compatible with your theme.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Category', 'listdom'),
                    'for' => 'lsd_settings_category_archive',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_settings_category_archive',
                        'name' => 'lsd[category_archive]',
                        'only_archive_skins' => '1',
                        'show_empty' => '1',
                        'empty_label' => esc_html__('Current Theme Style', 'listdom'),
                        'value' => $settings['category_archive'] ?? ''
                    ]); ?>
                    <p class="description"><?php esc_html_e("If your site theme doesn't support the Listdom category template, then Listdom uses its own template file which might not be 100% compatible with your theme.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Tag', 'listdom'),
                    'for' => 'lsd_settings_tag_archive',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_settings_tag_archive',
                        'name' => 'lsd[tag_archive]',
                        'only_archive_skins' => '1',
                        'show_empty' => '1',
                        'empty_label' => esc_html__('Current Theme Style', 'listdom'),
                        'value' => $settings['tag_archive'] ?? ''
                    ]); ?>
                    <p class="description"><?php esc_html_e("If your site theme doesn't support the Listdom tag template, then Listdom uses its own template file which might not be 100% compatible with your theme.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Feature', 'listdom'),
                    'for' => 'lsd_settings_feature_archive',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_settings_feature_archive',
                        'name' => 'lsd[feature_archive]',
                        'only_archive_skins' => '1',
                        'show_empty' => '1',
                        'empty_label' => esc_html__('Current Theme Style', 'listdom'),
                        'value' => $settings['feature_archive'] ?? ''
                    ]); ?>
                    <p class="description"><?php esc_html_e("If your site theme doesn't support the Listdom feature template, then Listdom uses its own template file which might not be 100% compatible with your theme.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Label', 'listdom'),
                    'for' => 'lsd_settings_label_archive',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_settings_label_archive',
                        'name' => 'lsd[label_archive]',
                        'only_archive_skins' => '1',
                        'show_empty' => '1',
                        'empty_label' => esc_html__('Current Theme Style', 'listdom'),
                        'value' => $settings['label_archive'] ?? ''
                    ]); ?>
                    <p class="description"><?php esc_html_e("If your site theme doesn't support the Listdom label template, then Listdom uses its own template file which might not be 100% compatible with your theme.", 'listdom'); ?></p>
                </div>
            </div>
            <h3 class="lsd-mb-5"><?php esc_html_e('Slugs', 'listdom'); ?></h3>
            <div class="lsd-border lsd-p-4 lsd-rounded lsd-mb-5">
                <h4 class="lsd-mt-0 lsd-mb-4"><?php esc_html_e('Listings', 'listdom'); ?></h4>
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Prefix', 'listdom'),
                        'for' => 'lsd_settings_listings_slug',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_settings_listings_slug',
                            'name' => 'lsd[listings_slug]',
                            'value' => $settings['listings_slug'] ?? ''
                        ]); ?>
                        <p class="description"><?php echo sprintf(esc_html__("This option modifies the listing page URL. For example, if you set it to markers, the listings' addresses will be %s", 'listdom'), sprintf('https://yourwebsite.com/%s/listing-name/', '<strong>markers</strong>')); ?></p>
                    </div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Advanced Slug', 'listdom'),
                        'for' => 'lsd_settings_advanced_slug',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_settings_advanced_slug',
                            'name' => 'lsd[advanced_slug_status]',
                            'toggle' => '.lsd-advanced-slug',
                            'value' => $advanced_slug_status
                        ]); ?>
                    </div>
                </div>
                <?php if (!$is_pro): ?>
                <div class="lsd-form-row lsd-advanced-slug <?php echo $advanced_slug_status ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-col-12">
                        <?php echo LSD_Base::alert(LSD_Main::missFeatureMessage(esc_html__('Advanced Slug', 'listdom')), 'warning'); ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="lsd-form-row lsd-mt-4 lsd-mb-0 lsd-advanced-slug <?php echo $advanced_slug_status ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Pattern', 'listdom'),
                        'for' => 'lsd_settings_advanced_slug',
                    ]); ?></div>
                    <div class="lsd-col-6">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_settings_advanced_slug',
                            'name' => 'lsd[advanced_slug]',
                            'value' => isset($settings['advanced_slug']) && trim($settings['advanced_slug']) ? preg_replace('/\s+/', '', strtolower($settings['advanced_slug'])) : ''
                        ]); ?>
                        <div class="lsd-advanced-slug-help">
                            <p><?php esc_html_e("You can use the following placeholders:", 'listdom'); ?></p>
                            <ul class="lsd-mb-0">
                                <li><code>%category%</code>: <?php esc_html_e('Includes the slug of the primary category in the URL, such as restaurant or cafe.', 'listdom'); ?></li>
                                <li><code>%categories%</code>: <?php esc_html_e('Includes the slug of the primary category and its parent categories in the URL, such as cars/suv or restaurants/persian.', 'listdom'); ?></li>
                                <li><code>%location%</code>: <?php esc_html_e('Includes the slug of the first location in the URL.', 'listdom'); ?></li>
                                <li><code>%locations%</code>: <?php esc_html_e('Includes the slug of the first location and its parent locations in the URL, such as united-states/california or canada/bc.', 'listdom'); ?></li>
                                <li><?php echo sprintf(esc_html__('Make sure to use %s or %s to separate parts of the slug.', 'listdom'), '<code>/</code>', '<code>-</code>'); ?></li>
                                <li><?php esc_html_e('Please use lowercase characters and avoid spaces or tab characters in the URL.', 'listdom'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Location', 'listdom'),
                    'for' => 'lsd_settings_location_slug',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_settings_location_slug',
                        'name' => 'lsd[location_slug]',
                        'value' => $settings['location_slug'] ?? ''
                    ]); ?>
                    <p class="description"><?php echo esc_html__("It's for changing the location archive prefix.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Category', 'listdom'),
                    'for' => 'lsd_settings_category_slug',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_settings_category_slug',
                        'name' => 'lsd[category_slug]',
                        'value' => $settings['category_slug'] ?? ''
                    ]); ?>
                    <p class="description"><?php echo esc_html__("It's for changing the category archive prefix.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Tag', 'listdom'),
                    'for' => 'lsd_settings_tag_slug',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_settings_tag_slug',
                        'name' => 'lsd[tag_slug]',
                        'value' => $settings['tag_slug'] ?? ''
                    ]); ?>
                    <p class="description"><?php echo esc_html__("It's for changing the tag archive prefix.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Feature', 'listdom'),
                    'for' => 'lsd_settings_feature_slug',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_settings_feature_slug',
                        'name' => 'lsd[feature_slug]',
                        'value' => $settings['feature_slug'] ?? ''
                    ]); ?>
                    <p class="description"><?php echo esc_html__("It's for changing the feature archive prefix.", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Label', 'listdom'),
                    'for' => 'lsd_settings_label_slug',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_settings_label_slug',
                        'name' => 'lsd[label_slug]',
                        'value' => $settings['label_slug'] ?? ''
                    ]); ?>
                    <p class="description lsd-mb-0"><?php echo esc_html__("It's for changing the label archive prefix.", 'listdom'); ?></p>
                </div>
            </div>
        </div>
        <div class="lsd-spacer-10"></div>
        <div class="lsd-form-row">
			<div class="lsd-col-12 lsd-flex lsd-gap-3">
				<?php LSD_Form::nonce('lsd_settings_form'); ?>
				<?php echo LSD_Form::submit([
					'label' => esc_html__('Save', 'listdom'),
					'id' => 'lsd_settings_save_button',
                    'class' => 'button button-hero button-primary',
				]); ?>
                <div>
                    <p class="lsd-util-hide lsd-settings-success-message lsd-alert lsd-success lsd-m-0"><?php esc_html_e('Options saved successfully.', 'listdom'); ?></p>
                    <p class="lsd-util-hide lsd-settings-error-message lsd-alert lsd-error lsd-m-0"><?php esc_html_e('Error: Unable to save options.', 'listdom'); ?></p>
                </div>
			</div>
        </div>
    </form>
</div>
<script>
jQuery('#lsd_settings_form').on('submit', function(e)
{
    e.preventDefault();

    // Elements
    const $button = jQuery("#lsd_settings_save_button");
    const $success = jQuery(".lsd-settings-success-message");
    const $error = jQuery(".lsd-settings-error-message");

    // Loading Styles
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    const settings = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_settings&" + settings,
        success: function()
        {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($success, 2000);
        },
        error: function()
        {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($error, 2000);
        }
    });
});
</script>
