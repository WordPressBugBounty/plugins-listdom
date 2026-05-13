<?php
// no direct access
defined('ABSPATH') || die();

// User is Already Logged-in
if (is_user_logged_in()) return '';

$instance_id = uniqid('lsd-forgot-');

$auth = LSD_Options::auth();

$redirect_link = isset($auth['forgot_password']['redirect']) ? get_permalink($auth['forgot_password']['redirect']) : false;
$redirect = $redirect_link ?: home_url();

$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$reset_key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
$login = isset($_GET['login']) ? sanitize_user($_GET['login']) : '';

$show_reset = in_array($action, ['rp', 'resetpass'], true) && $reset_key && $login;
$reset_error = '';

if ($show_reset)
{
    $user = check_password_reset_key($reset_key, $login);
    if ($user instanceof WP_Error)
    {
        $show_reset = false;
        $reset_error = esc_html__('This password reset link is invalid or has expired.', 'listdom');
    }
}

$assets = new LSD_Assets();

if ($show_reset)
{
    $assets->footer('<script>
    jQuery(document).ready(function()
    {
        jQuery("#' . esc_js('lsd-reset-password-' . $instance_id) . '").listdomResetPasswordForm(
        {
            ajax_url: "' . admin_url('admin-ajax.php', null) . '",
            nonce: "' . wp_create_nonce('lsd_reset_password_nonce') . '"
        });
    });
    </script>');
}
else
{
    if ($reset_error) echo '<div class="lsd-alert lsd-error">' . esc_html($reset_error) . '</div>';

    $assets->footer('<script>
    jQuery(document).ready(function()
    {
        jQuery("#' . esc_js('lsd-forgot-password-' . $instance_id) . '").listdomForgotPasswordForm(
        {
            ajax_url: "' . admin_url('admin-ajax.php', null) . '",
            nonce: "' . wp_create_nonce('lsd_forgot_password_nonce') . '"
        });
    });
    </script>');
}
?>
<?php if ($show_reset): ?>
    <div class="lsd-reset-password-wrapper">
        <div id="<?php echo esc_attr('lsd_reset_password_form_message_' . $instance_id); ?>" class="lsd-reset-password-form-message"></div>
        <form id="<?php echo esc_attr('lsd-reset-password-' . $instance_id); ?>" class="lsd-reset-password-form" method="post">
            <?php LSD_Form::nonce('lsd_reset_password_nonce', 'lsd_reset_password_nonce'); ?>
            <?php
            echo LSD_Form::hidden([
                'name' => 'user_login',
                'id' => 'lsd_reset_password_login_' . $instance_id,
                'value' => esc_attr($login),
            ]);
            echo LSD_Form::hidden([
                'name' => 'reset_key',
                'id' => 'lsd_reset_password_key_' . $instance_id,
                'value' => esc_attr($reset_key),
            ]);
            ?>
            <div class="form-group">
                <?php
                echo LSD_Form::label([
                    'for' => 'lsd_new_password_' . $instance_id,
                    'title' => esc_html__('New Password', 'listdom')
                ]);
                echo LSD_Form::input([
                    'name' => 'password',
                    'id' => 'lsd_new_password_' . $instance_id,
                    'value' => '',
                    'required' => true,
                    'placeholder' => esc_attr__('Enter your new password', 'listdom')
                ], 'password');
                ?>
            </div>
            <div class="form-group">
                <?php
                echo LSD_Form::label([
                    'for' => 'lsd_confirm_password_' . $instance_id,
                    'title' => esc_html__('Confirm Password', 'listdom')
                ]);
                echo LSD_Form::input([
                    'name' => 'password_confirmation',
                    'id' => 'lsd_confirm_password_' . $instance_id,
                    'value' => '',
                    'required' => true,
                    'placeholder' => esc_attr__('Confirm your new password', 'listdom')
                ], 'password');
                ?>
            </div>
            <div class="form-group">
                <?php
                echo LSD_Form::submit([
                    'class' => 'lsd-general-button',
                    'id' => 'lsd_reset_password_submit_' . $instance_id,
                    'label' => esc_html__('Change Password', 'listdom'),
                ]);
                ?>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="lsd-forgot-password-wrapper">
        <div id="<?php echo esc_attr('lsd_forgot_password_form_message_' . $instance_id); ?>" class="lsd-forgot-password-form-message"></div>
        <form name="lsd-forgot-password" id="<?php echo esc_attr('lsd-forgot-password-' . $instance_id); ?>" class="lsd-forgot-password-form" method="post">
            <?php LSD_Form::nonce('lsd_forgot_password_nonce', 'lsd_forgot_password_nonce'); ?>
            <div class="form-group">
                <?php
                echo LSD_Form::label([
                    'for' => 'lsd_forgot_password_' . $instance_id,
                    'title' => $auth['forgot_password']['email_label']
                ]);
                echo LSD_Form::text([
                    'name' => 'user_login',
                    'id' => 'lsd_forgot_password_' . $instance_id,
                    'value' => isset($_POST['user_login']) ? sanitize_text_field(wp_unslash($_POST['user_login'])) : '',
                    'required' => true,
                    'placeholder' => $auth['forgot_password']['email_placeholder'],
                    'attributes' => [
                        'autocomplete' => 'username',
                    ],
                ]);
                ?>
            </div>
            <div class="form-group">
                <?php
                echo LSD_Form::submit([
                    'class' => 'lsd-general-button',
                    'id' => 'lsd_forgot_password_submit_' . $instance_id,
                    'label' => $auth['forgot_password']['submit_label'],
                ]);
                ?>
            </div>
        </form>
    </div>
<?php endif; ?>
