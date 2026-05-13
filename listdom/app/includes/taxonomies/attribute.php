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
        add_action('lsd_' . LSD_Base::TAX_CATEGORY . '_term_slug_updated', [$this, 'slug_updated'], 10, 3);

        add_filter('manage_edit-' . LSD_Base::TAX_ATTRIBUTE . '_columns', [$this, 'filter_columns']);
        add_filter('manage_' . LSD_Base::TAX_ATTRIBUTE . '_custom_column', [$this, 'filter_columns_content'], 10, 3);
        add_filter('manage_edit-' . LSD_Base::TAX_ATTRIBUTE . '_sortable_columns', [$this, 'filter_sortable_columns']);
    }

    public function register()
    {
        $singular = lsd_t_label(LSD_Base::TAX_ATTRIBUTE);
        $plural = lsd_t_label(LSD_Base::TAX_ATTRIBUTE, 'plural');
        $plural_lc = lsd_t_label_lc(LSD_Base::TAX_ATTRIBUTE, 'plural');

        $args = [
            'label' => $plural,
            'labels' => [
                'name' => $plural,
                'singular_name' => $singular,
                'all_items' => sprintf(esc_html__('All %s', 'listdom'), $plural),
                'edit_item' => sprintf(esc_html__('Edit %s', 'listdom'), $singular),
                'view_item' => sprintf(esc_html__('View %s', 'listdom'), $plural),
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
        <div class="lsd-box-taxonomy-upsert lsd-mb-4">
            <div class="form-field">
                <label for="lsd_field_type"><?php esc_html_e('Field Type', 'listdom'); ?></label>
                <select name="lsd_field_type" id="lsd_field_type">
                    <option value="text"><?php esc_html_e('Text Input', 'listdom'); ?></option>
                    <option value="radio"><?php esc_html_e('Radio', 'listdom'); ?></option>
                    <option value="checkbox"><?php esc_html_e('Checkbox', 'listdom'); ?></option>
                    <option value="number"><?php esc_html_e('Number Input', 'listdom'); ?></option>
                    <option value="email"><?php esc_html_e('Email Input', 'listdom'); ?></option>
                    <option value="tel"><?php esc_html_e('Tel Input', 'listdom'); ?></option>
                    <option value="url"><?php esc_html_e('URL Input', 'listdom'); ?></option>
                    <option value="date"><?php esc_html_e('Date Input', 'listdom'); ?></option>
                    <option value="time"><?php esc_html_e('Time Input', 'listdom'); ?></option>
                    <option value="datetime"><?php esc_html_e('Date & Time Input', 'listdom'); ?></option>
                    <option value="image"><?php esc_html_e('Image', 'listdom'); ?></option>
                    <option value="file"><?php esc_html_e('File', 'listdom'); ?></option>
                    <option value="dropdown"><?php esc_html_e('Dropdown', 'listdom'); ?></option>
                    <option value="textarea"><?php esc_html_e('Textarea', 'listdom'); ?></option>
                    <option value="separator"><?php esc_html_e('Separator', 'listdom'); ?></option>
                </select>
            </div>
            <div class="form-field lsd-field-type-dependent lsd-field-type-dropdown lsd-field-type-radio lsd-field-type-checkbox">
                <label for="lsd_values"><?php esc_html_e('Values', 'listdom'); ?></label>
                <input type="text" name="lsd_values"
                       placeholder="<?php esc_attr_e('Active,Sold,Waiting', 'listdom'); ?>" id="lsd_values" value="">
                <p class="description"><?php esc_html_e('Comma Separated values for dropdown type.', 'listdom'); ?></p>
            </div>
            <div class="form-field lsd-field-type-dependent lsd-field-type-file">
                <label for="lsd_file_extensions"><?php esc_html_e('Supported Extensions', 'listdom'); ?></label>
                <input type="text" name="lsd_file_extensions"
                       placeholder="<?php esc_attr_e('pdf,doc,docx,zip', 'listdom'); ?>" id="lsd_file_extensions" value="">
                <p class="description"><?php esc_html_e('Comma separated extensions without dots. Leave empty to allow all extensions.', 'listdom'); ?></p>
            </div>
            <div class="form-field lsd-field-type-dependent lsd-field-type-file">
                <label for="lsd_file_max_size"><?php esc_html_e('Maximum File Size (KB)', 'listdom'); ?></label>
                <input type="number" min="0" name="lsd_file_max_size"
                       placeholder="<?php esc_attr_e('2048', 'listdom'); ?>" id="lsd_file_max_size" value="">
                <p class="description"><?php esc_html_e('Maximum allowed file size in kilobytes. Leave empty or zero for no limit.', 'listdom'); ?></p>
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
                            <p><?php echo sprintf(
                                /* translators: %s: Link to the categories settings screen. */
                                esc_html__('There is no category. You can define some categories %s', 'listdom'),
                                '<a href="' . admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_CATEGORY) . '&post_type=' . LSD_Base::PTYPE_LISTING . '">' . esc_html__('here', 'listdom') . '</a>'
                            ); ?></p>
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
            <div class="form-field lsd-field-type-dependent lsd-field-type-text lsd-field-type-number lsd-field-type-email lsd-field-type-tel lsd-field-type-url lsd-field-type-date lsd-field-type-time lsd-field-type-datetime lsd-field-type-dropdown lsd-field-type-textarea lsd-field-type-radio lsd-field-type-checkbox lsd-field-type-image lsd-field-type-file">
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
            <div class="form-field lsd-field-type-dependent lsd-field-type-url lsd-field-type-email lsd-field-type-tel">
                <label for="lsd_link_label"><?php esc_html_e('Link Label', 'listdom'); ?></label>
                <?php echo LSD_Form::text([
                    'name' => 'lsd_link_label',
                    'id' => 'lsd_link_label',
                    'value' => '',
                ]); ?>
                <p class="description"><?php esc_html_e('Text to display instead of the raw value.', 'listdom'); ?></p>
            </div>
            <div class="form-field">
                <label for="lsd_index"><?php esc_html_e('Index', 'listdom'); ?></label>
                <input type="text" name="lsd_index"
                       placeholder="<?php esc_attr_e('1.00 or 2 or 5.50 etc.', 'listdom'); ?>" id="lsd_index"
                       value="99.00">
                <p class="description"><?php esc_html_e('An arbitrary number to determine the field order relative to the others e.g. "1" to be at the top of other fields.', 'listdom'); ?></p>
            </div>
            <div class="form-field">
                <label for="lsd_disabled_icon"><?php esc_html_e('Disable Icon', 'listdom'); ?></label>
                <?php echo LSD_Form::switcher([
                    'name' => 'lsd_disabled_icon',
                    'id' => 'lsd_disabled_icon',
                    'value' => 0,
                    'toggle' => '#lsd_icon_field',
                ]); ?>
                <p class="description"><?php esc_html_e('Check to hide icon for this custom field.', 'listdom'); ?></p>
            </div>
            <div class="form-field" id="lsd_icon_field">
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
            <?php do_action('lsd_attributes_add_fields'); ?>
        </div>
        <?php
        wp_nonce_field('lsd_save_attribute_meta', 'lsd_attribute_meta_nonce');
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
        $current_categories = self::categories_to_checkbox_ids($current_categories);
        $icon = get_term_meta($term->term_id, 'lsd_icon', true);
        $itemprop = get_term_meta($term->term_id, 'lsd_itemprop', true);
        $disable_icon = get_term_meta($term->term_id, 'lsd_disabled_icon', true);
        if ($disable_icon === '') $disable_icon = 0;
        $required = get_term_meta($term->term_id, 'lsd_required', true);
        $editor = get_term_meta($term->term_id, 'lsd_editor', true);
        $link_label = get_term_meta($term->term_id, 'lsd_link_label', true);
        $file_extensions = get_term_meta($term->term_id, 'lsd_file_extensions', true);
        $file_max_size = get_term_meta($term->term_id, 'lsd_file_max_size', true);
        ?>
        <tr class="form-field form-field-taxonomy-row">
            <td></td>
            <td class="form-field-taxonomy-column">
                <div class="lsd-box-taxonomy-upsert widefat">
                    <div class="form-field">
                        <label for="lsd_field_type"><?php esc_html_e('Field Type', 'listdom'); ?></label>
                        <select name="lsd_field_type" id="lsd_field_type" class="width-95-percent">
                            <option value="text" <?php echo $field_type === 'text' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Text Input', 'listdom'); ?></option>
                            <option value="radio" <?php echo $field_type === 'radio' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Radio', 'listdom'); ?></option>
                            <option value="checkbox" <?php echo $field_type === 'checkbox' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Checkbox', 'listdom'); ?></option>
                            <option value="number" <?php echo $field_type === 'number' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Number Input', 'listdom'); ?></option>
                            <option value="email" <?php echo $field_type === 'email' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Email Input', 'listdom'); ?></option>
                            <option value="tel" <?php echo $field_type === 'tel' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Tel Input', 'listdom'); ?></option>
                            <option value="url" <?php echo $field_type === 'url' ? 'selected="selected"' : ''; ?>><?php esc_html_e('URL Input', 'listdom'); ?></option>
                            <option value="date" <?php echo $field_type === 'date' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Date Input', 'listdom'); ?></option>
                            <option value="time" <?php echo $field_type === 'time' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Time Input', 'listdom'); ?></option>
                            <option value="datetime" <?php echo $field_type === 'datetime' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Date & Time Input', 'listdom'); ?></option>
                            <option value="image" <?php echo $field_type === 'image' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Image', 'listdom'); ?></option>
                            <option value="file" <?php echo $field_type === 'file' ? 'selected="selected"' : ''; ?>><?php esc_html_e('File', 'listdom'); ?></option>
                            <option value="dropdown" <?php echo $field_type === 'dropdown' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Dropdown', 'listdom'); ?></option>
                            <option value="textarea" <?php echo $field_type === 'textarea' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Textarea', 'listdom'); ?></option>
                            <option value="separator" <?php echo $field_type === 'separator' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Separator', 'listdom'); ?></option>
                        </select>
                    </div>
                    <div class="form-field lsd-field-type-dependent lsd-field-type-dropdown lsd-field-type-radio lsd-field-type-checkbox">
                        <label for="lsd_values"><?php esc_html_e('Values', 'listdom'); ?></label>
                        <input type="text" name="lsd_values"
                               placeholder="<?php esc_attr_e('Active,Sold,Waiting', 'listdom'); ?>" id="lsd_values"
                               value="<?php echo esc_attr($values); ?>">
                        <p class="description"><?php esc_html_e('Comma Separated values for dropdown type.', 'listdom'); ?></p>
                    </div>
                    <div class="form-field lsd-field-type-dependent lsd-field-type-file">
                        <label for="lsd_file_extensions"><?php esc_html_e('Supported Extensions', 'listdom'); ?></label>
                        <input type="text" name="lsd_file_extensions"
                               placeholder="<?php esc_attr_e('pdf,doc,docx,zip', 'listdom'); ?>" id="lsd_file_extensions"
                               value="<?php echo esc_attr($file_extensions); ?>">
                        <p class="description"><?php esc_html_e('Comma separated extensions without dots. Leave empty to allow all extensions.', 'listdom'); ?></p>
                    </div>
                    <div class="form-field lsd-field-type-dependent lsd-field-type-file">
                        <label for="lsd_file_max_size"><?php esc_html_e('Maximum File Size (KB)', 'listdom'); ?></label>
                        <input type="number" min="0" name="lsd_file_max_size"
                               placeholder="<?php esc_attr_e('2048', 'listdom'); ?>" id="lsd_file_max_size"
                               value="<?php echo esc_attr($file_max_size); ?>">
                        <p class="description"><?php esc_html_e('Maximum allowed file size in kilobytes. Leave empty or zero for no limit.', 'listdom'); ?></p>
                    </div>
                    <div class="form-field lsd-attributes-category-specific">
                        <label class="lsd-attributes-related-categories-label"><?php esc_html_e('Related Categories', 'listdom'); ?></label>
                        <div>
                            <input type="hidden" name="lsd_all_categories" value="0">
                            <input type="checkbox" name="lsd_all_categories" id="lsd_all_categories"
                                   value="1" <?php echo $all_categories ? 'checked="checked"' : ''; ?>>
                            <label for="lsd_all_categories"><?php esc_html_e('All Categories', 'listdom'); ?></label>
                        </div>
                        <div id="lsd_categories_wp" class="<?php echo $all_categories ? 'lsd-util-hide' : 'lsd-util-show'; ?>">
                            <?php if (!count($categories)): ?>
                                <p><?php echo sprintf(
                                    /* translators: %s: Link to the categories settings screen. */
                                    esc_html__('There is no category. You can define some categories %s', 'listdom'),
                                    '<a href="' . admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_CATEGORY . '&post_type=' . LSD_Base::PTYPE_LISTING) . '">' . esc_html__('here', 'listdom') . '</a>'
                                ); ?></p>
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
                    </div>
                    <div class="form-field lsd-field-type-dependent lsd-field-type-text lsd-field-type-number lsd-field-type-email lsd-field-type-tel lsd-field-type-url lsd-field-type-date lsd-field-type-time lsd-field-type-datetime lsd-field-type-dropdown lsd-field-type-textarea lsd-field-type-radio lsd-field-type-checkbox lsd-field-type-image lsd-field-type-file">
                        <label for="lsd_required"><?php esc_html_e('Required', 'listdom'); ?></label>
                        <?php echo LSD_Form::switcher([
                            'name' => 'lsd_required',
                            'id' => 'lsd_required',
                            'value' => $required,
                        ]); ?>
                    </div>
                    <div class="form-field lsd-field-type-dependent lsd-field-type-textarea">
                        <label for="lsd_editor"><?php esc_html_e('Rich Editor', 'listdom'); ?></label>
                        <?php echo LSD_Form::switcher([
                            'name' => 'lsd_editor',
                            'id' => 'lsd_editor',
                            'value' => $editor,
                        ]); ?>
                        <p class="description"><?php esc_html_e('Rich or HTML editor cannot be required!', 'listdom'); ?></p>
                    </div>
                    <div class="form-field lsd-field-type-dependent lsd-field-type-url lsd-field-type-email lsd-field-type-tel">
                        <label for="lsd_link_label"><?php esc_html_e('Link Label', 'listdom'); ?></label>
                        <?php echo LSD_Form::text([
                            'name' => 'lsd_link_label',
                            'id' => 'lsd_link_label',
                            'value' => $link_label,
                        ]); ?>
                        <p class="description"><?php esc_html_e('Text to display instead of the raw value.', 'listdom'); ?></p>
                    </div>
                    <div class="form-field">
                        <label for="lsd_index"><?php esc_html_e('Index', 'listdom'); ?></label>
                        <input type="text" name="lsd_index"
                               placeholder="<?php esc_attr_e('1.00 or 2 or 5.50 etc.', 'listdom'); ?>" id="lsd_index"
                               value="<?php echo esc_attr($index); ?>">
                        <p class="description"><?php esc_html_e('An arbitrary number to determine the field order relative to the others e.g. "1" to be at the top of other fields.', 'listdom'); ?></p>
                    </div>
                    <div class="form-field">
                        <label for="lsd_disabled_icon"><?php esc_html_e('Disable Icon', 'listdom'); ?></label>
                        <?php echo LSD_Form::switcher([
                            'name' => 'lsd_disabled_icon',
                            'id' => 'lsd_disabled_icon',
                            'value' => $disable_icon,
                            'toggle' => '#lsd_icon_field',
                        ]); ?>
                        <p class="description"><?php esc_html_e('Check to hide icon for this custom field.', 'listdom'); ?></p>
                    </div>
                    <div class="form-field <?php echo $disable_icon ? 'lsd-util-hide' : ''; ?>" id="lsd_icon_field">
                        <label for="lsd_icon"><?php esc_html_e('Icon', 'listdom'); ?></label>
                        <?php echo LSD_Form::iconpicker([
                            'name' => 'lsd_icon',
                            'id' => 'lsd_icon',
                            'value' => $icon,
                        ]); ?>
                    </div>
                    <div class="form-field">
                        <label for="lsd_itemprop"><?php esc_html_e('Schema Property', 'listdom'); ?></label>
                        <?php echo LSD_Form::text([
                            'name' => 'lsd_itemprop',
                            'id' => 'lsd_itemprop',
                            'value' => $itemprop,
                            'placeholder' => 'additionalProperty',
                        ]); ?>
                        <p class="description"><?php esc_html_e("Schema Item Property (https://schema.org/)", 'listdom'); ?></p>
                    </div>

                    <?php do_action('lsd_attributes_edit_fields', $term); ?>
                </div>
            </td>
        </tr>
        <?php
        wp_nonce_field('lsd_save_attribute_meta', 'lsd_attribute_meta_nonce');
    }

    public function save_metadata($term_id): bool
    {
        // It's quick edit
        if (!isset($_POST['lsd_field_type'])) return false;

        $nonce = isset($_POST['lsd_attribute_meta_nonce']) ? sanitize_text_field(wp_unslash($_POST['lsd_attribute_meta_nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'lsd_save_attribute_meta')) return false;

        $taxonomy = get_taxonomy(LSD_Base::TAX_ATTRIBUTE);
        if (!$taxonomy || !current_user_can($taxonomy->cap->edit_terms)) return false;

        $field_type = sanitize_text_field(wp_unslash($_POST['lsd_field_type']));
        $values = isset($_POST['lsd_values']) ? preg_replace('/,\s/', ',', sanitize_text_field(wp_unslash($_POST['lsd_values']))) : '';
        $index = isset($_POST['lsd_index']) ? sanitize_text_field(wp_unslash($_POST['lsd_index'])) : '99.00';
        $all_categories = isset($_POST['lsd_all_categories']) ? sanitize_text_field(wp_unslash($_POST['lsd_all_categories'])) : '1';
        $categories = isset($_POST['lsd_categories']) ? wp_unslash($_POST['lsd_categories']) : [];

        // Sanitization
        array_walk_recursive($categories, 'sanitize_text_field');
        $categories = self::normalize_categories($categories);

        // Validations
        if ($field_type !== 'dropdown' && $field_type !== 'radio' && $field_type !== 'checkbox') $values = '';
        if (is_float($index)) $index = '99.00';
        if ($all_categories == 1) $categories = [];

        update_term_meta($term_id, 'lsd_field_type', $field_type);
        update_term_meta($term_id, 'lsd_values', $values);
        update_term_meta($term_id, 'lsd_index', $index);
        update_term_meta($term_id, 'lsd_all_categories', $all_categories);
        update_term_meta($term_id, 'lsd_categories', $categories);

        $icon = isset($_POST['lsd_icon']) ? sanitize_text_field(wp_unslash($_POST['lsd_icon'])) : '';
        $itemprop = isset($_POST['lsd_itemprop']) && trim($_POST['lsd_itemprop']) ? sanitize_text_field(wp_unslash($_POST['lsd_itemprop'])) : '';
        $disable_icon = isset($_POST['lsd_disabled_icon']) ? (int) sanitize_text_field(wp_unslash($_POST['lsd_disabled_icon'])) : 0;
        $required = isset($_POST['lsd_required']) ? (int) sanitize_text_field(wp_unslash($_POST['lsd_required'])) : 0;

        $editor = isset($_POST['lsd_editor']) ? (int) sanitize_text_field(wp_unslash($_POST['lsd_editor'])) : 0;
        if ($editor) $required = 0;

        $link_label = isset($_POST['lsd_link_label']) ? sanitize_text_field(wp_unslash($_POST['lsd_link_label'])) : '';
        if (!in_array($field_type, ['url', 'email', 'tel'])) $link_label = '';

        $file_extensions = isset($_POST['lsd_file_extensions']) ? sanitize_text_field(wp_unslash($_POST['lsd_file_extensions'])) : '';
        $file_max_size = isset($_POST['lsd_file_max_size']) ? (int) sanitize_text_field(wp_unslash($_POST['lsd_file_max_size'])) : 0;
        if ($file_max_size < 0) $file_max_size = 0;

        if ($field_type !== 'file')
        {
            $file_extensions = '';
            $file_max_size = 0;
        }
        else
        {
            $normalized_extensions = [];
            foreach (preg_split('/[\s,]+/', $file_extensions) as $extension)
            {
                $extension = strtolower(trim((string) $extension));
                $extension = ltrim($extension, '.');
                $extension = preg_replace('/[^a-z0-9]/', '', $extension);

                if ($extension === '') continue;
                if (in_array($extension, $normalized_extensions, true)) continue;

                $normalized_extensions[] = $extension;
            }

            $file_extensions = implode(',', $normalized_extensions);
        }

        update_term_meta($term_id, 'lsd_icon', $icon);
        update_term_meta($term_id, 'lsd_disabled_icon', $disable_icon);
        update_term_meta($term_id, 'lsd_itemprop', $itemprop);
        update_term_meta($term_id, 'lsd_required', $required);
        update_term_meta($term_id, 'lsd_editor', $editor);
        update_term_meta($term_id, 'lsd_link_label', $link_label);
        update_term_meta($term_id, 'lsd_file_extensions', $file_extensions);
        update_term_meta($term_id, 'lsd_file_max_size', $file_max_size);

        return true;
    }

    public function slug_updated($old_slug, $new_slug, $term): void
    {
        if (!is_object($term) || !isset($term->taxonomy) || $term->taxonomy !== LSD_Base::TAX_CATEGORY) return;
        self::sync_category_slug($old_slug, $new_slug, LSD_Base::TAX_ATTRIBUTE);
    }

    public static function categories_to_checkbox_ids($categories): array
    {
        $ids = [];
        $categories = self::normalize_categories($categories);

        foreach (array_keys($categories) as $slug)
        {
            $term = get_term_by('slug', $slug, LSD_Base::TAX_CATEGORY);
            if (!$term || is_wp_error($term)) continue;

            $ids[$term->term_id] = 1;
        }

        return $ids;
    }

    public static function normalize_categories($categories): array
    {
        if (!is_array($categories)) return [];

        $normalized = [];
        foreach ($categories as $key => $value)
        {
            if (is_string($key) && !is_numeric($key))
            {
                if (!$value) continue;

                $slug = sanitize_title($key);
                if ($slug !== '')
                {
                    $term = get_term_by('slug', $slug, LSD_Base::TAX_CATEGORY);
                    if ($term && !is_wp_error($term)) $normalized[$slug] = 1;
                }

                continue;
            }

            $slug = '';
            if (is_numeric($key))
            {
                if (is_string($value))
                {
                    $slug = sanitize_title($value);
                    if ($slug !== '')
                    {
                        $term = get_term_by('slug', $slug, LSD_Base::TAX_CATEGORY);
                        if (!$term || is_wp_error($term)) $slug = '';
                    }
                }

                if (
                    $slug === ''
                    && (
                        (is_numeric($value) && (int) $value === 1)
                        || $value === true
                        || $value === null
                        || $value === ''
                    )
                )
                {
                    $slug = LSD_Taxonomies::to_slug(LSD_Base::TAX_CATEGORY, $key);
                }

                if ($slug === '' && is_numeric($value) && (int) $value > 1)
                {
                    $slug = LSD_Taxonomies::to_slug(LSD_Base::TAX_CATEGORY, $value);
                }
            }

            if ($slug !== '') $normalized[$slug] = 1;
        }

        return $normalized;
    }

    public static function context(array $context = []): array
    {
        $raw_context = $context;

        $post_id = isset($context['post_id']) ? absint($context['post_id']) : 0;
        $category_id = isset($context['category_id']) ? absint($context['category_id']) : 0;
        $category_slug = isset($context['category_slug']) ? sanitize_title((string) $context['category_slug']) : '';
        $package_id = isset($context['package_id']) ? absint($context['package_id']) : 0;
        $resolve_post_category = !isset($context['resolve_post_category']) || $context['resolve_post_category'];
        $resolve_post_package = !isset($context['resolve_post_package']) || $context['resolve_post_package'];

        if ($post_id > 0 && $resolve_post_category && (!$category_id || $category_slug === ''))
        {
            $entity = new LSD_Entity_Listing($post_id);
            $category = $entity->get_data_category();

            if ($category && !is_wp_error($category))
            {
                if (!$category_id && isset($category->term_id)) $category_id = $category->term_id;
                if ($category_slug === '' && isset($category->slug)) $category_slug = sanitize_title($category->slug);
            }
        }

        if ($category_id > 0 && $category_slug === '')
        {
            $category = get_term($category_id, LSD_Base::TAX_CATEGORY);
            if ($category && !is_wp_error($category) && isset($category->slug))
            {
                $category_slug = sanitize_title($category->slug);
            }
        }

        if ($post_id > 0 && $resolve_post_package && !$package_id)
        {
            $package_id = (int) get_post_meta($post_id, 'lsd_package', true);
        }

        $context = [
            'post_id' => $post_id,
            'category_id' => $category_id,
            'category_slug' => $category_slug,
            'package_id' => $package_id,
        ];

        return apply_filters('lsd_attribute_context', $context, $raw_context);
    }

    public static function category_rules(int $term_id): array
    {
        $all_categories = get_term_meta($term_id, 'lsd_all_categories', true);
        if (trim((string) $all_categories) === '') $all_categories = 1;

        $categories = get_term_meta($term_id, 'lsd_categories', true);
        $categories = self::normalize_categories($categories);

        if ($all_categories) $categories = [];

        return [
            'all_categories' => (int) $all_categories,
            'categories' => $categories,
        ];
    }

    public static function package_context(int $term_id, array $context = []): bool
    {
        return (bool) apply_filters('lsd_attribute_applies', true, $term_id, self::prepared_context($context));
    }

    public static function applies(int $term_id, array $context = []): bool
    {
        $context = self::prepared_context($context);
        $rules = self::category_rules($term_id);

        if (
            !$rules['all_categories']
            && count($rules['categories'])
            && (
                $context['category_slug'] === ''
                || !isset($rules['categories'][$context['category_slug']])
            )
        )
        {
            return false;
        }

        return self::package_context($term_id, $context);
    }

    protected static function prepared_context(array $context = []): array
    {
        $keys = ['post_id', 'category_id', 'category_slug', 'package_id'];
        $is_prepared = !isset($context['resolve_post_category'])
            && !isset($context['resolve_post_package'])
            && !isset($context['submission']);

        foreach ($keys as $key)
        {
            if (!array_key_exists($key, $context))
            {
                $is_prepared = false;
                break;
            }
        }

        return $is_prepared ? $context : self::context($context);
    }

    public static function normalize_filter_attributes(array $attributes): array
    {
        $normalized = [];
        foreach ($attributes as $key => $value)
        {
            $normalized_key = self::normalize_filter_attribute_key((string) $key);
            if ($normalized_key === '') continue;

            if (isset($normalized[$normalized_key]) && is_array($normalized[$normalized_key]))
            {
                $incoming = is_array($value) ? $value : [$value];
                $normalized[$normalized_key] = array_values(array_unique(array_merge($normalized[$normalized_key], $incoming)));

                continue;
            }

            $normalized[$normalized_key] = $value;
        }

        return $normalized;
    }

    public static function normalize_filter_attribute_key(string $key): string
    {
        $separator = strrpos($key, '-');
        if ($separator === false) return '';

        $slug = substr($key, 0, $separator);
        $query = trim(substr($key, $separator + 1));
        if ($query === '') return '';

        $slug = sanitize_title($slug);
        if ($slug === '') return '';

        return $slug . '-' . $query;
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
}
