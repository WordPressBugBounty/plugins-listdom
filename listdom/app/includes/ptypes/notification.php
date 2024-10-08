<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Notification Post Types Class.
 *
 * @class LSD_PTypes_Notification
 * @version    1.0.0
 */
class LSD_PTypes_Notification extends LSD_PTypes
{
    public $PT;
    protected $settings;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->PT = LSD_Base::PTYPE_NOTIFICATION;

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

        // Duplicate Notification
        new LSD_Duplicate($this->PT);
    }

    public function register_post_type()
    {
        $args = [
            'labels' => [
                'name' => esc_html__('Notifications', 'listdom'),
                'singular_name' => esc_html__('Notification', 'listdom'),
                'add_new' => esc_html__('Add Notification', 'listdom'),
                'add_new_item' => esc_html__('Add New Notification', 'listdom'),
                'edit_item' => esc_html__('Edit Notification', 'listdom'),
                'new_item' => esc_html__('New Notification', 'listdom'),
                'view_item' => esc_html__('View Notification', 'listdom'),
                'view_items' => esc_html__('View Notifications', 'listdom'),
                'search_items' => esc_html__('Search Notifications', 'listdom'),
                'not_found' => esc_html__('No notifications found!', 'listdom'),
                'not_found_in_trash' => esc_html__('No notifications found in Trash!', 'listdom'),
                'all_items' => esc_html__('All Notifications', 'listdom'),
                'archives' => esc_html__('Notification Archives', 'listdom'),
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

        register_post_type($this->PT, apply_filters('lsd_ptype_notification_args', $args));
    }

    public function filter_columns($columns)
    {
        // Remove date column
        unset($columns['date']);

        $columns['hook'] = esc_html__('Hook', 'listdom');
        return $columns;
    }

    public function filter_columns_content($column_name, $post_id)
    {
        if ($column_name == 'hook')
        {
            $hook = get_post_meta($post_id, 'lsd_hook', true);
            $hooks = LSD_Notifications::get_notification_hooks();

            echo $hooks[$hook] ?? 'N/A';
        }
    }

    public function register_metaboxes()
    {
        add_meta_box('lsd_metabox_placeholders', esc_html__('Placeholders', 'listdom'), [$this, 'metabox_placeholders'], $this->PT, 'side');
        add_meta_box('lsd_metabox_recipients', esc_html__('Recipients', 'listdom'), [$this, 'metabox_recipients'], $this->PT, 'side', 'low');
        add_meta_box('lsd_metabox_content', esc_html__('Content', 'listdom'), [$this, 'metabox_content'], $this->PT, 'normal', 'high');
    }

    public function metabox_placeholders($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/notification/placeholders.php', ['return_path' => true]);
    }

    public function metabox_recipients($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/notification/recipients.php', ['return_path' => true]);
    }

    public function metabox_content($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/notification/content.php', ['return_path' => true]);
    }

    public function save($post_id, $post)
    {
        // It's not a notification
        if ($post->post_type !== $this->PT) return;

        // Nonce is not set!
        if (!isset($_POST['_lsdnonce'])) return;

        // Nonce is not valid!
        if (!wp_verify_nonce(sanitize_text_field($_POST['_lsdnonce']), 'lsd_notification_cpt')) return;

        // We don't need to do anything on post auto save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // Get Listdom Data
        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Hook
        $hook = $lsd['hook'] ?? '';
        update_post_meta($post_id, 'lsd_hook', $hook);

        // Content
        $content = (isset($_POST['lsd']) and isset($_POST['lsd']['content'])) ? $_POST['lsd']['content'] : '';
        update_post_meta($post_id, 'lsd_content', $content);

        // Original Receiver
        $original_to = $lsd['original_to'] ?? 1;
        update_post_meta($post_id, 'lsd_original_to', $original_to);

        // To
        $to = isset($lsd['to']) ? preg_replace('/\s/', '', $lsd['to']) : '';
        update_post_meta($post_id, 'lsd_to', $to);

        // CC
        $cc = isset($lsd['cc']) ? preg_replace('/\s/', '', $lsd['cc']) : '';
        update_post_meta($post_id, 'lsd_cc', $cc);

        // BCC
        $bcc = isset($lsd['bcc']) ? preg_replace('/\s/', '', $lsd['bcc']) : '';
        update_post_meta($post_id, 'lsd_bcc', $bcc);
    }
}
