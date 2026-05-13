<?php

class LSD_Payments_Engine extends LSD_Base
{
    const LISTDOM = 'listdom';
    const WC = 'woocommerce';

    private $payments;
    protected static $instance = null;

    public static function instance(): LSD_Payments_Engine
    {
        // Get an instance of Class
        if (is_null(self::$instance)) self::$instance = new self();

        // Return the instance
        return self::$instance;
    }

    protected function __construct()
    {
        // Payment Options
        $this->payments = LSD_Options::payments();
    }

    public function listdom(): bool
    {
        return isset($this->payments['listdom']) && $this->payments['listdom'];
    }

    public function wc(): bool
    {
        return !$this->listdom();
    }

    public function engine(): string
    {
        return $this->listdom()
            ? LSD_Payments_Engine::LISTDOM
            : LSD_Payments_Engine::WC;
    }
}
