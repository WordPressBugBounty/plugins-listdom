<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$form = get_post_meta($post->ID, 'lsd_form', true);
if(!is_array($form)) $form = [];
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
                'value' => isset($form['style']) && $form['style'] ? $form['style'] : null,
                'options' => [
                    'default' => esc_html__('Default', 'listdom'),
                    'sidebar' => esc_html__('Sidebar', 'listdom')
                ],
                'class' => 'widefat',
            ]); ?>
        </div>
    </div>
    <div class="lsd-row">
        <div class="lsd-col-12">
            <?php echo LSD_Form::label([
                'title' => esc_html__('Results Page', 'listdom'),
                'for' => 'lsd_search_form_results_page',
            ]); ?>
            <?php echo LSD_Form::pages([
                'id' => 'lsd_search_form_results_page',
                'name' => 'lsd[form][page]',
                'value' => isset($form['page']) && $form['page'] ? $form['page'] : null,
                'class' => 'widefat',
                'show_empty' => true,
            ]); ?>
            <p class="description"><?php echo esc_html__("Select the page that you want to see the search and filter results there. The page should include a Listdom skin shortcode.", 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-row">
        <div class="lsd-col-12">
            <?php echo LSD_Form::label([
                'title' => esc_html__('Target Shortcode', 'listdom'),
                'for' => 'lsd_search_form_shortcode',
            ]); ?>
            <?php echo LSD_Form::shortcodes([
                'id' => 'lsd_search_form_shortcode',
                'name' => 'lsd[form][shortcode]',
                'value' => isset($form['shortcode']) && $form['shortcode'] ? $form['shortcode'] : null,
                'class' => 'widefat',
                'only_archive_skins' => true,
                'show_empty' => true,
            ]); ?>
            <p class="description"><?php echo sprintf(esc_html__("If there are multiple shortcodes/widgets in your selected page, then you should specify the Target Shortcode too. Otherwise all of the shortcodes/widgets will be filtered! Note: Another way to connect this form to a skin shortcode is to choose this form on the desired shortcode setting.", 'listdom'), '<strong>'.esc_html__('Results Page', 'listdom').'</strong>', '<strong>'.esc_html__('Target Shortcode', 'listdom').'</strong>'); ?></p>
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