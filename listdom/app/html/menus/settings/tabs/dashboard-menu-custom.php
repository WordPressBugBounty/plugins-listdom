<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $menu */
/** @var string $slug */
/** @var bool $new */
$label = $menu['label'] ?? '';
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
            <span class="lsd-custom-menu-label"><?php echo esc_html($new ? __('Label', 'listdom') : $label); ?></span>
        </span>
        <span class="lsd-menu-actions">
            <i class="fas fa-trash"></i>
            <button type="button" class="lsd-dashboard-menu-toggle"
                    aria-pressed="<?php echo $enabled ? 'true' : 'false'; ?>"><i
                    class="fas <?php echo esc_attr($toggle_icon); ?>"></i></button>
            <i class="fas <?php echo $new ? 'fa-chevron-up' : 'fa-chevron-down'; ?> lsd-dashboard-menu-chevron"></i>
        </span>
    </p>
    <div class="lsd-custom-menu-content lsd-dashboard-menu-content<?php echo $new ? '' : ' lsd-util-hide'; ?>">
        <?php $base_id = 'lsd_menu_' . esc_attr($slug); ?>
        <div class="lsd-dashboard-menu-section">
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
                >
                <p class="lsd-admin-description-tiny">
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
                    value="<?php echo esc_attr($new ? '' : $slug); ?>"
                    data-field="slug"
                >
                <p class="lsd-admin-description-tiny">
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
                <p class="lsd-admin-description-tiny">
                    <?php esc_html_e('Select an icon.', 'listdom'); ?>
                </p>
            </div>
        </div>
        <div class="lsd-dashboard-menu-section">
            <h4 class="lsd-dashboard-menu-section-title"><?php esc_html_e('Access', 'listdom'); ?></h4>
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
                <p class="lsd-admin-description-tiny">
                    <?php esc_html_e('Choose whether this custom menu requires the user to be logged in.', 'listdom'); ?>
                </p>
            </div>

            <?php
            $menu_roles = LSD_Dashboard_Access::keys($menu['roles'] ?? []);
            $roles_name = 'lsd[dashboard_menu_custom][' . $slug . '][roles][]';
            include __DIR__ . '/dashboard-menu-roles.php';
            ?>
            <?php
            $membership_entitlement = sanitize_key($menu['entitlement'] ?? '');
            $membership_name = 'lsd[dashboard_menu_custom][' . $slug . '][entitlement]';
            include __DIR__ . '/dashboard-menu-membership.php';
            ?>
        </div>
        <div class="lsd-dashboard-menu-section">
            <h4 class="lsd-dashboard-menu-section-title"><?php esc_html_e('Content', 'listdom'); ?></h4>
            <div class="lsd-field-group">
                <label class="screen-reader-text"
                       for="<?php echo $base_id; ?>_content">
                    <?php esc_html_e('Content', 'listdom'); ?>
                </label>
                <?php echo LSD_Form::editor([
                    'id' => $base_id . '_content',
                    'name' => 'lsd[dashboard_menu_custom][' . esc_attr($slug) . '][content]',
                    'value' => $menu['content'] ?? '',
                    'editor_class' => 'lsd-dashboard-menu-editor',
                ]); ?>
                <p class="lsd-admin-description-tiny">
                    <?php esc_html_e('Type dashboard content. You can also use shortcodes.', 'listdom'); ?>
                </p>
            </div>
        </div>
    </div>

    <input type="hidden" name="lsd[dashboard_menus][]" class="custom-menu-slug"
           value="<?php echo esc_attr($slug); ?>">
</li>
