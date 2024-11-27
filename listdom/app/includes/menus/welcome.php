<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Welcome Menu Class.
 *
 * @class LSD_Menus_Welcome
 * @version    1.0.0
 */
class LSD_Menus_Welcome extends LSD_Menus
{
    /**
     * @var string
     */
    public $tab;

    private $listdomer = 'listdomer';

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize the Menu
        $this->init();
    }

    public function init()
    {
        // Listdomer Installation & Activation
        add_action('wp_ajax_install_listdomer_theme', [$this, 'install_theme']);
        add_action('wp_ajax_activate_listdomer_theme', [$this, 'activate_theme']);
    }
    
    public function output()
    {
        // Get the current tab
        $this->tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'welcome';

        // Generate output
        $this->include_html_file('menus/welcome/tpl.php');
    }

    public function install_theme()
    {
        // Check for necessary permissions
        if (!current_user_can('install_themes')) $this->response(['success' => 0, 'message' => esc_html__('You do not have permission to install themes.', 'listdom')]);

        // Current Theme
        $current_theme = get_option('stylesheet');

        // Listdomer Already Exists
        if (wp_get_theme($this->listdomer)->exists())
        {
            if ($current_theme === $this->listdomer) $this->response(['success' => 1, 'message' => esc_html__('Listdomer theme is already installed and activated.', 'listdom'), 'status' => 'activated']);
            $this->response(['success' => 1, 'message' => esc_html__('Listdomer theme is installed but not activated.', 'listdom'), 'status' => 'installed']);
        }

        // Load the WordPress filesystem
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/theme.php';

        // Prepare the WordPress filesystem
        if (!WP_Filesystem()) $this->response(['success' => 0, 'message' => esc_html__('Failed to initialize filesystem.', 'listdom')]);

        // Theme Installation
        $upgrader = new Theme_Upgrader(new Theme_Installer_Skin());
        $install = $upgrader->install('https://downloads.wordpress.org/theme/' . $this->listdomer . '.zip');

        // Error Response
        if (is_wp_error($install)) $this->response(['success' => 0, 'message' => $install->get_error_message()]);

        // Success Response
        $this->response(['success' => 1, 'message' => esc_html__('Listdomer theme is installed. Please activate it to proceed.', 'listdom'), 'status' => 'installed']);
    }

    public function activate_theme()
    {
        // Access Check
        if (!current_user_can('switch_themes')) $this->response(['success' => 0, 'message' => esc_html__('You do not have permission to activate themes.', 'listdom')]);

        // Activate Theme
        switch_theme($this->listdomer);

        // Success Response
        if (get_option('stylesheet') === $this->listdomer) $this->response(['success' => 1, 'message' => esc_html__('Listdomer theme has been activated.', 'listdom')]);

        // Failure Response
        $this->response(['success' => 0, 'message' => esc_html__('Failed to activate Listdomer theme.', 'listdom')]);
    }
}
