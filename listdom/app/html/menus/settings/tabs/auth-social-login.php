<?php
defined('ABSPATH') || die();
LSD_Auth::assets();
$button_design = LSD_Auth_Buttons::settings();
$guides = [
    'google' => ['url' => 'https://developers.google.com/identity/openid-connect/openid-connect', 'text' => __('Create a Google Cloud OAuth web client, configure its consent screen, and register the callback below. Request only openid, email and profile. Testing-mode and application verification restrictions are controlled by Google.', 'listdom')],
    'yahoo' => ['url' => 'https://developer.yahoo.com/oauth2/guide/openid_connect/', 'text' => __('Create a Yahoo Developer web application with OpenID Connect access and register the callback below. Enable the email and profile permissions for openid, email and profile scopes.', 'listdom')],
    'apple' => ['url' => 'https://developer.apple.com/documentation/signinwithapple/configuring-your-environment-for-sign-in-with-apple', 'text' => __('Apple web sign-in requires an eligible Apple Developer setup, a primary Sign in with Apple app, a Services ID, Team ID, Key ID and its private .p8 key. Use the Services ID as Client ID. Register the HTTPS domain and exact return URL. Name and email scopes use a POST callback; names may only be returned once and are not identity proof. Configure outbound mail sources for Apple private-relay addresses. Apple setup is not available solely by entering a normal Apple account email.', 'listdom')],
    'microsoft' => ['url' => 'https://learn.microsoft.com/en-us/entra/identity-platform/v2-protocols-oidc', 'text' => __('Register a Microsoft Entra web application and client secret. Match its supported account types to the tenant setting: common for personal and work/school, consumers for personal, organizations for work/school, or a tenant UUID for one directory. Register the exact callback as a Web redirect URI. Email may be absent or unverified; Listdom confirms it separately.', 'listdom')],
];
?>
<p class="lsd-admin-description"><?php esc_html_e('Google, Yahoo, Apple and Microsoft are included in Listdom Free. Save credentials before checking a connection. Enable Connected Accounts under Account Security to let users manage their identities.', 'listdom'); ?></p>
<?php if (!LSD_Auth_Providers::available()): ?><div class="lsd-alert lsd-warning"><?php esc_html_e('Social sign-in requires OpenSSL signing, verification and AES-256-GCM, and Multibyte String. Methods remain unavailable until the server supports these capabilities.', 'listdom'); ?></div><?php endif; ?>
<?php foreach (LSD_Auth_Providers::all() as $provider => $definition):
    $config = LSD_Auth_Providers::config($provider);
    $prefix = 'lsd_native_' . $provider;
    $name = 'lsd[social_login][' . $provider . ']';
    $credential_saved = LSD_Auth_Providers::secret($provider) !== '';
    $replace_label = $provider === 'apple' ? __('Replace private key', 'listdom') : __('Replace client secret', 'listdom');
    ?>
    <div class="lsd-settings-fields-wrapper">
        <h4 class="lsd-admin-title"><?php echo esc_html($definition['label']); ?></h4>
        <div class="lsd-form-row"><div class="lsd-col-3"><?php echo LSD_Form::label(['for' => $prefix . '_enabled', 'title' => __('Enabled', 'listdom')]); ?></div><div class="lsd-col-5"><?php echo LSD_Form::switcher(['id' => $prefix . '_enabled', 'name' => $name . '[enabled]', 'value' => $config['enabled'], 'toggle' => '#' . $prefix . '_fields']); ?></div></div>
        <div id="<?php echo esc_attr($prefix . '_fields'); ?>" class="lsd-settings-fields-sub-wrapper<?php echo empty($config['enabled']) ? ' lsd-util-hide' : ''; ?>">
            <?php foreach (['client_id' => __('Client ID', 'listdom')] + ($provider === 'apple' ? ['team_id' => __('Team ID', 'listdom'), 'key_id' => __('Key ID', 'listdom')] : []) as $key => $label): ?>
                <div class="lsd-form-row"><div class="lsd-col-3"><?php echo LSD_Form::label(['for' => $prefix . '_' . $key, 'title' => $label]); ?></div><div class="lsd-col-5"><?php echo LSD_Form::text(['class' => 'lsd-admin-input', 'id' => $prefix . '_' . $key, 'name' => $name . '[' . $key . ']', 'value' => $config[$key]]); ?></div></div>
            <?php endforeach; ?>
            <?php if ($provider === 'microsoft'): ?>
                <div class="lsd-form-row"><div class="lsd-col-3"><label for="<?php echo esc_attr($prefix . '_tenant'); ?>"><?php esc_html_e('Tenant / account types', 'listdom'); ?></label></div><div class="lsd-col-5"><input class="lsd-admin-input" id="<?php echo esc_attr($prefix . '_tenant'); ?>" name="<?php echo esc_attr($name . '[tenant]'); ?>" value="<?php echo esc_attr($config['tenant']); ?>"><p class="lsd-admin-description-tiny"><?php esc_html_e('common, consumers, organizations, or a tenant UUID.', 'listdom'); ?></p></div></div>
            <?php endif; ?>
            <div class="lsd-form-row">
                <div class="lsd-col-3"><label for="<?php echo esc_attr($prefix . '_secret'); ?>"><?php echo $provider === 'apple' ? esc_html__('Private key (.p8 PEM)', 'listdom') : esc_html__('Client secret', 'listdom'); ?></label></div>
                <div class="lsd-col-5">
                    <div class="lsd-native-credential-saved<?php echo $credential_saved ? '' : ' lsd-util-hide'; ?>">
                        <div class="lsd-alert lsd-success lsd-m-0">
                            <strong><?php echo $provider === 'apple' ? esc_html__('Private key saved', 'listdom') : esc_html__('Client secret saved', 'listdom'); ?></strong>
                            <div><?php esc_html_e('The saved value is hidden for security. You do not need to enter it again.', 'listdom'); ?></div>
                        </div>
                        <div class="lsd-mt-3">
                            <button type="button" class="lsd-text-button lsd-native-credential-replace" aria-expanded="false" aria-controls="<?php echo esc_attr($prefix . '_replacement'); ?>" data-replace-label="<?php echo esc_attr($replace_label); ?>" data-cancel-label="<?php esc_attr_e('Cancel replacement', 'listdom'); ?>"><?php echo esc_html($replace_label); ?></button>
                        </div>
                    </div>
                    <div id="<?php echo esc_attr($prefix . '_replacement'); ?>" class="<?php echo $credential_saved ? 'lsd-util-hide lsd-mt-3' : ''; ?>">
                        <?php if ($provider === 'apple'): ?><textarea class="lsd-admin-input" id="<?php echo esc_attr($prefix . '_secret'); ?>" name="<?php echo esc_attr('lsd_native_secrets[' . $provider . '][secret]'); ?>" rows="4" autocomplete="off" spellcheck="false" placeholder="<?php echo $credential_saved ? esc_attr__('Enter a new private key (.p8 PEM)', 'listdom') : esc_attr__('Paste your private key (.p8 PEM)', 'listdom'); ?>" <?php disabled($credential_saved); ?>></textarea>
                        <?php else: ?><input class="lsd-admin-input" type="password" id="<?php echo esc_attr($prefix . '_secret'); ?>" name="<?php echo esc_attr('lsd_native_secrets[' . $provider . '][secret]'); ?>" value="" autocomplete="new-password" placeholder="<?php echo $credential_saved ? esc_attr__('Enter a new client secret', 'listdom') : esc_attr__('Enter your client secret', 'listdom'); ?>" <?php disabled($credential_saved); ?>><?php endif; ?>
                        <p class="lsd-admin-description-tiny lsd-native-credential-hint" data-saved-text="<?php esc_attr_e('Save changes to apply the replacement. Leave blank to keep the saved credential.', 'listdom'); ?>" data-empty-text="<?php esc_attr_e('No credential saved yet. Enter it above, then save changes.', 'listdom'); ?>"><?php echo $credential_saved ? esc_html__('Save changes to apply the replacement. Leave blank to keep the saved credential.', 'listdom') : esc_html__('No credential saved yet. Enter it above, then save changes.', 'listdom'); ?></p>
                    </div>
                    <div class="lsd-mt-3"><label class="lsd-flex lsd-flex-align-items-center lsd-gap-2"><input class="lsd-m-0 lsd-native-credential-remove" type="checkbox" name="<?php echo esc_attr('lsd_native_secrets[' . $provider . '][remove]'); ?>" value="1"><span><?php esc_html_e('Remove saved credential when I save changes', 'listdom'); ?></span></label></div>
                </div>
            </div>
            <div class="lsd-form-row"><div class="lsd-col-3"><?php esc_html_e('Callback URL', 'listdom'); ?></div><div class="lsd-col-9"><code><?php echo esc_html(LSD_Auth::url('callback', ['provider' => $provider])); ?></code></div></div>
            <div class="lsd-alert lsd-info lsd-m-0"><?php echo esc_html($guides[$provider]['text'] ?? __('Configure the provider with this callback URL.', 'listdom')); ?> <a href="<?php echo esc_url($guides[$provider]['url'] ?? ''); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Provider setup documentation', 'listdom'); ?></a></div>
            <p class="lsd-admin-description-tiny"><?php echo esc_html(sprintf(__('Requested scopes: %s', 'listdom'), $definition['scopes'])); ?></p>
            <button type="button" class="lsd-secondary-button lsd-native-diagnose" data-url="<?php echo esc_url(LSD_Auth::url('diagnose', ['provider' => $provider, '_wpnonce' => wp_create_nonce('lsd_native_diagnose')])); ?>"><?php esc_html_e('Check saved connection', 'listdom'); ?></button>
            <div class="lsd-native-message" role="status" aria-live="polite" hidden></div>
        </div>
    </div>
<?php endforeach; ?>

<div class="lsd-settings-fields-wrapper" id="lsd-native-button-design">
    <h4 class="lsd-admin-title"><?php esc_html_e('Button Design', 'listdom'); ?></h4>
    <p class="lsd-admin-description"><?php esc_html_e('Apply the same design to social sign-in buttons in login, registration, header and dashboard forms.', 'listdom'); ?></p>
    <?php foreach ([
        'show_text' => [__('Show text', 'listdom'), __('Display the provider name in text, such as “Continue with Google”.', 'listdom')],
        'show_logo' => [__('Show provider logo', 'listdom'), __('Display the provider logo before the text.', 'listdom')],
        'branded' => [__('Use provider colors', 'listdom'), __('Use provider colors. Turn off to use Listdom’s secondary button colors.', 'listdom')],
    ] as $key => $field): ?>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label(['for' => 'lsd_native_button_' . $key, 'title' => $field[0]]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::switcher(['id' => 'lsd_native_button_' . $key, 'name' => 'lsd[social_login_buttons][' . $key . ']', 'value' => $button_design[$key]]); ?>
                <p class="lsd-admin-description-tiny"><?php echo esc_html($field[1]); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
    <div id="lsd-native-button-content-help" class="lsd-alert lsd-info lsd-m-0"><?php esc_html_e('Keep text or the provider logo enabled. To turn off the remaining option, enable the other one first.', 'listdom'); ?></div>
    <div class="lsd-mt-4">
        <h5 class="lsd-mt-0 lsd-mb-2"><?php esc_html_e('Preview', 'listdom'); ?></h5>
        <p class="lsd-admin-description-tiny lsd-mt-0 lsd-mb-3"><?php esc_html_e('Preview all providers, including disabled ones. These buttons do not start sign-in.', 'listdom'); ?></p>
        <div class="lsd-native-button-preview">
            <?php foreach (LSD_Auth_Providers::all() as $provider => $definition): ?>
                <?php echo LSD_Auth_Buttons::render($provider, $definition['label'], $button_design, true); ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<p class="lsd-admin-description"><a href="<?php echo esc_url(LSD_Portal::admin_url('security', 'account-sections')); ?>"><?php esc_html_e('Manage Connected Accounts under Account Security → Account Sections', 'listdom'); ?></a></p>
