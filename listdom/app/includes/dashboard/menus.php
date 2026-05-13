<?php

class LSD_Dashboard_Menus extends LSD_Shortcodes
{
    public $menus;
    public $content;

    public function __construct()
    {
        $settings = LSD_Options::settings();
        $this->menus = isset($settings['dashboard_menu_custom']) && is_array($settings['dashboard_menu_custom'])
            ? $settings['dashboard_menu_custom']
            : [];
    }

    public function init()
    {
        add_filter('lsd_dashboard_menus', [$this, 'menu'], 16, 2);
        add_filter('lsd_dashboard_modes', [$this, 'dashboard'], 16, 2);
    }

    /**
     * @param array $menus
     * @param LSD_Shortcodes_Dashboard $dashboard
     * @return array
     */
    public function menu(array $menus, LSD_Shortcodes_Dashboard $dashboard): array
    {
        if (!count($this->menus)) return $menus;

        $has_logout = isset($menus['logout']);
        if ($has_logout)
        {
            $logout = $menus['logout'];
            unset($menus['logout']);
        }

        foreach ($this->menus as $menu)
        {
            $slug = $menu['slug'] ?? '';
            $label = $menu['label'] ?? '';
            $icon = $menu['icon'] ?? 'fas fa-tachometer-alt';
            $enabled = !isset($menu['enabled']) || (int) $menu['enabled'] === 1;

            if (!$slug) continue;
            if (!$enabled) continue;

            $login_status = $menu['login_status'] ?? 'required';

            // If login is required and user is NOT logged in, do not add this custom menu
            if ($login_status === 'required' && !is_user_logged_in()) continue;

            $menus[$slug] = [
                'label' => $label,
                'id' => $slug,
                'url' => $dashboard->add_qs_var('mode', $slug, $dashboard->url ?? ''),
                'icon' => $icon,
                'login_status' => $login_status,
            ];
        }

        if ($has_logout)
        {
            $menus['logout'] = $logout;
        }

        return $menus;
    }

    /**
     * @param string $output
     * @param LSD_Shortcodes_Dashboard $dashboard
     * @return string
     */
    public function dashboard(string $output, LSD_Shortcodes_Dashboard $dashboard): string
    {
        foreach ($this->menus as $menu)
        {
            $enabled = !isset($menu['enabled']) || (int) $menu['enabled'] === 1;
            if (!$enabled) continue;

            if ($dashboard->mode !== ($menu['slug'] ?? '')) continue;

            $login_status = $menu['login_status'] ?? 'required';

            // If login required and user is NOT logged in, do not show this custom content
            if ($login_status === 'required' && !is_user_logged_in()) return $output;

            $this->content = $menu['content'] ?? '';
            return $this->output($dashboard);
        }

        return $output;
    }

    /**
     * @param LSD_Shortcodes_Dashboard $dashboard
     * @return string
     */
    public function output(LSD_Shortcodes_Dashboard $dashboard): string
    {
        // Dashboard
        ob_start();
        include lsd_template('dashboard/content.php');
        return ob_get_clean();
    }
}
