<?php

class LSD_Shortcodes_OrderSummary extends LSD_Base
{
    public function init()
    {
        add_shortcode('listdom-order-summary', [$this, 'output']);
    }

    public function output($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-order-summary');
        if (trim($pre)) return $pre;

        // Listdom Bar
        LSD_Payments_Helper::bar_menus();

        $atts = shortcode_atts([
            'invoice' => '1',
            'style' => 'list',
        ], $atts, 'listdom-order-summary');

        // OrderSummary Style
        $style = sanitize_key($atts['style']);

        $invoice_option = isset($atts['invoice']) ? sanitize_text_field($atts['invoice']) : '1';
        $show_invoice_button = $invoice_option !== '0';

        $order_key = isset($_GET['lsd-order']) ? sanitize_text_field($_GET['lsd-order']) : '';
        if (!$order_key) return self::alert(esc_html__('Order not found!', 'listdom'), 'error');

        $order = LSD_Payments_Orders::get_by_key($order_key);
        if (!$order) return self::alert(esc_html__('Order not found!', 'listdom'), 'error');

        $order_id = $order->get_id();
        $date = get_the_date('', $order_id);
        $email = $order->get_email();
        $gateway_key = $order->get_gateway();
        $gateway = LSD_Payments::gateway($gateway_key);
        $payment_method = $gateway ? $gateway->name() : $gateway_key;
        $currency = LSD_Options::currency();
        $total = $this->render_price($order->get_total(), $currency, false, false);
        $tax_helper = new LSD_Payments_Tax();
        $tax_label = $tax_helper->label();
        $tax_enabled = $tax_helper->enabled();
        $tax_value = $order->get_tax();
        $tax = $this->render_price($tax_value, $currency, false, false);
        $tax_items = $order->get_tax_items();
        $invoice_url = $show_invoice_button ? $order->get_invoice_url() : '';

        ob_start();
        include lsd_template('payments/order-summary.php');
        return ob_get_clean();
    }
}
