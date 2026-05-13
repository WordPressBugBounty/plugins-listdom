<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Cart $cart */
/** @var LSD_Main $main */
/** @var string $currency */
/** @var array $items */
/** @var array $fees */
/** @var string $coupon_code */

[$total, $discount, $tax] = $cart->apply_coupon();
?>
<form method="post" action="<?php echo esc_url($main->current_url()); ?>" class="lsd-cart-form">
    <?php wp_nonce_field('lsd_cart_action'); ?>
    <div class="lsd-cart-items lsd-fe-sections">
        <?php foreach ($items as $id => $item):
            $plan_id = (int) ($item['plan_id'] ?? 0);
            $tier_id = $item['tier_id'] ?? '';

            $plan = new LSD_Payments_Plan($plan_id, $tier_id);
            $tiers = $plan->get_tiers();

            $remove_url = wp_nonce_url(
                add_query_arg('lsd_cart_remove', $id, $main->current_url()),
                'lsd_cart_action'
            );
            ?>
            <div class="lsd-cart-item lsd-fe-box-white">
                <div class="lsd-cart-item-info">
                    <h3 class="lsd-cart-item-title lsd-fe-title"><?php echo esc_html(get_the_title($plan_id)); ?></h3>

                    <?php if (count($tiers) > 1): ?>
                        <div class="lsd-checkout-tier">
                            <select class="lsd-fe-input" title="" name="lsd_cart_tier[<?php echo esc_attr($id); ?>]" onchange="this.form.submit()">
                                <?php foreach ($tiers as $tier): ?>
                                    <option value="<?php echo esc_attr($tier->get_id()); ?>" <?php echo $tier->get_id() === $tier_id ? 'selected="selected"' : ''; ?>>
                                        <?php echo esc_html($tier->get_name()); ?> (<?php echo esc_html(wp_strip_all_tags($tier->get_price_frequency_html())); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="lsd-cart-item-end">
                    <div class="lsd-cart-item-price lsd-fe-title">
                        <?php echo LSD_Kses::element($plan->get_price_html()); ?>
                    </div>

                    <div class="lsd-cart-item-remove">
                        <a href="<?php echo esc_url($remove_url); ?>" class="lsd-checkout-remove">
                            <i class="lsd-fe-icon fa fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ($fees): ?>
        <div class="lsd-cart-fees lsd-fe-sections">
            <?php foreach ($fees as $fee):
                $title = isset($fee['title']) ? trim((string) $fee['title']) : '';
                $amount = isset($fee['amount']) ? (float) $fee['amount'] : 0;
            ?>
                <div class="lsd-cart-fee lsd-fe-box-white">
                    <div class="lsd-cart-item-info">
                        <h3 class="lsd-cart-item-title lsd-fe-title"><?php echo $title !== '' ? esc_html($title) : esc_html__('Fee', 'listdom'); ?></h3>
                    </div>
                    <div class="lsd-cart-item-end">
                        <div class="lsd-cart-item-price lsd-fe-title">
                            <?php echo LSD_Kses::element($main->render_price($amount, $currency)); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</form>
