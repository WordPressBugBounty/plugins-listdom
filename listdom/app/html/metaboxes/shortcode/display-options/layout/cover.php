<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$counts = (array) wp_count_posts(LSD_Base::PTYPE_LISTING);

unset($counts['auto-draft']);
$number_of_listings = array_sum($counts);

$cover = $options['cover'] ?? [];

$listing = isset($cover['listing']) && is_array($cover['listing'])
    ? $cover['listing'][0]
    : ($cover['listing'] ?? 0);
?>
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Layout", 'listdom'); ?></h3>
            <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("Control listing limit, pagination, and how single listing pages open from the results.", 'listdom'); ?> </p>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Listing', 'listdom'),
                    'for' => 'lsd_display_options_skin_cover_listing',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php if ($number_of_listings > 100): ?>
                    <?php echo LSD_Form::autosuggest([
                        'source' => LSD_Base::PTYPE_LISTING,
                        'name' => 'lsd[display][cover][listing]',
                        'id' => 'lsd_display_options_skin_cover_listing',
                        'input_id' => 'lsd_display_options_skin_cover_listing_input',
                        'suggestions' => 'lsd_display_options_skin_cover_listing_suggestions',
                        'values' => $listing ? [$listing] : [],
                        'max_items' => 1,
                        'placeholder' => esc_attr__("Enter at least 3 characters of the listing's title ...", 'listdom'),
                        'description' => esc_html__('You can select only one listing.', 'listdom'),
                    ]); ?>
                <?php else: ?>
                    <?php echo LSD_Form::listings([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_display_options_skin_cover_listing',
                        'name' => 'lsd[display][cover][listing]',
                        'value' => $listing,
                        'has_post_thumbnail' => true
                    ]); ?>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo esc_html__("You can select only the listings that have featured image.", 'listdom'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php $this->field_listing_link('cover', $cover); ?>
    </div>
</div>
