<?php

class LSD_IX_Listdom extends LSD_IX
{
    public function json()
    {
        $data = $this->data();

        header('Content-disposition: attachment; filename=listings-' . current_time('Y-m-d-H-i') . '.json');
        header('Content-type: application/json');

        echo json_encode($data);
        exit;
    }
}
