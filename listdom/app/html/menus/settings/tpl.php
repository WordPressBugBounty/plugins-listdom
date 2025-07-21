<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */
?>
<div class="wrap about-wrap lsd-wrap lsd-settings-wrap">
    <?php LSD_Menus::header(esc_html__('Settings', 'listdom')); ?>

    <div class="lsd-admin-main-wrapper">
        <?php echo lsd_ads('settings-top'); ?>
        <div class="lsd-settings-panel">
            <!-- Settings Tabs -->
            <?php $this->include_html_file('menus/settings/tabs.php'); ?>

                <!-- Settings Content -->
            <?php $this->include_html_file('menus/settings/content.php'); ?>
        </div>
        <?php echo lsd_ads('settings-bottom'); ?>
    </div>
</div>
