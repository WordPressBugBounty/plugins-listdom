<?php
// no direct access
defined('ABSPATH') || die();

// User is Already Logged-in
if(is_user_logged_in()) return '';

// Auth Settings
$auth = LSD_Options::auth();
?>
<div class="lsd-auth-wrapper">
    <?php if(in_array($auth['auth']['switch_style'], ['tabs', 'both'])): ?>
        <div class="lsd-auth-switcher-buttons">
            <?php if(!$auth['auth']['hide_login_form']): ?>
                <button class="lsd-auth-switch-button" data-target="#lsd-login-form"><?php echo esc_html($auth['auth']['login_tab_label']); ?></button>
            <?php endif;?>
            <?php if(!$auth['auth']['hide_register_form']): ?>
                <button class="lsd-auth-switch-button" data-target="#lsd-register-form"><?php echo esc_html($auth['auth']['register_tab_label']); ?></button>
            <?php endif;?>
            <?php if(!$auth['auth']['hide_forgot_password_form']): ?>
                <button class="lsd-auth-switch-button" data-target="#lsd-forgot-password-form"><?php echo esc_html($auth['auth']['forgot_password_tab_label']); ?></button>
            <?php endif;?>
        </div>
    <?php endif; ?>

    <div class="lsd-auth-form-container">
        <?php if(!$auth['auth']['hide_login_form']) : ?>
            <div id="lsd-login-form" class="lsd-auth-form-content">
                <?php echo do_shortcode('[listdom-login]'); ?>
                <?php if(in_array($auth['auth']['switch_style'], ['links', 'both'])): ?>
                    <div class="lsd-auth-switcher-links">
                        <?php if(!$auth['auth']['hide_register_form']) : ?>
                            <span class="lsd-auth-switch-button" data-target="#lsd-register-form"><?php echo esc_html($auth['auth']['register_link_label']); ?></span>
                        <?php endif; ?>
                        <?php if(!$auth['auth']['hide_forgot_password_form']) : ?>
                            <span class="lsd-auth-switch-button" data-target="#lsd-forgot-password-form"><?php echo esc_html($auth['auth']['forgot_password_link_label']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif;?>
            </div>
        <?php endif; ?>

        <?php if(!$auth['auth']['hide_register_form']) : ?>
            <div id="lsd-register-form" class="lsd-auth-form-content">
                <?php echo do_shortcode('[listdom-register]'); ?>
                <?php if(in_array($auth['auth']['switch_style'], ['links', 'both'])): ?>
                    <div class="lsd-auth-switcher-links">
                        <?php if(!$auth['auth']['hide_login_form']) : ?>
                            <span class="lsd-auth-switch-button" data-target="#lsd-login-form"><?php echo esc_html($auth['auth']['login_link_label']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif;?>

            </div>
        <?php endif; ?>

        <?php if(!$auth['auth']['hide_forgot_password_form']) : ?>
            <div id="lsd-forgot-password-form" class="lsd-auth-form-content">
                <?php echo do_shortcode('[listdom-forgot-password]'); ?>
                <?php if(in_array($auth['auth']['switch_style'], ['links', 'both'])): ?>
                    <div class="lsd-auth-switcher-links">
                        <?php if(!$auth['auth']['hide_register_form']) : ?>
                            <span class="lsd-auth-switch-button" data-target="#lsd-register-form"><?php echo esc_html($auth['auth']['register_link_label']); ?></span>
                        <?php endif; ?>
                        <?php if(!$auth['auth']['hide_login_form']) : ?>
                            <span class="lsd-auth-switch-button" data-target="#lsd-login-form"><?php echo esc_html($auth['auth']['login_link_label']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif;?>
            </div>
        <?php endif; ?>
    </div>
</div>
