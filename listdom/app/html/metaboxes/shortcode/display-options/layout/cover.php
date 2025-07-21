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
<h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Layout", 'listdom'); ?></h3>
<p class="description lsd-mb-4 lsd-mt-3"><?php echo esc_html__("Control listing limit, pagination, and how single listing pages open from the results.", 'listdom'); ?> </p>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listing', 'listdom'),
        'for' => 'lsd_display_options_skin_cover_listing',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php if ($number_of_listings > 100): ?>
            <?php echo LSD_Form::autosuggest([
                'source' => LSD_Base::PTYPE_LISTING,
                'name' => 'lsd[display][cover][listing]',
                'id' => 'lsd_display_options_skin_cover_listing',
                'input_id' => 'lsd_display_options_skin_cover_listing_input',
                'suggestions' => 'lsd_display_options_skin_cover_listing_suggestions',
                'values' => $listing ? [$listing] : [],
                'max_items' => 1,
                'placeholder' => esc_html__("Enter at least 3 characters of the listing's title ...", 'listdom'),
                'description' => esc_html__('You can select only one listing.', 'listdom'),
            ]); ?>
        <?php else: ?>
            <?php echo LSD_Form::listings([
                'id' => 'lsd_display_options_skin_cover_listing',
                'name' => 'lsd[display][cover][listing]',
                'value' => $listing,
                'has_post_thumbnail' => true
            ]); ?>
            <p class="description"><?php echo esc_html__("You can select only the listings that have featured image.", 'listdom'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php $this->field_listing_link('cover', $cover);
