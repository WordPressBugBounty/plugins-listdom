<?php

class LSD_Payments_Statuses extends LSD_Statuses
{
    public function __construct()
    {
        parent::__construct();

        $this->PT = LSD_Base::PTYPE_ORDER;
    }

    public function statuses()
    {
        $statuses = [
            LSD_Payments::STATUS_PENDING => [
                'applied' => esc_html__('Pending', 'listdom'),
                'args' => [
                    'label' => esc_html__('Pending', 'listdom'),
                    'label_count' => _n_noop('Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'listdom'),
                    'show_in_admin_status_list' => true,
                    'show_in_admin_all_list' => true,
                    'internal' => true,
                ],
            ],
            LSD_Payments::STATUS_ON_HOLD => [
                'applied' => esc_html__('On Hold', 'listdom'),
                'args' => [
                    'label' => esc_html__('On Hold', 'listdom'),
                    'label_count' => _n_noop('On Hold <span class="count">(%s)</span>', 'On Hold <span class="count">(%s)</span>', 'listdom'),
                    'show_in_admin_status_list' => true,
                    'show_in_admin_all_list' => true,
                    'internal' => true,
                ],
            ],
            LSD_Payments::STATUS_COMPLETED => [
                'applied' => esc_html__('Completed', 'listdom'),
                'args' => [
                    'label' => esc_html__('Completed', 'listdom'),
                    'label_count' => _n_noop('Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'listdom'),
                    'show_in_admin_status_list' => true,
                    'show_in_admin_all_list' => true,
                    'internal' => true,
                ],
            ],
            LSD_Payments::STATUS_CANCELED => [
                'applied' => esc_html__('Canceled', 'listdom'),
                'args' => [
                    'label' => esc_html__('Canceled', 'listdom'),
                    'label_count' => _n_noop('Canceled <span class="count">(%s)</span>', 'Canceled <span class="count">(%s)</span>', 'listdom'),
                    'show_in_admin_status_list' => true,
                    'show_in_admin_all_list' => true,
                    'internal' => true,
                ],
            ],
            LSD_Payments::STATUS_FAILED => [
                'applied' => esc_html__('Failed', 'listdom'),
                'args' => [
                    'label' => esc_html__('Failed', 'listdom'),
                    'label_count' => _n_noop('Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'listdom'),
                    'show_in_admin_status_list' => true,
                    'show_in_admin_all_list' => true,
                    'internal' => true,
                ],
            ],
            LSD_Payments::STATUS_DRAFT => [
                'applied' => esc_html__('Draft', 'listdom'),
                'args' => [
                    'label' => esc_html__('Draft', 'listdom'),
                    'label_count' => _n_noop('Draft <span class="count">(%s)</span>', 'Draft <span class="count">(%s)</span>', 'listdom'),
                    'show_in_admin_status_list' => true,
                    'show_in_admin_all_list' => true,
                    'internal' => true,
                ],
            ],
            LSD_Payments::STATUS_REFUNDED => [
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

        return apply_filters('lsd_order_statuses', $statuses);
    }
}
