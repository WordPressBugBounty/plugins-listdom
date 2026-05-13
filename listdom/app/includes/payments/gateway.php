<?php

abstract class LSD_Payments_Gateway extends LSD_Base
{
    public $id;
    public $key;
    protected $system = false;

    public function id(): int
    {
        return (int) $this->id;
    }

    public function key(): string
    {
        return $this->key;
    }

    abstract public function label(): string;

    abstract public function description(): string;

    abstract public function icon(): string;

    public function options(): array
    {
        $options = LSD_Options::payments();

        $gateway = isset($options['gateways']) && is_array($options['gateways'])
            ? $options['gateways']
            : [];

        return $gateway[$this->key()] ?? [];
    }

    public function name(): string
    {
        $options = $this->options();
        return isset($options['name']) && trim($options['name'])
            ? $options['name']
            : $this->label();
    }

    public function comment(): string
    {
        $options = $this->options();
        return isset($options['comment']) && trim($options['comment'])
            ? $options['comment']
            : '';
    }

    public function is_system(): bool
    {
        return $this->system;
    }

    public function enabled(): bool
    {
        $options = $this->options();
        return isset($options['status']) && $options['status'];
    }

    public function validate(array $args = []): bool
    {
        return false;
    }

    public function auto_complete(): bool
    {
        $options = $this->options();
        return isset($options['auto_complete']) && $options['auto_complete'];
    }

    public function form_specific(array $data = []): string
    {
        return '';
    }

    /**
     * Allow gateways to perform additional processing after an order is created.
     *
     * @param int $order_id
     * @param array $args
     * @return true|WP_Error
     */
    public function complete_order(int $order_id, array $args = [])
    {
        return true;
    }

    public function form_checkout(): string
    {
        $path = lsd_template('payments/gateway-forms/' . $this->key() . '.php');

        if (!file_exists($path)) return '';

        ob_start();
        include $path;
        return ob_get_clean();
    }

    public function form_checkout_user(): string
    {
        if (is_user_logged_in()) return '';

        $path = lsd_template('payments/gateway-forms/user.php');
        if (!file_exists($path)) return '';

        ob_start();
        include $path;
        return ob_get_clean();
    }

    public function form_options(): string
    {
        $data = $this->options();

        return $this->include_html_file('payments/form-options.php', [
            'return_output' => true,
            'parameters' => [
                'data' => $data,
            ],
        ]);
    }
}
