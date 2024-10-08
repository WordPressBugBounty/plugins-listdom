<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Ads Class.
 *
 * @class LSD_Ads
 * @version    1.0.0
 */
class LSD_Ads extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function display($position)
    {
        $html = LSD_Ads::get($position);
        if ($html) echo($html);
    }

    public static function get($position)
    {
        $ads = get_transient('lsd_ads');
        if (!$ads)
        {
            $JSON = LSD_File::download('https://api.webilia.com/ads', [
                'solution' => 'Listdom',
                'platform' => 'WordPress',
                'premium' => LSD_Base::isPro() ? 1 : 0,
                'url' => get_site_url(),
            ]);

            if (!$JSON or !trim($JSON)) return false;

            $response = json_decode($JSON, true);
            $ads = is_array($response) ? $response : false;

            set_transient('lsd_ads', $ads, DAY_IN_SECONDS);
        }

        $html = '';
        if (is_array($ads) and isset($ads[$position]) and trim($ads[$position])) $html = $ads[$position];

        return $html;
    }
}
