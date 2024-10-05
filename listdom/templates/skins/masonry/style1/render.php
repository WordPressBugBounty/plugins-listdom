<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Masonry $this */

$ids = $this->listings;
foreach($ids as $id)
{
    $listing = new LSD_Entity_Listing($id);
?>
<div class="lsd-col-<?php echo esc_attr((12 / $this->columns)); ?> <?php echo esc_attr($this->filters_classes($id)); ?>">
    <div class="lsd-listing<?php if(!$this->display_image) echo ' lsd-listing-no-image'; ?>" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>

        <?php if($this->display_image): ?>
        <div class="lsd-listing-image <?php echo esc_attr($listing->image_class_wrapper()); ?>">
            <?php echo LSD_Kses::element($listing->get_image_module($this)); ?>
        </div>
        <?php endif; ?>

        <div class="lsd-listing-body">
			
			<?php if($this->display_labels): ?>
            <div class="lsd-listing-labels">
                <?php echo LSD_Kses::element($listing->get_labels()); ?>
            </div>
            <?php endif; ?>
			
			<?php echo LSD_Kses::element($listing->get_rate_stars()); ?>

            <div class="lsd-listing-title-wrapper">
                <h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
                    <?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
                </h3>
                <div class="lsd-listing-icons-wrapper">
                    <?php echo LSD_Kses::element($listing->get_favorite_button()); ?>
                    <?php echo LSD_Kses::element($listing->get_compare_button()); ?>
                </div>
            </div>
            <div class="lsd-listing-address" <?php echo lsd_schema()->address(); ?>>
                <?php echo LSD_Kses::element($listing->get_address(false)); ?>
            </div>

            <?php do_action('lsd_skins_after_content', $this, $listing); ?>
            
			<div class="lsd-listing-contact-info lsd-viewstyle-grid-only">
				<?php echo LSD_Kses::element($listing->get_contact_info()); ?>
			</div>
			
            <div class="lsd-listing-bottom-bar">
                <?php if($this->display_share_buttons): ?>
                <div class="lsd-listing-share">
                    <?php echo LSD_Kses::element($listing->get_share_buttons()); ?>
                </div>
                <?php endif; ?>
				
				<div class="lsd-listing-price" <?php echo lsd_schema()->priceRange(); ?>>
					<?php echo LSD_Kses::element($listing->get_price()); ?>
				</div>
            </div>
        </div>
    </div>
</div>
<?php
}
