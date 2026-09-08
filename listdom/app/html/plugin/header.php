<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Dashboard $this */
/** @var string $title */
/** @var string $page */
/** @var string|null $url */
/** @var array $menus */
$can_manage_options = current_user_can('manage_options');
$announcements = $can_manage_options ? LSD_Announcements::instance() : null;
$active_announcements = $announcements ? $announcements->active() : [];
$dismissed_announcements = $announcements ? $announcements->dismissed() : [];
$announcements_count = count($active_announcements);
$announcement_icon_urls = [
    'info' => $this->lsd_asset_url('img/announcement-gear.svg'),
    'success' => $this->lsd_asset_url('img/announcement-checkmark.svg'),
    'warning' => $this->lsd_asset_url('img/announcement-exclamation.svg'),
    'danger' => $this->lsd_asset_url('img/announcement-exclamation.svg'),
    'error' => $this->lsd_asset_url('img/announcement-exclamation.svg'),
];

$render_announcement = function (array $announcement, string $tab) use ($announcement_icon_urls): void
{
    $id = sanitize_key((string) ($announcement['id'] ?? ''));
    if ($id === '') return;

    $severity = sanitize_key((string) ($announcement['severity'] ?? 'info'));
    if (!isset($announcement_icon_urls[$severity])) $severity = 'info';

    $severity_class = $severity === 'error' ? 'danger' : $severity;
    $created_at = (int) ($announcement['created_at'] ?? 0);
    $created_label = $created_at > 0 ? sprintf(esc_html__('%s ago', 'listdom'), human_time_diff($created_at, time())) : '';
    $url = esc_url((string) ($announcement['url'] ?? ''));
    $cta_label = trim((string) ($announcement['cta_label'] ?? ''));
    if ($url && $cta_label === '') $cta_label = esc_html__('Learn More', 'listdom');
    ?>
    <article class="lsd-announcement-item lsd-announcement-item-<?php echo esc_attr($severity_class); ?>" data-lsd-announcement-id="<?php echo esc_attr($id); ?>" data-lsd-announcement-created-at="<?php echo esc_attr($created_at); ?>">
        <div class="lsd-announcement-item-content">
            <div class="lsd-announcement-item-heading">
                <h3>
                    <span class="lsd-announcement-item-icon" aria-hidden="true">
                        <img alt="" src="<?php echo esc_url($announcement_icon_urls[$severity]); ?>">
                    </span>
                    <?php echo esc_html((string) ($announcement['title'] ?? '')); ?>
                </h3>
                <?php if ($created_label): ?>
                    <span class="lsd-announcement-item-date"><?php echo esc_html($created_label); ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($announcement['description'])): ?>
                <div class="lsd-announcement-item-description"><?php echo wp_kses_post((string) $announcement['description']); ?></div>
            <?php endif; ?>
            <?php if ($url || $tab === LSD_Announcements::STATE_ACTIVE): ?>
                <div class="lsd-announcement-item-actions">
                    <?php if ($url): ?>
                        <a class="lsd-announcement-item-cta lsd-primary-button" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($cta_label); ?></a>
                    <?php endif; ?>
                    <?php if ($tab === LSD_Announcements::STATE_ACTIVE): ?>
                        <button type="button" class="lsd-announcement-item-dismiss lsd-text-button" data-lsd-announcement-dismiss="<?php echo esc_attr($id); ?>"><?php echo esc_html__('Dismiss', 'listdom'); ?></button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </article>
    <?php
};
?>
<div class="lsd-dashboard-top-bar">
    <div class="lsd-logo-section">
        <a href="<?php echo esc_url($url ?: admin_url('admin.php?page=' . $page)); ?>">
            <img class="lsd-logo" alt="<?php esc_attr__('logo', 'listdom') ?>" src="<?php echo esc_url($this->lsd_asset_url('img/listdom-logo.svg')); ?>">
            <span><?php echo LSD_Kses::element($title); ?></span>
        </a>
    </div>

    <div class="lsd-header-icons">
        <?php echo lsd_ads('header-links-start'); ?>

        <?php if ($can_manage_options): ?>
            <button type="button" class="lsd-text-button lsd-header-announcements-button" aria-label="<?php echo esc_attr__('Announcements', 'listdom'); ?>" aria-expanded="false" aria-controls="lsd-announcements-panel">
                <span class="lsd-header-announcements-icon-wrap">
                    <img class="lsd-header-announcements-icon" alt="" src="<?php echo esc_url($this->lsd_asset_url('img/announcements.svg')); ?>">
                    <?php if ($announcements_count > 0): ?>
                        <span class="lsd-header-announcements-badge" data-lsd-announcements-header-badge><?php echo esc_html($announcements_count); ?></span>
                    <?php endif; ?>
                </span>
            </button>
        <?php endif; ?>

        <a href="<?php echo esc_url(admin_url('admin.php?page=listdom-connect&tab=addons')); ?>" class="lsd-text-button lsd-header-addons-button">
            <i class="webilia-icon wbli-add-plus-circle"></i>
            <span><?php echo esc_html__('Addons', 'listdom'); ?></span>
        </a>

        <?php if ($can_manage_options): ?>
            <?php
                $connect_connected = LSD_Webilia_Connect::enabled() && LSD_Webilia_Connect::isConnected();
                $connect_return_url = admin_url('admin.php?page=listdom-connect&tab=connect');
                $connect_url = $connect_connected ? $connect_return_url : LSD_Webilia_Connect::connectUrl($connect_return_url);
            ?>
            <a href="<?php echo esc_url($connect_url); ?>" class="lsd-primary-button lsd-header-connect-button<?php echo $connect_connected ? ' lsd-header-connect-button-connected' : ''; ?>">
                <i class="webilia-icon <?php echo $connect_connected ? 'wbli-checkmark-circle' : 'wbli-link-square'; ?>" aria-hidden="true"></i>
                <span><?php echo $connect_connected ? esc_html__('Connected to Webilia', 'listdom') : esc_html__('Connect Now', 'listdom'); ?></span>
            </a>
        <?php endif; ?>

        <?php echo lsd_ads('header-links-end'); ?>
    </div>
</div>
<?php if ($can_manage_options): ?>
    <div class="lsd-announcements-overlay" data-lsd-announcements-close hidden></div>
    <aside id="lsd-announcements-panel" class="lsd-announcements-panel" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="lsd-announcements-title" hidden>
        <div class="lsd-announcements-panel-header">
            <div class="lsd-announcements-panel-heading">
                <div class="lsd-announcements-panel-subject">
                    <h2 id="lsd-announcements-title" class="lsd-admin-title"><?php echo esc_html__('Notifications', 'listdom'); ?></h2>
                    <span class="lsd-announcements-panel-badge" data-lsd-announcements-count><?php echo esc_html($announcements_count); ?></span>
                </div>
                <button type="button" class="lsd-announcements-panel-close" aria-label="<?php echo esc_attr__('Close', 'listdom'); ?>" data-lsd-announcements-close>
                    <img alt="" src="<?php echo esc_url($this->lsd_asset_url('img/close.svg')); ?>">
                </button>
            </div>
            <ul class="lsd-announcements-panel-tabs lsd-level-3-menu lsd-sub-tabs" role="tablist" aria-label="<?php echo esc_attr__('Announcement views', 'listdom'); ?>">
                <li class="lsd-announcements-panel-tab lsd-sub-tabs-active" role="presentation">
                    <a href="#lsd-announcements-active-panel" id="lsd-announcements-active-tab" role="tab" aria-selected="true" aria-controls="lsd-announcements-active-panel" tabindex="0" data-lsd-announcements-tab="active">
                        <?php echo esc_html__('Active', 'listdom'); ?>
                    </a>
                </li>
                <li class="lsd-announcements-panel-tab" role="presentation">
                    <a href="#lsd-announcements-dismissed-panel" id="lsd-announcements-dismissed-tab" role="tab" aria-selected="false" aria-controls="lsd-announcements-dismissed-panel" tabindex="-1" data-lsd-announcements-tab="dismissed">
                        <?php echo esc_html__('Dismissed', 'listdom'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="lsd-announcements-panel-content">
            <div id="lsd-announcements-active-panel" class="lsd-announcements-panel-list" role="tabpanel" aria-labelledby="lsd-announcements-active-tab" data-lsd-announcements-list="active">
                <?php foreach ($active_announcements as $announcement) $render_announcement($announcement, LSD_Announcements::STATE_ACTIVE); ?>
                <p class="lsd-announcements-empty<?php echo count($active_announcements) ? ' lsd-util-hide' : ''; ?>" <?php echo count($active_announcements) ? 'hidden="hidden"' : ''; ?>><?php echo esc_html__('No active notifications.', 'listdom'); ?></p>
            </div>
            <div id="lsd-announcements-dismissed-panel" class="lsd-announcements-panel-list" role="tabpanel" aria-labelledby="lsd-announcements-dismissed-tab" data-lsd-announcements-list="dismissed" hidden>
                <?php foreach ($dismissed_announcements as $announcement) $render_announcement($announcement, LSD_Announcements::STATE_DISMISSED); ?>
                <p class="lsd-announcements-empty<?php echo count($dismissed_announcements) ? ' lsd-util-hide' : ''; ?>" <?php echo count($dismissed_announcements) ? 'hidden="hidden"' : ''; ?>><?php echo esc_html__('No dismissed notifications.', 'listdom'); ?></p>
            </div>
        </div>
    </aside>
<?php endif; ?>
<?php if (is_array($menus) && count($menus)): ?>
  <div class="lsd-header-submenu">
      <?php foreach ($menus as $menu): if (!isset($menu['title']) || !isset($menu['url'])) continue; ?>
      <?php $badge = isset($menu['badge']) ? (int) $menu['badge'] : 0; ?>
      <a href="<?php echo esc_url($menu['url']); ?>" class="lsd-submenu-item<?php echo isset($menu['selected']) && $menu['selected'] ? ' selected' : ''; ?>">
          <?php if (!empty($menu['icon'])): ?><i class="webilia-icon <?php echo esc_attr($menu['icon']); ?>" aria-hidden="true"></i><?php endif; ?>
          <?php echo esc_html($menu['title']); ?>
          <?php if ($badge > 0): ?><span class="update-plugins count-<?php echo esc_attr($badge); ?>"><span class="update-count"><?php echo esc_html($badge); ?></span></span><?php endif; ?>
      </a>
      <?php endforeach; ?>
  </div>
<?php endif;
?>
