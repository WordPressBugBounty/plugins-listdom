<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Carousel $this */

$ids = $this->listings;
?>
<?php foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
<div class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
	<div class="lsd-listing-image <?php echo esc_attr($listing->image_class_wrapper()); ?>">

		<?php if ($this->display_image): ?>
			<?php echo LSD_Kses::element($listing->get_cover_image([390, 260], $this->get_listing_link_method())); ?>
		<?php endif; ?>

		<div class="lsd-listing-data-wrapper">
			<?php if ($this->display_title): ?>
				<h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
					<?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
				</h3>
			<?php endif; ?>

			<?php if ($this->display_address): ?>
				<?php if ($address = $listing->get_address(false)): ?>
					<div class="lsd-listing-address" <?php echo lsd_schema()->address(); ?>>
						<?php echo LSD_Kses::element($address); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ($this->display_review_stars): ?>
				<?php echo LSD_Kses::element($listing->get_rate_stars()); ?>
			<?php endif; ?>

			<div class="las-listing-bottom">
				<div class="lsd-listing-price-categories">

					<?php if ($listing->get_price() && $this->display_price): ?>
						<div
							class="lsd-listing-price lsd-color-m-bg <?php echo esc_attr($listing->get_text_class()); ?>" <?php echo lsd_schema()->priceRange(); ?>>
							<?php echo LSD_Kses::element($listing->get_price()); ?>
						</div>
					<?php endif; ?>

					<?php if ($this->display_categories): ?>
						<div class="lsd-listing-categories">
							<?php echo LSD_Kses::element($listing->get_categories()); ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ($this->display_availability): ?>
					<div class="lsd-listing-availability">
						<?php echo LSD_Kses::element($listing->get_availability(true)); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

	</div>
</div>
<?php endforeach;
