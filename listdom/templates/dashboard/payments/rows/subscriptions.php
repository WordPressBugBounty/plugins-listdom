<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */
/** @var array $items */
/** @var string $dashboard_url */

foreach ($items as $recurring):
    if (!$recurring instanceof LSD_Payments_Recurring) continue;

    $manage_url = $this->get_recurring_manage_url($dashboard_url, $recurring);
    $type_key = $this->get_recurring_type_key($recurring);
    $recurring_badge = $this->recurring_badge($recurring);
    ?>
    <tr>
        <td>
            <div class="lsd-dashboard-payments-activity-item">
                <div class="lsd-dashboard-payments-activity-content">
                    <div class="lsd-dashboard-payments-activity-title-section">
                        <span class="lsd-dashboard-payments-activity-title"><?php echo esc_html($this->get_recurring_title($recurring)); ?></span>
                        <span class="lsd-dashboard-payments-activity-type lsd-badge lsd-<?php echo esc_attr($type_key); ?> lsd-dashboard-payments-activity-type-<?php echo esc_attr($type_key); ?>">
                            <?php echo esc_html($this->get_recurring_type_label($recurring)); ?>
                        </span>
                        <?php echo LSD_Kses::element($recurring_badge); ?>
                    </div>
                </div>
            </div>
        </td>
        <td><?php echo esc_html($this->get_recurring_next_renewal($recurring)); ?></td>
        <td class="lsd-dashboard-payments-subscription-price"><?php echo LSD_Kses::element($this->get_recurring_price_with_interval($recurring)); ?></td>
        <td>
            <span class="lsd-dashboard-payments-activity-status lsd-status-<?php echo esc_attr($this->get_recurring_status_key($recurring)); ?> lsd-badge <?php echo esc_attr($this->get_status_badge_class($this->get_recurring_status_key($recurring))); ?>">
                <?php echo esc_html($this->get_recurring_status_label($recurring)); ?>
            </span>
        </td>
        <td>
            <?php if ($manage_url): ?>
                <a class="lsd-text-button lsd-payment-billing-manage-button" href="<?php echo esc_url($manage_url); ?>">
                    <i class="lsd-fe-icon fa fa-cog"></i>
                    <?php esc_html_e('Manage', 'listdom'); ?>
                </a>
            <?php else: ?>
                <?php esc_html_e('N/A', 'listdom'); ?>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
