<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Welcome $this */
$assets = new LSD_Assets();
?>
<div class="lsd-welcome-wizard-header lsd-dashboard-top-bar">
    <div class="lsd-logo-section">
        <a href="<?php echo admin_url('admin.php?page=listdom-welcome'); ?>">
            <img class="lsd-logo" alt="<?php esc_attr__('logo', 'listdom') ?>" src="<?php echo esc_url_raw($this->lsd_asset_url('img/listdom-logo.svg')); ?>">
            <span><?php echo esc_html__('Listdom Wizard', 'listdom'); ?></span>
        </a>
    </div>

    <div class="lsd-flex lsd-stepper-numbers">
        <div class="lsd-stepper lsd-stepper-tabs">
            <div class="lsd-flex">
                <div class="step active">1</div>
            </div>
            <div class="stepper-line"></div>
            <div class="lsd-flex">
                <div class="step">2</div>
            </div>
            <div class="stepper-line"></div>
            <div class="lsd-flex">
                <div class="step">3</div>
            </div>
            <div class="stepper-line"></div>
            <div class="lsd-flex">
                <div class="step">4</div>
            </div>
            <div class="stepper-line"></div>
            <div class="lsd-flex lsd-step-ready">
                <div class="step">5</div>
            </div>
        </div>
    </div>

    <!-- Close Button -->
    <div class="lsd-close-button">
        <a href="<?php echo admin_url('/admin.php?page=listdom'); ?>" class="lsd-close-wizard-button">
            <i class="listdom-icon lsdi-cross"></i>
        </a>
    </div>
</div>

<div class="lsd-welcome-wizard-wrapper">
    <div class="lsd-welcome-wizard">
       <?php $this->include_html_file('menus/welcome/content.php'); ?>
    </div>
</div>
