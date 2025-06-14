<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */
?>
<div class="wrap about-wrap lsd-wrap lsd-settings-wrap">
    <?php LSD_Menus::header(esc_html__('Settings', 'listdom')); ?>

    <div class="lsd-admin-wrapper">
        <div class="about-text">
            <?php echo sprintf(esc_html__("Easily configure the %s to change its functionality and look.", 'listdom'), '<strong>Listdom</strong>'); ?>
        </div>

        <?php echo lsd_ads('settings-top'); ?>

        <!-- Settings Tabs -->
        <?php $this->include_html_file('menus/settings/tabs.php'); ?>

        <!-- Settings Content -->
        <?php $this->include_html_file('menus/settings/content.php'); ?>

        <?php echo lsd_ads('settings-bottom'); ?>
    </div>
</div>
