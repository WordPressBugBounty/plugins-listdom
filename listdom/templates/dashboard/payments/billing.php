<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */

$user_id = get_current_user_id();
$orders = $this->get_orders($user_id);
$billing_profile = $this->get_billing_profile($orders);
$billing_countries = $this->get_billing_countries();
$billing_states_list = $this->get_billing_states_list();
$billing_states = $this->get_billing_states((string) ($billing_profile['country'] ?? ''));
?>
<div class="lsd-row">
    <div class="lsd-col-12">
        <div class="lsd-fe-box-white">
            <h3 class="lsd-fe-title"><?php esc_html_e('Billing Information', 'listdom'); ?></h3>

            <form class="lsd-dashboard-payments-billing-form" method="post">
                <?php wp_nonce_field('lsd_dashboard_payments_billing', 'lsd_dashboard_payments_billing_nonce'); ?>

                <div class="lsd-row lsd-dashboard-payments-billing-rows">
                    <div class="lsd-col-12">
                        <div class="lsd-form-row">
                            <label class="lsd-fields-label" for="lsd_billing_name"><?php esc_html_e('Full Name', 'listdom'); ?></label>
                            <input
                                type="text"
                                id="lsd_billing_name"
                                name="lsd_billing[name]"
                                class="lsd-fe-input"
                                value="<?php echo esc_attr($billing_profile['name'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="lsd-col-6">
                        <div class="lsd-form-row">
                            <label class="lsd-fields-label" for="lsd_billing_email"><?php esc_html_e('Email', 'listdom'); ?></label>
                            <input
                                type="email"
                                id="lsd_billing_email"
                                name="lsd_billing[email]"
                                class="lsd-fe-input"
                                value="<?php echo esc_attr($billing_profile['email'] ?? ''); ?>"
                            >
                        </div>
                    </div>
                    <div class="lsd-col-6">
                        <div class="lsd-form-row">
                            <label class="lsd-fields-label" for="lsd_billing_phone"><?php esc_html_e('Phone', 'listdom'); ?></label>
                            <input
                                type="text"
                                id="lsd_billing_phone"
                                name="lsd_billing[phone]"
                                class="lsd-fe-input"
                                value="<?php echo esc_attr($billing_profile['phone'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="lsd-col-6">
                        <div class="lsd-form-row">
                            <label class="lsd-fields-label" for="lsd_billing_company_name"><?php esc_html_e('Company Name', 'listdom'); ?></label>
                            <input
                                type="text"
                                id="lsd_billing_company_name"
                                name="lsd_billing[company_name]"
                                class="lsd-fe-input"
                                value="<?php echo esc_attr($billing_profile['company_name'] ?? ''); ?>"
                            >
                        </div>
                    </div>
                    <div class="lsd-col-6">
                        <div class="lsd-form-row">
                            <label class="lsd-fields-label" for="lsd_billing_tax_vat_id"><?php esc_html_e('Tax/Vat ID', 'listdom'); ?></label>
                            <input
                                type="text"
                                id="lsd_billing_tax_vat_id"
                                name="lsd_billing[tax_vat_id]"
                                class="lsd-fe-input"
                                value="<?php echo esc_attr($billing_profile['tax_vat_id'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="lsd-col-6">
                        <div class="lsd-form-row">
                            <label class="lsd-fields-label" for="lsd_billing_country"><?php esc_html_e('Country', 'listdom'); ?></label>
                            <select class="lsd-billing-country" id="lsd_billing_country" name="lsd_billing[country]">
                                <option value=""><?php esc_html_e('Select country', 'listdom'); ?></option>
                                <?php foreach ($billing_countries as $country_code => $country_label): ?>
                                    <option value="<?php echo esc_attr($country_code); ?>"<?php selected($billing_profile['country'] ?? '', $country_code); ?>><?php echo esc_html($country_label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="lsd-col-6">
                        <div class="lsd-form-row">
                            <label class="lsd-fields-label" for="lsd_billing_state"><?php esc_html_e('State / Province', 'listdom'); ?></label>
                            <select class="lsd-billing-state" id="lsd_billing_state" name="lsd_billing[state]" data-selected="<?php echo esc_attr($billing_profile['state'] ?? ''); ?>">
                                <option value=""><?php esc_html_e('Select state / province', 'listdom'); ?></option>
                                <?php foreach ($billing_states as $state_code => $state_label): ?>
                                    <option value="<?php echo esc_attr($state_code); ?>"<?php selected($billing_profile['state'] ?? '', $state_code); ?>><?php echo esc_html($state_label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="lsd-col-12">
                        <div class="lsd-form-row">
                            <label class="lsd-fields-label" for="lsd_billing_address"><?php esc_html_e('Address', 'listdom'); ?></label>
                            <input
                                type="text"
                                id="lsd_billing_address"
                                name="lsd_billing[address]"
                                class="lsd-fe-input"
                                value="<?php echo esc_attr($billing_profile['address'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="lsd-col-6">
                        <div class="lsd-form-row">
                            <label class="lsd-fields-label" for="lsd_billing_city"><?php esc_html_e('City', 'listdom'); ?></label>
                            <input
                                type="text"
                                id="lsd_billing_city"
                                name="lsd_billing[city]"
                                class="lsd-fe-input"
                                value="<?php echo esc_attr($billing_profile['city'] ?? ''); ?>"
                            >
                        </div>
                    </div>
                    <div class="lsd-col-6">
                        <div class="lsd-form-row">
                            <label class="lsd-fields-label" for="lsd_billing_postal_code"><?php esc_html_e('Postal Code', 'listdom'); ?></label>
                            <input
                                type="text"
                                id="lsd_billing_postal_code"
                                name="lsd_billing[postal_code]"
                                class="lsd-fe-input"
                                value="<?php echo esc_attr($billing_profile['postal_code'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="lsd-col-12">
                        <div class="lsd-form-row lsd-dashboard-payments-billing-submit-button">
                            <button type="submit" class="lsd-general-button">
                                <?php esc_html_e('Save Changes', 'listdom'); ?>
                                <i class="fa-solid fa-check-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function($)
{
    const states = <?php echo wp_json_encode($billing_states_list); ?>;
    const $country = $('#lsd_billing_country');
    const $state = $('#lsd_billing_state');

    function initStyledSelect($select)
    {
        if (!$.fn.select2 || !$select.length) return;

        if ($select.hasClass('select2-hidden-accessible'))
        {
            $select.select2('destroy');
        }

        $select.select2({
            width: '100%',
            minimumResultsForSearch: Infinity
        });
    }

    function refreshStates(country)
    {
        const countryStates = states[country] || {};
        const selected = $state.data('selected') || '';
        let options = '<option value="">' + '<?php echo esc_js(esc_html__('Select state / province', 'listdom')); ?>' + '</option>';

        $.each(countryStates, function(code, label)
        {
            const isSelected = selected === code ? ' selected="selected"' : '';
            options += '<option value="' + code + '"' + isSelected + '>' + label + '</option>';
        });

        $state.html(options);
        initStyledSelect($state);
    }

    initStyledSelect($country);
    refreshStates($country.val());

    $country.on('change', function()
    {
        $state.data('selected', '');
        refreshStates($(this).val());
    });
})(jQuery);
</script>
