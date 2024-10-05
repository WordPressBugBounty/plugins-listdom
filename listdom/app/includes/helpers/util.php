<?php
// no direct access
defined('ABSPATH') || die();

function lsd_map($listings = [], $args = [])
{
    // Map Provider
    $provider = LSD_Map_Provider::get($args['provider'] ?? null);

    if($provider === LSD_MP_GOOGLE) return lsd_googlemap($listings, $args);
    elseif($provider === LSD_MP_LEAFLET) return lsd_leaflet($listings, $args);

    return null;
}

function lsd_googlemap($listings = [], $args = [])
{
    ob_start();
    include lsd_template('maps/google.php');
    return ob_get_clean();
}

function lsd_leaflet($listings = [], $args = [])
{
    ob_start();
    include lsd_template('maps/leaflet.php');
    return ob_get_clean();
}

function lsd_schema()
{
    return new LSD_Schema();
}