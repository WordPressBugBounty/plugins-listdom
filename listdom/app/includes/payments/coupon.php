<?php

class LSD_Payments_Coupon extends LSD_Base
{
    private $coupon;

    public function __construct(string $code)
    {
        $coupon = LSD_Base::get_post_by_title($code, LSD_Base::PTYPE_COUPON);
        if ($coupon && $coupon->post_status === 'publish') $this->coupon = $coupon;
    }

    public function is_valid(): bool
    {
        return (bool) $this->coupon;
    }

    public function get_discount(): float
    {
        if (!$this->is_valid()) return 0;

        $discount = get_post_meta($this->coupon->ID, 'lsd_discount', true);
        return $discount !== '' ? floatval($discount) : 0;
    }

    public function get_deduction(): float
    {
        if (!$this->is_valid()) return 0;

        $deduction = get_post_meta($this->coupon->ID, 'lsd_deduction', true);
        return $deduction !== '' ? floatval($deduction) : 0;
    }

    public function calculate(float $subtotal): float
    {
        $discount = 0;

        $percent = $this->get_discount();
        if ($percent > 0) $discount += $subtotal * ($percent / 100);

        $deduction = $this->get_deduction();
        if ($deduction > 0) $discount += $deduction;

        return $discount;
    }
}
