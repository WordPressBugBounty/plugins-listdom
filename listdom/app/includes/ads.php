<?php

class LSD_Ads extends LSD_Base
{
    public static function display($position)
    {
        $html = LSD_Ads::get($position);
        echo $html;
    }

    public static function get($position): string
    {
        $ads = get_transient('lsd_ads');
        if (!is_array($ads))
        {
            $JSON = LSD_File::download('https://api.webilia.com/ads', [
                'solution' => 'Listdom',
                'platform' => 'WordPress',
                'premium' => LSD_Base::isPro() ? 1 : 0,
                'url' => get_site_url(),
            ]);

            if (!$JSON || !trim($JSON))
            {
                // Do not call the Ads API for two days
                set_transient('lsd_ads', [], DAY_IN_SECONDS * 2);

                return '';
            }

            $response = json_decode($JSON, true);
            $ads = is_array($response) ? $response : [];

            set_transient('lsd_ads', $ads, DAY_IN_SECONDS);
        }

        $html = '';
        if (is_array($ads) && isset($ads[$position]) && trim($ads[$position])) $html = $ads[$position];

        return $html;
    }
}
