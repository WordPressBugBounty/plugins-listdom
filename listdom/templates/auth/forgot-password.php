<?php
// no direct access
defined('ABSPATH') || die();

// User is Already Logged-in
if(is_user_logged_in()) return '';

$auth = LSD_Options::auth();

$redirect_link = isset($auth['forgot_password']['redirect']) ? get_permalink($auth['forgot_password']['redirect']) : false;
$redirect = $redirect_link ?: home_url();
$email_label = $auth['forgot_password']['email_label'];
$email_placeholder = $auth['forgot_password']['email_placeholder'] ;
$submit_label = $auth['forgot_password']['submit_label'] ;

$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_forgot_password").listdomForgotPasswordForm(
    {
        ajax_url: "' . admin_url('admin-ajax.php', null) . '",
        nonce: "' . wp_create_nonce('lsd_forgot_password_nonce') . '"
    });
});
</script>');
?>
<div class="lsd-forgot-password-wrapper">
    <div id="lsd_forgot_password_form_message"></div>
    <form name="lsd-forgot-password" id="lsd-forgot-password" method="post">
        <?php LSD_Form::nonce('lsd_forgot_password_nonce', 'lsd_forgot_password_nonce'); ?>
        <div class="form-group">
            <?php
                echo LSD_Form::label([
                    'for' => 'lsd_forgot_password',
                    'title' => $email_label
                ]);
                echo LSD_Form::email([
                    'name' => 'user_login',
                    'id' => 'lsd_forgot_password',
                    'value' => isset($_POST['user_login']) ? esc_attr($_POST['user_login']) : '',
                    'required' => true,
                    'placeholder' => $email_placeholder
                ]);
            ?>
        </div>
        <div class="form-group">
            <?php
                echo LSD_Form::submit([
                    'id' => 'lsd_forgot_password_submit',
                    'label' => $submit_label,
                ]);
            ?>
        </div>
    </form>
</div>
