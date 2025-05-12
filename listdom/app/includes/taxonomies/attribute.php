<?php

class LSD_Taxonomies_Attribute extends LSD_Taxonomies
{
    public function init()
    {
        add_action('init', [$this, 'register'], 0, 9);

        add_action(LSD_Base::TAX_ATTRIBUTE . '_add_form_fields', [$this, 'add_form']);
        add_action(LSD_Base::TAX_ATTRIBUTE . '_edit_form_fields', [$this, 'edit_form']);
        add_action('created_' . LSD_Base::TAX_ATTRIBUTE, [$this, 'save_metadata']);
        add_action('edited_' . LSD_Base::TAX_ATTRIBUTE, [$this, 'save_metadata']);

        add_filter('manage_edit-' . LSD_Base::TAX_ATTRIBUTE . '_columns', [$this, 'filter_columns']);
        add_filter('manage_' . LSD_Base::TAX_ATTRIBUTE . '_custom_column', [$this, 'filter_columns_content'], 10, 3);
        add_filter('manage_edit-' . LSD_Base::TAX_ATTRIBUTE . '_sortable_columns', [$this, 'filter_sortable_columns']);

        // Attributes Metabox
        add_action('lsd_register_metaboxes', [$this, 'register_metaboxes'], 10, 2);
    }

    public function register()
    {
        $args = [
            'label' => esc_html__('Attributes', 'listdom'),
            'labels' => [
                'name' => esc_html__('Attributes', 'listdom'),
                'singular_name' => esc_html__('Attribute', 'listdom'),
                'all_items' => esc_html__('All Attributes', 'listdom'),
                'edit_item' => esc_html__('Edit Attribute', 'listdom'),
                'view_item' => esc_html__('View Attribute', 'listdom'),
                'update_item' => esc_html__('Update Attribute', 'listdom'),
                'add_new_item' => esc_html__('Add New Attribute', 'listdom'),
                'new_item_name' => esc_html__('New Attribute Name', 'listdom'),
                'popular_items' => esc_html__('Popular Attributes', 'listdom'),
                'search_items' => esc_html__('Search Attributes', 'listdom'),
                'separate_items_with_commas' => esc_html__('Separate attributes with commas', 'listdom'),
                'add_or_remove_items' => esc_html__('Add or remove attributes', 'listdom'),
                'choose_from_most_used' => esc_html__('Choose from the most used attributes', 'listdom'),
                'not_found' => esc_html__('No attributes found.', 'listdom'),
                'back_to_items' => esc_html__('← Back to Attributes', 'listdom'),
                'parent_item' => esc_html__('Parent Attribute', 'listdom'),
                'parent_item_colon' => esc_html__('Parent Attribute:', 'listdom'),
                'no_terms' => esc_html__('No Attributes', 'listdom'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => false,
            'hierarchical' => false,
            'has_archive' => false,
            'publicly_queryable' => false,
            'show_tagcloud' => false,
            'show_in_quick_edit' => false,
            'meta_box_cb' => false,
        ];

        register_taxonomy(
            LSD_Base::TAX_ATTRIBUTE,
            LSD_Base::PTYPE_LISTING,
            apply_filters('lsd_taxonomy_attribute_args', $args)
        );

        register_taxonomy_for_object_type(LSD_Base::TAX_ATTRIBUTE, LSD_Base::PTYPE_LISTING);
    }

    public function add_form()
    {
        $taxonomy = new LSD_Taxonomies_Category();
        $categories = $taxonomy->get_terms();
        ?>
        <div class="form-field">
            <label for="lsd_field_type"><?php esc_html_e('Field Type', 'listdom'); ?></label>
            <select name="lsd_field_type" id="lsd_field_type" class="width-95-percent">
                <option value="text"><?php esc_html_e('Text Input', 'listdom'); ?></option>
                <option value="number"><?php esc_html_e('Number Input', 'listdom'); ?></option>
                <option value="email"><?php esc_html_e('Email Input', 'listdom'); ?></option>
                <option value="url"><?php esc_html_e('URL Input', 'listdom'); ?></option>
                <option value="dropdown"><?php esc_html_e('Dropdown', 'listdom'); ?></option>
                <option value="textarea"><?php esc_html_e('Textarea', 'listdom'); ?></option>
                <option value="separator"><?php esc_html_e('Separator', 'listdom'); ?></option>
            </select>
        </div>
        <div class="form-field lsd-field-type-dependent lsd-field-type-dropdown">
            <label for="lsd_values"><?php esc_html_e('Values', 'listdom'); ?></label>
            <input type="text" name="lsd_values"
                   placeholder="<?php esc_attr_e('Active,Sold,Waiting', 'listdom'); ?>" id="lsd_values" value="">
            <p class="description"><?php esc_html_e('Comma Separated values for dropdown type.', 'listdom'); ?></p>
        </div>
        <div class="form-field">
            <label><?php esc_html_e('Related Categories', 'listdom'); ?></label>
            <div class="lsd-attributes-category-specific">
                <div>
                    <input type="hidden" name="lsd_all_categories" value="0">
                    <input type="checkbox" name="lsd_all_categories" id="lsd_all_categories" value="1"
                           checked="checked"><label
                        for="lsd_all_categories"><?php esc_html_e('All Categories', 'listdom'); ?></label>
                </div>
                <div id="lsd_categories_wp" class="lsd-util-hide">
                    <?php if (!count($categories)): ?>
                        <p><?php echo sprintf(esc_html__('There is no category. You can define some categories %s', 'listdom'), '<a href="' . admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_CATEGORY) . '&post_type=' . LSD_Base::PTYPE_LISTING . '">' . esc_html__('here', 'listdom') . '</a>'); ?></p>
                    <?php else: ?>
                        <ul>
                            <?php echo LSD_Kses::form($this->tax_checkboxes([
                                'taxonomy' => LSD_Base::TAX_CATEGORY,
                                'current' => [],
                                'name' => 'lsd_categories',
                            ])); ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <p class="description"><?php esc_html_e('You can create category specific attributes.', 'listdom'); ?></p>
            </div>
        </div>
        <div
            class="form-field lsd-field-type-dependent lsd-field-type-text lsd-field-type-number lsd-field-type-email lsd-field-type-url lsd-field-type-dropdown lsd-field-type-textarea">
            <label for="lsd_required"><?php esc_html_e('Required', 'listdom'); ?></label>
            <?php echo LSD_Form::switcher([
                'name' => 'lsd_required',
                'id' => 'lsd_required',
                'value' => 0,
            ]); ?>
        </div>
        <div class="form-field lsd-field-type-dependent lsd-field-type-textarea">
            <label for="lsd_editor"><?php esc_html_e('Rich Editor', 'listdom'); ?></label>
            <?php echo LSD_Form::switcher([
                'name' => 'lsd_editor',
                'id' => 'lsd_editor',
                'value' => 0,
            ]); ?>
            <p class="description"><?php esc_html_e('Rich or HTML editor cannot be required!', 'listdom'); ?></p>
        </div>
        <div class="form-field">
            <label for="lsd_index"><?php esc_html_e('Index', 'listdom'); ?></label>
            <input type="text" name="lsd_index"
                   placeholder="<?php esc_attr_e('1.00 or 2 or 5.50 etc.', 'listdom'); ?>" id="lsd_index"
                   value="99.00">
            <p class="description"><?php esc_html_e('An arbitrary number to determine the field order relative to the others e.g. "1" to be at the top of other fields.', 'listdom'); ?></p>
        </div>
        <div class="form-field">
            <label for="lsd_icon"><?php esc_html_e('Icon', 'listdom'); ?></label>
            <?php echo LSD_Form::iconpicker([
                'name' => 'lsd_icon',
                'id' => 'lsd_icon',
                'value' => '',
            ]); ?>
        </div>
        <div class="form-field">
            <label for="lsd_itemprop"><?php esc_html_e('Schema Property', 'listdom'); ?></label>
            <?php echo LSD_Form::text([
                'name' => 'lsd_itemprop',
                'id' => 'lsd_itemprop',
                'placeholder' => 'additionalProperty',
            ]); ?>
            <p class="description"><?php esc_html_e("Schema Item Property (https://schema.org/)", 'listdom'); ?></p>
        </div>
        <?php
    }

    public function edit_form($term)
    {
        $taxonomy = new LSD_Taxonomies_Category();
        $categories = $taxonomy->get_terms();

        $field_type = get_term_meta($term->term_id, 'lsd_field_type', true);
        $values = get_term_meta($term->term_id, 'lsd_values', true);
        $index = get_term_meta($term->term_id, 'lsd_index', true);
        $all_categories = get_term_meta($term->term_id, 'lsd_all_categories', true);
        $current_categories = get_term_meta($term->term_id, 'lsd_categories', true);
        $icon = get_term_meta($term->term_id, 'lsd_icon', true);
        $itemprop = get_term_meta($term->term_id, 'lsd_itemprop', true);
        $required = get_term_meta($term->term_id, 'lsd_required', true);
        $editor = get_term_meta($term->term_id, 'lsd_editor', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_field_type"><?php esc_html_e('Field Type', 'listdom'); ?></label>
            </th>
            <td>
                <select name="lsd_field_type" id="lsd_field_type" class="width-95-percent">
                    <option
                        value="text" <?php echo $field_type === 'text' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Text Input', 'listdom'); ?></option>
                    <option
                        value="number" <?php echo $field_type === 'number' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Number Input', 'listdom'); ?></option>
                    <option
                        value="email" <?php echo $field_type === 'email' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Email Input', 'listdom'); ?></option>
                    <option
                        value="url" <?php echo $field_type === 'url' ? 'selected="selected"' : ''; ?>><?php esc_html_e('URL Input', 'listdom'); ?></option>
                    <option
                        value="dropdown" <?php echo $field_type === 'dropdown' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Dropdown', 'listdom'); ?></option>
                    <option
                        value="textarea" <?php echo $field_type === 'textarea' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Textarea', 'listdom'); ?></option>
                    <option
                        value="separator" <?php echo $field_type === 'separator' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Separator', 'listdom'); ?></option>
                </select>
            </td>
        </tr>
        <tr class="form-field lsd-field-type-dependent lsd-field-type-dropdown">
            <th scope="row">
                <label for="lsd_values"><?php esc_html_e('Values', 'listdom'); ?></label>
            </th>
            <td>
                <input type="text" name="lsd_values"
                       placeholder="<?php esc_attr_e('Active,Sold,Waiting', 'listdom'); ?>" id="lsd_values"
                       value="<?php echo esc_attr($values); ?>">
                <p class="description"><?php esc_html_e('Comma Separated values for dropdown type.', 'listdom'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label><?php esc_html_e('Related Categories', 'listdom'); ?></label>
            </th>
            <td class="lsd-attributes-category-specific">
                <div>
                    <input type="hidden" name="lsd_all_categories" value="0">
                    <input type="checkbox" name="lsd_all_categories" id="lsd_all_categories"
                           value="1" <?php echo $all_categories ? 'checked="checked"' : ''; ?>><label
                        for="lsd_all_categories"><?php esc_html_e('All Categories', 'listdom'); ?></label>
                </div>
                <div id="lsd_categories_wp" class="<?php echo $all_categories ? 'lsd-util-hide' : 'lsd-util-show'; ?>">
                    <?php if (!count($categories)): ?>
                        <p><?php echo sprintf(esc_html__('There is no category. You can define some categories %s', 'listdom'), '<a href="' . admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_CATEGORY . '&post_type=' . LSD_Base::PTYPE_LISTING) . '">' . esc_html__('here', 'listdom') . '</a>'); ?></p>
                    <?php else: ?>
                        <ul>
                            <?php echo LSD_Kses::form($this->tax_checkboxes([
                                'taxonomy' => LSD_Base::TAX_CATEGORY,
                                'current' => $current_categories,
                                'name' => 'lsd_categories',
                            ])); ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <p class="description"><?php esc_html_e('You can create category specific attributes.', 'listdom'); ?></p>
            </td>
        </tr>
        <tr class="form-field lsd-field-type-dependent lsd-field-type-text lsd-field-type-number lsd-field-type-email lsd-field-type-url lsd-field-type-dropdown lsd-field-type-textarea">
            <th scope="row">
                <label for="lsd_required"><?php esc_html_e('Required', 'listdom'); ?></label>
            </th>
            <td>
                <?php echo LSD_Form::switcher([
                    'name' => 'lsd_required',
                    'id' => 'lsd_required',
                    'value' => $required,
                ]); ?>
            </td>
        </tr>
        <tr class="form-field lsd-field-type-dependent lsd-field-type-textarea">
            <th scope="row">
                <label for="lsd_editor"><?php esc_html_e('Rich Editor', 'listdom'); ?></label>
            </th>
            <td>
                <?php echo LSD_Form::switcher([
                    'name' => 'lsd_editor',
                    'id' => 'lsd_editor',
                    'value' => $editor,
                ]); ?>
                <p class="description"><?php esc_html_e('Rich or HTML editor cannot be required!', 'listdom'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_index"><?php esc_html_e('Index', 'listdom'); ?></label>
            </th>
            <td>
                <input type="text" name="lsd_index"
                       placeholder="<?php esc_attr_e('1.00 or 2 or 5.50 etc.', 'listdom'); ?>" id="lsd_index"
                       value="<?php echo esc_attr($index); ?>">
                <p class="description"><?php esc_html_e('An arbitrary number to determine the field order relative to the others e.g. "1" to be at the top of other fields.', 'listdom'); ?></p>
            </td>
        </tr>
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
            </td>
        </tr>
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
        <?php
    }

    public function save_metadata($term_id): bool
    {
        // It's quick edit
        if (!isset($_POST['lsd_field_type'])) return false;

        $field_type = sanitize_text_field($_POST['lsd_field_type']);
        $values = isset($_POST['lsd_values']) ? preg_replace('/,\s/', ',', sanitize_text_field($_POST['lsd_values'])) : '';
        $index = isset($_POST['lsd_index']) ? sanitize_text_field($_POST['lsd_index']) : '99.00';
        $all_categories = isset($_POST['lsd_all_categories']) ? sanitize_text_field($_POST['lsd_all_categories']) : '1';
        $categories = $_POST['lsd_categories'] ?? [];

        // Sanitization
        array_walk_recursive($categories, 'sanitize_text_field');

        // Validations
        if ($field_type !== 'dropdown') $values = '';
        if (is_float($index)) $index = '99.00';
        if ($all_categories == 1) $categories = [];

        update_term_meta($term_id, 'lsd_field_type', $field_type);
        update_term_meta($term_id, 'lsd_values', $values);
        update_term_meta($term_id, 'lsd_index', $index);
        update_term_meta($term_id, 'lsd_all_categories', $all_categories);
        update_term_meta($term_id, 'lsd_categories', $categories);

        $icon = isset($_POST['lsd_icon']) ? sanitize_text_field($_POST['lsd_icon']) : '';
        $itemprop = isset($_POST['lsd_itemprop']) && trim($_POST['lsd_itemprop']) ? sanitize_text_field($_POST['lsd_itemprop']) : '';
        $required = isset($_POST['lsd_required']) ? (int) sanitize_text_field($_POST['lsd_required']) : 0;

        $editor = isset($_POST['lsd_editor']) ? (int) sanitize_text_field($_POST['lsd_editor']) : 0;
        if ($editor) $required = 0;

        update_term_meta($term_id, 'lsd_icon', $icon);
        update_term_meta($term_id, 'lsd_itemprop', $itemprop);
        update_term_meta($term_id, 'lsd_required', $required);
        update_term_meta($term_id, 'lsd_editor', $editor);

        return true;
    }

    public function filter_columns($columns)
    {
        $name = $columns['name'] ?? null;

        unset($columns['name']);
        unset($columns['slug']);
        unset($columns['description']);
        unset($columns['posts']);

        $columns['icon'] = esc_html__('Icon', 'listdom');
        $columns['name'] = $name;
        $columns['type'] = esc_html__('Type', 'listdom');
        $columns['values'] = esc_html__('Values', 'listdom');
        $columns['index'] = esc_html__('Index', 'listdom');

        return $columns;
    }

    public function filter_columns_content($content, $column_name, $term_id)
    {
        switch ($column_name)
        {
            case 'icon':

                $content = LSD_Taxonomies::icon($term_id, 'fa-lg');
                break;

            case 'type':

                $content = get_term_meta($term_id, 'lsd_field_type', true);
                break;

            case 'values':

                $content = get_term_meta($term_id, 'lsd_values', true);
                break;

            case 'index':

                $content = get_term_meta($term_id, 'lsd_index', true);
                break;

            default:
                break;
        }

        return $content;
    }

    public function get_terms()
    {
        return LSD_Main::get_attributes();
    }

    public function register_metaboxes()
    {
        add_meta_box('lsd_metabox_attributes', esc_html__('Attributes', 'listdom'), [$this, 'metabox_attributes'], LSD_Base::PTYPE_LISTING, 'normal', 'high');
    }

    public function metabox_attributes($post)
    {
        // Generate output
        include $this->include_html_file('metaboxes/listing/attributes.php', ['return_path' => true]);
    }
}
