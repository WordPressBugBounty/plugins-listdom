<?php
// no direct access
defined('ABSPATH') || die();

// User is Already Logged-in
if(is_user_logged_in()) return '';

$auth = LSD_Options::auth();

$login_redirect_link = isset($auth['login']['redirect']) ? get_permalink($auth['login']['redirect']) : false;
$redirect = $login_redirect_link ?: home_url();
$username_label = $auth['login']['username_label'];
$username_placeholder = $auth['login']['username_placeholder'];
$password_label = $auth['login']['password_label'];
$password_placeholder = $auth['login']['password_placeholder'];
$remember_label = $auth['login']['remember_label'];
$login_submit_label = $auth['login']['submit_label'];

$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd-login-form").listdomLoginForm({
        ajax_url: "' . admin_url('admin-ajax.php') . '",
        nonce: "' . wp_create_nonce('lsd_login') . '"
    });
    
    // Adding placeholders via jQuery
    jQuery("#lsd-login-form #user_login").attr("placeholder", "' . $username_placeholder . '");
    jQuery("#lsd-login-form #user_pass").attr("placeholder", "' . $password_placeholder . '");
});
</script>');

$args = [
    'redirect' => $redirect,
    'form_id' => 'lsd-login',
    'label_username' => $username_label,
    'label_password' => $password_label,
    'label_remember' => $remember_label,
    'label_log_in' => $login_submit_label,
    'remember' => true,
    'placeholder_username' => $username_placeholder,
    'placeholder_password' => $password_placeholder
];
?>
<div class="lsd-login-wrapper">
    <div id="lsd_login_form_message"></div>
    <form id="<?php echo esc_attr($args['form_id']); ?>" method="post">
        <div class="login-form-content">
            <?php wp_login_form($args); ?>
            <?php LSD_Form::nonce('lsd_login','lsd_login'); ?>
        </div>
    </form>
</div>
