<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

// Attributes
$attributes = LSD_Main::get_attributes();

$raw = get_post_meta($post->ID, 'lsd_attributes', true);
if (!is_array($raw)) $raw = [];
?>
<div class="lsd-metabox lsd-metabox-attributes lsd-listing-module-attributes">
    <?php if (!count($attributes)): ?>
        <p class="description"><?php esc_html_e("No attribute are available.", 'listdom'); ?></p>
    <?php else: ?>
        <?php foreach ($attributes as $attribute): ?>
            <?php
            $type = get_term_meta($attribute->term_id, 'lsd_field_type', true);

            // Get all category status
            $all_categories = get_term_meta($attribute->term_id, 'lsd_all_categories', true);
            if (trim($all_categories) == '') $all_categories = 1;

            // Get specific categories
            $categories = get_term_meta($attribute->term_id, 'lsd_categories', true);
            if ($all_categories) $categories = [];

            // Generate category specific class
            $categories_class = $all_categories ? 'lsd-category-specific-all' : '';
            foreach ($categories as $category => $status) $categories_class .= ' lsd-category-specific-' . esc_attr($category);

            $options = [];
            $options_str = get_term_meta($attribute->term_id, 'lsd_values', true);
            foreach (explode(',', trim($options_str, ', ')) as $option) $options[$option] = $option;

            $required = get_term_meta($attribute->term_id, 'lsd_required', true);
            if (trim($required) === '') $required = 0;

            $editor = get_term_meta($attribute->term_id, 'lsd_editor', true);
            if (trim($editor) === '') $editor = 0;
            ?>
            <div class="lsd-form-row lsd-category-specific lsd-attribute-type-<?php echo esc_attr($type); ?>  <?php echo esc_attr(trim($categories_class)); ?>" id="lsd_attribute_<?php echo esc_attr($attribute->term_id); ?>">
                <div class="lsd-col-2 lsd-label-col">
                    <?php if ($type !== 'separator'): ?>
                        <?php echo LSD_Form::label([
                            'for' => 'lsd_listing_attributes' . $attribute->term_id,
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
                        'value' => get_post_meta($post->ID, 'lsd_attribute_' . $attribute->slug, true),
                        'attributes' => [
                            'data-required' => $data_required,
                        ],
                    ]);
                    else if ($type === 'radio' && count($options))
                    {
                        $saved_value = trim((string) get_post_meta($post->ID, 'lsd_attribute_' . $attribute->slug, true));

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
                                'id' => 'lsd_listing_attributes_' . $attribute->term_id . $r,
                                'name' => 'lsd[attributes][' . $attribute->slug . ']',
                                'value' => $opt,
                                'required' => $required,
                                'attributes' => $attributes,
                            ], 'radio');
                            echo LSD_Form::label([
                                'for' => 'lsd_listing_attributes_' . $attribute->term_id . $r,
                                'title' => $option,
                            ]);
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    else if ($type === 'checkbox' && count($options))
                    {
                        $saved_values = get_post_meta($post->ID, 'lsd_attribute_' . $attribute->slug, true) ?? [];
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
                                'id' => 'lsd_listing_attributes_' . $attribute->term_id . $c,
                                'name' => 'lsd[attributes][' . $attribute->slug . '][]',
                                'value' => $opt,
                                'attributes' => $attributes,
                            ]);
                            echo LSD_Form::label([
                                'for' => 'lsd_listing_attributes_' . $attribute->term_id . $c,
                                'title' => $option,
                            ]);
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    else if ($type === 'textarea' && !$editor) echo LSD_Form::textarea([
                        'id' => 'lsd_listing_attributes' . $attribute->term_id,
                        'name' => 'lsd[attributes][' . $attribute->slug . ']',
                        'required' => $required,
                        'rows' => 8,
                        'value' => get_post_meta($post->ID, 'lsd_attribute_' . $attribute->slug, true),
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
                        echo '<div class="lsd-attribute-image" data-required-message="' . esc_attr__('Please select an image.', 'listdom') . '" data-required="' . esc_attr($required) . '">';
                        echo LSD_Form::imagepicker([
                            'id' => 'lsd_listing_attributes' . $attribute->term_id,
                            'name' => 'lsd[attributes][' . $attribute->slug . ']',
                            'value' => get_post_meta($post->ID, 'lsd_attribute_' . $attribute->slug, true),
                            'required' => $required,
                        ]);
                        echo '</div>';
                    }
                    else if ($type === 'separator')
                    {
                        echo '<h3>' . esc_html($attribute->name) . '</h3>';
                    }
                    else echo LSD_Form::input([
                        'id' => 'lsd_listing_attributes' . $attribute->term_id,
                        'name' => 'lsd[attributes][' . $attribute->slug . ']',
                        'required' => $required,
                        'value' => get_post_meta($post->ID, 'lsd_attribute_' . $attribute->slug, true),
                        'attributes' => [
                            'data-required' => $data_required,
                        ],
                    ], $type);
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

