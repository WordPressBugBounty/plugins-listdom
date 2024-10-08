<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Tag Taxonomy Class.
 *
 * @class LSD_Taxonomies_Tag
 * @version    1.0.0
 */
class LSD_Taxonomies_Tag extends LSD_Taxonomies
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
    }

    public function register()
    {
        $args = [
            'label' => esc_html__('Tags', 'listdom'),
            'labels' => [
                'name' => esc_html__('Tags', 'listdom'),
                'singular_name' => esc_html__('Tag', 'listdom'),
                'all_items' => esc_html__('All Tags', 'listdom'),
                'edit_item' => esc_html__('Edit Tag', 'listdom'),
                'view_item' => esc_html__('View Tag', 'listdom'),
                'update_item' => esc_html__('Update Tag', 'listdom'),
                'add_new_item' => esc_html__('Add New Tag', 'listdom'),
                'new_item_name' => esc_html__('New Tag Name', 'listdom'),
                'popular_items' => esc_html__('Popular Tags', 'listdom'),
                'search_items' => esc_html__('Search Tags', 'listdom'),
                'separate_items_with_commas' => esc_html__('Separate tags with commas', 'listdom'),
                'add_or_remove_items' => esc_html__('Add or remove tags', 'listdom'),
                'choose_from_most_used' => esc_html__('Choose from the most used tags', 'listdom'),
                'not_found' => esc_html__('No tags found.', 'listdom'),
                'back_to_items' => esc_html__('← Back to Tags', 'listdom'),
                'parent_item' => esc_html__('Parent Tag', 'listdom'),
                'parent_item_colon' => esc_html__('Parent Tag:', 'listdom'),
                'no_terms' => esc_html__('No Tags', 'listdom'),
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => false,
            'hierarchical' => false,
            'has_archive' => true,
            'rewrite' => ['slug' => LSD_Options::tag_slug()],
        ];

        register_taxonomy(
            LSD_Base::TAX_TAG,
            LSD_Base::PTYPE_LISTING,
            apply_filters('lsd_taxonomy_tag_args', $args)
        );

        register_taxonomy_for_object_type(LSD_Base::TAX_TAG, LSD_Base::PTYPE_LISTING);
    }
}
