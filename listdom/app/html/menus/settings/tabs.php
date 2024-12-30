<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

$addon = apply_filters('lsd_is_addon_installed', false);
?>
<h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $this->tab === '' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings')); ?>"><?php esc_html_e('General', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'frontend-dashboard' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=frontend-dashboard')); ?>"><?php esc_html_e('Frontend Dashboard', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'archive-slugs' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=archive-slugs')); ?>"><?php esc_html_e('Archive & Slugs', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'details-page' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=details-page')); ?>"><?php esc_html_e('Details Page', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'api' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=api')); ?>"><?php esc_html_e('API', 'listdom'); ?></a>

    <?php
        /**
         * For showing new tabs in settings menu by third party plugins
         */
        do_action('lsd_admin_settings_tabs_before_addons', $this);
    ?>

    <a class="nav-tab <?php echo $this->tab === 'advanced' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=advanced')); ?>"><?php esc_html_e('Advanced', 'listdom'); ?></a>

    <?php if ($addon): ?>
    <a class="nav-tab <?php echo $this->tab === 'addons' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=addons')); ?>"><?php esc_html_e('Addons', 'listdom'); ?></a>
    <?php endif; ?>

    <?php
        /**
         * For showing new tabs in settings menu by third party plugins
         */
        do_action('lsd_admin_settings_tabs_after_addons', $this);
    ?>
</h2>
