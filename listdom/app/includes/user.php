<?php

class LSD_User extends LSD_Base
{
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
        if ('' === $sanitized_user_login) $errors->add('empty_username', __('<strong>Error</strong>: Please enter a username.'));
        else if (!validate_username($user_login))
        {
            $errors->add('invalid_username', __('<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.'));
            $sanitized_user_login = '';
        }
        else if (username_exists($sanitized_user_login)) $errors->add('username_exists', __('<strong>Error</strong>: This username is already registered. Please choose another one.'));
        else
        {
            /** This filter is documented in wp-includes/user.php */
            $illegal_user_logins = (array) apply_filters('illegal_user_logins', []);
            if (in_array(strtolower($sanitized_user_login), array_map('strtolower', $illegal_user_logins), true)) $errors->add('invalid_username', __('<strong>Error</strong>: Sorry, that username is not allowed.'));
        }

        // Check the email address.
        if ('' === $user_email) $errors->add('empty_email', __('<strong>Error</strong>: Please type your email address.'));
        else if (!is_email($user_email))
        {
            $errors->add('invalid_email', __('<strong>Error</strong>: The email address isn&#8217;t correct.'));
            $user_email = '';
        }
        else if (email_exists($user_email)) $errors->add('email_exists', __('<strong>Error</strong>: This email is already registered. Please choose another one.'));

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
                __('<strong>Error</strong>: Couldn&#8217;t register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
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

        return get_avatar($id_or_email, $size);
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

    public static function send_forgot_password_email(WP_User $user): bool
    {
        // Generate password reset key and link
        $reset_key = get_password_reset_key($user);
        if (is_wp_error($reset_key)) return false;

        $reset_link = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');

        // Send password reset email
        $message = __('Someone has requested a password reset for the following account:', 'listdom') . "\r\n\r\n";
        $message .= network_home_url('/') . "\r\n\r\n";
        $message .= sprintf(__('Username: %s', 'listdom'), $user->user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'listdom') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:', 'listdom') . "\r\n\r\n";
        $message .= '<a href="' . esc_url($reset_link) . '">' . esc_url($reset_link) . '</a>' . "\r\n";

        return wp_mail($user->user_email, __('Password Reset Request', 'listdom'), $message);
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
}
