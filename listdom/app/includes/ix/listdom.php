<?php

class LSD_IX_Listdom extends LSD_IX
{
    public function json()
    {
        $data = $this->data();

        header('Content-disposition: attachment; filename=listings-' . current_time('Y-m-d-H-i') . '.json');
        header('Content-type: application/json');

        echo wp_json_encode($data);
        exit;
    }

    public function csv()
    {
        // CSV
        $ix = new LSD_IX_CSV();
        $ix->csv();

        exit;
    }
}
