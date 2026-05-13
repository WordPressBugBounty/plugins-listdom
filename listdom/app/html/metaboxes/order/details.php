<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$order = new LSD_Payments_Order($post);

$subtotal = $order->get_subtotal();
$discount = $order->get_discount();
$tax = $order->get_tax();
$tax_items = $order->get_tax_items();
$total = $order->get_total();
$gateway_key = $order->get_gateway();
$coupon = $order->get_coupon();
$message = $order->get_message();
$gateway = LSD_Payments::gateway($gateway_key);
$recurring = $order->get_recurring();
$payment_number = $order->get_recurring_number();

$tax_helper = new LSD_Payments_Tax();
$tax_location = $order->get_tax_location();

$parts = [];
if ($name = $order->get_name()) $parts[] = esc_html($name);
if ($email = $order->get_email()) $parts[] = esc_html($email);
?>
<div class="lsd-metabox lsd-order-metabox">
    <table class="widefat fixed striped lsd-order-table">
        <tbody>
            <tr><th><?php esc_html_e('Subtotal', 'listdom'); ?></th><td><?php echo $this->render_price($subtotal, LSD_Options::currency(), false, false); ?></td></tr>
            <tr><th><?php esc_html_e('Discount', 'listdom'); ?></th><td><?php echo $this->render_price($discount, LSD_Options::currency(), false, false); ?></td></tr>
            <?php if ($tax_helper->enabled() || $tax > 0): ?>
                <?php if (count($tax_items)): ?>
                    <?php foreach ($tax_items as $tax_item): ?>
                        <?php
                        $item_label = isset($tax_item['label']) && trim((string) $tax_item['label']) !== '' ? $tax_item['label'] : $tax_helper->label();
                        $item_rate = isset($tax_item['rate']) ? (float) $tax_item['rate'] : 0.0;
                        if ($item_rate > 0) $item_label .= ' (' . number_format_i18n($item_rate, 2) . '%)';
                        $item_amount = isset($tax_item['amount']) ? (float) $tax_item['amount'] : 0.0;
                        ?>
                        <tr>
                            <th><?php echo esc_html($item_label); ?></th>
                            <td><?php echo $this->render_price($item_amount, LSD_Options::currency(), false, false); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <th><?php echo esc_html($tax_helper->label()); ?></th>
                        <td><?php echo $this->render_price($tax, LSD_Options::currency(), false, false); ?></td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>
            <tr><th><?php esc_html_e('Total', 'listdom'); ?></th><td><?php echo $this->render_price($total, LSD_Options::currency(), false, false); ?></td></tr>
            <tr><th><?php esc_html_e('Gateway', 'listdom'); ?></th><td><?php echo esc_html($gateway ? $gateway->name() : $gateway_key); ?></td></tr>

            <?php if ($recurring): ?>
            <tr>
                <th><?php esc_html_e('Recurring Payment', 'listdom'); ?></th>
                <td>
                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $recurring->get_id() . '&action=edit')); ?>">
                        #<?php echo esc_html($recurring->get_id()); ?>
                    </a>
                </td>
            </tr>
            <?php endif; ?>

            <?php if ($payment_number): ?>
            <tr>
                <th><?php esc_html_e('Payment Number', 'listdom'); ?></th>
                <td><?php echo esc_html($payment_number); ?></td>
            </tr>
            <?php endif; ?>

            <?php if ($coupon): ?>
            <tr><th><?php esc_html_e('Coupon', 'listdom'); ?></th><td><?php echo esc_html($coupon); ?></td></tr>
            <?php endif; ?>

            <?php if ($parts): ?>
            <tr><th><?php esc_html_e('Customer', 'listdom'); ?></th><td><?php echo implode(' - ', $parts); ?></td></tr>
            <?php endif; ?>

            <?php if ($message): ?>
            <tr><th><?php esc_html_e('Comment', 'listdom'); ?></th><td><?php echo esc_html($message); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
