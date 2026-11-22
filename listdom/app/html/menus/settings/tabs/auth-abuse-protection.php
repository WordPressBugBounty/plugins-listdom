<?php
defined('ABSPATH') || die();
?>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Abuse Protection', 'listdom'); ?></h3>
    <?php $row('lsd_security_rate_enabled', __('Rate Limiting', 'listdom'), LSD_Form::switcher([
        'id' => 'lsd_security_rate_enabled',
        'name' => 'lsd[security][rate_enabled]',
        'value' => $security['rate_enabled'],
        'toggle' => '#lsd_security_rate_fields, #lsd_security_totp_rate_fields',
    ]), __('Limits use expiring keyed hashes of network addresses and account identifiers. Forwarded IP headers are not trusted automatically.', 'listdom')); ?>
    <div id="lsd_security_rate_fields" class="<?php echo empty($security['rate_enabled']) ? 'lsd-util-hide' : ''; ?>">
        <div class="lsd-settings-fields-sub-wrapper">
            <?php foreach ([
                'login_pair_limit' => [__('Account Login Limit', 'listdom'), __('Maximum failed logins for the same account and IP address within 15 minutes.', 'listdom')],
                'login_ip_limit' => [__('IP Login Limit', 'listdom'), __('Maximum failed logins from one IP address within 15 minutes, across all accounts.', 'listdom')],
                'register_limit' => [__('Registration Limit', 'listdom'), __('Maximum public registration attempts from one IP address per hour.', 'listdom')],
                'mail_ip_limit' => [__('Recovery IP Limit', 'listdom'), __('Maximum requests from one IP address per hour, counted separately for each recovery or verification-resend action.', 'listdom')],
                'mail_identifier_limit' => [__('Identifier Limit', 'listdom'), __('Maximum requests for the same email address or login per hour, counted separately for each recovery or verification-resend action.', 'listdom')],
                'resend_cooldown' => [__('Resend Cooldown', 'listdom'), __('Minimum wait in seconds before repeating a recovery or verification-resend request for the same identifier.', 'listdom')],
            ] as $key => [$label, $description]): ?>
                <?php $row('lsd_security_' . $key, $label, LSD_Form::number(['class' => 'lsd-admin-input', 'id' => 'lsd_security_' . $key, 'name' => 'lsd[security][' . $key . ']', 'value' => $security[$key], 'min' => 1, 'max' => 3600]), $description); ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
