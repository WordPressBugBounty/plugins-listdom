<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */
?>
<div class="wrap about-wrap lsd-wrap">
    <h1><?php esc_html_e('Settings', 'listdom'); ?></h1>
    <div class="about-text">
		<?php echo sprintf(esc_html__("Easily configure the %s to change its functionality and look.", 'listdom'), '<strong>Listdom</strong>'); ?>
    </div>

    <?php LSD_Ads::display('settings-top'); ?>
    
    <!-- Settings Tabs -->
    <?php $this->include_html_file('menus/settings/tabs.php'); ?>
    
    <!-- Settings Content -->
    <?php $this->include_html_file('menus/settings/content.php'); ?>

    <?php LSD_Ads::display('settings-bottom'); ?>
    
</div>