<?php

class LSD_PTypes_Search extends LSD_PTypes
{
    public $PT;
    protected $settings;

    public function __construct()
    {
        $this->PT = LSD_Base::PTYPE_SEARCH;

        // Listdom Settings
        $this->settings = LSD_Options::settings();
    }

    public function init()
    {
        add_action('init', [$this, 'register_post_type']);

        add_filter('manage_' . $this->PT . '_posts_columns', [$this, 'filter_columns']);
        add_action('manage_' . $this->PT . '_posts_custom_column', [$this, 'filter_columns_content'], 10, 2);

        add_action('add_meta_boxes', [$this, 'register_metaboxes'], 10, 2);
        add_action('save_post', [$this, 'save'], 10, 2);

        add_action('wp_ajax_lsd_search_builder_params', [$this, 'params']);
        add_action('wp_ajax_lsd_search_builder_row_params', [$this, 'row']);

        // Duplicate Search
        new LSD_Duplicate($this->PT);
    }

    public function register_post_type()
    {
        $args = [
            'labels' => [
                'name' => esc_html__('Search and Filter Forms', 'listdom'),
                'singular_name' => esc_html__('Search Form', 'listdom'),
                'add_new' => esc_html__('Add a New Form', 'listdom'),
                'add_new_item' => esc_html__('Add New Search Form', 'listdom'),
                'edit_item' => esc_html__('Edit Search Form', 'listdom'),
                'new_item' => esc_html__('New Search Form', 'listdom'),
                'view_item' => esc_html__('View Search Form', 'listdom'),
                'view_items' => esc_html__('View Search Forms', 'listdom'),
                'search_items' => esc_html__('Search The Forms', 'listdom'),
                'not_found' => esc_html__('No search forms found!', 'listdom'),
                'not_found_in_trash' => esc_html__('No search forms found in Trash!', 'listdom'),
                'all_items' => esc_html__('All Search Forms', 'listdom'),
                'archives' => esc_html__('Search Form Archives', 'listdom'),
            ],
            'public' => false,
            'has_archive' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'supports' => ['title'],
            'capabilities' => [
                'edit_post' => 'manage_options',
                'read_post' => 'manage_options',
                'delete_post' => 'manage_options',
                'edit_posts' => 'manage_options',
                'edit_others_posts' => 'manage_options',
                'delete_posts' => 'manage_options',
                'publish_posts' => 'manage_options',
                'read_private_posts' => 'manage_options',
            ],
        ];

        register_post_type($this->PT, apply_filters('lsd_ptype_search_args', $args));
    }

    public function filter_columns($columns)
    {
        // Move the date column to the end
        $date = $columns['date'];
        unset($columns['date']);

        $columns['shortcode'] = esc_html__('Shortcode', 'listdom');
        $columns['date'] = $date;

        return $columns;
    }

    public function filter_columns_content($column_name, $post_id)
    {
        if ($column_name == 'shortcode')
        {
            echo '[listdom-search id="' . esc_attr($post_id) . '"]';
        }
    }

    public function register_metaboxes()
    {
        add_meta_box('lsd_metabox_form', esc_html__('Form', 'listdom'), [$this, 'metabox_form'], $this->PT, 'side');
        add_meta_box('lsd_metabox_results', esc_html__('Search Results', 'listdom'), [$this, 'metabox_results'], $this->PT, 'side');
        add_meta_box('lsd_metabox_shortcode', esc_html__('Shortcode', 'listdom'), [$this, 'metabox_shortcode'], $this->PT, 'side');
        add_meta_box('lsd_metabox_fields', esc_html__('Fields', 'listdom'), [$this, 'metabox_fields'], $this->PT, 'normal', 'high');
    }

    public function metabox_form($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/search/form.php', ['return_path' => true]);
    }

    public function metabox_results($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/search/results.php', ['return_path' => true]);
    }

    public function metabox_shortcode($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/search/shortcode.php', ['return_path' => true]);
    }

    public function metabox_fields($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/search/fields.php', ['return_path' => true]);
    }

    public function save($post_id, $post)
    {
        // It's not a search
        if ($post->post_type !== $this->PT) return;

        // Nonce is not set!
        if (!isset($_POST['_lsdnonce'])) return;

        // Nonce is not valid!
        if (!wp_verify_nonce(sanitize_text_field($_POST['_lsdnonce']), 'lsd_search_cpt')) return;

        // We don't need to do anything on post auto save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // Get Listdom Data
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Desktop Fields
        $desktop = $lsd['fields'] ?? [];
        update_post_meta($post_id, 'lsd_fields', $desktop);

        // Tablet Fields
        $tablet = $lsd['tablet'] ?? [];
        update_post_meta($post_id, 'lsd_tablet', $tablet);

        // Mobile Fields
        $mobile = $lsd['mobile'] ?? [];
        update_post_meta($post_id, 'lsd_mobile', $mobile);

        // Devices
        $devices = $lsd['devices'] ?? [];

        // Force to Inherit
        if (!count($tablet)) $devices['tablet']['inherit'] = 1;
        if (!count($mobile)) $devices['mobile']['inherit'] = 1;

        update_post_meta($post_id, 'lsd_devices', $devices);

        /**
         * Form Options
         */

        $form = $lsd['form'] ?? [];

        // Target Page Mode
        if (isset($form['page']) && trim($form['page']))
        {
            $form['connected_shortcodes'] = [];
            $form['ajax'] = 0;
        }
        // Connected Shortcodes Mode
        else
        {
            $form['shortcode'] = '';
        }

        // Empty Values
        if (!isset($form['connected_shortcodes'])) $form['connected_shortcodes'] = [];
        if (!isset($form['ajax'])) $form['ajax'] = 0;

        // Save Form Options
        update_post_meta($post_id, 'lsd_form', $form);
    }

    public function params()
    {
        $device_key = isset($_POST['device_key']) ? sanitize_text_field($_POST['device_key']) : 'fields';
        $i = isset($_POST['i']) ? sanitize_text_field($_POST['i']) : 1;
        $key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : null;
        $title = isset($_POST['title']) ? trim(sanitize_text_field($_POST['title'])) : null;

        $builder = new LSD_Search_Builder();
        $html = $builder->params($device_key, $key, ['title' => $title], $i);

        $this->response(['success' => 1, 'html' => $html]);
    }

    public function row()
    {
        $i = isset($_POST['i']) ? sanitize_text_field($_POST['i']) : 1;
        $post_id = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : 0;
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'row';
        $device_key = isset($_POST['device_key']) ? sanitize_text_field($_POST['device_key']) : 'fields';

        $builder = new LSD_Search_Builder();
        $html = $builder->row($post_id, $device_key, [
            'type' => $type,
            'buttons' => ['status' => 1],
            'clear' => ['status' => 0],
        ], $i);

        $this->response(['success' => 1, 'html' => $html]);
    }
}
