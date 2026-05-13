<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $index */
/** @var array $tier */

$payments = lsd_payment();

$id = $tier['id'] ?? wp_generate_uuid4();
$name = $tier['name'] ?? '';
$price = $tier['price'] ?? '';
$expiry = $tier['expiry'] ?? '';
$type = $tier['type'] ?? 'one_time';
$default = isset($tier['default']) && $tier['default'];
$recurring_enabled = $payments->is_recurring_enabled();
$recurring_available = $payments->is_recurring_available();

if ($type === 'recurring' && !$recurring_available) $type = 'one_time';
?>
<div class="lsd-plan-tier lsd-box lsd-flex lsd-flex-col lsd-flex-items-start lsd-gap-4" data-index="<?php echo esc_attr($index); ?>">
    <div class="lsd-flex lsd-flex-row lsd-flex-items-start">
        <div>
            <h3 class="lsd-plan-tier-title lsd-m-0"><?php echo esc_html($name); ?></h3>
        </div>
        <div class="lsd-plan-tier-actions lsd-flex lsd-gap-3 lsd-items-center">
            <input type="hidden" class="lsd-plan-tier-id-input" name="lsd_tiers[<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($id); ?>">
            <input type="hidden" class="lsd-plan-tier-default-input" name="lsd_tiers[<?php echo esc_attr($index); ?>][default]" value="<?php echo $default ? 1 : 0; ?>">
            <span class="lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Sort', 'listdom'); ?>">
                <i class="lsd-icon fas fa-arrows-alt lsd-plan-tier-sort"></i>
            </span>
            <span class="lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Default Tier', 'listdom'); ?>">
                <i class="lsd-icon <?php echo $default ? 'fas' : 'far'; ?> fa-star lsd-plan-tier-star"></i>
            </span>
            <span class="lsd-tooltip" data-lsd-tooltip="<?php esc_attr_e('Remove', 'listdom'); ?>">
                <i class="lsd-icon fas fa-trash-alt lsd-plan-tier-remove"></i>
            </span>
        </div>
    </div>
    <div class="lsd-plan-tier-fields lsd-flex lsd-flex-row lsd-flex-items-start lsd-flex-content-start lsd-gap-4 lsd-flex-wrap">
        <div class="lsd-flex-2">
            <?php echo LSD_Form::label([
                'for' => 'lsd_tiers_' . $index . '_name',
                'title' => esc_html__('Tier Name', 'listdom'),
            ]); ?>
            <?php echo LSD_Form::text([
                'id' => 'lsd_tiers_' . $index . '_name',
                'name' => 'lsd_tiers[' . $index . '][name]',
                'value' => $name,
                'placeholder' => esc_attr__('Tier Name', 'listdom'),
                'required' => true,
            ]); ?>
        </div>
        <div>
            <?php echo LSD_Form::label([
                'for' => 'lsd_tiers_' . $index . '_price',
                'title' => esc_html__('Price', 'listdom'),
            ]); ?>
            <?php echo LSD_Form::number([
                'id' => 'lsd_tiers_' . $index . '_price',
                'name' => 'lsd_tiers[' . $index . '][price]',
                'value' => $price,
                'placeholder' => esc_attr__('Price', 'listdom'),
                'attributes' => [
                    'step' => '0.01',
                    'min' => '0'
                ],
            ]); ?>
        </div>
        <div class="lsd-plan-tier-type-wrapper<?php echo $recurring_enabled ? '' : ' lsd-util-hide'; ?>">
            <div class="lsd-flex lsd-flex-row lsd-flex-items-center lsd-gap-2">
                <?php echo LSD_Form::label([
                    'for' => 'lsd_tiers_' . $index . '_type',
                    'title' => esc_html__('Type', 'listdom'),
                ]); ?>
                <?php if ($recurring_enabled && !$recurring_available): ?>
                    <span class="lsd-tooltip" data-lsd-tooltip="<?php echo esc_attr__('Stripe must be configured before recurring payments can be enabled.', 'listdom'); ?>">
                        <i class="lsd-icon fas fa-info-circle"></i>
                    </span>
                <?php endif; ?>
            </div>
            <select title="" id="lsd_tiers_<?php echo esc_attr($index); ?>_type" name="lsd_tiers[<?php echo esc_attr($index); ?>][type]" class="lsd-plan-tier-type">
                <option value="one_time" <?php selected($type, 'one_time'); ?>><?php esc_html_e('One Time', 'listdom'); ?></option>
                <option value="recurring" <?php selected($type, 'recurring'); ?><?php echo ($recurring_enabled && !$recurring_available) ? ' disabled="disabled"' : ''; ?>><?php esc_html_e('Recurring', 'listdom'); ?></option>
            </select>
            <?php if ($recurring_enabled && !$recurring_available): ?>
                <div class="lsd-alert lsd-warning inline lsd-mt-2 lsd-py-2 lsd-px-3">
                    <p class="lsd-m-0"><?php esc_html_e('Configure and activate Stripe gateway to enable recurring payments.', 'listdom'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <div class="lsd-plan-tier-expiry-wrapper<?php echo ($recurring_available && $type === 'recurring') ? '' : ' lsd-util-hide'; ?>">
            <?php echo LSD_Form::label([
                'for' => 'lsd_tiers_' . $index . '_expiry',
                'title' => esc_html__('Expiry (days)', 'listdom'),
            ]); ?>
            <?php echo LSD_Form::number([
                'id' => 'lsd_tiers_' . $index . '_expiry',
                'name' => 'lsd_tiers[' . $index . '][expiry]',
                'value' => $expiry,
                'required' => $recurring_available && $type === 'recurring',
                'placeholder' => esc_attr__('Expiry (days)', 'listdom'),
                'class' => 'lsd-plan-tier-expiry',
                'attributes' => [
                    'min' => '1',
                ],
            ]); ?>
            <p class="description lsd-mb-0"><?php esc_html_e('Leave empty for unlimited', 'listdom'); ?></p>
        </div>
    </div>
</div>
