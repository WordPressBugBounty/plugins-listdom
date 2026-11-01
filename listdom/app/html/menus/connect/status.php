<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $connect_url */

if (!LSD_Webilia_Connect::enabled()) return;

$connect_connected = LSD_Webilia_Connect::isConnected();
?>
<div class="lsd-webilia-connect-status" role="status">
    <div class="lsd-webilia-connect-status-content lsd-admin-section-heading">
        <div class="lsd-webilia-connect-status-details">
            <i class="wui-icon wui-icon--link-square lsd-webilia-connect-status-icon" aria-hidden="true"></i>
            <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Webilia Connect', 'listdom'); ?></h3>
            <span class="lsd-badge <?php echo $connect_connected ? 'lsd-success' : 'lsd-neutral'; ?>">
                <i class="wui-icon <?php echo $connect_connected ? 'wui-icon--checkmark-circle' : 'wui-icon--alert'; ?>" aria-hidden="true"></i>
                <?php echo $connect_connected ? esc_html__('Connected', 'listdom') : esc_html__('Not connected', 'listdom'); ?>
            </span>
        </div>
        <p class="lsd-admin-description lsd-m-0">
            <?php echo $connect_connected
                ? esc_html__('All installed products included in your Webilia plans are active now.', 'listdom')
                : esc_html__('Connect your site to activate eligible installed products automatically.', 'listdom'); ?>
        </p>
    </div>
    <a href="<?php echo esc_url($connect_url); ?>" class="lsd-primary-button lsd-webilia-connect-manage">
        <?php echo $connect_connected ? esc_html__('Manage', 'listdom') : esc_html__('Connect', 'listdom'); ?>
        <i class="wui-icon wui-icon--right-arrow" aria-hidden="true"></i>
    </a>
</div>
