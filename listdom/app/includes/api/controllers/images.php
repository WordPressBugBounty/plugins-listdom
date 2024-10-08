<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Images Controller Class.
 *
 * @class LSD_API_Controllers_Images
 * @version    1.0.0
 */
class LSD_API_Controllers_Images extends LSD_API_Controller
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

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'image' => LSD_API_Resources_Image::get($id),
            ],
            'status' => 200,
        ]);
    }

    public function upload(WP_REST_Request $request)
    {
        if (!current_user_can('upload_files')) return $this->response([
            'data' => new WP_Error(401, esc_html__("You're not authorized to upload images!", 'listdom')),
            'status' => 401,
        ]);

        // Media Libraries
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $vars = $request->get_file_params();

        $image = is_array($vars) && isset($vars['image']) ? $vars['image'] : [];
        $tmp = $image['tmp_name'] ?? null;

        // Image Not Found
        if (!$tmp) return $this->response([
            'data' => new WP_Error(400, esc_html__('Image is required!', 'listdom')),
            'status' => 400,
        ]);

        $ex = explode('.', $image['name']);
        $extension = end($ex);

        // Invalid Extension
        if (!in_array($extension, ['png', 'jpg', 'jpeg', 'gif'])) return $this->response([
            'data' => new WP_Error(400, esc_html__('Invalid image extension! PNG, JPG and GIF images are allowed.', 'listdom')),
            'status' => 400,
        ]);

        // Upload File
        $uploaded = wp_handle_upload($image, ['test_form' => false]);

        // Upload Failed
        if (isset($uploaded['error'])) return $this->response([
            'data' => new WP_Error(400, $uploaded['error']),
            'status' => 400,
        ]);

        $name = $image['name'];
        $file = $uploaded['file'];

        $attachment = [
            'post_mime_type' => $uploaded['type'],
            'post_title' => sanitize_file_name($name),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        // Add as Attachment
        $id = wp_insert_attachment($attachment, $file);

        // Update Metadata
        wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $file));

        // Trigger Action
        do_action('lsd_api_image_uploaded', $id, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'image' => LSD_API_Resources_Image::get($id),
            ],
            'status' => 200,
        ]);
    }
}
