<?php

class LSD_Payments_Gateways_Free extends LSD_Payments_Gateway
{
    public function __construct()
    {
        $this->id = 1;
        $this->key = 'free';
        $this->system = true;
    }

    public function label(): string
    {
        return esc_html__('Free', 'listdom');
    }

    public function description(): string
    {
        return esc_html__('Process orders that require no payment.', 'listdom');
    }

    public function enabled(): bool
    {
        return true;
    }

    public function icon(): string
    {
        return 'fa fa-check';
    }

    public function validate(array $args = []): bool
    {
        return true;
    }

    public function auto_complete(): bool
    {
        return true;
    }
}
