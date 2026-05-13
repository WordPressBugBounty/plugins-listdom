<?php

class LSD_Shortcodes_Checkout extends LSD_Base
{
    protected $thankyou_data = null;

    public function init()
    {
        // Checkout Shortcode
        add_shortcode('listdom-checkout', [$this, 'output']);

        // Cart actions before rendering
        add_action('template_redirect', [$this, 'actions']);

        // Checkout actions
        add_action('wp_ajax_lsd_checkout', [$this, 'checkout']);
        add_action('wp_ajax_nopriv_lsd_checkout', [$this, 'checkout']);
    }

    public function actions()
    {
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (isset($_GET['lsd_cart_remove'], $_GET['_wpnonce']) && wp_verify_nonce($nonce, 'lsd_cart_action'))
        {
            $cart = new LSD_Cart();
            $cart->remove(sanitize_text_field($_GET['lsd_cart_remove']));

            $main = new LSD_Main();
            wp_safe_redirect(remove_query_arg([
                'lsd_cart_remove',
                '_wpnonce',
                'lsd-coupon',
                'lsd-coupon-error',
            ], $main->current_url()));
            exit;
        }

        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (isset($_POST['lsd_cart_tier'], $_POST['_wpnonce']) && is_array($_POST['lsd_cart_tier']) && wp_verify_nonce($nonce, 'lsd_cart_action'))
        {
            $cart = new LSD_Cart();
            foreach ($_POST['lsd_cart_tier'] as $id => $tier_id)
                $cart->update(sanitize_text_field($id), sanitize_text_field($tier_id));

            $main = new LSD_Main();
            wp_safe_redirect($main->current_url());
            exit;
        }

        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (isset($_POST['lsd_coupon_code'], $_POST['_wpnonce']) && wp_verify_nonce($nonce, 'lsd_coupon_action'))
        {
            $code = sanitize_text_field($_POST['lsd_coupon_code']);

            $cart = new LSD_Cart();
            $main = new LSD_Main();

            $coupon = new LSD_Payments_Coupon($code);
            if ($coupon->is_valid())
            {
                $cart->add_coupon($code);
                wp_safe_redirect(add_query_arg('lsd-coupon', rawurlencode($code), remove_query_arg('lsd-coupon-error', $main->current_url())));
            }
            else
            {
                wp_safe_redirect(add_query_arg('lsd-coupon-error', 1, remove_query_arg('lsd-coupon', $main->current_url())));
            }

            exit;
        }

        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (isset($_GET['lsd_coupon_remove'], $_GET['_wpnonce']) && wp_verify_nonce($nonce, 'lsd_coupon_action'))
        {
            $cart = new LSD_Cart();
            $cart->remove_coupon();

            $main = new LSD_Main();
            wp_safe_redirect(remove_query_arg(['lsd_coupon_remove', '_wpnonce', 'lsd-coupon', 'lsd-coupon-error'], $main->current_url()));
            exit;
        }

        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (isset($_POST['lsd_tax_country'], $_POST['_wpnonce']) && wp_verify_nonce($nonce, 'lsd_tax_location'))
        {
            $country = sanitize_text_field($_POST['lsd_tax_country']);
            $state = isset($_POST['lsd_tax_state']) ? sanitize_text_field($_POST['lsd_tax_state']) : '';

            $cart = new LSD_Cart();
            $cart->set_tax_location($country, $state);

            $main = new LSD_Main();
            wp_safe_redirect($main->current_url());
            exit;
        }
    }

    public function output($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-checkout');
        if (trim($pre)) return $pre;

        // Listdom Bar
        LSD_Payments_Helper::bar_menus();

        $payments = LSD_Options::payments();

        $appreciation_message = $this->thankyou('appreciation_message', $payments);
        $thank_you = $this->thankyou('thank_you', $payments);

        $show_appreciation_invoice_button = $this->thankyou('show_appreciation_invoice_button', $payments);
        $appreciation_invoice_base = $this->thankyou('appreciation_invoice_base', $payments);

        $cart = new LSD_Cart();
        $totals = $cart->get_totals();
        $total = $totals['total'];
        $discount = $totals['discount'];
        $tax = $totals['tax'];
        $tax_items = $totals['taxes'] ?? [];

        // Empty Cart
        if ($cart->is_empty()) return $this->empty();

        // Enabled Gateways
        $gateways = array_filter(LSD_Payments::gateways(), function ($gateway)
        {
            return $gateway->enabled();
        });

        $items = $cart->get_items();
        $has_recurring = false;
        $has_non_recurring = false;

        foreach ($items as $item)
        {
            $plan_id = isset($item['plan_id']) ? (int) $item['plan_id'] : 0;
            $tier_id = $item['tier_id'] ?? '';

            if (!$plan_id || !$tier_id) continue;

            $plan = new LSD_Payments_Plan($plan_id, $tier_id);
            $tier = $plan->get_tier();

            if (!$tier) continue;

            if ($tier->is_recurring()) $has_recurring = true;
            else $has_non_recurring = true;

            if ($has_recurring && $has_non_recurring) break;
        }

        if ($has_recurring)
        {
            $allowed_gateways = ['stripe'];
            $gateways = array_values(array_filter($gateways, function ($gateway) use ($allowed_gateways)
            {
                return in_array($gateway->key(), $allowed_gateways, true);
            }));
        }

        $visible_gateways = array_values(array_filter($gateways, function ($gateway)
        {
            return $gateway->key() !== 'free';
        }));
        $gateway_tabs_count = count($visible_gateways);

        $gateway_warning = '';
        if ($has_recurring && $has_non_recurring)
        {
            $gateway_warning = esc_html__('You cannot purchase recurring and non-recurring items at the same time. Please adjust your cart to continue.', 'listdom');
        }

        // Checkout Style
        $style = isset($atts['style']) && trim($atts['style']) ? $atts['style'] : 'cards';

        // Checkout Template
        $tpl = 'payments/checkout/cards.php';
        if ($style === 'compact') $tpl = 'payments/checkout/compact.php';

        // Agreement
        $agreement = $payments['agreement'] ?? '';

        // Free Gateway
        $free = new LSD_Payments_Gateways_Free();

        $requires_payment = $total > 0;

        ob_start();
        include lsd_template($tpl);
        return ob_get_clean();
    }

    public function checkout()
    {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'lsd_checkout'))
        {
            wp_send_json(['success' => 0, 'message' => esc_html__('Security nonce is invalid.', 'listdom')]);
        }

        $gateway_key = sanitize_text_field($_POST['gateway'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');

        if (is_user_logged_in())
        {
            $user = wp_get_current_user();
            $name = $user->display_name;
            $email = $user->user_email;
        }

        $consent = isset($_POST['lsd_privacy_consent']) ? sanitize_text_field(wp_unslash($_POST['lsd_privacy_consent'])) : '';
        $checkout_consent_enabled = LSD_Privacy::is_consent_enabled('checkout');
        if ($checkout_consent_enabled && $consent !== '1') $this->response(['success' => 0, 'message' => LSD_Privacy::consent_required_text()]);

        $gateway = LSD_Payments::gateway($gateway_key);
        if (!$gateway || !$gateway->enabled() || !$gateway->validate($_POST)) wp_send_json(['success' => 0, 'message' => esc_html__('Payment is invalid.', 'listdom')]);

        $cart = new LSD_Cart();
        $items = $cart->get_items();
        $fees = $cart->get_fees();
        $coupon = $cart->get_coupon();
        $totals = $cart->get_totals();
        $subtotal = $totals['subtotal'];
        $discount = $totals['discount'];
        $tax = $totals['tax'];
        $tax_items = $totals['taxes'] ?? [];
        $total = $totals['total'];
        $tax_location = $cart->get_tax_location();
        $tax_helper = new LSD_Payments_Tax();
        $tax_prices_include = $tax_helper->prices_include_tax() ? 1 : 0;

        $order_title = wp_date('Y-m-d H:i:s');
        if ($items)
        {
            $first = reset($items);
            $plan_id = (int) ($first['plan_id'] ?? 0);
            $tier_id = $first['tier_id'] ?? '';
            $plan = new LSD_Payments_Plan($plan_id, $tier_id);
            $plan_name = get_the_title($plan_id);
            $tier = $plan->get_tier();
            $tier_name = $tier ? $tier->get_name() : '';

            $order_title = $plan_name;
            if ($tier_name) $order_title .= ' - ' . $tier_name;
        }
        else if ($fees)
        {
            $first_fee = reset($fees);
            $title = isset($first_fee['title']) ? trim((string) $first_fee['title']) : '';
            if ($title !== '') $order_title = $title;
        }

        // Create Order
        $order_id = LSD_Payments_Orders::add([
            'items' => $items,
            'fees' => $fees,
            'coupon' => $coupon,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'tax_items' => $tax_items,
            'total' => $total,
            'gateway' => $gateway_key,
            'tax_location' => $tax_location,
            'tax_prices_include' => $tax_prices_include,
            'user_id' => get_current_user_id(),
            'name' => $name,
            'email' => $email,
            'title' => $order_title,
            'message' => $message,
        ]);

        $order_key = get_post_meta($order_id, 'lsd_key', true);

        $completion = $gateway->complete_order($order_id, [
            'items' => $items,
            'fees' => $fees,
            'coupon' => $coupon,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'tax_items' => $tax_items,
            'total' => $total,
            'tax_location' => $tax_location,
            'tax_prices_include' => $tax_prices_include,
            'name' => $name,
            'email' => $email,
            'request' => $_POST,
        ]);

        if (is_wp_error($completion))
        {
            LSD_Payments_Orders::failed($order_id);
            wp_send_json(['success' => 0, 'message' => $completion->get_error_message()]);
        }

        // Complete Order
        if ($gateway->auto_complete()) LSD_Payments_Orders::completed($order_id);

        // Empty Cart
        $cart->clear();

        wp_send_json([
            'success' => 1,
            'order_id' => $order_id,
            'key' => $order_key,
        ]);
    }

    public function empty(): string
    {
        $payments = LSD_Options::payments();

        $empty_settings = $payments['empty'] ?? [];
        $empty_cart_logo_id = isset($empty_settings['logo']) ? absint($empty_settings['logo']) : 0;
        $empty_cart_logo = '';
        if ($empty_cart_logo_id)
        {
            $empty_cart_logo_url = wp_get_attachment_image_url($empty_cart_logo_id, 'full');
            if ($empty_cart_logo_url)
            {
                $alt_text = trim(get_post_meta($empty_cart_logo_id, '_wp_attachment_image_alt', true));
                if ($alt_text === '') $alt_text = __('Empty Cart', 'listdom');

                $empty_cart_logo = '<img src="' . esc_url($empty_cart_logo_url) . '" alt="' . esc_attr($alt_text) . '">';
            }
        }

        $empty_cart_logo = apply_filters('lsd_payments_empty_cart_logo', $empty_cart_logo, $empty_settings, $payments);

        $empty_cart_message = $empty_settings['content'] ?? '';
        if (!trim(wp_strip_all_tags($empty_cart_message))) $empty_cart_message = '<p>' . esc_html__('Your cart is empty.', 'listdom') . '</p>';
        $empty_cart_message = apply_filters('lsd_payments_empty_cart_message', $empty_cart_message, $empty_settings, $payments);

        $empty_cart_show_buttons = isset($empty_settings['show_buttons']) ? (int) $empty_settings['show_buttons'] : 1;
        $empty_cart_show_buttons = (int) apply_filters('lsd_payments_empty_cart_show_buttons', $empty_cart_show_buttons ? 1 : 0, $empty_settings, $payments);

        ob_start();
        include lsd_template('payments/empty.php');
        return ob_get_clean();
    }

    public function cart(): string
    {
        $cart = new LSD_Cart();
        $items = $cart->get_items();
        $fees = $cart->get_fees();
        $coupon_code = $cart->get_coupon();

        $main = new LSD_Main();
        $currency = LSD_Options::currency();

        ob_start();
        include lsd_template('payments/cart.php');
        return ob_get_clean();
    }

    public function thankyou($key = null, $payments = null)
    {
        if (!is_array($this->thankyou_data))
        {
            if (!is_array($payments)) $payments = LSD_Options::payments();

            $show_appreciation_invoice_button = (int) ($payments['appreciation_invoice_button'] ?? 1) === 1;
            $appreciation_invoice_base = $show_appreciation_invoice_button ? home_url('/') : '';

            $appreciation_message = trim($payments['appreciation_message'] ?? '');
            if (!$appreciation_message) $appreciation_message = esc_html__('Thank you for your purchase.', 'listdom');

            $page_id = (int) ($payments['appreciation_page'] ?? 0);
            $thank_you = $page_id ? get_permalink($page_id) : '';

            $this->thankyou_data = [
                'appreciation_message' => $appreciation_message,
                'thank_you' => $thank_you,
                'show_appreciation_invoice_button' => $show_appreciation_invoice_button,
                'appreciation_invoice_base' => $appreciation_invoice_base,
            ];
        }

        if ($key !== null) return $this->thankyou_data[$key] ?? ($key === 'show_appreciation_invoice_button' ? false : '');

        $appreciation_message = $this->thankyou_data['appreciation_message'];
        $thank_you = $this->thankyou_data['thank_you'];
        $show_appreciation_invoice_button = $this->thankyou_data['show_appreciation_invoice_button'];
        $appreciation_invoice_base = $this->thankyou_data['appreciation_invoice_base'];

        ob_start();
        include lsd_template('payments/thankyou.php');
        return ob_get_clean();
    }

    public function summary(): string
    {
        $cart = new LSD_Cart();
        $main = new LSD_Main();
        $currency = LSD_Options::currency();
        $coupon_code = $cart->get_coupon();
        $fees = $cart->get_fees();
        $tax_helper = new LSD_Payments_Tax();
        $tax_location = $cart->get_tax_location();
        $tax_countries = $tax_helper->get_countries();
        $tax_states = $tax_helper->get_states($tax_location['country']);
        $tax_states_list = $tax_helper->get_states_list();

        ob_start();
        include lsd_template('payments/summary.php');
        return ob_get_clean();
    }

    public function coupon(): string
    {
        $cart = new LSD_Cart();
        $coupon_code = $cart->get_coupon();

        $main = new LSD_Main();
        $error = isset($_GET['lsd-coupon-error']);

        ob_start();
        include lsd_template('payments/coupon.php');
        return ob_get_clean();
    }
}
