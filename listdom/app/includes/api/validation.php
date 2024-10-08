<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Validation Class.
 *
 * @class LSD_API_Validation
 * @version    1.0.0
 */
class LSD_API_Validation extends LSD_API
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function APIToken(WP_REST_Request $request, $token = null): bool
    {
        // Check Token
        if (trim($token))
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

    public function UserToken(WP_REST_Request $request, $token = null): bool
    {
        // Check User
        if (trim($token))
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
