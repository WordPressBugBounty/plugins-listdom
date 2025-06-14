<?php

class LSD_Assets extends LSD_Base
{
    /**
     * @static
     * @var array
     */
    public static $params = [];
    public $settings = [];

    public function __construct()
    {
        // General Settings
        $this->settings = LSD_Options::settings();
    }

    public function init()
    {
        // Include needed assets (CSS, JavaScript etc.) in the WordPress backend
        add_action('admin_enqueue_scripts', [$this, 'admin'], 0);

        // Include needed assets (CSS, JavaScript etc.) in the WordPress frontend
        add_action('wp_enqueue_scripts', [$this, 'site'], 0);

        // Add custom styles to header
        add_action('wp_head', [$this, 'CSS'], 9999);

        // Register Listdom function to be called in WordPress footer hook
        if (is_admin()) add_action('admin_footer', [$this, 'load_footer'], 9999);
        else add_action('wp_footer', [$this, 'load_footer'], 9999);

        // Load Google Maps async
        add_filter('script_loader_tag', [$this, 'async_googlemaps'], 99, 2);

        // Elementor
        add_action('elementor/editor/after_enqueue_scripts', function ()
        {
            wp_enqueue_script('lsd-elementor-editor', $this->lsd_asset_url('js/elementor-editor.min.js'), ['jquery'], LSD_Assets::version());
        });

        add_action('elementor/frontend/after_enqueue_scripts', function ()
        {
            $this->isotope();
            $this->googlemaps();
            $this->leaflet();

            wp_enqueue_script('lsd-elementor-preview', $this->lsd_asset_url('js/elementor-preview.min.js'), ['jquery', 'lsd-frontend'], LSD_Assets::version(), true);
        });
    }

    public function site()
    {
        // Check to see if we should include the assets or not
        if (!$this->should_include()) return;

        // Include Listdom frontend script file
        wp_enqueue_script('lsd-frontend', $this->lsd_asset_url('js/frontend.min.js'), ['jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-slider'], $this->version(), true);

        // Localize Vars
        wp_localize_script('lsd-frontend', 'lsd', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'timepicker_format' => (isset($this->settings['timepicker_format']) ? (int) $this->settings['timepicker_format'] : 24),
        ]);

        // Include Listdom frontend CSS file
        wp_enqueue_style('lsd-frontend', $this->lsd_asset_url('css/frontend.css'), [], $this->version());

        // RTL Style
        if (is_rtl()) wp_enqueue_style('lsd-frontend-rtl', $this->lsd_asset_url('css/frontend-rtl.css'), ['lsd-frontend'], $this->version());

        // Include Personalize Assets
        $personalize = new LSD_Personalize();
        $personalize->assets();

        // Include Listdom font-awesome assets
        $this->fontawesome();

        // Include OWL
        $this->owl(['lsd-frontend']);

        // Include Lightbox
        $this->lightbox();

        // Include Select2
        $this->select2();

        // Include light-slider
        $this->lightslider();

        // Include no-ui-slider
        $this->nouislider();
    }

    public function admin()
    {
        // Include Listdom backend CSS file for WordPress
        wp_enqueue_style('lsd-wp-backend', $this->lsd_asset_url('css/wp-backend.css'), [], $this->version());

        // Check to see if we should include the assets or not
        if (!$this->should_include('backend')) return;

        // Include Listdom backend script file
        wp_enqueue_script('lsd-backend', $this->lsd_asset_url('js/backend.min.js'), ['jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable'], $this->version(), true);

        $icon_options = [];
        $fonts = LSD_Base::get_font_icons();
        foreach ($fonts as $font => $code) $icon_options[] = ['value' => $font, 'label' => $font];

        // Localize Vars
        wp_localize_script('lsd-backend', 'lsd', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'timepicker_format' => (isset($this->settings['timepicker_format']) ? (int) $this->settings['timepicker_format'] : 24),
            'i18n_field_search' => __('Add the “More Options” fields in the row below', 'listdom'),
            'i18n_field_delete' => __('Click twice to delete', 'listdom'),
            'i18n_field_label' => __('Label', 'listdom'),
            'i18n_placeholder_label' => __('Enter the menu name', 'listdom'),
            'i18n_placeholder_slug' => __('Enter the menu Slug', 'listdom'),
            'i18n_placeholder_icon' => __('Enter the icon class', 'listdom'),
            'i18n_description_label' => __('This is the label for your menu item.', 'listdom'),
            'i18n_description_slug' => __('Provide a unique slug for this menu.', 'listdom'),
            'i18n_description_icon' => __('Select the icon.', 'listdom'),
            'i18n_description_content' => __('Type dashboard content, You can also use shortcodes.', 'listdom'),
            'icon_options' => $icon_options,
        ]);

        // WP Editor
        wp_enqueue_editor();

        // Include Listdom backend CSS file
        wp_enqueue_style('lsd-backend', $this->lsd_asset_url('css/backend.css'), [], $this->version());

        // WordPress Media
        $this->media();

        // Include Listdom font-awesome assets
        $this->fontawesome();

        // Include Color Picker
        $this->colorpicker();

        // Include Select2
        $this->select2();

        // Listdom Icon
        $this->lsdi();

        // Include Assets
        do_action('lsd_admin_assets');
    }

    public function CSS()
    {
        // Add Custom Styles
        $styles = LSD_Options::styles();

        // There is no Custom Styles
        if (!trim($styles['CSS'])) return;

        $CSS = strip_tags($styles['CSS']);
        echo '<style>' . stripslashes($CSS) . '</style>';
    }

    public function fontawesome(): bool
    {
        // Fontawesome is disabled!
        if (isset($this->settings['fontawesome_status']) && !$this->settings['fontawesome_status']) return false;

        // Include the font icons
        wp_enqueue_style('fontawesome', $this->lsd_asset_url('packages/font-awesome/css/font-awesome.min.css'), [], LSD_Assets::version());
        return true;
    }

    public function owl($deps = [])
    {
        // Scripts
        wp_enqueue_script('owl', $this->lsd_asset_url('packages/owl-carousel/owl.carousel.min.js'), $deps, LSD_Assets::version());
    }

    public function isotope()
    {
        // Scripts
        wp_enqueue_script('isotope', $this->lsd_asset_url('packages/isotope/isotope.pkgd.min.js'), [], LSD_Assets::version());
    }

    public function grecaptcha()
    {
        // Scripts
        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js');
    }

    public function iconpicker()
    {
        // Include the icon-picker JS file
        wp_enqueue_script('jquery-fonticonpicker', $this->lsd_asset_url('packages/font-iconpicker/jquery.fonticonpicker.min.js'), [], LSD_Assets::version());
    }

    public function colorpicker()
    {
        // Include WordPress color picker JavaScript file
        wp_enqueue_script('wp-color-picker');

        // Include WordPress color picker CSS file
        wp_enqueue_style('wp-color-picker');
    }

    public static function lightslider()
    {
        $base = new LSD_Base();

        // Include Select2 JavaScript file
        wp_enqueue_script('lightslider', $base->lsd_asset_url('packages/lightslider/js/lightslider.min.js'), [], LSD_Assets::version());

        // Include Select2 CSS file
        wp_enqueue_style('lightslider', $base->lsd_asset_url('packages/lightslider/css/lightslider.min.css'), [], LSD_Assets::version());
    }

    public static function select2()
    {
        $base = new LSD_Base();

        // Include Select2 JavaScript file
        wp_enqueue_script('select2', $base->lsd_asset_url('packages/select2/select2.full.min.js'), [], LSD_Assets::version());

        // Include Select2 CSS file
        wp_enqueue_style('select2', $base->lsd_asset_url('packages/select2/select2.min.css'), [], LSD_Assets::version());
    }

    public static function nouislider()
    {
        $base = new LSD_Base();

        // Include no-ui-slider JavaScript file
        wp_enqueue_script('no-ui-slider', $base->lsd_asset_url('packages/nouislider/nouislider.min.js'), [], LSD_Assets::version());

        // Include no-ui-slider CSS file
        wp_enqueue_style('no-ui-slider', $base->lsd_asset_url('packages/nouislider/nouislider.min.css'), [], LSD_Assets::version());
    }

    public static function lightbox()
    {
        $base = new LSD_Base();

        // Simple Lightbox
        wp_enqueue_script('simplelightbox', $base->lsd_asset_url('packages/simplelightbox/simple-lightbox.jquery.min.js'), [], LSD_Assets::version());
        wp_enqueue_style('simplelightbox', $base->lsd_asset_url('packages/simplelightbox/simple-lightbox.min.css'), [], LSD_Assets::version());

        // Featherlight
        wp_enqueue_script('featherlight', $base->lsd_asset_url('packages/featherlight/fl.min.js'), [], LSD_Assets::version());
        wp_enqueue_style('featherlight', $base->lsd_asset_url('packages/featherlight/fl.min.css'), [], LSD_Assets::version());
    }

    public static function media()
    {
        // WordPress Media Thickbox
        wp_enqueue_media();
    }

    public function moment()
    {
        // Include Moment JavaScript file
        wp_enqueue_script('moment', $this->lsd_asset_url('packages/moment/moment.min.js'), [], LSD_Assets::version());
    }

    public function daterangepicker()
    {
        // Include Date Range Picker JavaScript file
        wp_enqueue_script('date-range-picker', $this->lsd_asset_url('packages/date-range-picker/drp.min.js'), ['moment', 'lsd-frontend'], LSD_Assets::version());

        // Include Date Range Picker CSS file
        wp_enqueue_style('date-range-picker', $this->lsd_asset_url('packages/date-range-picker/drp.min.css'), ['lsd-frontend'], LSD_Assets::version());
    }

    public function lsdi()
    {
        // Include Listdom Icomoon CSS file
        wp_enqueue_style('lsdi', $this->lsd_asset_url('packages/lsdi/lsdi.css'), [], LSD_Assets::version());
    }

    public static function api()
    {
        $base = new LSD_Base();

        // Dependencies
        $dependencies = [];

        if (is_admin()) $dependencies[] = 'lsd-backend';
        else $dependencies[] = 'lsd-frontend';

        // Enqueue the JS API
        wp_enqueue_script('lsd-api', $base->lsd_asset_url('js/api.min.js'), $dependencies, LSD_Assets::version(), true);
    }

    public static function map($draw = false)
    {
        // Map Provider
        $provider = LSD_Map_Provider::get();

        if ($provider === LSD_MP_GOOGLE) self::googlemaps();
        else if ($provider === LSD_MP_LEAFLET) self::leaflet($draw);
    }

    public static function leaflet($draw = false): bool
    {
        // Include Leaflet Javascript API
        $leaflet_include = apply_filters('lsd_leaflet_include', true);
        if (!$leaflet_include) return false;

        $base = new LSD_Base();

        // Dependencies
        $dependencies = [];

        if (is_admin()) $dependencies[] = 'lsd-backend';
        else $dependencies[] = 'lsd-frontend';

        // Enqueue the CSS
        wp_enqueue_style('leaflet', $base->lsd_asset_url('packages/leaflet/leaflet.css'), [], LSD_Assets::version());

        // Enqueue the JS API
        wp_enqueue_script('leaflet', $base->lsd_asset_url('packages/leaflet/leaflet.js'), $dependencies, LSD_Assets::version());

        if ($draw)
        {
            // Add Leaflet to Dependencies
            $dependencies[] = 'leaflet';

            // Enqueue the CSS
            wp_enqueue_style('leaflet-draw', $base->lsd_asset_url('packages/leaflet/leaflet.draw.css'), [], LSD_Assets::version());

            // Enqueue the JS API
            wp_enqueue_script('leaflet-draw', $base->lsd_asset_url('packages/leaflet/leaflet.draw.js'), $dependencies, LSD_Assets::version());
        }

        $omnivore = apply_filters('lsd_leaflet_omnivore_include', false);
        if ($omnivore)
        {
            // Add Leaflet to Dependencies
            $dependencies[] = 'leaflet';

            wp_enqueue_script('leaflet-omnivore', $base->lsd_asset_url('packages/leaflet/leaflet.omnivore.js'), $dependencies, LSD_Assets::version());
        }

        return true;
    }

    public static function googlemaps(): bool
    {
        // Listdom Settings
        $settings = LSD_Options::settings();

        // Include Google Maps Javascript API
        $gm_include = apply_filters('lsd_gm_include', true);
        if (!$gm_include) return false;

        // Dependencies
        $dependencies = [];

        if (is_admin()) $dependencies[] = 'lsd-backend';
        else $dependencies[] = 'lsd-frontend';

        // Enqueue the API
        wp_enqueue_script('googlemaps', '//maps.googleapis.com/maps/api/js?loading=async&libraries=places,drawing&callback=listdom_googlemaps_callback' . (isset($settings['googlemaps_api_key']) && trim($settings['googlemaps_api_key']) ? '&key=' . urlencode($settings['googlemaps_api_key']) : ''), $dependencies);

        return true;
    }

    public static function print()
    {
        $base = new LSD_Base();

        // Enqueue Print JS
        wp_enqueue_script('lsd-print', $base->lsd_asset_url('js/print.min.js'), [], LSD_Assets::version(), true);

        // Enqueue Print CSS
        wp_enqueue_style('lsd-print', $base->lsd_asset_url('css/print.css'), [], LSD_Assets::version(), 'print');
    }

    public function async_googlemaps($tag, $handle)
    {
        if ('googlemaps' !== $handle && 'richmarker-script' !== $handle) return $tag;

        return str_replace(' src=', ' async="async" defer="defer" src=', $tag);
    }

    public function get_googlemap_style($style)
    {
        // Get Style Path
        $path = $this->lsd_asset_path('map-styles/' . $style . '.json');

        // File Does Not Exist
        if (!LSD_File::exists($path)) return apply_filters('lsd_mapstyles_json', "''", $style);

        // Return the Style
        return LSD_File::read($path);
    }

    public static function footer($string)
    {
        self::params($string);
    }

    public static function footerOrPreview($string)
    {
        if (class_exists(\Elementor\Plugin::class) && \Elementor\Plugin::instance()->editor->is_edit_mode()) echo $string;
        else self::footer($string);
    }

    public static function params($param, $key = 'footer')
    {
        // Closure
        if ($param instanceof Closure)
        {
            ob_start();
            call_user_func($param);
            $param = ob_get_clean();
        }

        $key = (string) $key;
        $param = (string) $param;

        // Invalid Key & Param
        if (trim($param) === '' || trim($key) === '') return;

        // Register the key for removing PHP notices
        if (!isset(self::$params[$key])) self::$params[$key] = [];

        // Add it to the params
        self::$params[$key][] = $param;
    }

    public function load_footer()
    {
        if (!isset(self::$params['footer']) || !is_array(self::$params['footer']) || !count(self::$params['footer'])) return;

        // Remove duplicate strings
        $strings = array_unique(self::$params['footer']);

        // Print the assets in the footer
        foreach ($strings as $string) echo PHP_EOL . $string . PHP_EOL;
    }

    public function should_include($client = 'frontend')
    {
        // Always return true for frontend
        if ($client === 'frontend')
        {
            // If global settings is on return true
            if (!isset($this->settings['assets']['load']) || $this->settings['assets']['load']) return true;

            // If it's a listdom page return true
            if (LSD_Base::is_listdom_page()) return true;

            // Queried Object
            $query = get_queried_object();

            // Is Post
            if ($query instanceof WP_Post)
            {
                $config = $this->settings['assets'][$query->post_type] ?? [];

                if (is_array($config) && isset($config['load']) && !$config['load'])
                {
                    // Empty Items
                    if (!isset($config['items']) || (is_array($config['items']) && !count($config['items']))) return false;

                    // Not Matched
                    if (is_array($config['items']) && !in_array($query->ID, $config['items'])) return false;
                }
            }

            // Is Term
            if ($query instanceof WP_Term)
            {
                $config = $this->settings['assets'][$query->taxonomy] ?? [];

                if (is_array($config) && isset($config['load']) && !$config['load'])
                {
                    // Empty Items
                    if (!isset($config['items']) || (is_array($config['items']) && !count($config['items']))) return false;

                    // Not Matched
                    if (is_array($config['items']) && !in_array($query->term_id, $config['items'])) return false;
                }
            }

            return true;
        }
        else
        {
            // Current Screen
            $screen = get_current_screen();

            $base = $screen->base;
            $post_type = $screen->post_type;
            $taxonomy = $screen->taxonomy;

            // It's one of Listdom taxonomy pages
            if (trim($taxonomy) && in_array($taxonomy, $this->taxonomies())) return true;

            // It's one of Listdom post type pages
            if (trim($post_type) && in_array($post_type, $this->postTypes())) return true;

            // It's one of Listdom pages or the pages that Listdom should work fine
            if (trim($base))
            {
                foreach ([
                    'listdom-settings',
                    'listdom-ix',
                    LSD_Base::WELCOME_SLUG,
                    'listdom-addons',
                    'listdom-licenses',
                    'toplevel_page_listdom',
                    'widgets',
                    'plugins',
                ] as $menu_id)
                {
                    if (strpos($base, $menu_id) !== false) return true;
                }
            }

            return apply_filters('lsd_should_include_backend', false);
        }
    }

    public static function version(): string
    {
        $version = LSD_VERSION;
        if (defined('WP_DEBUG') && WP_DEBUG) $version .= '.' . time();

        return $version;
    }
}
