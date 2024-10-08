<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Welcome $this */
$assets = new LSD_Assets();
?>
<div class="lsd-welcome-wizard-wrapper">
    <!-- Close Button -->
    <a href="<?php echo admin_url('/admin.php?page=listdom'); ?>" class="lsd-close-wizard-button">
        <i class="fas fa-times"></i>
        <span><?php echo esc_html__('Close', 'listdom'); ?></span>
    </a>

    <?php if ($this->tab !== 'welcome'): ?>
        <img class="lsd-welcome-wizard-logo" alt="<?php esc_attr__('Wizard logo', 'listdom') ?>"
             src="<?php echo esc_url_raw($assets->lsd_asset_url('/img/wizard-logo.png')); ?>">

        <div class="lsd-flex lsd-mb-4 lsd-stepper-numbers">
            <div class="lsd-stepper lsd-stepper-tabs">
                <div class="lsd-flex">
                    <div class="step active">1</div>
                    <span class="step-label"><?php esc_html_e('Step One', 'listdom'); ?></span>
                </div>
                <div class="stepper-line"></div>
                <div class="lsd-flex">
                    <div class="step">2</div>
                    <span class="step-label"><?php esc_html_e('Step Two', 'listdom'); ?></span>
                </div>
                <div class="stepper-line"></div>
                <div class="lsd-flex">
                    <div class="step">3</div>
                    <span class="step-label"><?php esc_html_e('Step Three', 'listdom'); ?></span>
                </div>
                <div class="stepper-line"></div>
                <div class="lsd-flex">
                    <div class="step">4</div>
                    <span class="step-label"><?php esc_html_e('Ready', 'listdom'); ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="lsd-welcome-wizard lsd-flex lsd-flex-col lsd-gap-5 lsd-flex-items-start lsd-flex-items-full-width">
        <div class="lsd-welcome-wizard-content lsd-mb-0">
            <!-- Dashboard Content -->
            <?php $this->include_html_file('menus/welcome/content.php'); ?>
        </div>
    </div>
</div>
