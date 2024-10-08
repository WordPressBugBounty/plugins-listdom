<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Location Taxonomy Class.
 *
 * @class LSD_Taxonomies_Location
 * @version    1.0.0
 */
class LSD_Taxonomies_Location extends LSD_Taxonomies
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
        add_action('init', [$this, 'register']);

        add_action(LSD_Base::TAX_LOCATION . '_add_form_fields', [$this, 'add_form']);
        add_action(LSD_Base::TAX_LOCATION . '_edit_form_fields', [$this, 'edit_form']);
        add_action('created_' . LSD_Base::TAX_LOCATION, [$this, 'save_metadata']);
        add_action('edited_' . LSD_Base::TAX_LOCATION, [$this, 'save_metadata']);

        add_filter('manage_edit-' . LSD_Base::TAX_LOCATION . '_columns', [$this, 'filter_columns']);
        add_filter('manage_' . LSD_Base::TAX_LOCATION . '_custom_column', [$this, 'filter_columns_content'], 10, 3);
        add_filter('manage_edit-' . LSD_Base::TAX_LOCATION . '_sortable_columns', [$this, 'filter_sortable_columns']);
    }

    public function register()
    {
        $args = [
            'label' => esc_html__('Locations', 'listdom'),
            'labels' => [
                'name' => esc_html__('Locations', 'listdom'),
                'singular_name' => esc_html__('Location', 'listdom'),
                'all_items' => esc_html__('All Locations', 'listdom'),
                'edit_item' => esc_html__('Edit Location', 'listdom'),
                'view_item' => esc_html__('View Location', 'listdom'),
                'update_item' => esc_html__('Update Location', 'listdom'),
                'add_new_item' => esc_html__('Add New Location', 'listdom'),
                'new_item_name' => esc_html__('New Location Name', 'listdom'),
                'popular_items' => esc_html__('Popular Locations', 'listdom'),
                'search_items' => esc_html__('Search Locations', 'listdom'),
                'separate_items_with_commas' => esc_html__('Separate locations with commas', 'listdom'),
                'add_or_remove_items' => esc_html__('Add or remove locations', 'listdom'),
                'choose_from_most_used' => esc_html__('Choose from the most used locations', 'listdom'),
                'not_found' => esc_html__('No locations found.', 'listdom'),
                'back_to_items' => esc_html__('← Back to Locations', 'listdom'),
                'parent_item' => esc_html__('Parent Location', 'listdom'),
                'parent_item_colon' => esc_html__('Parent Location:', 'listdom'),
                'no_terms' => esc_html__('No Locations', 'listdom'),
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => false,
            'hierarchical' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => LSD_Options::location_slug()],
        ];

        register_taxonomy(
            LSD_Base::TAX_LOCATION,
            LSD_Base::PTYPE_LISTING,
            apply_filters('lsd_taxonomy_location_args', $args)
        );

        register_taxonomy_for_object_type(LSD_Base::TAX_CATEGORY, LSD_Base::PTYPE_LISTING);
    }

    public function add_form()
    {
        ?>
        <div class="form-field">
            <label for="lsd_image"><?php esc_html_e('Image', 'listdom'); ?></label>
            <?php echo LSD_Form::imagepicker([
                'name' => 'lsd_image',
                'id' => 'lsd_image',
                'value' => '',
            ]); ?>
        </div>
        <?php
    }

    public function edit_form($term)
    {
        $image = get_term_meta($term->term_id, 'lsd_image', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_image"><?php esc_html_e('Image', 'listdom'); ?></label>
            </th>
            <td>
                <?php echo LSD_Form::imagepicker([
                    'name' => 'lsd_image',
                    'id' => 'lsd_image',
                    'value' => $image,
                ]); ?>
            </td>
        </tr>
        <?php
    }

    public function save_metadata($term_id): bool
    {
        // It's quick edit
        if (!isset($_POST['lsd_image'])) return false;

        $image = sanitize_text_field($_POST['lsd_image']);
        update_term_meta($term_id, 'lsd_image', $image);

        return true;
    }

    public function get_terms()
    {
        return get_terms([
            'taxonomy' => LSD_Base::TAX_LOCATION,
            'hide_empty' => false,
        ]);
    }

    public function filter_columns($columns)
    {
        $name = $columns['name'] ?? '';
        $slug = $columns['slug'] ?? '';
        $posts = $columns['posts'] ?? '';

        unset($columns['name']);
        unset($columns['description']);
        unset($columns['slug']);
        unset($columns['posts']);

        $columns['image'] = esc_html__('Image', 'listdom');
        $columns['name'] = $name;
        $columns['slug'] = $slug;
        $columns['posts'] = $posts;

        return $columns;
    }

    public function filter_columns_content($content, $column_name, $term_id)
    {
        switch ($column_name)
        {
            case 'image':

                $content = wp_get_attachment_image(get_term_meta($term_id, 'lsd_image', true), [50, 50]);
                break;

            default:
                break;
        }

        return $content;
    }
}
