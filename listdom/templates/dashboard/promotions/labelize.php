<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Promotions $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */

$has_labelize = $this->is_labelize_available();
$user_id = get_current_user_id();
$available_labels = $this->get_available_labels();
$active_records = $this->get_active_label_records($user_id, $dashboard);
$active_label_groups = $this->group_label_records($active_records);
$expired_records = $this->get_expired_label_records($user_id, $dashboard);
?>
<div class="lsd-dashboard-promotions-sections <?php echo LSD_Base::get_lsd_class('sections'); ?>">
        <?php if (!$has_labelize): ?>
            <div class="lsd-fe-box-white">
                <?php echo LSD_Base::alert(esc_html__('Labelize addon must be active to manage paid labels from the dashboard.', 'listdom'), 'warning'); ?>
            </div>
        <?php else: ?>
            <div class="lsd-fe-box-white lsd-dashboard-promotions-section lsd-dashboard-promotions-labelize-section lsd-dashboard-promotions-labelize-add">
                <div class="lsd-dashboard-promotions-section-head lsd-fe-section-heading">
                    <h3 class="lsd-fe-title"><?php esc_html_e('Available Premium Labels', 'listdom'); ?></h3>
                    <p class="lsd-fe-description"><?php esc_html_e('Active period, billing cycle, and price will be selected on checkout.', 'listdom'); ?></p>
                </div>

                <div class="lsd-dashboard-promotions-search lsd-promotion-search-wrap" data-target=".lsd-promotion-available-section" data-items=".lsd-promotion-label">
                    <input type="search" class="lsd-fe-input-base lsd-promotion-search-input" placeholder="<?php esc_attr_e('Type to Search', 'listdom'); ?>">
                    <button type="button" class="lsd-light-button lsd-promotion-search-submit" data-target=".lsd-promotion-available-section" data-items=".lsd-promotion-label"><?php esc_html_e('Search', 'listdom'); ?></button>
                </div>

                <?php if (count($available_labels)): ?>
                    <div class="lsd-dashboard-promotions-chip-list lsd-promotion-available-section">
                        <?php foreach ($available_labels as $label): ?>
                            <?php echo LSD_Kses::element($this->label_chip($label['name'], (int) $label['id'], [
                                'tag' => 'button',
                                'class' => 'lsd-dashboard-promotions-chip lsd-promotion-label',
                                'attributes' => array_filter([
                                    'type' => 'button',
                                    'data-label-id' => $label['id'],
                                    'data-search' => $label['search'],
                                    'style' => $label['chip_style'],
                                ], static function ($value)
                                {
                                    return $value !== '' && $value !== null;
                                }),
                                'icon_html' => '<i class="lsd-fe-icon fa-regular fa-circle-check" aria-hidden="true"></i>',
                            ])); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php $dashboard->empty([
                        'title' => esc_html__('No premium labels available', 'listdom'),
                        'description' => esc_html__('There are no payable labels configured right now.', 'listdom'),
                        'image' => 'img/dashboard/no-listings.svg',
                        'quick_actions' => [],
                    ]); ?>
                <?php endif; ?>

                <div class="lsd-dashboard-promotions-selection-bar">
                    <span class="lsd-dashboard-promotions-selection-count"><strong class="lsd-promotion-selected-label-count">0</strong> <?php esc_html_e('Selected', 'listdom'); ?></span>
                    <button type="button" class="lsd-light-button lsd-promotion-open-popup" data-mode="labelize"<?php echo count($available_labels) ? '' : ' disabled aria-disabled="true"'; ?>><?php esc_html_e('Select Listings to Add Labels', 'listdom'); ?></button>
                </div>
            </div>

            <div class="lsd-fe-box-white lsd-dashboard-promotions-section lsd-dashboard-promotions-labelize-section lsd-dashboard-promotions-labelize-active">
                <div class="lsd-dashboard-promotions-section-head lsd-fe-section-heading">
                    <h3 class="lsd-fe-title"><?php esc_html_e('Active Added Labels', 'listdom'); ?></h3>
                </div>

                <div class="lsd-dashboard-promotions-search lsd-promotion-search-wrap" data-target=".lsd-promotion-active-section" data-items=".lsd-promotion-active-card">
                    <input type="search" class="lsd-fe-input-base lsd-promotion-search-input" placeholder="<?php esc_attr_e('Type to Search', 'listdom'); ?>">
                    <button type="button" class="lsd-light-button lsd-promotion-search-submit" data-target=".lsd-promotion-active-section" data-items=".lsd-promotion-active-card"><?php esc_html_e('Search', 'listdom'); ?></button>
                </div>

                <?php if (count($active_records)): ?>
                    <div class="lsd-dashboard-promotions-card-grid lsd-promotion-active-section">
                        <?php foreach ($active_label_groups as $label_group): ?>
                            <div
                                class="lsd-dashboard-promotions-card lsd-promotion-active-card"
                                data-search="<?php echo esc_attr($label_group['search']); ?>"
                                <?php if ($label_group['label_color'] !== ''): ?>style="--lsd-dashboard-promotions-label-color: <?php echo esc_attr($label_group['label_color']); ?>;"<?php endif; ?>
                            >
                                <div class="lsd-dashboard-promotions-card-chip-row">
                                    <?php echo LSD_Kses::element($this->label_chip($label_group['label_name'], (int) $label_group['label_id'], [
                                        'class' => 'lsd-dashboard-promotions-chip lsd-dashboard-promotions-chip-static',
                                        'attributes' => array_filter([
                                            'style' => $label_group['label_chip_style'],
                                        ], static function ($value)
                                        {
                                            return $value !== '' && $value !== null;
                                        }),
                                    ])); ?>
                                </div>

                                <div class="lsd-dashboard-promotions-label-listings">
                                    <?php foreach ($label_group['records'] as $record): ?>
                                        <div class="lsd-dashboard-promotions-card-box lsd-fe-box-white">
                                            <div class="lsd-dashboard-promotions-card-head">
                                                <div class="lsd-dashboard-promotions-card-title-wrap">
                                                    <h4 class="lsd-fe-title-small"><?php echo esc_html($record['listing_title']); ?></h4>
                                                    <?php if ($record['is_recurring'] && trim((string) $record['recurring_badge']) !== ''): ?>
                                                        <span class="lsd-dashboard-promotions-recurring"><?php echo LSD_Kses::element($record['recurring_badge']); ?></span>
                                                    <?php endif; ?>
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

                                            <p class="lsd-dashboard-promotions-card-copy">
                                                <span class="lsd-dashboard-promotions-card-started"><?php echo esc_html($record['progress']['started']); ?></span><br>
                                                <span class="lsd-dashboard-promotions-card-remaining">
                                                    <span class="lsd-dashboard-promotions-card-remaining-value"><?php echo esc_html($record['progress']['remaining_value']); ?></span>
                                                    <?php echo esc_html($record['progress']['remaining_label']); ?>
                                                </span>
                                            </p>

                                            <div class="lsd-dashboard-promotions-card-footer">
                                                <div class="lsd-dashboard-promotions-card-actions">
                                                    <a class="lsd-fe-icon-button lsd-tooltip lsd-tooltip-top" href="<?php echo esc_url($record['listing_url']); ?>" target="_blank" data-lsd-tooltip="<?php esc_attr_e('View Listing', 'listdom'); ?>">
                                                        <i class="lsd-fe-icon fa fa-eye"></i>
                                                    </a>
                                                    <?php if ($record['can_remove']): ?>
                                                        <button
                                                            type="button"
                                                            class="lsd-fe-icon-button lsd-fe-icon-button-trash lsd-tooltip lsd-tooltip-top lsd-promotion-remove-label"
                                                            data-label-id="<?php echo esc_attr($record['label_id']); ?>"
                                                            data-listing-id="<?php echo esc_attr($record['listing_id']); ?>"
                                                            data-recurring-id="<?php echo esc_attr($record['recurring_id']); ?>"
                                                            data-lsd-tooltip="<?php esc_attr_e('Remove Label', 'listdom'); ?>"
                                                        >
                                                            <i class="lsd-fe-icon fa fa-trash-alt"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="lsd-tooltip lsd-tooltip-top" data-lsd-tooltip="<?php esc_attr_e('Recurring labels must be managed from subscription details', 'listdom'); ?>">
                                                            <button type="button" class="lsd-fe-icon-button lsd-fe-icon-button-trash lsd-promotion-remove-label" disabled aria-disabled="true" aria-label="<?php esc_attr_e('Recurring labels must be managed from subscription details', 'listdom'); ?>">
                                                                <i class="lsd-fe-icon fa fa-trash-alt"></i>
                                                            </button>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if ($record['is_recurring'] && $record['manage_url']): ?>
                                                    <a class="lsd-text-button lsd-dashboard-promotions-manage-link" href="<?php echo esc_url($record['manage_url']); ?>"><?php esc_html_e('Manage Subscription', 'listdom'); ?></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php $dashboard->empty([
                        'title' => esc_html__('No active labels yet', 'listdom'),
                        'description' => esc_html__('Active labels will appear here after checkout is completed.', 'listdom'),
                        'image' => 'img/dashboard/no-listings.svg',
                        'quick_actions' => [],
                    ]); ?>
                <?php endif; ?>
            </div>

            <div class="lsd-fe-box-white lsd-dashboard-promotions-section lsd-dashboard-promotions-labelize-section lsd-dashboard-promotions-labelize-expired">
                <div class="lsd-dashboard-promotions-section-head lsd-fe-section-heading">
                    <h3 class="lsd-fe-title"><?php esc_html_e('Expired Added Labels', 'listdom'); ?></h3>
                </div>

                <div class="lsd-dashboard-promotions-search lsd-promotion-search-wrap" data-target=".lsd-promotion-expired-section" data-items=".lsd-promotion-expired-row">
                    <input type="search" class="lsd-fe-input-base lsd-promotion-search-input" placeholder="<?php esc_attr_e('Type to Search', 'listdom'); ?>">
                    <button type="button" class="lsd-light-button lsd-promotion-search-submit" data-target=".lsd-promotion-expired-section" data-items=".lsd-promotion-expired-row"><?php esc_html_e('Search', 'listdom'); ?></button>
                </div>

                <?php if (count($expired_records)): ?>
                    <div class="lsd-dashboard-promotions-table-wrap">
                        <table class="lsd-fe-table lsd-dashboard-promotions-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Label', 'listdom'); ?></th>
                                    <th><?php esc_html_e('Listing', 'listdom'); ?></th>
                                    <th><?php esc_html_e('Expired On', 'listdom'); ?></th>
                                    <th><?php esc_html_e('Action', 'listdom'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="lsd-promotion-expired-section">
                                <?php foreach ($expired_records as $record): ?>
                                    <tr class="lsd-promotion-expired-row" data-search="<?php echo esc_attr($record['search']); ?>">
                                        <td>
                                            <?php echo LSD_Kses::element($this->label_chip($record['label_name'], (int) $record['label_id'], [
                                                'class' => 'lsd-dashboard-promotions-chip lsd-dashboard-promotions-chip-static',
                                                'attributes' => array_filter([
                                                    'style' => $record['label_chip_style'],
                                                ], static function ($value)
                                                {
                                                    return $value !== '' && $value !== null;
                                                }),
                                            ])); ?>
                                        </td>
                                        <td><?php echo esc_html($record['listing_title']); ?></td>
                                        <td><?php echo esc_html($record['expired_on']); ?></td>
                                        <td>
                                            <?php if ($record['can_renew']): ?>
                                                <button type="button" class="lsd-light-button lsd-promotion-renew" data-label-id="<?php echo esc_attr($record['label_id']); ?>" data-listing-id="<?php echo esc_attr($record['listing_id']); ?>">
                                                    <?php esc_html_e('Renew', 'listdom'); ?>
                                                </button>
                                            <?php else: ?>
                                                <span class="lsd-tooltip lsd-tooltip-top" data-lsd-tooltip="<?php echo esc_attr($record['renewal_message']); ?>">
                                                <button type="button" class="lsd-light-button" disabled aria-disabled="true" aria-label="<?php echo esc_attr($record['renewal_message']); ?>" title="<?php echo esc_attr($record['renewal_message']); ?>">
                                                    <?php esc_html_e('Renew', 'listdom'); ?>
                                                </button>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <?php $dashboard->empty([
                        'title' => esc_html__('No expired labels yet', 'listdom'),
                        'description' => esc_html__('Expired labels will appear here after their active period ends.', 'listdom'),
                        'image' => 'img/dashboard/no-listings.svg',
                        'quick_actions' => [],
                    ]); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
