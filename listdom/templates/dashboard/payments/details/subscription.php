<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */

$user_id = get_current_user_id();
$recurrings = $this->get_recurrings($user_id);
$recurring_detail_id = $this->get_recurring_detail_id($recurrings);
$recurring = $recurring_detail_id ? LSD_Payments_Recurrings::get($recurring_detail_id) : null;
$detail = $recurring instanceof LSD_Payments_Recurring ? $this->get_recurring_detail($recurring) : null;

if (!is_array($detail))
{
    include lsd_template('dashboard/payments/subscriptions.php');
    return;
}
?>
<div class="lsd-dashboard-payments-subscription-detail">
    <div class="lsd-dashboard-payments-subscription-detail-back-section">
        <a class="lsd-dashboard-payments-subscription-detail-back" href="<?php echo esc_url($this->get_section_url($dashboard, 'subscriptions')); ?>">
            <i class="lsd-fe-icon fa fa-arrow-left"></i>
            <?php esc_html_e('Back to Subscription', 'listdom'); ?>
        </a>
    </div>

    <div class="lsd-dashboard-payments-subscription-detail-head">
        <div class="lsd-dashboard-payments-subscription-detail-heading">
            <div class="lsd-dashboard-payments-subscription-detail-title-row">
                <div class="lsd-fe-title-icon">
                    <i class="lsd-fe-icon <?php echo esc_attr($this->get_subscription_type_icon($detail['type_key'])); ?>"></i>
                    <h3 class="lsd-fe-title"><?php echo esc_html($detail['title']); ?></h3>
                </div>
                <span class="lsd-dashboard-payments-activity-type lsd-badge lsd-dashboard-payments-activity-type-<?php echo esc_attr($detail['type_key']); ?>"><?php echo esc_html($detail['type']); ?></span>
                <?php echo LSD_Kses::element($detail['recurring_badge']); ?>
            </div>
        </div>
    </div>

    <?php if ((is_array($detail['alert']) && $detail['alert']['type'] === 'lsd-error') ?? null): ?>
        <div class="lsd-dashboard-payments-subscription-detail-alert">
            <div class="lsd-alert <?php echo esc_attr($detail['alert']['type']); ?>">
                <?php if (!empty($detail['alert']['icon'])): ?>
                    <i class="<?php echo esc_attr($detail['alert']['icon']); ?>"></i>
                <?php endif; ?>
                <div>
                    <strong><?php echo esc_html($detail['alert']['title']); ?></strong><br>
                    <?php echo esc_html($detail['alert']['message']); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="lsd-fe-box-white">
        <div class="lsd-dashboard-payments-subscription-progress-card lsd-dashboard-payments-subscription-progress-card-<?php echo esc_attr($detail['recurring_state']); ?>">
            <div class="lsd-dashboard-payment-subscription-progress-bar-section">
                <div class="lsd-dashboard-payments-subscription-progress-copy">
                    <p class="lsd-fe-description"><?php echo $detail['progress']['label']; ?></p>
                </div>
                <div class="lsd-dashboard-payments-subscription-progress">
                    <div class="lsd-dashboard-payments-subscription-progress-track" aria-hidden="true">
                        <span class="lsd-dashboard-payments-subscription-progress-bar" style="width: <?php echo esc_attr((int) $detail['progress']['percent']); ?>%"></span>
                    </div>
                    <span class="lsd-dashboard-payments-subscription-progress-value"><?php echo esc_html((int) $detail['progress']['percent']); ?>%</span>
                </div>
            </div>
            <div class="lsd-dashboard-payments-subscription-progress-card-head">
                <?php if (!empty($detail['disable_autorenew_enabled'])): ?>
                    <button
                        type="button"
                        class="lsd-fe-icon-button lsd-tooltip lsd-tooltip-top lsd-dashboard-payments-disable-autorenew lsd-fe-icon-button-trash"
                        data-recurring-id="<?php echo esc_attr($detail['recurring_id']); ?>"
                        data-nonce="<?php echo esc_attr($detail['disable_autorenew_nonce']); ?>"
                        data-dashboard-url="<?php echo esc_url($dashboard->url); ?>"
                        data-error-message="<?php esc_attr_e('Unable to disable auto renewal right now.', 'listdom'); ?>"
                        data-confirm-title="<?php esc_attr_e('Are you sure you want to cancel this subscription?', 'listdom'); ?>"
                        data-confirm-description="<?php esc_attr_e('All connected domains will be deactivated once the current active period is ended.', 'listdom'); ?>"
                        data-confirm-approve-label="<?php esc_attr_e('Yes', 'listdom'); ?>"
                        data-confirm-cancel-label="<?php esc_attr_e('No', 'listdom'); ?>"
                        aria-label="<?php esc_attr_e('Disable auto-renewal', 'listdom'); ?>"
                        data-lsd-tooltip="<?php esc_attr_e('Disable auto-renewal', 'listdom'); ?>"
                    >
                        <i class="lsd-fe-icon fa fa-trash-alt"></i>
                    </button>
                <?php endif; ?>
                <?php if (!empty($detail['activate_autorenew_enabled'])): ?>
                    <button
                        type="button"
                        class="lsd-dashboard-payments-activate-autorenew"
                        data-recurring-id="<?php echo esc_attr($detail['recurring_id']); ?>"
                        data-nonce="<?php echo esc_attr($detail['activate_autorenew_nonce']); ?>"
                        data-dashboard-url="<?php echo esc_url($dashboard->url); ?>"
                        data-error-message="<?php esc_attr_e('Unable to activate auto renewal right now.', 'listdom'); ?>"
                    >
                        <?php esc_html_e('Activate Auto-Renewal', 'listdom'); ?>
                        <i class="fa fa-check-circle"></i>
                    </button>
                <?php endif; ?>
                <?php if (!empty($detail['invoice_url'])): ?>
                    <a class="lsd-fe-icon-button lsd-tooltip lsd-tooltip-top" href="<?php echo esc_url($detail['invoice_url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('View Invoice', 'listdom'); ?>" data-lsd-tooltip="<?php esc_attr_e('View Invoice', 'listdom'); ?>">
                        <i class="lsd-fe-icon fa-regular fa-file-alt"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="lsd-dashboard-payments-subscription-progress-message lsd-util-hide"></div>
    </div>

    <div class="lsd-fe-box-white">
        <h3 class="lsd-fe-title"><?php esc_html_e('Subscription Details', 'listdom'); ?></h3>
        <div class="lsd-dashboard-payments-subscription-detail-fields">
            <?php foreach ($detail['details_fields'] as $field): ?>
                <div class="lsd-dashboard-payments-subscription-detail-field">
                    <span class="lsd-dashboard-payments-subscription-detail-field-label"><?php echo esc_html($field['label']); ?></span>
                    <strong class="lsd-dashboard-payments-subscription-detail-field-value">
                        <?php if (isset($field['value_html'])): ?>
                            <?php echo LSD_Kses::element($field['value_html']); ?>
                        <?php else: ?>
                            <?php echo esc_html($field['value']); ?>
                        <?php endif; ?>
                    </strong>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="lsd-dashboard-payments-subscription-membership-link">
            <a class="lsd-light-button" href="<?php echo esc_url(add_query_arg([
                'mode' => 'subscription',
                'membership_tab' => 'active',
            ], $dashboard->url)); ?>">
                <?php esc_html_e('View More Details', 'listdom-subscriptions'); ?>
            </a>
        </div>
    </div>

    <div class="lsd-fe-box-white lsd-dashboard-payments-billing-subscription-details-renewal">
        <h3 class="lsd-fe-title"><?php esc_html_e('Renewal History', 'listdom'); ?></h3>
        <table class="lsd-fe-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date', 'listdom'); ?></th>
                    <th><?php esc_html_e('Amount', 'listdom'); ?></th>
                    <th><?php esc_html_e('Status', 'listdom'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detail['renewal_history'] as $history): ?>
                    <tr>
                        <td><?php echo esc_html($history['date']); ?></td>
                        <td><?php echo LSD_Kses::element($history['amount']); ?></td>
                        <td>
                            <span class="lsd-dashboard-payments-activity-status lsd-status-<?php echo esc_attr($history['status_key']); ?> lsd-badge <?php echo esc_attr($this->get_status_badge_class($history['status_key'])); ?>">
                                <?php echo esc_html($history['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($history['invoice_url'])): ?>
                                <a class="lsd-text-button lsd-tooltip lsd-tooltip-top" href="<?php echo esc_url($history['invoice_url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('View invoice', 'listdom'); ?>" data-lsd-tooltip="<?php esc_attr_e('View invoice', 'listdom'); ?>">
                                    <?php esc_html_e('Invoice', 'listdom'); ?>
                                    <i class="lsd-fe-icon fa-regular fa-file-alt"></i>
                                </a>
                            <?php else: ?>
                                <?php esc_html_e('N/A', 'listdom'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ((is_array($detail['alert']) && $detail['alert']['type'] === 'lsd-info') ?? null): ?>
        <div class="lsd-dashboard-payments-subscription-detail-alert">
            <div class="lsd-alert <?php echo esc_attr($detail['alert']['type']); ?>">
                <?php if (!empty($detail['alert']['icon'])): ?>
                    <i class="<?php echo esc_attr($detail['alert']['icon']); ?>"></i>
                <?php endif; ?>
                <div>
                    <strong><?php echo esc_html($detail['alert']['title']); ?></strong><br>
                    <?php echo esc_html($detail['alert']['message']); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
