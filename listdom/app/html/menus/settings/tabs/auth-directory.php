<?php
defined('ABSPATH') || die();
?>
<p class="lsd-admin-description"><?php esc_html_e('The directory follows the profile visibility setting. Private profiles hide the user directory.', 'listdom'); ?> <a href="<?php echo esc_url(LSD_Portal::admin_url('profiles-directory', 'profiles')); ?>"><?php esc_html_e('Manage profile visibility', 'listdom'); ?></a></p>
    <div class="lsd-settings-fields-wrapper">
    <p class="lsd-admin-description lsd-m-0"><?php echo sprintf(
        /* translators: %s: Users shortcode. */
        esc_html__("Insert the %s shortcode on any page to display a user directory. Only users with the selected roles appear, including accounts without listings. The shortcode supports both List and Grid styles. Feel free to use one of the following shortcodes:", 'listdom'),
        '<code>[listdom-users]</code>'
    ); ?></p>
    <ul class="lsd-m-0">
        <li>[listdom-users style="list" limit="24"]</li>
        <li>[listdom-users style="grid" limit="12" columns="3"]</li>
    </ul>
    <fieldset class="lsd-field-group">
        <legend class="lsd-fields-label"><?php esc_html_e('Directory roles', 'listdom'); ?></legend>
        <input type="hidden" name="lsd[profile][directory_roles][]" value="">
        <div class="lsd-user-field-role-list">
            <?php foreach (LSD_Dashboard_Access::role_options() as $role => $role_name): ?>
                <label class="lsd-fields-label lsd-user-field-checkbox">
                    <input type="checkbox" name="lsd[profile][directory_roles][]" value="<?php echo esc_attr($role); ?>" <?php checked(in_array($role, LSD_Portal::directory_roles(), true)); ?>>
                    <span><?php echo esc_html(translate_user_role($role_name)); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <p class="lsd-admin-description-tiny"><?php esc_html_e('Ordinary customer accounts are excluded until you select their roles. Selecting no roles hides all directory entries. Public profile and field visibility settings still apply.', 'listdom'); ?></p>
    </fieldset>
</div>
