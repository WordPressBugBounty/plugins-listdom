<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Mosaic $this */

$ids = $this->listings;
$index = 0;
?>
<?php foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); $index++; ?>
    <div>
        <div class="lsd-listing<?php if (!$this->display_image) echo ' lsd-listing-no-image'; ?>" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>

            <div class="<?php echo $index % 2 === 0 ? 'lsd-left-side-mobile' : '';  ?> lsd-mosaic-left-side<?php echo (($index - 3) % 4 === 0) || (($index - 4) % 4 === 0) ? ' lsd-left-side-even-rows' : '' ?>">
                <div class="lsd-listing-image <?php echo esc_attr($listing->image_class_wrapper()); ?>">
                    <?php if ($this->display_image): ?>
                        <?php echo LSD_Kses::element($listing->get_image_module($this)); ?>
                    <?php endif; ?>
                </div>

                <?php if($this->display_favorite_icon || $this->display_compare_icon): ?>
                    <div class="lsd-listing-icons-wrapper">
                        <?php if ($this->display_favorite_icon): ?>
                            <?php echo LSD_Kses::element($listing->get_favorite_button()); ?>
                        <?php endif; ?>
                        <?php if ($this->display_compare_icon): ?>
                            <?php echo LSD_Kses::element($listing->get_compare_button()); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="lsd-listing-body lsd-mosaic-right-side<?php echo (($index - 3) % 4 === 0) || (($index - 4) % 4 === 0) ? ' lsd-right-side-even-rows' : '' ?>">
                <?php if ($this->display_categories): ?>
                    <div class="lsd-listing-categories">
                        <?php echo LSD_Kses::element($listing->get_categories()); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->display_title): ?>
                    <h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
                        <?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
                    </h3>
                <?php endif; ?>

                <?php if ($this->display_labels): ?>
                    <div class="lsd-listing-labels">
                        <?php echo LSD_Kses::element($listing->get_labels()); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->display_contact_info): ?>
                    <div class="lsd-listing-contact-info">
                        <?php echo LSD_Kses::element($listing->get_contact_info()); ?>
                    </div>
                <?php endif; ?>

                <?php do_action('lsd_skins_after_content', $this, $listing); ?>

                <div class="lsd-divider"></div>

                <div class="lsd-listing-bottom-bar">
                    <?php if ($listing->get_price() && $this->display_price): ?>
                        <div class="lsd-listing-price lsd-color-m-bg <?php echo esc_attr($listing->get_text_class()); ?>" <?php echo lsd_schema()->priceRange(); ?>>
                            <?php echo LSD_Kses::element($listing->get_price()); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->display_share_buttons): ?>
                        <div class="lsd-listing-share">
                            <?php echo LSD_Kses::element($listing->get_share_buttons()); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($this->display_review_stars): ?>
                    <?php echo LSD_Kses::element($listing->get_rate_stars('stars', false)); ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
<?php endforeach;
