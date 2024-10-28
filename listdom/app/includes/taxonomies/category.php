<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Category Taxonomy Class.
 *
 * @class LSD_Taxonomies_Category
 * @version    1.0.0
 */
class LSD_Taxonomies_Category extends LSD_Taxonomies
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
        add_action('add_meta_boxes', [$this, 'register_metaboxes'], 5, 2);

        add_action(LSD_Base::TAX_CATEGORY . '_add_form_fields', [$this, 'add_form']);
        add_action(LSD_Base::TAX_CATEGORY . '_edit_form_fields', [$this, 'edit_form']);
        add_action('created_' . LSD_Base::TAX_CATEGORY, [$this, 'save_metadata']);
        add_action('edited_' . LSD_Base::TAX_CATEGORY, [$this, 'save_metadata']);

        add_filter('manage_edit-' . LSD_Base::TAX_CATEGORY . '_columns', [$this, 'filter_columns']);
        add_filter('manage_' . LSD_Base::TAX_CATEGORY . '_custom_column', [$this, 'filter_columns_content'], 10, 3);
        add_filter('manage_edit-' . LSD_Base::TAX_CATEGORY . '_sortable_columns', [$this, 'filter_sortable_columns']);
    }

    public function register()
    {
        $args = [
            'label' => esc_html__('Categories', 'listdom'),
            'labels' => [
                'name' => esc_html__('Categories', 'listdom'),
                'singular_name' => esc_html__('Category', 'listdom'),
                'all_items' => esc_html__('All Categories', 'listdom'),
                'edit_item' => esc_html__('Edit Category', 'listdom'),
                'view_item' => esc_html__('View Category', 'listdom'),
                'update_item' => esc_html__('Update Category', 'listdom'),
                'add_new_item' => esc_html__('Add New Category', 'listdom'),
                'new_item_name' => esc_html__('New Category Name', 'listdom'),
                'popular_items' => esc_html__('Popular Categories', 'listdom'),
                'search_items' => esc_html__('Search Categories', 'listdom'),
                'separate_items_with_commas' => esc_html__('Separate categories with commas', 'listdom'),
                'add_or_remove_items' => esc_html__('Add or remove categories', 'listdom'),
                'choose_from_most_used' => esc_html__('Choose from the most used categories', 'listdom'),
                'not_found' => esc_html__('No categories found.', 'listdom'),
                'back_to_items' => esc_html__('← Back to Categories', 'listdom'),
                'parent_item' => esc_html__('Parent Category', 'listdom'),
                'parent_item_colon' => esc_html__('Parent Category:', 'listdom'),
                'no_terms' => esc_html__('No Categories', 'listdom'),
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => false,
            'hierarchical' => $this->isPro(),
            'has_archive' => true,
            'rewrite' => ['slug' => LSD_Options::category_slug()],
            'meta_box_cb' => false,
        ];

        register_taxonomy(
            LSD_Base::TAX_CATEGORY,
            LSD_Base::PTYPE_LISTING,
            apply_filters('lsd_taxonomy_category_args', $args)
        );

        register_taxonomy_for_object_type(LSD_Base::TAX_CATEGORY, LSD_Base::PTYPE_LISTING);
    }

    public function register_metaboxes()
    {
        add_meta_box('lsd_metabox_category', esc_html__('Category', 'listdom') . ' ' . LSD_Base::REQ_HTML, [$this, 'metabox_category'], LSD_Base::PTYPE_LISTING, 'side', 'low');
    }

    public function metabox_category($post)
    {
        // Primary Category
        $category = LSD_Entity_Listing::get_primary_category($post->ID);

        // Security Nonce
        LSD_Form::nonce('lsd_listing_cpt', '_lsdnonce');

        echo '<p>' . esc_html__('Please select primary category.', 'listdom') . '</p>';
        echo wp_dropdown_categories([
            'echo' => 0,
            'hide_empty' => 0,
            'name' => 'lsd[listing_category]',
            'id' => 'lsd_listing_category',
            'taxonomy' => LSD_Base::TAX_CATEGORY,
            'show_option_none' => '-----',
            'selected' => $category ? $category->term_id : null,
            'class' => 'widefat',
            'hierarchical' => 1,
            'orderby' => 'name',
            'order' => 'ASC',
            'required' => true,
            'option_none_value' => '',
        ]);

        // Additional Categories
        do_action('lsd_after_primary_category', $post, null);
    }

    public function add_form()
    {
        ?>
        <div class="form-field">
            <label for="lsd_icon"><?php esc_html_e('Icon', 'listdom'); ?></label>
            <?php echo LSD_Form::iconpicker([
                'name' => 'lsd_icon',
                'id' => 'lsd_icon',
                'value' => '',
            ]); ?>
            <p class="description"><?php esc_html_e("The icon is needed for listings' markers and some other sections.", 'listdom'); ?></p>
        </div>
        <div class="form-field">
            <label for="lsd_color"><?php esc_html_e('Color', 'listdom'); ?></label>
            <?php echo LSD_Form::colorpicker([
                'name' => 'lsd_color',
                'id' => 'lsd_color',
                'default' => '#1d7ed3',
                'value' => '#1d7ed3',
            ]); ?>
            <p class="description"><?php esc_html_e("It's listings' markers color.", 'listdom'); ?></p>
        </div>
        <div class="form-field">
            <label for="lsd_image"><?php esc_html_e('Image', 'listdom'); ?></label>
            <?php echo LSD_Form::imagepicker([
                'name' => 'lsd_image',
                'id' => 'lsd_image',
                'value' => '',
            ]); ?>
            <p class="description"><?php esc_html_e("The image used for category views/shortcodes.", 'listdom'); ?></p>
        </div>
        <div class="form-field">
            <?php if (!$this->isPro()): echo LSD_Base::alert($this->missFeatureMessage(esc_html__('SEO Schema', 'listdom')), 'warning'); ?>
            <?php else: ?>
                <label for="lsd_schema"><?php esc_html_e('Schema Type', 'listdom'); ?></label>
                <?php echo LSD_Form::url([
                    'name' => 'lsd_schema',
                    'id' => 'lsd_schema',
                    'value' => '',
                    'placeholder' => 'https://schema.org/LocalBusiness',
                ]); ?>
                <p class="description"><?php esc_html_e("Schema Item Type (https://schema.org/)", 'listdom'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    public function edit_form($term)
    {
        $icon = get_term_meta($term->term_id, 'lsd_icon', true);
        $color = get_term_meta($term->term_id, 'lsd_color', true);
        $image = get_term_meta($term->term_id, 'lsd_image', true);
        $schema = get_term_meta($term->term_id, 'lsd_schema', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_icon"><?php esc_html_e('Icon', 'listdom'); ?></label>
            </th>
            <td>
                <?php echo LSD_Form::iconpicker([
                    'name' => 'lsd_icon',
                    'id' => 'lsd_icon',
                    'value' => $icon,
                ]); ?>
                <p class="description"><?php esc_html_e("The icon is needed for listings' markers and some other sections.", 'listdom'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_color"><?php esc_html_e('Marker Color', 'listdom'); ?></label>
            </th>
            <td>
                <?php echo LSD_Form::colorpicker([
                    'name' => 'lsd_color',
                    'id' => 'lsd_color',
                    'default' => '#1d7ed3',
                    'value' => $color,
                ]); ?>
                <p class="description"><?php esc_html_e("It's listings' markers color.", 'listdom'); ?></p>
            </td>
        </tr>
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
                <p class="description"><?php esc_html_e("The image used for category views/shortcodes.", 'listdom'); ?></p>
            </td>
        </tr>
        <?php if ($this->isPro()): ?>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_schema"><?php esc_html_e('Schema Type', 'listdom'); ?></label>
            </th>
            <td>
                <?php echo LSD_Form::url([
                    'name' => 'lsd_schema',
                    'id' => 'lsd_schema',
                    'value' => $schema,
                    'placeholder' => 'https://schema.org/LocalBusiness',
                ]); ?>
                <p class="description"><?php esc_html_e("Schema Item Type (https://schema.org/)", 'listdom'); ?></p>
            </td>
        </tr>
    <?php endif; ?>
        <?php
    }

    public function save_metadata($term_id)
    {
        // It's quick edit
        if (!isset($_POST['lsd_icon'])) return false;

        $icon = sanitize_text_field($_POST['lsd_icon']);
        $color = isset($_POST['lsd_color']) ? sanitize_text_field($_POST['lsd_color']) : '';
        $image = isset($_POST['lsd_image']) ? sanitize_text_field($_POST['lsd_image']) : '';
        $schema = isset($_POST['lsd_schema']) && trim($_POST['lsd_schema']) ? sanitize_text_field($_POST['lsd_schema']) : 'https://schema.org/LocalBusiness';

        update_term_meta($term_id, 'lsd_icon', $icon);
        update_term_meta($term_id, 'lsd_color', $color);
        update_term_meta($term_id, 'lsd_image', $image);
        update_term_meta($term_id, 'lsd_schema', $schema);

        return true;
    }

    public static function get_terms()
    {
        return get_terms([
            'taxonomy' => LSD_Base::TAX_CATEGORY,
            'hide_empty' => false,
        ]);
    }

    public function filter_columns($columns)
    {
        $name = $columns['name'] ?? null;
        $slug = $columns['slug'] ?? null;
        $posts = $columns['posts'] ?? null;

        unset($columns['name']);
        unset($columns['description']);
        unset($columns['slug']);
        unset($columns['posts']);

        $columns['image'] = esc_html__('Image', 'listdom');
        $columns['icon'] = esc_html__('Icon', 'listdom');
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

            case 'icon':

                $content = '<div class="lsd-preview-color" style="background-color: ' . get_term_meta($term_id, 'lsd_color', true) . '"><div class="lsd-icon-wrapper">' . LSD_Taxonomies::icon($term_id, 'fa-lg') . '</div></div>';
                break;

            default:
                break;
        }

        return $content;
    }
}
