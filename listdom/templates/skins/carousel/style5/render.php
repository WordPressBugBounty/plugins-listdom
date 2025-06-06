<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Carousel $this */

$ids = $this->listings;
?>
<?php foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
<div class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
	<div>
		<div class="lsd-listing-image <?php echo esc_attr($listing->image_class_wrapper()); ?>">

			<?php if ($this->display_image): ?>
				<?php echo LSD_Kses::element($listing->get_cover_image([390, 260], $this->get_listing_link_method())); ?>
			<?php endif; ?>

			<?php if ($this->display_labels): ?>
				<div class="lsd-listing-labels">
					<?php echo LSD_Kses::element($listing->get_labels()); ?>
				</div>
			<?php endif; ?>

			<div class="lsd-listing-image-icons-wrapper">
				<?php if ($this->display_favorite_icon): ?>
					<div class="lsd-listing-favorite">
						<?php echo LSD_Kses::element($listing->get_favorite_button()); ?>
					</div>
				<?php endif; ?>
				<?php if ($this->display_compare_icon): ?>
					<div class="lsd-listing-compare">
						<?php echo LSD_Kses::element($listing->get_compare_button()); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ($this->display_availability): ?>
				<div class="lsd-listing-availability">
					<?php echo LSD_Kses::element($listing->get_availability(true)); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="lsd-listing-body">

			<div class="lsd-listing-top-bar lsd-row">
				<div class="lsd-col-8">
					<?php if ($this->display_categories): ?>
						<div class="lsd-listing-category">
							<?php echo LSD_Kses::element($listing->get_categories(['show_color' => true, 'multiple_categories' => false, 'color_method' => 'text'])); ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="lsd-col-4">
					<?php if ($this->display_price_class): ?>
						<div class="lsd-listing-price-class">
							<?php echo LSD_Kses::element($listing->get_price_class()); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if ($this->display_title): ?>
				<h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
					<?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
					<?php if ($this->display_is_claimed): ?>
						<?php echo($listing->is_claimed() ? '<i class="lsd-icon fas fa-check-square" title="' . esc_attr__('Verified', 'listdom') . '"></i>' : ''); ?>
					<?php endif; ?>
				</h3>
			<?php endif; ?>

			<?php if ($this->display_description): ?>
				<p class="lsd-listing-content" <?php echo lsd_schema()->description(); ?>>
					<?php echo LSD_Kses::element($listing->get_excerpt($this->description_length)); ?>
				</p>
			<?php endif; ?>
			<div class="lsd-listing-bottom-bar">
				<?php if ($this->display_review_stars): ?>
					<?php echo LSD_Kses::element($listing->get_rate_stars('summary')); ?>
				<?php endif; ?>
				<?php if ($this->display_address): ?>
					<?php if ($address = $listing->get_address()): ?>
						<div class="lsd-listing-address" <?php echo lsd_schema()->address(); ?>>
							<?php echo LSD_Kses::element($address); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>

		</div>
	</div>
</div>
<?php endforeach;
