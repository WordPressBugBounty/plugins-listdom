<?php
// no direct access
defined('ABSPATH') || die();

$connect_notice = LSD_Webilia_Connect::notice();
$connect_notice_type = in_array($connect_notice['type'] ?? '', ['error', 'info', 'success', 'warning'], true) ? $connect_notice['type'] : 'info';
$connect_return_url = LSD_Webilia_Connect::returnUrl(isset($_GET['return_to']) ? esc_url_raw(wp_unslash($_GET['return_to'])) : admin_url('admin.php?page=listdom-connect&tab=connect'));
$active_tab = $active_tab ?? 'connect';
?>
<div class="wrap lsd-wrap lsd-connect-page">
    <?php LSD_Menus::header(esc_html__('Connect', 'listdom'), null, LSD_Menus::webiliaTabs($active_tab)); ?>

    <div class="lsd-admin-main-wrapper">
        <?php echo lsd_ads('connect-top'); ?>
        <?php if (!empty($connect_notice['message'])): ?>
            <div class="lsd-alert lsd-<?php echo esc_attr($connect_notice_type); ?> lsd-my-0">
                <?php echo esc_html($connect_notice['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (LSD_Webilia_Connect::enabled()): ?>
            <?php $this->include_html_file('menus/activation/connect.php', ['parameters' => ['connect_return_url' => $connect_return_url, 'connect_notice' => [], 'connect_panel_classes' => 'lsd-admin-wrapper lsd-m-0']]); ?>
        <?php else: ?>
            <div class="lsd-alert lsd-info lsd-my-0">
                <?php esc_html_e('Webilia Connect is temporarily unavailable for this website.', 'listdom'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
