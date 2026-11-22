<?php

class LSD_API_Controllers_Login extends LSD_API_Controller
{
    public function perform(WP_REST_Request $request): WP_REST_Response
    {
        $user = LSD_Security_API::authenticate($request, $this->credential($request, 'username'), $this->credential($request, 'password'));
        if (is_wp_error($user)) return $this->result($user);
        return $this->result(LSD_Security_API::complete($user, $request));
    }

    public function challenge(WP_REST_Request $request): WP_REST_Response
    {
        return $this->result(LSD_Security_API::finish($request));
    }

    public function enroll(WP_REST_Request $request): WP_REST_Response
    {
        return $this->result(LSD_Security_API::enroll($request));
    }

    public function confirm_enrollment(WP_REST_Request $request): WP_REST_Response
    {
        return $this->result(LSD_Security_API::finish($request, true));
    }

    private function result($result): WP_REST_Response
    {
        nocache_headers();
        if (LSD_Security_API::pending($result)) return $this->authentication_response($result->get_error_data(), 202);
        if (is_wp_error($result)) return $this->security_error($result);
        return $this->authentication_response($result);
    }

    public function key(WP_REST_Request $request): WP_REST_Response
    {
        nocache_headers();
        $key = LSD_Security_API::handoff(get_current_user_id(), $request);
        if (is_wp_error($key)) return $this->security_error($key);
        return $this->authentication_response(['success' => 1, 'key' => $key]);
    }

    public function redirect(WP_REST_Request $request)
    {
        nocache_headers();
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        $key = $request->get_param('key');
        $data = LSD_Security_API::consume_handoff(is_string($key) ? $key : '');
        if (is_wp_error($data)) return $this->security_error($data);
        $user = get_userdata($data['user']);
        $requested = $request->get_param('redirect_url');
        $redirect = wp_validate_redirect(is_string($requested) ? wp_sanitize_redirect(urldecode($requested)) : '', home_url('/'));
        if (LSD_Security_API::protected_account($user->ID) && !LSD_Security_API::recent($data))
        {
            $challenge = LSD_Security_Challenge::issue_browser($user, $redirect, false, $data);
            if ($challenge->get_error_code() !== 'lsd_totp_required') return $this->security_error($challenge);
            wp_safe_redirect(LSD_Security_Challenge::url($challenge));
            exit;
        }
        // A newly protected or revoked account must not inherit a previously minted key.
        if ((!empty($data['legacy']) && LSD_Security_API::protected_account($user->ID)) || (empty($data['legacy']) && !LSD_Security_API::valid_handoff_source($data))) return $this->security_error(new WP_Error('lsd_handoff_invalid', __('Please sign in again.', 'listdom'), ['status' => 403]));
        wp_set_current_user($user->ID, $user->user_login);
        wp_set_auth_cookie($user->ID, false, is_ssl());
        do_action('wp_login', $user->user_login, $user);
        wp_safe_redirect($redirect);
        exit;
    }
}
