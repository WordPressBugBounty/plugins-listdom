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

$menus = $ds->menu_ids(true);

// Custom Menus
$custom_menus = $settings['dashboard_menu_custom'] ?? [];
$builtin_menu_statuses = $settings['dashboard_menu_builtin_status'] ?? [];
$filtered_menus = array_filter($menus, function ($menu) use ($custom_menus)
{
    $menu_id = is_array($menu) && isset($menu['id']) ? $menu['id'] : $menu;
    return !isset($custom_menus[$menu_id]);
});

$privacy = LSD_Options::privacy();
$add_listing_subtabs = ['general', 'guest-submission', 'fields', 'restrictions'];
$add_listing_active = $this->subtab === 'add-listing' || !$this->subtab || in_array($this->subtab, $add_listing_subtabs, true);
$add_listing_subtab = in_array($this->subtab, $add_listing_subtabs, true) ? $this->subtab : 'general';
?>
<div class="lsd-settings-wrap" id="lsd_settings_frontend_dashboard">
    <form id="lsd_settings_form">
        <div id="lsd_panel_frontend-dashboard_dashboard-menus"
             class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'dashboard-menus' ? ' lsd-tab-content-active' : ''; ?>">
            <h3 class="lsd-admin-title lsd-mt-0"><?php esc_html_e('Dashboard Menus', 'listdom'); ?></h3>

            <div class="lsd-settings-group-wrapper">
                <div class="lsd-settings-fields-wrapper">
                    <?php if ($this->isLite()): ?>
                        <div class="lsd-alert lsd-warning lsd-mt-0 lsd-mb-4">
                            <?php echo LSD_Base::missFeatureMessage(sprintf(esc_html__('Some options of %s', 'listdom'), '<strong>'.esc_html__('Dashboard Menus', 'listdom').'</strong>'), true, false); ?>
                        </div>
                    <?php endif; ?>
                    <div class="lsd-form-row">
                        <div class="lsd-col-8">
                            <ul class="lsd-settings-dashboard-menus lsd-mt-0">
                                <?php
                                $ordered_menu_ids = $settings['dashboard_menus'] ?? [];
                                $menu_items = [];

                                foreach ($filtered_menus as $key => $menu)
                                {
                                    $id = $menu['id'] ?? 'lsd_dashboard_menus_' . $key;
                                    $menu_items[$id] = [
                                        'type' => 'builtin',
                                        'key' => $key,
                                        'menu' => $menu,
                                    ];
                                }

                                foreach ($custom_menus as $custom_key => $menu)
                                {
                                    if (!is_array($menu)) continue;

                                    $slug = $menu['slug'] ?? (is_string($custom_key) ? $custom_key : '');
                                    if ($slug === '') continue;

                                    $menu_items[$slug] = [
                                        'type' => 'custom',
                                        'key' => $slug,
                                        'menu' => $menu,
                                    ];
                                }

                                $ordered_items = [];
                                foreach ($ordered_menu_ids as $menu_id)
                                {
                                    if (isset($menu_items[$menu_id]))
                                    {
                                        $ordered_items[] = $menu_items[$menu_id];
                                        unset($menu_items[$menu_id]);
                                    }
                                }

                                if (count($menu_items))
                                {
                                    $ordered_items = array_merge($ordered_items, array_values($menu_items));
                                }

                                foreach ($ordered_items as $item)
                                {
                                    $menu = $item['menu'];

                                    if ($item['type'] === 'builtin')
                                    {
                                        $key = $item['key'];

                                        $icon = $menu['icon'] ?? 'fas fa-tachometer-alt';
                                        $id = $menu['id'] ?? 'lsd_dashboard_menus_' . $key;
                                        $default_label = $menu['default_label'] ?? '';
                                        $builtin_label = $settings['dashboard_menu_builtin'][$key]['label'] ?? '';
                                        $builtin_icon = $settings['dashboard_menu_builtin'][$key]['icon'] ?? '';
                                        $menu_label = $builtin_label !== '' ? $builtin_label : $menu['label'];
                                        $menu_icon = $builtin_icon !== '' ? $builtin_icon : $icon;
                                        $enabled = !isset($builtin_menu_statuses[$key]) || (int) $builtin_menu_statuses[$key] === 1;
                                        $enabled_value = $enabled ? 1 : 0;
                                        $toggle_icon = $enabled ? 'fa-check-circle' : 'fa-minus-circle';
                                        $disabled_class = $enabled ? '' : ' lsd-dashboard-menu-item-disabled';

                                        $icon_picker = LSD_Form::iconpicker([
                                            'name' => 'lsd[dashboard_menu_builtin][' . esc_attr($key) . '][icon]',
                                            'id' => 'lsd_builtin_menu_' . esc_attr($key) . '_icon',
                                            'value' => $menu_icon,
                                            'class' => 'lsd-admin-input lsd-iconpicker',
                                        ]);
                                        ?>
                                        <li class="lsd-dashboard-menu-item<?php echo esc_attr($disabled_class); ?>"
                                            id="<?php echo esc_attr($id); ?>">
                                            <p class="lsd-flex lsd-flex-content-between">
                                                <span>
                                                    <i class="lsd-icon <?php echo esc_attr($menu_icon); ?>"></i>
                                                    <span><?php echo esc_html($menu_label); ?></span>
                                                    <?php if ($default_label && $menu_label !== $default_label): ?>
                                                        <span
                                                            class="lsd-dashboard-menu-default-label">(<?php echo esc_html($default_label); ?>)</span>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="lsd-menu-actions">
                                                    <button type="button" class="lsd-dashboard-menu-toggle"
                                                            aria-pressed="<?php echo $enabled ? 'true' : 'false'; ?>">
                                                        <i class="fas <?php echo esc_attr($toggle_icon); ?>"></i>
                                                    </button>
                                                    <i class="fas fa-chevron-down lsd-dashboard-menu-chevron"></i>
                                                </span>
                                            </p>
                                            <div class="lsd-dashboard-menu-content lsd-util-hide">
                                                <div class="lsd-field-group">
                                                    <label class="lsd-fields-label-tiny"
                                                           for="lsd_builtin_menu_<?php echo esc_attr($key); ?>_label">
                                                        <?php esc_html_e('Label', 'listdom'); ?>
                                                    </label>
                                                    <input
                                                        id="lsd_builtin_menu_<?php echo esc_attr($key); ?>_label"
                                                        type="text"
                                                        class="lsd-admin-input"
                                                        name="lsd[dashboard_menu_builtin][<?php echo esc_attr($key); ?>][label]"
                                                        placeholder="<?php esc_attr_e('Enter the menu name', 'listdom'); ?>"
                                                        value="<?php echo esc_attr($menu_label); ?>"
                                                    >
                                                </div>
                                                <div class="lsd-field-group">
                                                    <label class="lsd-fields-label-tiny"
                                                           for="lsd_builtin_menu_<?php echo esc_attr($key); ?>_icon">
                                                        <?php esc_html_e('Icon', 'listdom'); ?>
                                                    </label>
                                                    <?php echo LSD_Kses::form($icon_picker); ?>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lsd[dashboard_menus][]"
                                                   value="<?php echo esc_attr($id); ?>">
                                            <input type="hidden" class="lsd-dashboard-menu-status"
                                                   name="lsd[dashboard_menu_builtin_status][<?php echo esc_attr($key); ?>]"
                                                   value="<?php echo esc_attr($enabled_value); ?>">
                                        </li>
                                        <?php
                                        continue;
                                    }

                                    $label = $menu['label'] ?? '';
                                    $slug = $menu['slug'] ?? $item['key'];
                                    $icon = $menu['icon'] ?? 'fas fa-tachometer-alt';
                                    $enabled = !isset($menu['enabled']) || (int) $menu['enabled'] === 1;
                                    $enabled_value = $enabled ? 1 : 0;
                                    $toggle_icon = $enabled ? 'fa-check-circle' : 'fa-minus-circle';
                                    $disabled_class = $enabled ? '' : ' lsd-dashboard-menu-item-disabled';
                                    ?>
                                    <li class="lsd-dashboard-menu-item lsd-custom-menu-list<?php echo esc_attr($disabled_class); ?>">
                                        <p class="lsd-flex lsd-flex-content-between">
                                            <span>
                                                <i class="lsd-icon lsd-custom-menu-icon <?php echo esc_attr($icon); ?>"></i>
                                                <span
                                                    class="lsd-custom-menu-label"><?php echo esc_html($label); ?></span>
                                            </span>
                                            <span class="lsd-menu-actions">
                                                <i class="fas fa-trash"></i>
                                                <button type="button" class="lsd-dashboard-menu-toggle"
                                                        aria-pressed="<?php echo $enabled ? 'true' : 'false'; ?>"><i
                                                        class="fas <?php echo esc_attr($toggle_icon); ?>"></i></button>
                                                <i class="fas fa-chevron-down lsd-dashboard-menu-chevron"></i>
                                            </span>
                                        </p>
                                        <div class="lsd-custom-menu-content lsd-dashboard-menu-content lsd-util-hide">
                                            <?php $base_id = 'lsd_menu_' . esc_attr($slug); ?>
                                            <div class="lsd-field-group">
                                                <input
                                                    type="hidden"
                                                    class="lsd-dashboard-menu-status"
                                                    name="lsd[dashboard_menu_custom][<?php echo esc_attr($slug); ?>][enabled]"
                                                    value="<?php echo esc_attr($enabled_value); ?>"
                                                    data-field="enabled"
                                                >
                                                <label class="lsd-fields-label-tiny"
                                                       for="<?php echo $base_id; ?>_label">
                                                    <?php esc_html_e('Label', 'listdom'); ?>
                                                </label>
                                                <input
                                                    id="<?php echo $base_id; ?>_label"
                                                    type="text"
                                                    class="lsd-admin-input"
                                                    name="lsd[dashboard_menu_custom][<?php echo esc_attr($slug); ?>][label]"
                                                    placeholder="<?php esc_attr_e('Enter the menu name', 'listdom') ?>"
                                                    required="required"
                                                    value="<?php echo esc_attr($label); ?>"
                                                    data-field="label"
                                                />
                                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">
                                                    <?php esc_html_e('This is the label for your menu item.', 'listdom'); ?>
                                                </p>
                                            </div>

                                            <div class="lsd-field-group">
                                                <label class="lsd-fields-label-tiny" for="<?php echo $base_id; ?>_slug">
                                                    <?php esc_html_e('Slug', 'listdom'); ?>
                                                </label>
                                                <input
                                                    id="<?php echo $base_id; ?>_slug"
                                                    type="text"
                                                    class="lsd-admin-input"
                                                    name="lsd[dashboard_menu_custom][<?php echo esc_attr($slug); ?>][slug]"
                                                    placeholder="<?php esc_attr_e('Enter the menu slug', 'listdom') ?>"
                                                    required="required"
                                                    value="<?php echo esc_attr($slug); ?>"
                                                    data-field="slug"
                                                />
                                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">
                                                    <?php esc_html_e('Provide a unique slug for this menu.', 'listdom'); ?>
                                                </p>
                                            </div>

                                            <div class="lsd-field-group">
                                                <label class="lsd-fields-label-tiny" for="<?php echo $base_id; ?>_icon">
                                                    <?php esc_html_e('Icon', 'listdom'); ?>
                                                </label>
                                                <?php echo LSD_Form::iconpicker([
                                                    'name' => 'lsd[dashboard_menu_custom][' . esc_attr($slug) . '][icon]',
                                                    'id' => $base_id . '_icon',
                                                    'value' => $icon,
                                                    'data-field' => 'icon',
                                                ]); ?>
                                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">
                                                    <?php esc_html_e('Select an icon.', 'listdom'); ?>
                                                </p>
                                            </div>

                                            <div class="lsd-field-group">
                                                <label class="lsd-fields-label-tiny"
                                                       for="<?php echo $base_id; ?>_login_status">
                                                    <?php esc_html_e('Login Status', 'listdom'); ?>
                                                </label>
                                                <?php echo LSD_Form::select([
                                                    'id' => $base_id . '_login_status',
                                                    'name' => 'lsd[dashboard_menu_custom][' . esc_attr($slug) . '][login_status]',
                                                    'data-field' => 'login_status',
                                                    'options' => [
                                                        'required' => esc_html__('Required (only logged-in users)', 'listdom'),
                                                        'optional' => esc_html__('Optional (visible to all users)', 'listdom'),
                                                    ],
                                                    'value' => $menu['login_status'] ?? 'required',
                                                    'class' => 'lsd-admin-input',
                                                ]); ?>
                                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">
                                                    <?php esc_html_e('Choose whether this custom menu requires the user to be logged in.', 'listdom'); ?>
                                                </p>
                                            </div>

                                            <!-- Content -->
                                            <div class="lsd-field-group">
                                                <label class="lsd-fields-label-tiny"
                                                       for="<?php echo $base_id; ?>_content">
                                                    <?php esc_html_e('Content', 'listdom'); ?>
                                                </label>
                                                <?php echo LSD_Form::editor([
                                                    'id' => $base_id . '_content',
                                                    'name' => 'lsd[dashboard_menu_custom][' . esc_attr($slug) . '][content]',
                                                    'value' => $menu['content'] ?? '',
                                                    'data-field' => 'content',
                                                ]); ?>
                                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">
                                                    <?php esc_html_e('Type dashboard content. You can also use shortcodes.', 'listdom'); ?>
                                                </p>
                                            </div>
                                        </div>

                                        <input type="hidden" name="lsd[dashboard_menus][]" class="custom-menu-slug"
                                               value="<?php echo esc_attr($slug); ?>">
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <button type="button"
                                    class="lsd-custom-menu-btn lsd-secondary-button"><?php esc_html_e('Add Custom Menu', 'listdom'); ?></button>
                            <p class="lsd-admin-description lsd-mb-0 lsd-mt-2"><?php esc_html_e("Drag and drop the menus to change the order of dashboard menus.", 'listdom'); ?></p>
                            <?php echo LSD_Form::iconpicker([
                                'name' => '',
                                'id' => 'lsd_icon',
                                'value' => 'fas fa-tachometer-alt',
                                'data-field' => 'icon',
                                'class' => 'lsd-util-hide',
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="lsd_panel_frontend-dashboard_add-listing"
             class="lsd-settings-form-group lsd-tab-content<?php echo $add_listing_active ? ' lsd-tab-content-active' : ''; ?>">
            <h3 class="lsd-admin-title lsd-mt-0 lsd-no-border lsd-mb-2"><?php esc_html_e('Add Listing', 'listdom'); ?></h3>
            <ul class="lsd-tab-switcher lsd-level-3-menu lsd-sub-tabs lsd-flex lsd-mb-4"
                data-for=".lsd-frontend-dashboard-add-listing-tab-switcher-content">
                <li data-tab="frontend-dashboard-add-listing-general"
                    class="<?php echo $add_listing_subtab === 'general' ? 'lsd-sub-tabs-active' : ''; ?>"><a
                        href="#"><?php esc_html_e('General', 'listdom'); ?></a></li>
                <li data-tab="frontend-dashboard-add-listing-guest-submission"
                    class="<?php echo $add_listing_subtab === 'guest-submission' ? 'lsd-sub-tabs-active' : ''; ?>"><a
                        href="#"><?php esc_html_e('Guest Submission', 'listdom'); ?></a></li>
                <li data-tab="frontend-dashboard-add-listing-fields"
                    class="<?php echo $add_listing_subtab === 'fields' ? 'lsd-sub-tabs-active' : ''; ?>"><a
                        href="#"><?php esc_html_e('Fields', 'listdom'); ?></a></li>
                <li data-tab="frontend-dashboard-add-listing-restrictions"
                    class="<?php echo $add_listing_subtab === 'restrictions' ? 'lsd-sub-tabs-active' : ''; ?>"><a
                        href="#"><?php esc_html_e('Restrictions', 'listdom'); ?></a></li>
            </ul>
            <div
                class="lsd-tab-switcher-content lsd-frontend-dashboard-add-listing-tab-switcher-content<?php echo $add_listing_subtab === 'general' ? ' lsd-tab-switcher-content-active' : ''; ?>"
                id="lsd-tab-switcher-frontend-dashboard-add-listing-general-content">
                <div class="lsd-settings-group-wrapper">
                    <div class="lsd-settings-fields-wrapper">
                        <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Pages', 'listdom'); ?></h3>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Dashboard Page', 'listdom'),
                                    'for' => 'lsd_settings_submission_page',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::pages([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_settings_submission_page',
                                    'value' => $settings['submission_page'] ?? null,
                                    'name' => 'lsd[submission_page]',
                                    'show_empty' => true,
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo sprintf(
                                    /* translators: %s: Frontend dashboard shortcode. */
                                        esc_html__("Put %s shortcode into the page.", 'listdom'),
                                        '<code>[listdom-dashboard]</code>'
                                    ); ?></p>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Form Columns', 'listdom'),
                                    'for' => 'lsd_settings_dashboard_form_columns',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::select([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_settings_dashboard_form_columns',
                                    'name' => 'lsd[dashboard_form_columns]',
                                    'value' => $settings['dashboard_form_columns'] ?? 2,
                                    'options' => [
                                        2 => esc_html__('Two Columns', 'listdom'),
                                        1 => esc_html__('Single Column', 'listdom'),
                                    ],
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Choose how many columns are used in the submission form.', 'listdom'); ?></p>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Menu Sidebar Status', 'listdom'),
                                    'for' => 'lsd_settings_dashboard_menu_sidebar_status',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::select([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_settings_dashboard_menu_sidebar_status',
                                    'name' => 'lsd[dashboard_menu_sidebar_status]',
                                    'value' => $settings['dashboard_menu_sidebar_status'] ?? 'default',
                                    'options' => [
                                        'default' => esc_html__('Default', 'listdom'),
                                        'compact' => esc_html__('Compact', 'listdom'),
                                        'horizontal' => esc_html__('Horizontal', 'listdom'),
                                    ],
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Control how the dashboard navigation menus are displayed.', 'listdom'); ?></p>
                            </div>
                        </div>
                        <div
                            class="lsd-form-row<?php echo (isset($settings['dashboard_menu_sidebar_status']) && $settings['dashboard_menu_sidebar_status'] === 'horizontal') ? '' : ' lsd-util-hide'; ?>"
                            id="lsd_settings_dashboard_menu_sidebar_horizontal_wrapper">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Horizontal Menus', 'listdom'),
                                    'for' => 'lsd_settings_dashboard_menu_sidebar_horizontal_mode',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::select([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_settings_dashboard_menu_sidebar_horizontal_mode',
                                    'name' => 'lsd[dashboard_menu_sidebar_horizontal_mode]',
                                    'value' => $settings['dashboard_menu_sidebar_horizontal_mode'] ?? 'default',
                                    'options' => [
                                        'default' => esc_html__('Default', 'listdom'),
                                        'dropdown' => esc_html__('3 Dots Dropdown', 'listdom'),
                                        'carousel' => esc_html__('Carousel', 'listdom'),
                                    ],
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Choose how horizontal menus are presented.', 'listdom'); ?></p>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Independent Add Listing Form', 'listdom'),
                                    'for' => 'lsd_settings_add_listing_page_status',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_settings_add_listing_page_status',
                                    'value' => $settings['add_listing_page_status'] ?? 0,
                                    'name' => 'lsd[add_listing_page_status]',
                                    'toggle' => '#lsd_settings_add_listing_page_status_options',
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Enable to have a independent add listing page ", 'listdom'); ?></p>
                            </div>
                        </div>
                        <div id="lsd_settings_add_listing_page_status_options"
                             class="<?php echo isset($settings['add_listing_page_status']) && $settings['add_listing_page_status'] ? '' : 'lsd-util-hide'; ?>">
                            <?php if ($this->isLite()): ?>
                                <div class="lsd-alert lsd-warning lsd-mt-4">
                                    <?php echo LSD_Base::missFeatureMessage(esc_html__('Independent Add Listing', 'listdom')); ?>
                                </div>
                            <?php endif; ?>

                            <div class="lsd-form-row">
                                <div class="lsd-col-3"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Add Listing Page', 'listdom'),
                                        'for' => 'lsd_settings_add_listing_page',
                                    ]); ?></div>
                                <div class="lsd-col-5">
                                    <?php echo LSD_Form::pages([
                                        'class' => 'lsd-admin-input',
                                        'id' => 'lsd_settings_add_listing_page',
                                        'value' => $settings['add_listing_page'] ?? null,
                                        'name' => 'lsd[add_listing_page]',
                                        'show_empty' => true,
                                    ]); ?>
                                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo sprintf(
                                        /* translators: %s: Add listing shortcode. */
                                            esc_html__("Put %s shortcode into the page.", 'listdom'),
                                            '<code>[listdom-add-listing]</code>'
                                        ); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lsd-settings-fields-wrapper">
                        <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Listings', 'listdom'); ?></h3>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Listing Status', 'listdom'),
                                    'for' => 'lsd_settings_dashboard_listing_status',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::select([
                                    'class' => 'lsd-admin-input lsd-trigger-select-options',
                                    'id' => 'lsd_settings_dashboard_listing_status',
                                    'name' => 'lsd[dashboard_listing_status]',
                                    'value' => $settings['dashboard_listing_status'] ?? '',
                                    'options' => [
                                        '' => [
                                            'label' => esc_html__('Let Listdom Decide', 'listdom'),
                                            'attributes' => [
                                                'data-lsd-hide' => '#lsd_settings_dashboard_listing_status_guest_row',
                                            ],
                                        ],
                                        'publish' => [
                                            'label' => esc_html__('Published', 'listdom'),
                                            'attributes' => [
                                                'data-lsd-show' => '#lsd_settings_dashboard_listing_status_guest_row',
                                            ],
                                        ],
                                        'pending' => [
                                            'label' => esc_html__('Pending', 'listdom'),
                                            'attributes' => [
                                                'data-lsd-show' => '#lsd_settings_dashboard_listing_status_guest_row',
                                            ],
                                        ],
                                    ],
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Choose the default listing status for new submissions from the frontend dashboard.', 'listdom'); ?></p>
                            </div>
                        </div>
                        <div
                            class="lsd-form-row<?php echo empty($settings['dashboard_listing_status']) ? ' lsd-util-hide' : ''; ?>"
                            id="lsd_settings_dashboard_listing_status_guest_row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Apply to Guest Listings', 'listdom'),
                                    'for' => 'lsd_settings_dashboard_listing_status_guest',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_settings_dashboard_listing_status_guest',
                                    'name' => 'lsd[dashboard_listing_status_guest]',
                                    'value' => $settings['dashboard_listing_status_guest'] ?? 0,
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Enable to apply the selected listing status to guest submissions as well.', 'listdom'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="lsd-settings-fields-wrapper">
                        <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Privacy Consent', 'listdom'); ?></h3>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Enable', 'listdom'),
                                    'for' => 'lsd_submission_pc_enabled',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_submission_pc_enabled',
                                    'name' => 'lsd[submission_pc_enabled]',
                                    'value' => $privacy['submission_pc_enabled'],
                                    'toggle' => '#lsd_submission_pc_label_row',
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('This control also depends on the global privacy consent setting.', 'listdom'); ?></p>
                            </div>
                        </div>
                        <div
                            class="lsd-form-row<?php echo empty($privacy['submission_pc_enabled']) ? ' lsd-util-hide' : ''; ?>"
                            id="lsd_submission_pc_label_row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Label', 'listdom'),
                                    'for' => 'lsd_submission_pc_label',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::text([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_submission_pc_label',
                                    'name' => 'lsd[submission_pc_label]',
                                    'value' => $privacy['submission_pc_label'],
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Leave empty to use the default label. Use {{privacy_policy}} to automatically include the default privacy policy page link.', 'listdom'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="lsd-tab-switcher-content lsd-frontend-dashboard-add-listing-tab-switcher-content<?php echo $add_listing_subtab === 'guest-submission' ? ' lsd-tab-switcher-content-active' : ''; ?>"
                id="lsd-tab-switcher-frontend-dashboard-add-listing-guest-submission-content">
                <div class="lsd-settings-group-wrapper">
                    <div class="lsd-settings-fields-wrapper">

                        <h3 class="lsd-my-0 lsd-admin-title"><?php esc_html_e('Guest Submission', 'listdom'); ?></h3>

                        <?php if ($this->isLite()): ?>
                            <div class="lsd-alert lsd-warning lsd-my-0">
                                <?php echo LSD_Base::missFeatureMessage(esc_html__('Guest Submission', 'listdom')); ?>
                            </div>
                        <?php endif; ?>

                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Status', 'listdom'),
                                    'for' => 'lsd_settings_submission_guest',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_settings_submission_guest',
                                    'value' => $settings['submission_guest'] ?? 0,
                                    'name' => 'lsd[submission_guest]',
                                    'toggle' => '#lsd_settings_submission_guest_options',
                                    'toggle2' => '#lsd_settings_submission_non_guest_options',
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Enable listing submission for guest users!", 'listdom'); ?></p>
                            </div>
                        </div>
                        <div id="lsd_settings_submission_guest_options"
                             class="<?php echo isset($settings['submission_guest']) && $settings['submission_guest'] ? '' : 'lsd-util-hide'; ?>">
                            <div class="lsd-form-row">
                                <div class="lsd-col-3"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('User Registration', 'listdom'),
                                        'for' => 'lsd_settings_submission_guest_registration',
                                    ]); ?></div>
                                <div class="lsd-col-5">
                                    <?php echo LSD_Form::select([
                                        'class' => 'lsd-admin-input',
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
                        <div id="lsd_settings_submission_non_guest_options"
                             class="<?php echo !isset($settings['submission_guest']) || !$settings['submission_guest'] ? '' : 'lsd-util-hide'; ?>">
                            <div class="lsd-form-row">
                                <div class="lsd-col-3"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Redirect to Login', 'listdom'),
                                        'for' => 'lsd_settings_submission_guest_redirect',
                                    ]); ?></div>
                                <div class="lsd-col-5">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_settings_submission_guest_redirect',
                                        'value' => $settings['submission_guest_redirect'] ?? 0,
                                        'name' => 'lsd[submission_guest_redirect]',
                                    ]); ?>
                                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Redirect users to the WordPress login page instead of displaying the default login forms.", 'listdom'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="lsd-tab-switcher-content lsd-frontend-dashboard-add-listing-tab-switcher-content<?php echo $add_listing_subtab === 'fields' ? ' lsd-tab-switcher-content-active' : ''; ?>"
                id="lsd-tab-switcher-frontend-dashboard-add-listing-fields-content">
                <div class="lsd-settings-group-wrapper">
                    <div class="lsd-settings-fields-wrapper">
                        <h3 class="lsd-my-0 lsd-admin-title"><?php esc_html_e('Field Method', 'listdom'); ?></h3>
                        <?php foreach ([LSD_Base::TAX_LOCATION => esc_html__('Locations', 'listdom'), LSD_Base::TAX_FEATURE => esc_html__('Features', 'listdom')] as $tax => $label): ?>
                            <div class="lsd-form-row">
                                <div class="lsd-col-3"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html($label),
                                        'for' => 'lsd_settings_tax_' . $tax . '_method',
                                    ]); ?></div>
                                <div class="lsd-col-5">
                                    <?php echo LSD_Form::select([
                                        'class' => 'lsd-admin-input',
                                        'id' => 'lsd_settings_tax_' . $tax . '_method',
                                        'options' => [
                                            'checkboxes' => esc_html__('Checkboxes', 'listdom'),
                                            'dropdown' => esc_html__('Dropdown', 'listdom'),
                                        ],
                                        'value' => $settings['submission_tax_' . $tax . '_method'] ?? ($tax === LSD_Base::TAX_CATEGORY ? 'dropdown' : 'checkboxes'),
                                        'name' => 'lsd[submission_tax_' . $tax . '_method]',
                                    ]); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (class_exists(\LSDPACCAT\Module::class)): ?>
                            <div class="lsd-form-row">
                                <div class="lsd-col-3"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Additional Categories', 'listdom'),
                                        'for' => 'lsd_settings_tax_additional_' . LSD_Base::TAX_CATEGORY . '_method',
                                    ]); ?></div>
                                <div class="lsd-col-5">
                                    <?php echo LSD_Form::select([
                                        'class' => 'lsd-admin-input',
                                        'id' => 'lsd_settings_tax_additional_' . LSD_Base::TAX_CATEGORY . '_method',
                                        'options' => [
                                            'checkboxes' => esc_html__('Checkboxes', 'listdom'),
                                            'dropdown' => esc_html__('Dropdown', 'listdom'),
                                        ],
                                        'value' => $settings['submission_tax_additional_' . LSD_Base::TAX_CATEGORY . '_method'] ?? 'checkboxes',
                                        'name' => 'lsd[submission_tax_additional_' . LSD_Base::TAX_CATEGORY . '_method]',
                                    ]); ?>
                                </div>
                            </div>
                            <div class="lsd-form-row">
                                <div class="lsd-col-3"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Limit Additional Categories', 'listdom'),
                                        'for' => 'lsd_submission_category_children_only',
                                    ]); ?></div>
                                <div class="lsd-col-5">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_submission_category_children_only',
                                        'name' => 'lsd[submission_category_children_only]',
                                        'value' => $settings['submission_category_children_only'] ?? 0,
                                    ]); ?>
                                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('When enabled, the additional category selectors only show subcategories of the chosen primary category.', 'listdom'); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Tags', 'listdom'),
                                    'for' => 'lsd_settings_tax_' . LSD_Base::TAX_TAG . '_method',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::select([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_settings_tax_' . LSD_Base::TAX_TAG . '_method',
                                    'options' => [
                                        'textarea' => esc_html__('Text Input', 'listdom'),
                                        'checkboxes' => esc_html__('Checkboxes', 'listdom'),
                                        'dropdown' => esc_html__('Dropdown', 'listdom'),
                                    ],
                                    'value' => $settings['submission_tax_' . LSD_Base::TAX_TAG . '_method'] ?? 'textarea',
                                    'name' => 'lsd[submission_tax_' . LSD_Base::TAX_TAG . '_method]',
                                ]); ?>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Gallery Method', 'listdom'),
                                    'for' => 'lsd_settings_submission_gallery_method',
                                ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::select([
                                    'class' => 'lsd-admin-input',
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
                    </div>
                    <div class="lsd-settings-fields-wrapper">
                        <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Contact Fields', 'listdom'); ?></h3>
                        <?php foreach ([
                            'email' => esc_html__('Email', 'listdom'),
                            'phone' => esc_html__('Phone', 'listdom'),
                            'website' => esc_html__('Website', 'listdom'),
                            'contact_address' => esc_html__('Contact Address', 'listdom'),
                        ] as $field_key => $field_label): ?>
                            <div class="lsd-form-row">
                                <div class="lsd-col-3"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html($field_label),
                                        'for' => 'lsd_settings_submission_contact_field_' . esc_attr($field_key),
                                    ]); ?></div>
                                <div class="lsd-col-5">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_settings_submission_contact_field_' . esc_attr($field_key),
                                        'name' => 'lsd[submission_contact_fields][' . esc_attr($field_key) . ']',
                                        'value' => $settings['submission_contact_fields'][$field_key] ?? 1,
                                    ]); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="lsd-settings-fields-wrapper">
                        <div class="lsd-admin-section-heading">
                            <h3 class="lsd-admin-title"><?php esc_html_e('Terms Builder', 'listdom'); ?></h3>
                            <p class="lsd-admin-description lsd-my-0"><?php echo sprintf(
                                /* translators: 1: Category label, 2: Location label, 3: Label label, 4: Tag label, 5: Feature label. */
                                esc_html__('Use the options below to allow users to create %1$s, %2$s, %3$s, %4$s, and %5$s directly from the frontend listing form.', 'listdom'),
                                esc_html(lsd_t_label(LSD_Base::TAX_CATEGORY, 'plural')),
                                esc_html(lsd_t_label(LSD_Base::TAX_LOCATION, 'plural')),
                                esc_html(lsd_t_label(LSD_Base::TAX_LABEL, 'plural')),
                                esc_html(lsd_t_label(LSD_Base::TAX_TAG, 'plural')),
                                esc_html(lsd_t_label(LSD_Base::TAX_FEATURE, 'plural'))
                            ); ?></p>
                        </div>

                        <?php foreach ([
                                           LSD_Base::TAX_CATEGORY => esc_html(lsd_t_label(LSD_Base::TAX_CATEGORY, 'plural')),
                                           LSD_Base::TAX_LOCATION => esc_html(lsd_t_label(LSD_Base::TAX_LOCATION, 'plural')),
                                           LSD_Base::TAX_LABEL => esc_html(lsd_t_label(LSD_Base::TAX_LABEL, 'plural')),
                                           LSD_Base::TAX_TAG => esc_html(lsd_t_label(LSD_Base::TAX_TAG, 'plural')),
                                           LSD_Base::TAX_FEATURE => esc_html(lsd_t_label(LSD_Base::TAX_FEATURE, 'plural'))]
                                       as $tax => $label): ?>
                            <div class="lsd-form-row">
                                <div class="lsd-col-3"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html($label),
                                        'for' => 'lsd_settings_submission_term_builder_' . $tax,
                                    ]); ?></div>
                                <div class="lsd-col-5">
                                    <?php echo LSD_Form::select([
                                        'class' => 'lsd-admin-input',
                                        'id' => 'lsd_settings_submission_term_builder_' . $tax,
                                        'options' => [
                                            'disabled' => esc_html__('Disabled', 'listdom'),
                                            'express' => esc_html__('Express', 'listdom'),
                                            'detailed' => esc_html__('Detailed', 'listdom'),
                                        ],
                                        'value' => $settings['submission_term_builder_' . $tax] ?? 'disabled',
                                        'name' => 'lsd[submission_term_builder_' . $tax . ']',
                                    ]); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="lsd-settings-fields-wrapper">
                        <h3 class="lsd-admin-title"><?php esc_html_e('Required Fields', 'listdom'); ?></h3>
                        <div class="lsd-form-row">
                            <div class="lsd-col-12">
                                <ul class="lsd-boxed-list lsd-my-0">
                                    <?php foreach ($dashboard->fields() as $f => $field): ?>
                                        <li class="lsd-d-inline-block <?php echo isset($field['always_enabled']) && $field['always_enabled'] ? 'lsd-always-enabled' : ''; ?>">
                                            <label>
                                            <span class="lsd-inline-checkbox">
                                                <input type="hidden"
                                                       name="lsd[submission_fields][<?php echo esc_attr($f); ?>][required]"
                                                       value="0">
                                                <input type="checkbox"
                                                       name="lsd[submission_fields][<?php echo esc_attr($f); ?>][required]"
                                                       value="1" <?php echo (isset($settings['submission_fields'][$f]) && $settings['submission_fields'][$f]['required'] == 1) || isset($field['always_enabled']) && $field['always_enabled'] ? 'checked' : ''; ?> <?php echo isset($field['always_enabled']) && $field['always_enabled'] ? 'disabled' : ''; ?>>
                                            </span>
                                                <span><?php echo esc_html($field['label']); ?></span>
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="lsd-tab-switcher-content lsd-frontend-dashboard-add-listing-tab-switcher-content<?php echo $add_listing_subtab === 'restrictions' ? ' lsd-tab-switcher-content-active' : ''; ?>"
                id="lsd-tab-switcher-frontend-dashboard-add-listing-restrictions-content">

                <div class="lsd-settings-group-wrapper">
                    <div class="lsd-settings-fields-wrapper">
                        <h3 class="lsd-my-0 lsd-admin-title"><?php esc_html_e('Listing Content Restrictions', 'listdom'); ?></h3>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Maximum Gallery Images', 'listdom'),
                                    'for' => 'lsd_settings_submission_max_gallery_images',
                                ]); ?></div>
                            <div class="lsd-col-6">
                                <?php echo LSD_Form::number([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_settings_submission_max_gallery_images',
                                    'value' => $settings['submission_max_gallery_images'] ?? '',
                                    'name' => 'lsd[submission_max_gallery_images]',
                                    'attributes' => [
                                        'min' => 0,
                                        'step' => 1,
                                    ],
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Leave it empty for unlimited number of images", 'listdom'); ?></p>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Maximum Image Size Allowed', 'listdom'),
                                    'for' => 'lsd_settings_submission_max_image_upload_size',
                                ]); ?></div>
                            <div class="lsd-col-6">
                                <?php echo LSD_Form::number([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_settings_submission_max_image_upload_size',
                                    'value' => $settings['submission_max_image_upload_size'] ?? '',
                                    'name' => 'lsd[submission_max_image_upload_size]',
                                    'attributes' => [
                                        'min' => 0,
                                        'step' => 10,
                                    ],
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Leave it empty for unlimited size of images. The size is in KB", 'listdom'); ?></p>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Image Aspect Ratio', 'listdom'),
                                    'for' => 'lsd_settings_submission_image_aspect_ratio',
                                ]); ?></div>
                            <div class="lsd-col-6">
                                <?php echo LSD_Form::select([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_settings_submission_image_aspect_ratio',
                                    'options' => [
                                        '' => esc_html__('No restriction', 'listdom'),
                                        '16:9' => esc_html__('16:9', 'listdom'),
                                        '4:3' => esc_html__('4:3', 'listdom'),
                                        '3:2' => esc_html__('3:2', 'listdom'),
                                        '1:1' => esc_html__('1:1', 'listdom'),
                                        '2:3' => esc_html__('2:3', 'listdom'),
                                        '3:4' => esc_html__('3:4', 'listdom'),
                                        '9:16' => esc_html__('9:16', 'listdom'),
                                    ],
                                    'value' => $settings['submission_image_aspect_ratio'] ?? '',
                                    'name' => 'lsd[submission_image_aspect_ratio]',
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Applied to featured, gallery, and custom field images.", 'listdom'); ?></p>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Maximum Description Length', 'listdom'),
                                    'for' => 'lsd_settings_submission_max_description_length',
                                ]); ?></div>
                            <div class="lsd-col-6">
                                <?php echo LSD_Form::number([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_settings_submission_max_description_length',
                                    'value' => $settings['submission_max_description_length'] ?? '',
                                    'name' => 'lsd[submission_max_description_length]',
                                    'attributes' => [
                                        'min' => 0,
                                        'step' => 10,
                                    ],
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Leave it empty for unlimited length", 'listdom'); ?></p>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Maximum Number of Tags', 'listdom'),
                                    'for' => 'lsd_settings_submission_max_tags_count',
                                ]); ?></div>
                            <div class="lsd-col-6">
                                <?php echo LSD_Form::number([
                                    'class' => 'lsd-admin-input',
                                    'id' => 'lsd_settings_submission_max_tags_count',
                                    'value' => $settings['submission_max_tags_count'] ?? '',
                                    'name' => 'lsd[submission_max_tags_count]',
                                    'attributes' => [
                                        'min' => 0,
                                        'step' => 1,
                                    ],
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Leave it empty for unlimited number of tags", 'listdom'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="lsd-settings-fields-wrapper">
                        <h3 class="lsd-admin-title"><?php esc_html_e('Modules', 'listdom'); ?></h3>
                        <?php foreach ($dashboard->modules() as $module): ?>
                            <div class="lsd-form-row">
                                <div class="lsd-col-3"><?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => $module['label'],
                                    ]); ?></div>
                                <div class="lsd-col-6">
                                    <div class="lsd-radio-toggle">
                                        <input type="radio"
                                               name="lsd[submission_module][<?php echo esc_attr($module['key']); ?>]"
                                               value="1"
                                               id="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_1" <?php echo isset($settings['submission_module'][$module['key']]) && $settings['submission_module'][$module['key']] == 1 ? 'checked="checked"' : ''; ?>>
                                        <label
                                            for="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_1"><?php esc_html_e('Enabled', 'listdom'); ?></label>
                                        <input type="radio"
                                               name="lsd[submission_module][<?php echo esc_attr($module['key']); ?>]"
                                               value="2"
                                               id="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_2" <?php echo isset($settings['submission_module'][$module['key']]) && $settings['submission_module'][$module['key']] == 2 ? 'checked="checked"' : ''; ?>>
                                        <label
                                            for="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_2"><?php esc_html_e('Editor + Admin', 'listdom'); ?></label>
                                        <input type="radio"
                                               name="lsd[submission_module][<?php echo esc_attr($module['key']); ?>]"
                                               value="0"
                                               id="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_0" <?php echo isset($settings['submission_module']) && (!isset($settings['submission_module'][$module['key']]) || !$settings['submission_module'][$module['key']]) ? 'checked="checked"' : ''; ?>>
                                        <label
                                            for="lsd_settings_submission_module_<?php echo esc_attr($module['key']); ?>_0"><?php esc_html_e('Disabled', 'listdom'); ?></label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="lsd-spacer-30"></div>
        <div class="lsd-form-row lsd-settings-submit-wrapper">
            <div class="lsd-col-12 lsd-flex lsd-gap-3 lsd-flex-content-end">
                <?php LSD_Form::nonce('lsd_settings_form'); ?>
                <button type="submit" id="lsd_settings_save_button" class="lsd-primary-button">
                    <?php esc_html_e('Save The Changes', 'listdom'); ?>
                    <i class='wbli wbli-checkmark-circle'></i>
                </button>
            </div>
        </div>
    </form>
</div>
<script>
    jQuery(function ($)
    {
        const $sidebarStatus = $('#lsd_settings_dashboard_menu_sidebar_status');
        const $horizontalWrapper = $('#lsd_settings_dashboard_menu_sidebar_horizontal_wrapper');

        function listdomToggleDashboardSidebarOptions()
        {
            if ($sidebarStatus.val() === 'horizontal') $horizontalWrapper.removeClass('lsd-util-hide');
            else $horizontalWrapper.addClass('lsd-util-hide');
        }

        $sidebarStatus.on('change', listdomToggleDashboardSidebarOptions);
        listdomToggleDashboardSidebarOptions();
    });

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

        if (hasDuplicateError)
        {
            return false;
        }

        jQuery('.lsd-custom-menu-content textarea').each(function ()
        {
            const uniqueId = jQuery(this).attr('id');
            const content = tinymce.editors[uniqueId].getContent();

            jQuery(this).val(content);
        });

        // Elements
        const $button = jQuery("#lsd_settings_save_button");
        const $tab = jQuery('.lsd-nav-tab-active');

        // Loading Wrapper
        const loading = new ListdomButtonLoader($button);
        loading.start("<?php echo esc_js(esc_html__('Saving', 'listdom')); ?>");

        const settings = jQuery(this).serialize();
        jQuery.ajax(
            {
                type: "POST",
                url: ajaxurl,
                data: "action=lsd_save_dashboard&" + settings,
                success: function ()
                {
                    $tab.attr('data-saved', 'true');

                    listdom_toastify("<?php echo esc_js(esc_html__('Options saved successfully.', 'listdom')); ?>", 'lsd-success');

                    // Unloading
                    loading.stop();
                },
                error: function ()
                {
                    $tab.attr('data-saved', 'false');

                    listdom_toastify("<?php echo esc_js(esc_html__('Error: Unable to save options.', 'listdom')); ?>", 'lsd-error');

                    // Unloading
                    loading.stop();
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
