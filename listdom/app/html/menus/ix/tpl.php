<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_IX $this */
?>
<div class="wrap about-wrap lsd-wrap">
    <?php LSD_Menus::header(esc_html__('Import / Export', 'listdom')); ?>

    <div class="lsd-admin-main-wrapper">
        <?php echo lsd_ads('ix-top'); ?>
        <div class="lsd-admin-wrapper">
            <div class="about-text">
                <?php echo esc_html__('Easily import and export listings in your preferred format!', 'listdom'); ?>
            </div>
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
