<?php
// no direct access
defined('ABSPATH') || die();
$message = is_wp_error($result) ? $result->get_error_message() : ($result['message'] ?? '');
?>
<div class="lsd-auth-recovery lsd-flex lsd-flex-col lsd-flex-items-stretch" style="gap:var(--lsd-fe-form-row-gap,var(--lsd-fe-gap-wide,16px))" role="group" aria-label="<?php esc_attr_e('Recover Two-Factor Authentication', 'listdom'); ?>" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-error-label="<?php esc_attr_e('Unable to complete the request. Please try again.', 'listdom'); ?>">
    <div class="lsd-auth-recovery-message<?php echo $message === '' ? ' lsd-util-hide' : ''; ?>" role="status" aria-live="polite">
        <?php if ($message !== ''): ?>
            <div class="lsd-alert <?php echo is_wp_error($result) ? 'lsd-error' : 'lsd-success'; ?>"><?php echo is_wp_error($result) ? LSD_Countdown::error($result) : esc_html($message); ?></div>
        <?php endif; ?>
    </div>
    <?php if (!$complete && $valid): ?>
        <p class="lsd-m-0"><?php echo esc_html($key !== '' ? __('Confirm removal of your authenticator and recovery codes. Existing sign-ins will be revoked.', 'listdom') : __('Request an email link to reset two-factor authentication using your current account email address.', 'listdom')); ?></p>
        <form class="lsd-auth-recovery-form login-form-content" action="<?php echo esc_url($action_url); ?>" method="post">
            <?php
            echo LSD_Form::hidden(['id' => $instance . '-nonce', 'name' => '_wpnonce', 'value' => wp_create_nonce('lsd_totp_email_recovery')]);
            echo LSD_Form::hidden(['id' => $instance . '-key', 'name' => 'key', 'value' => $key]);
            echo LSD_Form::hidden(['id' => $instance . '-return', 'name' => 'return_url', 'value' => $return_url]);
            ?>
            <?php if ($key === ''): ?>
                <div class="form-group">
                    <?php
                    echo LSD_Form::label(['for' => $instance . '-login', 'title' => __('Username or email', 'listdom')]);
                    echo LSD_Form::text(['id' => $instance . '-login', 'name' => 'user_login', 'required' => true, 'attributes' => ['autocomplete' => 'username']]);
                    ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <?php echo LSD_Form::submit(['id' => $instance . '-submit', 'class' => 'lsd-general-button', 'label' => $key !== '' ? __('Reset 2FA', 'listdom') : __('Send recovery email', 'listdom')]); ?>
            </div>
        </form>
    <?php endif; ?>
    <div class="lsd-auth-switcher-links">
        <a class="lsd-auth-security-link lsd-auth-back-to-login" href="<?php echo esc_url($signin_url); ?>"><?php esc_html_e('Back to sign in', 'listdom'); ?></a>
    </div>
</div>
