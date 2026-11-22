<?php

class LSD_API_Controller extends LSD_API
{
    protected LSD_API_Validation $validate;

    public function __construct()
    {
        parent::__construct();

        // Validation
        $this->validate = new LSD_API_Validation();
    }

    public function getUserToken($user_id): string
    {
        $token = LSD_Security_API::issue_token((int) $user_id);
        return is_wp_error($token) ? '' : $token;
    }

    public function revokeUserToken($user_id)
    {
        LSD_Security_API::revoke((int) $user_id);
    }

    public function getLoginKey($user_id, ?WP_REST_Request $request = null): string
    {
        if (!$request) return '';
        $key = LSD_Security_API::handoff((int) $user_id, $request);
        return is_wp_error($key) ? '' : $key;
    }

    public function revokeLoginKey($user_id)
    {
        delete_user_meta($user_id, 'lsd_login');
    }

    /** Authentication endpoints ignore stale user tokens so clients can sign in again. */
    public function authentication_permission(WP_REST_Request $request)
    {
        return $this->validate->APIToken($request, $request->get_header('lsd-token')) ? true : new WP_Error('invalid_api_token', __('Invalid API Token!', 'listdom'), ['status' => 403]);
    }

    public function perform(WP_REST_Request $request): WP_REST_Response
    {
        $response = new WP_REST_Response(['success' => 1]);
        $response->set_status(200);

        return $response;
    }

    public function permission(WP_REST_Request $request)
    {
        // Validate API Token
        if (!$this->validate->APIToken($request, $request->get_header('lsd-token'))) return new WP_Error('invalid_api_token', esc_html__('Invalid API Token!', 'listdom'), ['status' => 403]);

        // Validate User Token
        if (!$this->validate->UserToken($request, $request->get_header('lsd-user'))) return new WP_Error('invalid_user_token', esc_html__('Invalid User Token!', 'listdom'), ['status' => 403]);

        return true;
    }

    public function guest(WP_REST_Request $request)
    {
        // Validate API Token
        if (!$this->validate->APIToken($request, $request->get_header('lsd-token'))) return new WP_Error('invalid_api_token', esc_html__('Invalid API Token!', 'listdom'), ['status' => 403]);

        // Set Current User if Token Provided
        $this->validate->UserToken($request, $request->get_header('lsd-user'));

        return true;
    }

    protected function security_error(WP_Error $error): WP_REST_Response
    {
        $data = $error->get_error_data();
        $status = is_array($data) ? ($data['status'] ?? 400) : 400;
        $body = ['success' => 0, 'code' => $error->get_error_code(), 'message' => $error->get_error_message()];
        if (is_array($data) && !empty($data['retry_after'])) $body['retry_after'] = (int) $data['retry_after'];
        $response = $this->response(['data' => $body, 'status' => $status]);
        $response->header('Cache-Control', 'no-store');
        if (is_array($data) && !empty($data['retry_after'])) $response->header('Retry-After', (string) $data['retry_after']);
        return $response;
    }

    protected function credential(WP_REST_Request $request, string $field): string
    {
        $value = $request->get_param($field);
        return is_string($value) ? (string) base64_decode($value, true) : '';
    }

    protected function authentication_response(array $data, int $status = 200): WP_REST_Response
    {
        $response = $this->response(['data' => $data, 'status' => $status]);
        $response->header('Cache-Control', 'no-store');
        return $response;
    }

    public function response(array $response): WP_REST_Response
    {
        $data = $response['data'] ?? [];
        $status = $response['status'] ?? 200;

        $wp = new WP_REST_Response($data);
        $wp->set_status($status);

        return $wp;
    }
}
