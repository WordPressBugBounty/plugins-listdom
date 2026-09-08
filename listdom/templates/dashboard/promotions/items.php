<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $dashboard */
/** @var array $listings */

$dashboard = new LSD_Shortcodes_Dashboard();
?>
<div class="lsd-dashboard-promotions-modal-list-group">
    <?php foreach ($listings as $item): ?>
        <?php
        $listing_id = (int) ($item['id'] ?? 0);
        $listing = get_post($listing_id);

        if (!$listing instanceof WP_Post) continue;
        ?>
        <div class="lsd-dashboard-promotions-modal-item lsd-promotion-popup-listing" data-search="<?php echo esc_attr($item['search'] ?? ''); ?>" data-category-id="<?php echo esc_attr($item['category_id'] ?? 0); ?>" data-listing-title="<?php echo esc_attr(get_the_title($listing)); ?>">
            <div class="lsd-dashboard-promotions-modal-item-label lsd-dashboard">
                <div class="lsd-dashboard-promotions-modal-item-body lsd-dashboard-listings-list">
                    <ul class="lsd-dashboard-listings-list-items">
                        <?php $dashboard->item($listing); ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
