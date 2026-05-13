<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Payments_Gateway $this */

$nonce = wp_create_nonce('lsd_checkout');
?>
<div class="lsd-gateway-wrapper">
    <div class="lsd-fe-section-heading">
        <div class="lsd-gateway-name lsd-fe-title"><?php echo esc_html($this->name()); ?></div>
        <?php if ($this->comment()): ?>
            <div class="lsd-gateway-comment lsd-fe-description"><?php echo LSD_Kses::element($this->comment()); ?></div>
        <?php endif; ?>
    </div>
    <?php echo LSD_Kses::form($this->form_checkout_user()); ?>
    <textarea id="lsd_gateway_comment_wire_transfer" title="<?php esc_attr_e('Comment', 'listdom'); ?>" class="lsd-form-control-textarea lsd-fe-input lsd-checkout-message" placeholder="<?php esc_attr_e('Your message …', 'listdom'); ?>"></textarea>

    <?php echo LSD_Privacy::consent_field([
        'id' => 'lsd_checkout_consent_' . uniqid(),
        'name' => 'lsd_privacy_consent',
        'class' => 'lsd-privacy-consent-checkbox lsd-checkout-consent-input',
        'wrapper_class' => 'lsd-checkout-consent',
        'context' => 'checkout',
        'required_message' => LSD_Privacy::consent_required_text(),
    ]); ?>

    <button type="button" class="lsd-button lsd-checkout-button lsd-general-button" data-gateway="<?php echo esc_attr($this->key()); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
        <?php esc_html_e('Complete Purchase', 'listdom'); ?>
        <i class="fa-solid fa-long-arrow-right"></i>
    </button>
    <div class="lsd-checkout-response"></div>
</div>
