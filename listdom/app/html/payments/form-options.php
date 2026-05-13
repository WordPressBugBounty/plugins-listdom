<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Payments_Gateway $this */
/** @var array $data */
?>
<div class="lsd-row">
    <div class="lsd-col-3">
        <?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Status', 'listdom'),
            'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_status',
        ]); ?>
    </div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::switcher([
            'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_status',
            'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][status]',
            'value' => $data['status'] ?? 0,
            'toggle' => '#lsd_gateway_' . esc_attr($this->key()) . '_container',
        ]); ?>
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Enable this gateway', 'listdom'); ?></p>
    </div>
</div>
<div id="lsd_gateway_<?php echo esc_attr($this->key()); ?>_container" class="lsd-gateway-options-wrapper <?php echo !isset($data['status']) || !$data['status'] ? 'lsd-util-hide' : ''; ?>">
    <div class="lsd-row">
        <div class="lsd-col-3">
            <?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Name', 'listdom'),
                'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_name',
            ]); ?>
        </div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_name',
                'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][name]',
                'value' => $data['name'] ?? '',
                'placeholder' => esc_attr__('Gateway Name', 'listdom'),
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Displayed title of the gateway.', 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-row">
        <div class="lsd-col-3">
            <?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Position', 'listdom'),
                'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_position',
            ]); ?>
        </div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::number([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_position',
                'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][position]',
                'value' => $data['position'] ?? '',
                'placeholder' => esc_attr__('1', 'listdom'),
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('The order in which this gateway appears.', 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-row">
        <div class="lsd-col-3">
            <?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Comment', 'listdom'),
                'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_comment',
            ]); ?>
        </div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::editor([
                'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_comment',
                'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][comment]',
                'value' => $data['comment'] ?? '',
                'media_buttons' => false,
                'rows' => 4,
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Additional information shown to users.', 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-row">
        <div class="lsd-col-3">
            <?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Auto Complete', 'listdom'),
                'for' => 'lsd_gateway_' . esc_attr($this->key()) . '_auto_complete',
            ]); ?>
        </div>
        <div class="lsd-col-6">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_gateway_' . esc_attr($this->key()) . '_auto_complete',
                'name' => 'lsd[payments][gateways][' . esc_attr($this->key()) . '][auto_complete]',
                'value' => $data['auto_complete'] ?? 0,
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Complete payments automatically.', 'listdom'); ?></p>
        </div>
    </div>
    <?php echo LSD_Kses::form($this->form_specific($data)); ?>
</div>
