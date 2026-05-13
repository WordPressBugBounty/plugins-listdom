<?php

class LSD_Taxonomies_Tag extends LSD_Taxonomies
{
    public function init()
    {
        add_action('init', [$this, 'register']);
    }

    public function register()
    {
        $singular = lsd_t_label(LSD_Base::TAX_TAG);
        $plural = lsd_t_label(LSD_Base::TAX_TAG, 'plural');
        $singular_lc = lsd_t_label_lc(LSD_Base::TAX_TAG);
        $plural_lc = lsd_t_label_lc(LSD_Base::TAX_TAG, 'plural');

        $args = [
            'label' => $plural,
            'labels' => [
                'name' => $plural,
                'singular_name' => $singular,
                'all_items' => sprintf(esc_html__('All %s', 'listdom'), $plural),
                'edit_item' => sprintf(esc_html__('Edit %s', 'listdom'), $singular),
                'view_item' => sprintf(esc_html__('View %s', 'listdom'), $singular),
                'update_item' => sprintf(esc_html__('Update %s', 'listdom'), $singular),
                'add_new_item' => sprintf(esc_html__('Add New %s', 'listdom'), $singular),
                'new_item_name' => sprintf(esc_html__('New %s Name', 'listdom'), $singular),
                'popular_items' => sprintf(esc_html__('Popular %s', 'listdom'), $plural),
                'search_items' => sprintf(esc_html__('Search %s', 'listdom'), $plural),
                'separate_items_with_commas' => sprintf(esc_html__('Separate %s with commas', 'listdom'), $plural_lc),
                'add_or_remove_items' => sprintf(esc_html__('Add or remove %s', 'listdom'), $plural_lc),
                'choose_from_most_used' => sprintf(esc_html__('Choose from the most used %s', 'listdom'), $plural_lc),
                'not_found' => sprintf(esc_html__('No %s found.', 'listdom'), $plural_lc),
                'back_to_items' => sprintf(esc_html__('← Back to %s', 'listdom'), $plural),
                'parent_item' => sprintf(esc_html__('Parent %s', 'listdom'), $singular),
                'parent_item_colon' => sprintf(esc_html__('Parent %s:', 'listdom'), $singular),
                'no_terms' => sprintf(esc_html__('No %s', 'listdom'), $plural),
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
