<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Search Controller Class.
 *
 * @class LSD_API_Controllers_Search
 * @version    1.0.0
 */
class LSD_API_Controllers_Search extends LSD_API_Controller
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function my(WP_REST_Request $request)
    {
        // All Params
        $vars = $request->get_params();

        // Get Current User
        $user = wp_get_current_user();

        // Invalid User
        if (is_wp_error($user)) return $user;

        // Skin Object
        $skin = new LSD_Skins_List();

        // Start the skin
        $skin->start([
            'lsd_filter' => [
                'authors' => [$user->ID],
                'status' => ['publish', 'pending', 'draft', 'future', 'private', 'trash', LSD_Base::STATUS_HOLD, LSD_Base::STATUS_EXPIRED],
            ],
        ]);

        $skin->after_start();

        // Generate the Query
        $skin->query();

        // Apply Search Parameters
        $skin->apply_search($vars);

        // Fetch the listings
        $skin->fetch();

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'listings' => LSD_API_Resources_Listing::collection($skin->listings),
                'pagination' => LSD_API_Resources_Listing::pagination($skin),
            ],
            'status' => 200,
        ]);
    }

    public function search(WP_REST_Request $request)
    {
        // All Params
        $vars = $request->get_params();

        // Set Params
        foreach ($vars as $key => $value) $_GET[$key] = $value;

        // Skin Object
        $skin = new LSD_Skins_List();

        // Start the skin
        $skin->start([]);
        $skin->after_start();

        // Generate the Query
        $skin->query();

        // Apply Search Parameters
        $skin->apply_search($vars);

        // Fetch the listings
        $skin->fetch();

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'listings' => LSD_API_Resources_Listing::collection($skin->listings),
                'pagination' => LSD_API_Resources_Listing::pagination($skin),
            ],
            'status' => 200,
        ]);
    }
}
