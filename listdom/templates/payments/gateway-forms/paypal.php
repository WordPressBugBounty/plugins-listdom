<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Payments_Gateways_Paypal $this */
/** @var LSD_Cart $cart */

$nonce = wp_create_nonce('lsd_checkout');
$options = $this->options();
$currency = LSD_Options::currency();
$cart = new LSD_Cart();
[$total, $discount, $tax] = $cart->apply_coupon();
$items = $cart->get_items();
$cart_id = isset($_COOKIE['lsd_cart_id']) ? sanitize_text_field(wp_unslash($_COOKIE['lsd_cart_id'])) : '';
$capture_key = md5($cart_id);
$order_title = wp_date('Y-m-d H:i:s');
if ($items)
{
    $first = reset($items);
    $plan_id = (int)($first['plan_id'] ?? 0);
    $tier_id = $first['tier_id'] ?? '';
    $plan = new LSD_Payments_Plan($plan_id, $tier_id);
    $plan_name = get_the_title($plan_id);
    $tier = $plan->get_tier();
    $tier_name = $tier ? $tier->get_name() : '';

    $order_title = $plan_name;
    if ($tier_name) $order_title .= ' - ' . $tier_name;
}
?>
<div class="lsd-gateway-wrapper">
    <div class="lsd-fe-section-heading">
        <div class="lsd-gateway-name lsd-fe-title"><?php echo esc_html($this->name()); ?></div>
        <?php if ($this->comment()): ?>
            <div class="lsd-gateway-comment lsd-fe-description"><?php echo LSD_Kses::element($this->comment()); ?></div>
        <?php endif; ?>
    </div>
    <?php echo LSD_Kses::form($this->form_checkout_user()); ?>

    <?php echo LSD_Privacy::consent_field([
        'id' => 'lsd_checkout_consent_' . uniqid(),
        'name' => 'lsd_privacy_consent',
        'class' => 'lsd-privacy-consent-checkbox lsd-checkout-consent-input',
        'wrapper_class' => 'lsd-checkout-consent',
        'context' => 'checkout',
        'required_message' => LSD_Privacy::consent_required_text(),
    ]); ?>

    <div id="lsd-paypal-button-container"></div>
    <div class="lsd-checkout-response"></div>
</div>
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo esc_attr($options['client_id'] ?? ''); ?>&currency=<?php echo esc_attr($currency); ?>" async></script>
<script>
(function ($)
{
    const lsdPaypalInterval = setInterval(function ()
    {
        if (typeof paypal === 'undefined') return;

        clearInterval(lsdPaypalInterval);

        const $gatewayForm = $('#lsd-gateway-form-paypal');
        const $wrapper = $gatewayForm.find('.lsd-gateway-wrapper').first();
        if (!$wrapper.length) return;

        const failureMessage = '<?php echo esc_js(esc_html__('Unable to complete checkout. Please try again.', 'listdom')); ?>';
        const ajaxUrl = typeof lsd !== 'undefined' ? lsd.ajaxurl : '';
        const showResponse = function (message)
        {
            const text = message || failureMessage;
            const $response = $wrapper.find('.lsd-checkout-response');

            if (typeof listdom_alertify === 'function') $response.html(listdom_alertify(text, 'lsd-error'));
            else $response.text(text);
        };

        const storage = (function ()
        {
            try
            {
                const key = 'lsdPaypalStorageTest';
                window.sessionStorage.setItem(key, key);
                window.sessionStorage.removeItem(key);
                return window.sessionStorage;
            }
            catch (err)
            {
                return null;
            }
        })();

        const consentStorageKey = 'lsdPaypalConsent';
        const getStoredConsent = function ()
        {
            if (!storage) return '';
            return storage.getItem(consentStorageKey) === '1' ? '1' : '';
        };

        const storeConsent = function (consent)
        {
            if (!storage) return;

            try
            {
                if (consent === '1')
                {
                    storage.setItem(consentStorageKey, '1');
                }
                else
                {
                    storage.removeItem(consentStorageKey);
                }
            }
            catch (err)
            {
            }
        };

        const validateConsent = function ()
        {
            const consentEl = $wrapper.find('input[name="lsd_privacy_consent"]').get(0);
            if (!consentEl || !consentEl.required) return true;

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

                return false;
            }

            consentEl.setCustomValidity('');
            storeConsent('1');
            return true;
        };

        const capturedOrderStorageKey = 'lsd-paypal-captured-order-<?php echo esc_js($capture_key); ?>';
        const capturedOrderCookieName = 'lsd_paypal_captured_order_<?php echo esc_js($capture_key); ?>';
        let capturedOrderId = '';
        const capturedPaymentMessage = '<?php echo esc_js(__('Your PayPal payment was captured, but checkout confirmation is pending. Please contact the site administrator with your PayPal order ID.', 'listdom')); ?>';

        const getCapturedOrderCookie = function ()
        {
            const prefix = capturedOrderCookieName + '=';
            const cookies = document.cookie ? document.cookie.split(';') : [];

            for (let index = 0; index < cookies.length; index++)
            {
                const cookie = cookies[index].trim();
                if (cookie.indexOf(prefix) !== 0) continue;

                return decodeURIComponent(cookie.substring(prefix.length));
            }

            return '';
        };

        const storeCapturedOrderCookie = function (orderId)
        {
            const value = orderId ? encodeURIComponent(orderId) : '';
            const expires = orderId ? '; max-age=86400' : '; max-age=0';
            document.cookie = capturedOrderCookieName + '=' + value + expires + '; path=/; SameSite=Lax';
        };

        try
        {
            capturedOrderId = window.sessionStorage.getItem(capturedOrderStorageKey) || getCapturedOrderCookie();
        }
        catch (err)
        {
            capturedOrderId = getCapturedOrderCookie();
        }

        const storeCapturedOrderId = function (orderId)
        {
            capturedOrderId = orderId || '';
            storeCapturedOrderCookie(capturedOrderId);

            try
            {
                if (capturedOrderId !== '') window.sessionStorage.setItem(capturedOrderStorageKey, capturedOrderId);
                else window.sessionStorage.removeItem(capturedOrderStorageKey);
            }
            catch (err)
            {
            }
        };

        paypal.Buttons({
            style: {
                disableMaxWidth: true
            },
            onClick: function (data, actions)
            {
                if (capturedOrderId !== '')
                {
                    showResponse(capturedPaymentMessage + ' ' + capturedOrderId);
                    return actions && typeof actions.reject === 'function' ? actions.reject() : false;
                }

                if (!validateConsent())
                {
                    return actions && typeof actions.reject === 'function' ? actions.reject() : false;
                }

                return actions && typeof actions.resolve === 'function' ? actions.resolve() : true;
            },
            createOrder: function (data, actions)
            {
                return actions.order.create({
                    purchase_units: [{
                        description: '<?php echo esc_js($order_title); ?>',
                        amount: {
                            value: '<?php echo esc_js(number_format($total, 2, '.', '')); ?>',
                            currency_code: '<?php echo esc_js($currency); ?>'
                        }
                    }]
                });
            },
            onApprove: function (data, actions)
            {
                if (!ajaxUrl)
                {
                    showResponse(failureMessage);
                    return;
                }

                return actions.order.capture().then(function (orderData)
                {
                    if (orderData.status === 'COMPLETED')
                    {
                        storeCapturedOrderId(orderData.id || '');
                        const name = $wrapper.find('.lsd-checkout-user-name').val() || '';
                        const email = $wrapper.find('.lsd-checkout-user-email').val() || '';

                        const consentValue = $wrapper.find('input[name="lsd_privacy_consent"]').is(':checked')
                            ? '1'
                            : getStoredConsent();

                        $.ajax({
                            url: ajaxUrl,
                            type: 'post',
                            dataType: 'json',
                            data: {
                                action: 'lsd_checkout',
                                _wpnonce: '<?php echo esc_js($nonce); ?>',
                                gateway: '<?php echo esc_js($this->key()); ?>',
                                paypal_order_id: orderData.id,
                                name: name,
                                email: email,
                                lsd_privacy_consent: consentValue
                            },
                            success: function (res)
                            {
                                if (res && res.success)
                                {
                                    storeCapturedOrderId('');
                                    storeConsent('');
                                    lsdCheckoutComplete(res.key ? res.key : res.order_id);
                                }
                                else showResponse(capturedPaymentMessage + ' ' + capturedOrderId);
                            },
                            error: function ()
                            {
                                showResponse(capturedPaymentMessage + ' ' + capturedOrderId);
                            }
                        });
                    }
                    else showResponse(failureMessage);
                }).catch(function ()
                {
                    showResponse(failureMessage);
                });
            },
            onError: function ()
            {
                showResponse(failureMessage);
            }
        }).render('#lsd-paypal-button-container');
    }, 100);
})(jQuery);
</script>
