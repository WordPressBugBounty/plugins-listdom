<?php
defined('ABSPATH') || die();

/** @var WP_User $user */
/** @var string $instance */
/** @var array $security */
$field = static function (string $name, string $label, string $type = 'text', bool $required = true) use ($instance): void
{
    $id = $instance . $name . '-' . wp_unique_id();
    echo '<div class="lsd-form-row"><label class="lsd-fields-label" for="' . esc_attr($id) . '">' . esc_html($label) . '</label>';
    echo '<input id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" type="' . esc_attr($type) . '"' . ($required ? ' required' : '') . ($type === 'password' ? ' autocomplete="' . ($name === 'current_password' ? 'current-password' : 'new-password') . '"' : '') . ($name === 'code' || $name === 'enrollment_code' ? ' autocomplete="one-time-code"' : '') . '></div>';
};
$open = static function (string $operation, bool $reauthenticate = true) use ($field, $totp_enabled): void
{
    echo '<form class="lsd-security-form lsd-form-group" method="post" action="' . esc_url(admin_url('admin-ajax.php')) . '">';
    echo '<input type="hidden" name="action" value="lsd_account_security"><input type="hidden" name="operation" value="' . esc_attr($operation) . '">';
    wp_nonce_field('lsd_account_security', '_wpnonce', false);
    if ($reauthenticate)
    {
        $field('current_password', __('Current password', 'listdom'), 'password');
        if ($totp_enabled) $field('code', __('Authenticator code or one recovery code', 'listdom'));
    }
};
$close = static function (string $label): void
{
    echo '<button type="submit" class="lsd-general-button">' . esc_html($label) . '</button><div class="lsd-security-message" role="status" aria-live="polite"></div></form>';
};
?>
<div class="wui lsd-account-security lsd-fe-box-white" id="<?php echo esc_attr($instance); ?>" data-refresh-label="<?php esc_attr_e('Refresh account security', 'listdom'); ?>" data-error-label="<?php esc_attr_e('Unable to complete the request. Please try again.', 'listdom'); ?>">
    <h3><?php esc_html_e('Account Security', 'listdom'); ?></h3>
    <?php foreach ($security['section_order'] as $section_id): ?>
        <?php
        $section = $security['sections'][$section_id];
        $enabled = $section_id === 'totp' ? LSD_Security_Totp::offered($user->ID) : !empty($section['enabled']);
        if (!$enabled || ($section_id === 'privacy' && empty($section['export']) && empty($section['erasure']))) continue;
        ?>
        <section class="lsd-dashboard-box lsd-security-section">
            <?php if (trim($section['heading']) !== ''): ?>
                <h4><?php echo esc_html($section['heading']); ?></h4>
            <?php endif; ?>
            <?php if (trim($section['description']) !== ''): ?>
                <div class="lsd-security-description"><?php echo wp_kses(wpautop($section['description']), LSD_Security_Sections::allowed_html()); ?></div>
            <?php endif; ?>
            <?php include lsd_template('auth/security/' . $section_id . '.php'); ?>
        </section>
    <?php endforeach; ?>
</div>
