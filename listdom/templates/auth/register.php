<?php
// no direct access
defined('ABSPATH') || die();

// User is Already Logged-in
if(is_user_logged_in()) return '';

$auth = LSD_Options::auth();

$register_redirect_link = isset($auth['register']['redirect']) ? get_permalink($auth['register']['redirect']) : false;
$redirect = $register_redirect_link ?: home_url();
$username_label = $auth['register']['username_label'];
$username_placeholder = $auth['register']['username_placeholder'];
$password_label = $auth['register']['password_label'];
$password_placeholder = $auth['register']['password_placeholder'];
$email_label = $auth['register']['email_label'];
$email_placeholder = $auth['register']['email_placeholder'];
$register_submit_label = $auth['register']['submit_label'];

$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd-registration-form").listdomRegisterForm(
    {
        ajax_url: "' . admin_url('admin-ajax.php', null) . '",
        nonce: "' . wp_create_nonce('lsd_register_nonce') . '"
    });
});
</script>');
?>
<div class="lsd-register-wrapper">
    <div id="lsd_register_form_message"></div>
    <form id="lsd-registration-form" method="post">
        <?php LSD_Form::nonce('lsd_register_nonce', 'lsd_register_nonce'); ?>
        <div class="form-group">
            <?php
                echo LSD_Form::label([
                    'for' => 'lsd_register_username',
                    'title' => $username_label
                ]);
                echo LSD_Form::text([
                    'name' => 'lsd_username',
                    'id' => 'lsd_register_username',
                    'value' => isset($_POST['lsd_username']) ? esc_attr($_POST['lsd_username']) : '',
                    'required' => true,
                    'placeholder' => $username_placeholder
                ]);
            ?>
        </div>

        <div class="form-group">
            <?php
                echo LSD_Form::label([
                    'for' => 'lsd_email',
                    'title' => $email_label
                ]);
                echo LSD_Form::email([
                    'name' => 'lsd_email',
                    'id' => 'lsd_email',
                    'value' => isset($_POST['reg_email']) ? esc_attr($_POST['reg_email']) : '',
                    'required' => true,
                    'placeholder' => $email_placeholder
                ]);
            ?>
        </div>

        <div class="form-group">
            <?php
                echo LSD_Form::label([
                    'for' => 'lsd_password',
                    'title' => $password_label
                ]);
                echo LSD_Form::input([
                    'name' => 'lsd_password',
                    'id' => 'lsd_password',
                    'value' => '',
                    'required' => true,
                    'placeholder' => $password_placeholder
                ], 'password');
            ?>
            <div class="lsd-register-password-rules">
                <?php esc_html_e('Password must contain at least:', 'listdom'); ?>
                <ul>
                    <li><?php esc_html_e('an uppercase letter','listdom'); ?></li>
                    <li><?php esc_html_e('a lowercase letter','listdom'); ?></li>
                    <li><?php esc_html_e('a number','listdom'); ?></li>
                    <li><?php esc_html_e('a special character e.g. ~`! @#$%^&*()-_+={}[]|;:"<>,./?','listdom'); ?></li>
                </ul>
            </div>
        </div>

        <?php
        if (!empty($redirect))
        {
            echo LSD_Form::hidden([
                'name' => 'lsd_redirect',
                'id' => 'lsd_redirect',
                'value' => $redirect,
            ]);
        }
        ?>
        <div class="form-group">
            <?php
                echo LSD_Form::submit([
                    'id' => 'lsd_register_submit',
                    'label' => $register_submit_label,
                ]);
            ?>
        </div>
    </form>
</div>
