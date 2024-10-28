<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Listing Post Types Class.
 *
 * @class LSD_PTypes_Listing
 * @version    1.0.0
 */
class LSD_PTypes_Listing extends LSD_PTypes
{
    public $PT;
    protected $settings;
    protected $details_page_options;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->PT = LSD_Base::PTYPE_LISTING;

        // Listdom Settings
        $this->settings = LSD_Options::settings();

        // Details Page options
        $this->details_page_options = LSD_Options::details_page();
    }

    public function init()
    {
        add_action('init', [$this, 'register_post_type']);

        add_filter('manage_' . $this->PT . '_posts_columns', [$this, 'filter_columns']);
        add_action('manage_' . $this->PT . '_posts_custom_column', [$this, 'filter_columns_content'], 10, 2);

        // Search Options
        add_action('restrict_manage_posts', [$this, 'add_filters']);
        add_filter('posts_join', [$this, 'filters_join']);
        add_filter('posts_where', [$this, 'filters_where']);

        add_action('add_meta_boxes', [$this, 'register_metaboxes'], 10, 2);
        add_action('save_post', [$this, 'save'], 10, 2);
        add_filter('post_type_link', [$this, 'filter_link'], 10, 2);

        // Delete
        add_action('delete_post', [$this, 'delete']);

        // New Status
        add_action('transition_post_status', [$this, 'status'], 10, 3);

        // Auto Create Listing User
        add_action('lsd_listing_status_changed', [$this, 'assign_user'], 10, 3);

        // Disable Comments
        add_filter('comments_open', [$this, 'comments_open'], 10, 2);

        // Single Page
        $single = new LSD_PTypes_Listing_Single();
        $single->hooks();

        // Archive Pages
        $archive = new LSD_PTypes_Listing_Archive();
        $archive->hooks();

        // Duplicate Listing
        new LSD_Duplicate($this->PT);

        // Avada Builder Integration
        add_action('awb_remove_third_party_the_content_changes', [$single, 'unhook']);
        add_action('awb_readd_third_party_the_content_changes', [$single, 'hooks']);

        // WPML Duplicate
        add_action('icl_make_duplicate', [$this, 'icl_duplicate'], 10, 4);
        add_action('icl_pro_translation_saved', [$this, 'wpml_pro_translation_saved'], 10, 3);
    }

    public function register_post_type()
    {
        $supports = apply_filters('lsd_ptype_listing_supports', ['title', 'editor', 'thumbnail', 'author', 'excerpt']);
        $icon = $this->lsd_asset_url('img/listings-icon.svg');

        $args = [
            'labels' => [
                'name' => esc_html__('Listings', 'listdom'),
                'singular_name' => esc_html__('Listing', 'listdom'),
                'add_new' => esc_html__('Add Listing', 'listdom'),
                'add_new_item' => esc_html__('Add New Listing', 'listdom'),
                'edit_item' => esc_html__('Edit Listing', 'listdom'),
                'new_item' => esc_html__('New Listing', 'listdom'),
                'view_item' => esc_html__('View Listing', 'listdom'),
                'view_items' => esc_html__('View Listings', 'listdom'),
                'search_items' => esc_html__('Search Listings', 'listdom'),
                'not_found' => esc_html__('No listings found!', 'listdom'),
                'not_found_in_trash' => esc_html__('No listings found in Trash!', 'listdom'),
                'all_items' => esc_html__('All Listings', 'listdom'),
                'archives' => esc_html__('Listing Archives', 'listdom'),
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => false,
            'supports' => $supports,
            'rewrite' => [
                'slug' => LSD_Options::slug(),
                'ep_mask' => LSD_Base::EP_LISTING,
            ],
            'menu_icon' => $icon,
            'menu_position' => 27,
        ];

        register_post_type($this->PT, apply_filters('lsd_ptype_listing_args', $args));
    }

    public function filter_columns($columns)
    {
        // Move the date column to the end
        $date = $columns['date'];
        $author = $columns['author'];

        unset($columns['date']);
        unset($columns['author']);

        $columns['address'] = esc_html__('Address', 'listdom');
        $columns['category'] = esc_html__('Category', 'listdom');
        $columns['author'] = $author;
        $columns['date'] = $date;

        return $columns;
    }

    public function filter_columns_content($column_name, $post_id)
    {
        if ($column_name == 'category')
        {
            // Primary Category
            $category = LSD_Entity_Listing::get_primary_category($post_id);

            echo $category && isset($category->name) ? '<strong>' . esc_html($category->name) . '</strong>' : '';
        }
        else if ($column_name == 'address')
        {
            echo esc_html(get_post_meta($post_id, 'lsd_address', true));
        }
    }

    public function register_metaboxes()
    {
        add_meta_box('lsd_metabox_address', esc_html__('Location', 'listdom'), [$this, 'metabox_address'], $this->PT, 'normal', 'high');
        add_meta_box('lsd_metabox_details', esc_html__('Details', 'listdom'), [$this, 'metabox_details'], $this->PT, 'normal', 'high');

        // Register Metaboxes
        do_action('lsd_register_metaboxes');
    }

    public function metabox_address($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/listing/address.php', ['return_path' => true]);
    }

    public function metabox_details($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/listing/details.php', ['return_path' => true]);
    }

    public function save($post_id, $post)
    {
        // It's not a listing
        if ($post->post_type !== $this->PT) return;

        // Nonce is not set!
        if (!isset($_POST['_lsdnonce'])) return;

        // Nonce is not valid!
        if (!wp_verify_nonce(sanitize_text_field($_POST['_lsdnonce']), 'lsd_listing_cpt')) return;

        // We don't need to do anything on post auto save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // Get Listdom Data
        $lsd = $_POST['lsd'] ?? [];

        // Gallery
        if (isset($lsd['_gallery']) and is_array($lsd['_gallery']))
        {
            $lsd['gallery'] = $lsd['_gallery'];
            unset($lsd['_gallery']);
        }

        // Embeds
        if (isset($lsd['_embeds']) and is_array($lsd['_embeds']))
        {
            $lsd['embeds'] = $lsd['_embeds'];
            unset($lsd['_embeds']);
        }

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Save the Data
        $entity = new LSD_Entity_Listing($post_id);
        $entity->save($lsd);
    }

    public function delete($post_id)
    {
        // Post
        $post = get_post($post_id);

        // It's not a listing
        if ($post->post_type != LSD_Base::PTYPE_LISTING) return false;

        $db = new LSD_db();
        return $db->q("DELETE FROM `#__lsd_data` WHERE `id`=" . esc_sql($post_id), 'DELETE');
    }

    public function status($new_status, $old_status, $post)
    {
        if ($post->post_type === $this->PT and $new_status !== $old_status)
        {
            do_action('lsd_listing_status_changed', $post->ID, $old_status, $new_status);
        }
    }

    public function filter_link($url, $post)
    {
        if (LSD_Base::PTYPE_LISTING == get_post_type($post))
        {
            $link = get_post_meta($post->ID, 'lsd_link', true);
            return (trim($link) and filter_var($link, FILTER_VALIDATE_URL)) ? $link : $url;
        }

        return $url;
    }

    public function add_filters($post_type)
    {
        if ($post_type != $this->PT) return;

        $taxonomy = LSD_Base::TAX_CATEGORY;
        if (wp_count_terms($taxonomy)) wp_dropdown_categories([
            'show_option_all' => esc_html__('Show all categories', 'listdom'),
            'taxonomy' => $taxonomy,
            'name' => $taxonomy,
            'value_field' => 'slug',
            'orderby' => 'name',
            'order' => 'ASC',
            'selected' => isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : '',
            'show_count' => false,
            'hide_empty' => false,
            'hierarchical' => 1,
        ]);

        $taxonomy = LSD_Base::TAX_LOCATION;
        if (wp_count_terms($taxonomy)) wp_dropdown_categories([
            'show_option_all' => esc_html__('Show all locations', 'listdom'),
            'taxonomy' => $taxonomy,
            'name' => $taxonomy,
            'value_field' => 'slug',
            'orderby' => 'name',
            'order' => 'ASC',
            'selected' => isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : '',
            'show_count' => false,
            'hide_empty' => false,
        ]);

        $taxonomy = LSD_Base::TAX_FEATURE;
        if (wp_count_terms($taxonomy)) wp_dropdown_categories([
            'show_option_all' => esc_html__('Show all features', 'listdom'),
            'taxonomy' => $taxonomy,
            'name' => $taxonomy,
            'value_field' => 'slug',
            'orderby' => 'name',
            'order' => 'ASC',
            'selected' => isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : '',
            'show_count' => false,
            'hide_empty' => false,
        ]);

        $taxonomy = LSD_Base::TAX_LABEL;
        if (wp_count_terms($taxonomy)) wp_dropdown_categories([
            'show_option_all' => esc_html__('Show all labels', 'listdom'),
            'taxonomy' => $taxonomy,
            'name' => $taxonomy,
            'value_field' => 'slug',
            'orderby' => 'name',
            'order' => 'ASC',
            'selected' => isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : '',
            'show_count' => false,
            'hide_empty' => false,
        ]);
    }

    public function filters_join($join)
    {
        global $pagenow, $wpdb;
        if (is_admin() && $pagenow == 'edit.php' && !empty($_GET['post_type']) && $_GET['post_type'] == $this->PT && !empty($_GET['s']) && filter_var($_GET['s'], FILTER_VALIDATE_EMAIL))
        {
            $join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
        }

        return $join;
    }

    public function filters_where($where)
    {
        global $pagenow, $wpdb;
        if (is_admin() && $pagenow == 'edit.php' && !empty($_GET['post_type']) && $_GET['post_type'] == $this->PT && !empty($_GET['s']) && filter_var($_GET['s'], FILTER_VALIDATE_EMAIL))
        {
            $where = preg_replace("/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*('[^']+')\s*\)/", "(" . $wpdb->posts . ".`post_title` LIKE $1) OR (" . $wpdb->postmeta . ".`meta_key`='lsd_guest_email' AND " . $wpdb->postmeta . ".`meta_value` LIKE $1)", $where);
        }

        return $where;
    }

    public function icl_duplicate($master_post_id, $lang, $post, $id)
    {
        $master = get_post($master_post_id);
        $target = get_post($id);

        if ($master->post_type !== $this->PT) return;
        if ($target->post_type !== $this->PT) return;

        $primary_category_id = get_post_meta($master_post_id, 'lsd_primary_category', true);
        $target_category_id = apply_filters('wpml_object_id', $primary_category_id, LSD_Base::TAX_CATEGORY, true, $lang);

        update_post_meta($id, 'lsd_primary_category', $target_category_id);
    }

    public function wpml_pro_translation_saved($new_post_id, $fields, $job)
    {
        /** @var TranslationManagement $iclTranslationManagement */
        global $iclTranslationManagement;

        $master_post_id = null;
        if (is_object($job) and $iclTranslationManagement)
        {
            $element_type_prefix = $iclTranslationManagement->get_element_type_prefix_from_job($job);
            $original_post = $iclTranslationManagement->get_post($job->original_doc_id, $element_type_prefix);

            if ($original_post) $master_post_id = $original_post->ID;
        }

        if (!$master_post_id) return;

        // Target Language
        $lang_options = apply_filters('wpml_post_language_details', null, $new_post_id);
        $lang = is_array($lang_options) && isset($lang_options['language_code']) ? $lang_options['language_code'] : '';

        // Duplicate Content
        $this->icl_duplicate($master_post_id, $lang, (new stdClass()), $new_post_id);
    }

    /**
     * @param $listing_id
     * @param $old_status
     * @param $new_status
     * @return void
     */
    public function assign_user($listing_id, $old_status, $new_status)
    {
        // Only run when listing is published
        if ($new_status !== 'publish') return;

        // Registration Method
        $guest_registration = $this->settings['submission_guest_registration'] ?? 'approval';

        // Create user and assign listing to new user
        $guest_email = get_post_meta($listing_id, 'lsd_guest_email', true);

        // Create and Assign User
        if (is_email($guest_email) && $guest_registration === 'approval')
        {
            // Guest Name
            $fullname = (string) get_post_meta($listing_id, 'lsd_guest_fullname', true);

            // Create and Assign User
            LSD_User::listing(
                $listing_id,
                $guest_email,
                '',
                $fullname
            );
        }

        // Assign User if already not assigned. We need to keep this in case
        // the guest registration method is set to submission
        if (is_email($guest_email))
        {
            $user = get_user_by_email($guest_email);
            $owner_id = (int) get_post_field('post_author', $listing_id);

            // Owner is different
            if ($user && $user->ID !== $owner_id) LSD_Main::assign($listing_id, $user->ID);
        }
    }

    public function comments_open($comments_open, $post_id): bool
    {
        $post = get_post($post_id);

        // Listing Post Type
        if ($post && $post->post_type === $this->PT)
        {
            // Details Page options
            $options = LSD_Options::details_page();

            // Comments are disabled globally for listings
            if (isset($options['general']['comments']) && !$options['general']['comments']) return false;
        }

        return $comments_open;
    }
}

