<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Payments_Order $order */
/** @var WP_Post $order_post */
/** @var string $order_status */
/** @var string $order_datetime */
/** @var string $customer_name */
/** @var string $customer_email */
/** @var string $customer_message */
/** @var array $items */
/** @var array $fees */
/** @var string $currency */
/** @var string $subtotal */
/** @var string $discount */
/** @var float $discount_value */
/** @var string $tax */
/** @var float $tax_value */
/** @var string $total */
/** @var string $gateway_name */
/** @var int $order_id */
/** @var string $invoice_logo */
/** @var string $invoice_from */
/** @var string $invoice_footer */
/** @var string $tax_label */
/** @var bool $tax_enabled */
/** @var array $tax_items */

$invoice_from_lines = array_filter(array_map('trim', explode("\n", $invoice_from)));
$invoice_footer_content = trim($invoice_footer) === '' ? '' : $invoice_footer;
?>
<div class="lsd-payments-invoice-wrapper">
    <div class="lsd-print-hide">
        <button class="lsd-general-button lsd-print-button">
            <i class="lsd-fe-icon fa-solid fa-print"></i>
            <?php esc_html_e('Print', 'listdom'); ?>
        </button>
    </div>
    <div class="lsd-print-main-content lsd-fe-sections">

        <div class="lsd-fe-box-white lsd-payments-invoice-top">
            <div class="lsd-payments-invoice-logo">
                <?php if (trim($invoice_logo)): ?>
                    <?php echo LSD_Kses::element($invoice_logo); ?>
                <?php else: ?>
                    <img src="<?php echo esc_url($this->lsd_asset_url('img/logo-placeholder.png')); ?>" alt="<?php esc_attr_e('Invoice Logo', 'listdom'); ?>">
                <?php endif; ?>
            </div>
            <div class="lsd-payments-invoice-details lsd-fe-subsections">
                <?php if (trim($order_status)): ?>
                    <div class="lsd-payments-invoice-status lsd-fe-description">
                        <?php echo sprintf(esc_html__('Status: %s', 'listdom'), '<span class="lsd-value">' . esc_html($order_status) . '</span>'); ?>
                    </div>
                <?php endif; ?>
                <div class="lsd-payments-invoice-number lsd-fe-description">
                    <?php echo sprintf(esc_html__('Invoice No: %s', 'listdom'), '<span class="lsd-value">#' . esc_html($order_id) . '</span>'); ?>
                </div>
                <div class="lsd-payments-invoice-date lsd-fe-description">
                    <?php echo sprintf(esc_html__('Date Issued: %s', 'listdom'), '<span class="lsd-value">' . esc_html($order_datetime) . '</span>'); ?>
                </div>
            </div>
        </div>

        <div class="lsd-payments-invoice-billing">
            <div class="lsd-fe-box-white lsd-payments-invoice-bill-from">
                <div class="lsd-fe-title-icon">
                    <i class="lsd-fe-icon fa-solid fa-arrow-right-from-file"></i>
                    <h3 class="lsd-fe-title"><?php esc_html_e('Invoice From', 'listdom'); ?></h3>
                </div>
                <?php if ($invoice_from_lines): ?>
                    <ul>
                        <?php foreach ($invoice_from_lines as $index => $line): ?>
                            <?php $line_class = $index === 0 ? 'lsd-payments-invoice-bill-name' : 'lsd-payments-invoice-bill-detail'; ?>
                            <li class="<?php echo esc_attr($line_class); ?> lsd-fe-description"><?php echo esc_html($line); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><?php esc_html_e('Business details are not available.', 'listdom'); ?></p>
                <?php endif; ?>
            </div>
            <div class="lsd-fe-box-white lsd-payments-invoice-bill">
                <div class="lsd-fe-title-icon">
                    <i class="lsd-fe-icon fa-solid fa-chalkboard-user"></i>
                    <h3 class="lsd-fe-title"><?php esc_html_e('Bill To', 'listdom'); ?></h3>
                </div>
                <?php if (trim($customer_name) || trim($customer_email)): ?>
                    <ul>
                        <?php if (trim($customer_name)): ?>
                            <li class="lsd-payments-invoice-bill-name lsd-fe-description"><?php echo esc_html($customer_name); ?></li>
                        <?php endif; ?>
                        <?php if (trim($customer_email)): ?>
                            <li class="lsd-payments-invoice-bill-email lsd-fe-description"><?php echo esc_html($customer_email); ?></li>
                        <?php endif; ?>
                    </ul>
                <?php else: ?>
                    <p><?php esc_html_e('Customer details are not available.', 'listdom'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (trim($customer_message)): ?>
        <div class="lsd-payments-invoice-message">
            <h3><?php esc_html_e('Customer Message', 'listdom'); ?></h3>
            <p><?php echo nl2br(esc_html($customer_message)); ?></p>
        </div>
        <?php endif; ?>

        <div class="lsd-fe-box-white lsd-payments-invoice-order-items">
            <div class="lsd-fe-title-icon">
                <i class="lsd-fe-icon fa fa-shopping-cart"></i>
                <h2 class="lsd-fe-title"><?php esc_html_e('Order Items', 'listdom'); ?></h2>
            </div>
            <?php if ($items || $fees): ?>
                <table class="lsd-fe-table lsd-payments-invoice-items">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Item', 'listdom'); ?></th>
                            <th><?php esc_html_e('Details', 'listdom'); ?></th>
                            <th><?php esc_html_e('Price', 'listdom'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <?php
                        $plan_id = (int) ($item['plan_id'] ?? 0);
                        $tier_id = $item['tier_id'] ?? '';

                        $plan_title = $plan_id ? get_the_title($plan_id) : '';
                        $plan = $plan_id ? new LSD_Payments_Plan($plan_id, $tier_id) : null;
                        $tier = $plan ? $plan->get_tier() : null;
                        $tier_name = $tier ? $tier->get_name() : '';
                        $price_html = $plan ? $plan->get_price_html() : '';

                        $meta_lines = $order->get_metas($item);
                        ?>
                        <tr>
                            <td>
                                <?php echo $plan_title ? esc_html($plan_title) : esc_html__('Plan', 'listdom'); ?>
                                <?php if ($tier_name): ?>
                                    <span class="lsd-payments-invoice-tier">- <?php echo esc_html($tier_name); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($meta_lines): ?>
                                    <?php foreach ($meta_lines as $meta_line): ?>
                                        <div class="lsd-payments-invoice-meta"><?php echo LSD_Kses::element($meta_line); ?></div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="lsd-payments-invoice-meta">
                                        <?php esc_html_e('No additional details.', 'listdom'); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($price_html): ?>
                                    <div class="lsd-payments-invoice-price"><?php echo LSD_Kses::element($price_html); ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php foreach ($fees as $fee): ?>
                        <?php
                        $fee_title = isset($fee['title']) ? trim((string) $fee['title']) : '';
                        $fee_amount = isset($fee['amount']) ? (float) $fee['amount'] : 0;
                        $fee_price = $this->render_price($fee_amount, $currency, false, false);
                        ?>
                        <tr class="lsd-payments-invoice-fee">
                            <td>
                                <?php echo $fee_title !== '' ? esc_html($fee_title) : esc_html__('Fee', 'listdom'); ?>
                            </td>
                            <td>
                                <div class="lsd-payments-invoice-meta">&mdash;</div>
                            </td>
                            <td>
                                <div class="lsd-payments-invoice-price"><?php echo LSD_Kses::element($fee_price); ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td class="lsd-text-right"><?php esc_html_e('Subtotal', 'listdom'); ?></td>
                            <td class="lsd-value"><?php echo LSD_Kses::element($subtotal); ?></td>
                        </tr>
                        <?php if ($discount_value > 0): ?>
                            <tr class="lsd-payments-invoice-discount">
                                <td></td>
                                <td class="lsd-text-right"><?php esc_html_e('Discount', 'listdom'); ?></td>
                                <td class="lsd-value">-<?php echo LSD_Kses::element($discount); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($tax_enabled || $tax_value > 0): ?>
                            <?php if (!empty($tax_items)): ?>
                                <?php foreach ($tax_items as $item): ?>
                                    <?php
                                    $item_label = isset($item['label']) && trim((string) $item['label']) !== '' ? $item['label'] : $tax_label;
                                    $item_rate = isset($item['rate']) ? (float) $item['rate'] : 0.0;
                                    if ($item_rate > 0) $item_label .= ' (' . number_format_i18n($item_rate, 2) . '%)';
                                    $item_amount = isset($item['amount']) ? (float) $item['amount'] : 0.0;
                                    $item_amount_html = $this->render_price($item_amount, $currency, false, false);
                                    ?>
                                    <tr class="lsd-payments-invoice-tax">
                                        <td></td>
                                        <td class="lsd-text-right"><?php echo esc_html($item_label); ?></td>
                                        <td class="lsd-value"><?php echo LSD_Kses::element($item_amount_html); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="lsd-payments-invoice-tax">
                                    <td></td>
                                    <td class="lsd-text-right"><?php echo esc_html($tax_label); ?></td>
                                    <td class="lsd-value"><?php echo LSD_Kses::element($tax); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                        <tr>
                            <td></td>
                            <td class="lsd-text-right"><?php esc_html_e('Total', 'listdom'); ?></td>
                            <td class="lsd-value"><?php echo LSD_Kses::element($total); ?></td>
                        </tr>
                        <?php if (trim($gateway_name)): ?>
                            <tr>
                                <td></td>
                                <td class="lsd-text-right"><?php esc_html_e('Payment Method', 'listdom'); ?></td>
                                <td class="lsd-value"><?php echo esc_html($gateway_name); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tfoot>
                </table>
            <?php else: ?>
                <p><?php esc_html_e('No items were found in this order.', 'listdom'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="lsd-payments-invoice-additional-info lsd-fe-box-white">
        <div class="lsd-fe-title-icon">
            <i class="lsd-fe-icon fa-solid fa-info-circle"></i>
            <h3 class="lsd-fe-title"><?php esc_html_e('Additional Information', 'listdom'); ?></h3>
        </div>
        <?php if ($invoice_footer_content !== ''): ?>
            <div class="lsd-payments-invoice-footer-content">
                <?php echo LSD_Kses::rich($invoice_footer_content); ?>
            </div>
        <?php else: ?>
            <p class="lsd-fe-description"><?php esc_html_e('No additional information provided.', 'listdom'); ?></p>
        <?php endif; ?>
    </div>
    <div class="lsd-print-hide">
        <button class="lsd-general-button lsd-print-button">
            <i class="lsd-fe-icon fa-solid fa-print"></i>
            <?php esc_html_e('Print', 'listdom'); ?>
        </button>
    </div>

</div>
