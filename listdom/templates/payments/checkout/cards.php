<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Checkout $this */
/** @var LSD_Cart $cart */
/** @var LSD_Payments_Gateway[] $gateways */
/** @var bool $requires_payment */
/** @var LSD_Payments_Gateways_Free $free */
/** @var string $agreement */
/** @var string $appreciation_message */
/** @var string $thank_you */
/** @var bool $show_appreciation_invoice_button */
/** @var string $appreciation_invoice_base */
/** @var string $gateway_warning */
/** @var int $gateway_tabs_count */
?>
<div class="lsd-checkout-wrapper lsd-checkout-style-cards">
    <div class="lsd-fe-sections">
        <div class="lsd-fe-box-white">
            <div class="lsd-fe-section-heading">
                <div class="lsd-fe-title-icon">
                    <i class="lsd-fe-icon fa fa-shopping-cart"></i>
                    <h2 class="lsd-fe-title"><?php echo esc_html__('Your Cart', 'listdom'); ?></h2>
                </div>
                <p class="lsd-fe-description"><?php esc_html_e('Review and submit your order' , 'listdom'); ?></p>
            </div>
        </div>
        <div class="lsd-checkout-cart">
            <?php echo LSD_Kses::full($this->cart()); ?>
        </div>
        <div class="lsd-checkout-coupon"><?php echo LSD_Kses::full($this->coupon()); ?></div>
    </div>
    <div class="lsd-fe-sections">
        <div class="lsd-fe-box-white">
            <div class="lsd-checkout-summary lsd-fe-subsections">
                <div class="lsd-fe-title-icon">
                    <i class="lsd-fe-icon fa fa-check-circle"></i>
                    <h2 class="lsd-fe-title"><?php echo esc_html__('Order Summary', 'listdom'); ?></h2>
                </div>
                <?php echo LSD_Kses::full($this->summary()); ?>
            </div>
        </div>
        <div class="lsd-fe-box-white">
            <div class="lsd-checkout-gateways lsd-fe-subsections">
                <?php if ($gateway_tabs_count > 1): ?>
                    <div class="lsd-fe-section-heading">
                        <div class="lsd-fe-title-icon">
                            <i class="lsd-fe-icon fa fa-credit-card-alt"></i>
                            <h2 class="lsd-fe-title"><?php echo esc_html__('Payment Method', 'listdom'); ?></h2>
                        </div>
                        <p class="lsd-fe-description"><?php esc_html_e('Select payment method' , 'listdom'); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($gateway_warning): ?>
                    <div class="lsd-alert lsd-warning"><?php echo esc_html($gateway_warning); ?></div>
                <?php elseif ($requires_payment): ?>
                    <div class="lsd-fe-tabs lsd-fe-subsections">
                        <?php if ($gateway_tabs_count > 1): ?>
                            <ul class="lsd-checkout-tabs-nav lsd-fe-tabs-nav">
                                <?php $first = true; foreach ($gateways as $gateway): if ($gateway->key() === 'free') continue; ?>
                                <li class="<?php echo $first ? 'lsd-active' : ''; ?>">
                                    <a href="#" data-gateway="<?php echo esc_attr($gateway->key()); ?>">
                                        <i class="<?php echo esc_attr($gateway->icon()); ?>"></i>
                                        <?php echo esc_html($gateway->name()); ?>
                                    </a>
                                </li>
                                <?php $first = false; endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <div class="lsd-checkout-tabs-contents lsd-fe-tabs-content">
                            <?php $first = true; foreach ($gateways as $gateway): if ($gateway->key() === 'free') continue; ?>
                            <div id="lsd-gateway-form-<?php echo esc_attr($gateway->key()); ?>" class="lsd-gateway-form<?php echo $first ? '' : ' lsd-util-hide'; ?>">
                                <?php echo LSD_Kses::full($gateway->form_checkout()); ?>
                            </div>
                            <?php $first = false; endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php echo LSD_Kses::full($free->form_checkout()); ?>
                <?php endif; ?>
            </div>
            <div class="lsd-checkout-agreement">
                <?php echo LSD_Kses::full($agreement); ?>
            </div>
        </div>
    </div>
</div>

<?php echo LSD_Kses::full($this->thankyou());
