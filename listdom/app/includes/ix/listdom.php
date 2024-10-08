<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom IX General Class.
 *
 * @class LSD_IX_Listdom
 * @version    1.0.0
 */
class LSD_IX_Listdom extends LSD_IX
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function json()
    {
        $data = $this->data();

        header('Content-disposition: attachment; filename=listings-' . current_time('Y-m-d-H-i') . '.json');
        header('Content-type: application/json');

        echo json_encode($data);
        exit;
    }
}
