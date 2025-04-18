<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Slider $this */

$ids = $this->listings;
if (!$this->display_slider_arrows) LSD_Assets::footer('<style>.owl-nav{display:none !important;}</style>');
?>
<?php foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
<div class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
    <div class="lsd-listing-image">
        <?php echo LSD_Kses::element($listing->get_cover_image([1100, 550], $this->get_listing_link_method())); ?>

        <div class="lsd-listing-detail">
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

            <?php if ($this->display_read_more_button): ?>
                <div class="lsd-listing-link-button">
                    <a href="<?php echo esc_url(get_the_permalink($id)); ?>" class="lsd-color-m-bg <?php echo esc_attr($listing->get_text_class()); ?>" <?php echo lsd_schema()->url(); ?>>
                        <?php esc_html_e('Read More', 'listdom'); ?>
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?php endforeach;
