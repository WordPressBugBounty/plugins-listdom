<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

$addon = apply_filters('lsd_is_addon_installed', false);
?>
<h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $this->tab === '' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings')); ?>"><?php esc_html_e('General', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'customizer' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=customizer')); ?>"><?php esc_html_e('Customizer', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'frontend-dashboard' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=frontend-dashboard')); ?>"><?php esc_html_e('Frontend Dashboard', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'archive-slugs' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=archive-slugs')); ?>"><?php esc_html_e('Archive & Slugs', 'listdom'); ?></a>

    <?php
        /**
         * For showing new tabs in settings menu by third party plugins
         */
        do_action('lsd_admin_settings_tabs_before_auth', $this);
    ?>

    <a class="nav-tab <?php echo $this->tab === 'auth' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=auth')); ?>"><?php esc_html_e('Users', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'single-listing' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=single-listing')); ?>"><?php esc_html_e('Single Listing', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'advanced' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=advanced')); ?>"><?php esc_html_e('Advanced', 'listdom'); ?></a>
    <a class="nav-tab <?php echo $this->tab === 'api' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=api')); ?>"><?php esc_html_e('API', 'listdom'); ?></a>

    <?php
        /**
         * For showing new tabs in settings menu by third party plugins
         */
        do_action('lsd_admin_settings_tabs_before_addons', $this);
    ?>

    <?php if ($addon): ?>
    <a class="nav-tab <?php echo $this->tab === 'addons' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=addons')); ?>"><?php esc_html_e('Addons', 'listdom'); ?></a>
    <?php endif; ?>

    <?php
        /**
         * For showing new tabs in settings menu by third party plugins
         */
        do_action('lsd_admin_settings_tabs_after_addons', $this);
    ?>
</h2>

<!-- Custom Modal HTML -->
<div id="tabSwitchModal" class="lsd-modal">
    <div class="lsd-modal-content">
        <p><?php esc_html_e('You have unsaved changes. Do you want to leave without saving? ', 'listdom'); ?></p>
        <div class="lsd-flex lsd-gap-4">
            <button id="confirmLeaveBtn" class="button button-primary button-hero"><?php esc_html_e('Yes, Leave', 'listdom'); ?></button>
            <button id="saveLeaveBtn" class="button button-secondary"><?php esc_html_e('Save & Leave', 'listdom'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function ($)
{
    const tabs = $('.nav-tab-wrapper a.nav-tab');
    let currentTab = $('.nav-tab-active');
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
        const $tab = $('.nav-tab-active');
        $tab.attr('data-saved', 'false');
    };

    // Attach event listeners
    tabs.on('click', handleTabClick);
    setTimeout(() => detectFormChanges(), 500);
});
</script>
