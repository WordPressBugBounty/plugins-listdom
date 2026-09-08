<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Promotions $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */

$has_topup = $this->is_topup_available();
$user_id = get_current_user_id();
$topup_product = $this->get_topup_product_data();
$active_topup_result = $this->get_active_topup_records_page($user_id, $dashboard);
$active_topup_records = $active_topup_result['items'];
$expired_topup_records = $this->get_expired_topup_records($user_id, $dashboard);
$can_purchase_topup = !empty($topup_product['is_valid']);
?>
<div class="lsd-dashboard-promotions-sections <?php echo LSD_Base::get_lsd_class('sections'); ?>">
        <?php if (!$has_topup): ?>
            <div class="lsd-fe-box-white">
                <?php echo LSD_Base::alert(esc_html__('Top-up addon is not active.', 'listdom'), 'warning'); ?>
            </div>
        <?php else: ?>
            <?php if (!$can_purchase_topup): ?>
                <?php echo LSD_Base::alert(esc_html__('Top-up checkout is currently unavailable because no supported payment option is configured.', 'listdom'), 'warning'); ?>
            <?php endif; ?>
            <div class="lsd-fe-box-white lsd-dashboard-promotions-section">
                <div class="lsd-dashboard-promotions-topup-add">
                    <div class="lsd-dashboard-promotions-section-head lsd-fe-section-heading">
                        <h3 class="lsd-fe-title"><?php esc_html_e('Top-up a New Listing', 'listdom'); ?></h3>
                        <p class="lsd-fe-description"><?php esc_html_e('Active period, billing cycle, and price will be selected on checkout.', 'listdom'); ?></p>
                    </div>

                    <div class="lsd-dashboard-promotions-selection-bar lsd-dashboard-promotions-selection-bar-topup">
                        <button type="button" class="lsd-general-button lsd-promotion-open-popup" data-mode="topup"<?php echo $can_purchase_topup ? '' : ' disabled aria-disabled="true"'; ?>>
                            <?php esc_html_e('Select Listings', 'listdom'); ?> <i class="lsd-fe-icon fa fa-arrow-right"></i>
                        </button>
                        <div class="lsd-divider"></div>
                        <img class="lsd-topup-image" src="<?php echo esc_url($this->lsd_asset_url('img/dashboard/no-topup.svg')); ?>" alt="<?php esc_html__('Topup Image', 'listdom'); ?>">
                    </div>
                </div>
            </div>

            <div class="lsd-fe-box-white lsd-dashboard-promotions-section">
                <div class="lsd-dashboard-promotions-section-head lsd-dashboard-promotions-topup-section-head">
                    <h3 class="lsd-fe-title"><?php esc_html_e('Active Top-ups', 'listdom'); ?></h3>
                    <div class="lsd-dashboard-promotions-sort-control">
                        <select class="lsd-fe-select-base lsd-promotion-topup-sort" aria-label="<?php esc_attr_e('Sort active Top-ups', 'listdom'); ?>">
                            <option value="expiry_asc"><?php esc_html_e('Sort by Expiry Date', 'listdom'); ?></option>
                            <option value="expiry_desc"><?php esc_html_e('Expiry Date: Latest First', 'listdom'); ?></option>
                            <option value="title_asc"><?php esc_html_e('Listing Title: A to Z', 'listdom'); ?></option>
                            <option value="title_desc"><?php esc_html_e('Listing Title: Z to A', 'listdom'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="lsd-dashboard-promotions-search lsd-promotion-search-wrap" data-target=".lsd-promotion-topup-active-section" data-items=".lsd-promotion-topup-active-card">
                    <input type="search" class="lsd-fe-input-base lsd-promotion-search-input" placeholder="<?php esc_attr_e('Type to Search', 'listdom'); ?>">
                    <button type="button" class="lsd-light-button lsd-promotion-search-submit" data-target=".lsd-promotion-topup-active-section" data-items=".lsd-promotion-topup-active-card"><?php esc_html_e('Search', 'listdom'); ?></button>
                </div>

                <?php if (count($active_topup_records)): ?>
                    <div class="lsd-dashboard-promotions-cards lsd-fe-subsections lsd-promotion-topup-active-section">
                        <?php $records = $active_topup_records; include lsd_template('dashboard/promotions/topup/cards.php'); ?>
                    </div>
                    <div class="lsd-dashboard-promotions-load-more-wrap<?php echo $active_topup_result['has_more'] ? '' : ' lsd-util-hide'; ?>">
                        <button type="button" class="lsd-light-button lsd-promotion-topup-load-more" data-next-page="<?php echo esc_attr($active_topup_result['next_page']); ?>">
                            <?php esc_html_e('Load More', 'listdom'); ?>
                        </button>
                    </div>
                <?php else: ?>
                    <?php $dashboard->empty([
                        'title' => esc_html__('No active Top-ups yet', 'listdom'),
                        'description' => esc_html__('Active Top-ups will appear here after checkout is completed.', 'listdom'),
                        'image' => 'img/dashboard/no-topup.svg',
                        'quick_actions' => [],
                    ]); ?>
                <?php endif; ?>
            </div>

            <div class="lsd-fe-box-white lsd-dashboard-promotions-section">
                <div class="lsd-dashboard-promotions-section-head lsd-fe-section-heading">
                    <h3 class="lsd-fe-title"><?php esc_html_e('Expired Top-ups', 'listdom'); ?></h3>
                </div>

                <div class="lsd-dashboard-promotions-search lsd-promotion-search-wrap" data-target=".lsd-promotion-topup-expired-section" data-items=".lsd-promotion-topup-expired-row">
                    <input type="search" class="lsd-fe-input-base lsd-promotion-search-input" placeholder="<?php esc_attr_e('Type to Search', 'listdom'); ?>">
                    <button type="button" class="lsd-light-button lsd-promotion-search-submit" data-target=".lsd-promotion-topup-expired-section" data-items=".lsd-promotion-topup-expired-row"><?php esc_html_e('Search', 'listdom'); ?></button>
                </div>

                <?php if (count($expired_topup_records)): ?>
                    <div class="lsd-dashboard-promotions-table-wrap">
                        <table class="lsd-fe-table lsd-dashboard-promotions-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Listing', 'listdom'); ?></th>
                                    <th><?php esc_html_e('Expired On', 'listdom'); ?></th>
                                    <th><?php esc_html_e('Action', 'listdom'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="lsd-promotion-topup-expired-section">
                                <?php foreach ($expired_topup_records as $record): ?>
                                    <tr class="lsd-promotion-topup-expired-row" data-search="<?php echo esc_attr($record['search']); ?>">
                                        <td><?php echo esc_html($record['listing_title']); ?></td>
                                        <td><?php echo esc_html($record['expired_on']); ?></td>
                                        <td>
                                            <?php if ($record['can_renew']): ?>
                                                <button type="button" class="lsd-light-button lsd-promotion-topup-renew" data-listing-id="<?php echo esc_attr($record['listing_id']); ?>">
                                                    <?php esc_html_e('Renew', 'listdom'); ?>
                                                </button>
                                            <?php else: ?>
                                                <span><?php esc_html_e('N/A', 'listdom'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <?php $dashboard->empty([
                        'title' => esc_html__('No expired Top-ups yet', 'listdom'),
                        'description' => esc_html__('Expired Top-ups will appear here after their active period ends.', 'listdom'),
                        'image' => 'img/dashboard/no-listings.svg',
                        'quick_actions' => [],
                    ]); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
