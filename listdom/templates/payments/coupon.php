<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Main $main */
/** @var string $coupon_code */
/** @var bool $error */
?>
<div class="lsd-fe-box-white">
    <div class="lsd-fe-section-heading">
        <div class="lsd-fe-title-icon">
            <i class="lsd-fe-icon fa fa-tag"></i>
            <h2 class="lsd-fe-title"><?php echo esc_html__('Coupon Code', 'listdom'); ?></h2>
        </div>
        <p class="lsd-fe-description"><?php esc_html_e('Have a discount code? Enter it below.' , 'listdom'); ?></p>
    </div>
    <form action="<?php echo esc_url($main->current_url()); ?>" method="post" class="lsd-coupon-form">
        <?php wp_nonce_field('lsd_coupon_action'); ?>
        <div class="lsd-apply-coupon">
            <input class="lsd-fe-input" title="<?php esc_attr_e('Coupon code', 'listdom'); ?>" type="text" name="lsd_coupon_code" value="<?php echo esc_attr($coupon_code); ?>" placeholder="<?php esc_attr_e('Coupon code', 'listdom'); ?>">
            <button class="lsd-light-button" type="submit"><?php esc_html_e('Apply', 'listdom'); ?></button>
            <?php if ($coupon_code): ?>
                <?php $remove_url = wp_nonce_url(add_query_arg('lsd_coupon_remove', 1, $main->current_url()), 'lsd_coupon_action'); ?>
                <a href="<?php echo esc_url($remove_url); ?>" class="lsd-coupon-remove lsd-text-button"><?php esc_html_e('Remove', 'listdom'); ?></a>
            <?php endif; ?>
        </div>
        <?php if ($error): ?>
            <p class="lsd-coupon-error lsd-alert lsd-error"><?php esc_html_e('Invalid coupon code.', 'listdom'); ?></p>
        <?php endif; ?>
    </form>
</div>
