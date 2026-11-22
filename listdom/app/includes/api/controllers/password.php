<?php

class LSD_API_Controllers_Password extends LSD_API_Controller
{
    public function update(WP_REST_Request $request): WP_REST_Response
    {
        $password = $request->get_param('password');
        $password_confirmation = $request->get_param('password_confirmation');

        $valid = LSD_Security::password(is_string($password) ? $password : '', is_string($password_confirmation) ? $password_confirmation : '');
        if (is_wp_error($valid)) return $this->security_error($valid);
        // Sensitive changes require the same fresh second factor as the frontend.
        $current = $request->get_param('current_password');
        if (!is_string($current) || $current === '') return $this->security_error(new WP_Error('lsd_current_password_required', __('Enter your current password to change it.', 'listdom'), ['status' => 400]));
        $code = $request->get_param('code');
        $auth = LSD_Security::reauthenticate(get_current_user_id(), is_string($current) ? $current : '', is_string($code) ? $code : '');
        if (is_wp_error($auth)) return $this->security_error($auth);

        // Update Password
        wp_set_password($password, get_current_user_id());

        LSD_Security::revoke_credentials(get_current_user_id());

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

    public function forgot(WP_REST_Request $request): WP_REST_Response
    {
        $username = $request->get_param('username');
        $result = LSD_Security::recover_password(is_string($username) ? $username : '');
        if (is_wp_error($result)) return $this->security_error($result);
        return $this->response(['data' => ['success' => 1, 'message' => LSD_Security::recovery_message()], 'status' => 200]);
    }
}
