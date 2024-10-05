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

                <div class="lsd-listing-availability">
                    <?php echo LSD_Kses::element($listing->get_availability(true)); ?>
                </div>

                <div class="lsd-listing-top-bar lsd-row">
                    <div class="lsd-col-8">
                        <div class="lsd-listing-category">
                            <?php echo LSD_Kses::element($listing->get_categories(true, false, 'text')); ?>
                        </div>
                    </div>
                    <div class="lsd-col-4">
                        <div class="lsd-listing-price-class">
                            <?php echo LSD_Kses::element($listing->get_price_class()); ?>
                        </div>
                    </div>
                </div>

                <div class="lsd-listing-title-wrapper">
                    <h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
                        <?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
                        <?php echo ($listing->is_claimed() ? '<i class="lsd-icon fas fa-check-square" title="'.esc_attr__('Verified', 'listdom').'"></i>' : ''); ?>
                    </h3>
                    <div class="lsd-listing-icons-wrapper">
                        <?php echo LSD_Kses::element($listing->get_favorite_button()); ?>
                        <?php echo LSD_Kses::element($listing->get_compare_button()); ?>
                    </div>
                </div>

                <p class="lsd-listing-content" <?php echo lsd_schema()->description(); ?>>
                    <?php echo LSD_Kses::element($listing->get_excerpt(12, false)); ?>
                </p>

                <?php do_action('lsd_skins_after_content', $this, $listing); ?>

                <div class="lsd-listing-bottom-bar">
					<?php echo LSD_Kses::element($listing->get_rate_stars('summary')); ?>

                    <?php if($address = $listing->get_address(true)): ?>
                    <div class="lsd-listing-address" <?php echo lsd_schema()->address(); ?>>
                        <?php echo LSD_Kses::element($address); ?>
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
