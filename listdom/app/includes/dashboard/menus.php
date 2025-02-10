<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Dashboard Menus Class.
 *
 * @class LSD_Dashboard_Menus
 * @version    1.0.0
 */
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

            $menus[$slug] = [
                'label' => $label,
                'id' => $slug,
                'url' => $dashboard->add_qs_var('mode', $slug, $dashboard->url ?? ''),
                'icon' => $icon,
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
            if ($dashboard->mode !== ($menu['slug'] ?? '')) continue;
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
