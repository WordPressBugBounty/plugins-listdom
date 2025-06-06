<?php

class LSD_Endpoints extends LSD_Base
{
    public function init()
    {
        add_action('init', [$this, 'register'], 20);
    }

    public function register()
    {
        // Listdom Endpoints
        $endpoints = $this->get();

        // Register Endpoints
        foreach ($endpoints as $endpoint) add_rewrite_endpoint($endpoint, LSD_Base::EP_LISTING);
    }

    public static function is($endpoint = null)
    {
        $response = false;

        global $wp_query;
        $endpoints = LSD_Endpoints::get();

        if (!$endpoint)
        {
            foreach ($endpoints as $e)
            {
                if (isset($wp_query->query_vars[$e]))
                {
                    $response = $e;
                    break;
                }
            }
        }
        else
        {
            if (in_array($endpoint, $endpoints) && isset($wp_query->query_vars[$endpoint])) $response = true;
        }

        return $response;
    }

    public static function get()
    {
        return apply_filters('lsd_listing_endpoints', ['raw']);
    }

    public static function output($endpoint)
    {
        $output = '';
        return apply_filters('lsd_listing_endpoints_output', $output, $endpoint);
    }
}
