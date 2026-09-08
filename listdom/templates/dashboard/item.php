<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */
/** @var WP_Post $listing */

// Listing Status
$status_key = $this->get_listing_status_key($listing);
$status = $this->get_listing_status_data($status_key);
$status_tooltip = $this->get_listing_status_tooltip($listing, $status);

// Listing Actions
$is_trashed = $listing->post_status === LSD_Base::STATUS_TRASH;
$edit_link = $this->get_form_link($listing->ID);
$renewal = $this->get_listing_renewal($listing);
$payment_link = $this->get_listing_payment_link($listing);
$skip_dashboard_actions = false;

// Listing Details
$detail_parts = $this->get_listing_detail_parts($listing);
$badges = $this->get_listing_badges($listing);
$selection_control = apply_filters('lsd_dashboard_listing_selection_control', '', $listing);

// Listing Permissions
$can_edit_listing = LSD_Capability::can('edit_listings', 'edit_posts') && current_user_can('edit_post', $listing->ID);
$can_manage_listing_status = $can_edit_listing && ((int) $listing->post_author === get_current_user_id() || current_user_can('edit_others_posts'));
$is_listing_owner = (int) $listing->post_author === get_current_user_id();
$visibility_enabled = class_exists('LSDPACVIS\\Module') && LSD_Components::visibility() && $this->is_enabled('visibility', $listing->ID);
?>
<li id="lsd_dashboard_listing_<?php echo esc_attr($listing->ID); ?>">
    <div class="lsd-dashboard-listing-item">
        <?php echo $selection_control; ?>
        <div class="lsd-dashboard-listing-status-icon">
            <span class="lsd-dashboard-status lsd-fe-icon-button <?php echo esc_attr($status['class']); ?>" title="<?php echo esc_attr($status_tooltip); ?>">
                <i class="<?php echo esc_attr($status['icon']); ?>" aria-hidden="true"></i>
                <span class="screen-reader-text"><?php echo esc_html($status_tooltip); ?></span>
            </span>
        </div>

        <div class="lsd-dashboard-listing-content">
            <div class="lsd-dashboard-listing-title">
                <?php if (!$is_trashed && $can_edit_listing && $selection_control === ''): ?>
                    <a class="lsd-dashboard-edit-link" href="<?php echo esc_url($this->get_form_link($listing->ID)); ?>">
                        <?php echo esc_html(get_the_title($listing->ID)); ?>
                    </a>
                <?php else: ?>
                    <span class="lsd-dashboard-edit-link"><?php echo esc_html(get_the_title($listing->ID)); ?></span>
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
                        <?php if (!$is_trashed && $can_edit_listing): ?>
                            <a class="lsd-actions-menu-item lsd-dashboard-action-edit" href="<?php echo esc_url($edit_link); ?>">
                                <i class="lsd-fe-icon fa fa-edit" aria-hidden="true"></i>
                                <span><?php esc_html_e('Edit Listing', 'listdom'); ?></span>
                            </a>
                        <?php endif; ?>

                        <?php if ($status_key === LSD_Base::STATUS_DRAFT): ?>
                            <?php if ($can_manage_listing_status): ?>
                                <button type="button" class="lsd-actions-menu-item lsd-dashboard-action-status lsd-dashboard-action-publish" data-id="<?php echo esc_attr($listing->ID); ?>" data-status-action="<?php echo current_user_can('publish_posts') ? 'publish' : 'pending'; ?>">
                                    <i class="lsd-fe-icon fa fa-circle-check" aria-hidden="true"></i>
                                    <span><?php echo esc_html(current_user_can('publish_posts') ? __('Publish', 'listdom') : __('Submit for Review', 'listdom')); ?></span>
                                </button>
                            <?php endif; ?>
                        <?php elseif ($status_key === LSD_Base::STATUS_SCHEDULED): ?>
                            <?php if ($can_manage_listing_status && current_user_can('publish_posts')): ?>
                                <button type="button" class="lsd-actions-menu-item lsd-dashboard-action-schedule lsd-dashboard-schedule-open" data-modal="#lsd_dashboard_schedule_modal_<?php echo esc_attr($listing->ID); ?>">
                                    <i class="lsd-fe-icon fa-solid fa-refresh" aria-hidden="true"></i>
                                    <span><?php esc_html_e('Edit Schedule', 'listdom'); ?></span>
                                </button>
                            <?php endif; ?>
                            <?php if ($can_manage_listing_status): ?>
                                <button type="button" class="lsd-actions-menu-item lsd-dashboard-action-status lsd-dashboard-action-publish" data-id="<?php echo esc_attr($listing->ID); ?>" data-status-action="<?php echo esc_attr(current_user_can('publish_posts') ? 'publish' : 'pending'); ?>">
                                    <i class="lsd-fe-icon fa-solid fa-check-circle" aria-hidden="true"></i>
                                    <span><?php echo esc_html(current_user_can('publish_posts') ? __('Publish Now', 'listdom') : __('Publish / Submit for Review', 'listdom')); ?></span>
                                </button>
                            <?php endif; ?>
                        <?php elseif ($status_key === LSD_Base::STATUS_OFFLINE): ?>
                            <?php if ($can_manage_listing_status && $visibility_enabled): ?>
                                <button type="button" class="lsd-actions-menu-item lsd-dashboard-action-visibility lsd-dashboard-visibility-open" data-modal="#lsd_dashboard_visibility_modal_<?php echo esc_attr($listing->ID); ?>">
                                    <i class="lsd-fe-icon fa-solid fa-eye" aria-hidden="true"></i>
                                    <span><?php esc_html_e('Edit Visibility', 'listdom'); ?></span>
                                </button>
                            <?php endif; ?>
                            <?php if ($can_manage_listing_status): ?>
                                <button type="button" class="lsd-actions-menu-item lsd-dashboard-action-status lsd-dashboard-action-visibility" data-id="<?php echo esc_attr($listing->ID); ?>" data-status-action="<?php echo esc_attr(current_user_can('publish_posts') ? 'publish' : 'pending'); ?>">
                                    <i class="lsd-fe-icon fa-solid fa-refresh" aria-hidden="true"></i>
                                    <span><?php echo esc_html(current_user_can('publish_posts') ? __('Put Online', 'listdom') : __('Publish / Submit for Review', 'listdom')); ?></span>
                                </button>
                            <?php endif; ?>
                        <?php elseif ($status_key === LSD_Base::STATUS_HOLD): ?>
                            <?php if ($payment_link): ?>
                            <a class="lsd-actions-menu-item lsd-dashboard-action-payment" href="<?php echo esc_url($payment_link); ?>">
                                <i class="lsd-fe-icon fa fa-credit-card" aria-hidden="true"></i>
                                <span><?php esc_html_e('Resolve Payment', 'listdom'); ?></span>
                            </a>
                            <?php endif; ?>
                            <?php if ($is_listing_owner) do_action('lsd_dashboard_actions', $listing, $this); $skip_dashboard_actions = true; ?>
                        <?php elseif ($status_key === LSD_Base::STATUS_EXPIRED): ?>
                            <?php if ($renewal['link']): ?>
                            <a class="lsd-actions-menu-item lsd-dashboard-action-renew" href="<?php echo esc_url($renewal['link']); ?>">
                                <i class="lsd-fe-icon fa fa-refresh" aria-hidden="true"></i>
                                <span><?php esc_html_e('Renew Package', 'listdom'); ?></span>
                            </a>
                            <?php elseif ($renewal['unavailable_message']): ?>
                            <button type="button" class="lsd-actions-menu-item lsd-dashboard-action-renew lsd-tooltip lsd-tooltip-top" data-lsd-tooltip="<?php echo esc_attr($renewal['unavailable_message']); ?>" disabled>
                                <i class="lsd-fe-icon fa fa-refresh" aria-hidden="true"></i>
                                <span><?php esc_html_e('Renew Package', 'listdom'); ?></span>
                            </button>
                            <?php endif; ?>
                            <?php if ($is_listing_owner) do_action('lsd_dashboard_actions', $listing, $this); $skip_dashboard_actions = true; ?>
                        <?php elseif ($status_key === LSD_Base::STATUS_DENIED): ?>
                            <?php if ($can_manage_listing_status): ?>
                                <button type="button" class="lsd-actions-menu-item lsd-dashboard-action-status lsd-dashboard-action-publish" data-id="<?php echo esc_attr($listing->ID); ?>" data-status-action="pending">
                                    <i class="lsd-fe-icon fa fa-paper-plane" aria-hidden="true"></i>
                                    <span><?php esc_html_e('Resubmit for Review', 'listdom'); ?></span>
                                </button>
                            <?php endif; ?>
                        <?php elseif ($status_key === LSD_Base::STATUS_INACTIVE): ?>
                            <?php if ($can_manage_listing_status): ?>
                                <button type="button" class="lsd-actions-menu-item lsd-dashboard-action-status lsd-dashboard-action-publish" data-id="<?php echo esc_attr($listing->ID); ?>" data-status-action="<?php echo esc_attr(current_user_can('publish_posts') ? 'publish' : 'pending'); ?>">
                                    <i class="lsd-fe-icon fa fa-check-circle" aria-hidden="true"></i>
                                    <span><?php echo esc_html(current_user_can('publish_posts') ? __('Activate', 'listdom') : __('Publish / Submit for Review', 'listdom')); ?></span>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($is_listing_owner && !$is_trashed && !$skip_dashboard_actions) do_action('lsd_dashboard_actions', $listing, $this); ?>

                        <?php if ($is_trashed): ?>
                            <?php $trash_retention_days = defined('EMPTY_TRASH_DAYS') ? (int) EMPTY_TRASH_DAYS : 30; ?>
                            <?php $trash_time = (int) get_post_meta($listing->ID, '_wp_trash_meta_time', true); ?>
                            <?php $remaining_trash_days = $trash_retention_days; ?>
                            <?php if ($trash_retention_days > 0 && $trash_time): ?>
                                <?php $remaining_trash_days = max(1, (int) ceil(($trash_time + ($trash_retention_days * DAY_IN_SECONDS) - time()) / DAY_IN_SECONDS)); ?>
                            <?php endif; ?>
                            <div class="lsd-dashboard-trash-notice">
                                <?php if ($trash_retention_days > 0): ?>
                                    <?php echo wp_kses_post(sprintf(esc_html__('This listing will be deleted permanently in %s.', 'listdom'), '<strong>' . sprintf(esc_html__('%d days', 'listdom'), $remaining_trash_days) . '</strong>')); ?>
                                <?php else: ?>
                                    <?php esc_html_e('Trash is disabled. This listing will be deleted permanently.', 'listdom'); ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($can_manage_listing_status && current_user_can('delete_post', $listing->ID)): ?>
                                <button type="button" class="lsd-actions-menu-item lsd-dashboard-action-status lsd-dashboard-action-restore" data-id="<?php echo esc_attr($listing->ID); ?>" data-status-action="restore">
                                    <i class="lsd-fe-icon fa fa-undo" aria-hidden="true"></i>
                                    <span><?php esc_html_e('Restore', 'listdom'); ?></span>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (LSD_Capability::can('delete_listings', 'delete_posts') && current_user_can('delete_post', $listing->ID)): ?>
                            <?php $trash_retention_days = defined('EMPTY_TRASH_DAYS') ? (int) EMPTY_TRASH_DAYS : 30; ?>
                            <?php $delete_permanently = $is_trashed || $trash_retention_days === 0; ?>
                            <?php if (!$is_trashed && $trash_retention_days === 0): ?>
                                <div class="lsd-dashboard-trash-notice">
                                    <?php esc_html_e('Trash is disabled. This listing will be deleted permanently.', 'listdom'); ?>
                                </div>
                            <?php endif; ?>
                            <button type="button" class="lsd-actions-menu-item lsd-dashboard-action-delete lsd-tooltip lsd-tooltip-top"
                                  data-id="<?php echo esc_attr($listing->ID); ?>"
                                  data-confirm="0"
                                  data-lsd-tooltip="<?php echo esc_attr($delete_permanently ? __('Click twice to permanently delete', 'listdom') : __('Click twice to move to Trash', 'listdom')); ?>">
                                <i class="lsd-fe-icon fas fa-trash-alt" aria-hidden="true"></i>
                                <span><?php echo esc_html($delete_permanently ? __('Delete Now', 'listdom') : __('Trash', 'listdom')); ?></span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($status_key === LSD_Base::STATUS_SCHEDULED): ?>
        <div id="lsd_dashboard_schedule_modal_<?php echo esc_attr($listing->ID); ?>" class="lsd-modal lsd-dashboard-schedule-modal" role="dialog" aria-modal="true" aria-labelledby="lsd_dashboard_schedule_title_<?php echo esc_attr($listing->ID); ?>">
            <div class="lsd-modal-content">
                <div class="lsd-fe-section-heading">
                    <div class="lsd-fe-title-icon">
                        <i class="lsd-fe-icon fa-solid fa-calendar-days" aria-hidden="true"></i>
                        <h3 id="lsd_dashboard_schedule_title_<?php echo esc_attr($listing->ID); ?>" class="lsd-fe-title"><?php esc_html_e('Edit Schedule', 'listdom'); ?></h3>
                    </div>
                    <p class="lsd-fe-description"><?php echo esc_html($listing->post_title); ?></p>
                </div>
                <div class="lsd-fe-sections lsd-dashboard-switch lsd-dashboard-schedule">
                    <div class="lsd-dashboard-schedule-fields">
                        <label>
                            <span><?php esc_html_e('Date & Time', 'listdom'); ?></span>
                            <?php echo LSD_Form::input([
                                'id' => 'lsd_dashboard_schedule_datetime_' . $listing->ID,
                                'class' => 'lsd-dashboard-schedule-datetime',
                                'value' => get_date_from_gmt($listing->post_date_gmt, 'Y-m-d\TH:i'),
                            ], 'datetime-local'); ?>
                        </label>
                    </div>
                    <p class="lsd-dashboard-schedule-message lsd-util-hide" role="alert" aria-atomic="true"></p>
                    <div class="lsdaddsub-switch-modal-actions lsd-dashboard-schedule-actions">
                        <button type="button" class="lsd-text-button lsd-dashboard-schedule-cancel"><?php esc_html_e('Cancel', 'listdom'); ?></button>
                        <button type="button" class="lsd-general-button lsd-dashboard-schedule-save" data-id="<?php echo esc_attr($listing->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('lsd_dashboard')); ?>"><?php esc_html_e('Save Changes', 'listdom'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($status_key === LSD_Base::STATUS_OFFLINE && $can_manage_listing_status && $visibility_enabled): ?>
        <div id="lsd_dashboard_visibility_modal_<?php echo esc_attr($listing->ID); ?>" class="lsd-modal lsd-dashboard-visibility-modal" role="dialog" aria-modal="true" aria-labelledby="lsd_dashboard_visibility_title_<?php echo esc_attr($listing->ID); ?>">
            <div class="lsd-modal-content">
                <div class="lsd-fe-section-heading">
                    <div class="lsd-fe-title-icon">
                        <i class="lsd-fe-icon fa-solid fa-eye" aria-hidden="true"></i>
                        <h3 id="lsd_dashboard_visibility_title_<?php echo esc_attr($listing->ID); ?>" class="lsd-fe-title"><?php esc_html_e('Edit Visibility', 'listdom'); ?></h3>
                    </div>
                    <p class="lsd-fe-description"><?php echo esc_html($listing->post_title); ?></p>
                </div>
                <div class="lsd-fe-sections lsd-dashboard-switch lsd-dashboard-visibility">
                    <?php (new \LSDPACVIS\Module())->output($listing, $this); ?>
                    <p class="lsd-dashboard-visibility-message lsd-util-hide" role="alert" aria-atomic="true"></p>
                    <div class="lsdaddsub-switch-modal-actions lsd-dashboard-visibility-actions">
                        <button type="button" class="lsd-text-button lsd-dashboard-visibility-cancel"><?php esc_html_e('Cancel', 'listdom'); ?></button>
                        <button type="button" class="lsd-general-button lsd-dashboard-visibility-save" data-id="<?php echo esc_attr($listing->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('lsd_dashboard')); ?>"><?php esc_html_e('Save Changes', 'listdom'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php do_action('lsd_dashboard_after_listing', $listing, $this); ?>
</li>
