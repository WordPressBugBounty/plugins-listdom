<?php

class LSD_Menus extends LSD_Base
{
    protected LSD_Menus_Dashboard $dashboard;
    protected LSD_Menus_Settings $settings;
    protected LSD_Menus_IX $ix;
    protected LSD_Menus_Addons $addons;
    protected LSD_Menus_Welcome $welcome;
    protected LSD_Activation $licenses;
    public $tab;

    public function init()
    {
        // Initialize menus
        $this->dashboard = new LSD_Menus_Dashboard();
        $this->settings = new LSD_Menus_Settings();
        $this->ix = new LSD_Menus_IX();
        $this->addons = new LSD_Menus_Addons();
        $this->welcome = new LSD_Menus_Welcome();
        $this->licenses = new LSD_Activation();

        // Register Listdom Menus
        add_action('admin_menu', [$this, 'register_menus'], 1);
        add_action('parent_file', [$this, 'mainmenu_selection']);
        add_action('submenu_file', [$this, 'submenu_selection']);

        // Add Separators
        add_action('admin_init', [$this, 'add_separators']);
    }

    public function register_menus()
    {
        $icon = $this->lsd_asset_url('img/listdom-icon.svg');

        $listdom_name = LSD_Branding::name();
        $listdom = esc_html($listdom_name);
        $licenses = esc_html__('Licenses', 'listdom');

        $pending = LSD_Payments_Engine::instance()->listdom() ? LSD_Payments::pending_count() : 0;
        if ($b = apply_filters('lsd_backend_main_badge', 0))
        {
            $listdom .= ' <span class="update-plugins count-' . esc_attr($b + $pending) . '"><span class="update-count">' . esc_html($b + $pending) . '</span></span>';
            $licenses .= ' <span class="update-plugins count-' . esc_attr($b) . '"><span class="update-count">' . esc_html($b) . '</span></span>';
        }

        add_menu_page(esc_html($listdom_name), $listdom, 'manage_options', 'listdom', null, $icon, 26);
        add_submenu_page('listdom', esc_html__('Home', 'listdom'), esc_html__('Home', 'listdom'), 'manage_options', 'listdom', [$this->dashboard, 'output'], 1);
        add_submenu_page('listdom', esc_html__('Shortcodes', 'listdom'), esc_html__('Shortcodes', 'listdom'), 'manage_options', 'edit.php?post_type=' . LSD_Base::PTYPE_SHORTCODE, null, 2);
        add_submenu_page('listdom', esc_html__('Search Builder', 'listdom'), esc_html__('Search and Filter Builder', 'listdom'), 'manage_options', 'edit.php?post_type=' . LSD_Base::PTYPE_SEARCH, null, 3);
        add_submenu_page('listdom', esc_html__('Notifications', 'listdom'), esc_html__('Notifications', 'listdom'), 'manage_options', 'edit.php?post_type=' . LSD_Base::PTYPE_NOTIFICATION, null, 4);

        if (LSD_Payments_Engine::instance()->listdom())
        {
            $payments = esc_html__('Payments', 'listdom');
            if ($pending) $payments .= ' <span class="update-plugins count-' . esc_attr($pending) . '"><span class="update-count">' . esc_html($pending) . '</span></span>';

            add_submenu_page('listdom', esc_html__('Payments', 'listdom'), $payments, 'manage_options', $this->payments_submenu_url(), null, 4.1);
        }

        add_submenu_page('listdom', esc_html__('Settings', 'listdom'), esc_html__('Settings', 'listdom'), 'manage_options', 'listdom-settings', [$this->settings, 'output'], 5);
        add_submenu_page('listdom', esc_html__('Import / Export', 'listdom'), esc_html__('Import / Export', 'listdom'), 'manage_options', 'listdom-ix', [$this->ix, 'output'], 6);
        add_submenu_page('listdom', esc_html__('Welcome to Listdom Setup Wizard', 'listdom'), esc_html__('Wizard', 'listdom'), 'manage_options', LSD_Base::WELCOME_SLUG, [$this->welcome, 'output'], 6.5);

        add_submenu_page('listdom', esc_html__('Addons', 'listdom'), '<span style="color: #ffd700; font-weight: bold;">' . esc_html__('Addons', 'listdom') . '</span>', 'manage_options', 'listdom-addons', [$this->addons, 'output'], 7);

        add_submenu_page('listdom', esc_html__('Documentation', 'listdom'), esc_html__('Documentation', 'listdom'), 'manage_options', LSD_Base::getListdomDocsURL(), null, 30);
        add_submenu_page('listdom', esc_html__('Support', 'listdom'), esc_html__('Support', 'listdom'), 'manage_options', LSD_Base::getSupportURL(), null, 31);

        if (apply_filters('lsd_display_activation_tab', true))
        {
            add_submenu_page('listdom', esc_html__('Licenses', 'listdom'), $licenses, 'manage_options', 'listdom-licenses', [$this->licenses, 'content'], 32);
        }
    }

    /**
     * @param mixed $parent_file
     * @return mixed
     */
    public function mainmenu_selection($parent_file)
    {
        global $current_screen;

        // Don't do anything if the post type is not Listdom Post Type
        $post_types = [
            LSD_Base::PTYPE_SHORTCODE,
            LSD_Base::PTYPE_SEARCH,
            LSD_Base::PTYPE_NOTIFICATION,
            LSD_Base::PTYPE_PLAN,
            LSD_Base::PTYPE_ORDER,
            LSD_Base::PTYPE_COUPON,
            LSD_Base::PTYPE_RECURRING,
        ];

        if (!in_array($current_screen->post_type, $post_types, true)) return $parent_file;

        return 'listdom';
    }

    /**
     * @param mixed $submenu_file
     * @return mixed
     */
    public function submenu_selection($submenu_file)
    {
        global $current_screen;

        $post_type = $current_screen->post_type ?? '';
        $taxonomy = $current_screen->taxonomy ?? '';

        if ($taxonomy === LSD_Base::TAX_TAX)
            return 'edit-tags.php?taxonomy=' . LSD_Base::TAX_TAX . '&post_type=' . LSD_Base::PTYPE_ORDER;

        // Don't do anything if the post type is not Listdom Post Type
        $post_types = [
            LSD_Base::PTYPE_SHORTCODE,
            LSD_Base::PTYPE_SEARCH,
            LSD_Base::PTYPE_NOTIFICATION,
            LSD_Base::PTYPE_PLAN,
            LSD_Base::PTYPE_ORDER,
            LSD_Base::PTYPE_COUPON,
            LSD_Base::PTYPE_RECURRING,
        ];

        if (!in_array($post_type, $post_types, true)) return $submenu_file;

        return 'edit.php?post_type=' . $post_type;
    }

    protected function payments_submenu_url(): string
    {
        $taxonomy = isset($_GET['taxonomy']) ? sanitize_key(wp_unslash($_GET['taxonomy'])) : '';
        if ($taxonomy === LSD_Base::TAX_TAX) return 'edit-tags.php?taxonomy=' . LSD_Base::TAX_TAX . '&post_type=' . LSD_Base::PTYPE_ORDER;

        $post_type = $this->payment_post_type_from_request();
        if ($post_type !== '') return 'edit.php?post_type=' . $post_type;

        return 'edit.php?post_type=' . LSD_Base::PTYPE_ORDER;
    }

    protected function payment_post_type_from_request(): string
    {
        $post_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : '';
        if ($post_type === '' && isset($_GET['post']))
        {
            $post_id = absint(wp_unslash($_GET['post']));
            if ($post_id)
            {
                $detected = get_post_type($post_id);
                if (is_string($detected)) $post_type = $detected;
            }
        }

        if (in_array($post_type, [
            LSD_Base::PTYPE_PLAN,
            LSD_Base::PTYPE_ORDER,
            LSD_Base::PTYPE_COUPON,
            LSD_Base::PTYPE_RECURRING,
        ], true)) return $post_type;

        return '';
    }

    public function add_separators(): bool
    {
        if (!is_admin()) return false;

        global $menu;
        if (!is_array($menu)) return false;

        $sep = null;
        $start = null;
        $listing = null;
        $plan = null;

        $i = 0;
        foreach ($menu as $m)
        {
            if (!$sep && isset($m['4']) && strpos($m['4'], 'menu-separator') !== false) $sep = $m;
            if (is_null($start) && isset($m['5']) && strpos($m['5'], 'page_listdom') !== false) $start = $i;
            if (is_null($listing) && isset($m['5']) && strpos($m['5'], LSD_Base::PTYPE_LISTING) !== false) $listing = $i;
            if (is_null($plan) && isset($m['5']) && strpos($m['5'], LSD_Base::PTYPE_PLAN) !== false) $plan = $i;

            $i++;
        }

        if (is_null($start)) return false;

        $end_menu = !is_null($plan) ? $plan : $listing;
        if (is_null($end_menu)) return false;

        if (!$sep) return false;

        $do_start = true;
        if ($start > 0 && isset($menu[$start - 1]['4']) && strpos($menu[$start - 1]['4'], 'menu-separator') !== false) $do_start = false;

        $do_end = true;
        if (isset($menu[$end_menu + 1]['4']) && strpos($menu[$end_menu + 1]['4'], 'menu-separator') !== false) $do_end = false;

        if ($do_start)
        {
            $menu = array_merge(
                array_slice($menu, 0, $start),
                [$sep],
                array_slice($menu, $start)
            );

            if ($end_menu >= $start) $end_menu++;
        }

        $end = $end_menu + 1;

        if ($do_end)
        {
            $menu = array_merge(
                array_slice($menu, 0, $end),
                [$sep],
                array_slice($menu, $end)
            );
        }

        if ($do_start)
        {
            if (isset($menu[$start - 1])) $menu[$start - 1]['4'] .= ' menu-top-last';
            if (isset($menu[$start + 1])) $menu[$start + 1]['4'] .= ' menu-top-first';
        }

        if ($do_end)
        {
            if (isset($menu[$end - 1])) $menu[$end - 1]['4'] .= ' menu-top-last';
            if (isset($menu[$end + 1])) $menu[$end + 1]['4'] .= ' menu-top-first';
        }

        return true;
    }

    public static function header($title = null, $url = null, $menus = [])
    {
        $base = new LSD_Base();

        // Current menu slug without extra parameters
        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

        // Set default title
        if (LSD_Base::isPro()) $default = esc_html(sprintf(__('%s Pro', 'listdom'), LSD_Branding::name()));
        else $default = esc_html(LSD_Branding::name());

        // Show Listdom version on the home page
        if ($page === 'listdom' && defined('LSD_VERSION')) $default .= ' <span class="lsd-dashboard-version">(' . esc_html(LSD_VERSION) . ')</span>';

        $base->include_html_file('plugin/header.php', [
            'parameters' => [
                'title' => $title ?? $default,
                'page' => $page,
                'url' => $url,
                'menus' => $menus,
            ],
        ]);
    }
}
