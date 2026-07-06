<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */

$user_id = get_current_user_id();
$orders = $this->get_orders($user_id);
$subscriptions = $this->get_subscriptions($user_id);
$order_detail_id = $this->get_order_detail_id($orders);
$order_detail_notice = $this->get_order_detail_notice($orders);
$order_detail = $order_detail_id ? LSD_Payments_Orders::get($order_detail_id) : null;
$activity_detail = $this->get_activity_detail($orders, $user_id, $subscriptions);
$detail = $order_detail instanceof LSD_Payments_Order ? $this->get_order_detail($order_detail) : $activity_detail;
if (!is_array($detail))
{
    if ($order_detail_notice !== '')
    {
        ?>
        <div class="lsd-dashboard-payments-order-detail">
            <div class="lsd-dashboard-payments-order-detail-back-section">
                <a class="lsd-dashboard-payments-order-detail-back" href="<?php echo esc_url($this->get_section_url($dashboard, 'orders')); ?>">
                    <i class="lsd-fe-icon fa fa-arrow-left"></i>
                    <?php esc_html_e('Back to Orders', 'listdom'); ?>
                </a>
            </div>
            <?php echo LSD_Base::alert($order_detail_notice, 'warning'); ?>
        </div>
        <?php
        return;
    }

    include lsd_template('dashboard/payments/billing.php');
    return;
}

$billing_details = isset($detail['billing_details']) && is_array($detail['billing_details']) ? $detail['billing_details'] : [];
$customer_name = trim((string) ($detail['customer_name'] ?? ''));
$customer_email = trim((string) ($detail['customer_email'] ?? ''));
$billing_phone = trim((string) ($billing_details['phone'] ?? ''));
$billing_company_name = trim((string) ($billing_details['company_name'] ?? ''));
$billing_tax_vat_id = trim((string) ($billing_details['tax_vat_id'] ?? ''));
$billing_address_parts = array_filter([
    trim((string) ($billing_details['city'] ?? '')),
    trim((string) ($billing_details['address'] ?? '')),
    trim((string) ($billing_details['postal_code'] ?? '')),
]);
?>
<div class="lsd-dashboard-payments-order-detail">
    <div class="lsd-dashboard-payments-order-detail-back-section">
        <a class="lsd-dashboard-payments-order-detail-back" href="<?php echo esc_url($this->get_section_url($dashboard, 'orders')); ?>">
            <i class="lsd-fe-icon fa fa-arrow-left"></i>
            <?php esc_html_e('Back to Orders', 'listdom'); ?>
        </a>
    </div>
    <div class="lsd-dashboard-payments-order-detail-head">
        <div class="lsd-dashboard-payments-order-detail-title-row">
            <div class="lsd-fe-title-icon">
                <i class="lsd-fe-icon fa-solid fa-cart-shopping"></i>
                <h3 class="lsd-fe-title"><?php echo esc_html($detail['title']); ?></h3>
            </div>
            <?php if (!empty($detail['order_status'])): ?>
                <span class="lsd-dashboard-payments-activity-status lsd-status-<?php echo esc_attr($detail['status_key']); ?> lsd-badge <?php echo esc_attr($this->get_status_badge_class($detail['status_key'])); ?>"><?php echo esc_html($detail['order_status']); ?></span>
            <?php endif; ?>
        </div>
        <div class="lsd-dashboard-payments-order-detail-actions">
            <?php if (!empty($detail['invoice_url'])): ?>
                <a class="lsd-fe-icon-button lsd-tooltip lsd-tooltip-top" href="<?php echo esc_url($detail['invoice_url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('View invoice', 'listdom'); ?>" data-lsd-tooltip="<?php esc_attr_e('View invoice', 'listdom'); ?>">
                    <i class="lsd-fe-icon fa-regular fa-file-alt"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($detail['email_url'])): ?>
                <a class="lsd-fe-icon-button lsd-tooltip lsd-tooltip-top" href="<?php echo esc_url($detail['email_url']); ?>" aria-label="<?php esc_attr_e('Email Invoice', 'listdom'); ?>" data-lsd-tooltip="<?php esc_attr_e('Email Invoice', 'listdom'); ?>">
                    <i class="lsd-fe-icon fa-solid fa-envelope"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="lsd-dashboard-payments-order-detail-meta lsd-fe-box-white">
        <div>
            <span class="lsd-dashboard-payments-stat-label"><?php esc_html_e('Order ID', 'listdom'); ?></span>
            <strong class="lsd-dashboard-payments-stat-value"><?php echo esc_html($detail['formatted_order_number'] ?: esc_html__('N/A', 'listdom')); ?></strong>
        </div>
        <div>
            <span class="lsd-dashboard-payments-stat-label"><?php esc_html_e('Payment Method', 'listdom'); ?></span>
            <strong class="lsd-dashboard-payments-stat-value"><?php echo esc_html($detail['gateway_name']); ?></strong>
        </div>
        <div>
            <span class="lsd-dashboard-payments-stat-label"><?php esc_html_e('Total Amount', 'listdom'); ?></span>
            <strong class="lsd-dashboard-payments-stat-value"><?php echo LSD_Kses::element($detail['total']); ?></strong>
        </div>
        <div>
            <span class="lsd-dashboard-payments-stat-label"><?php esc_html_e('Order Date', 'listdom'); ?></span>
            <strong class="lsd-dashboard-payments-stat-value"><?php echo esc_html($detail['order_datetime'] ?: esc_html__('N/A', 'listdom')); ?></strong>
        </div>
    </div>

    <div class="lsd-dashboard-payments-order-detail-grid">
        <div class="lsd-fe-box-white">
            <h3 class="lsd-fe-title"><?php esc_html_e('Customer Information', 'listdom'); ?></h3>
            <div class="lsd-dashboard-payments-order-detail-fields">
                <div class="lsd-dashboard-payments-order-detail-field">
                    <span class="lsd-dashboard-payments-order-detail-field-label"><?php esc_html_e('Name', 'listdom'); ?></span>
                    <strong class="lsd-dashboard-payments-order-detail-field-value"><?php echo esc_html($customer_name !== '' ? $customer_name : esc_html__('N/A', 'listdom')); ?></strong>
                </div>
                <div class="lsd-dashboard-payments-order-detail-field">
                    <span class="lsd-dashboard-payments-order-detail-field-label"><?php esc_html_e('Email', 'listdom'); ?></span>
                    <strong class="lsd-dashboard-payments-order-detail-field-value"><?php echo esc_html($customer_email !== '' ? $customer_email : esc_html__('N/A', 'listdom')); ?></strong>
                </div>
                <div class="lsd-dashboard-payments-order-detail-field">
                    <span class="lsd-dashboard-payments-order-detail-field-label"><?php esc_html_e('Phone', 'listdom'); ?></span>
                    <strong class="lsd-dashboard-payments-order-detail-field-value"><?php echo esc_html($billing_phone !== '' ? $billing_phone : esc_html__('N/A', 'listdom')); ?></strong>
                </div>
                <div class="lsd-dashboard-payments-order-detail-field">
                    <span class="lsd-dashboard-payments-order-detail-field-label"><?php esc_html_e('Company Name', 'listdom'); ?></span>
                    <strong class="lsd-dashboard-payments-order-detail-field-value"><?php echo esc_html($billing_company_name !== '' ? $billing_company_name : esc_html__('N/A', 'listdom')); ?></strong>
                </div>
                <div class="lsd-dashboard-payments-order-detail-field">
                    <span class="lsd-dashboard-payments-order-detail-field-label"><?php esc_html_e('Tax/VAT ID', 'listdom'); ?></span>
                    <strong class="lsd-dashboard-payments-order-detail-field-value"><?php echo esc_html($billing_tax_vat_id !== '' ? $billing_tax_vat_id : esc_html__('N/A', 'listdom')); ?></strong>
                </div>
            </div>
        </div>

        <div class="lsd-fe-box-white">
            <h3 class="lsd-fe-title"><?php esc_html_e('Billing Address', 'listdom'); ?></h3>
            <div class="lsd-dashboard-payments-order-billing-fields">
                <div class="lsd-dashboard-payments-order-billing-field">
                    <strong class="lsd-dashboard-payments-order-detail-field-value"><?php echo esc_html($customer_name !== '' ? $customer_name : esc_html__('N/A', 'listdom')); ?></strong>
                </div>
                <div class="lsd-dashboard-payments-order-billing-field">
                    <p class="lsd-dashboard-payments-order-detail-field-label lsd-m-0"><?php echo esc_html($billing_address_parts ? implode(' - ', $billing_address_parts) : esc_html__('N/A', 'listdom')); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="lsd-fe-box-white lsd-dashboard-payments-billing-order-details">
        <h3 class="lsd-fe-title"><?php esc_html_e('Items', 'listdom'); ?></h3>
        <table class="lsd-fe-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Items', 'listdom'); ?></th>
                    <th></th>
                    <th><?php esc_html_e('Quantity', 'listdom'); ?></th>
                    <th><?php esc_html_e('Price', 'listdom'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($detail['line_items'] ?? []) as $line_item): ?>
                    <tr>
                        <td>
                            <div class="lsd-dashboard-payments-order-detail-item">
                                <div class="lsd-dashboard-payments-order-title">
                                    <h3 class="lsd-fe-title lsd-dashboard-payments-order-detail-item-title"><?php echo esc_html($line_item['item']); ?></h3>
                                    <div class="lsd-dashboard-payments-order-detail-item-badges">
                                        <span class="lsd-dashboard-payments-activity-type lsd-badge lsd-dashboard-payments-activity-type-<?php echo esc_attr($line_item['type_key'] ?? 'order'); ?>"><?php echo esc_html($line_item['type']); ?></span>
                                        <?php if (!empty($line_item['is_recurring'])): ?>
                                            <?php echo LSD_Kses::element($line_item['recurring_badge'] ?? ''); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($line_item['billing_interval'])): ?>
                                    <span class="lsd-dashboard-payments-order-detail-item-tier"><?php echo esc_html($line_item['billing_interval']); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($line_item['manage_url'])): ?>
                                <a class="lsd-fe-icon-button lsd-tooltip lsd-tooltip-top" href="<?php echo esc_url($line_item['manage_url']); ?>" aria-label="<?php esc_attr_e('Manage subscription', 'listdom'); ?>" data-lsd-tooltip="<?php esc_attr_e('Manage subscription', 'listdom'); ?>">
                                    <i class="lsd-fe-icon fa fa-cog"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td class="lsd-text-center"><?php echo esc_html((string) ($line_item['quantity'] ?? 1)); ?></td>
                        <td><?php echo LSD_Kses::element($line_item['price']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td class="lsd-text-center"><?php esc_html_e('Subtotal', 'listdom'); ?></td>
                    <td class="lsd-value"><?php echo LSD_Kses::element($detail['subtotal']); ?></td>
                </tr>
                <?php if (!empty($detail['tax']) && (float) ($detail['tax_value'] ?? 0) > 0): ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="lsd-text-center"><?php esc_html_e('Tax', 'listdom'); ?></td>
                        <td class="lsd-value"><?php echo LSD_Kses::element($detail['tax']); ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (!empty($detail['discount']) && (float) ($detail['discount_value'] ?? 0) > 0): ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="lsd-text-center"><?php esc_html_e('Discount', 'listdom'); ?></td>
                        <td class="lsd-value"><?php echo LSD_Kses::element($detail['discount']); ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td class="lsd-text-center"><?php esc_html_e('Total', 'listdom'); ?></td>
                    <td><?php echo LSD_Kses::element($detail['total']); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
