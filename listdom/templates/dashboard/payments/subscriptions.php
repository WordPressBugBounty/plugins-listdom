<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */

$user_id = get_current_user_id();
$all_recurrings = $this->get_recurrings($user_id);
$recurrings = $this->get_filtered_subscription_recurrings($user_id);
$status_filter = $this->get_subscriptions_status_filter();
$search_filter = $this->get_subscriptions_search_filter();
$table_pagination = $this->get_table_load_more_args('subscriptions');
$page_data = $this->get_table_page_data('subscriptions', $user_id, 1);
$base_url = $dashboard->add_qs_vars([
    'mode' => LSD_Dashboard_Payments::MODE,
    LSD_Dashboard_Payments::SECTION_QUERY_VAR => 'subscriptions',
], $dashboard->url);
?>
<div class="lsd-fe-box-white lsd-dashboard-payments-billing-subscriptions">
    <h3 class="lsd-fe-title"><?php esc_html_e('Recurring Subscriptions', 'listdom'); ?></h3>

    <?php if (!$all_recurrings): ?>
        <?php $dashboard->empty([
            'title' => esc_html__('No recurring subscriptions yet', 'listdom'),
            'description' => esc_html__('Your recurring payments will appear here once a recurring plan or service is activated.', 'listdom'),
            'image' => 'img/dashboard/no-subscriptions.png',
        ]); ?>
    <?php else: ?>
        <div class="lsd-dashboard-payments-subscriptions-toolbar">
            <form class="lsd-dashboard-payments-subscriptions-search" action="<?php echo esc_url($dashboard->url); ?>" method="get">
                <input type="hidden" name="mode" value="<?php echo esc_attr(LSD_Dashboard_Payments::MODE); ?>">
                <input type="hidden" name="<?php echo esc_attr(LSD_Dashboard_Payments::SECTION_QUERY_VAR); ?>" value="subscriptions">
                <input type="hidden" name="<?php echo esc_attr(LSD_Dashboard_Payments::SUBSCRIPTIONS_STATUS_QUERY_VAR); ?>" value="<?php echo esc_attr($status_filter); ?>">
                <input type="search" name="<?php echo esc_attr(LSD_Dashboard_Payments::SUBSCRIPTIONS_SEARCH_QUERY_VAR); ?>" value="<?php echo esc_attr($search_filter); ?>" placeholder="<?php esc_attr_e('Type to Search', 'listdom'); ?>">
                <button type="submit" class="lsd-light-button"><?php esc_html_e('Search', 'listdom'); ?></button>
            </form>

            <div class="lsd-dashboard-payments-subscriptions-tabs lsd-fe-tabs">
                <ul class="lsd-fe-tabs-nav">
                    <li class="<?php echo $status_filter === 'all' ? 'lsd-active' : ''; ?>">
                        <a href="<?php echo esc_url($this->get_subscriptions_tab_url($dashboard, 'all')); ?>">
                            <?php echo esc_html__('All', 'listdom'); ?>
                        </a>
                    </li>
                    <li class="<?php echo $status_filter === 'active' ? 'lsd-active' : ''; ?>">
                        <a href="<?php echo esc_url($this->get_subscriptions_tab_url($dashboard, 'active')); ?>">
                            <?php echo esc_html__('Active', 'listdom'); ?>
                        </a>
                    </li>
                    <li class="<?php echo $status_filter === 'expiring' ? 'lsd-active' : ''; ?>">
                        <a href="<?php echo esc_url($this->get_subscriptions_tab_url($dashboard, 'expiring')); ?>">
                            <?php echo esc_html__('Expiring', 'listdom'); ?>
                        </a>
                    </li>
                    <li class="<?php echo $status_filter === 'expired' ? 'lsd-active' : ''; ?>">
                        <a href="<?php echo esc_url($this->get_subscriptions_tab_url($dashboard, 'expired')); ?>">
                            <?php echo esc_html__('Expired', 'listdom'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <?php if ($recurrings): ?>
            <table class="lsd-fe-table lsd-dashboard-load-more-table" data-load-more-context="subscriptions" data-load-more-next-page="<?php echo esc_attr($page_data['next_page']); ?>" data-load-more-has-more="<?php echo esc_attr($page_data['has_more'] ? 1 : 0); ?>" data-load-more-label="<?php echo esc_attr($table_pagination['button_label']); ?>" data-load-more-nonce="<?php echo esc_attr($table_pagination['nonce']); ?>" data-dashboard-url="<?php echo esc_url($dashboard->url); ?>" data-load-more-status="<?php echo esc_attr($status_filter); ?>" data-load-more-search="<?php echo esc_attr($search_filter); ?>">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Item', 'listdom'); ?></th>
                        <th><?php esc_html_e('Next Renewal', 'listdom'); ?></th>
                        <th><?php esc_html_e('Price', 'listdom'); ?></th>
                        <th><?php esc_html_e('Status', 'listdom'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo LSD_Kses::element($this->table_rows('subscriptions', $page_data['items'], $dashboard->url)); ?>
                </tbody>
            </table>
        <?php else: ?>
            <?php $dashboard->empty([
                'title' => esc_html__('No recurring subscriptions match your current filters.', 'listdom'),
                'description' => '',
                'quick_actions' => [],
                'image' => 'img/dashboard/no-subscriptions.png',
            ]); ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
