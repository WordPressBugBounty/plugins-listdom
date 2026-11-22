<?php
defined('ABSPATH') || die();
?>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Block Admin Access', 'listdom'); ?></h3>
    <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('You can block WordPress admin access for the following user roles, if needed. Check to block access, or uncheck to allow it.', 'listdom'); ?></p>

    <?php foreach (LSD_Roles::supported() as $role => $label): ?>
        <div class="lsd-form-row lsd-my-0">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'for' => 'lsd_block_admin_role_'. $role,
                'title' => $label
            ]); ?></div>
            <div class="lsd-col-9"><?php echo LSD_Form::switcher([
                'id' => 'lsd_block_admin_role_'. $role,
                'name' => 'settings[block_admin_'. $role.']',
                'value' => $settings['block_admin_'. $role] ?? 1
            ]); ?></div>
        </div>
    <?php endforeach; ?>
</div>
