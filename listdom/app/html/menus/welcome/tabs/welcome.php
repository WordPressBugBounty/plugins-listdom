<?php
// no direct access
defined('ABSPATH') || die();

$assets = new LSD_Assets();
?>
<img class="lsd-welcome-wizard-logo" alt="<?php esc_attr__('Wizard logo', 'listdom'); ?>" src="<?php echo esc_url_raw($assets->lsd_asset_url('/img/wizard-logo.png')); ?>">
<h2 class="lsd-mt-0"><?php esc_html_e('Welcome to Listdom!', 'listdom'); ?></h2>
<p class="description lsd-mt-0 lsd-mb-1"><?php esc_html_e('We appreciate your decision to use Listdom for enhancing your business directory. Our swift setup wizard will guide you through the basic configurations, getting you up and running in less than 3 minutes.', 'listdom'); ?></p>
<p class="description lsd-mt-0 lsd-mb-1"><?php esc_html_e('If you prefer not to initiate the setup wizard at this moment, feel free to skip and navigate back to the WordPress dashboard. Remember, you can always return and execute the wizard whenever it suits you.', 'listdom'); ?></p>

<div class="lsd-skip-wizard">
    <a href="<?php echo admin_url('/admin.php?page=listdom'); ?>"><?php echo esc_html__('Not Right Now', 'listdom'); ?></a>
    <a href="?page=listdom-welcome&tab=setup" class="lsd-step-link- button button-primary button-hero">
        <?php echo esc_html__("Let's start", 'listdom'); ?>
        <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
    </a>
</div>
