<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$order = new LSD_Payments_Order($post);
$items = $order->get_items();
$fees = $order->get_fees();
$currency = LSD_Options::currency();
$invoice_url = $order->get_invoice_url();
?>
<div class="lsd-metabox lsd-order-metabox lsd-p-3">
    <?php if ($invoice_url): ?>
    <div class="lsd-flex lsd-flex-content-end lsd-mb-3">
        <a class="lsd-secondary-button" href="<?php echo esc_url($invoice_url); ?>" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e('Download Invoice', 'listdom'); ?>
        </a>
    </div>
    <?php endif; ?>
    <?php if (count($items) || count($fees)): ?>
    <div class="lsd-order-items lsd-flex lsd-flex-col lsd-gap-4">
        <?php foreach ($items as $item):
            $plan_id = (int) ($item['plan_id'] ?? 0);
            $tier_id = $item['tier_id'] ?? '';

            $plan = new LSD_Payments_Plan($plan_id, $tier_id);
            $tier = $plan->get_tier();

            $metas = $order->get_metas($item);
        ?>
        <div class="lsd-order-item lsd-box lsd-flex lsd-flex-col lsd-flex-items-start lsd-flex-items-stretch lsd-gap-4">
            <div class="lsd-flex lsd-flex-row lsd-flex-items-start">
                <div>
                    <h3 class="lsd-admin-title lsd-my-0"><?php echo esc_html(get_the_title($plan_id)); ?></h3>
                    <?php if ($tier): ?>
                    <p class="lsd-order-item-tier lsd-my-0"><?php echo esc_html($tier->get_name()); ?></p>
                    <?php endif; ?>
                </div>
                <div class="lsd-order-item-total"><?php echo $this->render_price($plan->get_price(), $currency, false, false); ?></div>
            </div>

            <?php if (count($metas)): ?>
            <div class="lsd-order-item-meta"><?php echo implode('<br>', $metas); ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php foreach ($fees as $fee):
            $title = isset($fee['title']) ? trim((string) $fee['title']) : '';
            $amount = isset($fee['amount']) ? (float) $fee['amount'] : 0;
        ?>
        <div class="lsd-order-item lsd-box lsd-flex lsd-flex-col lsd-flex-items-start lsd-flex-items-stretch lsd-gap-4">
            <div class="lsd-flex lsd-flex-row lsd-flex-items-start">
                <div>
                    <h3 class="lsd-admin-title lsd-my-0"><?php echo $title !== '' ? esc_html($title) : esc_html__('Fee', 'listdom'); ?></h3>
                </div>
                <div class="lsd-order-item-total"><?php echo $this->render_price($amount, $currency, false, false); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div><?php esc_html_e('No items found.', 'listdom'); ?></div>
    <?php endif; ?>
</div>
