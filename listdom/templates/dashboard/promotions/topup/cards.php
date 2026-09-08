<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $records */
?>
<?php foreach ($records as $record): ?>
    <div class="lsd-dashboard-promotions-card lsd-promotion-topup-active-card" data-search="<?php echo esc_attr($record['search']); ?>" data-sort-expiry="<?php echo esc_attr($record['sort_expiry']); ?>" data-sort-title="<?php echo esc_attr($record['listing_title']); ?>">
        <div class="lsd-dashboard-promotions-card-box lsd-fe-box-white">
            <div class="lsd-dashboard-promotions-card-listing">
                <div class="lsd-dashboard-promotions-card-media">
                    <?php if ($record['listing_image']): ?>
                        <img src="<?php echo esc_url($record['listing_image']); ?>" alt="">
                    <?php else: ?>
                        <i class="lsd-fe-icon fa-solid fa-arrow-up" aria-hidden="true"></i>
                    <?php endif; ?>
                </div>

                <div class="lsd-dashboard-promotions-card-left">
                    <div class="lsd-dashboard-promotions-card-head">
                        <div class="lsd-dashboard-promotions-card-title-wrap">
                            <h4 class="lsd-fe-title-small"><?php echo esc_html($record['listing_title']); ?></h4>
                            <?php if ($record['is_recurring'] && trim((string) $record['recurring_badge']) !== ''): ?>
                                <span class="lsd-dashboard-promotions-recurring"><?php echo LSD_Kses::element($record['recurring_badge']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <p class="lsd-dashboard-promotions-card-copy lsd-fe-description-tiny">
                        <span class="lsd-dashboard-promotions-card-started"><?php echo esc_html($record['progress']['started']); ?></span><br>
                        <span class="lsd-dashboard-promotions-card-remaining">
                            <span class="lsd-dashboard-promotions-card-remaining-value"><?php echo esc_html($record['progress']['remaining_value']); ?></span>
                            <?php echo esc_html($record['progress']['remaining_label']); ?>
                        </span>
                    </p>
                </div>
            </div>

            <div class="lsd-dashboard-promotions-card-progress">
                <div class="lsd-dashboard-promotions-progress-header">
                    <span class="lsd-dashboard-promotions-progress-label"><?php esc_html_e('Time remaining', 'listdom'); ?></span>
                    <span class="lsd-dashboard-promotions-progress-value"><?php echo esc_html(sprintf('%d%%', $record['progress']['percent'])); ?></span>
                </div>
                <span class="lsd-dashboard-promotions-progress-track" role="progressbar" aria-label="<?php esc_attr_e('Time remaining', 'listdom'); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr($record['progress']['percent']); ?>">
                    <span class="lsd-dashboard-promotions-progress-bar status-<?php echo esc_attr($record['progress']['status_class']); ?>" style="<?php echo esc_attr($record['progress']['bar_style']); ?>"></span>
                </span>
            </div>

            <div class="lsd-dashboard-promotions-card-actions">
                <a class="lsd-fe-icon-button lsd-tooltip lsd-tooltip-top" href="<?php echo esc_url($record['listing_url']); ?>" target="_blank" data-lsd-tooltip="<?php esc_attr_e('View Listing', 'listdom'); ?>">
                    <i class="lsd-fe-icon fa fa-eye"></i>
                </a>
                <?php if ($record['can_remove']): ?>
                    <button
                        type="button"
                        class="lsd-fe-icon-button lsd-fe-icon-button-trash lsd-tooltip lsd-tooltip-top lsd-promotion-remove-topup"
                        data-listing-id="<?php echo esc_attr($record['listing_id']); ?>"
                        data-lsd-tooltip="<?php esc_attr_e('Remove Top-up', 'listdom'); ?>"
                    >
                        <i class="lsd-fe-icon fa fa-trash-alt"></i>
                    </button>
                <?php else: ?>
                    <span class="lsd-tooltip lsd-tooltip-top" data-lsd-tooltip="<?php esc_attr_e('Recurring Top-ups must be managed from subscription details', 'listdom'); ?>">
                        <button type="button" class="lsd-fe-icon-button lsd-fe-icon-button-trash lsd-promotion-remove-topup" data-listing-id="<?php echo esc_attr($record['listing_id']); ?>" disabled aria-disabled="true">
                            <i class="lsd-fe-icon fa fa-trash-alt"></i>
                        </button>
                    </span>
                <?php endif; ?>
            </div>

            <div class="lsd-dashboard-promotions-card-manage-actions">
                <?php if ($record['is_recurring'] && $record['manage_url']): ?>
                    <a class="lsd-light-button lsd-dashboard-promotions-manage-link" href="<?php echo esc_url($record['manage_url']); ?>"><?php esc_html_e('Manage Subscription', 'listdom'); ?></a>
                <?php endif; ?>

                <?php if ($record['order_url']): ?>
                    <a class="lsd-text-button lsd-dashboard-promotions-manage-link" href="<?php echo esc_url($record['order_url']); ?>"><?php esc_html_e('View Order', 'listdom'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
