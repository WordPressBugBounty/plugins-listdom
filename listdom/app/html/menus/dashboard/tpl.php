<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Dashboard $this */
?>
<div class="wrap about-wrap lsd-wrap">
    <h1><?php echo sprintf(($this->isLite() ? esc_html__('Listdom %s', 'listdom') : esc_html__('Listdom Pro %s', 'listdom')), '<span>v'.LSD_VERSION.'</span>'); ?></h1>

    <?php if($this->isLite() && $this->isPastFromInstallationTime(604800)): // 7 days ?>
    <p><?php echo LSD_Base::alert($this->upgradeMessage(), 'warning'); ?></p>
    <?php endif; ?>

    <div class="about-text">
		<?php echo sprintf(esc_html__("Thanks for using %s. Listdom is a great plugin for creating directory and listing websites. With Listdom you can show the listings in different skins and views such as List, Grid, Half Map etc.", 'listdom'), '<strong>Listdom</strong>'); ?>
    </div>

    <?php LSD_Ads::display('dashboard-top'); ?>
    
    <!-- Dashboard Tabs -->
    <?php $this->include_html_file('menus/dashboard/tabs.php'); ?>
    
    <!-- Dashboard Content -->
    <?php $this->include_html_file('menus/dashboard/content.php'); ?>

    <?php LSD_Ads::display('dashboard-bottom'); ?>
    
</div>