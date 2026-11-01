<?php

class LSD_Taxonomies_Category extends LSD_Taxonomies
{
    private $rest_primary_categories;
    private $deleted_primary_categories = [];

    public function init()
    {
        add_action('init', [$this, 'register']);
        add_action('add_meta_boxes', [$this, 'register_metaboxes'], 5, 2);

        add_action(LSD_Base::TAX_CATEGORY . '_add_form_fields', [$this, 'add_form']);
        add_action(LSD_Base::TAX_CATEGORY . '_edit_form_fields', [$this, 'edit_form']);
        add_action('created_' . LSD_Base::TAX_CATEGORY, [$this, 'save_metadata']);
        add_action('edited_' . LSD_Base::TAX_CATEGORY, [$this, 'save_metadata']);

        add_filter('rest_prepare_taxonomy', [$this, 'rest_visibility'], 10, 3);
        add_filter('rest_pre_insert_' . LSD_Base::PTYPE_LISTING, [$this, 'validate_rest_categories'], 10, 2);
        add_action('rest_after_insert_' . LSD_Base::PTYPE_LISTING, [$this, 'save_rest_primary_category'], 10, 2);
        add_action('lsd_' . LSD_Base::TAX_CATEGORY . '_term_slug_updated', [$this, 'primary_slug_updated'], 10, 3);
        add_action('pre_delete_term', [$this, 'capture_deleted_primary_categories'], 10, 2);
        add_action('delete_' . LSD_Base::TAX_CATEGORY, [$this, 'replace_deleted_primary_categories']);

        add_filter('manage_edit-' . LSD_Base::TAX_CATEGORY . '_columns', [$this, 'filter_columns']);
        add_filter('manage_' . LSD_Base::TAX_CATEGORY . '_custom_column', [$this, 'filter_columns_content'], 10, 3);
        add_filter('manage_edit-' . LSD_Base::TAX_CATEGORY . '_sortable_columns', [$this, 'filter_sortable_columns']);
    }

    public function register()
    {
        $singular = lsd_t_label(LSD_Base::TAX_CATEGORY);
        $plural = lsd_t_label(LSD_Base::TAX_CATEGORY, 'plural');
        $singular_lc = lsd_t_label_lc(LSD_Base::TAX_CATEGORY);
        $plural_lc = lsd_t_label_lc(LSD_Base::TAX_CATEGORY, 'plural');

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

    public function rest_visibility($response, $taxonomy, $request)
    {
        if ($taxonomy->name !== LSD_Base::TAX_CATEGORY || $request['context'] !== 'edit') return $response;

        // Gutenberg ignores meta_box_cb; keep Listdom's required primary selector as the only category UI.
        $data = $response->get_data();
        if (isset($data['visibility']['show_ui']))
        {
            $data['visibility']['show_ui'] = false;
            $response->set_data($data);
        }

        return $response;
    }

    private function rest_category_field(): string
    {
        $taxonomy = get_taxonomy(LSD_Base::TAX_CATEGORY);
        if (!$taxonomy || !$taxonomy->show_in_rest) return '';

        return $taxonomy->rest_base ?: $taxonomy->name;
    }

    public function validate_rest_categories($prepared_post, $request)
    {
        if (is_wp_error($prepared_post)) return $prepared_post;

        $field = $this->rest_category_field();
        if (!$field || !array_key_exists($field, $request->get_params())) return $prepared_post;

        $categories = $request->get_param($field);
        if (!is_array($categories) || !$categories)
            return new WP_Error('listdom_invalid_categories', esc_html__('Select at least one existing listing category.', 'listdom'), ['status' => 400]);

        $taxonomy = get_taxonomy(LSD_Base::TAX_CATEGORY);
        if (!current_user_can($taxonomy->cap->assign_terms))
            return new WP_Error('rest_cannot_assign_term', esc_html__('You are not allowed to assign listing categories.', 'listdom'), ['status' => rest_authorization_required_code()]);

        $id = (int) $request->get_param('id');
        if ($id && get_post_meta($id, 'lsd_lock_edit', true))
            return new WP_Error('listdom_listing_locked', esc_html__('This listing is locked for editing.', 'listdom'), ['status' => 403]);

        foreach ($categories as $category)
        {
            if (!(get_term((int) $category, LSD_Base::TAX_CATEGORY) instanceof WP_Term))
                return new WP_Error('listdom_invalid_categories', esc_html__('Every listing category must exist.', 'listdom'), ['status' => 400]);

            if (!current_user_can('assign_term', (int) $category))
                return new WP_Error('rest_cannot_assign_term', esc_html__('You are not allowed to assign listing categories.', 'listdom'), ['status' => rest_authorization_required_code()]);
        }

        $primary = $id ? LSD_Entity_Listing::get_primary_category($id) : null;
        if (!$this->rest_primary_categories) $this->rest_primary_categories = new SplObjectStorage();
        $this->rest_primary_categories[$request] = $primary instanceof WP_Term ? (int) $primary->term_id : 0;

        return $prepared_post;
    }

    public function save_rest_primary_category($post, $request): void
    {
        if (!$this->rest_primary_categories || !$this->rest_primary_categories->contains($request)) return;

        $primary_id = $this->rest_primary_categories[$request];
        $this->rest_primary_categories->detach($request);
        $field = $this->rest_category_field();
        if (!$field || !array_key_exists($field, $request->get_params())) return;

        $categories = array_values(array_map('intval', $request->get_param($field)));
        $assigned = wp_get_post_terms($post->ID, LSD_Base::TAX_CATEGORY, ['fields' => 'ids']);
        if (is_wp_error($assigned)) return;

        // Use actual assignments, while retaining request order when a replacement primary is needed.
        $categories = array_values(array_intersect($categories, $assigned));
        if (!$categories) return;
        if (!in_array($primary_id, $categories, true)) $primary_id = $categories[0];

        $primary = get_term($primary_id, LSD_Base::TAX_CATEGORY);
        if ($primary instanceof WP_Term) update_post_meta($post->ID, 'lsd_primary_category', $primary->slug);
    }

    public function primary_slug_updated($old_slug, $new_slug, $term): void
    {
        $listings = get_posts([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => array_values(get_post_stati()),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_key' => 'lsd_primary_category',
            'meta_value' => $old_slug,
        ]);

        foreach ($listings as $id) update_post_meta($id, 'lsd_primary_category', $new_slug);
    }

    public function capture_deleted_primary_categories($term_id, $taxonomy): void
    {
        if ($taxonomy !== LSD_Base::TAX_CATEGORY) return;

        $objects = get_objects_in_term($term_id, $taxonomy);
        if (is_wp_error($objects)) return;

        $this->deleted_primary_categories[$term_id] = [];
        foreach ($objects as $id)
        {
            if (get_post_type($id) !== LSD_Base::PTYPE_LISTING) continue;

            // Resolve before deletion so legacy numeric primary values keep their existing meaning.
            $primary = LSD_Entity_Listing::get_primary_category($id);
            if ($primary instanceof WP_Term && (int) $primary->term_id === (int) $term_id)
                $this->deleted_primary_categories[$term_id][] = (int) $id;
        }
    }

    public function replace_deleted_primary_categories($term_id): void
    {
        $listings = $this->deleted_primary_categories[$term_id] ?? [];
        unset($this->deleted_primary_categories[$term_id]);

        foreach ($listings as $id)
        {
            // Match the existing first-category read fallback without changing term relationships.
            $terms = wp_get_post_terms($id, LSD_Base::TAX_CATEGORY);
            if (is_wp_error($terms)) continue;

            if ($terms) update_post_meta($id, 'lsd_primary_category', $terms[0]->slug);
            else delete_post_meta($id, 'lsd_primary_category');
        }
    }

    public function register_metaboxes()
    {
        add_meta_box(
            'lsd_metabox_category',
            esc_html(lsd_t_label(LSD_Base::TAX_CATEGORY)) . ' ' . LSD_Base::REQ_HTML,
            [$this, 'metabox_category'],
            LSD_Base::PTYPE_LISTING,
            'side',
            'low'
        );
    }

    public function metabox_category($post)
    {
        // Primary Category
        $category = LSD_Entity_Listing::get_primary_category($post->ID);

        // All Categories
        $all_categories = LSD_Taxonomies_Category::get_terms();

        // Default Category Selection
        $uncategorized_id = LSD_Main::get_uncategorized_category_id();
        $selected = $category && isset($category->term_id)
            ? $category->term_id
            : (is_array($all_categories) && count($all_categories) === 1
                ? $all_categories[0]->term_id
                : ($uncategorized_id ?: null));

        // Security Nonce
        LSD_Form::nonce('lsd_listing_cpt', '_lsdnonce');

        echo '<p>' . sprintf(
            esc_html__('Select the primary %s.', 'listdom'),
            esc_html(lsd_t_label_lc(LSD_Base::TAX_CATEGORY))
        ) . '</p>';
        echo wp_dropdown_categories([
            'echo' => 0,
            'hide_empty' => 0,
            'name' => 'lsd[listing_category]',
            'id' => 'lsd_listing_category',
            'taxonomy' => LSD_Base::TAX_CATEGORY,
            'show_option_none' => '-----',
            'selected' => $selected,
            'class' => 'widefat lsd-admin-input',
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
        <div class="form-field" id="lsd_icon_field">
            <label for="lsd_icon"><?php esc_html_e('Icon', 'listdom'); ?></label>
            <?php echo LSD_Form::iconpicker([
                'name' => 'lsd_icon',
                'id' => 'lsd_icon',
                'value' => '',
            ]); ?>
            <p class="description"><?php esc_html_e("The icon is needed for listings' markers and some other sections.", 'listdom'); ?></p>
        </div>
        <div class="form-field">
            <label for="lsd_disabled_icon"><?php esc_html_e('Disable Icon', 'listdom'); ?></label>
            <?php echo LSD_Form::switcher([
                'name' => 'lsd_disabled_icon',
                'id' => 'lsd_disabled_icon',
                'value' => 0,
                'toggle' => '#lsd_icon_field'
            ]); ?>
            <p class="description"><?php esc_html_e('Check to hide icon for this category.', 'listdom'); ?></p>
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
        <?php $this->archive_shortcode_add_field(); ?>
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
        wp_nonce_field('lsd_save_category_meta', 'lsd_category_meta_nonce');
    }

    public function edit_form($term)
    {
        $icon = get_term_meta($term->term_id, 'lsd_icon', true);
        $color = get_term_meta($term->term_id, 'lsd_color', true);
        $image = get_term_meta($term->term_id, 'lsd_image', true);
        $disable = get_term_meta($term->term_id, 'lsd_disabled_icon', true);
        $schema = get_term_meta($term->term_id, 'lsd_schema', true);
        ?>
        <tr class="form-field <?php echo $disable ? 'lsd-util-hide' : ''; ?>" id="lsd_icon_field">
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
                <label for="lsd_disabled_icon"><?php esc_html_e('Disable Icon', 'listdom'); ?></label>
            </th>
            <td>
                <?php echo LSD_Form::switcher([
                    'name' => 'lsd_disabled_icon',
                    'id' => 'lsd_disabled_icon',
                    'value' => $disable,
                    'toggle' => '#lsd_icon_field'
                ]); ?>
                <p class="description"><?php esc_html_e('Check to hide icon for this category.', 'listdom'); ?></p>
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
        <?php $this->archive_shortcode_edit_field($term); ?>
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
        <?php endif;
        $this->seo_schema_edit_warning();
        wp_nonce_field('lsd_save_category_meta', 'lsd_category_meta_nonce');
    }

    public function save_metadata($term_id): bool
    {
        // It's quick edit
        if (!isset($_POST['lsd_icon'])) return false;

        $nonce = isset($_POST['lsd_category_meta_nonce']) ? sanitize_text_field(wp_unslash($_POST['lsd_category_meta_nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'lsd_save_category_meta')) return false;

        $taxonomy = get_taxonomy(LSD_Base::TAX_CATEGORY);
        if (!$taxonomy || !current_user_can($taxonomy->cap->edit_terms)) return false;

        $icon = sanitize_text_field(wp_unslash($_POST['lsd_icon']));
        $color = isset($_POST['lsd_color']) ? sanitize_text_field(wp_unslash($_POST['lsd_color'])) : '';
        $image = isset($_POST['lsd_image']) ? sanitize_text_field(wp_unslash($_POST['lsd_image'])) : '';
        $disable = isset($_POST['lsd_disabled_icon']) ? (int) sanitize_text_field(wp_unslash($_POST['lsd_disabled_icon'])) : 0;

        LSD_Listings_Taxonomies::save_metadata((int) $term_id, ['icon' => $icon]);
        LSD_Listings_Taxonomies::save_metadata((int) $term_id, ['color' => $color]);
        LSD_Listings_Taxonomies::save_metadata((int) $term_id, ['image' => $image]);
        LSD_Listings_Taxonomies::save_metadata((int) $term_id, ['disabled_icon' => $disable]);

        $this->save_archive_shortcode($term_id);
        if (isset($_POST['lsd_schema'])) LSD_Listings_Taxonomies::save_metadata((int) $term_id, ['schema' => $this->posted_scalar('lsd_schema')]);

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

                $disabled_icon = (int) get_term_meta($term_id, 'lsd_disabled_icon', true);
                $content = $disabled_icon ? '' : '<div class="lsd-preview-color" style="background-color: ' . get_term_meta($term_id, 'lsd_color', true) . '"><div class="lsd-icon-wrapper">' . LSD_Taxonomies::icon($term_id, 'fa-lg') . '</div></div>';
                break;

            default:
                break;
        }

        return $content;
    }
}
