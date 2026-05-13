<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Payments_Gateways_Free $this */

$nonce = wp_create_nonce('lsd_checkout');

// Comment
$payments = LSD_Options::payments();
$comment = $payments['free_checkout_comment'] ?? '';
?>
<div class="lsd-gateway-wrapper">
    <?php if ($comment): ?>
        <div class="lsd-free-checkout-comment"><?php echo LSD_Kses::element($comment); ?></div>
    <?php endif; ?>
    <?php echo LSD_Kses::form($this->form_checkout_user()); ?>

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
</div>
