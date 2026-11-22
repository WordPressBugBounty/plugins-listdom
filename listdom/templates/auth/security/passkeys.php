<?php
defined('ABSPATH') || die();
if (!LSD_Auth_Passkeys::enabled()) return;

LSD_Auth::assets();
$passkey_context = LSD_Auth::context();
$passkey_view = LSD_Auth_State::read('view', LSD_Security_Account::input('lsd_auth_flow', $_GET));
if (empty($passkey_view['context']['inline_passkey'])) $passkey_view = [];
?>
<div class="lsd-native-passkey-controls lsd-native-account-entry" id="lsd-account-passkeys">
    <?php include lsd_template('auth/security/passkey-controls.php'); ?>
</div>
