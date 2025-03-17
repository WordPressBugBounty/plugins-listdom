<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Menus $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */
?>
<div class="lsd-dashboard" id="lsd_dashboard">

    <div class="lsd-row">
        <div class="lsd-col-2 lsd-dashboard-menus-wrapper">
            <?php echo LSD_Kses::element($dashboard->menus()); ?>
        </div>
        <div class="lsd-col-10">
            <?php
            if (trim($this->content)) echo LSD_Kses::full($this->content);
            else echo LSD_Base::alert(esc_html__('Content Not Found.', 'listdom'), 'warning');
            ?>
        </div>
    </div>
</div>
