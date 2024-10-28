<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Side $this */

$ids = $this->listings;
foreach($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
<div
    data-listing-id="<?php echo esc_attr($listing->id()); ?>"
    data-url="<?php echo esc_url(get_the_permalink($listing->id())); ?>"
    class="lsd-listing <?php if (!$this->display_image) echo ' lsd-listing-no-image'; ?>"
    <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>
>
    <?php if ($this->display_image): ?>
    <div class="lsd-listing-image <?php echo esc_attr($listing->image_class_wrapper()); ?>">
        <?php echo LSD_Kses::element($listing->get_cover_image([100, 100], $this->get_listing_link_method())); ?>
    </div>
    <?php endif; ?>

    <div class="lsd-listing-body">
        <div class="lsd-listing-title-wrapper">
            <?php if ($this->display_title): ?>
                <h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
                    <?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
                </h3>
            <?php endif; ?>
            <div class="lsd-listing-icons-wrapper">
                <?php if ($this->display_favorite_icon): ?>
                    <?php echo LSD_Kses::element($listing->get_favorite_button()); ?>
                <?php endif; ?>
                <?php if ($this->display_compare_icon): ?>
                    <?php echo LSD_Kses::element($listing->get_compare_button()); ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($this->display_address): ?>
            <div class="lsd-listing-address" <?php echo lsd_schema()->address(); ?>>
                <?php echo LSD_Kses::element($listing->get_address()); ?>
            </div>
        <?php endif; ?>

        <?php do_action('lsd_skins_after_content', $this, $listing); ?>

        <div class="lsd-listing-bottom-bar">
            <?php if ($this->display_review_stars): ?>
                <?php echo LSD_Kses::element($listing->get_rate_stars('stars', false)); ?>
            <?php endif; ?>
            <?php if ($this->display_price): ?>
                <div class="lsd-listing-price" <?php echo lsd_schema()->priceRange(); ?>>
                    <?php echo LSD_Kses::element($listing->get_price()); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
<?php endforeach;
