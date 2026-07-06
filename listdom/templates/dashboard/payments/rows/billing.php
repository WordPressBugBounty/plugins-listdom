<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */
/** @var array $items */

foreach ($items as $recurring):
    if (!$recurring instanceof LSD_Payments_Recurring) continue;
    ?>
    <tr>
        <td><?php echo esc_html($this->get_recurring_title($recurring)); ?></td>
        <td><?php echo esc_html($this->get_recurring_status_label($recurring)); ?></td>
        <td><?php echo esc_html($this->get_gateway_label($recurring->get_gateway()) ?: __('N/A', 'listdom')); ?></td>
        <td class="lsd-dashboard-payments-billing-price"><?php echo LSD_Kses::element($this->get_recurring_price_with_interval($recurring)); ?></td>
    </tr>
<?php endforeach; ?>
