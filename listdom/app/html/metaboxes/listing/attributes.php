<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

// Attributes
$attributes = LSD_Main::get_attributes();

$raw = get_post_meta($post->ID, 'lsd_attributes', true);
if(!is_array($raw)) $raw = [];
?>
<div class="lsd-metabox lsd-metabox-attributes lsd-listing-module-attributes">
    <?php if(!count($attributes)): ?>
        <p class="description"><?php esc_html_e("No attribute are available.", 'listdom'); ?></p>
    <?php else: ?>
        <?php foreach($attributes as $attribute): ?>
        <?php
            $type = get_term_meta($attribute->term_id, 'lsd_field_type', true);

            // Get all category status
            $all_categories = get_term_meta($attribute->term_id, 'lsd_all_categories', true);
            if(trim($all_categories) == '') $all_categories = 1;

            // Get specific categories
            $categories = get_term_meta($attribute->term_id, 'lsd_categories', true);
            if($all_categories) $categories = [];

            // Generate category specific class
            $categories_class = $all_categories ? 'lsd-category-specific-all' : '';
            foreach($categories as $category=>$status) $categories_class .= ' lsd-category-specific-'.esc_attr($category);

            $options = [];
            $options_str = get_term_meta($attribute->term_id, 'lsd_values', true);
            foreach(explode(',', trim($options_str, ', ')) as $option) $options[$option] = $option;

            $required = get_term_meta($attribute->term_id, 'lsd_required', true);
            if(trim($required) == '') $required = 0;

            $editor = get_term_meta($attribute->term_id, 'lsd_editor', true);
            if(trim($editor) == '') $editor = 0;
        ?>
        <div class="lsd-form-row lsd-category-specific <?php echo esc_attr(trim($categories_class)); ?>" id="lsd_attribute_<?php echo esc_attr($attribute->term_id); ?>">
            <?php if($type != 'separator'): ?>
            <div class="lsd-col-3 lsd-text-right">
                <?php echo LSD_Form::label([
                    'for'=>'lsd_listing_attributes'.$attribute->term_id,
                    'title'=>$attribute->name
                ]); ?>
                <?php if($required) echo ' '.LSD_Base::REQ_HTML ?>
            </div>
            <?php endif; ?>
            <div class="lsd-col-<?php echo ($type != 'separator' ? '9' : '12'); ?>">
                <?php $data_required = $required ? 1 : 0;
                    if($type == 'dropdown') echo LSD_Form::select([
                        'id'=>'lsd_listing_attributes'.$attribute->term_id,
                        'options'=>$options,
                        'name'=>'lsd[attributes]['.$attribute->term_id.']',
                        'required'=>$required,
                        'value'=>get_post_meta($post->ID, 'lsd_attribute_'.$attribute->term_id, true),
                        'attributes' => [
                            'data-required' => $data_required
                        ]
                    ]);
                    elseif($type == 'textarea' and !$editor) echo LSD_Form::textarea([
                        'id'=>'lsd_listing_attributes'.$attribute->term_id,
                        'name'=>'lsd[attributes]['.$attribute->term_id.']',
                        'required'=>$required,
                        'rows'=>8,
                        'value'=>get_post_meta($post->ID, 'lsd_attribute_'.$attribute->term_id, true),
                        'attributes' => [
                            'data-required' => $data_required
                        ]
                    ]);
                    elseif($type == 'textarea' and $editor) echo LSD_Form::editor([
                        'id'=>'lsd_listing_attributes'.$attribute->term_id,
                        'name'=>'lsd[attributes]['.$attribute->term_id.']',
                        'value'=>$raw[$attribute->term_id] ?? ''
                    ]);
                    elseif($type == 'separator') echo LSD_Form::separator([
                        'id'=>'lsd_listing_attributes'.$attribute->term_id,
                        'label'=>$attribute->name
                    ]);
                    else echo LSD_Form::input([
                        'id'=>'lsd_listing_attributes'.$attribute->term_id,
                        'name'=>'lsd[attributes]['.$attribute->term_id.']',
                        'required'=>$required,
                        'value'=>get_post_meta($post->ID, 'lsd_attribute_'.$attribute->term_id, true),
                        'attributes' => [
                            'data-required' => $data_required
                        ]
                    ], $type);
                ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
