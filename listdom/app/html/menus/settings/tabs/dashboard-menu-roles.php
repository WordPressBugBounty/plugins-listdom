<?php
defined('ABSPATH') || die();
?>
<fieldset class="lsd-field-group lsd-dashboard-menu-role-settings">
    <legend class="lsd-fields-label lsd-fields-label-tiny"><?php esc_html_e('Allowed roles', 'listdom'); ?></legend>
    <div class="lsd-user-field-role-list">
        <?php foreach (LSD_Dashboard_Access::role_options() as $role => $role_name): ?>
            <label class="lsd-fields-label lsd-user-field-checkbox">
                <input type="checkbox" name="<?php echo esc_attr($roles_name); ?>" data-field="roles" value="<?php echo esc_attr($role); ?>" <?php checked(in_array($role, $menu_roles, true)); ?>>
                <span><?php echo esc_html(translate_user_role($role_name)); ?></span>
            </label>
        <?php endforeach; ?>
    </div>
    <p class="lsd-admin-description-tiny lsd-m-0"><?php esc_html_e('Select who can access this menu. Leave all unchecked to allow all roles. The login setting still applies.', 'listdom'); ?></p>
</fieldset>
