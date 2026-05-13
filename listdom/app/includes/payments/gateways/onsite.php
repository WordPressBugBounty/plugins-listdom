<?php

class LSD_Payments_Gateways_Onsite extends LSD_Payments_Gateway
{
    public function __construct()
    {
        $this->id = 2;
        $this->key = 'onsite';
    }

    public function label(): string
    {
        return esc_html__('On-Site', 'listdom');
    }

    public function description(): string
    {
        return esc_html__('Accept payments on site.', 'listdom');
    }

    public function icon(): string
    {
        return 'fa fa-money-bill-alt';
    }

    public function validate(array $args = []): bool
    {
        return true;
    }
}
