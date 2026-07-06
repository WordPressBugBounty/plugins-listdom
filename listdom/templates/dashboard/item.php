<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */
/** @var WP_Post $listing */

$status = $this->get_listing_status_data($listing->post_status);
$detail_parts = $this->get_listing_detail_parts($listing);
$badges = $this->get_listing_badges($listing);
?>
<li id="lsd_dashboard_listing_<?php echo esc_attr($listing->ID); ?>">
    <div class="lsd-dashboard-listing-item">
        <div class="lsd-dashboard-listing-status-icon">
            <span class="lsd-dashboard-status lsd-fe-icon-button <?php echo esc_attr($status['class']); ?>" title="<?php echo esc_attr($status['label']); ?>">
                <i class="<?php echo esc_attr($status['icon']); ?>" aria-hidden="true"></i>
                <span class="screen-reader-text"><?php echo esc_html($status['label']); ?></span>
            </span>
        </div>

        <div class="lsd-dashboard-listing-content">
            <div class="lsd-dashboard-listing-title">
                <?php if (LSD_Capability::can('edit_listings', 'edit_posts')): ?>
                    <a class="lsd-dashboard-edit-link" href="<?php echo esc_url($this->get_form_link($listing->ID)); ?>">
                        <?php echo esc_html(get_the_title($listing->ID)); ?>
                    </a>
                <?php else: ?>
                    <?php echo esc_html(get_the_title($listing->ID)); ?>
                <?php endif; ?>
                <?php if ($detail_parts): ?>
                    <div class="lsd-dashboard-listing-detail">
                        <span><?php echo esc_html(implode(' • ', $detail_parts)); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($badges): ?>
                    <div class="lsd-dashboard-listing-badges">
                        <?php foreach ($badges as $badge): ?>
                            <span class="lsd-badge <?php echo esc_attr($badge['class']); ?>">
                                <span><?php echo esc_html($badge['label']); ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="lsd-dashboard-listing-actions">
                <?php if($listing->post_status === LSD_Base::STATUS_PUBLISHED): ?>
                    <a class="lsd-dashboard-view lsd-fe-icon-button lsd-tooltip <?php echo $listing->post_status === LSD_Base::STATUS_PUBLISHED ? '' : 'lsd-disable lsd-disable-icon'; ?>"
                       data-lsd-tooltip="<?php esc_attr_e('View', 'listdom'); ?>"
                       href="<?php echo $listing->post_status !== LSD_Base::STATUS_PUBLISHED ? '#' : esc_url(get_post_permalink($listing->ID)); ?>"
                       target="_blank">
                        <i class="lsd-fe-icon fa fa-eye"></i>
                    </a>
                <?php endif; ?>

                <div class="lsd-actions-menu">
                    <button type="button" class="lsd-actions-menu-toggle lsd-fe-icon-button" aria-label="<?php esc_attr_e('Listing actions', 'listdom'); ?>" aria-expanded="false">
                        <i class="lsd-fe-icon fas fa-ellipsis" aria-hidden="true"></i>
                    </button>

                    <div class="lsd-actions-menu-dropdown">
                        <?php if (LSD_Capability::can('edit_listings', 'edit_posts')): ?>
                            <a class="lsd-actions-menu-item lsd-dashboard-action-edit" href="<?php echo esc_url($this->get_form_link($listing->ID)); ?>">
                                <i class="lsd-fe-icon fa fa-edit" aria-hidden="true"></i>
                                <span><?php esc_html_e('Edit Listing', 'listdom'); ?></span>
                            </a>
                        <?php endif; ?>

                        <?php do_action('lsd_dashboard_actions', $listing, $this); ?>

                        <?php if (LSD_Capability::can('delete_listings', 'delete_posts')): ?>
                            <span class="lsd-actions-menu-item lsd-dashboard-action-delete"
                                  data-id="<?php echo esc_attr($listing->ID); ?>"
                                  data-confirm="0">
                                <i class="lsd-fe-icon fas fa-trash-alt" aria-hidden="true"></i>
                                <span><?php esc_html_e('Trash', 'listdom'); ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php do_action('lsd_dashboard_after_listing', $listing, $this); ?>
</li>
