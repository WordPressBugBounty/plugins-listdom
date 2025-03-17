<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Dashboard $this */
?>
<div class="wrap about-wrap lsd-wrap">
    <h1><?php echo sprintf(($this->isLite() ? esc_html__('Listdom %s', 'listdom') : esc_html__('Listdom Pro %s', 'listdom')), '<span>v' . LSD_VERSION . '</span>'); ?></h1>

    <?php if ($this->isLite() && $this->isPastFromInstallationTime(604800)): // 7 days ?>
        <p><?php echo LSD_Base::alert($this->upgradeMessage(), 'warning'); ?></p>
    <?php endif; ?>

    <div class="about-text">
        <?php echo sprintf(esc_html__("Thank you for using %s! Listdom is a powerful plugin for creating directory and listing websites. It allows you to display listings in various skins and views, including List, Grid, Half Map, and more.", 'listdom'), '<strong>Listdom</strong>'); ?>
    </div>

    <?php LSD_Ads::display('dashboard-top'); ?>

    <!-- Dashboard Tabs -->
    <?php $this->include_html_file('menus/dashboard/tabs.php'); ?>

    <!-- Dashboard Content -->
    <?php $this->include_html_file('menus/dashboard/content.php'); ?>

    <?php LSD_Ads::display('dashboard-bottom'); ?>

</div>
