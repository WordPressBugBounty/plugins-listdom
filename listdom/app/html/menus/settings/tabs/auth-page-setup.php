<?php
defined('ABSPATH') || die();

$plan = LSD_Portal::setup_plan();
$definitions = LSD_Portal::definitions();
$status = isset($_GET['portal_status']) && is_string($_GET['portal_status']) ? sanitize_key($_GET['portal_status']) : '';
?>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Account Portal Preset', 'listdom'); ?></h3>
    <p class="lsd-admin-description"><?php esc_html_e('Use Listdom for user accounts without listings. This preset fills empty page assignments, enables Account Overview, Profile Settings with account security, and Logout, disables Dashboard and Add Listing, and makes profiles and the user directory private. It does not change registration policy or site-wide login redirects.', 'listdom'); ?></p>
    <?php if (get_option('lsd_account_portal_preset')): ?>
        <p class="lsd-admin-description"><?php esc_html_e('The preset has been applied. Restore the previous configuration to undo its settings. Changes you made afterward and created pages are kept.', 'listdom'); ?></p>
        <button type="submit" form="lsd_account_page_setup" name="portal_operation" value="restore" class="lsd-secondary-button"><?php esc_html_e('Restore Previous Configuration', 'listdom'); ?></button>
    <?php else: ?>
        <button type="submit" form="lsd_account_page_setup" name="portal_operation" value="apply" class="lsd-secondary-button"><?php esc_html_e('Apply Account Portal Preset', 'listdom'); ?></button>
    <?php endif; ?>
    <?php if ($status === 'preset-applied'): ?>
        <p class="lsd-alert lsd-success"><?php esc_html_e('Account Portal preset applied. Review the pages below. Enable registration under Login & Registration if your site needs public sign-ups.', 'listdom'); ?></p>
    <?php elseif (in_array($status, ['preset-restored', 'preset-restored-changed'], true)): ?>
        <p class="lsd-alert lsd-success"><?php esc_html_e('Previous configuration restored. Settings changed since applying the preset and created pages were kept.', 'listdom'); ?></p>
    <?php endif; ?>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Set Up Account Pages', 'listdom'); ?></h3>
    <p class="lsd-admin-description"><?php esc_html_e('Use your saved pages and fill empty assignments. Missing authentication pages can share one page. Creating pages does not turn on site-wide redirects.', 'listdom'); ?></p>
    <?php if ($status === 'saved'): ?>
        <p class="lsd-alert lsd-success"><?php esc_html_e('Account page setup complete.', 'listdom'); ?></p>
    <?php elseif ($status === 'incomplete'): ?>
        <p class="lsd-alert lsd-warning"><?php esc_html_e('Some account pages need attention. Check their status below.', 'listdom'); ?></p>
    <?php endif; ?>
    <?php foreach ($plan as $key => $item): ?>
        <?php
        $settings_url = LSD_Portal::admin_url('account-setup', 'pages');
        ?>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><strong><?php echo esc_html($definitions[$key][0]); ?></strong></div>
            <div class="lsd-col-9">
                <?php if (in_array($item['status'], ['assigned', 'reuse'], true)): ?>
                    <?php if ($item['status'] === 'reuse') esc_html_e('Will reuse', 'listdom'); ?>
                    <a href="<?php echo esc_url(get_permalink($item['id'])); ?>"><?php echo esc_html(get_the_title($item['id'])); ?></a>
                <?php elseif ($item['status'] === 'invalid'): ?>
                    <?php esc_html_e('The assigned page is unavailable. Update the assignment in Settings.', 'listdom'); ?>
                <?php else: ?>
                    <?php echo esc_html($item['generated'] === 'authentication' ? __('Will use a new shared Authentication page', 'listdom') : __('Will create a page', 'listdom')); ?>
                <?php endif; ?>
                <?php if ($key === 'account' || $key === 'profile'): ?>
                    <a href="<?php echo esc_url($settings_url); ?>"><?php esc_html_e('Page settings', 'listdom'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (array_intersect(['reuse', 'create'], array_column($plan, 'status'))): ?>
        <button type="submit" form="lsd_account_page_setup" class="lsd-secondary-button"><?php esc_html_e('Set Up Account Pages', 'listdom'); ?></button>
    <?php endif; ?>
</div>
