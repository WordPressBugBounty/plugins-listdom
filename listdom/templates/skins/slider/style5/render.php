<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Slider $this */

$ids = $this->listings;
?>
<?php foreach($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
<div class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
    <div class="lsd-listing-image">
        <?php echo LSD_Kses::element($listing->get_cover_image([1100, 550], $this->get_listing_link_method())); ?>

        <div class="lsd-listing-detail">
            <div class="lsd-listing-detail-top">
                <div class="lsd-listing-share">
                    <?php echo LSD_Kses::element($listing->get_share_buttons('single')); ?>
                </div>

                <div class="lsd-listing-category">
                    <?php echo LSD_Kses::element($listing->get_categories(true, false, 'text')); ?>
                </div>

                <h3 class="lsd-listing-title" <?php echo lsd_schema()->name(); ?>>
                    <?php echo LSD_Kses::element($this->get_title_tag($listing)); ?>
                    <?php echo ($listing->is_claimed() ? '<i class="lsd-icon fas fa-check-square" title="'.esc_attr__('Verified', 'listdom').'"></i>' : ''); ?>
                </h3>
            </div>

            <div class="lsd-listing-detail-bottom">
                <div class="lsd-row">
                    <div class="lsd-col-9">
                        <div class="lsd-listing-locations">
                            <?php echo LSD_Kses::element($listing->get_address(true)); ?>
                        </div>
                    </div>
                    <div class="lsd-col-3">
                        <?php echo LSD_Kses::element($listing->get_rate_stars('summary')); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach;