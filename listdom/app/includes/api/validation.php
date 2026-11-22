<?php

class LSD_API_Validation extends LSD_API
{
    public function APIToken(WP_REST_Request $request, $token = ''): bool
    {
        return is_string($token) && LSD_Security_API::api_key_valid($token);
    }

    public function UserToken(WP_REST_Request $request, $token = ''): bool
    {
        // Check User
        if (is_string($token) && $token !== '')
        {
            global $wpdb;
            $user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1", 'lsd_token', $token));
            if (!$user_id || !LSD_Security_API::valid_token((int) $user_id, $token)) return false;

            // Set Current User
            wp_set_current_user($user_id);
            return true;
        }

        return false;
    }

    public function numeric($param, $request, $key): bool
    {
        return is_numeric($param);
    }
}
