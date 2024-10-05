<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Dashboard $this */
?>
<div class="lsd-dashboard-wrap">
    <div class="welcome-panel lsd-flex lsd-flex-col lsd-gap-5 lsd-flex-items-start lsd-flex-items-full-width">

        <?php if($count = LSD_Activation::getLicenseActivationRequiredCount()): ?>
        <div class="lsd-alert lsd-warning lsd-px-5">
            <p class="lsd-mb-0"><?php echo sprintf(esc_html__('%s of your installed products require activation. Please activate them as soon as possible using the license key you received upon purchase; otherwise, the functionality will cease.', 'listdom'), '<strong>('.$count.')</strong>'); ?></p>
            <div>
                <p class="lsd-mb-0 lsd-mt-2"><?php echo sprintf(esc_html__("If you have misplaced your license key or are unable to locate it, please don't hesitate to contact %s. We are here to assist you.", 'listdom'), '<strong><a href="'.LSD_Base::getSupportURL().'" target="_blank">'.esc_html__('Webilia Support', 'listdom').'</a></strong>'); ?></p>
                <p class="lsd-mt-2"><?php echo sprintf(esc_html__("If, for any reason, you do not have a license key, you can obtain one from the %s.", 'listdom'), '<strong><a href="'.LSD_Base::getWebiliaShopURL().'" target="_blank">'.esc_html__('Webilia Website', 'listdom').'</a></strong>'); ?></p>
            </div>
        </div>
        <?php endif; ?>

		<div class="welcome-panel-content lsd-mb-0">
            <h2 class="lsd-mt-0"><?php esc_html_e('Welcome to Listdom!', 'listdom'); ?></h2>
            <p class="about-description lsd-mt-0 lsd-mb-4"><?php esc_html_e('We’ve assembled some links to get you started:', 'listdom'); ?></p>
            <div class="welcome-panel-column-container">
                <div class="welcome-panel-column">
                    <h3><?php esc_html_e('Get Started', 'listdom'); ?></h3>
                    <a class="button button-primary button-hero" href="<?php echo esc_url_raw(LSD_Base::getListdomDocsURL()); ?>" target="_blank"><?php esc_html_e('Check Documentation', 'listdom'); ?></a>
                    <p><?php esc_html_e('or,', 'listdom'); ?> <a href="<?php echo esc_url_raw(LSD_Base::getSupportURL()); ?>" target="_blank"><?php esc_html_e('contact our support team!', 'listdom'); ?></a></p>
                </div>
                <div class="welcome-panel-column">
                    <h3><?php esc_html_e('Next Steps', 'listdom'); ?></h3>
                    <ul>
                        <li><a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy='.LSD_Base::TAX_CATEGORY.'&post_type='.LSD_Base::PTYPE_LISTING)); ?>" class="welcome-icon dashicons-category"><?php esc_html_e('Manage Categories', 'listdom'); ?></a></li>
                        <li><a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy='.LSD_Base::TAX_LOCATION.'&post_type='.LSD_Base::PTYPE_LISTING)); ?>" class="welcome-icon dashicons-location"><?php esc_html_e('Manage Locations', 'listdom'); ?></a></li>
                        <?php if($this->isPro()): ?><li><a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy='.LSD_Base::TAX_ATTRIBUTE.'&post_type='.LSD_Base::PTYPE_LISTING)); ?>" class="welcome-icon dashicons-nametag"><?php esc_html_e('Manage Attributes', 'listdom'); ?></a></li><?php endif; ?>
                    </ul>
                </div>
                <div class="welcome-panel-column">
                    <h3><?php esc_html_e('Listings', 'listdom'); ?></h3>
                    <ul>
                        <li><a href="<?php echo esc_url(admin_url('edit.php?post_type='.LSD_Base::PTYPE_LISTING)); ?>" class="welcome-icon dashicons-media-text"><?php esc_html_e('Manage/Add Listings', 'listdom'); ?></a></li>
                        <li><a href="<?php echo esc_url(admin_url('edit.php?post_type='.LSD_Base::PTYPE_SHORTCODE)); ?>" class="welcome-icon dashicons-grid-view"><?php esc_html_e('Manage/Add Shortcodes', 'listdom'); ?></a></li>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings')); ?>" class="welcome-icon dashicons-admin-settings"><?php esc_html_e('Configure the Listdom', 'listdom'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if($this->can_display_review()): ?>
        <div class="lsd-ask-review-wrapper lsd-flex lsd-flex-row lsd-gap-5 lsd-flex-content-start lsd-flex-items-start">
            <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/rating.svg')); ?>" alt="">
            <div class="lsd-flex lsd-flex-col lsd-gap-3 lsd-flex-items-start">
                <h3 class="lsd-m-0 lsd-bold">Looks Like you are using Listdom for a while.</h3>
                <p class="lsd-m-0">Can you give a 5-star rating on the WordPress repository? It’s going to let the Listdom team know their efforts are useful for you.</p>
                <div class="lsd-ask-review-buttons lsd-flex lsd-flex-row lsd-gap-4 lsd-mt-3">
                    <a class="button button-primary" href="https://api.webilia.com/go/wp-review" target="_blank">
                        <span>Sure, you deserve it</span>
                        <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=listdom&review=later')); ?>">Maybe later.</a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=listdom&review=done')); ?>">Already done :)</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <h2><?php esc_html_e('Changelog', 'listdom'); ?></h2>
        <div class="lsd-changelog-wrapper"><?php $this->include_html_file('menus/dashboard/tabs/changelog.php'); ?></div>
        <div class="lsd-credit-wrapper"><?php $this->include_html_file('menus/dashboard/tabs/credits.php'); ?></div>

    </div>
</div>