<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Payments_Gateways_Stripe $this */
/** @var array $data */
$webhook_url = rest_url('listdom/v1/payments/stripe/webhook');
$supported_events = apply_filters('lsd_payments_stripe_supported_webhook_events', [
    'invoice.payment_succeeded',
]);
?>
<hr class="lsd-my-4">
<div class="lsd-row">
    <div class="lsd-col-3">
        <?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Publishable Key', 'listdom'),
            'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_publishable_key',
        ]); ?>
    </div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'class' => 'lsd-admin-input',
            'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_publishable_key',
            'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][publishable_key]',
            'value' => $data['publishable_key'] ?? '',
            'placeholder' => esc_attr__('Publishable Key', 'listdom'),
        ]); ?>
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Stripe publishable API key.', 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-row">
    <div class="lsd-col-3">
        <?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Secret Key', 'listdom'),
            'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_secret_key',
        ]); ?>
    </div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'class' => 'lsd-admin-input',
            'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_secret_key',
            'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][secret_key]',
            'value' => $data['secret_key'] ?? '',
            'placeholder' => esc_attr__('Secret Key', 'listdom'),
        ]); ?>
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Stripe secret API key.', 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-row">
    <div class="lsd-col-3">
        <?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Link', 'listdom'),
            'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_link',
        ]); ?>
    </div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_link',
            'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][link]',
            'value' => $data['link'] ?? 0,
        ]); ?>
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Enable Stripe Link for quick checkout.', 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-row">
    <div class="lsd-col-3">
        <?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Address', 'listdom'),
            'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_address',
        ]); ?>
    </div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'class' => 'lsd-admin-input',
            'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_address',
            'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][address]',
            'options' => [
                0 => esc_html__('Disabled', 'listdom'),
                'billing' => esc_html__('Billing', 'listdom'),
                'shipping' => esc_html__('Shipping', 'listdom'),
            ],
            'value' => $data['address'] ?? 0,
        ]); ?>
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Collect customer address information.', 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-row">
    <div class="lsd-col-3">
        <?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Webhook URL', 'listdom'),
            'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_webhook_url',
        ]); ?>
    </div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'class' => 'lsd-admin-input',
            'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_webhook_url',
            'name' => '',
            'value' => $webhook_url,
            'attributes' => [
                'readonly' => 'readonly',
                'onfocus' => 'this.select();',
            ],
        ]); ?>
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Add this URL in your Stripe Dashboard (Developers → Webhooks) so Stripe can notify Listdom about subscription updates.', 'listdom'); ?></p>
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Subscribe to the following events for full support:', 'listdom'); ?></p>
        <ul class="lsd-admin-description-tiny lsd-mt-2">
            <?php foreach ($supported_events as $event): ?>
                <li><code><?php echo esc_html($event); ?></code></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<div class="lsd-row">
    <div class="lsd-col-3">
        <?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Webhook Signing Secret', 'listdom'),
            'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_webhook_secret',
        ]); ?>
    </div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'class' => 'lsd-admin-input',
            'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_webhook_secret',
            'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][webhook_secret]',
            'value' => $data['webhook_secret'] ?? '',
            'placeholder' => esc_attr__('whsec_...', 'listdom'),
        ]); ?>
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Copy the signing secret from the webhook in your Stripe Dashboard and paste it here so Listdom can verify incoming events.', 'listdom'); ?></p>
    </div>
</div>
