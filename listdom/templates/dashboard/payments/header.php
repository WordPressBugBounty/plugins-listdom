<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Payments $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */

$section = $this->get_section();
$sections = $this->get_sections();
?>
<div class="lsd-fe-section-heading lsd-dashboard-payment-billing-title">
    <div class="lsd-fe-title-icon">
        <i class="lsd-fe-icon far fa-check-circle"></i>
        <h2 class="lsd-fe-title"><?php echo esc_html__('Payments & Billing', 'listdom'); ?></h2>
    </div>
    <p class="lsd-fe-description"><?php esc_html_e('Manage your memberships, orders, invoices, and billing information' , 'listdom'); ?></p>
</div>
<div class="lsd-fe-box-white lsd-dashboard-payment-billing-tab">
    <div class="lsd-dashboard-payments-tabs lsd-fe-tabs">
        <ul class="lsd-fe-tabs-nav">
            <?php foreach ($sections as $key => $data): ?>
                <li class="<?php echo $section === $key ? 'lsd-active' : ''; ?>">
                    <a href="<?php echo esc_url($this->get_section_url($dashboard, $key)); ?>">
                        <i class="lsd-fe-icon <?php echo esc_attr($data['icon'] ?? 'fas fa-circle'); ?>"></i>
                        <?php echo esc_html($data['label'] ?? ''); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
