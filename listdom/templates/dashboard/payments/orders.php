<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */

$user_id = get_current_user_id();
$orders = $this->get_filtered_orders($user_id);
$all_orders = $this->get_orders($user_id);
$status_filter = $this->get_orders_status_filter();
$search_filter = $this->get_orders_search_filter();
$table_pagination = $this->get_table_load_more_args('orders');
$page_data = $this->get_table_page_data('orders', $user_id, 1);
$base_url = $dashboard->add_qs_vars([
    'mode' => LSD_Dashboard_Payments::MODE,
    LSD_Dashboard_Payments::SECTION_QUERY_VAR => 'orders',
], $dashboard->url);
?>
<div class="lsd-fe-box-white lsd-dashboard-payments-billing-order">
    <h3 class="lsd-fe-title"><?php esc_html_e('Orders Summary', 'listdom'); ?></h3>

    <?php if ($all_orders): ?>
        <div class="lsd-dashboard-payments-subscriptions-toolbar">
            <form class="lsd-dashboard-payments-subscriptions-search" action="<?php echo esc_url($dashboard->url); ?>" method="get">
                <input type="hidden" name="mode" value="<?php echo esc_attr(LSD_Dashboard_Payments::MODE); ?>">
                <input type="hidden" name="<?php echo esc_attr(LSD_Dashboard_Payments::SECTION_QUERY_VAR); ?>" value="orders">
                <input type="hidden" name="<?php echo esc_attr(LSD_Dashboard_Payments::ORDERS_STATUS_QUERY_VAR); ?>" value="<?php echo esc_attr($status_filter); ?>">
                <input type="search" name="<?php echo esc_attr(LSD_Dashboard_Payments::ORDERS_SEARCH_QUERY_VAR); ?>" value="<?php echo esc_attr($search_filter); ?>" placeholder="<?php esc_attr_e('Type to Search', 'listdom'); ?>">
                <button type="submit" class="lsd-light-button"><?php esc_html_e('Search', 'listdom'); ?></button>
            </form>

            <div class="lsd-dashboard-payments-subscriptions-tabs lsd-fe-tabs">
                <ul class="lsd-fe-tabs-nav">
                    <li class="<?php echo $status_filter === 'all' ? 'lsd-active' : ''; ?>">
                        <a href="<?php echo esc_url($this->get_orders_tab_url($dashboard, 'all')); ?>">
                            <?php echo esc_html__('All', 'listdom'); ?>
                        </a>
                    </li>
                    <li class="<?php echo $status_filter === 'successful' ? 'lsd-active' : ''; ?>">
                        <a href="<?php echo esc_url($this->get_orders_tab_url($dashboard, 'successful')); ?>">
                            <?php echo esc_html__('Successful', 'listdom'); ?>
                        </a>
                    </li>
                    <li class="<?php echo $status_filter === 'pending' ? 'lsd-active' : ''; ?>">
                        <a href="<?php echo esc_url($this->get_orders_tab_url($dashboard, 'pending')); ?>">
                            <?php echo esc_html__('Pending', 'listdom'); ?>
                        </a>
                    </li>
                    <li class="<?php echo $status_filter === 'failed' ? 'lsd-active' : ''; ?>">
                        <a href="<?php echo esc_url($this->get_orders_tab_url($dashboard, 'failed')); ?>">
                            <?php echo esc_html__('Failed', 'listdom'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <?php if ($orders): ?>
            <table class="lsd-fe-table lsd-dashboard-load-more-table" data-load-more-context="orders" data-load-more-next-page="<?php echo esc_attr($page_data['next_page']); ?>" data-load-more-has-more="<?php echo esc_attr($page_data['has_more'] ? 1 : 0); ?>" data-load-more-label="<?php echo esc_attr($table_pagination['button_label']); ?>" data-load-more-nonce="<?php echo esc_attr($table_pagination['nonce']); ?>" data-dashboard-url="<?php echo esc_url($dashboard->url); ?>" data-load-more-status="<?php echo esc_attr($status_filter); ?>" data-load-more-search="<?php echo esc_attr($search_filter); ?>">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Order ID', 'listdom'); ?></th>
                        <th><?php esc_html_e('Details', 'listdom'); ?></th>
                        <th><?php esc_html_e('Amount', 'listdom'); ?></th>
                        <th><?php esc_html_e('Status', 'listdom'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo LSD_Kses::element($this->table_rows('orders', $page_data['items'], $dashboard->url)); ?>
                </tbody>
            </table>
        <?php else: ?>
            <?php $dashboard->empty([
                'title' => esc_html__('No orders match your current filters.', 'listdom'),
                'description' => '',
                'quick_actions' => [],
                'image' => 'img/dashboard/no-orders.png',
            ]); ?>
        <?php endif; ?>
    <?php else: ?>
        <?php $dashboard->empty([
            'title' => esc_html__('No orders yet', 'listdom'),
            'description' => esc_html__('Your completed purchases, renewals, claims, promotions, and membership payments will appear here.', 'listdom'),
            'quick_actions' => $this->get_quick_actions($dashboard, $user_id),
            'image' => 'img/dashboard/no-orders.png',
        ]); ?>
    <?php endif; ?>
</div>
