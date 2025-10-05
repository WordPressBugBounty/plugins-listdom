<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Timeline $this */

$ids = $this->listings;
?>
<?php foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
<div class="lsd-timeline-item">
    <div class="lsd-timeline-header" role="button" tabindex="0">
        <div class="lsd-listing-title-section">
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
            <?php if ($this->display_price): ?>
                <div class="lsd-listing-price" <?php echo lsd_schema()->priceRange(); ?>>
                    <?php echo LSD_Kses::element($listing->get_price()); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="lsd-timeline-toggle-wrapper">
            <?php if ($this->display_price): ?>
                <div class="lsd-listing-price" <?php echo lsd_schema()->priceRange(); ?>>
                    <?php echo LSD_Kses::element($listing->get_price()); ?>
                </div>
            <?php endif; ?>
            <div class="lsd-timeline-toggle-icon" title="<?php echo esc_html__('Toggle details', 'listdom'); ?>">
                <i class="fas fa-chevron-down timeline-arrow"></i>
            </div>
        </div>
    </div>
    <div class="lsd-timeline-body" style="display: none;">
        <div class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
            <?php echo (new LSD_Builders())->listing($listing)->build($this->style); ?>
        </div>
    </div>
</div>
<?php endforeach;
