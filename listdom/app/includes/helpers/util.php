<?php

function lsd_map($listings = [], $args = [])
{
    // Map Provider
    $provider = LSD_Map_Provider::get($args['provider'] ?? null);

    if ($provider === LSD_MP_GOOGLE) return lsd_googlemap($listings, $args);
    else if ($provider === LSD_MP_LEAFLET) return lsd_leaflet($listings, $args);

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

function lsd_schema(): LSD_Schema
{
    return new LSD_Schema();
}

function lsd_date(string $format = '', $time = null): string
{
    if (!$time) $time = current_time('timestamp', true);
    if (!$format) $format = LSD_Base::datetime_format();

    return LSD_Base::datetime($time, $format);
}

function lsd_ads(string $position): string
{
    $ads = get_transient('lsd_ads');
    if (!is_array($ads))
    {
        $api = new \Webilia\WP\Ads(1);
        $ads = $api->getAds([
            'premium' => LSD_Base::isPro() ? 1 : 0,
        ]);

        set_transient('lsd_ads', $ads, DAY_IN_SECONDS);
    }

    return $ads[$position] ?? '';
}
