<?php
defined('ABSPATH') || die();

/**
 * @var array $application
 * @var array $options
 */
$directory_url = $this->directory_url($application);
?>
<div class="lsd-admin-wizard__completion">
    <div class="lsd-admin-wizard__completion-layout">
        <div class="lsd-admin-wizard__completion-video">
            <div class="lsd-admin-wizard__video-frame">
                <img src="https://i.ytimg.com/vi/du_96cv6BAw/maxresdefault.jpg" alt="<?php esc_attr_e('Listdom introduction video', 'listdom'); ?>">
                <a class="lsd-admin-wizard__video-play" href="https://www.youtube.com/watch?v=du_96cv6BAw" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Play the Listdom introduction video', 'listdom'); ?>">
                    <i class="webilia-icon wbli-computer-video"></i>
                </a>
            </div>
        </div>

        <div class="lsd-admin-wizard__completion-actions">
            <form class="lsd-admin-wizard__email-form" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post">
                <label for="lsd-admin-wizard-updates-email"><?php esc_html_e('Stay in the loop', 'listdom'); ?></label>
                <div class="lsd-admin-wizard__email-row">
                    <input id="lsd-admin-wizard-updates-email" name="email" type="email" class="lsd-admin-input" placeholder="<?php esc_attr_e('Your email', 'listdom'); ?>" autocomplete="email" required>
                    <input name="action" type="hidden" value="lsd_submit_newsletter">
                    <input name="lsd_submit_newsletter_nonce" type="hidden" value="<?php echo esc_attr(wp_create_nonce('lsd_submit_newsletter')); ?>">
                    <button class="lsd-admin-wizard__secondary-button lsd-secondary-button" type="submit"><?php esc_html_e('Subscribe', 'listdom'); ?></button>
                </div>
                <small class="lsd-admin-description-tiny lsd-admin-wizard__email-status lsd-m-0" aria-live="polite"></small>
            </form>

            <?php if ($directory_url !== ''): ?>
                <a class="lsd-admin-wizard__directory-link lsd-admin-wizard__primary-button lsd-primary-button" href="<?php echo esc_url($directory_url); ?>">
                    <?php esc_html_e('View your directory', 'listdom'); ?>
                    <i class="webilia-icon wbli-right-arrow"></i>
                </a>
            <?php endif; ?>
            <a class="lsd-admin-wizard__manage-link lsd-admin-wizard__secondary-button lsd-secondary-button" href="<?php echo esc_url(admin_url('edit.php?post_type=' . LSD_Base::PTYPE_LISTING)); ?>">
                <?php esc_html_e('Manage your listings', 'listdom'); ?>
            </a>

        </div>
    </div>
</div>
