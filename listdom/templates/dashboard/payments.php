<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */

$user_id = get_current_user_id();

if (!$user_id)
{
    echo $dashboard->auth();
    return;
}

if (!$this->is_available())
{
    echo LSD_Base::alert(esc_html__('Payments and billing details are available only when the Listdom payment engine is active.', 'listdom'), 'warning');
    return;
}

$section = $this->get_section();
$recurring_detail_id = $this->get_recurring_detail_id($this->get_recurrings($user_id));
$dashboard_wrapper = $dashboard->get_dashboard_wrapper([
    'classes' => ['lsd-dashboard', 'lsd-dashboard-payments'],
]);
?>
<div class="<?php echo esc_attr($dashboard_wrapper['class']); ?>" id="lsd_dashboard"<?php echo $dashboard_wrapper['attributes']; ?>>
    <div class="lsd-row lsd-dashboard-wrapper">
        <div class="lsd-dashboard-menus-wrapper">
            <?php echo LSD_Kses::element($dashboard->menus()); ?>
        </div>
        <div class="lsd-dashboard-content-wrapper lsd-dashboard-payment-billing-wrapper">
            <?php if ($section !== LSD_Dashboard_Payments::DETAIL_SECTION): ?>
                <?php include lsd_template('dashboard/payments/header.php'); ?>
            <?php endif; ?>

            <?php if ($section === LSD_Dashboard_Payments::DETAIL_SECTION): ?>
                <?php if ($recurring_detail_id): ?>
                    <?php include lsd_template('dashboard/payments/details/subscription.php'); ?>
                <?php else: ?>
                    <?php include lsd_template('dashboard/payments/details/order.php'); ?>
                <?php endif; ?>
            <?php elseif ($section === 'overview'): ?>
                <?php include lsd_template('dashboard/payments/overview.php'); ?>
            <?php elseif ($section === 'subscriptions'): ?>
                <?php include lsd_template('dashboard/payments/subscriptions.php'); ?>
            <?php elseif ($section === 'orders'): ?>
                <?php include lsd_template('dashboard/payments/orders.php'); ?>
            <?php else: ?>
                <?php include lsd_template('dashboard/payments/billing.php'); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
