<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

// Dashboard
$dashboard = new LSD_Dashboard();

// Settings
$settings = LSD_Options::settings();
$styles = LSD_Options::styles();

// Post Types & Their Taxonomies
$post_types = LSD_Base::get_ptypes_and_tax(true);

// Ensure Taxonomies Generate Once
$did_taxonomies = [];
?>
<div class="lsd-settings-wrap" id="lsd_settings_advanced_wrapper">
    <div class="lsd-form-row lsd-my-0">
        <div class="lsd-col-12 lsd-flex-o-2">
            <form id="lsd_settings_form">
                <div id="lsd_panel_advanced_assets-loading" class="lsd-settings-form-group lsd-tab-content-active lsd-tab-content">
                    <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Optimize Assets Loading', 'listdom'); ?></h3>
                    <div class="lsd-settings-group-wrapper">
                        <div class="lsd-settings-fields-wrapper">
                            <p class="description"><?php esc_html_e("This feature allows you to optimize your website's performance by controlling where Listdom’s JavaScript and CSS files are loaded. You can choose to load these assets globally or limit them to specific pages where they are required, such as pages that utilize Listdom's features.", 'listdom'); ?></p>
                            <p class="description"><?php esc_html_e("By reducing asset loading to only the necessary pages, this feature minimizes resource usage and enhances your website's speed, delivering a better experience for your visitors.", 'listdom'); ?></p>

                            <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4 lsd-mt-2">
                                <div>
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_load_assets_globally',
                                        'name' => 'lsd[assets][load]',
                                        'value' => $settings['assets']['load'] ?? '1',
                                        'toggle' => '#lsd_load_assets_per_post_type',
                                    ]); ?>
                                </div>
                                <div>
                                    <?php echo LSD_Form::label([
                                        'title' => esc_html__('Load Assets Everywhere', 'listdom'),
                                        'for' => 'lsd_load_assets_globally',
                                    ]); ?>
                                </div>
                            </div>

                            <div id="lsd_load_assets_per_post_type" class="lsd-mt-2 lsd-row <?php echo $settings['assets']['load'] ? 'lsd-util-hide' : ''; ?>">
                                <div class="lsd-col-12">
                                    <p class="description lsd-mt-4 lsd-mb-3"><?php esc_html_e("Please configure each post type. By default, all post types are set to load Listdom's CSS and JS files.", 'listdom'); ?></p>
                                    <ul class="lsd-tab-switcher lsd-level-3-menu lsd-sub-tabs lsd-flex lsd-mb-4 lsd-flex-wrap" data-for=".lsd-post-type-tab-switcher-content">
                                        <?php $p = 0; foreach ($post_types as $post_type_key => $post_type_data): $p++; $post_type = $post_type_data['post_type']; $taxonomies = $post_type_data['taxonomies']; ?>
                                            <li data-tab="post-type-<?php echo esc_attr($post_type_key); ?>" class="<?php echo $p === 1 ? 'lsd-sub-tabs-active' : ''; ?>"><a href="#"><?php echo esc_html($post_type->label ?? ''); ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php $p = 0; foreach ($post_types as $post_type_key => $post_type_data): $p++; $post_type = $post_type_data['post_type']; $taxonomies = $post_type_data['taxonomies']; ?>
                                        <div class="lsd-tab-switcher-content lsd-post-type-tab-switcher-content <?php echo ($post_type->name === 'post') ? 'lsd-tab-switcher-content-active' : ''; ?>" id="lsd-tab-switcher-post-type-<?php echo esc_attr($post_type_key); ?>-content">
                                            <ul class="lsd-tab-switcher lsd-level-5-menu lsd-sub-tabs lsd-flex lsd-flex-wrap" data-for=".lsd-tab-switcher-content-details-<?php echo $post_type->name; ?>">
                                                <li data-tab="<?php echo esc_attr($post_type->name); ?>" class="lsd-sub-tabs-active">
                                                    <a href="#"><?php echo esc_html($post_type->label); ?></a>
                                                </li>
                                                <?php foreach ($taxonomies as $taxonomy_key => $taxonomy): if (isset($did_taxonomies[$taxonomy_key])) continue; ?>
                                                    <li data-tab="<?php echo esc_attr($taxonomy->name); ?>">
                                                        <a href="#"><?php echo esc_html($taxonomy->label); ?></a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>

                                            <div class="lsd-tab-switcher-content-wrapper lsd-mt-4">
                                                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-details-<?php echo $post_type->name; ?> lsd-tab-switcher-content-active" id="lsd-tab-switcher-<?php echo esc_attr($post_type->name); ?>-content">
                                                    <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4 lsd-mt-3">
                                                        <div>
                                                            <?php echo LSD_Form::switcher([
                                                                'id' => 'lsd_load_assets_globally_' . $post_type->name,
                                                                'name' => 'lsd[assets][' . $post_type->name . '][load]',
                                                                'value' => $settings['assets'][$post_type->name]['load'] ?? '1',
                                                                'toggle' => '#lsd-' . esc_attr($post_type->name) . '-content',
                                                            ]); ?>
                                                        </div>
                                                        <div>
                                                            <?php echo LSD_Form::label([
                                                                'title' => sprintf(esc_html__('Load for all %s', 'listdom'), strtolower($post_type->label)),
                                                                'for' => 'lsd_load_assets_globally_' . $post_type->name,
                                                            ]); ?>
                                                        </div>
                                                    </div>
                                                    <div
                                                        id="<?php echo esc_attr('lsd-' . esc_attr($post_type->name) . '-content'); ?>"
                                                        class="lsd-mt-4 <?php echo !isset($settings['assets'][$post_type->name]['load']) || $settings['assets'][$post_type->name]['load'] ? 'lsd-util-hide' : ''; ?>"
                                                    >
                                                        <?php echo LSD_Form::autosuggest([
                                                            'source' => $post_type->name,
                                                            'id' => 'lsd_' . esc_attr($post_type->name) . '_input',
                                                            'values' => $settings['assets'][esc_attr($post_type->name)]['items'] ?? [],
                                                            'name' => 'lsd[assets][' . esc_attr($post_type->name) . '][items]',
                                                            'input_id' => 'lsd_' . esc_attr($post_type->name) . '_input_id',
                                                            'suggestions' => 'lsd_' . esc_attr($post_type->name) . '_suggestions',
                                                            'description' => sprintf(
                                                                esc_html__("Please select the %s for which you want to load the CSS and JS files.", 'listdom'),
                                                                strtolower($post_type->label)
                                                            ),
                                                            'placeholder' => sprintf(
                                                                esc_html__("Enter at least 3 characters for %s ...", 'listdom'),
                                                                strtolower($post_type->label)
                                                            ),
                                                        ]); ?>
                                                    </div>
                                                </div>

                                                <?php foreach ($taxonomies as $taxonomy_key => $taxonomy): if (isset($did_taxonomies[$taxonomy_key])) continue; $did_taxonomies[$taxonomy_key] = 1; ?>
                                                    <div class="lsd-tab-switcher-content lsd-tab-switcher-content-details-<?php echo $post_type->name; ?>" id="lsd-tab-switcher-<?php echo esc_attr($taxonomy->name); ?>-content">
                                                        <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4 lsd-mt-3">
                                                            <div>
                                                                <?php echo LSD_Form::switcher([
                                                                    'id' => 'lsd_load_assets_globally_' . $taxonomy->name,
                                                                    'name' => 'lsd[assets][' . $taxonomy->name . '][load]',
                                                                    'value' => $settings['assets'][$taxonomy->name]['load'] ?? '1',
                                                                    'toggle' => '#lsd-' . esc_attr($taxonomy->name) . '-content',
                                                                ]); ?>
                                                            </div>
                                                            <div>
                                                                <?php echo LSD_Form::label([
                                                                    'title' => sprintf(esc_html__('Load for all %s', 'listdom'), strtolower($taxonomy->label)),
                                                                    'for' => 'lsd_load_assets_globally_' . $taxonomy->name,
                                                                ]); ?>
                                                            </div>
                                                        </div>
                                                        <div
                                                            id="<?php echo esc_attr('lsd-' . esc_attr($taxonomy->name) . '-content'); ?>"
                                                            class="lsd-mt-4 <?php echo !isset($settings['assets'][$taxonomy->name]['load']) || $settings['assets'][$taxonomy->name]['load'] ? 'lsd-util-hide' : ''; ?>"
                                                        >
                                                            <?php echo LSD_Form::autosuggest([
                                                                'source' => $taxonomy->name,
                                                                'id' => 'lsd_' . esc_attr($taxonomy->name) . '_input',
                                                                'values' => $settings['assets'][esc_attr($taxonomy->name)]['items'] ?? [],
                                                                'name' => 'lsd[assets][' . esc_attr($taxonomy->name) . '][items]',
                                                                'input_id' => 'lsd_' . esc_attr($taxonomy->name) . '_input_id',
                                                                'suggestions' => 'lsd_' . esc_attr($taxonomy->name) . '_suggestions',
                                                                'description' => sprintf(
                                                                    esc_html__("Please select the %s for which you want to load the CSS and JS files.", 'listdom'),
                                                                    strtolower($taxonomy->label)
                                                                ),
                                                                'placeholder' => sprintf(
                                                                    esc_html__("Enter at least 3 characters for %s ...", 'listdom'),
                                                                    strtolower($taxonomy->label)
                                                                ),
                                                            ]); ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                    <?php endforeach; ?>
                                </div>

                            </div>

                            <div class="lsd-flex lsd-flex-col lsd-mt-4 lsd-flex-align-items-start lsd-gap-2">
                                <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4 lsd-mt-2">
                                    <div>
                                        <?php echo LSD_Form::switcher([
                                            'id' => 'lsd_settings_fontawesome_status',
                                            'value' => $settings['fontawesome_status'] ?? 1,
                                            'name' => 'lsd[fontawesome_status]',
                                        ]); ?>
                                    </div>
                                    <div><?php echo LSD_Form::label([
                                        'title' => esc_html__('Font Awesome Status', 'listdom'),
                                        'for' => 'lsd_settings_fontawesome_status',
                                    ]); ?></div>
                                </div>
                                <p class="description lsd-mb-0"><?php esc_html_e("Disable the Listdom icon file (Font Awesome) if it is already being loaded by a third party.", 'listdom'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="lsd_panel_advanced_custom-styles" class="lsd-settings-form-group lsd-tab-content">
                    <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Custom Styles', 'listdom'); ?></h3>
                    <div class="lsd-settings-group-wrapper">
                        <div class="lsd-settings-fields-wrapper">
                            <div class="lsd-form-row lsd-mt-0">
                        <div class="lsd-col-12">
                            <p class="description lsd-mt-0 lsd-mb-3"><?php echo sprintf(esc_html__('Enter your custom CSS code here. There is no need to include the %s tag.', 'listdom'), '<code>'.htmlspecialchars('<style>').'</code>'); ?></p>
                            <?php echo LSD_Form::textarea([
                                'id' => 'lsd_settings_custom_styles',
                                'name' => 'lsd[CSS]',
                                'value' => $styles['CSS'] ?? '',
                            ]); ?>
                        </div>
                    </div>
                        </div>
                    </div>
                </div>

                <div id="lsd_panel_advanced_components" class="lsd-settings-form-group lsd-tab-content">
                    <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Components', 'listdom'); ?></h3>

                    <div class="lsd-settings-group-wrapper">
                        <div class="lsd-settings-fields-wrapper">
                            <p class="description lsd-mt-0 lsd-mb-0"><?php esc_html_e("If you deactivate a listing component it will be hidden across all Listdom backend and frontend areas. Disable the components you don't need to simplify your website.", 'listdom'); ?></p>
                            <div class="lsd-alert lsd-info lsd-my-4"><?php esc_html_e('Disabling any listing component removes its related fields, filters, and views throughout Listdom.', 'listdom'); ?></div>

                            <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4 lsd-mt-2">
                                <div>
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_component_map',
                                        'name' => 'lsd[components][map]',
                                        'value' => $settings['components']['map'] ?? '1',
                                    ]); ?>
                                </div>
                                <div><?php echo LSD_Form::label([
                                    'title' => esc_html__('Map & Address', 'listdom'),
                                    'for' => 'lsd_component_map',
                                ]); ?></div>
                            </div>

                            <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4 lsd-mt-2">
                                <div>
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_component_pricing',
                                        'name' => 'lsd[components][pricing]',
                                        'value' => $settings['components']['pricing'] ?? '1',
                                    ]); ?>
                                </div>
                                <div><?php echo LSD_Form::label([
                                    'title' => esc_html__('Pricing', 'listdom'),
                                    'for' => 'lsd_component_pricing',
                                ]); ?></div>
                            </div>

                            <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4 lsd-mt-2">
                                <div>
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_component_work_hours',
                                        'name' => 'lsd[components][work_hours]',
                                        'value' => $settings['components']['work_hours'] ?? '1',
                                    ]); ?>
                                </div>
                                <div><?php echo LSD_Form::label([
                                    'title' => esc_html__('Work Hours', 'listdom'),
                                    'for' => 'lsd_component_work_hours',
                                ]); ?></div>
                            </div>
                            <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4 lsd-mt-2">
                                <div>
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_component_visibility',
                                        'name' => 'lsd[components][visibility]',
                                        'value' => $settings['components']['visibility'] ?? '1',
                                    ]); ?>
                                </div>
                                <div><?php echo LSD_Form::label([
                                    'title' => esc_html__('Visibility', 'listdom'),
                                    'for' => 'lsd_component_visibility',
                                ]); ?></div>
                            </div>
                            <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4 lsd-mt-2">
                                <div>
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_component_related',
                                        'name' => 'lsd[components][related]',
                                        'value' => $settings['components']['related'] ?? '1',
                                    ]); ?>
                                </div>
                                <div><?php echo LSD_Form::label([
                                    'title' => esc_html__('Related Listings', 'listdom'),
                                    'for' => 'lsd_component_related',
                                ]); ?></div>
                            </div>
                            <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4 lsd-mt-2">
                                <div>
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_component_socials',
                                        'name' => 'lsd[components][socials]',
                                        'value' => $settings['components']['socials'] ?? '1',
                                    ]); ?>
                                </div>
                                <div><?php echo LSD_Form::label([
                                    'title' => esc_html__('Social Networks', 'listdom'),
                                    'for' => 'lsd_component_socials',
                                ]); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                    // Third Party Options
                    do_action('lsd_settings_form_advanced', $settings);
                ?>

                <div class="lsd-spacer-10"></div>
                <div class="lsd-form-row lsd-settings-submit-wrapper">
                    <div class="lsd-col-12 lsd-flex lsd-gap-3 lsd-flex-content-end">
                        <?php LSD_Form::nonce('lsd_settings_form'); ?>
                        <button type="submit" id="lsd_settings_save_button" class="lsd-primary-button">
                            <?php esc_html_e('Save The Changes', 'listdom'); ?>
                            <i class='lsdi lsdi-checkmark-circle'></i>
                        </button>
                        <div>
                            <p class="lsd-util-hide lsd-settings-success-message lsd-alert lsd-success lsd-m-0"><?php esc_html_e('Options saved successfully.', 'listdom'); ?></p>
                            <p class="lsd-util-hide lsd-settings-error-message lsd-alert lsd-error lsd-m-0"><?php esc_html_e('Error: Unable to save options.', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="lsd_panel_advanced_import-export" class="lsd-settings-form-group lsd-tab-content lsd-w-full lsd-flex-o-1">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Import/Export', 'listdom'); ?></h3>

            <div class="lsd-col-12">
                <div class="lsd-settings-ix-wrapper lsd-flex lsd-flex-col lsd-flex-items-start lsd-flex-items-stretch lsd-gap-3">
                    <div class="lsd-settings-group-wrapper">
                        <div class="lsd-settings-fields-wrapper">
                            <div class="lsd-box-white">
                                <h4 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Import', 'listdom'); ?></h4>
                                <p class="description lsd-my-0"><?php esc_html_e("Simply restore a backup using the exported JSON file.", 'listdom'); ?></p>
                                <p class="lsd-text-red lsd-mt-0"><?php echo sprintf(esc_html__("%s: This will overwrite all existing configurations.", 'listdom'), '<strong class="lsd-text-uppercase">'.esc_html__('Caution', 'listdom').'</strong>'); ?></p>
                                <form id="lsd_import_settings_form" enctype="multipart/form-data" method="post">
                                    <input class="lsd-m-0 lsd-p-0" type="file" id="lsd_settings_file">
                                    <div class="lsd-text-left lsd-mt-3">
                                        <input type="hidden" name="action" value="lsd_settings_import">
                                        <?php wp_nonce_field('lsd_settings_import'); ?>
                                        <button class="lsd-secondary-button" type="submit" id="lsd_import_settings_button"><?php esc_html_e('Import', 'listdom'); ?></button>
                                    </div>
                                </form>
                                <div>
                                    <p class="lsd-util-hide lsd-settings-import-success-message lsd-alert lsd-success lsd-m-0"><?php esc_html_e('Options imported successfully.', 'listdom'); ?></p>
                                    <p class="lsd-util-hide lsd-settings-import-error-message lsd-alert lsd-error lsd-m-0"><?php esc_html_e('Error: Unable to import options.', 'listdom'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="lsd-settings-fields-wrapper">
                            <div class="lsd-box-white">
                                <h4 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Export', 'listdom'); ?></h4>
                                <p class="description lsd-mb-3"><?php esc_html_e("You can export all Listdom settings in JSON format to create a backup or import them to another website.", 'listdom'); ?></p>
                                <div class="lsd-text-left">
                                    <a class="lsd-secondary-button" href="<?php echo esc_url_raw(LSD_IX_Settings::get_export_url()); ?>"><?php esc_html_e('Export', 'listdom'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Settings Form
jQuery('#lsd_settings_form').on('submit', function (e)
{
    e.preventDefault();

    // Elements
    const $button = jQuery("#lsd_settings_save_button");
    const $success = jQuery(".lsd-settings-success-message");
    const $error = jQuery(".lsd-settings-error-message");
    const $tab = jQuery('.nav-tab-active');

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
        data: "action=lsd_save_advanced&" + settings,
        success: function ()
        {
            $tab.attr('data-saved', 'true');

            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save The Changes', 'listdom')); ?><i class='lsdi lsdi-checkmark-circle'></i>");

            // Unloading
            loading.stop($success, 2000);
        },
        error: function ()
        {
            $tab.attr('data-saved', 'false');

            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save The Changes', 'listdom')); ?><i class='lsdi lsdi-checkmark-circle'></i>");

            // Unloading
            loading.stop($error, 2000);
        }
    });
});

// Import Settings Form
jQuery('#lsd_import_settings_form').on('submit', function (e)
{
    e.preventDefault();

    // Form Data
    let fd = new FormData();

    let fields = jQuery(this).find(jQuery(':input')).serializeArray();
    jQuery.each(fields, function (i, field) {
        fd.append(field.name, field.value);
    });

    // Append File
    let $file = jQuery('#lsd_settings_file');
    fd.append('import', $file.prop('files')[0]);

    const $button = jQuery('#lsd_import_settings_button');
    const $success = jQuery('.lsd-settings-import-success-message');
    const $error = jQuery('.lsd-settings-import-error-message');

    // Loading Styles
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    jQuery.ajax(
    {
        url: lsd.ajaxurl,
        type: 'POST',
        data: fd,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function () {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Import', 'listdom')); ?>");

            // Unloading
            loading.stop($success, 1000);

            setTimeout(function () {
                window.location.reload();
            }, 2000);
        },
        error: function () {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Import', 'listdom')); ?>");

            // Unloading
            loading.stop($error, 2000);
        },
    });
});
</script>
