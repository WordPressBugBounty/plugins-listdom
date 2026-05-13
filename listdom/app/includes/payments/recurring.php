<?php

class LSD_Payments_Recurring extends LSD_Base
{
    /**
     * @var WP_Post|null
     */
    private $recurring;

    public function __construct($recurring)
    {
        $this->recurring = get_post($recurring);
    }

    public function get_id(): int
    {
        return $this->recurring ? $this->recurring->ID : 0;
    }

    public function get_status(): string
    {
        return $this->recurring ? $this->recurring->post_status : '';
    }

    public function get_orders(): array
    {
        $order_ids = $this->get_order_ids('desc');

        $orders = [];
        foreach ($order_ids as $order_id)
        {
            $order = LSD_Payments_Orders::get($order_id);
            if ($order) $orders[] = $order;
        }

        return $orders;
    }

    protected function get_order_ids(string $direction = 'desc'): array
    {
        $recurring_id = $this->get_id();
        if (!$recurring_id) return [];

        $order_ids = LSD_Main::get_post_ids('lsd_recurring_id', $recurring_id);
        $order_ids = is_array($order_ids) ? array_map('intval', $order_ids) : [];

        if ($order_ids)
        {
            $order_ids = array_values(array_filter($order_ids, static function (int $order_id): bool
            {
                return get_post_type($order_id) === LSD_Base::PTYPE_ORDER;
            }));
        }

        $first_order_id = (int) get_post_meta($recurring_id, 'lsd_first_order_id', true);
        if ($first_order_id && !in_array($first_order_id, $order_ids, true))
        {
            $order_ids[] = $first_order_id;
        }

        if ($order_ids)
        {
            $order_ids = array_values(array_unique($order_ids));

            usort($order_ids, static function (int $a, int $b) use ($direction): int
            {
                $time_a = (int) get_post_time('U', true, $a);
                $time_b = (int) get_post_time('U', true, $b);

                if ($time_a === $time_b)
                {
                    return $direction === 'asc' ? $a <=> $b : $b <=> $a;
                }

                return $direction === 'asc' ? $time_a <=> $time_b : $time_b <=> $time_a;
            });
        }

        return $order_ids;
    }

    public function get_first_order_id(): int
    {
        $first_order_id = (int) get_post_meta($this->get_id(), 'lsd_first_order_id', true);
        if ($first_order_id) return $first_order_id;

        $order_ids = $this->get_order_ids('asc');
        if ($order_ids)
        {
            $first = (int) reset($order_ids);
            if ($first) return $first;
        }

        return 0;
    }

    public function get_first_order(): ?LSD_Payments_Order
    {
        $order_id = $this->get_first_order_id();
        if ($order_id)
        {
            $order = LSD_Payments_Orders::get($order_id);
            if ($order) return $order;
        }

        $orders = $this->get_orders();
        return $orders ? $orders[0] : null;
    }

    public function get_order_number(int $order_id): int
    {
        if ($order_id < 1) return 0;

        return (int) get_post_meta($order_id, 'lsd_recurring_number', true);
    }

    public function get_gateway(): string
    {
        $gateway = get_post_meta($this->get_id(), 'lsd_gateway', true);
        return $gateway ? (string) $gateway : '';
    }

    public function get_gateway_instance(): ?LSD_Payments_Gateway
    {
        $key = $this->get_gateway();
        return $key !== '' ? LSD_Payments::gateway($key) : null;
    }

    public function get_user_id(): int
    {
        return (int) get_post_meta($this->get_id(), 'lsd_user_id', true);
    }

    public function get_user(): ?WP_User
    {
        $user_id = $this->get_user_id();
        return $user_id ? get_userdata($user_id) : null;
    }

    public function get_items(): array
    {
        $items = get_post_meta($this->get_id(), 'lsd_items', true);
        return is_array($items) ? $items : [];
    }

    public function get_total(): float
    {
        return (float) get_post_meta($this->get_id(), 'lsd_total', true);
    }

    public function get_currency(): string
    {
        $currency = get_post_meta($this->get_id(), 'lsd_currency', true);
        return $currency ? (string) $currency : LSD_Options::currency();
    }

    public function get_gateway_meta(): array
    {
        $meta = get_post_meta($this->get_id(), 'lsd_gateway_meta', true);
        return is_array($meta) ? $meta : [];
    }
}
