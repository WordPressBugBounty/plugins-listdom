<?php
defined('ABSPATH') || die();

$create_context = ['inline_passkey' => true] + $passkey_context;
$proof = ($passkey_view['stage'] ?? '') === 'passkey-enroll' ? $passkey_view['proof'] : '';
$reauth = $proof !== '' ? LSD_Auth_State::read('reauth', $proof) : [];
$resume = ($reauth['operation'] ?? '') === 'passkey_create:new'
    && (int) $reauth['user'] === $user->ID
    && hash_equals($reauth['session'], hash('sha256', wp_get_session_token()))
    && hash_equals($reauth['fingerprint'], LSD_Security_Challenge::fingerprint($user));
$error = $passkey_view['message'] ?? ($proof !== '' && !$resume ? LSD_Auth_State::expired()->get_error_message() : '');
?>
<?php foreach (LSD_Auth_Store::passkeys($user->ID) as $credential): ?>
    <div class="lsd-native-account-entry">
        <p><?php echo esc_html($credential['label']); ?> <?php echo esc_html($credential['last_used'] ? sprintf(__('Last used: %s UTC', 'listdom'), $credential['last_used']) : __('Not used yet', 'listdom')); ?></p>
        <?php LSD_Auth_Accounts::form('passkey_rename:' . $credential['id'], __('Rename passkey', 'listdom'), true, $passkey_context); ?>
        <?php LSD_Auth_Accounts::form('passkey_delete:' . $credential['id'], __('Remove passkey', 'listdom'), false, $passkey_context); ?>
    </div>
<?php endforeach; ?>
<?php if ($resume): ?>
    <?php
    $resume_context = ['credential_label' => $passkey_view['context']['credential_label'] ?? ''] + $create_context;
    LSD_Auth_Accounts::form('passkey_create:new', __('Create passkey', 'listdom'), true, $resume_context, $proof);
    ?>
<?php endif; ?>
<div class="lsd-native-passkey-add"<?php if ($resume) echo ' hidden'; ?>>
    <?php LSD_Auth_Accounts::form('passkey_create:new', __('Add passkey', 'listdom'), true, $create_context); ?>
</div>
<div class="lsd-native-message lsd-alert lsd-error" role="status" aria-live="polite" tabindex="-1"<?php if ($error === '') echo ' hidden'; ?>><?php echo esc_html($error); ?></div>
