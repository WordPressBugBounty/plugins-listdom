<?php

class LSD_Payments_Gateways_Wiretransfer extends LSD_Payments_Gateway
{
    public function __construct()
    {
        $this->id = 3;
        $this->key = 'wiretransfer';
    }

    public function label(): string
    {
        return esc_html__('Wire Transfer', 'listdom');
    }

    public function description(): string
    {
        return esc_html__('Get paid via direct bank transfer.', 'listdom');
    }

    public function icon(): string
    {
        return 'fa fa-money-check-alt';
    }

    public function validate(array $args = []): bool
    {
        return true;
    }
}
