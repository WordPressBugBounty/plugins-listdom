<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $id */
/** @var string $button_label */
/** @var bool $hover */
/** @var string $align */
/** @var array $role_tabs */
/** @var string $redirect */
/** @var bool $explicit_redirect */
/** @var string $account_redirect */
/** @var string $logout_redirect */
/** @var string $form_type */
/** @var array $attr */

$is_logged_in = is_user_logged_in();
$user_info = LSD_User::get_user_info();
$avatar = $is_logged_in ? LSD_User::get_user_avatar('', 40) : '';
$panel_id = $id . '-panel';
$wrapper_classes = [
    'lsd-auth-dropdown',
    'lsd-auth-dropdown-align-'.$align,
];
?>
<div class="<?php echo esc_attr(implode(' ', array_filter($wrapper_classes))); ?>" id="<?php echo esc_attr($id); ?>" data-hover="<?php echo $hover ? 1 : 0; ?>">
    <button type="button" class="lsd-auth-dropdown-toggle" aria-label="<?php echo esc_attr($is_logged_in ? ($user_info['display_name'] ?? $button_label) : $button_label); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr($panel_id); ?>">
        <span class="lsd-auth-dropdown-toggle-content">
            <?php if ($is_logged_in && $avatar): ?>
                <span class="lsd-auth-dropdown-toggle-avatar"><?php echo wp_kses_post($avatar); ?></span>
            <?php else: ?>
                <span class="lsd-auth-dropdown-toggle-icon lsd-icon-user" aria-hidden="true"></span>
            <?php endif; ?>
            <span class="lsd-auth-dropdown-toggle-label">
                <?php echo esc_html($is_logged_in ? ($user_info['display_name'] ?? $button_label) : $button_label); ?>
            </span>
        </span>
        <span class="lsd-auth-dropdown-caret" aria-hidden="true">
            <i class="fa-solid fa-chevron-down"></i>
        </span>
    </button>
    <div class="lsd-auth-dropdown-panel" id="<?php echo esc_attr($panel_id); ?>" aria-hidden="true">
        <?php if ($is_logged_in): ?>
            <div class="lsd-auth-dropdown-logged-in">
                <div class="lsd-auth-dropdown-user">
                    <div class="lsd-auth-dropdown-avatar">
                        <?php echo $avatar ? wp_kses_post($avatar) : ''; ?>
                    </div>
                    <div class="lsd-auth-dropdown-user-meta">
                        <div class="lsd-auth-dropdown-name">
                            <?php echo esc_html($user_info['display_name'] ?? ''); ?>
                        </div>
                        <?php if (!empty($user_info['email'])): ?>
                            <div class="lsd-auth-dropdown-email"><?php echo esc_html($user_info['email']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="lsd-auth-dropdown-actions">
                    <a href="<?php echo esc_url($account_redirect); ?>" class="lsd-auth-dropdown-link lsd-general-button"><?php esc_html_e('My Account', 'listdom'); ?></a>
                    <a href="<?php echo esc_url(wp_logout_url($logout_redirect)); ?>" class="lsd-auth-dropdown-link lsd-auth-dropdown-link-danger lsd-text-button"><?php esc_html_e('Logout', 'listdom'); ?></a>
                </div>
            </div>
        <?php else: ?>
            <?php include lsd_template('auth/role-tabs.php'); ?>
        <?php endif; ?>
    </div>
</div>
