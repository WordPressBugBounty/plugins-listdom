<?php

class LSD_Payments_Invoice extends LSD_Base
{
    public function init()
    {
        add_action('init', [$this, 'render']);
    }

    public function render()
    {
        $key = $this->get_request_key();
        if ($key === '') return;

        $order = $this->get_order($key);
        if (!$order) return;

        $order_id = $order->get_id();
        if (!$order_id) return;

        $order_post = get_post($order_id);
        if (!$order_post || $order_post->post_type !== LSD_Base::PTYPE_ORDER) return;

        // Print Assets
        LSD_Assets::print();

        $currency = LSD_Options::currency();
        $tax_helper = new LSD_Payments_Tax();

        $subtotal_value = $order->get_subtotal();
        $discount_value = $order->get_discount();
        $tax_value = $order->get_tax();
        $tax_items = $order->get_tax_items();
        $total_value = $order->get_total();

        $tax_prices_include = $order->prices_include_tax();

        $display_subtotal_value = $subtotal_value;
        if ($tax_prices_include && $tax_value > 0) $display_subtotal_value = max($subtotal_value - $tax_value, 0);

        $subtotal = $this->render_price($display_subtotal_value, $currency, false, false);
        $discount = $this->render_price($discount_value, $currency, false, false);
        $tax = $this->render_price($tax_value, $currency, false, false);
        $total = $this->render_price($total_value, $currency, false, false);
        $tax_label = $tax_helper->label();
        $tax_enabled = $tax_helper->enabled();

        $gateway_key = $order->get_gateway();
        $gateway_obj = $gateway_key ? LSD_Payments::gateway($gateway_key) : null;
        $gateway_name = $gateway_obj ? $gateway_obj->name() : '';

        $obj = get_post_status_object($order_post->post_status);
        $order_status = $obj ? $obj->label : '';

        $order_datetime = LSD_Base::datetime($order_post->post_date);

        $customer_name = $order->get_name();
        $customer_email = $order->get_email();
        $customer_message = $order->get_message();

        $items = $order->get_items();
        $fees = $order->get_fees();

        $payments = LSD_Options::payments();
        $invoice_settings = $payments['invoice'] ?? [];
        $invoice_logo_id = isset($invoice_settings['logo']) ? absint($invoice_settings['logo']) : 0;
        $invoice_logo = $invoice_logo_id ? wp_get_attachment_image($invoice_logo_id, [250 , 100]) : '';
        $invoice_from = isset($invoice_settings['from']) && is_string($invoice_settings['from']) ? $invoice_settings['from'] : '';
        $invoice_footer = isset($invoice_settings['footer']) && is_string($invoice_settings['footer']) ? $invoice_settings['footer'] : '';

        ob_start();
        include lsd_template('payments/invoice.php');
        $invoice = ob_get_clean();

        header('Content-Type: text/html');
        echo trim($this->iframe($invoice));
        exit;
    }

    protected function get_request_key(): string
    {
        if (!isset($_REQUEST['lsd-invoice'])) return '';

        $value = $_REQUEST['lsd-invoice'];
        if (!is_string($value)) return '';

        $value = sanitize_text_field(wp_unslash($value));
        return trim($value);
    }

    protected function get_order(string $key): ?LSD_Payments_Order
    {
        return LSD_Payments_Orders::get_by_key($key);
    }
}
