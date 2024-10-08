<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Forgot Controller Class.
 *
 * @class LSD_API_Controllers_Password
 * @version    1.0.0
 */
class LSD_API_Controllers_Password extends LSD_API_Controller
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function update(WP_REST_Request $request)
    {
        $password = $request->get_param('password');
        $password_confirmation = $request->get_param('password_confirmation');

        // Password is Too Short
        if (strlen($password) < 6) return $this->response([
            'data' => new WP_Error('400', esc_html__("Password is too short! It should be at-least 6 characters.", 'listdom')),
            'status' => 400,
        ]);

        // Password do not Match
        if ($password != $password_confirmation) return $this->response([
            'data' => new WP_Error('400', esc_html__("Password do not match with its confirmation.", 'listdom')),
            'status' => 400,
        ]);

        // Update Password
        wp_set_password($password, get_current_user_id());

        // Trigger Action
        do_action('lsd_api_user_password_changed', get_current_user_id(), $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
            ],
            'status' => 200,
        ]);
    }

    public function forgot(WP_REST_Request $request)
    {
        $username = $request->get_param('username');
        $response = $this->send($username);

        // Invalid Username
        if (is_wp_error($response)) return $response;

        // Response
        return $this->response([
            'data' => [
                'success' => (int) $response,
            ],
            'status' => 200,
        ]);
    }

    public function send($username)
    {
        $user = null;
        $errors = new WP_Error();

        if (empty($username) or !is_string($username))
        {
            $errors->add('empty_username', esc_html__('Enter a username or email address.', 'listdom'));
        }
        else if (strpos($username, '@'))
        {
            $user = get_user_by('email', sanitize_email(trim(wp_unslash($username))));
            if (empty($user)) $errors->add('invalid_email', esc_html__('There is no account with that username or email address.', 'listdom'));
        }
        else
        {
            $login = trim($username);
            $user = get_user_by('login', $login);
        }

        if ($errors->has_errors()) return $errors;

        if (!$user)
        {
            $errors->add('invalidcombo', esc_html__('There is no account with that username or email address.', 'listdom'));
            return $errors;
        }

        $user_login = $user->user_login;
        $user_email = $user->user_email;
        $key = get_password_reset_key($user);

        if (is_wp_error($key)) return $key;

        if (is_multisite()) $site_name = get_network()->site_name;
        else $site_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $message = esc_html__('Someone has requested a password reset for the following account:') . "\r\n\r\n";
        $message .= sprintf(esc_html__('Site Name: %s'), $site_name) . "\r\n\r\n";
        $message .= sprintf(esc_html__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= esc_html__('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
        $message .= esc_html__('To reset your password, visit the following address:') . "\r\n\r\n";
        $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

        $title = sprintf(esc_html__('[%s] Password Reset'), $site_name);
        $title = apply_filters('retrieve_password_title', $title, $user_login, $user);
        $message = apply_filters('retrieve_password_message', $message, $key, $user_login, $user);

        return wp_mail($user_email, wp_specialchars_decode($title), $message);
    }
}
