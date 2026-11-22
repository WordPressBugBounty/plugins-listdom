<?php
defined('ABSPATH') || die();
?>
<?php foreach (apply_filters('lsd_security_retention_information', [], $user->ID) as $notice): ?>
    <p><?php echo esc_html($notice); ?></p>
<?php endforeach; ?>
<?php foreach (['privacy_export' => __('Request personal data export', 'listdom'), 'privacy_erasure' => __('Request personal data erasure', 'listdom'), 'privacy_delete' => __('Request account deletion and erasure', 'listdom')] as $operation => $label): ?>
    <?php if (empty($section[$operation === 'privacy_export' ? 'export' : 'erasure'])) continue; ?>
    <?php $open($operation); ?>
    <div class="lsd-form-row">
        <label class="lsd-security-confirmation"><input type="checkbox" name="confirm" value="1" required><span><?php echo esc_html($label); ?></span></label>
    </div>
    <?php $close($label); ?>
<?php endforeach; ?>
