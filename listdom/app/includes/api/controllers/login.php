<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Login Controller Class.
 *
 * @class LSD_API_Controllers_Login
 * @version    1.0.0
 */
class LSD_API_Controllers_Login extends LSD_API_Controller
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function perform(WP_REST_Request $request)
    {
        $vars = $request->get_params();

        $username = isset($vars['username']) ? base64_decode($vars['username']) : null;
        $password = isset($vars['password']) ? base64_decode($vars['password']) : null;

        // Login
        $response = wp_signon([
            'user_login' => $username,
            'user_password' => $password,
            'remember' => false,
        ], is_ssl());

        // Invalid Credentials
        if (is_wp_error($response)) return $response;

        // Trigger Action
        do_action('lsd_api_user_loggedin', $response->ID, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'id' => $response->ID,
                'token' => $this->getUserToken($response->ID),
            ],
            'status' => 200,
        ]);
    }

    public function key(WP_REST_Request $request)
    {
        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'key' => $this->getLoginKey(get_current_user_id()),
            ],
            'status' => 200,
        ]);
    }

    public function redirect(WP_REST_Request $request)
    {
        // Login Key
        $key = $request->get_param('key');

        // Search Users
        $users = get_users([
            'meta_key' => 'lsd_login',
            'meta_value' => $key,
            'number' => 1,
            'count_total' => false,
        ]);

        // User
        $user = reset($users);

        // Not Found!
        if (!$user || !$user->ID) return $this->response([
            'data' => new WP_Error('404', esc_html__('User not found!', 'listdom')),
            'status' => 404,
        ]);

        // Login
        wp_set_current_user($user->ID, $user->user_login);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->user_login, $user);

        // Redirection URL
        $redirect_url = urldecode($request->get_param('redirect_url'));
        if (!trim($redirect_url)) $redirect_url = get_home_url();

        wp_redirect($redirect_url);
        exit;
    }
}
