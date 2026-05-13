<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $redirect */
/** @var string $role */

// User is Already Logged-in
if (is_user_logged_in()) return '';

$instance_id = uniqid('lsd-register-');

$auth = LSD_Options::auth();

$register_redirect_link = isset($auth['register']['redirect']) ? get_permalink($auth['register']['redirect']) : false;
$redirect = $register_redirect_link ?: home_url();

$register_privacy_field = LSD_Privacy::consent_field([
    'id' => 'lsd_register_privacy_consent_' . $instance_id,
    'wrapper_class' => 'lsd-register-privacy-consent-field',
    'context' => 'register',
]);

$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#' . esc_js('lsd-registration-form-' . $instance_id) . '").listdomRegisterForm(
    {
        ajax_url: "' . admin_url('admin-ajax.php', null) . '",
        nonce: "' . wp_create_nonce('lsd_register_nonce') . '"
    });
});
</script>');
?>
<div class="lsd-register-wrapper">
    <div id="<?php echo esc_attr('lsd_register_form_message_' . $instance_id); ?>" class="lsd-register-form-message"></div>
    <form id="<?php echo esc_attr('lsd-registration-form-' . $instance_id); ?>" method="post" class="lsd-registration-form">
        <?php LSD_Form::nonce('lsd_register_nonce', 'lsd_register_nonce'); ?>
        <div class="form-group">
            <?php
            echo LSD_Form::label([
                'for' => 'lsd_register_username_' . $instance_id,
                'title' => $auth['register']['username_label']
            ]);
            echo LSD_Form::text([
                'name' => 'lsd_username',
                'id' => 'lsd_register_username_' . $instance_id,
                'value' => isset($_POST['lsd_username']) ? sanitize_text_field(wp_unslash($_POST['lsd_username'])) : '',
                'required' => true,
                'placeholder' => $auth['register']['username_placeholder']
            ]);
            ?>
        </div>

        <div class="form-group">
            <?php
            echo LSD_Form::label([
                'for' => 'lsd_email_' . $instance_id,
                'title' => $auth['register']['email_label']
            ]);
            echo LSD_Form::email([
                'name' => 'lsd_email',
                'id' => 'lsd_email_' . $instance_id,
                'value' => isset($_POST['reg_email']) ? sanitize_email(wp_unslash($_POST['reg_email'])) : '',
                'required' => true,
                'placeholder' => $auth['register']['email_placeholder']
            ]);
            ?>
        </div>

        <div class="form-group">
            <?php
            echo LSD_Form::label([
                'for' => 'lsd_password_' . $instance_id,
                'title' => $auth['register']['password_label']
            ]);
            echo LSD_Form::input([
                'name' => 'lsd_password',
                'id' => 'lsd_password_' . $instance_id,
                'value' => '',
                'required' => true,
                'placeholder' => $auth['register']['password_placeholder']
            ], 'password');
            ?>
            <?php if ($auth['register']['strong_password']): ?>
                <?php
                $length = isset($auth['register']['password_length']) ? intval($auth['register']['password_length']) : 8;
                $contain_uppercase = isset($auth['register']['contain_uppercase']) && $auth['register']['contain_uppercase'];
                $contain_lowercase = isset($auth['register']['contain_lowercase']) && $auth['register']['contain_lowercase'];
                $contain_numbers = isset($auth['register']['contain_numbers']) && $auth['register']['contain_numbers'];
                $contain_specials = isset($auth['register']['contain_specials']) && $auth['register']['contain_specials'];

                $password_rules = [];
                if ($length > 0)
                {
                    $password_rules[] = [
                        'rule' => 'length',
                        'min_length' => $length,
                        'label' => sprintf(
                            /* translators: %s: Minimum required password length. */
                            esc_html__('%s characters long', 'listdom'),
                            $length
                        ),
                    ];
                }
                if ($contain_uppercase)
                {
                    $password_rules[] = [
                        'rule' => 'uppercase',
                        'label' => esc_html__('one uppercase letter', 'listdom'),
                    ];
                }
                if ($contain_lowercase)
                {
                    $password_rules[] = [
                        'rule' => 'lowercase',
                        'label' => esc_html__('one lowercase letter', 'listdom'),
                    ];
                }
                if ($contain_numbers)
                {
                    $password_rules[] = [
                        'rule' => 'number',
                        'label' => esc_html__('one number', 'listdom'),
                    ];
                }
                if ($contain_specials)
                {
                    $password_rules[] = [
                        'rule' => 'special',
                        'label' => esc_html__('one special character', 'listdom'),
                    ];
                }
                ?>
                <?php if ($password_rules): ?>
                    <div class="lsd-register-password-rules">
                        <?php if ($contain_uppercase || $contain_lowercase || $contain_numbers || $contain_specials): ?>
                            <p class="lsd-fe-description-tiny">
                                <?php esc_html_e('Password must contain at least:', 'listdom'); ?>
                            </p>
                        <?php endif; ?>
                        <div class="lsd-register-password-rule-list">
                            <?php foreach ($password_rules as $rule): ?>
                                <span class="lsd-register-password-rule" data-rule="<?php echo esc_attr($rule['rule']); ?>"<?php echo isset($rule['min_length']) ? ' data-min-length="' . esc_attr($rule['min_length']) . '"' : ''; ?>>
                                    <?php echo esc_html($rule['label']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
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

        if (trim($role))
        {
            echo LSD_Form::hidden([
                'name' => 'lsd_role',
                'id' => 'lsd_role',
                'value' => $role,
            ]);
        }
        ?>
        <?php if ($register_privacy_field !== ''): ?>
            <div class="form-group lsd-register-privacy-consent">
                <?php echo $register_privacy_field; ?>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <?php
            echo LSD_Form::submit([
                'class' => 'lsd-general-button',
                'id' => 'lsd_register_submit_' . $instance_id,
                'label' => $auth['register']['submit_label'],
            ]);
            ?>
        </div>
    </form>
</div>
