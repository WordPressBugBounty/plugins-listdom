<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Cover $this */

$ids = $this->listings;
?>
<?php foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
<div class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
    <div class="lsd-listing-image">
        <?php echo LSD_Kses::element($listing->get_cover_image([1100, 550], $this->get_listing_link_method())); ?>

        <div class="lsd-listing-detail">
            <div class="lsd-listing-detail-top">
                <?php if ($this->display_share_buttons): ?>
                    <div class="lsd-listing-share">
                        <?php echo LSD_Kses::element($listing->get_share_buttons('single')); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->display_categories): ?>
                    <div class="lsd-listing-category">
                        <?php echo LSD_Kses::element($listing->get_categories(true, false, 'text')); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->display_title): ?>
                    <h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
                        <?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
                        <?php if ($this->display_is_claimed): ?>
                            <?php echo($listing->is_claimed() ? '<i class="lsd-icon fas fa-check-square" title="' . esc_attr__('Verified', 'listdom') . '"></i>' : ''); ?>
                        <?php endif; ?>
                    </h3>
                <?php endif; ?>
            </div>

            <div class="lsd-listing-detail-bottom">
                <div class="lsd-row">
                    <div class="lsd-col-9">
                        <?php if ($this->display_location): ?>
                            <div class="lsd-listing-locations">
                                <?php echo LSD_Kses::element($listing->get_address(true)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="lsd-col-3">
                        <?php if ($this->display_review_stars): ?>
                            <?php echo LSD_Kses::element($listing->get_rate_stars('summary')); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach;
