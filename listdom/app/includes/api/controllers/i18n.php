<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API I18n Controller Class.
 *
 * @class LSD_API_Controllers_I18n
 * @version    1.0.0
 */
class LSD_API_Controllers_I18n extends LSD_API_Controller
{
    public function languages(WP_REST_Request $request): WP_REST_Response
    {
        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'languages' => LSD_i18n::languages(),
            ],
            'status' => 200,
        ]);
    }
}
