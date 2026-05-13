<?php

class LSD_Payments_Gateways_Paypal extends LSD_Payments_Gateway
{
    public function __construct()
    {
        $this->id = 4;
        $this->key = 'paypal';
    }

    public function label(): string
    {
        return esc_html__('PayPal', 'listdom');
    }

    public function description(): string
    {
        return esc_html__('Process payments using PayPal.', 'listdom');
    }

    public function icon(): string
    {
        return 'fab fa-paypal';
    }

    public function enabled(): bool
    {
        if (!parent::enabled()) return false;

        $options = $this->options();
        return isset($options['client_id']) && trim($options['client_id'])
            && isset($options['secret']) && trim($options['secret']);
    }

    public function form_specific(array $data = []): string
    {
        return $this->include_html_file('payments/specific/paypal.php', [
            'return_output' => true,
            'parameters' => [
                'data' => $data,
            ],
        ]);
    }

    public function validate(array $args = []): bool
    {
        $order_id = isset($args['paypal_order_id']) ? sanitize_text_field($args['paypal_order_id']) : '';

        if (!trim($order_id)) return false;

        $options = $this->options();

        $client_id = $options['client_id'] ?? '';
        $secret = $options['secret'] ?? '';
        $mode = $options['mode'] ?? 'sandbox';

        if (!$client_id || !$secret) return false;

        $token = $this->get_token($client_id, $secret, $mode);
        if (!$token) return false;

        $order = $this->get_order($token, $order_id, $mode);

        return isset($order['status']) && $order['status'] === 'COMPLETED';
    }

    protected function get_token(string $client_id, string $secret, string $mode): string
    {
        $url = $this->get_endpoint($mode);
        $request = wp_remote_post($url . '/v1/oauth2/token', [
            'httpversion' => '1.0',
            'sslverify' => false,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $secret),
            ],
            'body' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $token = '';
        $result = wp_remote_retrieve_body($request);
        if ($result)
        {
            $json = json_decode($result);
            $token = $json->access_token ?? '';
        }

        return $token;
    }

    protected function get_order(string $token, string $order_id, string $mode): array
    {
        $url = $this->get_endpoint($mode);
        $request = wp_remote_get($url . '/v2/checkout/orders/' . $order_id, [
            'httpversion' => '1.0',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'User-Agent' => 'PHP-LISTDOM',
            ],
        ]);

        if (is_wp_error($request)) return [];

        $result = wp_remote_retrieve_body($request);
        return $result ? json_decode($result, true) : [];
    }

    protected function get_endpoint(string $mode): string
    {
        if ($mode === 'sandbox') return 'https://api-m.sandbox.paypal.com';
        return 'https://api-m.paypal.com';
    }
}
