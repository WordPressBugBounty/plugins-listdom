<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

// Payments Settings
$payments = LSD_Options::payments();
$invoice_settings = $payments['invoice'] ?? [];
$empty_settings = $payments['empty'] ?? [];

// Payment Engine
$engine = LSD_Payments_Engine::instance();

// Is Listdom?
$is_listdom = (int) $engine->listdom();
$tax_settings = $payments['taxes'] ?? [];
?>
<div class="lsd-settings-wrap">
    <form id="lsd_payments_form">
        <div id="lsd_panel_payments_engine" class="lsd-payments-form-group lsd-tab-content <?php echo $this->subtab === 'engine' || !$this->subtab ? ' lsd-tab-content-active' : ''; ?>">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Engine', 'listdom'); ?></h3>

            <div class="lsd-settings-group-wrapper">
                <div class="lsd-settings-fields-wrapper">
                    <div class="lsd-row lsd-my-0">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'title' => esc_html__('Listdom Engine', 'listdom'),
                            'for' => 'lsd_payments_system',
                            'class' => 'lsd-d-block lsd-pt-2 lsd-fields-label',
                        ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::switcher([
                                'id' => 'lsd_payments_system',
                                'name' => 'lsd[payments][listdom]',
                                'value' => $engine->listdom(),
                                'toggle' => '#lsd-payments-listdom-form-container',
                                'toggle2' => '#lsd-payments-wc-form-container'
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div id="lsd-payments-wc-form-container" class="<?php echo $engine->wc() ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-alert lsd-natural lsd-m-0">
                        <p class="lsd-mt-2 lsd-mb-0"><?php esc_html_e("You're using", 'listdom'); ?></p>
                        <h3 class="lsd-mt-0"><?php esc_html_e('WooCommerce Payment Engine', 'listdom'); ?></h3>

                        <p><?php esc_html_e("In this mode, all Listdom payments (e.g. bookings, claims, subscriptions, etc.) are processed through WooCommerce.", 'listdom'); ?></p>
                        <p><?php esc_html_e("Please make sure to:", 'listdom'); ?></p>

                        <ol>
                            <li><?php esc_html_e("Install and activate the WooCommerce plugin", 'listdom'); ?></li>
                            <li><?php esc_html_e("Configure your desired payment gateways in WooCommerce → Settings → Payments", 'listdom'); ?></li>
                            <li><?php esc_html_e("Set up order emails, statuses, and processing rules in WooCommerce", 'listdom'); ?></li>
                            <li><?php esc_html_e("Use WooCommerce coupons for any discount or promotion logic", 'listdom'); ?></li>
                        </ol>

                        <p><?php esc_html_e("All pricing for Listdom features (like Booking, Claim, Labelize, etc.) must be mapped to WooCommerce products. Orders will be handled by WooCommerce and visible under WooCommerce → Orders.", 'listdom'); ?></p>
                        <p><?php esc_html_e("You can switch to Listdom's internal payment system at any time if you prefer a lightweight alternative without WooCommerce.", 'listdom'); ?></p>
                    </div>
                </div>
                <div id="lsd-payments-listdom-form-container" class="<?php echo $engine->listdom() ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-alert lsd-natural lsd-m-0">
                        <p class="lsd-mt-2 lsd-mb-0"><?php esc_html_e("You're using", 'listdom'); ?></p>
                        <h3 class="lsd-mt-0"><?php esc_html_e("Listdom Payment Engine", 'listdom'); ?></h3>

                        <p><?php esc_html_e("In this mode, Listdom handles all payments directly — no need to install or configure WooCommerce.", 'listdom'); ?></p>
                        <p><?php esc_html_e("Please make sure to:", 'listdom'); ?></p>

                        <ol>
                            <li><?php esc_html_e("Choose and enable your desired payment gateways in Listdom → Settings → Payments", 'listdom'); ?></li>
                            <li><?php esc_html_e("Configure manual payment instructions (for On-Site and Wire Transfer)", 'listdom'); ?></li>
                            <li><?php esc_html_e("Set your global currency under Listdom → Settings → Payments and manage tax rates in Listdom → Taxes", 'listdom'); ?></li>
                            <li><?php esc_html_e("Define and manage discount codes under Listdom → Coupons", 'listdom'); ?></li>
                        </ol>

                        <p><?php esc_html_e("All transactions will be tracked inside Listdom as internal Orders and optionally linked to Bookings or Claims depending on the feature. Admins can manually confirm offline payments, view orders in Listdom → Orders, and enable email notifications.", 'listdom'); ?></p>
                        <p><?php esc_html_e("You can switch back to WooCommerce anytime if you prefer a full eCommerce suite with more advanced features.", 'listdom'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div id="lsd_panel_payments_options" class="lsd-payments-form-group lsd-tab-content <?php echo $this->subtab === 'options' ? ' lsd-tab-content-active' : ''; ?>">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Options', 'listdom'); ?></h3>
            <div class="lsd-settings-group-wrapper">
                <div class="lsd-settings-fields-wrapper">
                    <h4 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Pages', 'listdom'); ?></h4>
                    <div class="lsd-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Checkout Page', 'listdom'),
                            'for' => 'lsd_payments_checkout_page',
                        ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::pages([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_payments_checkout_page',
                                'name' => 'lsd[payments][checkout_page]',
                                'value' => $payments['checkout_page'] ?? null,
                                'show_empty' => true,
                            ]); ?>
                            <div class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0">
                                <p class="lsd-mb-2"><?php esc_html_e('Use the [listdom-checkout] shortcode to display the Listdom checkout page.', 'listdom'); ?></p>
                                <p class="lsd-mb-2"><?php esc_html_e('It supports an optional style parameter to control the layout:', 'listdom'); ?></p>
                                <p class="lsd-mb-1"><strong><?php esc_html_e('Default (cards layout):', 'listdom'); ?></strong><br><code>[listdom-checkout]</code></p>
                                <p class="lsd-mb-1"><strong><?php esc_html_e('Compact layout:', 'listdom'); ?></strong><br><code>[listdom-checkout style="compact"]</code></p>
                                <p class="lsd-mb-0"><?php esc_html_e('If no style is set, the default cards layout is used.', 'listdom'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lsd-settings-fields-wrapper">
                    <h4 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Appreciation', 'listdom'); ?></h4>
                    <div class="lsd-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Page', 'listdom'),
                            'for' => 'lsd_payments_appreciation_page',
                        ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::pages([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_payments_appreciation_page',
                                'name' => 'lsd[payments][appreciation_page]',
                                'value' => $payments['appreciation_page'] ?? '',
                                'show_empty' => true,
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Default page users are redirected to after payment.', 'listdom'); ?></p>
                            <div class="lsd-alert lsd-info lsd-mt-2">
                                <?php printf(esc_html__('You can use the %s shortcode in the appreciation page to display the order summary.', 'listdom'), '<code>[listdom-order-summary]</code>'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="lsd-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Appreciation Message', 'listdom'),
                            'for' => 'lsd_payments_appreciation_message',
                        ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::editor([
                                'id' => 'lsd_payments_appreciation_message',
                                'name' => 'lsd[payments][appreciation_message]',
                                'value' => $payments['appreciation_message'] ?? '',
                                'rows' => 4,
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Message shown after payment if gateway does not set one.', 'listdom'); ?></p>
                        </div>
                    </div>
                    <div class="lsd-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Invoice Button', 'listdom'),
                            'for' => 'lsd_payments_appreciation_invoice_button',
                        ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::select([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_payments_appreciation_invoice_button',
                                'name' => 'lsd[payments][appreciation_invoice_button]',
                                'options' => [
                                    '1' => esc_html__('Append to the message', 'listdom'),
                                    '0' => esc_html__('Do not append', 'listdom'),
                                ],
                                'value' => $payments['appreciation_invoice_button'] ?? '1',
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="lsd-settings-fields-wrapper">
                    <h4 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Content', 'listdom'); ?></h4>
                    <div class="lsd-row">
                        <div class="lsd-col-3 lsd-pt-2"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Agreement', 'listdom'),
                            'for' => 'lsd_payments_agreement',
                        ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::editor([
                                'id' => 'lsd_payments_agreement',
                                'name' => 'lsd[payments][agreement]',
                                'value' => $payments['agreement'] ?? '',
                                'rows' => 4,
                                'media_buttons' => false,
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Displayed on the checkout page as terms customers must accept.', 'listdom'); ?></p>
                        </div>
                    </div>
                    <div class="lsd-row">
                        <div class="lsd-col-3 lsd-pt-2"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Free Checkout Comment', 'listdom'),
                            'for' => 'lsd_payments_free_checkout_comment',
                        ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::editor([
                                'id' => 'lsd_payments_free_checkout_comment',
                                'name' => 'lsd[payments][free_checkout_comment]',
                                'value' => $payments['free_checkout_comment'] ?? '',
                                'rows' => 4,
                                'media_buttons' => false,
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Shown above the free checkout button to inform users.', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="lsd-settings-fields-wrapper">
                    <h4 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Empty Cart', 'listdom'); ?></h4>
                    <div class="lsd-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Image', 'listdom'),
                                'for' => 'lsd_payments_empty_logo',
                            ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::imagepicker([
                                'class' => 'lsd-neutral-button',
                                'id' => 'lsd_payments_empty_logo',
                                'name' => 'lsd[payments][empty][logo]',
                                'value' => $empty_settings['logo'] ?? '',
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Select the image displayed above the empty cart message.', 'listdom'); ?></p>
                        </div>
                    </div>
                    <h4 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Content', 'listdom'); ?></h4>
                    <div class="lsd-row">
                        <div class="lsd-col-3 lsd-pt-2"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Cart Content', 'listdom'),
                                'for' => 'lsd_payments_empty_content',
                            ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::editor([
                                'id' => 'lsd_payments_empty_content',
                                'name' => 'lsd[payments][empty][content]',
                                'value' => $empty_settings['content'] ?? '',
                                'rows' => 6,
                                'media_buttons' => false,
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Displayed when the cart is empty to guide users on what to do next.', 'listdom'); ?></p>
                        </div>
                    </div>
                    <div class="lsd-row">
                        <div class="lsd-col-3 lsd-pt-2"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Show Buttons', 'listdom'),
                                'for' => 'lsd_payments_empty_buttons',
                            ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::switcher([
                                'id' => 'lsd_payments_empty_buttons',
                                'name' => 'lsd[payments][empty][show_buttons]',
                                'value' => $empty_settings['show_buttons'] ?? 1,
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Toggle the dashboard and homepage buttons shown beneath the message.', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="lsd-settings-fields-wrapper">
                    <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Privacy Consent', 'listdom'); ?></h3>
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Enable', 'listdom'),
                                'for' => 'lsd_payments_pc_enabled',
                            ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::switcher([
                                'id' => 'lsd_payments_pc_enabled',
                                'name' => 'lsd[payments][pc_enabled]',
                                'value' => $payments['pc_enabled'] ?? null,
                                'toggle' => '#lsd_payments_pc_label_row',
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('This control also depends on the global privacy consent setting.', 'listdom'); ?></p>
                        </div>
                    </div>
                    <div class="lsd-form-row<?php echo empty($payments['pc_enabled']) ? ' lsd-util-hide' : ''; ?>" id="lsd_payments_pc_label_row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Label', 'listdom'),
                                'for' => 'lsd_payments_pc_label',
                            ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::text([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_payments_pc_label',
                                'name' => 'lsd[payments][pc_label]',
                                'value' => $payments['pc_label'] ?? '',
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Leave empty to use the default label. Use {{privacy_policy}} to automatically include the default privacy policy page link.', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="lsd_panel_payments_invoice" class="lsd-payments-form-group lsd-tab-content <?php echo $this->subtab === 'invoice' ? ' lsd-tab-content-active' : ''; ?>">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Invoice', 'listdom'); ?></h3>

            <div class="lsd-settings-group-wrapper">
                <div class="lsd-settings-fields-wrapper">
                    <h4 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Logo', 'listdom'); ?></h4>
                    <div class="lsd-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Invoice Logo', 'listdom'),
                                'for' => 'lsd_payments_invoice_logo',
                            ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::imagepicker([
                                'class' => 'lsd-neutral-button',
                                'id' => 'lsd_payments_invoice_logo',
                                'name' => 'lsd[payments][invoice][logo]',
                                'value' => $invoice_settings['logo'] ?? '',
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Select the logo that appears at the top of the invoice.', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="lsd-settings-fields-wrapper">
                    <h4 class="lsd-admin-title lsd-m-0"><?php esc_html_e('From Information', 'listdom'); ?></h4>
                    <div class="lsd-row">
                        <div class="lsd-col-3 lsd-pt-2"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Invoice From Details', 'listdom'),
                                'for' => 'lsd_payments_invoice_from',
                            ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::textarea([
                                'id' => 'lsd_payments_invoice_from',
                                'name' => 'lsd[payments][invoice][from]',
                                'class' => 'lsd-admin-input',
                                'rows' => 5,
                                'value' => $invoice_settings['from'] ?? '',
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Provide the business name and contact information shown under "Invoice From". Use a new line for each detail.', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="lsd-settings-fields-wrapper">
                    <h4 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Footer', 'listdom'); ?></h4>
                    <div class="lsd-row">
                        <div class="lsd-col-3 lsd-pt-2"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Additional Information', 'listdom'),
                                'for' => 'lsd_payments_invoice_footer',
                            ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::editor([
                                'id' => 'lsd_payments_invoice_footer',
                                'name' => 'lsd[payments][invoice][footer]',
                                'value' => $invoice_settings['footer'] ?? '',
                                'rows' => 6,
                                'media_buttons' => false,
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('This content appears in the "Additional Information" section of the invoice.', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="lsd_panel_payments_taxes" class="lsd-payments-form-group lsd-tab-content <?php echo $this->subtab === 'taxes' ? ' lsd-tab-content-active' : ''; ?>">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Tax', 'listdom'); ?></h3>

            <div class="lsd-settings-group-wrapper">
                <div class="lsd-settings-fields-wrapper">
                    <div class="lsd-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Enable', 'listdom'),
                            'for' => 'lsd_tax_enable',
                        ]); ?></div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::switcher([
                                'id' => 'lsd_tax_enable',
                                'name' => 'lsd[payments][taxes][enable]',
                                'value' => $tax_settings['enable'] ?? 0,
                                'toggle' => '#lsd-tax-settings-fields',
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Activate Tax calculations for Listdom payments.', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>

                <div id="lsd-tax-settings-fields" class="lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-3 <?php echo $tax_settings['enable'] ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-settings-fields-wrapper">
                        <h4 class="lsd-admin-title lsd-m-0"><?php esc_html_e('General', 'listdom'); ?></h4>

                        <div class="lsd-row">
                            <div class="lsd-col-3 lsd-pt-2">
                                <?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Tax Calculation', 'listdom'),
                                    'for' => 'lsd_tax_prices_include',
                                ]); ?>
                            </div>
                            <div class="lsd-col-6">
                                <?php echo LSD_Form::select([
                                    'id' => 'lsd_tax_prices_include',
                                    'class' => 'lsd-admin-input',
                                    'name' => 'lsd[payments][taxes][prices_include_tax]',
                                    'value' => $tax_settings['prices_include_tax'] ?? 0,
                                    'options' => [
                                        1 => esc_html__('Prices include tax', 'listdom'),
                                        0 => esc_html__('Prices exclude tax', 'listdom'),
                                    ],
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Determines whether tax is added on top of prices or extracted from them.', 'listdom'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="lsd-row lsd-align-center">
                            <div class="lsd-col-3">
                                <label class="lsd-fields-label"><?php esc_html_e('Location Rates', 'listdom'); ?></label>
                            </div>
                            <div class="lsd-col-6">
                                <p class="lsd-admin-description-tiny lsd-mt-0 lsd-mb-2">
                                    <?php
                                    printf(
                                    /* translators: %s is the Tax settings link */
                                        esc_html__(
                                            'Manage country/state tax rates under Listdom > Payments > %s.',
                                            'listdom'
                                        ),
                                        '<a href="' . esc_url(
                                            admin_url(
                                                'edit-tags.php?taxonomy=' . LSD_Base::TAX_TAX . '&post_type=' . LSD_Base::PTYPE_ORDER
                                            )
                                        ) . '">' . esc_html__('Tax', 'listdom') . '</a>'
                                    );
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="lsd-settings-fields-wrapper">
                        <h4 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Default Tax', 'listdom'); ?></h4>

                        <div class="lsd-row">
                            <div class="lsd-col-3">
                                <?php echo LSD_Form::label([
                                    'id' => 'lsd_tax_default_rate',
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Default Tax Rate (%)', 'listdom'),
                                    'for' => 'lsd_tax_default_rate',
                                ]); ?>
                            </div>
                            <div class="lsd-col-6">
                                <?php echo LSD_Form::number([
                                    'id' => 'lsd_tax_default_rate',
                                    'class' => 'lsd-admin-input',
                                    'name' => 'lsd[payments][taxes][rate]',
                                    'value' => $tax_settings['rate'] ?? 0,
                                    'attributes' => [
                                        'step' => '0.01',
                                        'min'  => '0',
                                    ],
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Used when no specific country/state rate matches.', 'listdom'); ?></p>
                            </div>
                        </div>

                        <div class="lsd-row">
                            <div class="lsd-col-3">
                                <?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Label', 'listdom'),
                                    'for' => 'lsd_tax_label',
                                ]); ?>
                            </div>
                            <div class="lsd-col-6">
                                <?php
                                echo LSD_Form::input([
                                    'id' => 'lsd_tax_label',
                                    'class' => 'lsd-admin-input',
                                    'name' => 'lsd[payments][taxes][label]',
                                    'value' => $tax_settings['label'] ?? (new LSD_Payments_Tax())->label(),
                                    'placeholder' => esc_attr__('Tax, VAT, GST, etc.', 'listdom'),
                                ]);
                                ?>
                                <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('How tax appears on checkout, orders, and invoices.', 'listdom'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($engine->listdom()): ?>
        <div id="lsd_panel_payments_gateways" class="lsd-payments-form-group lsd-tab-content <?php echo $this->subtab === 'gateways' ? ' lsd-tab-content-active' : ''; ?>">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Gateways', 'listdom'); ?></h3>

            <div class="lsd-settings-group-wrapper">
                <?php foreach (LSD_Payments::gateways() as $gateway): if ($gateway->is_system()) continue; ?>
                <div class="lsd-settings-fields-wrapper">
                    <h4 class="lsd-admin-title lsd-m-0"><?php echo esc_html($gateway->label()); ?></h4>
                    <?php echo LSD_Kses::full($gateway->form_options()); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="lsd-spacer-10"></div>
        <div class="lsd-row lsd-settings-submit-wrapper">
			<div class="lsd-col-12 lsd-flex lsd-flex-content-end">
				<?php LSD_Form::nonce('lsd_payments_form'); ?>
                <button type="submit" id="lsd_payments_save_button" class="lsd-primary-button">
                    <?php esc_html_e('Save The Changes', 'listdom'); ?>
                    <i class='lsdi lsdi-checkmark-circle'></i>
                </button>
			</div>
        </div>
    </form>
</div>
<script>
// Save
jQuery('#lsd_payments_form').on('submit', function (event)
{
    event.preventDefault();

    if (typeof tinyMCE !== 'undefined') tinyMCE.triggerSave();

    // Loading Wrapper
    const $button = jQuery('#lsd_payments_save_button');
    const $tab = jQuery('.nav-tab-active');

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( __('Saving', 'listdom') ); ?>");

    // Engine Changes
    const engine_changed = (jQuery('#lsd_payments_system').is(':checked') ? 1 : 0) !== <?php echo $is_listdom; ?>;

    const settings = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_payments&" + settings,
        success: function ()
        {
            $tab.attr('data-saved', 'true');

            listdom_toastify("<?php echo esc_js(__('Options saved successfully.', 'listdom')); ?>", 'lsd-success', {
                hideTime: engine_changed ? 1000 : 8000
            });

            // Unloading
            loading.stop();

            // Reload the Page if Payment Engine is changed
            setTimeout(() =>
            {
                engine_changed && window.location.reload();
            }, 1000);
        },
        error: function ()
        {
            $tab.attr('data-saved', 'false');

            listdom_toastify("<?php echo esc_js(__('Error: Unable to save options.', 'listdom')); ?>", 'lsd-error');

            // Unloading
            loading.stop();
        }
    });
});
</script>
