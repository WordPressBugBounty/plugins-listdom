<?php
defined('ABSPATH') || die();
$connections = array_column(LSD_Auth_Store::identities($user->ID), null, 'provider');
foreach (LSD_Auth_Providers::all() as $provider => $definition):
    if (!isset($connections[$provider]) && !LSD_Auth_Providers::enabled($provider)) continue;
    $operation = (isset($connections[$provider]) ? 'unlink:' : 'link:') . $provider;
    ?>
    <div class="lsd-native-account-entry">
        <p><?php echo esc_html($definition['label'] . ' — ' . (isset($connections[$provider]) ? __('Connected', 'listdom') : __('Not connected', 'listdom'))); ?></p>
        <?php LSD_Auth_Accounts::form($operation, isset($connections[$provider]) ? __('Disconnect', 'listdom') : __('Connect', 'listdom')); ?>
    </div>
<?php endforeach; ?>
<div class="lsd-auth-switcher-links"><a class="lsd-auth-security-link" href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Set or recover your password', 'listdom'); ?></a></div>
