<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Payments_Gateways_Stripe $this */

$nonce = wp_create_nonce('lsd_checkout');
$options = $this->options();
$intent = $this->createIntent();
$intent_client_secret = is_array($intent) ? ($intent['client_secret'] ?? '') : '';
$intent_mode = is_array($intent) ? ($intent['mode'] ?? 'payment') : 'payment';
$checkout_init_notice = !$intent_client_secret ? $this->checkout_init_notice() : '';

if ($intent_client_secret)
{
    // Include Stripe JS only when checkout can be initialized.
    wp_enqueue_script('stripe', 'https://js.stripe.com/v3/');
}
?>
<div class="lsd-gateway-wrapper" id="lsd-stripe-wrapper">
    <div class="lsd-fe-section-heading">
        <div class="lsd-gateway-name lsd-fe-title"><?php echo esc_html($this->name()); ?></div>
        <?php if ($this->comment()): ?>
            <div class="lsd-gateway-comment lsd-fe-description"><?php echo LSD_Kses::element($this->comment()); ?></div>
        <?php endif; ?>
    </div>
    <?php if ($checkout_init_notice): ?>
        <div class="lsd-alert lsd-error" role="alert"><?php echo esc_html($checkout_init_notice); ?></div>
    <?php else: ?>
        <?php echo LSD_Kses::form($this->form_checkout_user()); ?>
        <div class="lsd-flex lsd-flex-col lsd-gap-4">
            <?php if (isset($options['link']) && $options['link']): ?>
                <div id="lsd-stripe-link-auth"></div>
            <?php endif; ?>
            <div id="lsd-stripe-payment-element"></div>
            <?php if (isset($options['address']) && $options['address']): ?>
                <div id="lsd-stripe-address-element"></div>
            <?php endif; ?>
            <div class="lsd-alert" id="lsd-stripe-errors" role="alert"></div>
        </div>

        <?php echo LSD_Privacy::consent_field([
            'id' => 'lsd_checkout_consent_' . uniqid(),
            'name' => 'lsd_privacy_consent',
            'class' => 'lsd-privacy-consent-checkbox lsd-checkout-consent-input',
            'wrapper_class' => 'lsd-checkout-consent',
            'context' => 'checkout',
            'required_message' => LSD_Privacy::consent_required_text(),
        ]); ?>

        <button type="button" class="lsd-button lsd-stripe-button lsd-general-button"><?php esc_html_e('Checkout', 'listdom'); ?></button>
        <div class="lsd-checkout-response"></div>
    <?php endif; ?>
</div>
<?php if ($intent_client_secret): ?>
    <script>
        (function($)
        {
            const lsdStripeInterval = setInterval(function ()
            {
                if (typeof Stripe === 'undefined') return;

                clearInterval(lsdStripeInterval);

                const stripe = Stripe('<?php echo esc_js($options['publishable_key'] ?? ''); ?>');
                const elements = stripe.elements({clientSecret: '<?php echo esc_js($intent_client_secret); ?>'});

                const payment = elements.create('payment', {});
                payment.mount('#lsd-stripe-payment-element');

                <?php if (isset($options['address']) && $options['address']): ?>
                const address = elements.create('address', {mode: '<?php echo esc_js($options['address']); ?>'});
                address.mount('#lsd-stripe-address-element');
                <?php endif; ?>

                <?php if (isset($options['link']) && $options['link']): ?>
                const linkAuth = elements.create('linkAuthentication');
                linkAuth.mount('#lsd-stripe-link-auth');
                <?php endif; ?>

                const $wrapper = $('#lsd-stripe-wrapper');
                const $btn = $wrapper.find('.lsd-stripe-button');
                const $alert = $wrapper.find('#lsd-stripe-errors');
                $alert.addClass('lsd-util-hide');

                const intentMode = '<?php echo esc_js($intent_mode); ?>';

                const storage = (function ()
                {
                    try
                    {
                        const key = 'lsdStripeStorageTest';
                        window.sessionStorage.setItem(key, key);
                        window.sessionStorage.removeItem(key);
                        return window.sessionStorage;
                    }
                    catch (err)
                    {
                        return null;
                    }
                })();

                const storageKeys = {
                    name: 'lsdStripeName',
                    email: 'lsdStripeEmail',
                    consent: 'lsdStripeConsent',
                };

                const failureMessage = '<?php echo esc_js(esc_html__('We could not verify your payment. Please try again.', 'listdom')); ?>';

                const getStoredCustomer = function ()
                {
                    if (!storage) return {name: '', email: ''};

                    return {
                        name: storage.getItem(storageKeys.name) || '',
                        email: storage.getItem(storageKeys.email) || '',
                    };
                };

                const saveStoredCustomer = function (name, email)
                {
                    if (!storage) return;

                    try
                    {
                        storage.setItem(storageKeys.name, name);
                        storage.setItem(storageKeys.email, email);
                    }
                    catch (err)
                    {
                    }
                };

                const getStoredConsent = function ()
                {
                    if (!storage) return '';

                    return storage.getItem(storageKeys.consent) === '1' ? '1' : '';
                };

                const saveStoredConsent = function (consent)
                {
                    if (!storage) return;

                    try
                    {
                        if (consent === '1')
                        {
                            storage.setItem(storageKeys.consent, '1');
                        }
                        else
                        {
                            storage.removeItem(storageKeys.consent);
                        }
                    }
                    catch (err)
                    {
                    }
                };

                const clearStoredCustomer = function ()
                {
                    if (!storage) return;

                    try
                    {
                        storage.removeItem(storageKeys.name);
                        storage.removeItem(storageKeys.email);
                        storage.removeItem(storageKeys.consent);
                    }
                    catch (err)
                    {
                    }
                };

                const clearStripeParams = function ()
                {
                    if (typeof URL === 'undefined' || !window.history.replaceState) return;

                    try
                    {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('payment_intent');
                        url.searchParams.delete('payment_intent_client_secret');
                        url.searchParams.delete('setup_intent');
                        url.searchParams.delete('setup_intent_client_secret');
                        url.searchParams.delete('redirect_status');
                        window.history.replaceState({}, document.title, url.toString());
                    }
                    catch (err)
                    {
                    }
                };

                const populateStoredValues = function ()
                {
                    const customer = getStoredCustomer();

                    if (customer.name)
                    {
                        $wrapper.find('.lsd-checkout-user-name').val(customer.name);
                    }

                    if (customer.email)
                    {
                        $wrapper.find('.lsd-checkout-user-email').val(customer.email);
                    }
                };

                const finalizeCheckout = function (intentId, customer, extra)
                {
                    const details = customer || {};
                    const name = details.name || '';
                    const email = details.email || '';
                    const additional = extra || {};

                    const consentValue = $wrapper.find('input[name="lsd_privacy_consent"]').is(':checked')
                        ? '1'
                        : getStoredConsent();

                    const data = {
                        action: 'lsd_checkout',
                        _wpnonce: '<?php echo esc_js($nonce); ?>',
                        gateway: '<?php echo esc_js($this->key()); ?>',
                        name: name,
                        email: email,
                        lsd_privacy_consent: consentValue,
                    };

                    if (intentMode === 'setup')
                    {
                        data.setup_intent = intentId;
                        data.payment_method = additional.paymentMethod || '';
                    }
                    else
                    {
                        data.payment_intent = intentId;
                    }

                    $.ajax({
                        url: lsd.ajaxurl,
                        type: 'post',
                        dataType: 'json',
                        data: data,
                        success: function (res)
                        {
                            if (res && res.success)
                            {
                                clearStoredCustomer();
                                $btn.removeClass('lsd-loading');
                                lsdCheckoutComplete(res.key ? res.key : res.order_id);
                            }
                            else if (res && res.message)
                            {
                                lsdCheckoutRestoreFromProcessing();
                                $wrapper.find('.lsd-checkout-response').html(res.message);
                                $btn.prop('disabled', false).removeClass('lsd-loading');
                            }
                            else
                            {
                                lsdCheckoutRestoreFromProcessing();
                                $btn.prop('disabled', false).removeClass('lsd-loading');
                            }
                        },
                        error: function (xhr, status, error)
                        {
                            $alert.text(error).removeClass('lsd-util-hide').addClass('lsd-error');
                            lsdCheckoutRestoreFromProcessing();
                            $btn.prop('disabled', false).removeClass('lsd-loading');
                        }
                    });
                };

                const handleStripeReturn = async function ()
                {
                    if (typeof URLSearchParams === 'undefined') return;

                    const params = new URLSearchParams(window.location.search);
                    const paymentClientSecret = params.get('payment_intent_client_secret');
                    const setupClientSecret = params.get('setup_intent_client_secret');
                    const hasClientSecret = intentMode === 'setup' ? setupClientSecret : paymentClientSecret;
                    if (!hasClientSecret) return;

                    lsdCheckoutShowProcessing();
                    clearStripeParams();

                    $alert.removeClass('lsd-error').addClass('lsd-util-hide').text('');
                    $btn.prop('disabled', true).addClass('lsd-loading');

                    try
                    {
                        if (intentMode === 'setup')
                        {
                            const result = await stripe.retrieveSetupIntent(setupClientSecret);
                            const setupIntent = result.setupIntent;

                            if (setupIntent && setupIntent.status === 'succeeded')
                            {
                                finalizeCheckout(setupIntent.id, getStoredCustomer(), {paymentMethod: setupIntent.payment_method});
                                return;
                            }

                            const message = (result.error && result.error.message)
                                ? result.error.message
                                : failureMessage;
                            $alert.text(message).removeClass('lsd-util-hide').addClass('lsd-error');
                            lsdCheckoutRestoreFromProcessing();
                            $btn.prop('disabled', false).removeClass('lsd-loading');
                            return;
                        }

                        const result = await stripe.retrievePaymentIntent(paymentClientSecret);

                        const paymentIntent = result.paymentIntent;
                        if (!paymentIntent)
                        {
                            const message = result.error && result.error.message ? result.error.message : failureMessage;
                            if (message)
                            {
                                $alert.text(message).removeClass('lsd-util-hide').addClass('lsd-error');
                            }
                            lsdCheckoutRestoreFromProcessing();
                            $btn.prop('disabled', false).removeClass('lsd-loading');
                            return;
                        }

                        if (['succeeded', 'processing'].includes(paymentIntent.status))
                        {
                            finalizeCheckout(paymentIntent.id, getStoredCustomer());
                            return;
                        }

                        let message = failureMessage;
                        if (paymentIntent.last_payment_error && paymentIntent.last_payment_error.message)
                        {
                            message = paymentIntent.last_payment_error.message;
                        }

                        $alert.text(message).removeClass('lsd-util-hide').addClass('lsd-error');
                        lsdCheckoutRestoreFromProcessing();
                        $btn.prop('disabled', false).removeClass('lsd-loading');
                    }
                    catch (err)
                    {
                        const message = err && err.message ? err.message : failureMessage;
                        $alert.text(message).removeClass('lsd-util-hide').addClass('lsd-error');
                        lsdCheckoutRestoreFromProcessing();
                        $btn.prop('disabled', false).removeClass('lsd-loading');
                    }
                };

                populateStoredValues();
                handleStripeReturn();

                payment.addEventListener('change', function ()
                {
                    $alert.removeClass('lsd-error').addClass('lsd-util-hide').text('');
                    $btn.prop('disabled', false);
                });

                $btn.on('click', async function (e)
                {
                    e.preventDefault();

                    $alert.removeClass('lsd-error').addClass('lsd-util-hide').text('');
                    $btn.prop('disabled', true).addClass('lsd-loading');

                    const consentEl = $wrapper.find('input[name="lsd_privacy_consent"]').get(0);
                    if (consentEl && consentEl.required)
                    {
                        if (!consentEl.checked)
                        {
                            if (typeof consentEl.reportValidity === 'function')
                            {
                                consentEl.reportValidity();
                            }
                            else
                            {
                                consentEl.focus();
                            }

                            $btn.prop('disabled', false).removeClass('lsd-loading');
                            return;
                        }

                        consentEl.setCustomValidity('');
                    }

                    const name = $wrapper.find('.lsd-checkout-user-name').val() || '';
                    const email = $wrapper.find('.lsd-checkout-user-email').val() || '';
                    const consentValue = $wrapper.find('input[name="lsd_privacy_consent"]').is(':checked') ? '1' : '';

                    saveStoredCustomer(name, email);
                    saveStoredConsent(consentValue);

                    if (intentMode === 'setup')
                    {
                        const { error, setupIntent } = await stripe.confirmSetup({
                            elements,
                            confirmParams: {return_url: window.location.href}
                        });

                        if (error)
                        {
                            const message = error.message ? error.message : failureMessage;
                            $alert.text(message).removeClass('lsd-util-hide').addClass('lsd-error');
                            $btn.prop('disabled', false).removeClass('lsd-loading');
                            return;
                        }

                        if (setupIntent)
                        {
                            finalizeCheckout(setupIntent.id, {name: name, email: email}, {paymentMethod: setupIntent.payment_method});
                        }

                        return;
                    }

                    const { error, paymentIntent } = await stripe.confirmPayment({
                        elements,
                        confirmParams: {return_url: window.location.href},
                        redirect: 'if_required'
                    });

                    if (error)
                    {
                        const message = error.message ? error.message : failureMessage;
                        $alert.text(message).removeClass('lsd-util-hide').addClass('lsd-error');
                        $btn.prop('disabled', false).removeClass('lsd-loading');
                        return;
                    }

                    if (paymentIntent)
                    {
                        finalizeCheckout(paymentIntent.id, {name: name, email: email});
                    }
                });
            }, 100);
        })(jQuery);
    </script>
<?php endif;
