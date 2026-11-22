<?php
defined('ABSPATH') || die();
?>
<?php if ($deadline && !$totp_enabled): ?>
    <div class="lsd-alert lsd-warning"><?php echo esc_html(sprintf(
        /* translators: %s: enrollment deadline. */
        __('Your user role requires enrollment by %s.', 'listdom'), wp_date(get_option('date_format'), $deadline)
    )); ?></div>
<?php endif; ?>
<?php if (!$totp_enabled): ?>
    <?php if (!LSD_Security_Totp::available()): ?>
        <div class="lsd-alert lsd-warning"><?php esc_html_e('Secure authenticator encryption is unavailable. Contact the administrator.', 'listdom'); ?></div>
    <?php else: ?>
        <?php $open('totp_begin'); $close(__('Set up authenticator', 'listdom')); ?>
        <div class="lsd-security-setup lsd-util-hide" role="status">
            <p class="lsd-security-qr-instructions lsd-util-hide"><?php esc_html_e('Scan this QR code with your preferred authenticator app, then enter its code below.', 'listdom'); ?></p>
            <img class="lsd-security-qr-image lsd-util-hide" width="256" height="256" alt="<?php esc_attr_e('Authenticator setup QR code', 'listdom'); ?>">
            <p><?php esc_html_e('You can also enter this setup key manually in your authenticator app:', 'listdom'); ?></p>
            <code class="lsd-security-setup-key"></code>
            <p><a class="lsd-security-setup-link"><?php esc_html_e('Open authenticator', 'listdom'); ?></a></p>
        </div>
        <?php $open('totp_confirm'); ?>
        <?php $field('enrollment_code', __('Code from your authenticator app', 'listdom')); ?>
        <?php $close(__('Confirm enrollment', 'listdom')); ?>
    <?php endif; ?>
<?php else: ?>
    <?php $open('totp_codes'); $close(__('Replace recovery codes', 'listdom')); ?>
    <?php if (!LSD_Security_Totp::enforced($user->ID)): ?>
        <?php $open('totp_disable'); $close(__('Disable authenticator', 'listdom')); ?>
    <?php endif; ?>
<?php endif; ?>
<p><a href="<?php echo esc_url(LSD_Security_Recovery::link()); ?>"><?php esc_html_e('Recover 2FA by email', 'listdom'); ?></a></p>
