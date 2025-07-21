<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

$addon = apply_filters('lsd_is_addon_installed', false);
?>
<div class="lsd-nav-wrapper">
    <ul class="lsd-nav-tab-wrapper">
        <li class="lsd-has-children lsd-general-nav">
            <a class="lsd-nav-tab <?php echo $this->tab === 'general' ? 'lsd-nav-tab-active' : ''; ?>"  href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=general')); ?>">
                <i class="listdom-icon lsdi-menu-square lsd-m-0"></i>
                <?php esc_html_e('General', 'listdom'); ?>
            </a>
            <ul class="lsd-nav-sub-tabs" data-parent="general" hidden>
                <li class="lsd-nav-tab" data-key="general"><?php esc_html_e('General', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="map-module"><?php esc_html_e('Map Module', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="price-components"><?php esc_html_e('Price Components', 'listdom'); ?></li>
                <?php if (LSD_Components::socials()): ?>
                <li class="lsd-nav-tab" data-key="socials"><?php esc_html_e('Socials', 'listdom'); ?></li>
                <?php endif; ?>
                <li class="lsd-nav-tab" data-key="archive-pages"><?php esc_html_e('Archive Pages', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="slugs"><?php esc_html_e('Slugs', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="Google-reCAPTCHA"><?php esc_html_e('Google reCAPTCHA', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="integrations"><?php esc_html_e('Integrations', 'listdom'); ?></li>
            </ul>
        </li>
        <li class="lsd-has-children lsd-customizer-nav">
            <a class="lsd-nav-tab <?php echo $this->tab === 'customizer' ? 'lsd-nav-tab-active' : ''; ?>"  href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=customizer')); ?>">
                <i class="listdom-icon lsdi-customizer lsd-m-0"></i>
                <?php esc_html_e('Customizer', 'listdom'); ?>
            </a>
            <ul data-parent="customizer" class="lsd-nav-sub-tabs lsd-tabs">
                <li class="lsd-nav-tab nav-tab-active" data-key="general"><?php esc_html_e('General', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="buttons"><?php esc_html_e('Buttons', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="forms"><?php esc_html_e('Forms', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="single-listing"><?php esc_html_e('Single Listing', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="skins"><?php esc_html_e('Skins', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="taxonomies"><?php esc_html_e('Taxonomies', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="frontend-dashboard"><?php esc_html_e('Frontend Dashboard', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="profile"><?php esc_html_e('Profile', 'listdom'); ?></li>
            </ul>
        </li>
        <li class="lsd-has-children lsd-frontend-dashboard-nav">
            <a class="lsd-nav-tab <?php echo $this->tab === 'frontend-dashboard' ? 'lsd-nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=frontend-dashboard')); ?>">
                <i class="listdom-icon lsdi-frontend-dashboard lsd-m-0"></i>
                <?php esc_html_e('Frontend Dashboard', 'listdom'); ?>
            </a>
            <ul data-parent="frontend-dashboard" class="lsd-nav-sub-tabs lsd-tabs">
                <li class="lsd-nav-tab nav-tab-active" data-key="pages"><?php esc_html_e('Pages', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="dashboard-menus"><?php esc_html_e('Dashboard Menus', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="guest-submission"><?php esc_html_e('Guest Submission', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="fields"><?php esc_html_e('Fields', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="restrictions"><?php esc_html_e('Restrictions', 'listdom'); ?></li>
            </ul>
        </li>
        <?php
            /**
             * For showing new tabs in settings menu by third party plugins
             */
            do_action('lsd_admin_settings_tabs_before_auth', $this);
        ?>

        <li class="lsd-has-children lsd-users-nav">
            <a class="lsd-nav-tab <?php echo $this->tab === 'auth' ? 'lsd-nav-tab-active' : ''; ?>"   href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=auth')); ?>">
                <i class="listdom-icon lsdi-user-circle lsd-m-0"></i>
                <?php esc_html_e('Users', 'listdom'); ?>
            </a>
            <ul data-parent="auth" class="lsd-nav-sub-tabs lsd-tabs">
                <li class="lsd-nav-tab nav-tab-active" data-key="authentication"><?php esc_html_e('Authentication', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="login"><?php esc_html_e('Login', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="register"><?php esc_html_e('Register', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="forgot-password"><?php esc_html_e('Forgot Password', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="profile-user-directory"><?php esc_html_e('Profile & User Directory', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="logged-in-users"><?php esc_html_e('Logged In Users', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="block-admin-access"><?php esc_html_e('Block Admin Access', 'listdom'); ?></li>
            </ul>
        </li>
        <li class="lsd-single-listing-nav">
            <a class="lsd-nav-tab <?php echo $this->tab === 'single-listing' ? 'lsd-nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=single-listing')); ?>">
                <i class="listdom-icon lsdi-list-view lsd-m-0"></i>
                <?php esc_html_e('Single Listing', 'listdom'); ?>
            </a>
        </li>
        <li class="lsd-has-children lsd-advanced-nav">
            <a class="lsd-nav-tab <?php echo $this->tab === 'advanced' ? 'lsd-nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=advanced')); ?>">
                <i class="listdom-icon lsdi-advanced lsd-m-0"></i>
                <?php esc_html_e('Advanced', 'listdom'); ?>
            </a>
            <ul data-parent="advanced" class="lsd-nav-sub-tabs lsd-tabs">
                <li class="lsd-nav-tab nav-tab-active" data-key="assets-loading"><?php esc_html_e('Optimize Assets Loading', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="custom-styles"><?php esc_html_e('Custom Styles', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="components"><?php esc_html_e('Components', 'listdom'); ?></li>
                <li class="lsd-nav-tab" data-key="import-export"><?php esc_html_e('Import/Export', 'listdom'); ?></li>
            </ul>
        </li>
        <li class="lsd-ai-nav">
            <a class="lsd-nav-tab <?php echo $this->tab === 'ai' ? 'lsd-nav-tab-active' : ''; ?>"  href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=ai')); ?>">
                <i class="listdom-icon lsdi-stars lsd-m-0"></i>
                <?php esc_html_e('AI', 'listdom'); ?>
            </a>
        </li>
        <li class="lsd-api-nav">
            <a class="lsd-nav-tab <?php echo $this->tab === 'api' ? 'lsd-nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=api')); ?>">
                <i class="listdom-icon lsdi-api lsd-m-0"></i>
                <?php esc_html_e('API', 'listdom'); ?>
            </a>
        </li>
        <?php
            /**
             * For showing new tabs in settings menu by third party plugins
             */
            do_action('lsd_admin_settings_tabs_before_addons', $this);
        ?>

        <?php if ($addon): ?>
        <li class="lsd-has-children lsd-addons-nav">
            <a class="lsd-nav-tab <?php echo $this->tab === 'addons' ? 'lsd-nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=addons')); ?>">
                <i class="listdom-icon lsdi-add-plus-circle lsd-m-0"></i>
                <?php esc_html_e('Addons', 'listdom'); ?>
            </a>

            <ul class="lsd-nav-sub-tabs lsd-tabs" data-parent="addons">
                <?php echo $this->addons_tab(); ?>
            </ul>
        </li>
        <?php endif; ?>

        <?php
            /**
             * For showing new tabs in settings menu by third party plugins
             */
            do_action('lsd_admin_settings_tabs_after_addons', $this);
        ?>
    </ul>
    <p class="lsd-nav-support-link"><?php echo sprintf(esc_html__("Have Problems? %s", 'listdom'), '<strong><a href="' . LSD_Base::getSupportURL() . '" target="_blank">' . esc_html__('Contact Support', 'listdom') . '</a></strong>'); ?></p>
</div>
<!-- Custom Modal HTML -->
<div id="tabSwitchModal" class="lsd-modal">
    <div class="lsd-modal-content">
        <div class="lsd-switch-modal-message">
            <p class="lsd-m-0"><?php esc_html_e('You have unsaved changes.', 'listdom'); ?></p>
            <p class="lsd-m-0"><?php esc_html_e('Do you want to leave without saving? ', 'listdom'); ?></p>
        </div>
        <div class="lsd-switch-modal-buttons">
            <button id="confirmLeaveBtn" class="lsd-secondary-button"><?php esc_html_e('Leave Now', 'listdom'); ?></button>
            <button id="saveLeaveBtn" class="lsd-secondary-button"><?php esc_html_e('Save & Leave', 'listdom'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function ($)
{
    const tabs = $('.lsd-nav-tab-wrapper a.lsd-nav-tab');
    let currentTab = $('.lsd-nav-tab-active');
    const formSelectors = ['#lsd_settings_form', '#lsd_addons_form', '#lsd_api_form', '#lsd_auth_form'];

    // Function to check if there are unsaved changes
    const hasUnsavedChanges = () => currentTab.attr('data-saved') === 'false';

    // Function to handle tab click events
    const handleTabClick = function (e)
    {
        const newTab = $(this);

        if (hasUnsavedChanges())
        {
            e.preventDefault();
            listdomConfirmModal('#tabSwitchModal', '#confirmLeaveBtn', '#saveLeaveBtn', (confirmLeave) =>
            {
                if (confirmLeave) {
                    updateTab(newTab);
                    window.location.href = newTab.attr('href');
                } else handleFormSubmission(newTab);
            });
        }
        else updateTab(newTab);
    };

    // Function to update the current tab and its state
    const updateTab = newTab => {
        currentTab.attr('data-saved', 'true');
        currentTab = newTab;
    };

    // Function to handle form submission
    const handleFormSubmission = (newTab) => {
        const form = $(formSelectors.join(', '));
        form.trigger('submit');

        setTimeout(() => {
            updateTab(newTab);
            window.location.href = newTab.attr('href');
        }, 1000);
    };

    // Function to detect form changes and mark as unsaved
    const detectFormChanges = () => {
        formSelectors.forEach(function (selector) {
            $(selector).on('change', function () {
                markFormAsUnsaved();
            });
        });
    };

    // Function to mark form as unsaved
    const markFormAsUnsaved = () => {
        const $tab = $('.lsd-nav-tab-active');
        $tab.attr('data-saved', 'false');
    };

    // Attach event listeners
    tabs.on('click', handleTabClick);
    setTimeout(() => detectFormChanges(), 500);
});
</script>
