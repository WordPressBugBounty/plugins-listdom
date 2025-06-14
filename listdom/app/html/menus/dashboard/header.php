<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Dashboard $this */
/** @var string $title */
?>
<div class="lsd-dashboard-top-bar">
    <div class="lsd-logo-section">
        <img class="lsd-logo" alt="<?php esc_attr__('logo', 'listdom') ?>" src="<?php echo esc_url_raw($this->lsd_asset_url('img/listdom-logo.png')); ?>">
        <span><?php echo esc_html($title); ?></span>
    </div>

    <div class="lsd-header-icons">
        <a href="<?php echo esc_url(admin_url('admin.php?page=listdom-addons')); ?>" class="lsd-text-button">
            <i class="listdom-icon lsdi-add-plus-circle"></i>
            <span><?php echo esc_html__('Addons', 'listdom'); ?></span>
        </a>

        <a href="<?php echo esc_url(LSD_Base::getAccountURL()); ?>" target="_blank" class="lsd-text-button">
            <i class="listdom-icon lsdi-user-circle"></i>
            <span><?php echo esc_html__('My Listdom Account', 'listdom'); ?></span>
        </a>
    </div>
</div>
