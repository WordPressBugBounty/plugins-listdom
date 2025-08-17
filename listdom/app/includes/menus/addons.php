<?php

class LSD_Menus_Addons extends LSD_Menus
{
    public function output()
    {
        // Generate output
        $this->include_html_file('menus/addons/tpl.php');
    }

    public function get(bool $all = false)
    {
        $addons = get_transient('lsd_addons');
        if (!$addons)
        {
            $JSON = LSD_File::download('https://api.webilia.com/products', [
                'platform' => 'WordPress',
                'solution' => 'Listdom',
                'url' => get_site_url(),
                'types' => 'toolkit,app,addon',
            ]);

            if (!$JSON || !trim($JSON)) return false;

            $response = json_decode($JSON);
            $addons = is_array($response) ? $response : false;

            set_transient('lsd_addons', $addons, WEEK_IN_SECONDS);
        }

        // Return All Addons
        if ($all) return $addons;

        $installed = [];
        $others = [];

        if ($addons)
        {
            foreach ($addons as $addon)
            {
                $basename = isset($addon->basename) && trim($addon->basename) ? $addon->basename : null;
                if (!$basename) continue;

                $is_installed = apply_filters('lsd_addons_is_installed', is_plugin_active($basename), $basename);

                if ($is_installed) $installed[] = $addon;
                else $others[] = $addon;
            }
        }

        return [$installed, $others];
    }
}
