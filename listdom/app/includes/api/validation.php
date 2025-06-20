<?php

class LSD_API_Validation extends LSD_API
{
    public function APIToken(WP_REST_Request $request, $token = ''): bool
    {
        // Check Token
        if (!is_null($token) && trim($token))
        {
            $api = LSD_Options::api();

            $tokens = [];
            foreach ($api['tokens'] as $t)
            {
                if (!isset($t['key'])) continue;
                $tokens[] = $t['key'];
            }

            if (in_array($token, $tokens)) return true;
        }

        return false;
    }

    public function UserToken(WP_REST_Request $request, $token = ''): bool
    {
        // Check User
        if (!is_null($token) && trim($token))
        {
            $user_id = $this->db->select("SELECT `user_id` FROM `#__usermeta` WHERE `meta_key`='lsd_token' AND `meta_value`='" . esc_sql($token) . "'", 'loadResult');
            if (!$user_id) return false;

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
