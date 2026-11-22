<?php
defined('ABSPATH') || die();
$error = $result['error'] ?? (!$data ? LSD_Auth_State::expired() : null);
$stage = $data['stage'] ?? 'message';
$message = $result['message'] ?? ($data['message'] ?? '');
$return = $data['context']['return'] ?? home_url('/');
$privacy = $stage === 'register' ? LSD_Privacy::consent_field([
    'id' => $instance . '-consent',
    'wrapper_class' => 'lsd-register-privacy-consent-field',
    'context' => 'register',
]) : '';
?>
<div class="lsd-auth-wrapper">
    <div class="lsd-auth-form-container">
        <div class="<?php echo $stage === 'register' ? 'lsd-register-wrapper' : 'lsd-login-wrapper'; ?>">
            <div class="lsd-native-flow">
                <?php if ($error): ?><div class="lsd-alert lsd-error" role="alert"><?php echo LSD_Countdown::error($error); ?></div><?php endif; ?>
                <?php if ($message !== ''): ?><div class="lsd-alert <?php echo !empty($data['error']) ? 'lsd-error' : 'lsd-success'; ?>" role="status"><?php echo esc_html($message); ?></div><?php endif; ?>
                <?php if (!empty($data['restart'])): ?><p><?php esc_html_e('This registration attempt has ended. Start again and choose your provider to continue, or sign in if your account was already created.', 'listdom'); ?></p><?php endif; ?>
                <?php if ($data && empty($result['message']) && $stage === 'register'): ?>
                    <?php
                    $submitted = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
                    $password = $submitted ? LSD_Security_Account::input('password') : LSD_Security::generated_password();
                    $password_confirmation = $submitted ? LSD_Security_Account::input('password_confirmation') : $password;
                    ?>
                    <h3><?php esc_html_e('Complete your registration', 'listdom'); ?></h3>
                    <form id="<?php echo esc_attr($instance . '-registration'); ?>" method="post" class="lsd-native-registration lsd-registration-form" autocomplete="on">
                        <?php wp_nonce_field('lsd_native_view_' . $token, '_wpnonce', false); ?>
                        <div class="form-group">
                            <?php
                            echo LSD_Form::label(['for' => $instance . '-username', 'title' => __('Username', 'listdom')]);
                            echo LSD_Form::text([
                                'id' => $instance . '-username',
                                'name' => 'username',
                                'attributes' => ['autocomplete' => 'off', 'autocapitalize' => 'none', 'spellcheck' => 'false'],
                                'value' => $submitted ? sanitize_user(LSD_Security_Account::input('username'), true) : sanitize_user(strstr($data['identity']['email'], '@', true) ?: '', true),
                                'required' => true,
                            ]);
                            ?>
                        </div>
                        <div class="form-group">
                            <?php
                            echo LSD_Form::label(['for' => $instance . '-email', 'title' => __('Email address', 'listdom')]);
                            echo LSD_Form::email([
                                'id' => $instance . '-email',
                                'name' => 'email',
                                'attributes' => ['autocomplete' => 'username', 'autocapitalize' => 'none', 'spellcheck' => 'false'],
                                'value' => $submitted ? sanitize_email(LSD_Security_Account::input('email')) : $data['identity']['email'],
                                'required' => true,
                            ]);
                            ?>
                        </div>
                        <div class="form-group">
                            <?php
                            echo LSD_Form::label(['for' => $instance . '-password', 'title' => __('Password', 'listdom')]);
                            echo LSD_Form::input([
                                'id' => $instance . '-password',
                                'name' => 'password',
                                'value' => $password,
                                'attributes' => ['autocomplete' => 'new-password', 'minlength' => LSD_Security::password_rules()['length'], 'aria-describedby' => $instance . '-password-help'],
                                'required' => true,
                            ], 'password');
                            ?>
                        </div>
                        <div class="form-group">
                            <?php
                            echo LSD_Form::label(['for' => $instance . '-password-confirmation', 'title' => __('Confirm password', 'listdom')]);
                            echo LSD_Form::input([
                                'id' => $instance . '-password-confirmation',
                                'name' => 'password_confirmation',
                                'value' => $password_confirmation,
                                'attributes' => ['autocomplete' => 'new-password', 'minlength' => LSD_Security::password_rules()['length']],
                                'required' => true,
                            ], 'password');
                            ?>
                        </div>
                        <div class="form-group lsd-auth-switcher-links lsd-pt-0">
                            <button type="button" class="lsd-text-button lsd-native-password-toggle" aria-pressed="false" aria-controls="<?php echo esc_attr($instance . '-password ' . $instance . '-password-confirmation'); ?>" data-show-label="<?php esc_attr_e('Show passwords', 'listdom'); ?>" data-hide-label="<?php esc_attr_e('Hide passwords', 'listdom'); ?>"><?php esc_html_e('Show passwords', 'listdom'); ?></button>
                        </div>
                        <div id="<?php echo esc_attr($instance . '-password-help'); ?>" class="form-group">
                            <p><?php esc_html_e('Use the generated password or enter your own in both fields. Save it to sign in with your email or confirm account security changes. This is your password for this site, separate from your social account password.', 'listdom'); ?></p>
                            <p class="lsd-password-policy"><?php echo esc_html(LSD_Security::password_description()); ?></p>
                        </div>
                        <?php LSD_Portal_Fields::registration($data['context']['role'] ?? ''); ?>
                        <?php if ($privacy !== ''): ?>
                            <div class="form-group lsd-register-privacy-consent"><?php echo $privacy; ?></div>
                        <?php endif; ?>
                        <div class="form-group">
                            <button type="submit" class="lsd-general-button"><?php esc_html_e('Create account', 'listdom'); ?></button>
                        </div>
                    </form>
                <?php elseif ($data && $stage === 'email-confirm'): ?>
                    <p><?php echo esc_html(sprintf(__('Confirm %s and finish creating your account.', 'listdom'), $data['email'])); ?></p>
                    <form method="post" action="<?php echo esc_url(LSD_Auth::url('verify-email', ['proof' => $data['proof']])); ?>" class="lsd-native-email-confirm login-form-content">
                        <?php wp_nonce_field('lsd_signup_email_' . $data['proof'], '_wpnonce', false); ?>
                        <div class="form-group">
                            <button class="lsd-general-button" type="submit"><?php esc_html_e('Confirm email and create account', 'listdom'); ?></button>
                        </div>
                    </form>
                <?php elseif ($data && $stage === 'magic'): ?>
                    <p><?php echo esc_html(sprintf(__('Sign in as %s in this browser?', 'listdom'), $data['account'])); ?></p>
                    <form method="post" class="lsd-native-magic-confirm login-form-content">
                        <?php wp_nonce_field('lsd_native_view_' . $token, '_wpnonce', false); ?>
                        <div class="form-group">
                            <button class="lsd-general-button" type="submit"><?php esc_html_e('Confirm sign-in', 'listdom'); ?></button>
                        </div>
                    </form>
                <?php elseif ($data && $stage === 'passkey-enroll'): ?>
                    <form method="post" action="<?php echo esc_url(LSD_Auth::url('passkey')); ?>" class="lsd-native-passkey login-form-content" data-operation="create_options" data-passkey-error="<?php esc_attr_e('Passkey setup failed or was cancelled. Start again from Account Security.', 'listdom'); ?>">
                        <?php wp_nonce_field('lsd_native_auth', '_wpnonce', false); ?>
                        <input type="hidden" name="context" value="<?php echo esc_attr(LSD_Auth::sign($data['context'])); ?>">
                        <input type="hidden" name="proof" value="<?php echo esc_attr($data['proof']); ?>">
                        <div class="form-group">
                            <?php
                            echo LSD_Form::label(['for' => $instance . '-label', 'title' => __('Passkey name', 'listdom')]);
                            echo LSD_Form::text([
                                'id' => $instance . '-label',
                                'name' => 'label',
                                'attributes' => ['maxlength' => '191'],
                                'required' => true,
                            ]);
                            ?>
                        </div>
                        <div class="form-group">
                            <button class="lsd-general-button" type="submit"><?php esc_html_e('Create passkey', 'listdom'); ?></button>
                        </div>
                        <div class="lsd-native-message" role="status" aria-live="polite" hidden></div>
                    </form>
                <?php endif; ?>
            </div>
            <div class="lsd-auth-switcher-links"><a class="lsd-auth-security-link" href="<?php echo esc_url(remove_query_arg('lsd_auth_flow', $return)); ?>"><?php echo !empty($data['restart']) ? esc_html__('Start registration again', 'listdom') : (is_user_logged_in() ? esc_html__('Back to account', 'listdom') : esc_html__('Back to sign in', 'listdom')); ?></a></div>
        </div>
    </div>
</div>
