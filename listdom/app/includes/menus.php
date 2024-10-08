<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Menus Class.
 *
 * @class LSD_Menus
 * @version    1.0.0
 */
class LSD_Menus extends LSD_Base
{
    protected $dashboard;
    protected $settings;
    protected $ix;
    protected $addons;
    protected $welcome;
    public $tab;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        // Initialize menus
        $this->dashboard = new LSD_Menus_Dashboard();
        $this->settings = new LSD_Menus_Settings();
        $this->ix = new LSD_Menus_IX();
        $this->addons = new LSD_Menus_Addons();
        $this->welcome = new LSD_Menus_Welcome();

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

        $listdom = esc_html__('Listdom', 'listdom');
        $home = esc_html__('Home', 'listdom');

        if ($b = apply_filters('lsd_backend_main_badge', 0))
        {
            $listdom .= ' <span class="update-plugins count-' . $b . '"><span class="update-count">' . $b . '</span></span>';
            $home .= ' <span class="update-plugins count-' . $b . '"><span class="update-count">' . $b . '</span></span>';
        }

        add_menu_page(esc_html__('Listdom', 'listdom'), $listdom, 'manage_options', 'listdom', null, $icon, 26);
        add_submenu_page('listdom', esc_html__('Home', 'listdom'), $home, 'manage_options', 'listdom', [$this->dashboard, 'output'], 1);
        add_submenu_page('listdom', esc_html__('Shortcodes', 'listdom'), esc_html__('Shortcodes', 'listdom'), 'manage_options', 'edit.php?post_type=' . LSD_Base::PTYPE_SHORTCODE, null, 2);
        add_submenu_page('listdom', esc_html__('Search Builder', 'listdom'), esc_html__('Search and Filter Builder', 'listdom'), 'manage_options', 'edit.php?post_type=' . LSD_Base::PTYPE_SEARCH, null, 3);
        add_submenu_page('listdom', esc_html__('Notifications', 'listdom'), esc_html__('Notifications', 'listdom'), 'manage_options', 'edit.php?post_type=' . LSD_Base::PTYPE_NOTIFICATION, null, 4);
        add_submenu_page('listdom', esc_html__('Settings', 'listdom'), esc_html__('Settings', 'listdom'), 'manage_options', 'listdom-settings', [$this->settings, 'output'], 5);
        add_submenu_page('listdom', esc_html__('Import / Export', 'listdom'), esc_html__('Import / Export', 'listdom'), 'manage_options', 'listdom-ix', [$this->ix, 'output'], 6);
        add_submenu_page('listdom', esc_html__('Welcome to Listdom Setup Wizard', 'listdom'), esc_html__('Wizard', 'listdom'), 'manage_options', LSD_Base::WELCOME_SLUG, [$this->welcome, 'output'], 6.5);
        add_submenu_page('listdom', esc_html__('Addons', 'listdom'), '<span style="color: #ffd700; font-weight: bold;">' . esc_html__('Addons', 'listdom') . '</span>', 'manage_options', 'listdom-addons', [$this->addons, 'output'], 7);

        add_submenu_page('listdom', esc_html__('Documentation', 'listdom'), esc_html__('Documentation', 'listdom'), 'manage_options', LSD_Base::getListdomDocsURL(), null, 30);
        add_submenu_page('listdom', esc_html__('Support', 'listdom'), esc_html__('Support', 'listdom'), 'manage_options', LSD_Base::getSupportURL(), null, 31);
    }

    public function mainmenu_selection($parent_file)
    {
        global $current_screen;
        $post_type = $current_screen->post_type;

        // Don't do anything if the post type is not Listdom Post Type
        if (!in_array($post_type, [
            LSD_Base::PTYPE_SHORTCODE,
            LSD_Base::PTYPE_SEARCH,
            LSD_Base::PTYPE_NOTIFICATION,
        ])) return $parent_file;

        return 'listdom';
    }

    public function submenu_selection($submenu_file)
    {
        global $current_screen;
        $post_type = $current_screen->post_type;

        // Don't do anything if the post type is not Listdom Post Type
        if (!in_array($post_type, [
            LSD_Base::PTYPE_SHORTCODE,
            LSD_Base::PTYPE_SEARCH,
            LSD_Base::PTYPE_NOTIFICATION,
        ])) return $submenu_file;

        return 'edit.php?post_type=' . $post_type;
    }

    public function add_separators()
    {
        if (!is_admin()) return false;

        global $menu;
        if (!is_array($menu)) return false;

        $sep = null;
        $do_start = null;
        $start = null;
        $end = null;
        $do_end = null;

        $i = 0;
        $previous = null;
        foreach ($menu as $m)
        {
            // Next menu of end is separator, so we don't need to add separator again
            if ($end and is_null($do_end) and isset($m['4']) and strpos($m['4'], 'menu-separator') !== false) $do_end = false;
            else if ($end and is_null($do_end)) $do_end = true;

            if (!$sep and isset($m['4']) and strpos($m['4'], 'menu-separator') !== false) $sep = $m;
            if (!$start and isset($m['5']) and strpos($m['5'], 'page_listdom') !== false) $start = $i;
            if (!$end and isset($m['5']) and strpos($m['5'], 'listdom-listing') !== false) $end = $i + 2;

            // Previous menu of start is separator, so we don't need to add separator again
            if ($start and is_null($do_start) and isset($previous['4']) and strpos($previous['4'], 'menu-separator') !== false) $do_start = false;
            else if ($start and is_null($do_start)) $do_start = true;

            $i++;
            $previous = $m;

            if ($sep and $start and $end and !is_null($do_end)) break;
        }

        if (is_null($do_start)) $do_start = true;
        if (is_null($do_end)) $do_end = true;

        // Start not found! Maybe because current user is not administrator
        if (!$start) return false;

        // End not found!
        if (!$end) return false;

        // Separator not found!
        if (!$sep) return false;

        // Add First Separator
        if ($do_start) $menu = array_merge(
            array_slice($menu, 0, $start),
            [$sep],
            array_slice($menu, $start)
        );

        // Add Second Separator
        if ($do_end) $menu = array_merge(
            array_slice($menu, 0, $end),
            [$sep],
            array_slice($menu, $end)
        );

        if (isset($menu[$start - 1])) $menu[$start - 1][4] .= ' menu-top-last';
        if (isset($menu[$start + 1])) $menu[$start + 1][4] .= ' menu-top-first';

        if (isset($menu[$end - 1])) $menu[$end - 1][4] .= ' menu-top-last';
        if (isset($menu[$end + 1])) $menu[$end + 1][4] .= ' menu-top-first';

        return true;
    }
}
