<?php
defined('ABSPATH') || die();
?>
<div class="lsd-settings-fields-wrapper">
    <?php if (!LSD_Base::isPro()): ?><div class="lsd-alert lsd-warning"><?php esc_html_e('Email sign-in links and passkeys require Listdom Pro. All four social providers are available in Free.', 'listdom'); ?></div><?php endif; ?>
    <?php foreach (['magic' => __('Email sign-in links', 'listdom'), 'passkeys' => __('Passkeys', 'listdom')] as $method => $label): ?>
        <div class="lsd-form-row"><div class="lsd-col-3"><?php echo LSD_Form::label(['for' => 'lsd_passwordless_' . $method, 'title' => $label]); ?></div><div class="lsd-col-5"><?php echo LSD_Form::switcher(['id' => 'lsd_passwordless_' . $method, 'name' => 'lsd[passwordless][' . $method . ']', 'value' => $auth['passwordless'][$method] ?? 0]); ?></div></div>
    <?php endforeach; ?>
    <p class="lsd-admin-description"><?php esc_html_e('Magic links sign in existing accounts only, expire after 10 minutes, and work only in the requesting browser. Passkeys require HTTPS and a supported browser. Enable the Passkeys section under Account Security for enrollment, management and sign-in. Password login and free account recovery remain available.', 'listdom'); ?></p>
</div>
<p class="lsd-admin-description"><a href="<?php echo esc_url(LSD_Portal::admin_url('security', 'account-sections')); ?>"><?php esc_html_e('Manage the Passkeys section under Account Security → Account Sections', 'listdom'); ?></a></p>
