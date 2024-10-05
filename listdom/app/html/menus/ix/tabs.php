<?php
// no direct access
defined('ABSPATH') || die();
?>
<h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $this->tab === 'json' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-ix')); ?>"><?php esc_html_e('JSON', 'listdom'); ?></a>

    <?php
        /**
         * For showing new tabs in IX menu by third party plugins
         */
        do_action('lsd_admin_ix_tabs', $this->tab);
    ?>
</h2>