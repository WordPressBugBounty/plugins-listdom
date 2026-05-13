<?php

class LSD_User extends LSD_Base
{
    public const META_VERIFICATION_STATUS = 'lsd_email_verification_status';
    public const META_VERIFICATION_TOKEN = 'lsd_email_verification_token';

    public static function requires_email_verification(): bool
    {
        $auth = LSD_Options::auth();
        return !empty($auth['register']['email_verification']);
    }

    public static function is_email_verified(int $user_id): bool
    {
        if (!$user_id) return false;

        if (!self::requires_email_verification()) return true;

        $status = get_user_meta($user_id, self::META_VERIFICATION_STATUS, true);
        if ($status === 'pending') return false;
        if ($status === 'verified') return true;

        // Treat users without a status as verified to preserve backward compatibility.
        return true;
    }

    public static function verification_required_message(): string
    {
        $message = esc_html__('Your account is pending email verification. Please check your inbox to confirm your email address.', 'listdom');
        return apply_filters('lsd_email_verification_required_message', $message);
    }

    public static function verification_success_message(): string
    {
        $message = esc_html__('Your email address has been verified. You can now log in.', 'listdom');
        return apply_filters('lsd_email_verification_success_message', $message);
    }

    public static function verification_failed_message(): string
    {
        $message = esc_html__('Email verification link is invalid or has already been used.', 'listdom');
        return apply_filters('lsd_email_verification_failed_message', $message);
    }

    public static function request_email_verification(int $user_id): string
    {
        if (!$user_id) return '';

        $token = wp_generate_password(32, false);

        update_user_meta($user_id, self::META_VERIFICATION_TOKEN, $token);
        update_user_meta($user_id, self::META_VERIFICATION_STATUS, 'pending');
        update_user_meta($user_id, 'lsd_email_verification_requested', current_time('mysql'));

        return $token;
    }

    public static function verification_url(int $user_id, string $token): string
    {
        $url = add_query_arg([
            'lsd_verify' => $user_id,
            'lsd_key' => $token,
        ], home_url('/'));

        return apply_filters('lsd_email_verification_url', $url, $user_id, $token);
    }

    public static function maybe_send_verification_email(int $user_id)
    {
        if (!$user_id || !self::requires_email_verification()) return;

        $status = get_user_meta($user_id, self::META_VERIFICATION_STATUS, true);
        if ($status === 'verified') return;

        $token = get_user_meta($user_id, self::META_VERIFICATION_TOKEN, true);

        // When a pending token already exists, reuse it to avoid invalidating
        // previously issued verification links (for example, when multiple
        // registration hooks trigger this method).
        if ($status !== 'pending' || !$token)
        {
            $token = self::request_email_verification($user_id);
            if (!$token) return;
        }

        $url = self::verification_url($user_id, $token);

        do_action('lsd_user_email_verification', $user_id, $token, $url);
    }

    public static function verify_email(int $user_id, string $token): bool
    {
        if (!$user_id) return false;

        $token = sanitize_text_field($token);
        if (!$token) return false;

        $saved_token = get_user_meta($user_id, self::META_VERIFICATION_TOKEN, true);
        if (!$saved_token) return false;

        $is_valid = function_exists('hash_equals')
            ? hash_equals($saved_token, $token)
            : $saved_token === $token;

        if (!$is_valid) return false;

        delete_user_meta($user_id, self::META_VERIFICATION_TOKEN);
        update_user_meta($user_id, self::META_VERIFICATION_STATUS, 'verified');
        update_user_meta($user_id, 'lsd_email_verification_time', current_time('mysql'));

        do_action('lsd_user_email_verified', $user_id);

        return true;
    }

    public static function create(string $email)
    {
        $email = sanitize_email($email);

        $exists = email_exists($email);
        if ($exists) return $exists;
        else
        {
            $results = register_new_user($email, $email);
            if (!is_wp_error($results)) return $results;
        }

        return 0;
    }

    public static function register($user_login, $user_email, $password = null)
    {
        // Password
        if (!$password) $password = wp_generate_password();

        // Errors
        $errors = new WP_Error();
        $sanitized_user_login = sanitize_user($user_login);

        /**
         * Filters the email address of a user being registered.
         *
         * @param string $user_email The email address of the new user.
         * @since 2.1.0
         *
         */
        $user_email = apply_filters('user_registration_email', $user_email);

        // Check the username.
        if ('' === $sanitized_user_login) $errors->add('empty_username', wp_kses_post(__('<strong>Error</strong>: Please enter a username.', 'listdom')));
        else if (!validate_username($user_login))
        {
            $errors->add('invalid_username', wp_kses_post(__('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'listdom')));
            $sanitized_user_login = '';
        }
        else if (username_exists($sanitized_user_login)) $errors->add('username_exists', wp_kses_post(__('<strong>Error</strong>: This username is already registered. Please choose another one.', 'listdom')));
        else
        {
            /** This filter is documented in wp-includes/user.php */
            $illegal_user_logins = (array) apply_filters('illegal_user_logins', []);
            if (in_array(strtolower($sanitized_user_login), array_map('strtolower', $illegal_user_logins), true)) $errors->add('invalid_username', wp_kses_post(__('<strong>Error</strong>: Sorry, that username is not allowed.', 'listdom')));
        }

        // Check the email address.
        if ('' === $user_email) $errors->add('empty_email', wp_kses_post(__('<strong>Error</strong>: Please type your email address.', 'listdom')));
        else if (!is_email($user_email))
        {
            $errors->add('invalid_email', wp_kses_post(__('<strong>Error</strong>: The email address isn&#8217;t correct.', 'listdom')));
            $user_email = '';
        }
        else if (email_exists($user_email)) $errors->add('email_exists', wp_kses_post(__('<strong>Error</strong>: This email is already registered. Please choose another one.', 'listdom')));

        /**
         * Fires when submitting registration form data, before the user is created.
         *
         * @param string $sanitized_user_login The submitted username after being sanitized.
         * @param string $user_email The submitted email.
         * @param WP_Error $errors Contains any errors with submitted username and email,
         *                                       e.g., an empty field, an invalid username or email,
         *                                       or an existing username or email.
         * @since 2.1.0
         *
         */
        do_action('register_post', $sanitized_user_login, $user_email, $errors);

        /**
         * Filters the errors encountered when a new user is being registered.
         *
         * The filtered WP_Error object may, for example, contain errors for an invalid
         * or existing username or email address. A WP_Error object should always be returned,
         * but may or may not contain errors.
         *
         * If any errors are present in $errors, this will abort the user's registration.
         *
         * @param WP_Error $errors A WP_Error object containing any errors encountered
         *                                       during registration.
         * @param string $sanitized_user_login User's username after it has been sanitized.
         * @param string $user_email User's email.
         * @since 2.1.0
         *
         */
        $errors = apply_filters('registration_errors', $errors, $sanitized_user_login, $user_email);

        // Return Errors
        if ($errors->has_errors()) return $errors;

        $user_id = wp_create_user($sanitized_user_login, $password, $user_email);
        if (!$user_id || is_wp_error($user_id))
        {
            $errors->add('registerfail', sprintf(
                /* translators: %s: Support email address. */
                wp_kses_post(__('<strong>Error</strong>: Couldn&#8217;t register you&hellip; please contact the <a href="mailto:%s">site admin</a>!', 'listdom')),
                get_option('admin_email')
            ));

            return $errors;
        }

        update_user_option($user_id, 'default_password_nag', true, true); // Set up the password change nag.

        /**
         * Fires after a new user registration has been recorded.
         *
         * @param int $user_id ID of the newly registered user.
         * @since 4.4.0
         *
         */
        do_action('register_new_user', $user_id);

        self::maybe_send_verification_email($user_id);

        return $user_id;
    }

    public static function login($user_id)
    {
        wp_clear_auth_cookie();
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
    }

    /**
     * @param int $listing_id
     * @param string $email
     * @param string $password
     * @param string $fullname
     * @return int|mixed|WP_Error|null
     */
    public static function listing(int $listing_id, string $email, string $password = '', string $fullname = '')
    {
        if (!is_email($email)) return null;

        $user_id = null;
        $exists = email_exists($email);

        if ($exists) $user_id = $exists;
        else
        {
            $password = trim($password) ? sanitize_text_field($password) : wp_generate_password();
            $registered = LSD_User::register($email, $email, $password);

            if (!is_wp_error($registered))
            {
                $user_id = $registered;
                if (trim($fullname))
                {
                    [$first_name, $last_name] = LSD_Main::get_name_parts(sanitize_text_field($fullname));

                    // Update User
                    wp_update_user([
                        'ID' => $user_id,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'display_name' => trim($first_name . ' ' . $last_name),
                    ]);
                }
            }
        }

        // Assign Listing to new Owner
        LSD_Main::assign($listing_id, $user_id);

        // User ID
        return $user_id;
    }

    public static function get_user_avatar($id_or_email = null, $size = 96)
    {
        if (!$id_or_email)
        {
            $user_id = get_current_user_id();
            if (!$user_id) return '';

            $id_or_email = $user_id;
        }

        $user_id = 0;

        if (is_numeric($id_or_email)) $user_id = (int) $id_or_email;
        else if (is_object($id_or_email) && isset($id_or_email->ID)) $user_id = (int) $id_or_email->ID;
        else if (is_string($id_or_email) && is_email($id_or_email))
        {
            $user = get_user_by('email', $id_or_email);
            if ($user) $user_id = $user->ID;
        }

        if ($user_id)
        {
            $profile_image_id = get_user_meta($user_id, 'lsd_profile_image', true);
            if ($profile_image_id)
            {
                $profile_image_url = wp_get_attachment_url($profile_image_id);
                if ($profile_image_url) return '<img src="' . esc_url($profile_image_url) . '" class="avatar avatar-' . esc_attr($size) . ' photo" height="' . esc_attr($size) . '" width="' . esc_attr($size) . '" alt="' . esc_attr__('Profile Image', 'listdom') . '">';
            }
        }

        return get_avatar($id_or_email, $size);
    }

    public static function get_user_image_url(int $user_id, string $meta_key): string
    {
        if (!$user_id || !$meta_key) return '';

        $attachment_id = (int) get_user_meta($user_id, $meta_key, true);
        if (!$attachment_id) return '';

        $url = wp_get_attachment_url($attachment_id);
        if ($url) return $url;

        return '';
    }

    public static function get_user_info($username = ''): array
    {
        if ($username)
        {
            if (is_numeric($username)) $user = get_user_by('ID', $username);
            else $user = get_user_by('login', $username);
        }
        else
        {
            $user_id = get_current_user_id();
            if (!$user_id) return [];

            $user = get_userdata($user_id);
        }

        if (!($user instanceof WP_User)) return [];

        $meta_keys = [
            'lsd_profile_image' => 'profile_image',
            'lsd_hero_image' => 'hero_image',
            'lsd_job_title' => 'job_title',
            'description' => 'bio',
            'lsd_phone' => 'phone',
            'lsd_mobile' => 'mobile',
            'lsd_website' => 'website',
            'lsd_fax' => 'fax',
            'lsd_facebook' => 'facebook',
            'lsd_twitter' => 'twitter',
            'lsd_pinterest' => 'pinterest',
            'lsd_linkedin' => 'linkedin',
            'lsd_instagram' => 'instagram',
            'lsd_whatsapp' => 'whatsapp',
            'lsd_youtube' => 'youtube',
            'lsd_tiktok' => 'tiktok',
            'lsd_telegram' => 'telegram',
        ];

        $user_meta = [];
        foreach ($meta_keys as $meta_key => $field_name)
        {
            $user_meta[$field_name] = get_user_meta($user->ID, $meta_key, true) ?: '';
        }

        return array_merge([
            'ID' => $user->ID,
            'display_name' => $user->display_name,
            'email' => $user->user_email,
            'description' => $user->description,
            'first_name' => $user->first_name ?: '',
            'last_name' => $user->last_name ?: '',
        ], $user_meta);
    }

    public static function profile_edit_fields_defaults(): array
    {
        $social = [];
        foreach (self::profile_social_networks() as $network) $social[$network] = 1;

        return [
            'job_title' => 1,
            'bio' => 1,
            'profile_image' => 1,
            'hero_image' => 1,
            'email' => 1,
            'phone' => 1,
            'mobile' => 1,
            'website' => 1,
            'fax' => 1,
            'social' => $social,
        ];
    }

    public static function profile_social_networks(): array
    {
        $networks = [
            'facebook',
            'twitter',
            'pinterest',
            'linkedin',
            'instagram',
            'whatsapp',
            'youtube',
            'tiktok',
            'telegram',
        ];

        $socials = LSD_Options::socials();
        foreach ($socials as $network => $options)
        {
            if (!is_string($network) || trim($network) === '') continue;
            if (!in_array($network, $networks, true)) $networks[] = $network;
        }

        return $networks;
    }

    public static function profile_edit_fields(): array
    {
        $auth = LSD_Options::auth();
        $fields = isset($auth['profile']['fields']) && is_array($auth['profile']['fields']) ? $auth['profile']['fields'] : [];
        $defaults = self::profile_edit_fields_defaults();

        $fields = self::parse_args($fields, $defaults);
        foreach (['job_title', 'bio', 'profile_image', 'hero_image', 'email', 'phone', 'mobile', 'website', 'fax'] as $field)
        {
            $fields[$field] = isset($fields[$field]) && (int) $fields[$field] === 0 ? 0 : 1;
        }

        $social_defaults = $defaults['social'];
        $legacy_socials = isset($fields['socials']) && (int) $fields['socials'] === 0 ? 0 : 1;
        $social = isset($fields['social']) && is_array($fields['social']) ? $fields['social'] : [];
        $social = self::parse_args($social, $social_defaults);

        foreach ($social_defaults as $network => $enabled)
        {
            $social[$network] = ($legacy_socials === 0 || (isset($social[$network]) && (int) $social[$network] === 0)) ? 0 : 1;
        }

        $fields['social'] = $social;
        unset($fields['socials']);

        return $fields;
    }

    public static function is_profile_edit_field_enabled(string $field): bool
    {
        $fields = self::profile_edit_fields();
        if (!array_key_exists($field, $fields)) return true;

        return (int) $fields[$field] === 1;
    }

    public static function is_profile_social_field_enabled(string $network): bool
    {
        $fields = self::profile_edit_fields();

        if (!isset($fields['social']) || !is_array($fields['social'])) return true;
        if (!array_key_exists($network, $fields['social'])) return true;

        return (int) $fields['social'][$network] === 1;
    }

    public static function has_enabled_profile_social_fields(): bool
    {
        $fields = self::profile_edit_fields();
        if (!isset($fields['social']) || !is_array($fields['social'])) return true;

        foreach ($fields['social'] as $enabled)
        {
            if ((int) $enabled === 1) return true;
        }

        return false;
    }

    public static function send_forgot_password_email(WP_User $user): bool
    {
        // Generate password reset key and link
        $reset_key = get_password_reset_key($user);
        if (is_wp_error($reset_key)) return false;

        $reset_link = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');

        // Send password reset email
        $message = esc_html__('Someone has requested a password reset for the following account:', 'listdom') . "\r\n\r\n";
        $message .= network_home_url('/') . "\r\n\r\n";
        $message .= sprintf(
            /* translators: %s: Username for the account. */
            esc_html__('Username: %s', 'listdom'),
            $user->user_login
        ) . "\r\n\r\n";
        $message .= esc_html__('If this was a mistake, just ignore this email and nothing will happen.', 'listdom') . "\r\n\r\n";
        $message .= esc_html__('To reset your password, visit the following address:', 'listdom') . "\r\n\r\n";
        $message .= '<a href="' . esc_url($reset_link) . '">' . esc_url($reset_link) . '</a>' . "\r\n";

        return wp_mail($user->user_email, esc_html__('Password Reset Request', 'listdom'), $message);
    }

    public static function profile_link(int $id): string
    {
        $auth = LSD_Options::auth();

        $profile_page_id = isset($auth['profile']['page']) && $auth['profile']['page'] ? $auth['profile']['page'] : 0;
        $profile_page = $profile_page_id ? get_post($profile_page_id) : null;

        // Listdom Profile Page
        if ($profile_page instanceof WP_Post && $profile_page->post_status === 'publish')
        {
            // User
            $user = get_user($id);

            if ($user && isset($user->user_login))
            {
                // URL Structure
                $structure = get_option('permalink_structure');

                // URL
                $url = get_permalink($profile_page);

                // Main
                $main = new LSD_Main();

                $slug = $user->user_login;
                if (is_email($slug)) $slug = $user->ID;

                // Generate URL
                if ($structure) return trim($url, '/ ') . '/' . sanitize_title($slug);
                else return $main->add_qs_var('user', sanitize_title($slug), $url);
            }
        }

        return get_author_posts_url($id);
    }

    public static function roles(bool $only_keys = false): array
    {
        return LSD_Roles::supported($only_keys);
    }
}
