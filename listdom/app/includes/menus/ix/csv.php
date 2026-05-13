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
        add_action('wp_ajax_lsd_ix_csv_export_init', [$this, 'export_init']);
        add_action('wp_ajax_lsd_ix_csv_export_chunk', [$this, 'export_chunk']);
        add_action('wp_ajax_lsd_ix_csv_export_download', [$this, 'export_download']);

        // Remove Mapping Template
        add_action('wp_ajax_lsd_csv_template_remove', [$this, 'remove_template']);
    }

    public function export_init()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : null;

        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        if (!wp_verify_nonce($wpnonce, 'lsd_ix_csv_export')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

        $type = isset($_POST['type']) ? LSD_IX::normalize_import_type(sanitize_text_field(wp_unslash($_POST['type']))) : 'listings';
        $size = isset($_POST['size']) ? absint($_POST['size']) : 100;
        if ($size < 1) $size = 100;

        $filename = 'listdom-' . sanitize_key($type) . '-' . current_time('Y-m-d-H-i') . '-' . wp_generate_password(6, false, false) . '.csv';
        $path = $this->get_upload_path() . $filename;

        $ix = new LSD_IX_CSV();
        $columns = $ix->export_columns($type);

        $fh = fopen($path, 'w');
        if ($fh === false) $this->response(['success' => 0, 'code' => 'FILE_OPEN_FAILED']);

        fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fh, $columns);
        fclose($fh);

        $this->response([
            'success' => 1,
            'data' => [
                'file' => $filename,
                'type' => $type,
                'offset' => 0,
                'size' => $size,
            ],
        ]);
    }

    public function export_chunk()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : null;

        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        if (!wp_verify_nonce($wpnonce, 'lsd_ix_csv_export')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

        $file = isset($_POST['file']) ? sanitize_file_name(wp_unslash($_POST['file'])) : '';
        if (!trim($file)) $this->response(['success' => 0, 'code' => 'FILE_MISSED']);

        $type = isset($_POST['type']) ? LSD_IX::normalize_import_type(sanitize_text_field(wp_unslash($_POST['type']))) : 'listings';
        $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;
        $size = isset($_POST['size']) ? absint($_POST['size']) : 100;
        if ($size < 1) $size = 100;

        $path = $this->get_upload_path() . $file;
        if (!LSD_File::exists($path)) $this->response(['success' => 0, 'code' => 'FILE_NOT_FOUND']);

        $ix = new LSD_IX_CSV();
        [$rows, $count] = $ix->export_rows($type, $offset, $size);

        if ($count)
        {
            $fh = fopen($path, 'a');
            if ($fh === false) $this->response(['success' => 0, 'code' => 'FILE_OPEN_FAILED']);

            foreach ($rows as $row)
            {
                fputcsv($fh, $row);
            }

            fclose($fh);
        }

        $next_offset = $offset + $count;
        $done = $count < $size;
        $type_label = LSD_IX::import_type_label($type);

        $message = sprintf(
            /* translators: 1: Number exported, 2: Export type label. */
            esc_html__('%1$s %2$s exported successfully. Preparing the next batch...', 'listdom'),
            '<strong>' . $next_offset . '</strong>',
            strtolower($type_label)
        );

        $download = '';

        if ($done)
        {
            $message = sprintf(
                /* translators: 1: Number exported, 2: Export type label. */
                esc_html__('%1$s %2$s exported successfully. Your download will start shortly.', 'listdom'),
                $next_offset,
                strtolower($type_label)
            );

            $download = add_query_arg([
                'action' => 'lsd_ix_csv_export_download',
                'file' => $file,
                '_wpnonce' => wp_create_nonce('lsd_ix_csv_export_download'),
            ], admin_url('admin-ajax.php'));
        }

        $this->response([
            'success' => 1,
            'done' => $done ? 1 : 0,
            'message' => $message,
            'data' => [
                'offset' => $next_offset,
            ],
            'download' => $download,
        ]);
    }

    public function export_download()
    {
        $wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if (!trim($wpnonce)) wp_die(esc_html__('Security nonce is missing.', 'listdom'));

        if (!wp_verify_nonce($wpnonce, 'lsd_ix_csv_export_download')) wp_die(esc_html__('Security nonce is invalid.', 'listdom'));

        if (!current_user_can('manage_options')) wp_die(esc_html__('You are not allowed to perform this action.', 'listdom'));

        $file = isset($_GET['file']) ? sanitize_file_name(wp_unslash($_GET['file'])) : '';
        if (!trim($file)) wp_die(esc_html__('File is missing.', 'listdom'));

        $path = $this->get_upload_path() . $file;
        if (!LSD_File::exists($path)) wp_die(esc_html__('File not found.', 'listdom'));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Pragma: public');
        header('Cache-Control: max-age=0');

        readfile($path);
        LSD_File::delete($path);
        exit;
    }

    public function upload()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'message' => esc_html__('Security nonce missed!', 'listdom'), 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_csv_upload')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is invalid!', 'listdom'), 'code' => 'NONCE_IS_INVALID']);

        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to perform this action.', 'listdom'), 'code' => 'NO_ACCESS']);

        $uploaded_file = $_FILES['file'] ?? null;
        $type = isset($_POST['type']) ? LSD_IX::normalize_import_type(sanitize_text_field(wp_unslash($_POST['type']))) : 'listings';

        // No file
        if (!$uploaded_file) $this->response(['success' => 0, 'message' => esc_html__('Please upload a file first!', 'listdom'), 'code' => 'NO_FILE']);

        $ex = explode('.', sanitize_file_name(wp_unslash($uploaded_file['name'])));
        $extension = end($ex);

        // Invalid Extension
        if ($extension !== 'csv') $this->response(['success' => 0, 'message' => esc_html__('Invalid file extension! Only CSV files are allowed.', 'listdom'), 'code' => 'INVALID_EXTENSION']);

        // Upload File
        $data = [];
        $output = '';
        $file = time() . '.' . $extension;

        $uploaded = $this->upload_to_listdom_dir($uploaded_file, $file);

        if ($uploaded && !isset($uploaded['error']))
        {
            $success = 1;
            $message = esc_html__('The file is uploaded. Please map the CSV file fields with Listdom fields and then import it.', 'listdom');

            $file = basename($uploaded['file']);
            $data = ['file' => $file];
            $output = $this->mapping_form($file, $type);
        }
        else
        {
            $success = 0;
            $message = isset($uploaded['error']) ? esc_html($uploaded['error']) : esc_html__('An error occurred during uploading the file!', 'listdom');
        }

        $this->response(['success' => $success, 'message' => $message, 'data' => $data, 'output' => $output]);
    }

    public function import()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_csv_import')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

        // Get Parameters
        $ix = isset($_POST['ix']) && is_array($_POST['ix']) ? wp_unslash($_POST['ix']) : [];

        // Sanitization
        array_walk_recursive($ix, 'sanitize_text_field');

        $type = isset($ix['type']) ? LSD_IX::normalize_import_type($ix['type']) : 'listings';
        $type_label = LSD_IX::import_type_label($type);

        // File
        $file = isset($ix['file']) ? sanitize_text_field($ix['file']) : '';

        // No File
        if (trim($file) === '') $this->response(['success' => 0, 'code' => 'FILE_MISSED']);

        // Main Library
        $main = new LSD_Main();

        // File Full Path
        $path = $main->get_upload_path() . $file;

        // File Not Found
        if (!LSD_File::exists($path)) $this->response(['success' => 0, 'code' => 'FILE_NOT_FOUND']);

        // Offset & Limit
        $offset = $ix['offset'] ?? 0;
        $limit = $ix['size'] ?? 20;
        $options = [
            'hierarchical_terms' => !empty($ix['hierarchical_terms']),
        ];

        $templates = '';
        $dropdown = '';

        if (isset($ix['template']) && trim($ix['template']) && $offset == 0)
        {
            $template = new LSD_IX_Templates_CSV($type);
            $template->upsert([
                'name' => $ix['template'],
                'fields' => $ix['mapping'],
            ]);

            // Updated templates list
            $templates = $this->templates_markup();

            // Updated dropdown
            if ($type === 'listings')
            {
                $dropdown = (new LSD_IX_Templates_CSV('listings'))->dropdown([
                    'id' => 'lsd_ix_csv_auto_import_mapping',
                    'name' => 'ix[mapping]',
                    'show_empty' => true,
                ]);
            }
        }

        $csv = new LSD_IX_CSV();
        [$count] = $csv->import_by_mapping($path, $ix['mapping'], $offset, $limit, $type, $options);

        // Message
        $message = sprintf(
            /* translators: 1: Number processed, 2: Import type label. */
            esc_html__('%1$s %2$s imported successfully! Please be patient and do not close the window. Continuing to import the remaining items...', 'listdom'),
            '<strong>' . (($offset / $limit) + 1) * $count . '</strong>',
            strtolower($type_label)
        );
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
            $message = sprintf(
                /* translators: 1: Total imported count, 2: Import type label. */
                esc_html__('%1$s %2$s imported successfully! The import process is now complete.', 'listdom'),
                $offset + $count,
                strtolower($type_label)
            );
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
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_csv_load_template')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

        // Get Parameters
        $key = isset($_POST['template']) ? sanitize_text_field(wp_unslash($_POST['template'])) : [];
        $type = isset($_POST['type']) ? LSD_IX::normalize_import_type(sanitize_text_field(wp_unslash($_POST['type']))) : 'listings';

        // Template Library
        $tpl = new LSD_IX_Templates_CSV($type);
        $template = $tpl->get($key);

        $this->response(['success' => 1, 'template' => $template['fields'] ?? []]);
    }

    public function ai_mapping()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_ix_csv_ai_mapping')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

        // Parameters
        $file = isset($_POST['file']) ? sanitize_text_field(wp_unslash($_POST['file'])) : '';
        $ai_profile = isset($_POST['ai_profile']) ? sanitize_text_field(wp_unslash($_POST['ai_profile'])) : '';
        $type = isset($_POST['type']) ? LSD_IX::normalize_import_type(sanitize_text_field(wp_unslash($_POST['type']))) : 'listings';

        // Feed Path / URL
        $path = $this->get_upload_path().$file;

        // Mapping Library
        $mapping = new LSD_IX_Mapping();
        $f_fields = $mapping->feed_fields($path);

        // AI Mapping
        $ai = (new LSD_AI())->by_profile($ai_profile);

        // Mapped by AI!
        $mapping_ai = $ai ? $ai->auto_mapping($mapping->fields_for_type($type), $f_fields) : [];

        $this->response([
            'success' => $ai ? 1 : 0,
            'template' => $mapping_ai,
            'message' => $ai
                ? esc_html__('Mapping by AI was successful!', 'listdom')
                : esc_html__('The AI model is not configured. You can configure it in Listdom settings.', 'listdom'),
        ]);
    }

    public function mapping_form($file, $type = 'listings')
    {
        // Generate output
        return $this->include_html_file('menus/ix/tabs/csv/mapping.php', [
            'return_output' => true,
            'parameters' => [
                'file' => $file,
                'type' => $type,
            ],
        ]);
    }

    protected function templates_markup(): string
    {
        ob_start();

        foreach (LSD_IX::import_type_labels() as $type_key => $type_label)
        {
            $type_template = new LSD_IX_Templates_CSV($type_key);
            ?>
            <div class="lsd-settings-fields-sub-wrapper">
                <h4 class="lsd-admin-title lsd-mt-0"><?php echo esc_html($type_label); ?></h4>
                <?php echo $type_template->manage('lsd_csv_template_remove'); ?>
            </div>
            <?php
        }

        return (string) ob_get_clean();
    }

    public function remove_template()
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : null;

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, 'lsd_csv_template_remove')) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        if (!current_user_can('manage_options')) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);

        // Get Parameters
        $key = isset($_POST['key']) ? sanitize_text_field(wp_unslash($_POST['key'])) : [];
        $type = isset($_POST['type']) ? LSD_IX::normalize_import_type(sanitize_text_field(wp_unslash($_POST['type']))) : 'listings';

        // Template Library
        $tpl = new LSD_IX_Templates_CSV($type);

        // Jobs Library
        $job = new LSD_IX_Jobs_CSV();
        $jobs = $job->all();
        $removed_jobs = [];

        if ($type === 'listings')
        {
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
        }

        $tpl->remove($key);

        $this->response(['success' => 1, 'jobs' => $removed_jobs]);
    }
}
