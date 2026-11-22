<?php
// no direct access
defined('ABSPATH') || die();
$title = $codes ? __('Save Your Recovery Codes', 'listdom') : ($enroll ? __('Set Up Two-Factor Authentication', 'listdom') : __('Two-Factor Authentication', 'listdom'));
?>
<div class="lsd-auth-challenge lsd-flex lsd-flex-col lsd-flex-items-stretch" style="gap:var(--lsd-fe-form-row-gap,var(--lsd-fe-gap-wide,16px))" role="group" aria-label="<?php echo esc_attr($title); ?>" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-error-label="<?php esc_attr_e('Unable to complete the request. Please try again.', 'listdom'); ?>">
    <?php if ($codes || $enroll): ?>
        <p class="lsd-m-0"><strong><?php echo esc_html($title); ?></strong></p>
    <?php endif; ?>
    <div class="lsd-auth-challenge-message<?php echo is_wp_error($result) ? '' : ' lsd-util-hide'; ?>" role="status" aria-live="polite">
        <?php if (is_wp_error($result)): ?>
            <div class="lsd-alert lsd-error"><?php echo LSD_Countdown::error($result); ?></div>
        <?php endif; ?>
    </div>
    <?php if ($codes): ?>
        <p class="lsd-m-0"><?php esc_html_e('Save these recovery codes now. They are shown only once. Each line is a separate, single-use code; enter only one when signing in.', 'listdom'); ?></p>
        <pre class="lsd-m-0" tabindex="0" style="white-space:pre-wrap"><?php echo esc_html(implode("\n", $codes)); ?></pre>
        <p class="lsd-m-0"><a class="lsd-general-button" href="<?php echo esc_url($result['redirect']); ?>"><?php esc_html_e('Continue', 'listdom'); ?></a></p>
    <?php elseif ($data): ?>
        <?php if ($enroll): ?>
            <p class="lsd-m-0"><?php esc_html_e('Set up an authenticator app before completing sign-in.', 'listdom'); ?></p>
        <?php endif; ?>
        <?php if (is_array($setup)): ?>
            <?php if ($setup['qr_code'] !== ''): ?>
                <p class="lsd-m-0"><?php esc_html_e('Scan this QR code with your preferred authenticator app, then enter its code below.', 'listdom'); ?></p>
                <img src="<?php echo esc_attr($setup['qr_code']); ?>" width="256" height="256" style="max-width:100%;height:auto" alt="<?php esc_attr_e('Authenticator setup QR code', 'listdom'); ?>">
            <?php endif; ?>
            <p class="lsd-m-0"><?php esc_html_e('You can also enter this setup key manually in your authenticator app:', 'listdom'); ?></p>
            <code style="overflow-wrap:anywhere"><?php echo esc_html($setup['secret']); ?></code>
            <p class="lsd-m-0"><a href="<?php echo esc_attr($setup['uri']); ?>"><?php esc_html_e('Open authenticator', 'listdom'); ?></a></p>
        <?php endif; ?>
        <form class="lsd-auth-challenge-form login-form-content" method="post" action="<?php echo esc_url($action_url); ?>">
            <?php
            echo LSD_Form::hidden(['id' => $instance . '-challenge', 'name' => 'challenge', 'value' => $token]);
            echo LSD_Form::hidden(['id' => $instance . '-nonce', 'name' => '_wpnonce', 'value' => wp_create_nonce('lsd_challenge_' . $token)]);
            echo LSD_Form::hidden(['id' => $instance . '-operation', 'name' => 'operation', 'value' => $enroll && !$has_setup ? 'begin' : 'verify']);
            ?>
            <?php if (!$enroll || $has_setup): ?>
                <div class="form-group">
                    <?php
                    echo LSD_Form::label(['for' => $instance . '-code', 'title' => $enroll ? __('Code from your authenticator app', 'listdom') : __('Authenticator code or one recovery code', 'listdom')]);
                    echo LSD_Form::text(['id' => $instance . '-code', 'name' => 'code', 'required' => true, 'attributes' => ['autocomplete' => 'one-time-code', 'autocapitalize' => 'off', 'spellcheck' => 'false', 'aria-describedby' => $enroll ? '' : $instance . '-code-help']]);
                    ?>
                    <?php if (!$enroll): ?>
                        <p id="<?php echo esc_attr($instance . '-code-help'); ?>" class="lsd-m-0"><?php esc_html_e('Enter the six-digit code from your authenticator app or one unused recovery code. Do not paste the full list of recovery codes.', 'listdom'); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <?php echo LSD_Form::submit(['id' => $instance . '-submit', 'class' => 'lsd-general-button', 'label' => $enroll && !$has_setup ? __('Set up authenticator', 'listdom') : __('Continue', 'listdom')]); ?>
            </div>
            <?php if ($enroll && $has_setup): ?>
                <p class="lsd-m-0"><button type="submit" class="lsd-text-button" name="operation" value="begin" data-operation="begin" formnovalidate><?php esc_html_e('Start setup again', 'listdom'); ?></button></p>
            <?php endif; ?>
        </form>
    <?php endif; ?>
    <?php if (!$codes): ?>
        <div class="lsd-auth-switcher-links">
            <?php if ($data): ?>
                <a class="lsd-auth-security-link lsd-totp-recovery-link" href="<?php echo esc_url(LSD_Security_Recovery::link()); ?>" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('lsd_totp_email_recovery')); ?>"><?php esc_html_e('Recover 2FA by email', 'listdom'); ?></a>
            <?php endif; ?>
            <a class="lsd-auth-security-link lsd-auth-back-to-login" href="<?php echo esc_url($restart); ?>"><?php esc_html_e('Back to sign in', 'listdom'); ?></a>
        </div>
    <?php endif; ?>
</div>
