<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Import / Export Menu Class.
 *
 * @class LSD_Menus_IX
 * @version    1.0.0
 */
class LSD_Menus_IX extends LSD_Menus
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize the menu
        $this->init();
    }

    public function init()
    {
        // Export
        add_action('init', [$this, 'export'], 100);

        // Import
        add_action('wp_ajax_lsd_ix_listdom_upload', [$this, 'upload']);
        add_action('wp_ajax_lsd_ix_listdom_import', [$this, 'import']);

        // General Import
        add_action('lsd_import', [new LSD_IX(), 'import']);
    }

    public function output()
    {
        // Get the current tab
        $this->tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'json';

        // Generate output
        $this->include_html_file('menus/ix/tpl.php');
    }

    public function export()
    {
        $export = isset($_GET['lsd-export']) ? sanitize_text_field(strtolower($_GET['lsd-export'])) : '';

        // It's not an export request
        if (!trim($export)) return false;

        $wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field($_GET['_wpnonce']) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) return false;

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_form')) return false;

        // Export Library
        $ix = new LSD_IX_Listdom();

        switch ($export)
        {
            case 'json':

                $ix->json();
                break;

            default:

                do_action('lsd_ix_export_request');
                break;
        }

        // End the Execution
        exit;
    }

    public function upload()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'message' => esc_html__('Security nonce missed!', 'listdom'), 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_listdom_upload')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is invalid!', 'listdom'), 'code' => 'NONCE_IS_INVALID']);

        $uploaded_file = $_FILES['file'] ?? null;

        // No file
        if (!$uploaded_file) $this->response(['success' => 0, 'message' => esc_html__('Please upload a file first!', 'listdom'), 'code' => 'NO_FILE']);

        $ex = explode('.', sanitize_file_name($uploaded_file['name']));
        $extension = end($ex);

        // Invalid Extension
        if ($extension !== 'json') $this->response(['success' => 0, 'message' => esc_html__('Invalid file extension! Only JSON files are allowed.', 'listdom'), 'code' => 'INVALID_EXTENSION']);

        // Upload File
        $file = time() . '.' . $extension;
        $destination = $this->get_upload_path() . $file;

        $data = [];
        if (move_uploaded_file($uploaded_file['tmp_name'], $destination))
        {
            $success = 1;
            $message = esc_html__('The file is uploaded! You can import the file now.', 'listdom');

            $data = ['file' => $file];
        }
        else
        {
            $success = 0;
            $message = esc_html__('An error occurred during uploading the file!', 'listdom');
        }

        $this->response(['success' => $success, 'message' => $message, 'data' => $data]);
    }

    public function import()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_listdom_import')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Get Parameters
        $ix = $_POST['ix'] ?? [];

        // Sanitization
        array_walk_recursive($ix, 'sanitize_text_field');

        // File
        $file = $ix['file'] ?? null;

        // No File
        if (trim($file) == '') $this->response(['success' => 0, 'code' => 'FILE_MISSED']);

        // Full File Path
        $path = $this->get_upload_path() . $file;

        // File Not Found
        if (!LSD_File::exists($path)) $this->response(['success' => 0, 'code' => 'FILE_NOT_FOUND']);

        // Import
        do_action('lsd_import', $path);

        // Delete the File
        LSD_File::delete($path);

        // Print the response
        $this->response(['success' => 1, 'message' => esc_html__('Listings imported successfully!', 'listdom')]);
    }
}
