<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */

$user_id = get_current_user_id();
$orders = $this->get_orders($user_id);
$recurrings = $this->get_recurrings($user_id);
$subscriptions = $this->get_subscriptions($user_id);
$active_recurrings = $this->get_active_recurrings($recurrings);
$active_recurring_count = count($active_recurrings);
$membership_capacity = $this->get_membership_capacity($subscriptions);
$quick_actions = $this->get_quick_actions($dashboard, $user_id);
$active_recurring_labels = $this->get_active_recurring_labels($recurrings);
$get_package_url = $quick_actions[0]['url'] ?? '';
$recent_activity = $this->get_recent_activity($orders, $user_id, $subscriptions);
$table_pagination = $this->get_table_load_more_args('overview');
$page_data = $this->get_table_page_data('overview', $user_id, 1);
$autorenew_groups = $this->get_overview_autorenew_groups($recurrings, $dashboard->url);
?>
<div class="lsd-dashboard-payments-billing-overview lsd-fe-sections">
    <div class="lsd-dashboard-payments-overview-alerts">
        <?php foreach ($autorenew_groups as $group): ?>
            <?php if (empty($group['items'])) continue; ?>
            <div class="lsd-alert lsd-<?php echo esc_attr($group['type']); ?>">
                <?php if (!empty($group['icon'])): ?>
                    <i class="<?php echo esc_attr($group['icon']); ?>"></i>
                <?php endif; ?>
                <div>
                    <div class="lsd-dashboard-payments-overview-alert-head">
                        <strong><?php echo esc_html($group['title']); ?></strong>
                        <p><?php echo esc_html($group['message']); ?></p>
                    </div>
                    <ul class="lsd-dashboard-payments-overview-alert-list">
                        <?php foreach ($group['items'] as $item): ?>
                            <li>
                                <span class="lsd-dashboard-payments-overview-alert-item-title"><?php echo esc_html($item['title']); ?></span>
                                <?php if (!empty($item['date']) && $item['date'] !== esc_html__('N/A', 'listdom')): ?>
                                    <i class="lsd-separator-dot">&bull;</i>
                                    <span class="lsd-dashboard-payments-overview-alert-item-date"><?php echo esc_html($item['date']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($item['action_label']) && !empty($item['action_url'])): ?>
                                    <i class="lsd-separator-dot">&bull;</i>
                                    <a class="lsd-dashboard-payments-overview-alert-action" href="<?php echo esc_url($item['action_url']); ?>">
                                        <?php echo esc_html($item['action_label']); ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="lsd-dashboard-payments-overview">
        <div class="lsd-dashboard-payments-summary">
            <div class="lsd-fe-box-white lsd-dashboard-payments-stat-card">
                <div class="lsd-fe-icon-box">
                    <i class="lsd-fe-icon fa fa-refresh"></i>
                </div>
                <div class="lsd-dashboard-payments-stat-content">
                    <span class="lsd-dashboard-payments-stat-label"><?php esc_html_e('Active Subscriptions', 'listdom'); ?></span>
                    <span class="lsd-dashboard-payments-stat-value">
                        <?php echo esc_html(sprintf(_n('%d Active', '%d Active', $active_recurring_count, 'listdom'), $active_recurring_count)); ?>
                    </span>
                </div>
            </div>

            <div class="lsd-fe-box-white lsd-dashboard-payments-stat-card">
                <div class="lsd-fe-icon-box">
                    <i class="lsd-fe-icon fa-solid fa-battery-4"></i>
                </div>
                <div class="lsd-dashboard-payments-stat-content">
                    <span class="lsd-dashboard-payments-stat-label"><?php esc_html_e('Packages Remaining Capacity', 'listdom'); ?></span>
                    <span class="lsd-dashboard-payments-stat-value">
                        <?php if (!empty($membership_capacity['label'])): ?>
                            <?php echo esc_html($membership_capacity['label']); ?>
                        <?php elseif ($get_package_url): ?>
                            <a href="<?php echo esc_url($get_package_url); ?>"><?php esc_html_e('Get a Package', 'listdom'); ?></a>
                        <?php else: ?>
                            <?php esc_html_e('Get a Package', 'listdom'); ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="lsd-dashboard-payments-actions">
            <div class="lsd-fe-box-white lsd-dashboard-payments-quick-actions">
                <h2 class="lsd-fe-title"><?php esc_html_e('Quick Actions', 'listdom'); ?></h2>
                <div class="lsd-dashboard-payments-actions-grid">
                    <?php foreach ($quick_actions as $action): ?>
                        <a class="lsd-light-button" href="<?php echo esc_url($action['url']); ?>">
                            <?php echo esc_html($action['label']); ?>
                            <i class="lsd-fe-icon wbli-right-arrow"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="lsd-fe-box-white lsd-dashboard-payments-activity">
        <h2 class="lsd-fe-title"><?php esc_html_e('Recent Activity', 'listdom'); ?></h2>
        <?php if ($recent_activity): ?>
            <table class="lsd-fe-table lsd-dashboard-load-more-table" data-load-more-context="overview" data-load-more-next-page="<?php echo esc_attr($page_data['next_page']); ?>" data-load-more-has-more="<?php echo esc_attr($page_data['has_more'] ? 1 : 0); ?>" data-load-more-label="<?php echo esc_attr($table_pagination['button_label']); ?>" data-load-more-nonce="<?php echo esc_attr($table_pagination['nonce']); ?>" data-dashboard-url="<?php echo esc_url($dashboard->url); ?>">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Item', 'listdom'); ?></th>
                        <th><?php esc_html_e('Price', 'listdom'); ?></th>
                        <th><?php esc_html_e('Status', 'listdom'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo LSD_Kses::element($this->table_rows('overview', $page_data['items'], $dashboard->url)); ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="lsd-fe-description"><?php esc_html_e('No payment activity found yet.', 'listdom'); ?></p>
        <?php endif; ?>
    </div>
</div>
