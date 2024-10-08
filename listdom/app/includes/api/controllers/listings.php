<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Listings Controller Class.
 *
 * @class LSD_API_Controllers_Listings
 * @version    1.0.0
 */
class LSD_API_Controllers_Listings extends LSD_API_Controller
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function get(WP_REST_Request $request)
    {
        $id = $request->get_param('id');

        // Listing
        $listing = get_post($id);

        // Not Found!
        if (!$listing || (isset($listing->post_type) && $listing->post_type !== LSD_Base::PTYPE_LISTING)) return $this->response([
            'data' => new WP_Error('404', esc_html__('Listing not found!', 'listdom')),
            'status' => 404,
        ]);

        // Update Listing Visits
        (new LSD_Entity_Listing($listing))->update_visits();

        // Trigger Action
        do_action('lsd_listing_visited', $listing);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'listing' => LSD_API_Resources_Listing::get($id),
            ],
            'status' => 200,
        ]);
    }

    public function create(WP_REST_Request $request)
    {
        $vars = $request->get_params();

        $tax = (isset($vars['taxonomies']) && is_array($vars['taxonomies'])) ? $vars['taxonomies'] : [];
        $post_title = isset($vars['title']) ? sanitize_text_field($vars['title']) : '';
        $post_content = $vars['content'] ?? '';

        // Listing Title is Required
        if (!trim($post_title)) return $this->response([
            'data' => new WP_Error('400', esc_html__("Listing title field is required!", 'listdom')),
            'status' => 400,
        ]);

        // Post Status
        $status = 'pending';
        if (current_user_can('publish_posts')) $status = 'publish';

        // Filter Listing Status
        $status = apply_filters('lsd_default_listing_status', $status, $vars);
        $status = apply_filters('lsd_api_listing_status', $status, $vars);

        // Create New Listing
        $post = ['post_title' => $post_title, 'post_content' => $post_content, 'post_type' => LSD_Base::PTYPE_LISTING, 'post_status' => $status];
        $id = wp_insert_post($post);

        // Tags
        $tags = isset($tax[LSD_Base::TAX_TAG]) ? sanitize_text_field($tax[LSD_Base::TAX_TAG]) : '';
        wp_set_post_terms($id, $tags, LSD_Base::TAX_TAG);

        // Locations
        $locations = (isset($tax[LSD_Base::TAX_LOCATION]) and is_array($tax[LSD_Base::TAX_LOCATION])) ? $tax[LSD_Base::TAX_LOCATION] : [];
        wp_set_post_terms($id, $locations, LSD_Base::TAX_LOCATION);

        // Features
        $features = (isset($tax[LSD_Base::TAX_FEATURE]) and is_array($tax[LSD_Base::TAX_FEATURE])) ? $tax[LSD_Base::TAX_FEATURE] : [];
        wp_set_post_terms($id, LSD_Taxonomies::name($features, LSD_Base::TAX_FEATURE), LSD_Base::TAX_FEATURE);

        // Labels
        $labels = (isset($tax[LSD_Base::TAX_LABEL]) and is_array($tax[LSD_Base::TAX_LABEL])) ? $tax[LSD_Base::TAX_LABEL] : [];
        wp_set_post_terms($id, LSD_Taxonomies::name($labels, LSD_Base::TAX_LABEL), LSD_Base::TAX_LABEL);

        // Featured Image
        $featured_image = isset($vars['featured_image']) ? sanitize_text_field($vars['featured_image']) : '';
        set_post_thumbnail($id, $featured_image);

        // Publish Listing
        if ($status === 'publish' and get_post_status($id) !== 'published') wp_publish_post($id);

        // Sanitization
        array_walk_recursive($vars, 'sanitize_text_field');

        // Save the Data
        $entity = new LSD_Entity_Listing($id);
        $entity->save($vars, true);

        if ($status === 'publish') $message = esc_html__('The listing published.', 'listdom');
        else $message = esc_html__('The listing submitted. It will publish as soon as possible.', 'listdom');

        // Trigger Action
        do_action('lsd_api_listing_created', $id, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'message' => $message,
                'listing' => LSD_API_Resources_Listing::get($id),
            ],
            'status' => 200,
        ]);
    }

    public function edit(WP_REST_Request $request)
    {
        $id = $request->get_param('id');

        // Current User is not Authorized to Edit this Listing
        if (!current_user_can('edit_post', $id)) return $this->response([
            'data' => new WP_Error('401', esc_html__("You're not authorized to edit this listing!", 'listdom')),
            'status' => 401,
        ]);

        // Listing
        $listing = get_post($id);

        // Not Found!
        if (!$listing || (isset($listing->post_type) && $listing->post_type !== LSD_Base::PTYPE_LISTING)) return $this->response([
            'data' => new WP_Error('404', esc_html__('Listing not found!', 'listdom')),
            'status' => 404,
        ]);

        $vars = $request->get_params();

        $tax = isset($vars['taxonomies']) && is_array($vars['taxonomies']) ? $vars['taxonomies'] : [];
        $post_title = isset($vars['title']) ? sanitize_text_field($vars['title']) : '';
        $post_content = $vars['content'] ?? '';

        // Listing Title is Required
        if (!trim($post_title)) return $this->response([
            'data' => new WP_Error('400', esc_html__("Listing title field is required!", 'listdom')),
            'status' => 400,
        ]);

        wp_update_post(['ID' => $id, 'post_title' => $post_title, 'post_content' => $post_content]);

        // Tags
        $tags = isset($tax[LSD_Base::TAX_TAG]) ? sanitize_text_field($tax[LSD_Base::TAX_TAG]) : '';
        wp_set_post_terms($id, $tags, LSD_Base::TAX_TAG);

        // Locations
        $locations = (isset($tax[LSD_Base::TAX_LOCATION]) and is_array($tax[LSD_Base::TAX_LOCATION])) ? $tax[LSD_Base::TAX_LOCATION] : [];
        wp_set_post_terms($id, $locations, LSD_Base::TAX_LOCATION);

        // Features
        $features = (isset($tax[LSD_Base::TAX_FEATURE]) and is_array($tax[LSD_Base::TAX_FEATURE])) ? $tax[LSD_Base::TAX_FEATURE] : [];
        wp_set_post_terms($id, LSD_Taxonomies::name($features, LSD_Base::TAX_FEATURE), LSD_Base::TAX_FEATURE);

        // Labels
        $labels = (isset($tax[LSD_Base::TAX_LABEL]) and is_array($tax[LSD_Base::TAX_LABEL])) ? $tax[LSD_Base::TAX_LABEL] : [];
        wp_set_post_terms($id, LSD_Taxonomies::name($labels, LSD_Base::TAX_LABEL), LSD_Base::TAX_LABEL);

        // Featured Image
        $featured_image = isset($vars['featured_image']) ? sanitize_text_field($vars['featured_image']) : '';
        set_post_thumbnail($id, $featured_image);

        // Sanitization
        array_walk_recursive($vars, 'sanitize_text_field');

        // Save the Data
        $entity = new LSD_Entity_Listing($id);
        $entity->save($vars, true);

        // Trigger Action
        do_action('lsd_api_listing_updated', $id, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'listing' => LSD_API_Resources_Listing::get($id),
            ],
            'status' => 200,
        ]);
    }

    public function trash(WP_REST_Request $request)
    {
        $id = $request->get_param('id');

        // Current User is not Authorized to Delete this Listing
        if (!current_user_can('delete_post', $id)) return $this->response([
            'data' => new WP_Error('401', esc_html__("You're not authorized to trash this listing!", 'listdom')),
            'status' => 401,
        ]);

        // Listing
        $listing = get_post($id);

        // Not Found!
        if (!$listing || (isset($listing->post_type) && $listing->post_type !== LSD_Base::PTYPE_LISTING)) return $this->response([
            'data' => new WP_Error('404', esc_html__('Listing not found!', 'listdom')),
            'status' => 404,
        ]);

        // Trash
        wp_delete_post($id);

        // Trigger Action
        do_action('lsd_api_listing_trashed', $id, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
            ],
            'status' => 200,
        ]);
    }

    public function delete(WP_REST_Request $request)
    {
        $id = $request->get_param('id');

        // Current User is not Authorized to Delete this Listing
        if (!current_user_can('delete_post', $id)) return $this->response([
            'data' => new WP_Error('401', esc_html__("You're not authorized to delete this listing!", 'listdom')),
            'status' => 401,
        ]);

        // Listing
        $listing = get_post($id);

        // Not Found!
        if (!$listing || (isset($listing->post_type) && $listing->post_type !== LSD_Base::PTYPE_LISTING)) return $this->response([
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

    public function contact(WP_REST_Request $request)
    {
        $id = $request->get_param('id');

        // Listing
        $listing = get_post($id);

        // Not Found!
        if (!$listing || (isset($listing->post_type) && $listing->post_type !== LSD_Base::PTYPE_LISTING)) return $this->response([
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
        do_action('lsd_contact_owner', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'post_id' => $id,
        ]);

        // Update Listing Contacts
        (new LSD_Entity_Listing($id))->update_contacts();

        // Trigger Action
        do_action('lsd_api_contact_owner', $id, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
            ],
            'status' => 200,
        ]);
    }

    public function abuse(WP_REST_Request $request)
    {
        $id = $request->get_param('id');

        // Listing
        $listing = get_post($id);

        // Not Found!
        if (!$listing || (isset($listing->post_type) && $listing->post_type !== LSD_Base::PTYPE_LISTING)) return $this->response([
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

    public function fields()
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
}
