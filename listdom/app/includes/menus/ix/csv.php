<?php

class LSD_Menus_IX_CSV extends LSD_Base
{
    public function init()
    {
        // Manual Import
        add_action('wp_ajax_lsd_ix_csv_upload', [$this, 'upload']);
        add_action('wp_ajax_lsd_ix_csv_import', [$this, 'import']);
        add_action('wp_ajax_lsd_ix_csv_load_template', [$this, 'template']);
        add_action('wp_ajax_lsd_ix_csv_ai_mapping', [$this, 'ai_mapping']);

        // Remove Mapping Template
        add_action('wp_ajax_lsd_csv_template_remove', [$this, 'remove_template']);
    }

    public function upload()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'message' => esc_html__('Security nonce missed!', 'listdom'), 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_csv_upload')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is invalid!', 'listdom'), 'code' => 'NONCE_IS_INVALID']);

        $uploaded_file = $_FILES['file'] ?? null;

        // No file
        if (!$uploaded_file) $this->response(['success' => 0, 'message' => esc_html__('Please upload a file first!', 'listdom'), 'code' => 'NO_FILE']);

        $ex = explode('.', $uploaded_file['name']);
        $extension = end($ex);

        // Invalid Extension
        if ($extension !== 'csv') $this->response(['success' => 0, 'message' => esc_html__('Invalid file extension! Only CSV files are allowed.', 'listdom'), 'code' => 'INVALID_EXTENSION']);

        // Main Library
        $main = new LSD_Main();

        // Upload File
        $file = time() . '.' . $extension;
        $destination = $main->get_upload_path() . $file;

        $data = [];
        $output = '';

        if (move_uploaded_file($uploaded_file['tmp_name'], $destination))
        {
            $success = 1;
            $message = esc_html__('The file is uploaded. Please map the CSV file fields with Listdom fields and then import it.', 'listdom');

            $data = ['file' => $file];
            $output = $this->mapping_form($file);
        }
        else
        {
            $success = 0;
            $message = esc_html__('An error occurred during uploading the file!', 'listdom');
        }

        $this->response(['success' => $success, 'message' => $message, 'data' => $data, 'output' => $output]);
    }

    public function import()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_csv_import')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Get Parameters
        $ix = $_POST['ix'] ?? [];

        // Sanitization
        array_walk_recursive($ix, 'sanitize_text_field');

        // File
        $file = $ix['file'] ?? '';

        // No File
        if (trim($file) === '') $this->response(['success' => 0, 'code' => 'FILE_MISSED']);

        // Main Library
        $main = new LSD_Main();

        // File Full Path
        $path = $main->get_upload_path() . $file;

        // File Not Found
        if (!LSD_File::exists($path)) $this->response(['success' => 0, 'code' => 'FILE_NOT_FOUND']);

        // Unlimited Time Needed!
        set_time_limit(0);

        // Offset & Limit
        $offset = $ix['offset'] ?? 0;
        $limit = $ix['size'] ?? 20;

        $templates = '';
        $dropdown = '';

        if (isset($ix['template']) && trim($ix['template']) && $offset == 0)
        {
            $template = new LSD_IX_Templates_CSV();
            $template->upsert([
                'name' => $ix['template'],
                'fields' => $ix['mapping'],
            ]);

            // Updated templates list
            $templates = $template->manage('lsd_csv_template_remove');

            // Updated dropdown
            $dropdown = $template->dropdown([
                'id' => 'lsd_ix_csv_auto_import_mapping',
                'name' => 'ix[mapping]',
                'show_empty' => true,
            ]);
        }

        $csv = new LSD_IX_CSV();
        [$count] = $csv->import_by_mapping($path, $ix['mapping'], $offset, $limit);

        // Message
        $message = sprintf(esc_html__("%s listing(s) imported successfully! Please be patient and do not close the window. Continuing to import the remaining listings...", 'listdom'), '<strong>' . (($offset / $limit) + 1) * $count . '</strong>');
        $done = 0;

        // Import Finished
        if ($count < $limit)
        {
            // Delete the File
            LSD_File::delete($path);

            // Import Finished
            do_action('lsdaddcsv_import_finished');
            do_action('lsd_import_finished');

            // Message
            $message = sprintf(esc_html__("%s listing(s) imported successfully! The import process is now complete.", 'listdom'), $offset + $count);
            $done = 1;
        }

        // Print the response
        $this->response([
            'success' => 1,
            'done' => $done,
            'message' => $message,
            'templates' => $templates,
            'dropdown' => $dropdown,
        ]);
    }

    public function template()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_csv_load_template')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Get Parameters
        $key = isset($_POST['template']) ? sanitize_text_field($_POST['template']) : [];

        // Template Library
        $tpl = new LSD_IX_Templates_CSV();
        $template = $tpl->get($key);

        $this->response(['success' => 1, 'template' => $template['fields'] ?? []]);
    }

    public function ai_mapping()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_csv_ai_mapping')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Parameters
        $file = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';
        $ai_profile = isset($_POST['ai_profile']) ? sanitize_text_field($_POST['ai_profile']) : '';

        // Feed Path / URL
        $path = $this->get_upload_path().$file;

        // Mapping Library
        $mapping = new LSD_IX_Mapping();
        $f_fields = $mapping->feed_fields($path);

        // AI Mapping
        $ai = (new LSD_AI())->by_profile($ai_profile);

        // Mapped by AI!
        $mapping_ai = $ai ? $ai->auto_mapping($mapping->listdom_fields(), $f_fields) : [];

        $this->response([
            'success' => $ai ? 1 : 0,
            'template' => $mapping_ai,
            'message' => $ai
                ? esc_html__('Mapping by AI was successful!', 'listdom')
                : esc_html__('The AI model is not configured. You can configure it in Listdom settings.', 'listdom'),
        ]);
    }

    public function mapping_form($file)
    {
        // Generate output
        return $this->include_html_file('menus/ix/tabs/csv/mapping.php', [
            'return_output' => true,
            'parameters' => [
                'file' => $file,
            ],
        ]);
    }

    public function remove_template()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_csv_template_remove')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Get Parameters
        $key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : [];

        // Template Library
        $tpl = new LSD_IX_Templates_CSV();

        // Jobs Library
        $job = new LSD_IX_Jobs_CSV();
        $jobs = $job->all();
        $removed_jobs = [];

        foreach ($jobs as $job_key => $job_item)
        {
            $mapping = $job_item['mapping'] ?? [];
            if (!is_array($mapping)) $mapping = [$mapping];

            if (in_array($key, $mapping))
            {
                $job->remove($job_key);
                $removed_jobs[] = $job_key;
            }
        }

        $tpl->remove($key);

        $this->response(['success' => 1, 'jobs' => $removed_jobs]);
    }
}
