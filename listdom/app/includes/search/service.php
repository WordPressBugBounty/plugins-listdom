<?php

/** Run the existing Listdom search pipeline for REST and native abilities. */
class LSD_Search_Service
{
    /** Prepare canonical constraints without fetching, tracking or geocoding. */
    public function prepare_inspection(LSD_Skins $skin, array $atts, array $vars, ?callable $guest = null): LSD_Skins
    {
        $original = [$_GET, $_POST, $_REQUEST];
        $_GET = $vars;
        $_POST = [];
        $_REQUEST = $vars;
        try
        {
            $atts = (new LSD_Hooks())->apply_search($atts);
            if ($guest) $atts = $guest($atts, 0);
            $skin->prepare_inspection($atts);
            $skin->prepare_query();
            // Named centers would call the geocoder. Only explicit coordinates are safe.
            $center = $skin->filter_options['circle']['center'] ?? null;
            if (LSD_Skins::has_coordinates($center)) $skin->query_radius();
            $skin->apply_search($vars);
            return $skin;
        }
        finally
        {
            [$_GET, $_POST, $_REQUEST] = $original;
        }
    }

    /** Run a Listdom search with optional public limits and request isolation. */
    public function run(array $vars, bool $public_only = false, bool $restore_request = false): LSD_Skins_List
    {
        // Keep ability searches isolated from the current HTTP request.
        $original_get = $_GET;
        $original_post = $_POST;
        $original_request = $_REQUEST;
        if ($restore_request)
        {
            $_GET = [];
            $_POST = [];
            $_REQUEST = [];
        }

        foreach ($vars as $key => $value)
        {
            $_GET[$key] = $value;
        }

        try
        {
            $skin = new LSD_Skins_List();
            $track_search = !$public_only && absint($vars['page'] ?? 0) <= 1;
            $tracking_filters = $track_search ? (isset($vars['sf']) && is_array($vars['sf']) ? $vars['sf'] : $skin->get_sf()) : [];

            // The skin builds keyword and taxonomy clauses from lsd_filter in
            // start(); apply_search() does not populate those filter options.
            $atts = $public_only ? [
                'lsd_filter' => $skin->get_sf([]),
                'lsd_ignore_current_query' => true,
            ] : [];
            $skin->start($atts);
            $skin->after_start();
            $skin->query();
            $skin->apply_search($vars);

            $public_filter = null;
            if ($public_only)
            {
                $skin->args['post_status'] = 'publish';
                $skin->args['has_password'] = false;
                // Run after normal Listdom search filters so a configured
                // status filter cannot widen an ability's public query.
                $public_filter = static function ($args) use ($vars)
                {
                    // Apply guest visibility constraints before locking the public status.
                    $args = (array) apply_filters('lsd_ai_visibility_public_listing_query_args', $args, LSD_AI_Visibility::instance());
                    $args['post_status'] = 'publish';
                    $args['has_password'] = false;
                    if (!empty($vars['seed']) && ($args['orderby'] ?? '') === 'rand') $args['orderby'] = 'RAND(' . absint($vars['seed']) . ')';
                    return $args;
                };
                add_filter('lsd_before_search', $public_filter, PHP_INT_MAX);
            }

            try
            {
                $skin->fetch();
            }
            finally
            {
                if ($public_filter) remove_filter('lsd_before_search', $public_filter, PHP_INT_MAX);
            }
            if ($track_search) do_action('lsd_search_executed', (int) $skin->found_listings, 'rest', $tracking_filters);

            return $skin;
        }
        finally
        {
            if ($restore_request)
            {
                $_GET = $original_get;
                $_POST = $original_post;
                $_REQUEST = $original_request;
            }
        }
    }
}
