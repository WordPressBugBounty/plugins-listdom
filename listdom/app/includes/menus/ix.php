<?php

class LSD_Menus_IX extends LSD_Menus
{
    public $subtab;

    public function __construct()
    {
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
        add_action('lsd_import', [new LSD_IX(), 'import'], 10, 2);

        // CSV
        (new LSD_Menus_IX_CSV())->init();
    }

    public function output()
    {
        // Get the current tab
        $this->tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'csv';
        $this->subtab = isset($_GET['subtab']) ? sanitize_text_field(wp_unslash($_GET['subtab'])) : 'import';

        // Generate output
        $this->include_html_file('menus/ix/tpl.php');
    }

    public function export()
    {
        $export = isset($_GET['lsd-export']) ? sanitize_text_field(strtolower(wp_unslash($_GET['lsd-export']))) : '';

        // It's not an export request
        if (!trim($export)) return false;

        if (!current_user_can('manage_options')) return false;

        $wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) return false;

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_form')) return false;

        // Export Library
        $ix = new LSD_IX_Listdom();

        $type = isset($_GET['lsd-type']) ? sanitize_text_field(wp_unslash($_GET['lsd-type'])) : 'listings';
        $type = LSD_IX::normalize_import_type($type);

        switch ($export)
        {
            case 'json':

                $ix->json($type);
                break;

            case 'csv':

                $ix->csv($type);
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
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'message' => esc_html__('Security nonce missed!', 'listdom'), 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_listdom_upload')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is invalid!', 'listdom'), 'code' => 'NONCE_IS_INVALID']);

        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to perform this action.', 'listdom'), 'code' => 'NO_ACCESS']);

        $uploaded_file = $_FILES['file'] ?? null;

        // No file
        if (!$uploaded_file) $this->response(['success' => 0, 'message' => esc_html__('Please upload a file first!', 'listdom'), 'code' => 'NO_FILE']);

        $ex = explode('.', sanitize_file_name($uploaded_file['name']));
        $extension = end($ex);

        // Invalid Extension
        if ($extension !== 'json') $this->response(['success' => 0, 'message' => esc_html__('Invalid file extension! Only JSON files are allowed.', 'listdom'), 'code' => 'INVALID_EXTENSION']);

        // Upload File
        $data = [];
        $file = time() . '.' . $extension;

        $uploaded = $this->upload_to_listdom_dir($uploaded_file, $file, [
            'mimes' => [
                'json' => 'application/json',
            ],
        ]);

        if ($uploaded && !isset($uploaded['error']))
        {
            $success = 1;
            $message = esc_html__('The file is uploaded! You can import the file now.', 'listdom');

            $data = ['file' => basename($uploaded['file'])];
        }
        else
        {
            $success = 0;
            $message = isset($uploaded['error']) ? esc_html($uploaded['error']) : esc_html__('An error occurred during uploading the file!', 'listdom');
        }

        $this->response(['success' => $success, 'message' => $message, 'data' => $data]);
    }

    public function import()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_listdom_import')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

        // Get Parameters
        $ix = isset($_POST['ix']) && is_array($_POST['ix']) ? wp_unslash($_POST['ix']) : [];

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

        $type = $ix['type'] ?? 'listings';
        $type = LSD_IX::normalize_import_type($type);

        $selected_label = LSD_IX::import_type_label($type);

        LSD_IX::set_last_result(null);

        // Import
        do_action('lsd_import', $path, $type);

        // Delete the File
        LSD_File::delete($path);

        $result = LSD_IX::get_last_result();
        if (is_wp_error($result))
        {
            $code = $result->get_error_code();
            if ($code === 'lsd_import_type_mismatch')
            {
                $file_type = LSD_IX::normalize_import_type($result->get_error_data('lsd_import_type_mismatch')['file_type'] ?? '');
                $file_label = LSD_IX::import_type_label($file_type);

                $message = sprintf(
                    esc_html__('You selected %1$s but the uploaded file is for %2$s. Please choose the %2$s option to import it.', 'listdom'),
                    '<strong>' . $selected_label . '</strong>',
                    '<strong>' . $file_label . '</strong>'
                );
            }
            else
            {
                $message = $result->get_error_message();
                if (!$message) $message = esc_html__('Import failed! Please make sure the JSON file is valid.', 'listdom');
            }

            $this->response([
                'success' => 0,
                'message' => $message,
                'code' => $code ? strtoupper($code) : 'IMPORT_ERROR',
            ]);
        }

        // Print the response
        $this->response([
            'success' => 1,
            'message' => sprintf(esc_html__('%s imported successfully!', 'listdom'), $selected_label),
        ]);
    }
}
