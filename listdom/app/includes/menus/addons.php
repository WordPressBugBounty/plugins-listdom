<?php

class LSD_Menus_Addons extends LSD_Menus
{
    public function output()
    {
        // Generate output
        $this->include_html_file('menus/addons/tpl.php');
    }

    public function get()
    {
        $addons = get_transient('lsd_addons');
        if (!$addons)
        {
            $JSON = LSD_File::download('https://api.webilia.com/products', [
                'platform' => 'WordPress',
                'solution' => 'Listdom',
                'url' => get_site_url(),
            ]);

            if (!$JSON || !trim($JSON)) return false;

            $response = json_decode($JSON);
            $addons = is_array($response) ? $response : false;

            set_transient('lsd_addons', $addons, DAY_IN_SECONDS);
        }

        return $addons;
    }
}
