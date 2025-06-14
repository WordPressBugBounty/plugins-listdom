<?php
// no direct access
defined('ABSPATH') || die();
?>
<h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $this->tab === 'csv' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=csv')); ?>"><?php esc_html_e('CSV', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'excel' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=excel')); ?>"><?php esc_html_e('Excel', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'json' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=json')); ?>"><?php esc_html_e('JSON', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'dummy-data' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix&tab=dummy-data')); ?>"><?php esc_html_e('Dummy Data', 'listdom'); ?></a>

    <?php
        /**
         * For showing new tabs in IX menu by third party plugins
         */
        do_action('lsd_admin_ix_tabs', $this->tab);
    ?>
</h2>
