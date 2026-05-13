<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Cart $cart */
/** @var LSD_Main $main */
/** @var string $currency */
/** @var string $coupon_code */
/** @var array $fees */

$totals = $cart->get_totals();
$total = $totals['total'];
$discount = $totals['discount'];
$tax = $totals['tax'];
$tax_items = isset($totals['taxes']) && is_array($totals['taxes']) ? $totals['taxes'] : [];
$tax_helper = isset($tax_helper) && $tax_helper instanceof LSD_Payments_Tax ? $tax_helper : new LSD_Payments_Tax();

$coupon = $coupon_code ? new LSD_Payments_Coupon($coupon_code) : null;

$display_subtotal = $totals['subtotal'];
if ($tax_helper->prices_include_tax() && $tax > 0) $display_subtotal = max($totals['subtotal'] - $tax, 0);

$tax_location = isset($tax_location) && is_array($tax_location) ? $tax_location : ['country' => '', 'state' => ''];
$tax_countries = isset($tax_countries) && is_array($tax_countries) ? $tax_countries : [];
$tax_states = isset($tax_states) && is_array($tax_states) ? $tax_states : [];
$tax_states_list = isset($tax_states_list) && is_array($tax_states_list) ? $tax_states_list : [];

$tooltip = '';
if ($discount > 0 && $coupon)
{
    $parts = [];
    if ($coupon->get_discount() > 0) $parts[] = sprintf(__('%s%%', 'listdom'), $coupon->get_discount());
    if ($coupon->get_deduction() > 0) $parts[] = $main->render_price($coupon->get_deduction(), $currency);

    if ($coupon_code) $tooltip .= sprintf(__('Code: %s', 'listdom'), $coupon_code);
    if ($parts) $tooltip .= "\n" . sprintf(__('Amount: %s', 'listdom'), implode(' + ', $parts));

    $tooltip = strip_tags($tooltip);
}
?>
<?php if ($tax_helper->enabled() && count($tax_countries)): ?>
    <form class="lsd-tax-location-form lsd-fe-box-white" method="post">
        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('lsd_tax_location')); ?>">
        <div class="lsd-tax-location-inner">
            <div>
                <label class="lsd-fields-label" for="lsd_tax_country"><?php esc_html_e('Country', 'listdom'); ?></label>
                <select class="lsd-admin-input lsd-tax-country" id="lsd_tax_country" name="lsd_tax_country">
                    <option value=""><?php esc_html_e('Select country', 'listdom'); ?></option>
                    <?php foreach ($tax_countries as $country_code => $country_label): ?>
                        <option value="<?php echo esc_attr($country_code); ?>"<?php selected($tax_location['country'], $country_code); ?>><?php echo esc_html($country_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="lsd-fields-label" for="lsd_tax_state"><?php esc_html_e('State / Province', 'listdom'); ?></label>
                <select class="lsd-admin-input lsd-tax-state" id="lsd_tax_state" name="lsd_tax_state" data-selected="<?php echo esc_attr($tax_location['state']); ?>">
                    <option value=""><?php esc_html_e('All states / provinces', 'listdom'); ?></option>
                    <?php foreach ($tax_states as $state_code => $state_label): ?>
                        <option value="<?php echo esc_attr($state_code); ?>"<?php selected($tax_location['state'], $state_code); ?>><?php echo esc_html($state_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="button lsd-neutral-button"><?php esc_html_e('Update', 'listdom'); ?></button>
            </div>
        </div>
    </form>
    <script>
    (function($)
    {
        const states = <?php echo wp_json_encode($tax_states_list); ?>;
        const $country = $('#lsd_tax_country');
        const $state = $('#lsd_tax_state');

        function refreshStates(country)
        {
            const countryStates = states[country] || {};
            const selected = $state.data('selected') || '';
            let options = '<option value="">' + '<?php echo esc_js(esc_html__('All states / provinces', 'listdom')); ?>' + '</option>';
            $.each(countryStates, function(code, label)
            {
                const isSelected = selected === code ? ' selected="selected"' : '';
                options += '<option value="' + code + '"' + isSelected + '>' + label + '</option>';
            });
            $state.html(options);
        }

        refreshStates($country.val());

        $country.on('change', function()
        {
            $state.data('selected', '');
            refreshStates($(this).val());
        });
    })(jQuery);
    </script>
<?php endif; ?>
<div class="lsd-cart-summary">
    <div class="lsd-cart-summary-row">
        <span class="lsd-label"><?php esc_html_e('Subtotal:', 'listdom'); ?></span>
        <span class="lsd-value">
            <?php echo LSD_Kses::element($main->render_price($display_subtotal, $currency)); ?>
        </span>
    </div>

    <?php if ($discount > 0): ?>
        <div class="lsd-cart-summary-row lsd-cart-discount">
            <span class="lsd-label">
                <?php esc_html_e('Discount:', 'listdom'); ?>
                <?php if ($tooltip): ?>
                    <span class="lsd-tooltip lsd-fe-tooltip" data-lsd-tooltip="<?php echo esc_attr($tooltip); ?>">
                        <i class="fa fa-question-circle lsd-fe-icon"></i>
                    </span>
                <?php endif; ?>
            </span>
            <span class="lsd-value">-<?php echo LSD_Kses::element($main->render_price($discount, $currency)); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($tax_helper->enabled() || $tax > 0): ?>
        <?php if (count($tax_items)): ?>
            <?php foreach ($tax_items as $tax_item): ?>
                <?php
                $item_label = isset($tax_item['label']) && trim((string) $tax_item['label']) !== '' ? $tax_item['label'] : $tax_helper->label();
                $item_rate = isset($tax_item['rate']) ? (float) $tax_item['rate'] : 0.0;
                if ($item_rate > 0) $item_label .= ' (' . number_format_i18n($item_rate, 2) . '%)';
                $item_amount = isset($tax_item['amount']) ? (float) $tax_item['amount'] : 0.0;
                ?>
                <div class="lsd-cart-summary-row lsd-cart-tax">
                    <span class="lsd-label"><?php echo esc_html($item_label); ?>:</span>
                    <span class="lsd-value"><?php echo LSD_Kses::element($main->render_price($item_amount, $currency, false, false)); ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="lsd-cart-summary-row lsd-cart-tax">
                <span class="lsd-label"><?php echo esc_html($tax_helper->label()); ?>:</span>
                <span class="lsd-value"><?php echo LSD_Kses::element($main->render_price($tax, $currency, false, false)); ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="lsd-fe-divider"></div>

    <div class="lsd-cart-summary-row lsd-cart-total">
        <span class="lsd-label"><?php esc_html_e('Total:', 'listdom'); ?></span>
        <span class="lsd-value"><?php echo LSD_Kses::element($main->render_price($total, $currency)); ?></span>
    </div>
</div>
