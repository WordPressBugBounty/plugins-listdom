<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$form = get_post_meta($post->ID, 'lsd_form', true);
if (!is_array($form)) $form = [];
?>
<div class="lsd-metabox lsd-search-form-metabox">
    <div class="lsd-row">
        <div class="lsd-col-12">
            <?php echo LSD_Form::label([
                'title' => esc_html__('Style', 'listdom'),
                'for' => 'lsd_search_form_style',
            ]); ?>
            <?php echo LSD_Form::select([
                'id' => 'lsd_search_form_style',
                'name' => 'lsd[form][style]',
                'value' => isset($form['style']) && $form['style'] ? $form['style'] : 'default',
                'options' => [
                    'default' => esc_html__('Default', 'listdom'),
                    'sidebar' => esc_html__('Sidebar', 'listdom')
                ],
                'class' => 'widefat',
            ]); ?>
        </div>
    </div>
    <div class="lsd-row">
        <div class="lsd-col-12 lsd-search-form-criteria-row">
            <?php echo LSD_Form::label([
                'title' => esc_html__('Display Criteria', 'listdom'),
                'for' => 'lsd_search_form_criteria',
            ]); ?>
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_search_form_criteria',
                'name' => 'lsd[form][criteria]',
                'value' => isset($form['criteria']) && $form['criteria'] ? $form['criteria'] : 0,
            ]); ?>
        </div>
    </div>
</div>
