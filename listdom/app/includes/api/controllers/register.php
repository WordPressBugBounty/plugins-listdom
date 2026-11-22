<?php

class LSD_API_Controllers_Register extends LSD_API_Controller
{
    public function perform(WP_REST_Request $request): WP_REST_Response
    {
        nocache_headers();
        $vars = $request->get_params();

        $name = $this->credential($request, 'name');
        $email = $this->credential($request, 'email');
        $password = $this->credential($request, 'password');

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

        $username = isset($vars['username']) && is_string($vars['username']) ? sanitize_user($this->credential($request, 'username')) : (is_multisite() ? 'lsd' . bin2hex(random_bytes(8)) : sanitize_user($email));
        $allowed = LSD_Security::registration_check('public', $username, $email);
        if (is_wp_error($allowed)) return $this->security_error($allowed);
        $valid = LSD_Security::password($password);
        if (is_wp_error($valid)) return $this->security_error($valid);
        $role = LSD_Security::registration_role(isset($vars['role']) && is_string($vars['role']) ? sanitize_key($vars['role']) : '');
        if (is_wp_error($role)) return $this->security_error($role);

        $fields = LSD_Portal_Fieldlayout::registration_values($vars, $vars['custom_fields'] ?? [], $role);
        if (is_wp_error($fields)) return $this->security_error($fields);

        // Registration
        $response = LSD_Security::insert_user([
            'display_name' => $name,
            'user_login' => $username,
            'user_email' => sanitize_email($email),
            'user_pass' => $password,
            'role' => $role,
        ] + LSD_Portal_Fieldlayout::registration_user_data($fields));

        // Invalid Credentials
        if (is_wp_error($response)) return $this->response([
            'data' => new WP_Error('400', $response->get_error_message()),
            'status' => 400,
        ]);

        LSD_User::maybe_send_verification_email($response);

        // Trigger Action
        do_action('lsd_api_user_registered', $response, $request);

        if (LSD_User::is_email_verified($response) && LSD_Security_API::protected_account($response))
        {
            $challenge = LSD_Security_API::challenge(get_userdata($response), $request, 'registration');
            if (LSD_Security_API::pending($challenge)) return $this->authentication_response($challenge->get_error_data(), 202);
            return $this->security_error($challenge);
        }
        $token = LSD_User::is_email_verified($response) ? LSD_Security_API::issue_token($response) : '';
        if (is_wp_error($token)) return $this->security_error($token);
        return $this->authentication_response(['success' => 1, 'id' => $response, 'token' => $token]);
    }
}
