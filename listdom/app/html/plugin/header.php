<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Dashboard $this */
/** @var string $title */
/** @var string $page */
/** @var string|null $url */
/** @var array $menus */
?>
<div class="lsd-dashboard-top-bar">
    <div class="lsd-logo-section">
        <a href="<?php echo esc_url($url ?: admin_url('admin.php?page=' . $page)); ?>">
            <img class="lsd-logo" alt="<?php esc_attr__('logo', 'listdom') ?>" src="<?php echo esc_url_raw($this->lsd_asset_url('img/listdom-logo.svg')); ?>">
            <span><?php echo LSD_Kses::element($title); ?></span>
        </a>
    </div>

    <div class="lsd-header-icons">
        <?php echo lsd_ads('header-links-start'); ?>

        <a href="<?php echo esc_url(admin_url('admin.php?page=listdom-addons')); ?>" class="lsd-text-button">
            <i class="listdom-icon lsdi-add-plus-circle"></i>
            <span><?php echo esc_html__('Addons', 'listdom'); ?></span>
        </a>

        <a href="<?php echo esc_url(LSD_Base::getAccountURL()); ?>" target="_blank" class="lsd-text-button">
            <i class="listdom-icon lsdi-user-circle"></i>
            <span><?php echo esc_html__('My Listdom Account', 'listdom'); ?></span>
        </a>

        <?php echo lsd_ads('header-links-end'); ?>
    </div>
</div>
<?php if (is_array($menus) && count($menus)): ?>
  <div class="lsd-header-submenu">
      <?php foreach ($menus as $menu): if (!isset($menu['title']) || !isset($menu['url'])) continue; ?>
      <a href="<?php echo esc_url($menu['url']); ?>" class="lsd-submenu-item<?php echo isset($menu['selected']) && $menu['selected'] ? ' selected' : ''; ?>"><?php echo esc_html($menu['title']); ?></a>
      <?php endforeach; ?>
  </div>
<?php endif;
