<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$form = get_post_meta($post->ID, 'lsd_form', true);
if(!is_array($form)) $form = [];

// Results Page
$page = isset($form['page']) && $form['page'] ? $form['page'] : '';

// Connected Shortcodes
$connected_shortcodes = $form['connected_shortcodes'] ?? [];
?>
<div class="lsd-metabox lsd-search-form-metabox">
    <div class="lsd-alert-no-mb">
        <p class="lsd-alert lsd-info"><?php esc_html_e('If you plan to use this search form in a shortcode, you don’t need to enable the following option. Simply select this form in your desired shortcode.', 'listdom'); ?></p>
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
                'value' => $page,
                'class' => 'widefat lsd-searchable-select',
                'show_empty' => true,
                'empty_label' => '('.esc_html__('Current Page', 'listdom').')',
            ]); ?>
            <p class="description"><?php esc_html_e("Select the page where you want the search and filter results to appear. The page must include a Listdom skin shortcode.", 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-row lsd-search-target-shortcode <?php echo $page ? '' : 'lsd-util-hide'; ?>">
        <div class="lsd-col-12">
            <?php echo LSD_Form::label([
                'title' => esc_html__('Target Shortcode', 'listdom'),
                'for' => 'lsd_search_form_shortcode',
            ]); ?>
            <?php echo LSD_Form::shortcodes([
                'id' => 'lsd_search_form_shortcode',
                'name' => 'lsd[form][shortcode]',
                'value' => isset($form['shortcode']) && $form['shortcode'] ? $form['shortcode'] : '',
                'class' => 'widefat lsd-searchable-select',
                'only_archive_skins' => true,
                'show_empty' => true,
            ]); ?>
            <p class="description"><?php esc_html_e("If there are multiple shortcodes or widgets on your selected page, you need to specify the Target Shortcode. Otherwise, all shortcodes and widgets on the page will be filtered!", 'listdom'); ?></p>
        </div>
    </div>
    <?php if (class_exists(LSDADDAPS::class) || class_exists(\LSDPACAPS\Base::class)): ?>
    <div class="lsd-search-connected-shortcodes <?php echo $page ? 'lsd-util-hide' : ''; ?>">
        <div class="lsd-row">
            <div class="lsd-col-12">
                <?php echo LSD_Form::label([
                    'title' => esc_html__('Connected Shortcodes', 'listdom'),
                    'for' => 'lsd_search_form_connected_shortcodes',
                ]); ?>
                <?php echo LSD_Form::autosuggest([
                    'source' => LSD_Base::PTYPE_SHORTCODE.'-searchable',
                    'name' => 'lsd[form][connected_shortcodes]',
                    'id' => 'lsd_form_connected_shortcodes',
                    'input_id' => 'lsd_form_connected_shortcodes_input',
                    'suggestions' => 'lsd_form_connected_shortcodes_suggestions',
                    'values' => $connected_shortcodes,
                    'max_items' => 4,
                    'toggle' => '.lsd-search-ajax-method',
                    'placeholder' => esc_attr__("Enter shortcode's title ...", 'listdom'),
                    'description' => esc_html__("You can select up to 4 searchable skin shortcodes, such as List, Grid, Masonry, List + Grid, Half Map, Side By Side, etc. These shortcodes must be on the same page as the search form; otherwise, they will not be updated.", 'listdom'),
                ]); ?>
            </div>
        </div>
        <div class="lsd-row lsd-search-ajax-method <?php echo count($connected_shortcodes) ? '' : 'lsd-util-hide'; ?>">
            <div class="lsd-col-12">
                <?php echo LSD_Form::label([
                    'title' => esc_html__('Ajax Search', 'listdom'),
                    'for' => 'lsd_search_form_ajax',
                ]); ?>
                <?php echo LSD_Form::select([
                    'id' => 'lsd_search_form_ajax',
                    'name' => 'lsd[form][ajax]',
                    'options' => [
                        1 => esc_html__('On Submit', 'listdom'),
                        2 => esc_html__('On The Fly', 'listdom')
                    ],
                    'value' => $form['ajax'] ?? 1
                ]); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
