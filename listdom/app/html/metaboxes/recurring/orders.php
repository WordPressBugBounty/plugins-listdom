<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$recurring = new LSD_Payments_Recurring($post);
$orders = $recurring->get_orders();
$currency = $recurring->get_currency();
?>
<div class="lsd-metabox lsd-order-metabox lsd-p-3">
    <?php if (count($orders)): ?>
    <div class="lsd-order-items lsd-flex lsd-flex-col lsd-gap-4">
        <?php foreach ($orders as $order):
            $order_id = $order->get_id();
            $order_link = admin_url('post.php?post=' . $order_id . '&action=edit');
            $status = get_post_status($order_id);
            $status_object = $status ? get_post_status_object($status) : null;
            $status_label = $status_object && isset($status_object->label) ? $status_object->label : ($status ?: '');
            $timestamp = get_post_time('U', true, $order_id);
            $date_display = $timestamp ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp) : '';
            $meta_lines = array_map('wp_kses_post', $order->get_lines());
            $payment_number = $order->get_recurring_number();

            $info_parts = [];
            if ($status_label !== '') $info_parts[] = esc_html__('Status', 'listdom') . ': ' . esc_html($status_label);
            if ($date_display !== '') $info_parts[] = esc_html__('Date', 'listdom') . ': ' . esc_html($date_display);
            if ($payment_number) $info_parts[] = sprintf(esc_html__('Payment #%d', 'listdom'), $payment_number);
        ?>
        <div class="lsd-order-item lsd-box lsd-flex lsd-flex-col lsd-flex-items-start lsd-flex-items-stretch lsd-gap-4">
            <div class="lsd-flex lsd-flex-row lsd-flex-items-start lsd-flex-space-between lsd-flex-wrap">
                <div>
                    <h3 class="lsd-admin-title lsd-my-0">
                        <a href="<?php echo esc_url($order_link); ?>">#<?php echo esc_html($order_id); ?></a>
                    </h3>
                    <?php if ($info_parts): ?>
                    <p class="lsd-order-item-tier lsd-my-0"><?php echo implode(' | ', $info_parts); ?></p>
                    <?php endif; ?>
                </div>
                <div class="lsd-order-item-total"><?php echo $this->render_price($order->get_total(), $currency, false, false); ?></div>
            </div>

            <?php if ($meta_lines): ?>
            <div class="lsd-order-item-meta"><?php echo implode('<br>', $meta_lines); ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div><?php esc_html_e('No orders found.', 'listdom'); ?></div>
    <?php endif; ?>
</div>
