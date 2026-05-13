<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $appreciation_message */
/** @var string $thank_you */
/** @var bool $show_appreciation_invoice_button */
/** @var string $appreciation_invoice_base */

// General Settings
$settings = LSD_Options::settings();

// Dashboard Page
$dashboard_page_id = isset($settings['submission_page']) && $settings['submission_page'] ? $settings['submission_page'] : 0;

$url = add_query_arg(['mode' => 'manage'], get_permalink($dashboard_page_id));
?>
<div class="lsd-checkout-processing lsd-util-hide">
    <span class="lsd-checkout-processing-icon">
        <i class="lsd-fe-icon fa fa-circle-notch fa-spin fa-fw"></i>
    </span>
    <div class="lsd-checkout-processing-message lsd-fe-description">
        <?php esc_html_e('Please wait while we confirm your payment&hellip;', 'listdom'); ?>
    </div>
</div>
<div class="lsd-checkout-appreciation lsd-util-hide" data-thankyou="<?php echo esc_attr($thank_you); ?>">
    <i class="lsd-fe-icon fa fa-check-circle"></i>
    <div class="lsd-checkout-appreciation-message lsd-fe-description">
        <?php echo LSD_Kses::full($appreciation_message); ?>
    </div>
    <div class="lsd-checkout-appreciation-buttons">
        <?php if ($show_appreciation_invoice_button && $appreciation_invoice_base): ?>
            <div class="lsd-checkout-appreciation-invoice lsd-util-hide" data-invoice-base="<?php echo esc_url($appreciation_invoice_base); ?>">
                <a class="lsd-light-button" href="#" target="_blank" rel="noopener noreferrer">
                    <i class="lsd-fe-icon fa fa-file-invoice-dollar"></i>
                    <?php esc_html_e('View Invoice', 'listdom'); ?>
                </a>
            </div>
        <?php endif; ?>
        <a class="lsd-general-button lsd-color-white-txt" href="<?php echo esc_url($url); ?>">
            <i class="fa-solid fa-long-arrow-right"></i>
            <?php esc_html_e('Go to Dashboard', 'listdom'); ?>
        </a>
    </div>
</div>
