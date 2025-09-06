<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_IX $this */
?>
<div class="wrap lsd-wrap lsd-settings-wrap">
    <?php LSD_Menus::header(esc_html__('Import / Export', 'listdom')); ?>

    <div class="lsd-admin-main-wrapper">
        <?php echo lsd_ads('ix-top'); ?>
        <div class="lsd-settings-panel">
            <?php
                // IX Tabs
                $this->include_html_file('menus/ix/tabs.php');

                // IX Content
                $this->include_html_file('menus/ix/content.php');
            ?>
        </div>
        <?php echo lsd_ads('ix-bottom'); ?>
    </div>
</div>
