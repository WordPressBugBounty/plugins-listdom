<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Cover $this */

$ids = $this->listings;
?>
<?php foreach($ids as $id): $listing = new LSD_Entity_Listing($id); ?>

    <div class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
        <div class="lsd-listing-image">
            <?php echo LSD_Kses::element($listing->get_featured_image()); ?>
			
			<div class="lsd-listing-detail">
                <div class="lsd-listing-labels">
                    <?php echo LSD_Kses::element($listing->get_labels()); ?>
                </div>
                
				<h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
                    <?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
				</h3>

				<div class="lsd-listing-locations">
					<?php echo LSD_Kses::element($listing->get_locations()); ?>
				</div>
				
				<?php echo LSD_Kses::element($listing->get_rate_stars()); ?>
			</div>
        </div>
    </div>

<?php endforeach;