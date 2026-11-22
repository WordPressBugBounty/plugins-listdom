<?php
defined('ABSPATH') || die();
?>
<?php include __DIR__ . '/auth-page-setup.php'; ?>
<div class="lsd-settings-fields-wrapper">
    <div class="lsd-admin-section-heading">
        <h3 class="lsd-admin-title"><?php esc_html_e('Change Default Pages', 'listdom'); ?></h3>
        <p class="lsd-admin-description lsd-my-0"><?php echo esc_html__('Select your login, registration, and password recovery pages. Turn on the corresponding switch to use each page as the site-wide WordPress destination.', 'listdom'); ?></p>
    </div>

    <div class="lsd-default-forms lsd-settings-fields-sub-wrapper <?php echo $auth['auth']['hide_login_form'] ? 'lsd-util-hide' : ''; ?>" id="lsd-login-default-form">
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Login', 'listdom'),
                'for' => 'lsd_auth_login_form',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_auth_login_form',
                    'name' => 'lsd[auth][login_form]',
                    'value' => $auth['auth']['login_form'] ?? 0,
                ]); ?>
            </div>
        </div>
        <div id="lsd_login_page_select" class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Login Page', 'listdom'),
                'for' => 'lsd_auth_login_page',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::pages([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_login_page',
                    'name' => 'lsd[auth][login_page]',
                    'value' => $auth['auth']['login_page'] ?? null,
                    'show_empty' => true,
                ]); ?>
            </div>
        </div>
    </div>

    <div class="lsd-default-forms lsd-settings-fields-sub-wrapper <?php echo $auth['auth']['hide_register_form'] ? 'lsd-util-hide' : ''; ?>" id="lsd-register-default-form">
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Register', 'listdom'),
                'for' => 'lsd_auth_register_form',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_auth_register_form',
                    'name' => 'lsd[auth][register_form]',
                    'value' => $auth['auth']['register_form'] ?? 0,
                ]); ?>
            </div>
        </div>
        <div id="lsd_register_page_select" class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Register Page', 'listdom'),
                'for' => 'lsd_auth_register_page',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::pages([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_register_page',
                    'name' => 'lsd[auth][register_page]',
                    'value' => $auth['auth']['register_page'] ?? null,
                    'show_empty' => true,
                ]); ?>
            </div>
        </div>
    </div>

    <div class="lsd-default-forms lsd-settings-fields-sub-wrapper <?php echo $auth['auth']['hide_forgot_password_form'] ? 'lsd-util-hide' : ''; ?>" id="lsd-forgot-password-default-form">
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Forgot Password', 'listdom'),
                'for' => 'lsd_auth_forgot_password_form',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_auth_forgot_password_form',
                    'name' => 'lsd[auth][forgot_password_form]',
                    'value' => $auth['auth']['forgot_password_form'] ?? 0,
                ]); ?>
            </div>
        </div>
        <div id="lsd_forgot_password_page_select" class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Forgot Password Page', 'listdom'),
                'for' => 'lsd_auth_forgot_password_page',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::pages([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_forgot_password_page',
                    'name' => 'lsd[auth][forgot_password_page]',
                    'value' => $auth['auth']['forgot_password_page'] ?? null,
                    'show_empty' => true,
                ]); ?>
            </div>
        </div>
    </div>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Account / Dashboard Page', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Dashboard Page', 'listdom'),
            'for' => 'lsd_settings_submission_page',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::pages([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_settings_submission_page',
                'value' => $settings['submission_page'] ?? null,
                'name' => 'settings[submission_page]',
                'show_empty' => true,
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo sprintf(
                /* translators: %s: Frontend dashboard shortcode. */
                esc_html__('Put %s shortcode into the page.', 'listdom'),
                '<code>[listdom-dashboard]</code>'
            ); ?></p>
        </div>
    </div>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Public Profile Page', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Profile Page', 'listdom'),
            'for' => 'lsd_author_profile_page',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::pages([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_author_profile_page',
                'name' => 'lsd[profile][page]',
                'show_empty' => true,
                'value' => $auth['profile']['page'],
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo sprintf(
                /* translators: %s: Public profile shortcode. */
                esc_html__('Put %s shortcode into the page.', 'listdom'),
                '<code>[listdom-profile]</code>'
            ); ?></p>
        </div>
    </div>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Account Security Page', 'listdom'); ?></h3>
    <p class="lsd-admin-description lsd-m-0"><?php echo sprintf(
        /* translators: %s: Account security shortcode. */
        esc_html__('Place %s on a page for account security without directory features. The same sections also appear in frontend profile settings.', 'listdom'),
        '<code>[listdom-account-security]</code>'
    ); ?></p>
    <?php $row('lsd_security_page', __('Security Page', 'listdom'), LSD_Form::pages(['class' => 'lsd-admin-input', 'id' => 'lsd_security_page', 'name' => 'lsd[security][page]', 'value' => $security['page'], 'show_empty' => true])); ?>
    <div id="lsd_security_link_guide" class="lsd-alert lsd-info lsd-m-0<?php echo LSD_Security_Account::url() === '' ? ' lsd-util-hide' : ''; ?>">
        <h4 class="lsd-mt-0 lsd-mb-3"><?php esc_html_e('Account Security Link', 'listdom'); ?></h4>
        <div class="lsd-mb-3"><?php esc_html_e('The authentication shortcode hides the Account Security link by default. Use one of these examples to show it to signed-in users:', 'listdom'); ?></div>
        <div class="lsd-mb-3"><code><?php echo esc_html('[listdom-auth show_security_link="1"]'); ?></code></div>
        <div class="lsd-mb-3"><code><?php echo esc_html('[listdom-auth layout="dropdown" show_security_link="1"]'); ?></code></div>
        <div><?php esc_html_e('The link opens the published Security Page selected above. Place the account security shortcode on that page and save your settings. This option controls the link only; it does not enable or disable account security features.', 'listdom'); ?></div>
    </div>
</div>
