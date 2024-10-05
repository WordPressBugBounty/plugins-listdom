<?php
// no direct access
defined('ABSPATH') || die();
?>
<h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $this->tab === 'dashboard' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom')); ?>"><?php esc_html_e('Dashboard', 'listdom'); ?></a>
    
    <?php
        /**
         * For showing new tabs in admin dashboard by third party plugins
         */
        do_action('lsd_admin_dashboard_tabs', $this->tab);
    ?>
</h2>