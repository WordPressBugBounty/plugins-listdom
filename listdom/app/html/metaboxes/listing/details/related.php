<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing $this */
/** @var LSD_Shortcodes_Dashboard|null $dashboard */
/** @var WP_Post $post */

$related = get_post_meta($post->ID, 'lsd_related_listings', true);
if (!is_array($related)) $related = [];
?>
<div class="<?php echo LSD_Base::get_lsd_class('box-white'); ?>">
    <div class="lsd-form-row">
        <div class="lsd-col-10 <?php echo LSD_Base::get_lsd_class('subsections'); ?>">
            <div class="<?php echo $dashboard ? 'lsd-fe-section-heading':'lsd-admin-section-heading'; ?>">
                <h3 class="<?php echo LSD_Base::get_lsd_class('title'); ?>"><?php esc_html_e('Related Listings', 'listdom'); ?><?php $dashboard && $dashboard->required_html('related_listings'); ?></h3>
                <p class="<?php echo LSD_Base::get_lsd_class('description'); ?>"><?php echo esc_html__("Selecting a listing here will override the related listing settings defined in the single listing settings.", 'listdom'); ?> </p>
            </div>
            <?php echo LSD_Form::autosuggest([
                'source' => LSD_Base::PTYPE_LISTING,
                'name' => 'lsd[related_listings]',
                'id' => 'lsd_related_listing_manual_selection',
                'input_id' => 'lsd_related_listing_manual_selection_input',
                'suggestions' => 'lsd_related_listing_manual_selection_suggestion',
                'values' => $related,
                'max_items' => 20,
                'placeholder' => esc_attr__("Enter at least 3 characters of the Listing's title ...", 'listdom'),
            ]); ?>
        </div>
    </div>
</div>
