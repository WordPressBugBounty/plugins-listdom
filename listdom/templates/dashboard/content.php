<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Menus $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */
?>
<?php
$dashboard_wrapper = $dashboard->get_dashboard_wrapper();
?>
<div class="<?php echo esc_attr($dashboard_wrapper['class']); ?>" id="lsd_dashboard"<?php echo $dashboard_wrapper['attributes']; ?>>

    <div class="lsd-dashboard-wrapper">
        <div class="lsd-dashboard-menus-wrapper">
            <?php echo LSD_Kses::element($dashboard->menus()); ?>
        </div>
        <div class="lsd-dashboard-content-wrapper">
            <?php
            if (trim($this->content)) echo LSD_Kses::full($this->content);
            else echo LSD_Base::alert(esc_html__('Content Not Found.', 'listdom'), 'warning');
            ?>
        </div>
    </div>
</div>
