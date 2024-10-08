<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Logout Controller Class.
 *
 * @class LSD_API_Controllers_Logout
 * @version    1.0.0
 */
class LSD_API_Controllers_Logout extends LSD_API_Controller
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
        $user_id = get_current_user_id();

        // Revoke User Token
        $this->revokeUserToken($user_id);

        // Trigger Action
        do_action('lsd_api_user_logged_out', $user_id, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
            ],
            'status' => 200,
        ]);
    }
}
