<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Listgrid $this */

$ids = $this->listings;
$open = false;
?>
<?php $i = 1; foreach($ids as $id): $listing = new LSD_Entity_Listing($id); ?>

    <?php if($this->columns && ($i % $this->columns) == 1): $open = true; ?>
        <div class="lsd-row">
    <?php endif; ?>

    <div class="lsd-col-<?php echo ($this->default_view == 'list' ? 12 : (12 / $this->columns)); ?>">
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

                <?php if($this->display_review_stars): ?>
				    <?php echo LSD_Kses::element($listing->get_rate_stars()); ?>
                <?php endif; ?>

                <div class="lsd-listing-title-wrapper">
                    <?php if($this->display_title): ?>
                        <h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
                            <?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
                        </h3>
                    <?php endif; ?>
                    <div class="lsd-listing-icons-wrapper">
                        <?php if($this->display_favorite_icon): ?>
                            <?php echo LSD_Kses::element($listing->get_favorite_button()); ?>
                        <?php endif; ?>
                        <?php if($this->display_compare_icon): ?>
                            <?php echo LSD_Kses::element($listing->get_compare_button()); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($this->display_description): ?>
                    <p class="lsd-listing-content lsd-viewstyle-list-only" <?php echo lsd_schema()->description(); ?>>
                        <?php echo LSD_Kses::element($listing->get_excerpt($this->description_length)); ?>
                    </p>
                <?php endif; ?>

                <?php do_action('lsd_skins_after_content', $this, $listing); ?>
				
				<div class="lsd-listing-price-categories">

					<?php if($listing->get_price() && $this->display_price): ?>
					<div class="lsd-listing-price lsd-color-m-bg <?php echo esc_attr($listing->get_text_class()); ?>" <?php echo lsd_schema()->priceRange(); ?>>
						<?php echo LSD_Kses::element($listing->get_price()); ?>
					</div>
					<?php endif; ?>

                    <?php if($this->display_categories): ?>
                        <div class="lsd-listing-categories">
                            <?php echo LSD_Kses::element($listing->get_categories()); ?>
                        </div>
					<?php endif; ?>
				</div>

                <?php if($this->display_availability): ?>
                    <div class="lsd-listing-availability">
                        <?php echo LSD_Kses::element($listing->get_availability(true)); ?>
                    </div>
				<?php endif; ?>

				<div class="lsd-listing-bottom-bar">
					<?php if($this->display_share_buttons): ?>
						<div class="lsd-listing-share">
							<?php echo LSD_Kses::element($listing->get_share_buttons()); ?>
						</div>
					<?php endif; ?>

                    <?php if($this->display_location): ?>
                        <div class="lsd-listing-locations">
                            <?php echo LSD_Kses::element($listing->get_locations()); ?>
                        </div>
                    <?php endif; ?>
                </div>
				
            </div>
        </div>
    </div>

    <?php if($this->columns && ($i % $this->columns) == 0): $open = false; ?>
        </div>
    <?php endif; ?>

<?php $i++; endforeach; ?>
<?php /** Close the unclosed Row **/ if($this->columns && $open) echo '</div>';
