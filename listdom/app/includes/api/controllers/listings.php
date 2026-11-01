<?php

class LSD_API_Controllers_Listings extends LSD_API_Controller
{
    public function get(WP_REST_Request $request): WP_REST_Response
    {
        $id = $request->get_param('id');

        // Listing
        $listing = get_post($id);

        // Guard: stop early if the listing post can't be loaded.
        if (!($listing instanceof WP_Post) || $listing->post_type !== LSD_Base::PTYPE_LISTING) return $this->response([
            'data' => new WP_Error('404', esc_html__('Listing not found!', 'listdom')),
            'status' => 404,
        ]);

        // Update Listing Visits
        (new LSD_Entity_Listing($listing))->update_visits();

        // Trigger Action
        do_action('lsd_listing_visited', $listing, 'rest');

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'listing' => LSD_API_Resources_Listing::get($id),
            ],
            'status' => 200,
        ]);
    }

    public function create(WP_REST_Request $request): WP_REST_Response
    {
        // The REST route already requires a valid Listdom user token; preserve
        // its existing submission permissions for signed-in directory users.
        $result = (new LSD_Listings_Service())->create($request->get_params(), false);
        if (is_wp_error($result)) return $this->service_error($result);

        do_action('lsd_api_listing_created', $result['id'], $request);

        // Response
        return $this->response(['data' => [
            'success' => 1,
            'message' => $result['message'],
            'listing' => LSD_API_Resources_Listing::get($result['id']),
        ], 'status' => 200]);
    }

    public function edit(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');
        $result = (new LSD_Listings_Service())->update($id, $request->get_params());
        if (is_wp_error($result)) return $this->service_error($result);

        do_action('lsd_api_listing_updated', $id, $request);

        // Response
        return $this->response(['data' => [
            'success' => 1,
            'listing' => LSD_API_Resources_Listing::get($id),
        ], 'status' => 200]);
    }

    public function trash(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');
        $result = (new LSD_Listings_Service())->trash($id);
        if (is_wp_error($result)) return $this->service_error($result);

        do_action('lsd_api_listing_trashed', $id, $request);

        // Response
        return $this->response(['data' => ['success' => 1], 'status' => 200]);
    }

    public function delete(WP_REST_Request $request): WP_REST_Response
    {
        $id = $request->get_param('id');

        // Current User is not Authorized to Delete this Listing
        if (!current_user_can('delete_post', $id)) return $this->response([
            'data' => new WP_Error('401', esc_html__("You're not authorized to delete this listing!", 'listdom')),
            'status' => 401,
        ]);

        // Listing
        $listing = get_post($id);

        // Guard: stop early if the listing post can't be loaded.
        if (!($listing instanceof WP_Post) || $listing->post_type !== LSD_Base::PTYPE_LISTING) return $this->response([
            'data' => new WP_Error('404', esc_html__('Listing not found!', 'listdom')),
            'status' => 404,
        ]);

        // Delete
        wp_delete_post($id, true);

        // Trigger Action
        do_action('lsd_api_listing_deleted', $id, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
            ],
            'status' => 200,
        ]);
    }

    public function contact(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');
        $result = (new LSD_Listings_Service())->contact($id, $request->get_params());
        if (is_wp_error($result)) return $this->service_error($result);

        do_action('lsd_api_contact_owner', $id, $request);

        // Response
        return $this->response(['data' => ['success' => 1], 'status' => 200]);
    }

    public function abuse(WP_REST_Request $request): WP_REST_Response
    {
        $id = $request->get_param('id');

        // Listing
        $listing = get_post($id);

        // Guard: stop early if the listing post can't be loaded.
        if (!($listing instanceof WP_Post) || $listing->post_type !== LSD_Base::PTYPE_LISTING) return $this->response([
            'data' => new WP_Error('404', esc_html__('Listing not found!', 'listdom')),
            'status' => 404,
        ]);

        $name = $request->get_param('name');
        $email = sanitize_email($request->get_param('email'));
        $phone = $request->get_param('phone');
        $message = $request->get_param('message');

        // Required
        if (!trim($name) || !trim($email) || !trim($phone) || !trim($message)) return $this->response([
            'data' => new WP_Error('400', esc_html__("Name, Email, Phone and Message fields are required!", 'listdom')),
            'status' => 400,
        ]);

        // Email is not valid
        if (!is_email($email)) return $this->response([
            'data' => new WP_Error('400', esc_html__("Email is not valid!", 'listdom')),
            'status' => 400,
        ]);

        // Trigger Notification
        do_action('lsd_listing_report_abuse', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'post_id' => $id,
        ]);

        // Trigger Action
        do_action('lsd_api_listing_report_abuse', $id, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
            ],
            'status' => 200,
        ]);
    }

    public function fields(): WP_REST_Response
    {
        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'form' => LSD_API_Resources_Fields::grab(),
            ],
            'status' => 200,
        ]);
    }

    /** Translate a listing service error to its REST status. */
    private function service_error(WP_Error $error): WP_REST_Response
    {
        $status = 400;
        if ($error->get_error_code() === 'listdom_forbidden') $status = 401;
        if ($error->get_error_code() === 'listdom_listing_not_found') $status = 404;

        // Keep the numeric error codes exposed by the existing REST routes.
        if (in_array($error->get_error_code(), [
            'listdom_forbidden', 'listdom_listing_not_found', 'listdom_invalid_title',
            'listdom_invalid_contact', 'listdom_invalid_email', 'listdom_trash_failed',
            'listdom_invalid_coordinates', 'listdom_listing_locked',
        ], true)) $error = new WP_Error((string) $status, $error->get_error_message());

        // Response
        return $this->response(['data' => $error, 'status' => $status]);
    }
}
