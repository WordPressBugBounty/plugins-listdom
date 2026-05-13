<?php

class LSD_IX_Listdom extends LSD_IX
{
    public function json($type = 'listings')
    {
        $type = self::normalize_import_type($type);
        $data = $this->get_data($type);

        $filename = sanitize_key($this->filename_type($type)) . '-' . current_time('Y-m-d-H-i') . '.json';

        header('Content-disposition: attachment; filename=' . $filename);
        header('Content-type: application/json');

        echo wp_json_encode([
            'version' => 2,
            'type' => $type,
            'generated_at' => current_time('mysql'),
            'items' => $data,
        ]);
        exit;
    }

    public function csv($type = 'listings')
    {
        // CSV
        $ix = new LSD_IX_CSV();
        $ix->csv($type);

        exit;
    }
}
