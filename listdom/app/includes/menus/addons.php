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

    public function get_type_descriptions(): array
    {
        return [
            'addon' => esc_html__('Listdom has the most complete set of addons for listing directory industry. Use these addons to achieve what you want.', 'listdom'),
            'toolkit' => esc_html__('Toolkits are used to use Listdom demos without activating several addons.', 'listdom'),
            'app' => esc_html__('Apps are advanced directory solutions designated for those who aim for big targets.', 'listdom'),
        ];
    }

    public function get_type_label(string $type): string
    {
        if ($type === 'toolkit') return esc_html__('Toolkits', 'listdom');
        if ($type === 'app') return esc_html__('Apps', 'listdom');

        return esc_html__('Addons', 'listdom');
    }

    public function get_download_labels(): array
    {
        return [
            'addon' => esc_html__('Get This Addon', 'listdom'),
            'toolkit' => esc_html__('Get This Toolkit', 'listdom'),
            'app' => esc_html__('Get This App', 'listdom'),
        ];
    }

    public function get_badge(string $status): array
    {
        return $status === 'installed'
            ? ['class' => 'lsd-success', 'icon' => 'lsdi-checkmark-circle', 'text' => esc_html__('Installed', 'listdom')]
            : ['class' => '', 'icon' => 'lsdi-alert', 'text' => esc_html__('Not Installed', 'listdom')];
    }

    public function get_grouped_addons(): array
    {
        [$installed, $others] = $this->get();

        $grouped = [];
        foreach (['installed' => $installed ?? [], 'available' => $others ?? []] as $status => $addons)
        {
            foreach ($addons as $addon)
            {
                $addon->status = $status;
                $grouped[$addon->type][] = $addon;
            }
        }

        return $grouped;
    }
}
