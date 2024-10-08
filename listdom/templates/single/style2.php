<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing_Single $this */

// Element Options
$elements = $this->details_page_options['elements'] ?? [];

$title = isset($elements['title']['enabled']) && $elements['title']['enabled'] ? $this->title() : '';
$price = isset($elements['price']['enabled']) && $elements['price']['enabled'] ? $this->price() : '';
$address = isset($elements['address']['enabled']) && $elements['address']['enabled'] ? $this->address() : '';
$locations = isset($elements['locations']['enabled']) && $elements['locations']['enabled'] ? $this->locations() : '';
$share = isset($elements['share']['enabled']) && $elements['share']['enabled'] ? $this->share() : '';
$categories = isset($elements['categories']['enabled']) && $elements['categories']['enabled'] ? $this->categories() : '';
$image = isset($elements['image']['enabled']) && $elements['image']['enabled'] ? $this->image() : '';
$gallery = isset($elements['gallery']['enabled']) && $elements['gallery']['enabled'] ? $this->gallery() : '';
$embeds = isset($elements['embed']['enabled']) && $elements['embed']['enabled'] ? $this->embeds() : '';
$video = isset($elements['$video']['enabled']) && $elements['$video']['enabled'] ? $this->featured_video() : '';
$labels = isset($elements['labels']['enabled']) && $elements['labels']['enabled'] ? $this->labels() : '';
$content = isset($elements['content']['enabled']) && $elements['content']['enabled'] ? $this->content($this->filtered_content) : '';
$remark = isset($elements['remark']['enabled']) && $elements['remark']['enabled'] ? $this->remark() : '';
$tags = isset($elements['tags']['enabled']) && $elements['tags']['enabled'] ? $this->tags() : '';
$contact_info = isset($elements['contact']['enabled']) && $elements['contact']['enabled'] ? $this->contact_info() : '';
$features = isset($elements['features']['enabled']) && $elements['features']['enabled'] ? $this->features() : '';
$attributes = isset($elements['attributes']['enabled']) && $elements['attributes']['enabled'] ? $this->attributes() : '';
$map = isset($elements['map']['enabled']) && $elements['map']['enabled'] ? $this->map() : '';
$owner = isset($elements['owner']['enabled']) && $elements['owner']['enabled'] ? $this->owner() : '';
$abuse = isset($elements['abuse']['enabled']) && $elements['abuse']['enabled'] ? $this->abuse() : '';
$availability = isset($elements['availability']['enabled']) && $elements['availability']['enabled'] ? $this->availability() : '';
?>
<div class="lsd-row">
    <div class="lsd-col-8">

        <?php if($labels || $image): ?>
		<div class="lsd-single-image-wrapper">
			<?php
				if($labels) echo LSD_Kses::element($labels);
				if($image) echo LSD_Kses::element($image);
			?>
		</div>
        <?php endif; ?>

		<?php if($title) echo LSD_Kses::element($title); ?>

		<?php if($categories) echo LSD_Kses::element($categories); ?>
		<?php if($price) echo LSD_Kses::element($price); ?>
		<?php if($tags) echo LSD_Kses::element($tags); ?>

		<?php if($content) echo LSD_Kses::element($content); ?>
        <?php if($gallery) echo LSD_Kses::element($gallery); ?>
        <?php if($video) echo LSD_Kses::rich($video); ?>

        {ads}

        <?php if($embeds) echo LSD_Kses::rich($embeds); ?>
		<?php if($attributes) echo LSD_Kses::element($attributes); ?>

		{acf}

		<?php if($remark) echo LSD_Kses::element($remark); ?>

        {franchise}
		{auction}
		{stats}

        <?php if($locations || $address): ?>
		<div class="lsd-single-page-section-map-top">
			<?php if($locations) echo LSD_Kses::element($locations); ?>
			<?php if($address) echo LSD_Kses::element($address); ?>
		</div>
        <?php endif; ?>

		<?php if($map) echo LSD_Kses::form($map); ?>

        {booking}
        {discussion}
		{application}

		<?php if($share) echo LSD_Kses::element($share); ?>

    </div>
    <div class="lsd-col-4 lsd-single-page-section-right-col lsd-flex lsd-flex-col lsd-flex-content-start lsd-flex-items-stretch lsd-gap-4">

		<?php if($owner) echo LSD_Kses::form($owner); ?>
		<?php if($features) echo LSD_Kses::element($features); ?>

        {locallogic}

		<?php if($availability) echo LSD_Kses::element($availability); ?>
		<?php if($contact_info) echo LSD_Kses::element($contact_info); ?>

        {team}

        <?php if($abuse) echo LSD_Kses::form($abuse); ?>

    </div>
</div>
