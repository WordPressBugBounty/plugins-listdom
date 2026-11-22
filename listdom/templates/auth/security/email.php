<?php
defined('ABSPATH') || die();
?>
<p><?php echo esc_html($user->user_email); ?></p>
<?php if (is_array($pending_email) && !empty($pending_email['email'])): ?>
    <div class="lsd-alert lsd-info">
        <?php echo esc_html(sprintf(
            /* translators: %s: pending email address. */
            __('Awaiting confirmation: %s. Your current address remains active.', 'listdom'), $pending_email['email']
        )); ?>
        <?php if (($pending_email['expires'] ?? 0) <= time()) esc_html_e(' The confirmation has expired; request another email.', 'listdom'); ?>
    </div>
    <?php $open('email_resend'); $close(__('Resend confirmation', 'listdom')); ?>
    <?php $open('email_cancel'); $close(__('Cancel email change', 'listdom')); ?>
<?php endif; ?>
<?php $open('email_request'); ?>
<?php $field('email', __('New email address', 'listdom'), 'email'); ?>
<?php $close(__('Request email change', 'listdom')); ?>
