<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing_Single $this */

// Element Options
$elements = $this->details_page_options['elements'] ?? [];

$categories = isset($elements['categories']['enabled']) && $elements['categories']['enabled'] ? $this->categories(true, 'text') : '';
$title = isset($elements['title']['enabled']) && $elements['title']['enabled'] ? $this->title(false, false) : '';
$discussion_status = isset($elements['discussion']['enabled']) && $elements['discussion']['enabled'];
$labels = isset($elements['labels']['enabled']) && $elements['labels']['enabled'] ? $this->labels() : '';
$favorite = $this->entity->get_favorite_button('button');
$image = isset($elements['image']['enabled']) && $elements['image']['enabled'] ? $this->image() : '';
$gallery = isset($elements['gallery']['enabled']) && $elements['gallery']['enabled'] ? $this->gallery() : '';
$features = isset($elements['features']['enabled']) && $elements['features']['enabled'] ? $this->features() : '';
$content = isset($elements['content']['enabled']) && $elements['content']['enabled'] ? $this->content($this->filtered_content) : '';
$embeds = isset($elements['embed']['enabled']) && $elements['embed']['enabled'] ? $this->embeds() : '';
$video = isset($elements['video']['enabled']) && $elements['video']['enabled'] ? $this->featured_video() : '';
$price = isset($elements['price']['enabled']) && $elements['price']['enabled'] ? $this->price() : '';
$address = isset($elements['address']['enabled']) && $elements['address']['enabled'] ? $this->address() : '';
$locations = isset($elements['locations']['enabled']) && $elements['locations']['enabled'] ? $this->locations() : '';
$share = isset($elements['share']['enabled']) && $elements['share']['enabled'] ? $this->share() : '';
$remark = isset($elements['remark']['enabled']) && $elements['remark']['enabled'] ? $this->remark() : '';
$tags = isset($elements['tags']['enabled']) && $elements['tags']['enabled'] ? $this->tags() : '';
$contact_info = isset($elements['contact']['enabled']) && $elements['contact']['enabled'] ? $this->contact_info() : '';
$attributes = isset($elements['attributes']['enabled']) && $elements['attributes']['enabled'] ? $this->attributes() : '';
$map = isset($elements['map']['enabled']) && $elements['map']['enabled'] ? $this->map() : '';
$owner = isset($elements['owner']['enabled']) && $elements['owner']['enabled'] ? $this->owner() : '';
$abuse = isset($elements['abuse']['enabled']) && $elements['abuse']['enabled'] ? $this->abuse() : '';
$availability = isset($elements['availability']['enabled']) && $elements['availability']['enabled'] ? $this->availability() : '';

$minified_availability = $this->entity->get_availability(true);
$claim = $this->entity->get_claim_button();
$rate_summary = $this->entity->get_rate_stars('summary');
?>
<div class="lsd-row">
    <div class="lsd-col-12">
        <div class="lsd-full-width-banner-gallery">
            <div class="lsd-single-image-wrapper">
                <?php if($gallery) echo LSD_Kses::element($gallery); ?>

                <?php if($labels || $favorite): ?>
                    <?php
                        if($labels) echo LSD_Kses::element($labels);
                        if($favorite) echo LSD_Kses::element($favorite);
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="lsd-row">
    <div class="lsd-col-8">
        <div class="listdom-single-top">
            <?php if($categories) echo LSD_Kses::element($categories); ?>

            <div class="listdom-single-title-wrapper">
                <?php if($title) echo LSD_Kses::element($title); ?>
                <?php echo $this->entity->is_claimed() ? '<i class="lsd-icon fas fa-check-square" title="' . esc_attr__('Verified', 'listdom') . '"></i>' : ''; ?>
            </div>

            <?php if($minified_availability || $claim || $discussion_status || $rate_summary): ?>
                <div class="listdom-single-top-bottom">

                    <?php if($minified_availability): ?>
                        <div class="lsd-single-availability-top">
                            <?php echo LSD_Kses::element($minified_availability); ?>
                        </div>
                    <?php endif; ?>

                    <?php if($claim) echo LSD_Kses::element($claim); ?>

                    <?php if($discussion_status): ?>
                        <div class="listdomer-write-a-review-button">
                            <a href="#lsd-discussion">
                                <?php esc_html_e('Submit a Review', 'listdom'); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if($rate_summary) echo LSD_Kses::element($rate_summary); ?>
                </div>
            <?php endif; ?>

        </div>

        <?php if($image): ?>
            <div class="lsd-single-image-wrapper"><?php echo LSD_Kses::element($image); ?></div>
        <?php endif; ?>

        <?php if($features) echo LSD_Kses::element($features); ?>
        <?php if($content) echo LSD_Kses::element($content); ?>

        {ads}

        <?php if($attributes) echo LSD_Kses::element($attributes); ?>

        {acf}

        <?php if($embeds) echo LSD_Kses::rich($embeds); ?>
        <?php if($video) echo LSD_Kses::rich($video); ?>

        <?php if($remark) echo LSD_Kses::element($remark); ?>

        {auction}
        {booking}
        {discussion}
        {application}
        {stats}

        <?php if($tags) echo LSD_Kses::element($tags); ?>
        <?php if($share) echo LSD_Kses::element($share); ?>

        {franchise}

    </div>
    <div class="lsd-col-4 lsd-single-page-section-right-col lsd-flex lsd-flex-col lsd-flex-content-start lsd-flex-items-stretch lsd-gap-4">

        <?php if($owner) echo LSD_Kses::form($owner); ?>
        <?php if($price) echo '<div class="lsd-single-page-price lsd-single-page-section">' . LSD_Kses::element($price) . '</div>'; ?>
        <?php if($availability) echo LSD_Kses::element($availability); ?>
        <?php if($map) echo LSD_Kses::form($map); ?>

        <?php if($locations || $address): ?>
            <div class="lsd-single-page-section-map-top">
                <?php if($locations) echo LSD_Kses::element($locations); ?>
                <?php if($address) echo LSD_Kses::element($address); ?>
            </div>
        <?php endif; ?>

        {locallogic}

        <?php if($contact_info) echo LSD_Kses::element($contact_info); ?>

        {team}

        <?php if($abuse) echo LSD_Kses::form($abuse); ?>

    </div>
</div>
