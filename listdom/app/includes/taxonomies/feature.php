<?php

class LSD_Taxonomies_Feature extends LSD_Taxonomies
{
    public function init()
    {
        add_action('init', [$this, 'register']);

        add_action(LSD_Base::TAX_FEATURE . '_add_form_fields', [$this, 'add_form']);
        add_action(LSD_Base::TAX_FEATURE . '_edit_form_fields', [$this, 'edit_form']);
        add_action('created_' . LSD_Base::TAX_FEATURE, [$this, 'save_metadata']);
        add_action('edited_' . LSD_Base::TAX_FEATURE, [$this, 'save_metadata']);

        add_filter('manage_edit-' . LSD_Base::TAX_FEATURE . '_columns', [$this, 'filter_columns']);
        add_filter('manage_' . LSD_Base::TAX_FEATURE . '_custom_column', [$this, 'filter_columns_content'], 10, 3);
        add_filter('manage_edit-' . LSD_Base::TAX_FEATURE . '_sortable_columns', [$this, 'filter_sortable_columns']);
    }

    public function register()
    {
        $singular = lsd_t_label(LSD_Base::TAX_FEATURE);
        $plural = lsd_t_label(LSD_Base::TAX_FEATURE, 'plural');
        $singular_lc = lsd_t_label_lc(LSD_Base::TAX_FEATURE);
        $plural_lc = lsd_t_label_lc(LSD_Base::TAX_FEATURE, 'plural');

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
            'hierarchical' => false,
            'has_archive' => true,
            'show_in_rest' => false,
            'publicly_queryable' => true,
            'rewrite' => ['slug' => LSD_Options::feature_slug()],
        ];

        register_taxonomy(
            LSD_Base::TAX_FEATURE,
            LSD_Base::PTYPE_LISTING,
            apply_filters('lsd_taxonomy_feature_args', $args)
        );

        register_taxonomy_for_object_type(LSD_Base::TAX_FEATURE, LSD_Base::PTYPE_LISTING);
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
            <p class="description"><?php esc_html_e("The icon will show on the website frontend next to the feature.", 'listdom'); ?></p>
        </div>
        <?php if (!$this->isPro()): echo LSD_Base::alert($this->missFeatureMessage(esc_html__('SEO Schema', 'listdom')), 'warning'); ?>
        <?php else: ?>
            <div class="form-field">
                <label for="lsd_itemprop"><?php esc_html_e('Schema Property', 'listdom'); ?></label>
                <?php echo LSD_Form::text([
                    'name' => 'lsd_itemprop',
                    'id' => 'lsd_itemprop',
                    'placeholder' => 'additionalProperty',
                ]); ?>
                <p class="description"><?php esc_html_e("Schema Item Property (https://schema.org/)", 'listdom'); ?></p>
            </div>
        <?php endif;
        wp_nonce_field('lsd_save_feature_meta', 'lsd_feature_meta_nonce');
    }

    public function edit_form($term)
    {
        $icon = get_term_meta($term->term_id, 'lsd_icon', true);
        $itemprop = get_term_meta($term->term_id, 'lsd_itemprop', true);
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
                <p class="description"><?php esc_html_e("The icon will show on the website frontend next to the feature.", 'listdom'); ?></p>
            </td>
        </tr>
        <?php if ($this->isPro()): ?>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_itemprop"><?php esc_html_e('Schema Property', 'listdom'); ?></label>
            </th>
            <td>
                <?php echo LSD_Form::text([
                    'name' => 'lsd_itemprop',
                    'id' => 'lsd_itemprop',
                    'value' => $itemprop,
                    'placeholder' => 'additionalProperty',
                ]); ?>
                <p class="description"><?php esc_html_e("Schema Item Property (https://schema.org/)", 'listdom'); ?></p>
            </td>
        </tr>
        <?php endif;
        wp_nonce_field('lsd_save_feature_meta', 'lsd_feature_meta_nonce');
    }

    public function save_metadata($term_id)
    {
        // It's quick edit
        if (!isset($_POST['lsd_icon'])) return;

        $nonce = isset($_POST['lsd_feature_meta_nonce']) ? sanitize_text_field(wp_unslash($_POST['lsd_feature_meta_nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'lsd_save_feature_meta')) return;

        $taxonomy = get_taxonomy(LSD_Base::TAX_FEATURE);
        if (!$taxonomy || !current_user_can($taxonomy->cap->edit_terms)) return;

        $icon = sanitize_text_field(wp_unslash($_POST['lsd_icon']));
        $itemprop = isset($_POST['lsd_itemprop']) && trim($_POST['lsd_itemprop']) ? sanitize_text_field(wp_unslash($_POST['lsd_itemprop'])) : '';

        update_term_meta($term_id, 'lsd_icon', $icon);
        update_term_meta($term_id, 'lsd_itemprop', $itemprop);
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
            case 'icon':

                $content = LSD_Taxonomies::icon($term_id, 'fa-lg');
                break;

            default:
                break;
        }

        return $content;
    }
}
