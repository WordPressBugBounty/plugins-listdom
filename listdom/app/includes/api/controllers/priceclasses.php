<?php

class LSD_API_Controllers_PriceClasses extends LSD_API_Controller
{
    public function get(WP_REST_Request $request): WP_REST_Response
    {
        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'classes' => [
                    1 => __('Cheap', 'listdom'),
                    2 => __('Normal', 'listdom'),
                    3 => __('High', 'listdom'),
                    4 => __('Ultra High', 'listdom')
                ]
            ],
            'status' => 200,
        ]);
    }
}
