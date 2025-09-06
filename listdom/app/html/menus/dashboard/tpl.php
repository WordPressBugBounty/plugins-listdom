<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Dashboard $this */
?>
<div class="wrap about-wrap lsd-wrap">
    <?php LSD_Menus::header(); ?>

    <div class="lsd-admin-main-wrapper">
        <?php echo lsd_ads('dashboard-top'); ?>
        <?php if ($this->isLite() && $this->isPastFromInstallationTime(604800)): // 7 days ?>
            <div class="lsd-alert-no-my"><?php echo LSD_Base::alert($this->upgradeMessage(), 'warning'); ?></div>
        <?php endif; ?>
        <div class="lsd-dashboard-wrap">
            <!-- Dashboard Content -->
            <?php $this->include_html_file('menus/dashboard/content.php'); ?>
        </div>
        <?php echo lsd_ads('dashboard-bottom'); ?>
    </div>

</div>
