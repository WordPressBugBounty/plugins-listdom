<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

// Dashboard
$dashboard = new LSD_Dashboard();

// Settings
$settings = LSD_Options::settings();

// Dashboard Shortcode
$ds = new LSD_Shortcodes_Dashboard();

$menus = $ds->menu_ids();
unset($menus['manage'], $menus['logout']);

// Custom Menus
$custom_menus = $settings['dashboard_menu_custom'] ?? [];
$filtered_menus = array_filter($menus, function ($menu) use ($custom_menus)
{
    $menu_id = is_array($menu) && isset($menu['id']) ? $menu['id'] : $menu;
    return !isset($custom_menus[$menu_id]);
});
?>
<div class="lsd-settings-wrap" id="lsd_settings_frontend_dashboard">
    <?php if ($this->isLite()): ?>
    <div class="lsd-alert lsd-warning lsd-my-4">
        <?php echo LSD_Base::missFeatureMessage(esc_html__('Frontend Dashboard', 'listdom')); ?>
    </div>
    <?php endif; ?>
    <form id="lsd_settings_form">
        <div class="lsd-accordion-title lsd-accordion-active">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Pages', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel lsd-accordion-open">
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Dashboard Page', 'listdom'),
                    'for' => 'lsd_settings_submission_page',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::pages([
                        'id' => 'lsd_settings_submission_page',
                        'value' => $settings['submission_page'] ?? null,
                        'name' => 'lsd[submission_page]',
                        'show_empty' => true,
                    ]); ?>
                    <p class="description"><?php echo sprintf(esc_html__("Put %s shortcode into the page.", 'listdom'), '<code>[listdom-dashboard]</code>'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Independent Add Listing Form', 'listdom'),
                    'for' => 'lsd_settings_add_listing_page_status',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_settings_add_listing_page_status',
                        'value' => $settings['add_listing_page_status'] ?? 0,
                        'name' => 'lsd[add_listing_page_status]',
                        'toggle' => '#lsd_settings_add_listing_page_status_options',
                    ]); ?>
                    <p class="description"><?php esc_html_e("Enable to have a independent add listing page ", 'listdom'); ?></p>
                </div>
            </div>
            <div id="lsd_settings_add_listing_page_status_options"
                 class="lsd-form-row  <?php echo isset($settings['add_listing_page_status']) && $settings['add_listing_page_status'] ? '' : 'lsd-util-hide'; ?>">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Add Listing Page', 'listdom'),
                    'for' => 'lsd_settings_add_listing_page',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::pages([
                        'id' => 'lsd_settings_add_listing_page',
                        'value' => $settings['add_listing_page'] ?? null,
                        'name' => 'lsd[add_listing_page]',
                        'show_empty' => true,
                    ]); ?>
                    <p class="description lsd-mb-0"><?php echo sprintf(esc_html__("Put %s shortcode into the page.", 'listdom'), '<code>[listdom-add-listing]</code>'); ?></p>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Dashboard Menus', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel">
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Sort dashboard menus', 'listdom'),
                        'for' => 'lsd_settings_add_listing_page_status',
                    ]); ?>
                </div>
                <div class="lsd-col-4">
                    <ul class="lsd-settings-dashboard-menus">
                        <?php foreach ($filtered_menus as $key => $menu)
                        {
                            $target = $menu['target'] ?? '_self';
                            $icon = $menu['icon'] ?? 'fas fa-tachometer-alt';
                            $id = $menu['id'] ?? 'lsd_dashboard_menus_' . $key;

                            echo '<li id="' . esc_attr($id) . '"><p><i class="lsd-icon ' . esc_attr($icon) . '"></i><span>' . esc_html($menu['label']) . '</span></p><input type="hidden" name="lsd[dashboard_menus][]" value="' . esc_attr($id) . '"/></li>';
                        }

                        foreach ($custom_menus as $menu):
                            $label = $menu['label'] ?? '';
                            $slug = $menu['slug'] ?? '';
                            $icon = $menu['icon'] ?? 'fas fa-tachometer-alt'; ?>
                            <li class="lsd-custom-menu-list">
                                <p>
                                    <span class="lsd-custom-menu-label"><?php echo esc_html($label); ?></span>
                                    <span class="lsd-custom-menu-actions">
                                        <i class="fas fa-trash"></i>
                                        <i class="fas fa-chevron-down"></i>
                                    </span>
                                </p>
                                <div class="lsd-custom-menu-content">
                                    <label>
                                        <?php esc_html_e('Label', 'listdom'); ?>
                                        <input type="text"
                                          name="lsd[dashboard_menu_custom][<?php echo esc_attr($slug); ?>][label]"
                                          placeholder="<?php esc_attr_e('Enter the menu name', 'listdom') ?>"
                                          required="required"
                                          value="<?php echo esc_attr($label); ?>"
                                          data-field="label">
                                        <p class="description"><?php esc_html_e('This is the label for your menu item.', 'listdom'); ?></p>
                                    </label>
                                    <label>
                                        <?php esc_html_e('Slug', 'listdom'); ?>
                                        <input type="text"
                                           name="lsd[dashboard_menu_custom][<?php echo esc_attr($slug); ?>][id]"
                                           placeholder="<?php esc_attr_e('Enter the menu slug', 'listdom') ?>"
                                           required="required"
                                           value="<?php echo esc_attr($slug); ?>"
                                           data-field="slug">
                                        <p class="description"><?php esc_html_e('Provide a unique slug for this menu.', 'listdom'); ?></p>
                                    </label>
                                    <label>
                                        <div class="lsd-mb-2"><?php esc_html_e('Icon', 'listdom'); ?></div>
                                        <?php echo LSD_Form::iconpicker([
                                            'name' => 'lsd[dashboard_menu_custom][' . esc_attr($slug) . '][icon]',
                                            'id' => 'lsd_icon',
                                            'value' => $icon,
                                            'data-field' => 'icon',
                                        ]); ?>
                                        <p class="description"><?php esc_html_e('Select an icon.', 'listdom'); ?></p>
                                    </label>
                                    <label>
                                        <?php echo LSD_Form::editor([
                                            'id' => esc_attr($slug),
                                            'name' => 'lsd[dashboard_menu_custom][' . esc_attr($slug) . '][content]',
                                            'value' => $menu['content'] ?? '',
                                            'data-field' => 'content',
                                        ]); ?>
                                        <p class="description"><?php esc_html_e('Type dashboard content. You can also use shorcodes.', 'listdom'); ?></p>
                                    </label>
                                </div>
                                <input type="hidden" name="lsd[dashboard_menus][]" class="custom-menu-slug"
                                    value="<?php echo esc_attr($slug); ?>">
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="lsd-custom-menu-btn button"><?php esc_html_e('Add Custom Menu', 'listdom'); ?></button>
                    <p class="description lsd-mb-0 lsd-mt-3"><?php esc_html_e("Drag and drop the menus to change the order of dashboard menus.", 'listdom'); ?></p>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Guest Submission', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel">
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Status', 'listdom'),
                    'for' => 'lsd_settings_submission_guest',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_settings_submission_guest',
                        'value' => $settings['submission_guest'] ?? 0,
                        'name' => 'lsd[submission_guest]',
                        'toggle' => '#lsd_settings_submission_guest_options',
                    ]); ?>
                    <p class="description lsd-mb-2"><?php esc_html_e("Enable listing submission for guest users!", 'listdom'); ?></p>
                </div>
            </div>
            <div id="lsd_settings_submission_guest_options" class="lsd-mt-3 <?php echo isset($settings['submission_guest']) && $settings['submission_guest'] ? '' : 'lsd-util-hide'; ?>">
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('User Registration', 'listdom'),
                        'for' => 'lsd_settings_submission_guest_registration',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::select([
                            'id' => 'lsd_settings_submission_guest_registration',
                            'value' => $settings['submission_guest_registration'] ?? 'approval',
                            'name' => 'lsd[submission_guest_registration]',
                            'options' => [
                                'approval' => esc_html__('Once Approved', 'listdom'),
                                'submission' => esc_html__('Once Submitted', 'listdom'),
                                '0' => esc_html__('Disabled', 'listdom'),
                            ],
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Fields', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel">
            <?php foreach ([LSD_Base::TAX_LOCATION => esc_html__('Locations'), LSD_Base::TAX_FEATURE => esc_html__('Features'),] as $tax => $label): ?>
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__($label, 'listdom'),
                        'for' => 'lsd_settings_tax_' . $tax . '_method',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::select([
                            'id' => 'lsd_settings_tax_' . $tax . '_method',
                            'options' => [
                                'checkboxes' => esc_html__('Checkboxes', 'listdom'),
                                'dropdown' => esc_html__('Dropdown', 'listdom'),
                            ],
                            'value' => $settings['submission_tax_' . $tax . '_method'] ?? 'checkboxes',
                            'name' => 'lsd[submission_tax_' . $tax . '_method]',
                        ]); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Gallery Method', 'listdom'),
                    'for' => 'lsd_settings_submission_gallery_method',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::select([
                        'id' => 'lsd_settings_submission_gallery_method',
                        'options' => [
                            'wp' => esc_html__('WordPress Media', 'listdom'),
                            'uploader' => esc_html__('Simple Uploader', 'listdom'),
                        ],
                        'value' => $settings['submission_gallery_method'] ?? 'wp',
                        'name' => 'lsd[submission_gallery_method]',
                    ]); ?>
                </div>
            </div>
            <h3 class="lsd-mt-5 lsd-mb-0"><?php esc_html_e('Required Fields', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-12">
                    <ul class="lsd-boxed-list lsd-mb-0">
                        <?php foreach ($dashboard->fields() as $f => $field): ?>
                            <li class="lsd-d-inline-block">
                                <label
                                    class="<?php echo isset($field['always_enabled']) && $field['always_enabled'] ? 'lsd-always-enabled' : ''; ?>">
                                    <input type="hidden" name="lsd[submission_fields][<?php echo esc_attr($f); ?>][required]" value="0">
                                    <input type="checkbox" name="lsd[submission_fields][<?php echo esc_attr($f); ?>][required]" value="1" <?php echo (isset($settings['submission_fields'][$f]) && $settings['submission_fields'][$f]['required'] == 1) || (isset($field['always_enabled']) && $field['always_enabled']) ? 'checked' : ''; ?> <?php echo isset($field['always_enabled']) && $field['always_enabled'] ? 'disabled' : ''; ?>>
                                    <?php echo esc_html($field['label']); ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Restrictions', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel">
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Maximum Gallery Images', 'listdom'),
                    'for' => 'lsd_settings_submission_max_gallery_images',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::number([
                        'id' => 'lsd_settings_submission_max_gallery_images',
                        'value' => $settings['submission_max_gallery_images'] ?? '',
                        'name' => 'lsd[submission_max_gallery_images]',
                        'attributes' => [
                            'min' => 0,
                            'step' => 1,
                        ],
                    ]); ?>
                    <p class="description lsd-mb-2"><?php esc_html_e("Leave it empty for unlimited number of images", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Maximum Image Size Allowed', 'listdom'),
                    'for' => 'lsd_settings_submission_max_image_upload_size',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::number([
                        'id' => 'lsd_settings_submission_max_image_upload_size',
                        'value' => $settings['submission_max_image_upload_size'] ?? '',
                        'name' => 'lsd[submission_max_image_upload_size]',
                        'attributes' => [
                            'min' => 0,
                            'step' => 10,
                        ],
                    ]); ?>
                    <p class="description lsd-mb-2"><?php esc_html_e("Leave it empty for unlimited size of images. The size is in KB", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Maximum Description Length', 'listdom'),
                    'for' => 'lsd_settings_submission_max_description_length',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::number([
                        'id' => 'lsd_settings_submission_max_description_length',
                        'value' => $settings['submission_max_description_length'] ?? '',
                        'name' => 'lsd[submission_max_description_length]',
                        'attributes' => [
                            'min' => 0,
                            'step' => 10,
                        ],
                    ]); ?>
                    <p class="description lsd-mb-2"><?php esc_html_e("Leave it empty for unlimited length", 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Maximum Number of Tags', 'listdom'),
                    'for' => 'lsd_settings_submission_max_tags_count',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::number([
                        'id' => 'lsd_settings_submission_max_tags_count',
                        'value' => $settings['submission_max_tags_count'] ?? '',
                        'name' => 'lsd[submission_max_tags_count]',
                        'attributes' => [
                            'min' => 0,
                            'step' => 1,
                        ],
                    ]); ?>
                    <p class="description lsd-mb-2"><?php esc_html_e("Leave it empty for unlimited number of tags", 'listdom'); ?></p>
                </div>
            </div>

            <h3 class="lsd-mt-5 lsd-mb-4"><?php esc_html_e('Modules', 'listdom'); ?></h3>
            <?php foreach ($dashboard->modules() as $module): ?>
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => $module['label'],
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <div class="lsd-radio-toggle lsd-mb-1">
                            <input type="radio" name="lsd[submission_module][<?php echo esc_attr($module['key']); ?>]" value="1" id="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_1" <?php echo isset($settings['submission_module'][$module['key']]) && $settings['submission_module'][$module['key']] == 1 ? 'checked="checked"' : ''; ?>>
                            <label for="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_1"><?php esc_html_e('Enabled', 'listdom'); ?></label>
                            <input type="radio" name="lsd[submission_module][<?php echo esc_attr($module['key']); ?>]" value="2" id="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_2" <?php echo isset($settings['submission_module'][$module['key']]) && $settings['submission_module'][$module['key']] == 2 ? 'checked="checked"' : ''; ?>>
                            <label for="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_2"><?php esc_html_e('Editor + Admin', 'listdom'); ?></label>
                            <input type="radio" name="lsd[submission_module][<?php echo esc_attr($module['key']); ?>]" value="0" id="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_0" <?php echo isset($settings['submission_module']) && (!isset($settings['submission_module'][$module['key']]) || !$settings['submission_module'][$module['key']]) ? 'checked="checked"' : ''; ?>>
                            <label for="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_0"><?php esc_html_e('Disabled', 'listdom'); ?></label>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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
jQuery('#lsd_settings_form').on('submit', function (e)
{
    e.preventDefault();

    // Remove Existing Errors
    jQuery('.lsd-simple-error-message').remove();

    let hasDuplicateError = false;
    if (hasDuplicate('slug'))
    {
        jQuery('input[data-field="slug"]').each(function ()
        {
            jQuery(this).after('<p class="lsd-simple-error-message"><?php esc_html_e('Values for slugs cannot be equal!', 'listdom'); ?></p>');
        });

        hasDuplicateError = true;
    }

    if (hasDuplicateError) {
        return false;
    }

    jQuery('.lsd-custom-menu-content textarea').each(function ()
    {
        const uniqueId = jQuery(this).attr('slug');
        const content = tinymce.activeEditor.getContent(uniqueId);

        jQuery(this).val(content);
    });

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
        data: "action=lsd_save_dashboard&" + settings,
        success: function ()
        {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($success, 2000);
        },
        error: function ()
        {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($error, 2000);
        }
    });
});

function hasDuplicate(type)
{
    const values = [];
    let duplicate = false;

    jQuery('input[data-field="' + type + '"]').each(function ()
    {
        const value = jQuery(this).val();

        if (values.includes(value))
        {
            duplicate = true;
            return false;
        }

        values.push(value);
    });

    return duplicate;
}
</script>
