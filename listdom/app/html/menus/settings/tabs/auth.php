<?php
// no direct access
defined('ABSPATH') || die();

// Auth Settings
$auth = LSD_Options::auth();

// Settings
$settings = LSD_Options::settings();
$privacy = LSD_Options::privacy();
$security = LSD_Security::settings();
$safe_roles = array_filter(LSD_Roles::all(), static function ($label, $role): bool { return LSD_Security::safe_role($role); }, ARRAY_FILTER_USE_BOTH);
$row = static function (string $id, string $label, string $control, string $description = ''): void
{
    echo '<div class="lsd-form-row"><div class="lsd-col-3">' . LSD_Form::label(['class' => 'lsd-fields-label', 'for' => $id, 'title' => $label]) . '</div><div class="lsd-col-5">' . $control;
    if ($description !== '') echo '<p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">' . esc_html($description) . '</p>';
    echo '</div></div>';
};

[$user_subtab, $user_section] = LSD_Portal::admin_route($this->subtab, isset($_GET['section']) ? sanitize_key(wp_unslash($_GET['section'])) : '');
?>
<div class="lsd-auth-wrap">
    <form id="lsd_auth_form">
        <?php foreach (LSD_Portal::admin_tabs() as $user_tab_key => $user_tab): ?>
            <?php $active_section = $user_tab_key === $user_subtab ? $user_section : array_key_first($user_tab['sections']); ?>
            <div id="lsd_panel_auth_<?php echo esc_attr($user_tab_key); ?>" data-user-tab="<?php echo esc_attr($user_tab_key); ?>" class="lsd-auth-form-group lsd-tab-content<?php echo $user_tab_key === $user_subtab ? ' lsd-tab-content-active' : ''; ?>">
                <h3 class="lsd-admin-title lsd-mt-0 lsd-no-border lsd-mb-2"><?php echo esc_html($user_tab['label']); ?></h3>
                <ul class="lsd-tab-switcher lsd-user-tabs lsd-level-3-menu lsd-sub-tabs lsd-flex lsd-flex-wrap lsd-mb-4" data-for=".lsd-auth-<?php echo esc_attr($user_tab_key); ?>-section">
                    <?php foreach ($user_tab['sections'] as $section_key => [$section_label, $section_file]): ?>
                        <li data-tab="auth-<?php echo esc_attr($user_tab_key . '-' . $section_key); ?>" data-user-section="<?php echo esc_attr($section_key); ?>" class="<?php echo $section_key === $active_section ? 'lsd-sub-tabs-active' : ''; ?>"><a href="<?php echo esc_url(LSD_Portal::admin_url($user_tab_key, $section_key)); ?>"><?php echo esc_html($section_label); ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <?php foreach ($user_tab['sections'] as $section_key => [$section_label, $section_file]): ?>
                    <div id="lsd-tab-switcher-auth-<?php echo esc_attr($user_tab_key . '-' . $section_key); ?>-content" class="lsd-tab-switcher-content lsd-auth-<?php echo esc_attr($user_tab_key); ?>-section<?php echo $section_key === $active_section ? ' lsd-tab-switcher-content-active' : ''; ?>">
                        <div class="lsd-settings-group-wrapper">
                            <?php include __DIR__ . '/' . $section_file; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <?php do_action('lsd_auth_form_general', $auth); ?>

        <div class="lsd-spacer-30"></div>
        <div class="lsd-form-row lsd-settings-submit-wrapper">
            <div class="lsd-col-12 lsd-flex lsd-gap-3 lsd-flex-content-end">
                <?php LSD_Form::nonce('lsd_auth_form'); ?>
                <button type="submit" id="lsd_auth_save_button" class="lsd-primary-button">
                    <?php esc_html_e('Save The Changes', 'listdom'); ?>
                    <i class='wui-icon wui-icon--checkmark-circle'></i>
                </button>
            </div>
        </div>
    </form>
    <form id="lsd_account_page_setup" class="lsd-util-hide" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="lsd_account_portal">
        <?php wp_nonce_field('lsd_account_portal', '_wpnonce', false); ?>
    </form>
</div>
<script>
jQuery('#lsd_auth_form').on('submit', function (e)
{
    e.preventDefault();

    // Elements
    const $button = jQuery("#lsd_auth_save_button");
    const $tab = jQuery('.lsd-nav-tab-active');

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Saving', 'listdom') ); ?>");

    if (window.listdomSecuritySections) window.listdomSecuritySections.save();
    jQuery(this).trigger('lsd:prepare-user-fields');
    const auth = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_auth&" + auth,
        dataType: "json",
        success: function(response)
        {
            if (response && response.success === 0)
            {
                $tab.attr('data-saved', 'false');
                listdom_notify(response.message, 'lsd-error');
                loading.stop();
                return;
            }
            if (response && response.fields_saved)
            {
                window.location.assign(<?php echo wp_json_encode(LSD_Portal::admin_url('profiles-directory', 'fields')); ?>);
                return;
            }
            $tab.attr('data-saved', 'true');
            jQuery('#lsd_auth_form').trigger('lsd:auth-saved', [response.native_credentials || {}]);

            listdom_notify("<?php echo esc_js(esc_html__('Options saved successfully.', 'listdom')); ?>", 'lsd-success');

            // Unloading
            loading.stop();
        },
        error: function()
        {
            $tab.attr('data-saved', 'false');

            listdom_notify("<?php echo esc_js(esc_html__('Error: Unable to save options.', 'listdom')); ?>", 'lsd-error');

            // Unloading
            loading.stop();
        }
    });
});
</script>
