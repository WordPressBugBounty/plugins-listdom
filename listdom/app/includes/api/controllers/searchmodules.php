<?php

class LSD_API_Controllers_SearchModules extends LSD_API_Controller
{
    public function perform(WP_REST_Request $request): WP_REST_Response
    {
        $searches = get_posts([
            'post_type' => LSD_Base::PTYPE_SEARCH,
            'posts_per_page' => '-1',
        ]);

        $ids = [];
        foreach ($searches as $search) $ids[] = $search->ID;

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'searches' => LSD_API_Resources_SearchModule::collection($ids),
            ],
            'status' => 200,
        ]);
    }

    public function get(WP_REST_Request $request): WP_REST_Response
    {
        $id = $request->get_param('id');

        // Search
        $search = get_post($id);

        // Not Found!
        if (!$search || (isset($search->post_type) && $search->post_type !== LSD_Base::PTYPE_SEARCH)) return $this->response([
            'data' => new WP_Error('404', esc_html__('Search module not found!', 'listdom')),
            'status' => 404,
        ]);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'module' => LSD_API_Resources_SearchModule::get($id),
            ],
            'status' => 200,
        ]);
    }
}
