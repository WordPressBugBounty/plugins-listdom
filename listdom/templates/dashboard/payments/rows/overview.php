<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */
/** @var array $items */
/** @var string $dashboard_url */

foreach ($items as $activity):
    if (!is_array($activity)) continue;

    $related_order = !empty($activity['order_id']) ? LSD_Payments_Orders::get((int) $activity['order_id']) : null;
    $activity_recurring = !empty($activity['recurring_id']) ? LSD_Payments_Recurrings::get((int) $activity['recurring_id']) : null;
    if (!$activity_recurring instanceof LSD_Payments_Recurring && $related_order instanceof LSD_Payments_Order)
    {
        $activity_recurring = $this->get_order_primary_recurring($related_order);
    }

    $recurring_badge = $activity_recurring instanceof LSD_Payments_Recurring ? $this->recurring_badge($activity_recurring) : '';
    ?>
    <tr>
        <td>
            <div class="lsd-dashboard-payments-activity-item">
                <div class="lsd-dashboard-payments-activity-content">
                    <div class="lsd-dashboard-payments-activity-title-section">
                        <span class="lsd-dashboard-payments-activity-title"><?php echo esc_html($activity['item'] ?? ''); ?></span>
                        <span class="lsd-dashboard-payments-activity-type lsd-badge lsd-<?php echo esc_attr($activity['type_key'] ?? 'order'); ?> lsd-dashboard-payments-activity-type-<?php echo esc_attr($activity['type_key'] ?? 'order'); ?>"><?php echo esc_html($activity['type'] ?? ''); ?></span>
                        <?php if (!empty($activity['is_recurring'])): ?>
                            <?php echo LSD_Kses::element($recurring_badge); ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($activity['date'])): ?>
                        <span class="lsd-dashboard-payments-activity-date"><?php echo esc_html($activity['date']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </td>
        <td class="lsd-dashboard-payments-activity-price"><?php echo LSD_Kses::element($activity['price'] ?? ''); ?></td>
        <td><span class="lsd-dashboard-payments-activity-status lsd-status-<?php echo esc_attr($activity['status_key'] ?? ''); ?> lsd-badge <?php echo esc_attr($this->get_status_badge_class($activity['status_key'] ?? '')); ?>"><?php echo esc_html($activity['status'] ?? ''); ?></span></td>
        <td>
            <a class="lsd-light-button" href="<?php echo esc_url($this->get_activity_detail_link($dashboard_url, $activity)); ?>">
                <?php esc_html_e('Details', 'listdom'); ?>
            </a>
        </td>
    </tr>
<?php endforeach; ?>
