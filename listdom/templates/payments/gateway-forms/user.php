<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Payments_Gateway $this */
?>
<div class="lsd-checkout-user">
    <input name="name" title="<?php esc_attr_e('Your name', 'listdom'); ?>" type="text" class="lsd-form-control-text lsd-fe-input lsd-checkout-user-name" placeholder="<?php esc_attr_e('Your name', 'listdom'); ?>">
    <input name="email" title="<?php esc_attr_e('Your email', 'listdom'); ?>" type="email" class="lsd-form-control-text lsd-fe-input lsd-checkout-user-email" placeholder="<?php esc_attr_e('Your email', 'listdom'); ?>">
</div>
