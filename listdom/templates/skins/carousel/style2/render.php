<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Carousel $this */

$ids = $this->listings;
?>
<?php foreach($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
<div class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
	<div>
		<div class="lsd-listing-image <?php echo esc_attr($listing->image_class_wrapper()); ?>">
			<?php echo LSD_Kses::element($listing->get_cover_image([390, 260], $this->get_listing_link_method())); ?>

			<?php if($this->display_labels): ?>
			<div class="lsd-listing-labels">
				<?php echo LSD_Kses::element($listing->get_labels()); ?>
			</div>
			<?php endif; ?>

			<?php echo LSD_Kses::element($listing->get_rate_stars()); ?>
		</div>
		<div class="lsd-listing-body">

			<h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
				<?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
			</h3>

			<div class="lsd-listing-price-categories">

				<?php if($listing->get_price()): ?>
				<div class="lsd-listing-price lsd-color-m-bg <?php echo esc_attr($listing->get_text_class()); ?>" <?php echo lsd_schema()->priceRange(); ?>>
					<?php echo LSD_Kses::element($listing->get_price()); ?>
				</div>
				<?php endif; ?>

				<div class="lsd-listing-categories">
					<?php echo LSD_Kses::element($listing->get_categories()); ?>
				</div>

			</div>

			<div class="lsd-listing-availability">
				<?php echo LSD_Kses::element($listing->get_availability(true)); ?>
			</div>

			<div class="lsd-listing-bottom-bar">
				<?php if($this->display_share_buttons): ?>
					<div class="lsd-listing-share">
						<?php echo LSD_Kses::element($listing->get_share_buttons()); ?>
					</div>
				<?php endif; ?>

				<div class="lsd-listing-locations">
					<?php echo LSD_Kses::element($listing->get_locations()); ?>
				</div>
			</div>

		</div>
	</div>
</div>
<?php endforeach;