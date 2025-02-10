<?php
// no direct access
defined('ABSPATH') || die();

class LSD_API_Controllers_Register extends LSD_API_Controller
{
    public function perform(WP_REST_Request $request): WP_REST_Response
    {
        $vars = $request->get_params();

        $name = isset($vars['name']) ? base64_decode($vars['name']) : '';
        $email = isset($vars['email']) ? base64_decode($vars['email']) : '';
        $password = isset($vars['password']) ? base64_decode($vars['password']) : '';

        // Required Data
        if (trim($email) === '' || trim($password) === '') return $this->response([
            'data' => new WP_Error('400', esc_html__('Email and Password are required!', 'listdom')),
            'status' => 400,
        ]);

        // Invalid Email
        if (!is_email($email)) return $this->response([
            'data' => new WP_Error('400', esc_html__('Email is not valid!', 'listdom')),
            'status' => 400,
        ]);

        // Password is too Short
        if (strlen($password) < 6) return $this->response([
            'data' => new WP_Error('400', esc_html__('Password should be at-least 6 characters!', 'listdom')),
            'status' => 400,
        ]);

        // Registration
        $response = wp_insert_user([
            'display_name' => $name,
            'user_login' => sanitize_user($email),
            'user_email ' => sanitize_email($email),
            'user_pass' => $password,
        ]);

        // Invalid Credentials
        if (is_wp_error($response)) return $this->response([
            'data' => new WP_Error('400', $response->get_error_message()),
            'status' => 400,
        ]);

        // Trigger Action
        do_action('lsd_api_user_registered', $response, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'id' => $response,
                'token' => $this->getUserToken($response),
            ],
            'status' => 200,
        ]);
    }
}
