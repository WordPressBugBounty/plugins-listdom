<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */
/** @var array $items */
/** @var string $dashboard_url */

$currency = LSD_Options::currency();

foreach ($items as $order):
    if (!$order instanceof LSD_Payments_Order) continue;

    $order_post = get_post($order->get_id());
    $status_key = $order_post instanceof WP_Post ? $this->get_order_status_group($order_post->post_status) : 'pending';
    $date = $order_post instanceof WP_Post ? $this->format_activity_date($this->get_activity_timestamp($order_post->post_date_gmt, $order_post->post_date)) : '';
    $detail_url = $this->get_order_detail_link($dashboard_url, $order->get_id());
    $invoice_url = $order->get_invoice_url();
    $line_items = $this->get_order_table_items($order);
    ?>
    <tr>
        <td>
            <div class="lsd-dashboard-payments-order-id">
                <span class="lsd-dashboard-payments-order-number"><?php echo esc_html($this->format_order_number($order->get_id())); ?></span>
                <?php if ($date !== ''): ?>
                    <span class="lsd-dashboard-payments-order-date"><?php echo esc_html($date); ?></span>
                <?php endif; ?>
            </div>
        </td>

        <td>
            <div class="lsd-dashboard-payments-order-lines">
                <?php foreach ($line_items as $index => $line_item): ?>
                    <?php
                    $line_recurring = null;
                    $line_recurring_id = isset($line_item['recurring_id']) ? (int) $line_item['recurring_id'] : 0;

                    if ($line_recurring_id > 0)
                    {
                        $line_recurring = LSD_Payments_Recurrings::get($line_recurring_id);
                    }

                    if (!$line_recurring instanceof LSD_Payments_Recurring && !empty($line_item['is_recurring']) && $order->get_recurring() instanceof LSD_Payments_Recurring)
                    {
                        // Fallback for renewal orders.
                        $line_recurring = $order->get_recurring();
                    }

                    $recurring_badge = $line_recurring instanceof LSD_Payments_Recurring ? $this->recurring_badge($line_recurring) : '';
                    ?>

                    <div class="lsd-dashboard-payments-order-line<?php echo $index < (count($line_items) - 1) ? ' has-border' : ''; ?>">
                        <span class="lsd-dashboard-payments-order-line-title"><?php echo esc_html($line_item['title'] ?? ''); ?></span>

                        <div class="lsd-dashboard-payments-order-line-meta">
                            <span class="lsd-dashboard-payments-activity-type lsd-badge lsd-dashboard-payments-activity-type-<?php echo esc_attr($line_item['type_key'] ?? 'order'); ?>">
                                <?php echo esc_html($line_item['type'] ?? ''); ?>
                            </span>

                            <?php if (!empty($line_item['is_recurring']) && $recurring_badge !== ''): ?>
                                <?php echo LSD_Kses::element($recurring_badge); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </td>

        <td class="lsd-dashboard-payments-order-price">
            <?php echo LSD_Kses::element($this->get_order_price_with_interval($order)); ?>
        </td>

        <td>
            <span class="lsd-dashboard-payments-activity-status lsd-status-<?php echo esc_attr($status_key); ?> lsd-badge <?php echo esc_attr($this->get_status_badge_class($status_key)); ?>">
                <?php echo $order_post instanceof WP_Post ? esc_html($this->get_order_status_label($order_post->post_status)) : ''; ?>
            </span>
        </td>

        <td>
            <div class="lsd-dashboard-payments-order-actions">
                <a class="lsd-fe-icon-button lsd-tooltip lsd-tooltip-top" href="<?php echo esc_url($detail_url); ?>" aria-label="<?php esc_attr_e('View Details', 'listdom'); ?>" data-lsd-tooltip="<?php esc_attr_e('View Details', 'listdom'); ?>">
                    <i class="lsd-fe-icon fa fa-eye"></i>
                </a>

                <?php if ($invoice_url): ?>
                    <a class="lsd-fe-icon-button lsd-tooltip lsd-tooltip-top" href="<?php echo esc_url($invoice_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('View Invoice', 'listdom'); ?>" data-lsd-tooltip="<?php esc_attr_e('View Invoice', 'listdom'); ?>">
                        <i class="lsd-fe-icon fa-regular fa-file-alt"></i>
                    </a>
                <?php endif; ?>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
