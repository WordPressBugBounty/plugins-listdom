<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $order_id */
/** @var string $date */
/** @var string $email */
/** @var string $total */
/** @var string $payment_method */
/** @var string $tax */
/** @var float $tax_value */
/** @var string $tax_label */
/** @var bool $tax_enabled */
/** @var array $tax_items */
/** @var bool $show_invoice_button */
/** @var string $invoice_url */
/** @var string $style */
?>
<div class="lsd-order-summary-wrapper">
    <div class="lsd-order-summary-icon">
        <i class="lsd-fe-icon fa fa-check-circle"></i>
        <h2 class="lsd-fe-title"><?php esc_html_e('Thanks for your order', 'listdom'); ?></h2>
    </div>

    <ul class="lsd-order-summary <?php echo $style === 'list' ? 'lsd-order-summary-list' : 'lsd-order-summary-inline'; ?>">
        <li>
            <span class="lsd-label"><?php esc_html_e('Order Number', 'listdom'); ?>:</span>
            <span class="lsd-value"><?php echo esc_html($order_id); ?></span>
        </li>
        <li>
            <span class="lsd-label"><?php esc_html_e('Date', 'listdom'); ?>:</span>
            <span class="lsd-value"><?php echo esc_html($date); ?></span>
        </li>
        <li>
            <span class="lsd-label"><?php esc_html_e('Email', 'listdom'); ?>:</span>
            <span class="lsd-value"><?php echo esc_html($email); ?></span>
        </li>
        <li>
            <span class="lsd-label"><?php esc_html_e('Payment Method', 'listdom'); ?>:</span>
            <span class="lsd-value"><?php echo esc_html($payment_method); ?></span>
        </li>
        <li class="<?php echo $style === 'list' ? '': 'lsd-util-hide'; ?>">
            <div class="lsd-fe-divider"></div>
        </li>
        <?php if ($tax_enabled || $tax_value > 0): ?>
            <?php if (!empty($tax_items)): ?>
                <?php foreach ($tax_items as $item): ?>
                    <?php
                    $item_label = isset($item['label']) && trim((string) $item['label']) !== '' ? $item['label'] : $tax_label;
                    $item_rate = isset($item['rate']) ? (float) $item['rate'] : 0.0;
                    if ($item_rate > 0) $item_label .= ' (' . number_format_i18n($item_rate, 2) . '%)';
                    $item_amount = isset($item['amount']) ? (float) $item['amount'] : 0.0;
                    $item_amount_html = LSD_Kses::element($this->render_price($item_amount, $currency, false, false));
                    ?>
                    <li>
                        <span class="lsd-label"><?php echo esc_html($item_label); ?>:</span>
                        <span class="lsd-value"><?php echo $item_amount_html; ?></span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>
                    <span class="lsd-label"><?php echo esc_html($tax_label); ?>:</span>
                    <span class="lsd-value"><?php echo LSD_Kses::element($tax); ?></span>
                </li>
            <?php endif; ?>
        <?php endif; ?>
        <li>
            <span class="lsd-label"><?php esc_html_e('Total', 'listdom'); ?>:</span>
            <span class="lsd-value"><?php echo LSD_Kses::element($total); ?></span>
        </li>
    </ul>

    <?php if ($show_invoice_button && $invoice_url): ?>
        <a class="lsd-light-button" href="<?php echo esc_url($invoice_url); ?>" target="_blank" rel="noopener noreferrer">
            <i class="lsd-fe-icon fa fa-file-pdf"></i>
            <?php esc_html_e('Download Invoice', 'listdom'); ?>
        </a>
    <?php endif; ?>
</div>
