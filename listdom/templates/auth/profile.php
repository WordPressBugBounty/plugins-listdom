<?php
// no direct access
defined('ABSPATH') || die();

// User is not Logged-in
if(!is_user_logged_in()) return '';

$user = LSD_User::get_user_info();
$auth = LSD_Options::auth();

// Dashboard Page
$dashboard_page = LSD_Options::post_id('submission_page');

$logout_link = isset($auth['logout']['redirect']) ? get_permalink($auth['logout']['redirect']) : false;
$logout_redirect = $logout_link ?: home_url();

$account_link = isset($auth['account']['redirect']) ? get_permalink($auth['account']['redirect']) : false;
$account_redirect = $account_link ?: ($dashboard_page ? get_page_link($dashboard_page) : home_url());
?>
<div class="lsd-logged-in-wrapper">
    <div class="lsd-user-avatar">
        <?php echo LSD_User::get_user_avatar(70); ?>
        <span class="lsd-user-display-name">
            <?php echo sprintf(esc_html__('Hi %s 👋', 'listdom'), $user['display_name'] ?? ''); ?>
        </span>
    </div>
    <div class="lsd-logged-in-actions">
        <a href="<?php echo esc_url_raw($account_redirect); ?>"><?php esc_html_e('My Account', 'listdom'); ?></a>
        <a href="<?php echo wp_logout_url($logout_redirect); ?>"><?php esc_html_e('Logout', 'listdom'); ?></a>
    </div>
</div>
