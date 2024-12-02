<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Addons Controller Class.
 *
 * @class LSD_API_Controllers_Addons
 * @version    1.0.0
 */
class LSD_API_Controllers_Addons extends LSD_API_Controller
{
    public function get(WP_REST_Request $request): WP_REST_Response
    {
        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'addons' => LSD_API_Resources_Addon::all(),
            ],
            'status' => 200,
        ]);
    }
}
