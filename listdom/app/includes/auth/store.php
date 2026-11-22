<?php

/** Site-local credentials. Unique indexes arbitrate competing ownership claims. */
class LSD_Auth_Store
{
    public static function install(): void
    {
        global $wpdb;
        $collation = $wpdb->get_charset_collate();
        dbDelta("CREATE TABLE {$wpdb->prefix}lsd_identities (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            provider varchar(64) NOT NULL,
            identity_hash char(64) NOT NULL,
            issuer text NOT NULL,
            subject text NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY identity_hash (identity_hash),
            UNIQUE KEY user_provider (user_id,provider)
        ) $collation;");
        dbDelta("CREATE TABLE {$wpdb->prefix}lsd_passkeys (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            credential_hash char(64) NOT NULL,
            credential_id text NOT NULL,
            public_key text NOT NULL,
            user_handle varchar(64) NOT NULL,
            label varchar(191) NOT NULL,
            counter bigint(20) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            last_used datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY credential_hash (credential_hash),
            KEY user_id (user_id)
        ) $collation;");
    }

    /** @return array|WP_Error|null */
    public static function identity(string $issuer, string $subject)
    {
        global $wpdb;
        if (self::canonical_issuer($issuer) === 'https://accounts.google.com')
        {
            // Older installations may have stored either exact Google issuer hash.
            $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}lsd_identities WHERE identity_hash IN (%s, %s)", self::identity_hash($issuer, $subject), hash('sha256', wp_json_encode(['accounts.google.com', $subject]))), ARRAY_A);
            // Never choose an owner if both representations were linked before normalization.
            if (count($rows) > 1) return self::conflict();
            return $rows[0] ?? null;
        }
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}lsd_identities WHERE identity_hash = %s", self::identity_hash($issuer, $subject)), ARRAY_A);
    }

    private static function canonical_issuer(string $issuer): string
    {
        return $issuer === 'accounts.google.com' ? 'https://accounts.google.com' : $issuer;
    }

    public static function identity_hash(string $issuer, string $subject): string
    {
        return hash('sha256', wp_json_encode([self::canonical_issuer($issuer), $subject]));
    }

    public static function identities(int $user_id): array
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}lsd_identities WHERE user_id = %d ORDER BY id", $user_id), ARRAY_A);
    }

    public static function link(int $user_id, array $identity)
    {
        global $wpdb;
        $existing = self::identity($identity['issuer'], $identity['subject']);
        if (is_wp_error($existing)) return $existing;
        if ($existing && (int) $existing['user_id'] === $user_id && $existing['provider'] === $identity['provider']) return true;
        if ($existing) return self::conflict();
        foreach (self::identities($user_id) as $linked) if ($linked['provider'] === $identity['provider']) return self::conflict();
        // The unique indexes settle concurrent claims without logging sensitive identity values.
        $previous = $wpdb->suppress_errors(true);
        try
        {
            $saved = $wpdb->insert($wpdb->prefix . 'lsd_identities', [
                'user_id' => $user_id, 'provider' => $identity['provider'],
                'identity_hash' => self::identity_hash($identity['issuer'], $identity['subject']),
                'issuer' => self::canonical_issuer($identity['issuer']), 'subject' => $identity['subject'], 'created_at' => current_time('mysql', true),
            ]);
        }
        finally { $wpdb->suppress_errors($previous); }
        if (!$saved) return self::conflict();
        do_action('lsd_auth_identity_linked', $user_id, $identity['provider']);
        return true;
    }

    private static function conflict(): WP_Error
    {
        return new WP_Error('lsd_identity_conflict', __('This identity or provider is already connected. Sign in to its owner account to manage connections.', 'listdom'));
    }

    public static function passkeys(int $user_id): array
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}lsd_passkeys WHERE user_id = %d ORDER BY id", $user_id), ARRAY_A);
    }

    public static function credential(string $id): ?array
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}lsd_passkeys WHERE credential_hash = %s", hash('sha256', $id)), ARRAY_A);
    }

    public static function delete_user(int $user_id): void
    {
        global $wpdb;
        if ((int) get_option('lsd_db_version', 0) < 4) return;
        $wpdb->delete($wpdb->prefix . 'lsd_identities', ['user_id' => $user_id]);
        $wpdb->delete($wpdb->prefix . 'lsd_passkeys', ['user_id' => $user_id]);
        if (get_userdata($user_id)) update_user_meta($user_id, '_lsd_security_epoch', wp_generate_password(32, false));
    }

    public static function erase(string $email, int $page = 1): array
    {
        $user = get_user_by('email', $email);
        if ($user) self::delete_user($user->ID);
        return ['items_removed' => (bool) $user, 'items_retained' => false, 'messages' => [], 'done' => true];
    }

    public static function export(string $email, int $page = 1): array
    {
        $user = get_user_by('email', $email);
        $data = [];
        if ($user)
        {
            foreach (self::identities($user->ID) as $row) $data[] = ['group_id' => 'listdom-identities', 'group_label' => __('Connected accounts', 'listdom'), 'item_id' => 'listdom-identity-' . $row['id'], 'data' => [['name' => __('Provider', 'listdom'), 'value' => $row['provider']], ['name' => __('Connected', 'listdom'), 'value' => $row['created_at']]]];
            foreach (self::passkeys($user->ID) as $row) $data[] = ['group_id' => 'listdom-passkeys', 'group_label' => __('Passkeys', 'listdom'), 'item_id' => 'listdom-passkey-' . $row['id'], 'data' => [['name' => __('Name', 'listdom'), 'value' => $row['label']], ['name' => __('Created', 'listdom'), 'value' => $row['created_at']]]];
        }
        return ['data' => $data, 'done' => true];
    }
}
