<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom General AJAX Class.
 *
 * @class LSD_Ajax
 * @version    1.0.0
 */
class LSD_Ajax extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        // Get Map Objects
        add_action('wp_ajax_lsd_ajax_search', [$this, 'search']);
        add_action('wp_ajax_nopriv_lsd_ajax_search', [$this, 'search']);

        // AutoSuggest
        add_action('wp_ajax_lsd_autosuggest', [$this, 'autosuggest']);
        add_action('wp_ajax_nopriv_lsd_autosuggest', [$this, 'autosuggest']);

        // Hierarchical Dropdowns
        add_action('wp_ajax_lsd_hierarchical_terms', [$this, 'terms']);
        add_action('wp_ajax_nopriv_lsd_hierarchical_terms', [$this, 'terms']);
    }

    public function search()
    {
        $args = (isset($_POST['args']) and is_array($_POST['args'])) ? $_POST['args'] : [];

        // Get From Request
        if (!count($args)) $args = $_POST;

        // Sanitization
        array_walk_recursive($args, 'sanitize_text_field');

        $atts = (isset($args['atts']) and is_array($args['atts'])) ? $args['atts'] : [];

        // Listdom Shortcode
        $LSD = new LSD_Shortcodes_Listdom();

        // Skin
        $skin = (isset($atts['lsd_display']) and isset($atts['lsd_display']['skin'])) ? sanitize_text_field($atts['lsd_display']['skin']) : $LSD->get_default_skin();

        // Get Skin Object
        $SKO = $LSD->SKO($skin);

        // Start the skin
        $SKO->start($atts);
        $SKO->after_start();

        // Current View
        $SKO->setField('default_view', (isset($_POST['view']) ? sanitize_text_field($_POST['view']) : 'grid'));

        // Generate the Query
        $SKO->query();

        // Apply Search
        $SKO->apply_search($_POST, 'map');

        // Get Map Objects
        $archive = new LSD_PTypes_Listing_Archive();
        $objects = $archive->render_map_objects($SKO->search(), $args);

        // Change the limit
        $SKO->setLimit();

        // Get Listings
        $IDs = $SKO->search();
        $SKO->setField('listings', $IDs);

        $listings = $SKO->listings_html();
        $total = $SKO->getField('found_listings');
        $next_page = $SKO->getField('next_page');

        $this->response([
            'objects' => $objects,
            'listings' => LSD_Kses::full($listings),
            'next_page' => $next_page,
            'count' => count($IDs),
            'total' => $total,
        ]);
    }

    public function autosuggest()
    {
        // Check if security nonce is set
        if (!isset($_REQUEST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is required.", 'listdom')]);

        // Verify that the nonce is valid
        if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'lsd_autosuggest')) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is invalid.", 'listdom')]);

        $term = isset($_REQUEST['term']) ? sanitize_text_field($_REQUEST['term']) : '';
        $source = isset($_REQUEST['source']) ? sanitize_text_field($_REQUEST['source']) : '';

        $found = false;
        $items = '<ul class="lsd-autosuggest-items">';

        if ($source === 'users')
        {
            $users = get_users([
                'search' => '*' . $term . '*',
                'search_columns' => ['user_email', 'display_name'],
                'number' => 10,
            ]);

            foreach ($users as $user)
            {
                $found = true;
                $items .= '<li data-value="' . esc_attr($user->ID) . '">' . $user->user_email . '</li>';
            }
        }
        else
        {
            $posts = get_posts([
                'post_type' => $source,
                's' => $term,
                'numberposts' => 10,
                'post_status' => 'publish',
            ]);

            foreach ($posts as $post)
            {
                $found = true;
                $items .= '<li data-value="' . esc_attr($post->ID) . '">' . $post->post_title . '</li>';
            }
        }

        $items .= '</ul>';

        $this->response([
            'success' => ((int) $found),
            'items' => $items,
        ]);
    }

    public function terms()
    {
        // Check if security nonce is set
        if (!isset($_REQUEST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is required.", 'listdom')]);

        // Verify that the nonce is valid
        if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'lsd_search_form')) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is invalid.", 'listdom')]);

        // Taxonomy
        $taxonomy = $_REQUEST['taxonomy'] ?? LSD_Base::TAX_LOCATION;
        $hide_empty = $_REQUEST['hide_empty'] ?? 0;
        $parent = $_REQUEST['parent'] ?? 0;

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'parent' => $parent,
            'hide_empty' => $hide_empty,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $items = [];
        foreach ($terms as $term)
        {
            $items[] = [
                'id' => $term->term_id,
                'name' => $term->name,
                'parent' => $term->parent,
            ];
        }

        $this->response([
            'success' => 1,
            'found' => count($terms) ? 1 : 0,
            'items' => $items,
        ]);
    }
}
