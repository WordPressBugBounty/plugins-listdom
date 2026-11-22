<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $redirect */
/** @var string $role */
/** @var bool $explicit_redirect */

// User is Already Logged-in
if (is_user_logged_in()) return '';

$instance_id = uniqid('lsd-login-');
$auth        = LSD_Options::auth();
$role_signature = trim($role) ? $this->role_signature('login', $role) : '';

if (!trim($redirect))
{
    $redirect = $this->page_url($auth['login']['redirect'] ?? 0);
    if (!trim($redirect)) $redirect = home_url();
}

// Verification notice
$verification_notice = '';
$verification_notice_class = 'lsd-success';

if (isset($_GET['lsd-verified']))
{
    $verified_status = sanitize_text_field(wp_unslash($_GET['lsd-verified']));

    if ($verified_status === '1')
    {
        $verification_notice = LSD_User::verification_success_message();
        $verification_notice_class = 'lsd-success';
    }
    else
    {
        $verification_notice = LSD_User::verification_failed_message();
        $verification_notice_class = 'lsd-error';
    }
}

$form_id = 'lsd-login-' . $instance_id;
$forgot_target = sanitize_html_class($atts['forgot_target'] ?? '');
$standalone = empty($atts['auth_switcher']);
$forgot_url = $standalone && LSD_Portal::is_page('login') && !LSD_Portal::is_page('forgot') ? LSD_Portal::url('forgot') : '';
$register_url = $standalone && LSD_Portal::is_page('login') && !LSD_Portal::is_page('register') && LSD_Security::registration_allowed() ? LSD_Portal::url('register', $explicit_redirect ? $redirect : '') : '';

$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#' . esc_js($form_id) . '").listdomLoginForm(
    {
        ajax_url: "' . admin_url('admin-ajax.php') . '",
        nonce: "' . wp_create_nonce('lsd_login') . '",
        resend_nonce: "' . wp_create_nonce('lsd_resend_verification') . '",
        can_resend: ' . (LSD_User::requires_email_verification() ? 'true' : 'false') . ',
        username_placeholder: "' . esc_js($auth["login"]["username_placeholder"]) . '",
        password_placeholder: "' . esc_js($auth["login"]["password_placeholder"]) . '"
    });
});
</script>');
?>
<div class="lsd-login-wrapper" id="<?php echo esc_attr('lsd_login_wrapper_' . $instance_id); ?>">
    <?php if (trim($verification_notice)): ?>
        <div class="lsd-alert <?php echo esc_attr($verification_notice_class); ?>">
            <?php echo esc_html($verification_notice); ?>
        </div>
    <?php endif; ?>

    <div id="<?php echo esc_attr('lsd_login_form_message_' . $instance_id); ?>" class="lsd-login-form-message" role="status"></div>

    <?php if (LSD_User::requires_email_verification()): ?>
        <div class="lsd-login-resend-wrapper lsd-util-hide">
            <p class="lsd-m-0 lsd-mb-2"><?php esc_html_e("Didn't receive the verification email? Verify your username or email, then request a new link.", 'listdom'); ?></p>
            <button type="button" id="<?php echo esc_attr('lsd-resend-verification-' . $instance_id); ?>" class="lsd-solid-button lsd-resend-verification" disabled>
                <?php esc_html_e('Resend verification email', 'listdom'); ?>
            </button>
        </div>
    <?php endif; ?>

    <form id="<?php echo esc_attr($form_id); ?>" method="post">
        <div class="login-form-content">
            <?php
            wp_login_form([
                'redirect'             => $redirect,
                'form_id'              => $form_id . '-inner',
                'label_username'       => $auth['login']['username_label'],
                'label_password'       => $auth['login']['password_label'],
                'label_remember'       => $auth['login']['remember_label'],
                'label_log_in'         => $auth['login']['submit_label'],
                'remember'             => true,
                'placeholder_username' => $auth['login']['username_placeholder'],
                'placeholder_password' => $auth['login']['password_placeholder'],
                'id_username'          => $form_id . '-user-login',
                'id_password'          => $form_id . '-user-pass',
                'id_submit'            => $form_id . '-submit',
            ]); ?>

            <?php LSD_Form::nonce('lsd_login', 'lsd_login'); ?>
            <?php if (LSD_User::requires_email_verification()) LSD_Form::nonce('lsd_resend_verification', 'lsd_resend_verification'); ?>
            <?php
            if ($explicit_redirect && trim($redirect) !== '')
            {
                echo LSD_Form::hidden([
                    'name' => 'lsd_explicit_redirect',
                    'id' => 'lsd_explicit_redirect',
                    'value' => '1',
                ]);
            }

            if (trim($role) && $role_signature)
            {
                echo LSD_Form::hidden([
                    'name' => 'lsd_role',
                    'id' => 'lsd_role',
                    'value' => $role,
                ]);
                echo LSD_Form::hidden([
                    'name' => 'lsd_role_signature',
                    'id' => 'lsd_role_signature',
                    'value' => $role_signature,
                ]);
            }
            ?>
        </div>
    </form>
    <?php if ($forgot_target !== ''): ?>
        <button type="button" class="lsd-login-recovery lsd-auth-switch-button lsd-text-button" data-target="#<?php echo esc_attr($forgot_target); ?>"><?php echo esc_html($auth['auth']['forgot_password_link_label']); ?></button>
    <?php elseif ($forgot_url !== ''): ?>
        <a class="lsd-login-recovery lsd-text-button" href="<?php echo esc_url($forgot_url); ?>"><?php esc_html_e('Forgot your password?', 'listdom'); ?></a>
    <?php endif; ?>
    <?php echo LSD_Auth::methods('login', $role, $redirect, $explicit_redirect); ?>
    <?php if ($register_url !== ''): ?>
        <div class="lsd-auth-switcher-links">
            <a href="<?php echo esc_url($register_url); ?>"><?php esc_html_e('Create an account', 'listdom'); ?></a>
        </div>
    <?php endif; ?>
</div>
