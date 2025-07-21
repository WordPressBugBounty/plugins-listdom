<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Dashboard $this */
?>
<div class="welcome-panel lsd-flex lsd-flex-col lsd-gap-4 lsd-flex-items-start lsd-flex-items-full-width">
    <?php if ($count = LSD_Activation::getLicenseActivationRequiredCount()): ?>
        <div class="lsd-alert lsd-warning lsd-p-4 lsd-my-0">
            <p class="lsd-mt-0 lsd-mb-1"><?php echo sprintf(esc_html__('%s of your installed products require activation. Please activate them as soon as possible using the license key you received upon purchase; otherwise, the functionality will cease.', 'listdom'), '<strong>(' . $count . ')</strong>'); ?></p>
            <p class="lsd-mt-0 lsd-mb-1"><?php echo sprintf(esc_html__("If you have misplaced your license key or are unable to locate it, please don't hesitate to contact %s. We are here to assist you.", 'listdom'), '<strong><a href="' . LSD_Base::getSupportURL() . '" target="_blank">' . esc_html__('Webilia Support', 'listdom') . '</a></strong>'); ?></p>
            <p class="lsd-mt-0 lsd-mb-1"><?php echo sprintf(esc_html__("If, for any reason, you do not have a license key, you can obtain one from the %s.", 'listdom'), '<strong><a href="' . LSD_Base::getWebiliaShopURL() . '" target="_blank">' . esc_html__('Webilia Website', 'listdom') . '</a></strong>'); ?></p>
        </div>
    <?php endif; ?>

    <?php do_action('lsd_home_content_top'); ?>

    <div class="lsd-welcome-panel-container welcome-panel-column-container">
        <div class="welcome-panel-column lsd-welcome-start">
            <h3 class="lsd-m-0 lsd-admin-title"><?php esc_html_e('Start Your Directory Website', 'listdom'); ?></h3>
            <p class="lsd-m-0"><?php esc_html_e('Thanks for using Listdom. Listdom is a great plugin for creating directory and listing websites. With Listdom you can show the listings in different skins and views such as List, Grid, Half Map etc.', 'listdom') ?></p>

            <div class="lsd-get-start-section lsd-mt-4">
                <a class="lsd-primary-button" href="<?php echo esc_url(admin_url('post-new.php?post_type=' . LSD_Base::PTYPE_LISTING)); ?>" target="_blank">
                    <span><?php esc_html_e('Publish a Listing', 'listdom'); ?></span>
                    <i class="listdom-icon lsdi-right-arrow"></i>
                </a>
                <a class="lsd-text-button" href="<?php echo esc_url_raw(LSD_Base::getListdomWelcomeWizardUrl()); ?>">
                    <span><?php esc_html_e('Start Welcome Wizard', 'listdom'); ?></span>
                    <i class="listdom-icon lsdi-wizard"></i>
                </a>
            </div>
        </div>

        <div class="welcome-video">
            <iframe width="640" height="360"
                    src="https://www.youtube-nocookie.com/embed/du_96cv6BAw?si=E1LwDdzdgdZNXpkw"
                    title="YouTube video player" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
    </div>

    <?php (new LSD_Plugin_Notice())->display('review', true); ?>

    <div class="lsd-welcome-panel-container lsd-welcome-icon-box">
        <div class="lsd-icon-box">
            <h3 class="lsd-m-0 lsd-admin-title"><?php esc_html_e('Create the Listings', 'listdom'); ?></h3>
            <ul>
                <li>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=' . LSD_Base::PTYPE_LISTING)); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-list-view"></i>
                        <?php esc_html_e('Create a Listing', 'listdom'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_CATEGORY . '&post_type=' . LSD_Base::PTYPE_LISTING)); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-menu-square"></i>
                        <?php esc_html_e('Add a Category', 'listdom'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_LOCATION . '&post_type=' . LSD_Base::PTYPE_LISTING)); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-location"></i>
                        <?php esc_html_e('Add a Location', 'listdom'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_FEATURE . '&post_type=' . LSD_Base::PTYPE_LISTING)); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-add-plus"></i>
                        <?php esc_html_e('Add a Feature', 'listdom'); ?>

                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_LABEL . '&post_type=' . LSD_Base::PTYPE_LISTING)); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-label-important"></i>
                        <?php esc_html_e('Add a Label', 'listdom'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_TAG . '&post_type=' . LSD_Base::PTYPE_LISTING)); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-tag"></i>
                        <?php esc_html_e('Add a Tag', 'listdom'); ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="lsd-icon-box">
            <h3 class="lsd-m-0 lsd-admin-title"><?php esc_html_e('Build the Essentials', 'listdom'); ?></h3>
            <ul>
                <li>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=' . LSD_Base::PTYPE_SEARCH)); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-search-area"></i>
                        <?php esc_html_e('Create a Search & Filter Form', 'listdom'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=' . LSD_Base::PTYPE_SHORTCODE)); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-second-bracket-square"></i>
                        <?php esc_html_e('Create a Skin Shortcode', 'listdom'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=auth')); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-login-method"></i>
                        <?php esc_html_e('Authentication Page', 'listdom'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=frontend-dashboard')); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-frontend-dashboard"></i>
                        <?php esc_html_e('Frontend Dashboard', 'listdom'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=auth&subtab=profile')); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-user-circle"></i>
                        <?php esc_html_e('Author Profile', 'listdom'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="lsd-icon-box">
            <h3 class="lsd-m-0 lsd-admin-title"><?php esc_html_e('Design Your Directory', 'listdom'); ?></h3>
            <ul>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=single-listing')); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-pencil-edit"></i>
                        <?php esc_html_e('Single Listing Styles', 'listdom'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=customizer&subtab=single-listing')); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-file-edit-pencil"></i>
                        <?php esc_html_e('Single Listing Customizer', 'listdom'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=customizer&subtab=forms')); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-ai-search"></i>
                        <?php esc_html_e('Search Forms Customizer', 'listdom'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=customizer&subtab=skins')); ?>" class="lsd-neutral-button">
                        <i class="listdom-icon lsdi-property-edit"></i>
                        <?php esc_html_e('Skins Customizer', 'listdom'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="lsd-welcome-bottom-big-banner">
        <div class="lsd-banner">
            <img class="lsd-icon" src="<?php echo esc_url_raw($this->lsd_asset_url('img/dashboard/listdom-mobile.png')); ?>" alt="">
            <div class="lsd-banner-right-side">
                <div>
                    <span class="lsd-admin-title"><?php esc_html_e('Get Your Directory Mobile App', 'listdom'); ?></span>
                    <p class="lsd-m-0"><?php esc_html_e('Bring your directory to users’ fingertips with the Listdom Mobile App. Browse listings, contact authors, get directions, and receive real-time updates. All from an intuitive, fast, and fully customizable mobile experience for Android and iOS.', 'listdom'); ?></p>
                </div>
                <a class="lsd-primary-button" href="<?php echo esc_url_raw(LSD_Base::getMobileApp()); ?>" target="_blank">
                    <span><?php esc_html_e('Show Me the Features', 'listdom'); ?></span>
                    <i class="listdom-icon lsdi-right-arrow"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="lsd-welcome-bottom">
        <div class="lsd-docs-problem">
            <div class="lsd-welcome-panel-container lsd-documentation">
                <img class="lsd-icon" src="<?php echo esc_url_raw($this->lsd_asset_url('img/dashboard/search.png')); ?>" alt="">
                <div>
                    <span class="lsd-admin-title"><?php esc_html_e('Documentation', 'listdom'); ?></span>
                    <p class="lsd-m-0"><?php esc_html_e('Discover everything in Listdom’s documentation', 'listdom'); ?></p>
                    <a class="lsd-neutral-button" href="<?php echo esc_url_raw(LSD_Base::getListdomDocsURL()); ?>" target="_blank">
                        <span><?php esc_html_e('Documentation', 'listdom'); ?></span>
                        <i class="listdom-icon lsdi-right-arrow"></i>
                    </a>
                </div>
            </div>
            <div class="lsd-welcome-panel-container lsd-problem">
                <img class="lsd-icon" src="<?php echo esc_url_raw($this->lsd_asset_url('img/dashboard/support.png')); ?>" alt="">
                <div>
                    <span class="lsd-admin-title"><?php esc_html_e('Have Problems?', 'listdom'); ?></span>
                    <p class="lsd-m-0"><?php esc_html_e('Visit our support center for help with Listdom.', 'listdom'); ?></p>
                    <a class="lsd-neutral-button" href="<?php echo esc_url_raw(LSD_Base::getSupportURL()); ?>" target="_blank">
                        <span><?php esc_html_e('Support', 'listdom'); ?></span>
                        <i class="listdom-icon lsdi-right-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="lsd-welcome-changelog">
            <div class="lsd-changelog-wrapper">
                <h3 class="lsd-admin-title"><?php esc_html_e('Changelog', 'listdom'); ?></h3>
                <?php $this->include_html_file('menus/dashboard/tabs/changelog.php'); ?>
            </div>
        </div>
    </div>

    <?php if (is_plugin_active('elementor/elementor.php') && !is_plugin_active('addons-for-elementor-builder/addons-for-elementor-builder.php')): ?>
    <div class="lsd-welcome-bottom-banner">
        <div class="lsd-banner">
            <div class="lsd-banner-right-side">
                <img class="lsd-icon" src="<?php echo esc_url_raw($this->lsd_asset_url('img/dashboard/vertex.png')); ?>" alt="">
                <div>
                    <span class="lsd-admin-title"><?php esc_html_e('Working with Elementor?', 'listdom'); ?></span>
                    <p class="lsd-m-0"><?php esc_html_e('Try using the Vertex addons for Elementor. A new plugin developed by Webilia.', 'listdom'); ?></p>
                </div>
            </div>

            <a class="lsd-primary-button" href="<?php echo esc_url(admin_url('update.php?action=install-plugin&plugin=addons-for-elementor-builder&_wpnonce=' . wp_create_nonce('install-plugin_addons-for-elementor-builder'))); ?>" title="<?php echo esc_attr__('Install Vertex Addons for Elementor', 'listdom'); ?>">
                <span><?php echo esc_html__('Install For Free', 'listdom'); ?></span>
                <i class="listdom-icon lsdi-right-arrow"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>
