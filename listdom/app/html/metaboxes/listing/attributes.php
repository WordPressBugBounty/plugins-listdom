<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

// Attributes
$attributes = LSD_Main::get_attributes();

$raw = get_post_meta($post->ID, 'lsd_attributes', true);
if (!is_array($raw)) $raw = [];

// Dashboard context and restrictions
$dashboard = LSD_Payload::get('dashboard');
$max_upload_size_limit = 0;
$image_aspect_ratio = '';
if ($dashboard instanceof LSD_Shortcodes_Dashboard)
{
    $max_upload_size_setting = $dashboard->settings['submission_max_image_upload_size'] ?? '';
    if (is_numeric($max_upload_size_setting)) $max_upload_size_limit = (int) $max_upload_size_setting;
    $image_aspect_ratio = $dashboard->settings['submission_image_aspect_ratio'] ?? '';
    $image_aspect_ratio = is_string($image_aspect_ratio) ? trim($image_aspect_ratio) : '';
}

$attribute_context_args = [
    'post_id' => (int) $post->ID,
    'resolve_post_category' => false,
];

// Frontend dashboard create flow has no stored package yet, so package visibility
// should follow the selected subscription from the request.
if ($dashboard instanceof LSD_Shortcodes_Dashboard && (int) $post->ID <= 0)
{
    $attribute_context_args['submission'] = true;
}

$attribute_context = LSD_Taxonomies_Attribute::context($attribute_context_args);
?>
<div class="lsd-metabox lsd-metabox-attributes lsd-listing-module-attributes <?php echo LSD_Base::get_lsd_class('box-white'); ?>">
    <?php if (!count($attributes)): ?>
        <p class="<?php echo LSD_Base::get_lsd_class('description'); ?>"><?php esc_html_e("No attribute are available.", 'listdom'); ?></p>
    <?php else: ?>
        <?php foreach ($attributes as $attribute): ?>
            <?php
            if (!LSD_Taxonomies_Attribute::package_context((int) $attribute->term_id, $attribute_context)) continue;

            $type = get_term_meta($attribute->term_id, 'lsd_field_type', true);
            $rules = LSD_Taxonomies_Attribute::category_rules((int) $attribute->term_id);
            $all_categories = $rules['all_categories'];
            $categories = $rules['categories'];

            // Generate category specific class
            $categories_class = $all_categories ? 'lsd-category-specific-all' : '';
            foreach (array_keys($categories) as $category_slug)
            {
                $category = get_term_by('slug', $category_slug, LSD_Base::TAX_CATEGORY);
                if (!$category || is_wp_error($category)) continue;

                $categories_class .= ' lsd-category-specific-' . esc_attr($category->term_id);
            }

            $options = [];
            $options_str = get_term_meta($attribute->term_id, 'lsd_values', true);
            foreach (explode(',', trim($options_str, ', ')) as $option) $options[$option] = $option;

            $required = get_term_meta($attribute->term_id, 'lsd_required', true);
            if (trim($required) === '') $required = 0;

            $editor = get_term_meta($attribute->term_id, 'lsd_editor', true);
            if (trim($editor) === '') $editor = 0;

            $file_extensions = LSD_Entity_Listing::normalize_file_extensions(get_term_meta($attribute->term_id, 'lsd_file_extensions', true));
            $file_max_size_raw = get_term_meta($attribute->term_id, 'lsd_file_max_size', true);
            $file_max_size = is_numeric($file_max_size_raw) ? (int) $file_max_size_raw : 0;
            if ($file_max_size < 0) $file_max_size = 0;

            $meta_value = get_post_meta($post->ID, 'lsd_attribute_' . $attribute->slug, true);
            ?>
            <div class="lsd-form-row lsd-category-specific lsd-attribute-type-<?php echo esc_attr($type); ?>  <?php echo esc_attr(trim($categories_class)); ?>" id="lsd_attribute_<?php echo esc_attr($attribute->term_id); ?>">
                <div class="lsd-col-2 lsd-label-col">
                    <?php if ($type !== 'separator'): ?>
                        <?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'for' => 'lsd_listing_attributes' . $attribute->term_id . (in_array($type, ['radio', 'checkbox']) ? 1 : ''),
                            'title' => $attribute->name,
                            'required' => $required
                        ]); ?>
                    <?php endif; ?>
                </div>
                <div class="lsd-col-8">
                    <?php
                    $data_required = $required ? 1 : 0;
                    if ($type === 'dropdown') echo LSD_Form::select([
                        'id' => 'lsd_listing_attributes' . $attribute->term_id,
                        'options' => $options,
                        'name' => 'lsd[attributes][' . $attribute->slug . ']',
                        'required' => $required,
                        'class' => 'lsd-admin-input',
                        'value' => $meta_value,
                        'attributes' => [
                            'data-required' => $data_required,
                        ],
                    ]);
                    else if ($type === 'radio' && count($options))
                    {
                        $saved_value = trim((string) $meta_value);

                        echo '<div class="lsd-attribute-radio" data-required-message="' . esc_attr__('Please select at least one option.', 'listdom') . '" data-required="' . esc_attr($required) . '">';

                        $r = 0;
                        foreach ($options as $opt => $option)
                        {
                            $r++;

                            $is_checked = $saved_value === trim((string) $opt);
                            $attributes = [];
                            if ($saved_value === trim((string) $opt)) $attributes['checked'] = true;

                            echo '<div>';
                            echo LSD_Form::input([
                                'id' => 'lsd_listing_attributes' . $attribute->term_id . $r,
                                'name' => 'lsd[attributes][' . $attribute->slug . ']',
                                'value' => $opt,
                                'required' => $required,
                                'attributes' => $attributes,
                            ], 'radio');
                            echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'for' => 'lsd_listing_attributes' . $attribute->term_id . $r,
                                'title' => $option,
                            ]);
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    else if ($type === 'checkbox' && count($options))
                    {
                        $saved_values = $meta_value ?? [];
                        if (!is_array($saved_values)) $saved_values = array_map('trim', explode(',', $saved_values));

                        echo '<div class="lsd-attribute-checkbox" data-required-message="' . esc_attr__('Please select at least one option.', 'listdom') . '" data-required="' . esc_attr($required) . '">';

                        $c = 0;
                        foreach ($options as $opt => $option)
                        {
                            $c++;

                            $attributes = [];
                            if (in_array(trim($opt), $saved_values)) $attributes['checked'] = true;

                            echo '<div>';
                            echo LSD_Form::checkbox([
                                'id' => 'lsd_listing_attributes' . $attribute->term_id . $c,
                                'name' => 'lsd[attributes][' . $attribute->slug . '][]',
                                'value' => $opt,
                                'attributes' => $attributes,
                            ]);
                            echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'for' => 'lsd_listing_attributes' . $attribute->term_id . $c,
                                'title' => $option,
                            ]);
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    else if ($type === 'textarea' && !$editor) echo LSD_Form::textarea([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_listing_attributes' . $attribute->term_id,
                        'name' => 'lsd[attributes][' . $attribute->slug . ']',
                        'required' => $required,
                        'rows' => 8,
                        'value' => $meta_value,
                        'attributes' => [
                            'data-required' => $data_required,
                        ],
                    ]);
                    else if ($type === 'textarea' && $editor) echo LSD_Form::editor([
                        'id' => 'lsd_listing_attributes' . $attribute->term_id,
                        'name' => 'lsd[attributes][' . $attribute->slug . ']',
                        'value' => $raw[$attribute->slug] ?? '',
                    ]);
                    else if ($type === 'image')
                    {
                        $wrapper_attributes = [
                            'class' => 'lsd-attribute-image',
                            'data-required-message' => esc_attr__('Please select an image.', 'listdom'),
                            'data-required' => (string) (int) $required,
                        ];

                        if ($max_upload_size_limit > 0)
                        {
                            $field_label = wp_strip_all_tags($attribute->name);
                            $size_message = sprintf(
                                /* translators: 1: Custom field label, 2: Maximum allowed image size in kilobytes. */
                                esc_html__('The selected image for "%1$s" exceeds the maximum allowed size of %2$s KB.', 'listdom'),
                                $field_label,
                                $max_upload_size_limit
                            );

                            $wrapper_attributes['data-max-upload-size'] = (string) $max_upload_size_limit;
                            $wrapper_attributes['data-size-message'] = $size_message;
                        }

                        if ($image_aspect_ratio !== '')
                        {
                            $field_label = wp_strip_all_tags($attribute->name);
                            $aspect_message = sprintf(
                                /* translators: 1: Custom field label, 2: Required aspect ratio for images. */
                                esc_html__('The image selected for "%1$s" should have an aspect ratio close to %2$s.', 'listdom'),
                                $field_label,
                                $image_aspect_ratio
                            );

                            $wrapper_attributes['data-aspect-ratio'] = $image_aspect_ratio;
                            $wrapper_attributes['data-aspect-message'] = $aspect_message;
                        }

                        $wrapper_attributes_str = '';
                        foreach ($wrapper_attributes as $attr_key => $attr_value)
                        {
                            if ($attr_value === '') continue;

                            if ($attr_key === 'class')
                            {
                                $wrapper_attributes_str .= ' class="' . esc_attr($attr_value) . '"';
                                continue;
                            }

                            $wrapper_attributes_str .= ' ' . esc_attr($attr_key) . '="' . esc_attr($attr_value) . '"';
                        }

                        echo '<div' . $wrapper_attributes_str . '>';
                        echo LSD_Form::imagepicker([
                            'id' => 'lsd_listing_attributes' . $attribute->term_id,
                            'name' => 'lsd[attributes][' . $attribute->slug . ']',
                            'value' => $meta_value,
                            'required' => $required,
                        ]);
                        echo '<div class="lsd-attribute-image-message"></div>';
                        echo '</div>';
                    }
                    else if ($type === 'file')
                    {
                        $wrapper_attributes = [
                            'class' => 'lsd-attribute-file',
                            'data-required-message' => esc_attr__('Please select a file.', 'listdom'),
                            'data-required' => (string) (int) $required,
                        ];

                        if (count($file_extensions))
                        {
                            $field_label = wp_strip_all_tags($attribute->name);
                            $wrapper_attributes['data-allowed-extensions'] = implode(',', $file_extensions);
                            $wrapper_attributes['data-extension-message'] = sprintf(
                                /* translators: 1: Custom field label, 2: Allowed extensions. */
                                esc_html__('The selected file for "%1$s" has an invalid extension. Allowed extensions are: %2$s.', 'listdom'),
                                $field_label,
                                implode(', ', $file_extensions)
                            );
                        }

                        if ($file_max_size > 0)
                        {
                            $field_label = wp_strip_all_tags($attribute->name);
                            $wrapper_attributes['data-max-upload-size'] = (string) $file_max_size;
                            $wrapper_attributes['data-size-message'] = sprintf(
                                /* translators: 1: Custom field label, 2: Maximum allowed file size in kilobytes. */
                                esc_html__('The selected file for "%1$s" exceeds the maximum allowed size of %2$s KB.', 'listdom'),
                                $field_label,
                                $file_max_size
                            );
                        }

                        $wrapper_attributes_str = '';
                        foreach ($wrapper_attributes as $attr_key => $attr_value)
                        {
                            if ($attr_value === '') continue;

                            if ($attr_key === 'class')
                            {
                                $wrapper_attributes_str .= ' class="' . esc_attr($attr_value) . '"';
                                continue;
                            }

                            $wrapper_attributes_str .= ' ' . esc_attr($attr_key) . '="' . esc_attr($attr_value) . '"';
                        }

                        echo '<div' . $wrapper_attributes_str . '>';
                        echo LSD_Form::filepicker([
                            'id' => 'lsd_listing_attributes' . $attribute->term_id,
                            'name' => 'lsd[attributes][' . $attribute->slug . ']',
                            'value' => $meta_value,
                            'required' => $required,
                        ]);
                        echo '<div class="lsd-attribute-file-message"></div>';
                        echo '</div>';
                    }
                    else if ($type === 'separator')
                    {
                        echo '<h3>' . esc_html($attribute->name) . '</h3>';
                    }
                    else
                    {
                        $value = is_string($meta_value) ? $meta_value : '';
                        if ($type === 'datetime') $value = str_replace(' ', 'T', trim($value));

                        echo LSD_Form::input([
                            'id' => 'lsd_listing_attributes' . $attribute->term_id,
                            'name' => 'lsd[attributes][' . $attribute->slug . ']',
                            'required' => $required,
                            'class' => 'lsd-admin-input',
                            'value' => $value,
                            'attributes' => [
                                'data-required' => $data_required,
                            ],
                        ], $type === 'datetime' ? 'datetime-local' : $type);
                    }
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
