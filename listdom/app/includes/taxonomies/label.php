<?php

class LSD_Taxonomies_Label extends LSD_Taxonomies
{
    public function init()
    {
        add_action('init', [$this, 'register']);

        add_action(LSD_Base::TAX_LABEL . '_add_form_fields', [$this, 'add_form']);
        add_action(LSD_Base::TAX_LABEL . '_edit_form_fields', [$this, 'edit_form']);
        add_action('created_' . LSD_Base::TAX_LABEL, [$this, 'save_metadata']);
        add_action('edited_' . LSD_Base::TAX_LABEL, [$this, 'save_metadata']);
    }

    public function register()
    {
        $singular = lsd_t_label(LSD_Base::TAX_LABEL);
        $plural = lsd_t_label(LSD_Base::TAX_LABEL, 'plural');
        $singular_lc = lsd_t_label_lc(LSD_Base::TAX_LABEL);
        $plural_lc = lsd_t_label_lc(LSD_Base::TAX_LABEL, 'plural');

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
            'rewrite' => ['slug' => LSD_Options::label_slug()],
        ];

        register_taxonomy(
            LSD_Base::TAX_LABEL,
            LSD_Base::PTYPE_LISTING,
            apply_filters('lsd_taxonomy_label_args', $args)
        );

        register_taxonomy_for_object_type(LSD_Base::TAX_LABEL, LSD_Base::PTYPE_LISTING);
    }

    public function add_form()
    {
        ?>
        <div class="form-field">
            <label for="lsd_color"><?php esc_html_e('Color', 'listdom'); ?></label>
            <?php echo LSD_Form::colorpicker([
                'name' => 'lsd_color',
                'id' => 'lsd_color',
                'default' => '#1d7ed3',
                'value' => '#1d7ed3',
            ]); ?>
        </div>
        <?php $this->archive_shortcode_add_field(); ?>
        <?php
        wp_nonce_field('lsd_save_label_meta', 'lsd_label_meta_nonce');
    }

    public function edit_form($term)
    {
        $color = get_term_meta($term->term_id, 'lsd_color', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_color"><?php esc_html_e('Color', 'listdom'); ?></label>
            </th>
            <td>
                <?php echo LSD_Form::colorpicker([
                    'name' => 'lsd_color',
                    'id' => 'lsd_color',
                    'default' => '#1d7ed3',
                    'value' => $color,
                ]); ?>
            </td>
        </tr>
        <?php $this->archive_shortcode_edit_field($term); ?>
        <?php
        wp_nonce_field('lsd_save_label_meta', 'lsd_label_meta_nonce');
    }

    public function save_metadata($term_id)
    {
        // It's quick edit
        if (!isset($_POST['lsd_color'])) return;

        $nonce = isset($_POST['lsd_label_meta_nonce']) ? sanitize_text_field(wp_unslash($_POST['lsd_label_meta_nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'lsd_save_label_meta')) return;

        $taxonomy = get_taxonomy(LSD_Base::TAX_LABEL);
        if (!$taxonomy || !current_user_can($taxonomy->cap->edit_terms)) return;

        $color = sanitize_text_field(wp_unslash($_POST['lsd_color']));
        update_term_meta($term_id, 'lsd_color', $color);
        $this->save_archive_shortcode($term_id);
    }

    public function get_terms()
    {
        return get_terms([
            'taxonomy' => LSD_Base::TAX_LABEL,
            'hide_empty' => false,
        ]);
    }
}
