<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$discount = get_post_meta($post->ID, 'lsd_discount', true);
if ($discount === '') $discount = 0;

$deduction = get_post_meta($post->ID, 'lsd_deduction', true);
if ($deduction === '') $deduction = 0;

$usage_limit = get_post_meta($post->ID, 'lsd_usage_limit', true);
?>
<div class="lsd-metabox lsd-coupon-metabox lsd-pt-3">
    <?php /* Security Nonce */ LSD_Form::nonce('lsd_coupon_cpt', '_lsdnonce'); ?>
    <div class="lsd-form-row">
        <div class="lsd-col-2 lsd-text-right">
            <?php echo LSD_Form::label([
                'for' => 'lsd_coupon_discount',
                'title' => esc_html__('Discount (%)', 'listdom'),
            ]); ?>
        </div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::number([
                'id' => 'lsd_coupon_discount',
                'name' => 'lsd[discount]',
                'value' => $discount,
                'attributes' => [
                    'step' => '0.01',
                    'min' => '0',
                    'max' => '100',
                ],
            ]); ?>
            <p class="description lsd-mb-3"><?php esc_html_e('This is a percentage-based discount. Enter a value like 15 to apply a 15% reduction to the price. Leave empty if not applicable.', 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2 lsd-text-right">
            <?php echo LSD_Form::label([
                'for' => 'lsd_coupon_deduction',
                'title' => esc_html__('Deduction ($)', 'listdom'),
            ]); ?>
        </div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::number([
                'id' => 'lsd_coupon_deduction',
                'name' => 'lsd[deduction]',
                'value' => $deduction,
                'attributes' => [
                    'step' => '0.01',
                    'min' => '0',
                ],
            ]); ?>
            <p class="description lsd-mb-3"><?php esc_html_e('This is a fixed amount that will be subtracted from the price. For example, entering 20 will deduct $20 from the final amount. Leave empty if not applicable.', 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"></div>
        <div class="lsd-col-6">
            <h3 class="lsd-mt-0 lsd-mb-2"><?php esc_html_e('Formula', 'listdom'); ?></h3>
            <p class="description"><?php esc_html_e('If you enter both a percentage discount and a fixed deduction, they will be applied in this order: Final Price = Price – Discount (%) – Deduction ($).', 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2 lsd-text-right">
            <?php echo LSD_Form::label([
                'for' => 'lsd_coupon_usage_limit',
                'title' => esc_html__('Usage Limit', 'listdom'),
            ]); ?>
        </div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::number([
                'id' => 'lsd_coupon_usage_limit',
                'name' => 'lsd[usage_limit]',
                'value' => $usage_limit,
                'attributes' => [
                    'step' => '1',
                    'min' => '0',
                ],
            ]); ?>
            <p class="description lsd-mb-0"><?php esc_html_e('Defines the total number of times this coupon can be redeemed across all customers. Leave it empty for unlimited usage.', 'listdom'); ?></p>
        </div>
    </div>
</div>
