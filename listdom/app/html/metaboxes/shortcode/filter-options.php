<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var WP_Post $post */

// Number of Users
$number_of_users = count_users()['total_users'];

// Filter Options
$options = get_post_meta($post->ID, 'lsd_filter', true);

$walker = new LSD_Walker_Taxonomy();
?>
<div class="lsd-metabox lsd-metabox-filter-options">
    <p class="description lsd-m-4"><?php esc_html_e('Use the following options to filter the listings that you want to display with this shortcode', 'listdom'); ?></p>
    <div class="lsd-accordion-title lsd-accordion-active">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Categories', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel lsd-accordion-open">
        <div class="lsd-form-row">
            <div class="lsd-col-12">
                <p class="description"><?php esc_html_e("If you don't want to filter the listings by category then don't select any option.", 'listdom'); ?></p>
                <ul class="lsd-categories">
                    <?php
                    wp_terms_checklist(0, [
                        'descendants_and_self' => 0,
                        'taxonomy' => LSD_Base::TAX_CATEGORY,
                        'selected_cats' => $options[LSD_Base::TAX_CATEGORY] ?? [],
                        'popular_cats' => false,
                        'checked_ontop' => false,
                        'walker' => $walker
                    ]);
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-title">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Locations', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel">
        <div class="lsd-form-row">
            <div class="lsd-col-12">
                <p class="description"><?php esc_html_e("Leave all locations unchecked if you don't want to filter the listings by their location.", 'listdom'); ?></p>
                <ul class="lsd-locations">
                    <?php
                    wp_terms_checklist(0, [
                        'descendants_and_self' => 0,
                        'taxonomy' => LSD_Base::TAX_LOCATION,
                        'selected_cats' => $options[LSD_Base::TAX_LOCATION] ?? [],
                        'popular_cats' => false,
                        'checked_ontop' => false,
                        'walker' => $walker
                    ]);
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-title">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Tags', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel">
        <div class="lsd-form-row">
            <div class="lsd-col-12">
                <input title="<?php esc_attr_e('Tags', 'listdom'); ?>" id="lsd_filter_options_tag" type="text" name="lsd[filter][<?php echo LSD_Base::TAX_TAG; ?>]" value="<?php echo (isset($options[LSD_Base::TAX_TAG]) ? esc_attr($options[LSD_Base::TAX_TAG]) : ''); ?>" class="widefat" />
                <p class="description lsd-mb-0"><?php esc_html_e('Insert your desired tags separated by comma to filter the listings based on them. Leave empty to show the listings of all tags.', 'listdom'); ?></p>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-title">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Features', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel">
        <div class="lsd-form-row">
            <div class="lsd-col-12">
                <p class="description"><?php esc_html_e("Don't select any option if you don't want to filter the listings by features.", 'listdom'); ?></p>
                <ul class="lsd-features">
                    <?php
                    wp_terms_checklist(0, [
                        'descendants_and_self' => 0,
                        'taxonomy' => LSD_Base::TAX_FEATURE,
                        'selected_cats' => $options[LSD_Base::TAX_FEATURE] ?? [],
                        'popular_cats' => false,
                        'checked_ontop' => false,
                        'walker' => $walker
                    ]);
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-title">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Labels', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel">
        <div class="lsd-form-row">
            <div class="lsd-col-12">
                <p class="description"><?php esc_html_e("Don't select any option if you don't want to filter the listings by labels.", 'listdom'); ?></p>
                <ul class="lsd-features">
                    <?php
                    wp_terms_checklist(0, [
                        'descendants_and_self' => 0,
                        'taxonomy' => LSD_Base::TAX_LABEL,
                        'selected_cats' => $options[LSD_Base::TAX_LABEL] ?? [],
                        'popular_cats' => false,
                        'checked_ontop' => false,
                        'walker' => $walker
                    ]);
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-title">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Authors', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel">
        <div class="lsd-form-row">
            <div class="lsd-col-12">
                <p class="description"><?php esc_html_e("Don't select any option if you don't want to filter the listings by authors.", 'listdom'); ?></p>
                <?php if($number_of_users > 20): ?>
                    <?php echo LSD_Form::autosuggest([
                        'source' => 'users',
                        'name' => 'lsd[filter][authors]',
                        'id' => 'lsd_filter_author',
                        'input_id' => 'in_lsd_author',
                        'suggestions' => 'lsd_filter_author_suggestions',
                        'values' => $options['authors'] ?? [],
                        'placeholder' => esc_html__('Type at-least 3 characters of author name ...', 'listdom'),
                        'description' => esc_html__('You can select multiple authors.', 'listdom'),
                    ]); ?>
                <?php else: ?>
                    <ul class="lsd-authors">
                        <?php
                        $authors = get_users([
                            'role__not_in' => ['subscriber','contributor'],
                            'orderby' => 'post_count',
                            'order' => 'DESC',
                            'number' => '-1',
                            'fields' => ['ID', 'display_name']
                        ]);

                        $selected_authors = $options['authors'] ?? [];
                        foreach($authors as $author)
                        {
                            echo '<li><label><input id="in_lsd_author_'.esc_attr($author->ID).'" name="lsd[filter][authors][]" type="checkbox" value="'.esc_attr($author->ID).'" '.(in_array($author->ID, $selected_authors) ? 'checked="checked"' : '').' /> '.esc_html($author->display_name).'</label></li>';
                        }
                        ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
        // Action for Third Party Plugins
        do_action('lsd_shortcode_filter_options', $options);
    ?>
</div>