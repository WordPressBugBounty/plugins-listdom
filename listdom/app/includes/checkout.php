<?php

class LSD_Checkout extends LSD_Base
{
    protected $cart;

    public function __construct()
    {
        $this->cart = new LSD_Cart();
    }

    public function cart(): LSD_Cart
    {
        return $this->cart;
    }
}
