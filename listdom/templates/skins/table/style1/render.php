<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Table $this */

$ids = $this->listings;
?>
<?php foreach($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
	<tr class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
		<td>
			<h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
                <?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
			</h3>
		</td>
		
		<td class="lsd-listing-address" <?php echo lsd_schema()->address(); ?>>
			<?php echo LSD_Kses::element($listing->get_address(false)); ?>
		</td>
		
		<td <?php echo lsd_schema()->priceRange(); ?>>
			<?php echo LSD_Kses::element($listing->get_price()); ?>
		</td>
		
		<td>
			<?php echo LSD_Kses::element($listing->get_availability(true)); ?>
		</td>
		
		<td class="lsd-listing-phone" <?php echo lsd_schema()->telephone(); ?>>
			<?php echo LSD_Kses::element($listing->get_phone()); ?>
		</td>
	</tr>
<?php endforeach; ?>
	