<?php
defined('ABSPATH') || die();
?>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-my-0 lsd-admin-title"><?php esc_html_e('User Profile', 'listdom'); ?></h3>
    <p class="lsd-admin-description lsd-m-0"><?php echo sprintf(
        /* translators: %s: Profile shortcode. */
        esc_html__('Insert the %s shortcode into any page to display the user profile.', 'listdom'),
        '<code>[listdom-profile]</code>'
    ); ?></p>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><label for="lsd_profile_visibility" class="lsd-fields-label"><?php esc_html_e('Profile and Directory Visibility', 'listdom'); ?></label></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::select([
                'id' => 'lsd_profile_visibility',
                'class' => 'lsd-admin-input',
                'name' => 'lsd[profile][visibility]',
                'value' => LSD_Portal::public_profiles() ? 'public' : 'private',
                'options' => [
                    'public' => __('Public profiles and user directory', 'listdom'),
                    'private' => __('Private profiles', 'listdom'),
                ],
            ]); ?>
            <p class="lsd-admin-description-tiny"><?php esc_html_e('Private profiles are visible only to their owner, and the user directory is hidden. This setting applies independently of dashboard menus and their order.', 'listdom'); ?></p>
            <a href="<?php echo esc_url(LSD_Portal::admin_url('profiles-directory', 'fields')); ?>"><?php esc_html_e('Manage which fields can appear on public profiles', 'listdom'); ?></a>
        </div>
    </div>


    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Author Listings Shortcode', 'listdom'),
            'for' => 'lsd_author_shortcode',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::shortcodes([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_author_shortcode',
                'name' => 'lsd[profile][shortcode]',
                'value' => $auth['profile']['shortcode'] ?? '',
                'only_archive_skins' => true,
                'show_empty' => true,
            ]); ?>
        </div>
    </div>
</div>

<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Privacy Consent', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Enable', 'listdom'),
                'for' => 'lsd_auth_profile_pc_enabled',
            ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_auth_profile_pc_enabled',
                'name' => 'lsd[profile][pc_enabled]',
                'value' => $auth['profile']['pc_enabled'],
                'toggle' => '#lsd_auth_profile_pc_label_row',
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('This control also depends on the global privacy consent setting.', 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-form-row<?php echo empty($auth['profile']['pc_enabled']) ? ' lsd-util-hide' : ''; ?>" id="lsd_auth_profile_pc_label_row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Label', 'listdom'),
                'for' => 'lsd_auth_profile_pc_label',
            ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_profile_pc_label',
                'name' => 'lsd[profile][pc_label]',
                'value' => $auth['profile']['pc_label'],
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Leave empty to use the default label. Use {{privacy_policy}} to automatically include the default privacy policy page link.', 'listdom'); ?></p>
        </div>
    </div>
</div>
<p class="lsd-admin-description"><a href="<?php echo esc_url(LSD_Portal::admin_url('account-setup', 'pages')); ?>"><?php esc_html_e('Manage account page assignments', 'listdom'); ?></a></p>
