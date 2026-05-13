<?php

class LSD_Payments_Recurring_Statuses extends LSD_Statuses
{
    public function __construct()
    {
        parent::__construct();

        $this->PT = LSD_Base::PTYPE_RECURRING;
    }

    public function statuses()
    {
        $statuses = [
            LSD_Payments_Recurrings::STATUS_ACTIVE => [
                'applied' => esc_html__('Active', 'listdom'),
                'args' => [
                    'label' => esc_html__('Active', 'listdom'),
                    'label_count' => _n_noop('Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'listdom'),
                    'show_in_admin_status_list' => true,
                    'show_in_admin_all_list' => true,
                    'internal' => true,
                ],
            ],
            LSD_Payments_Recurrings::STATUS_CANCEL => [
                'applied' => esc_html__('Cancel', 'listdom'),
                'args' => [
                    'label' => esc_html__('Cancel', 'listdom'),
                    'label_count' => _n_noop('Cancel <span class="count">(%s)</span>', 'Cancel <span class="count">(%s)</span>', 'listdom'),
                    'show_in_admin_status_list' => true,
                    'show_in_admin_all_list' => true,
                    'internal' => true,
                ],
            ],
            LSD_Payments_Recurrings::STATUS_REFUNDED => [
                'applied' => esc_html__('Refunded', 'listdom'),
                'args' => [
                    'label' => esc_html__('Refunded', 'listdom'),
                    'label_count' => _n_noop('Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'listdom'),
                    'show_in_admin_status_list' => true,
                    'show_in_admin_all_list' => true,
                    'internal' => true,
                ],
            ],
        ];

        return apply_filters('lsd_recurring_statuses', $statuses);
    }
}
