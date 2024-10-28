<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Main Listdom Class.
 *
 * @class Listdom
 * @version 1.0.0
 */
final class Listdom
{
    /**
     * Listdom version.
     *
     * @var string
     */
    public $version = '3.6.0';

    /**
     * The single instance of the class.
     *
     * @var Listdom
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * Main Listdom Instance.
     *
     * Ensures only one instance of Listdom is loaded or can be loaded.
     *
     * @return Listdom - Main instance.
     * @see Listdom()
     * @since 1.0.0
     * @static
     */
    public static function instance()
    {
        // Get an instance of Class
        if (is_null(self::$instance)) self::$instance = new self();

        // Return the instance
        return self::$instance;
    }

    /**
     * Listdom Constructor.
     */
    protected function __construct()
    {
        // Define Constants
        $this->define_constants();

        // Auto Loading
        require LSD_ABSPATH . '/vendor/autoload.php';

        // Auto Loader
        spl_autoload_register([$this, 'autoload']);

        // Initialize the Listdom
        $this->init();

        // Include Helper Functions
        $this->helpers();

        // Do listdom_loaded action
        do_action('listdom_loaded');
    }

    /**
     * Define Listdom Constants.
     */
    private function define_constants()
    {
        // Listdom Absolute Path
        if (!defined('LSD_ABSPATH')) define('LSD_ABSPATH', dirname(__FILE__));

        // Listdom Directory Name
        if (!defined('LSD_DIRNAME')) define('LSD_DIRNAME', basename(LSD_ABSPATH));

        // Listdom Plugin Base Name
        if (!defined('LSD_BASENAME')) define('LSD_BASENAME', plugin_basename(LSD_ABSPATH . '/listdom.php')); // listdom/listdom.php or listdom-pro/listdom.php

        // Listdom Version
        if (!defined('LSD_VERSION')) define('LSD_VERSION', $this->version);

        // Listdom Update Server
        if (!defined('LSD_LICENSING_SERVER')) define('LSD_LICENSING_SERVER', 'https://api.webilia.com/licensing');

        // WordPress Upload Directory
        $upload_dir = wp_upload_dir();

        // Listdom Logs Directory
        if (!defined('LSD_LOG_DIR')) define('LSD_LOG_DIR', $upload_dir['basedir'] . '/lsd-logs/');

        // Listdom Upload Directory
        if (!defined('LSD_UP_DIR')) define('LSD_UP_DIR', $upload_dir['basedir'] . '/listdom/');

        // Listdom Map Providers
        if (!defined('LSD_MP_GOOGLE')) define('LSD_MP_GOOGLE', 'googlemap');
        if (!defined('LSD_MP_LEAFLET')) define('LSD_MP_LEAFLET', 'leaflet');
    }

    /**
     * Initialize the Listdom
     */
    private function init()
    {
        // LSD Main
        $main = new LSD_Main();

        // Plugin Activation / Deactivation / Uninstall
        LSD_Plugin_Hooks::instance();

        // Life Cycle
        $life_cycle = new LSD_LifeCycle();
        $life_cycle->init();

        // Listdom Kses
        $kses = new LSD_Kses();
        $kses->init();

        // Listdom Menus
        $menus = new LSD_Menus();
        $menus->init();

        // Listdom Post Types
        $post_types = new LSD_PTypes();
        $post_types->init();

        // Listdom Taxonomies
        $taxonomies = new LSD_Taxonomies();
        $taxonomies->init();

        // Listdom Author
        $author = new LSD_Author();
        $author->init();

        // Listdom Actions / Filters
        $hooks = new LSD_Hooks();
        $hooks->init();

        // Flush WordPress rewrite rules only if needed
        LSD_RewriteRules::flush();

        // Listdom Assets
        $assets = new LSD_Assets();
        $assets->init();

        // Listdom Social Networks
        $socials = new LSD_Socials();
        $socials->init();

        // Listdom Skins
        $skins = new LSD_Skins();
        $skins->init();

        // Listdom Shortcodes
        $shortcodes = new LSD_Shortcodes();
        $shortcodes->init();

        // Listdom Widgets
        $widgets = new LSD_Widgets();
        $widgets->init();

        // Listdom Integrations
        $integrations = new LSD_Integrations();
        $integrations->init();

        // Listdom Endpoints
        $endpoints = new LSD_Endpoints();
        $endpoints->init();

        // Listdom Compatibility
        $compatibility = new LSD_Compatibility();
        $compatibility->init();

        // Listdom Internationalization
        $i18n = new LSD_i18n();
        $i18n->init();

        // Listdom Notifications
        $notifications = new LSD_Notifications();
        $notifications->init();

        // Listdom AJAX
        $ajax = new LSD_Ajax();
        $ajax->init();

        // Listdom Dummy Data
        $dummy = new LSD_Dummy();
        $dummy->init();

        // Listdom Search
        $search = new LSD_Search();
        $search->init();

        // Dashboard
        $dashboard = new LSD_Dashboard();
        $dashboard->init();

        // Upgrade
        $upgrade = new LSD_Upgrade();
        $upgrade->init();

        // License Activation
        $activation = new LSD_Activation();
        $activation->init();

        // Listing Statuses
        $statuses = new LSD_Statuses();
        $statuses->init();

        // Review Notice
        $review = new LSD_Plugin_Review();
        $review->init();

        // Initialize Lite Features
        if ($main->isLite())
        {
            $lite = new LSD_Lite();
            $lite->init();
        }
    }

    /**
     * Include Helper Functions
     */
    public function helpers()
    {
        // Template Functions
        require_once 'app/includes/helpers/templates.php';

        // Util Functions
        require_once 'app/includes/helpers/util.php';
    }

    /**
     * Automatically load Listdom classes whenever needed.
     * @param string $class_name
     * @return void
     */
    private function autoload(string $class_name)
    {
        $class_ex = explode('_', strtolower($class_name));

        // It's not a Listdom Class
        if ($class_ex[0] != 'lsd') return;

        // Drop Class Prefix
        $class_path = array_slice($class_ex, 1);

        // Create Class File Path
        $file_path = LSD_ABSPATH . '/app/includes/' . implode('/', $class_path) . '.php';

        // We found the class!
        if (file_exists($file_path)) require_once $file_path;
    }
}

/**
 * Main instance of Listdom.
 *
 * Returns the main instance of Listdom to prevent the need to use globals.
 *
 * @return Listdom
 * @since  1.0.0
 */
function listdom()
{
    return Listdom::instance();
}

// Init the Listdom :)
listdom();
