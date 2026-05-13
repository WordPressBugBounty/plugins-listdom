<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Payments_Gateways_Paypal $this */
/** @var array $data */
?>
<hr class="lsd-my-4">
<div class="lsd-row">
    <div class="lsd-col-3">
        <?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Client ID', 'listdom'),
            'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_client_id',
        ]); ?>
    </div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'class' => 'lsd-admin-input',
            'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_client_id',
            'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][client_id]',
            'value' => $data['client_id'] ?? '',
            'placeholder' => esc_attr__('Client ID', 'listdom'),
        ]); ?>
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('PayPal REST client ID.', 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-row">
    <div class="lsd-col-3">
        <?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Secret', 'listdom'),
            'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_secret',
        ]); ?>
    </div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'class' => 'lsd-admin-input',
            'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_secret',
            'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][secret]',
            'value' => $data['secret'] ?? '',
            'placeholder' => esc_attr__('Secret', 'listdom'),
        ]); ?>
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('PayPal REST secret key.', 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-row">
    <div class="lsd-col-3">
        <?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Mode', 'listdom'),
            'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_mode',
        ]); ?>
    </div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'class' => 'lsd-admin-input',
            'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_mode',
            'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][mode]',
            'options' => [
                'sandbox' => esc_html__('Sandbox', 'listdom'),
                'live' => esc_html__('Live', 'listdom'),
            ],
            'value' => $data['mode'] ?? 'sandbox',
        ]); ?>
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Choose Sandbox to test payments or Live to accept real payments.', 'listdom'); ?></p>
    </div>
</div>
