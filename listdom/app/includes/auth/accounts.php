<?php

/** Own-account mutations require an operation-bound, recent authentication proof. */
class LSD_Auth_Accounts
{
    public static function enabled(): bool
    {
        return !empty(LSD_Security::settings()['sections']['connections']['enabled']);
    }

    public static function request(string $action, array $context, bool $json = false): void
    {
        $user_id = get_current_user_id();
        $operation = sanitize_text_field(LSD_Security_Account::input('operation'));
        $provider = sanitize_key(LSD_Security_Account::input('reauth_provider'));
        $valid = self::allowed($operation);
        if (!$user_id || is_wp_error($valid)) LSD_Auth::failure(is_wp_error($valid) ? $valid : LSD_Auth_State::expired(), $context, '', $json);
        $context['return'] = LSD_Auth::local_url($context['return']);
        $context['credential_label'] = substr(sanitize_text_field(LSD_Security_Account::input('label')), 0, 191);
        $user = get_userdata($user_id);
        if (LSD_Security_Totp::enrollment_required($user_id)) LSD_Auth::failure(new WP_Error('lsd_auth_enroll', __('Sign out and sign in again to complete required authenticator enrollment first.', 'listdom')), $context, '', $json);
        if ($provider !== '')
        {
            $linked = array_column(LSD_Auth_Store::identities($user_id), 'provider');
            if (empty(LSD_Auth_Providers::all()[$provider]['reauth']) || !in_array($provider, $linked, true) || !LSD_Auth_Providers::enabled($provider)) LSD_Auth::failure(new WP_Error('lsd_auth_reauth_provider', __('Use your password or another connected provider that supports recent-authentication verification.', 'listdom')), $context);
            if (LSD_Security_Totp::enabled($user_id))
            {
                $limit = LSD_Security_Rate::second_factor($user_id, 'native-reauth:' . $user_id);
                if (is_wp_error($limit)) LSD_Auth::failure($limit, $context);
                if (!LSD_Security_Totp::verify($user_id, LSD_Security_Account::input('code'))) LSD_Auth::failure(new WP_Error('lsd_totp_invalid', __('Enter a valid authenticator or recovery code.', 'listdom')), $context);
            }
            (new LSD_Auth())->start($provider, $context, ['phase' => 'reauth', 'operation' => $operation, 'user' => $user_id, 'session' => hash('sha256', wp_get_session_token()), 'fingerprint' => LSD_Security_Challenge::fingerprint($user)]);
        }
        $proof = LSD_Security::reauthenticate($user_id, LSD_Security_Account::input('current_password'), LSD_Security_Account::input('code'));
        if (is_wp_error($proof)) LSD_Auth::failure($proof, $context, '', $json);
        self::execute($operation, $user, $context, $json);
    }

    public static function allowed(string $operation)
    {
        $parts = explode(':', $operation, 2);
        if (count($parts) !== 2) return LSD_Auth_State::expired();
        if (in_array($parts[0], ['link', 'unlink'], true) && self::enabled() && isset(LSD_Auth_Providers::all()[$parts[1]])) return true;
        if (in_array($parts[0], ['passkey_create', 'passkey_rename', 'passkey_delete'], true) && LSD_Auth_Passkeys::enabled()) return true;
        return new WP_Error('lsd_auth_management_disabled', __('This account-management action is not available.', 'listdom'));
    }

    public static function callback(array $state, array $identity): void
    {
        $operation = $state['operation'];
        $user = get_userdata($operation['user']);
        if (!$user || get_current_user_id() !== (int) $user->ID || !hash_equals($operation['session'], hash('sha256', wp_get_session_token())) || !hash_equals($operation['fingerprint'], LSD_Security_Challenge::fingerprint($user))) LSD_Auth::failure(LSD_Auth_State::expired(), $state['context']);
        $valid = self::allowed($operation['operation']);
        if (is_wp_error($valid)) LSD_Auth::failure($valid, $state['context']);
        if ($operation['phase'] === 'reauth')
        {
            $owner = LSD_Auth_Store::identity($identity['issuer'], $identity['subject']);
            if (is_wp_error($owner)) LSD_Auth::failure($owner, $state['context']);
            if (!$owner || (int) $owner['user_id'] !== (int) $user->ID || $owner['provider'] !== $identity['provider']) LSD_Auth::failure(new WP_Error('lsd_auth_wrong_identity', __('Choose an identity already connected to your current account.', 'listdom')), $state['context']);
            self::execute($operation['operation'], $user, $state['context']);
        }
        if ($operation['phase'] === 'link' && $operation['operation'] === 'link:' . $identity['provider'])
        {
            $result = LSD_Auth_Store::link($user->ID, $identity);
            if (is_wp_error($result)) LSD_Auth::failure($result, $state['context']);
            LSD_Auth::show(['stage' => 'message', 'message' => __('Account connected.', 'listdom')], $state['context']);
        }
        LSD_Auth::failure(LSD_Auth_State::expired(), $state['context']);
    }

    public static function execute(string $operation, WP_User $user, array $context, bool $json = false): void
    {
        global $wpdb;
        [$action, $target] = explode(':', $operation, 2);
        $context['return'] = remove_query_arg('lsd_auth_flow', $context['return']);
        if ($action === 'link')
        {
            (new LSD_Auth())->start($target, $context, ['phase' => 'link', 'operation' => $operation, 'user' => $user->ID, 'session' => hash('sha256', wp_get_session_token()), 'fingerprint' => LSD_Security_Challenge::fingerprint($user)]);
        }
        if ($action === 'unlink')
        {
            $wpdb->delete($wpdb->prefix . 'lsd_identities', ['user_id' => $user->ID, 'provider' => $target]);
            LSD_Security_API::revoke($user->ID);
            update_user_meta($user->ID, '_lsd_security_epoch', wp_generate_password(32, false));
            do_action('lsd_auth_identity_unlinked', $user->ID, $target);
        }
        else if ($action === 'passkey_create')
        {
            $proof = LSD_Auth_State::issue('reauth', ['user' => $user->ID, 'operation' => $operation, 'session' => hash('sha256', wp_get_session_token()), 'fingerprint' => LSD_Security_Challenge::fingerprint($user)], 300);
            if ($proof === '') LSD_Auth::failure(LSD_Auth_State::expired(), $context, '', $json);
            if ($json) wp_send_json(['success' => 1, 'proof' => $proof]);
            LSD_Auth::show(['stage' => 'passkey-enroll', 'proof' => $proof], $context);
        }
        else if ($action === 'passkey_delete')
        {
            $wpdb->delete($wpdb->prefix . 'lsd_passkeys', ['id' => absint($target), 'user_id' => $user->ID]);
            update_user_meta($user->ID, '_lsd_security_epoch', wp_generate_password(32, false));
            do_action('lsd_auth_passkey_deleted', $user->ID);
        }
        else if ($action === 'passkey_rename')
        {
            $label = sanitize_text_field(LSD_Security_Account::input('label'));
            // Provider reauthentication carries the submitted name in its signed server context.
            if ($label === '') $label = $context['credential_label'] ?? __('Passkey', 'listdom');
            $wpdb->update($wpdb->prefix . 'lsd_passkeys', ['label' => substr($label, 0, 191)], ['id' => absint($target), 'user_id' => $user->ID]);
        }
        LSD_Auth::show(['stage' => 'message', 'message' => __('Account security updated.', 'listdom')], $context);
    }

    public static function form(string $operation, string $label, bool $name = false, array $context = [], string $proof = ''): void
    {
        $id = 'lsd-native-manage-' . wp_generate_uuid4();
        $context = $context ?: LSD_Auth::context();
        $inline = $operation === 'passkey_create:new' && !empty($context['inline_passkey']);
        $user_id = get_current_user_id();
        $action = $proof !== '' ? 'passkey' : 'manage';
        echo '<form method="post" class="lsd-form-group lsd-native-manage' . ($inline ? ' lsd-native-passkey' : '') . '" action="' . esc_url(LSD_Auth::url($action)) . '"';
        if ($inline) echo ' data-operation="create_options" data-passkey-url="' . esc_url(LSD_Auth::url('passkey')) . '" data-passkey-error="' . esc_attr__('Passkey setup failed or was cancelled. Please try again.', 'listdom') . '"';
        echo '>';
        wp_nonce_field('lsd_native_auth', '_wpnonce', false);
        echo '<input type="hidden" name="operation" value="' . esc_attr($operation) . '"><input type="hidden" name="context" value="' . esc_attr(LSD_Auth::sign($context)) . '">';
        if ($name)
        {
            echo '<div class="lsd-form-row">';
            echo LSD_Form::label(['for' => $id . '-label', 'class' => 'lsd-fields-label', 'title' => __('Passkey name', 'listdom')]);
            echo LSD_Form::text(['id' => $id . '-label', 'name' => 'label', 'value' => $proof !== '' ? ($context['credential_label'] ?? '') : '', 'attributes' => ['maxlength' => '191'], 'required' => true]);
            echo '</div>';
        }
        if ($proof !== '')
        {
            echo '<input type="hidden" name="proof" value="' . esc_attr($proof) . '">';
            echo '<button type="submit" class="lsd-general-button">' . esc_html($label) . '</button>';
            echo ' <a class="lsd-native-passkey-restart" href="' . esc_url($context['return']) . '">' . esc_html__('Start again', 'listdom') . '</a></form>';
            return;
        }
        $linked = array_filter(LSD_Auth_Store::identities($user_id), static function (array $row): bool { return !empty(LSD_Auth_Providers::all()[$row['provider']]['reauth']) && LSD_Auth_Providers::enabled($row['provider']); });
        if ($linked)
        {
            echo '<div class="lsd-form-row"><label class="lsd-fields-label" for="' . esc_attr($id . '-provider') . '">' . esc_html__('Confirm identity using', 'listdom') . '</label><select id="' . esc_attr($id . '-provider') . '" name="reauth_provider"><option value="">' . esc_html__('Current password', 'listdom') . '</option>';
            foreach ($linked as $row) echo '<option value="' . esc_attr($row['provider']) . '">' . esc_html(LSD_Auth_Providers::all()[$row['provider']]['label']) . '</option>';
            echo '</select></div>';
        }
        echo '<div class="lsd-form-row">';
        echo LSD_Form::label(['for' => $id . '-password', 'class' => 'lsd-fields-label', 'title' => __('Current password (when selected)', 'listdom')]);
        echo LSD_Form::input(['id' => $id . '-password', 'name' => 'current_password', 'attributes' => ['autocomplete' => 'current-password']], 'password');
        echo '</div>';
        if (LSD_Security_Totp::enabled($user_id))
        {
            echo '<div class="lsd-form-row">';
            echo LSD_Form::label(['for' => $id . '-code', 'class' => 'lsd-fields-label', 'title' => __('Authenticator code or one recovery code', 'listdom')]);
            echo LSD_Form::text(['id' => $id . '-code', 'name' => 'code', 'attributes' => ['autocomplete' => 'one-time-code'], 'required' => true]);
            echo '</div>';
        }
        echo '<button type="submit" class="lsd-general-button">' . esc_html($label) . '</button></form>';
    }
}
