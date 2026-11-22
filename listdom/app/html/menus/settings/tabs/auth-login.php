<?php
defined('ABSPATH') || die();
?>
<p class="lsd-admin-description lsd-my-0"><?php echo sprintf(
    /* translators: %s: Login shortcode. */
    esc_html__('Insert the %s shortcode into any page to display a login form.', 'listdom'),
    '<code>[listdom-login]</code>'
); ?></p>
<div class="lsd-alert lsd-info lsd-my-0">
    <?php echo esc_html__('You can restrict the login form to a specific role using the role parameter (subscriber, contributor, listdom_author or listdom_publisher). Example:', 'listdom'); ?>
    <strong>[listdom-login role="listdom_author"]</strong>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Redirect', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('After Login Redirect Page', 'listdom'),
            'for' => 'lsd_auth_login_redirect',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::pages([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_login_redirect',
                'name' => 'lsd[login][redirect]',
                'value' => $auth['login']['redirect']
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("After the user logs in, they will be redirected to the designated page.", 'listdom'); ?></p>
        </div>
    </div>

    <h3 class="lsd-admin-title"><?php esc_html_e('Redirection Per User Role', 'listdom'); ?></h3>
    <?php foreach (LSD_Roles::supported() as $role => $label): ?>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'for' => 'lsd_auth_login_redirect_role_'. $role,
                'title' => $label
            ]); ?></div>
            <div class="lsd-col-5"><?php echo LSD_Form::switcher([
                'id' => 'lsd_auth_login_redirect_role_'. $role,
                'name' => 'lsd[login][redirect_'. $role.']',
                'value' => $auth['login']['redirect_'. $role],
                'toggle' => '#lsd-auth-login-redirect-page-' . esc_attr($role) . '-content',
            ]); ?></div>
        </div>
        <div class="lsd-form-row <?php echo ($auth['login']['redirect_'. $role]) == 0 ? 'lsd-util-hide' : ''; ?>" id="<?php echo esc_attr('lsd-auth-login-redirect-page-' . esc_attr($role) . '-content'); ?>" >
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Redirect Page', 'listdom'),
                'for' => 'lsd_auth_login_redirect_' . $role,
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::pages([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_login_redirect_' . $role,
                    'name' => 'lsd[login]['. $role .'][redirect]',
                    'value' => $auth['login'][$role]['redirect'] ?? $auth['login']['redirect'],
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo sprintf(
    /* translators: %s: User role label. */
    esc_html__("After logging in, the %s user will be redirected to the designated page.", 'listdom'),
    '<strong>'.$label.'</strong>'
); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Labels', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Username', 'listdom'),
            'for' => 'lsd_auth_login_username_label',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_login_username_label',
                'name' => 'lsd[login][username_label]',
                'value' => $auth['login']['username_label']
            ]); ?>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Password', 'listdom'),
            'for' => 'lsd_auth_login_password_label',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_login_password_label',
                'name' => 'lsd[login][password_label]',
                'value' => $auth['login']['password_label']
            ]); ?>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Remember me', 'listdom'),
            'for' => 'lsd_auth_login_remember_label',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_login_remember_label',
                'name' => 'lsd[login][remember_label]',
                'value' => $auth['login']['remember_label']
            ]); ?>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Button', 'listdom'),
            'for' => 'lsd_auth_login_submit_label',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_login_submit_label',
                'name' => 'lsd[login][submit_label]',
                'value' => $auth['login']['submit_label']
            ]); ?>
        </div>
    </div>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Placeholders', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Username', 'listdom'),
            'for' => 'lsd_auth_login_username_placeholder',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_login_username_placeholder',
                'name' => 'lsd[login][username_placeholder]',
                'value' => $auth['login']['username_placeholder']
            ]); ?>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Password', 'listdom'),
            'for' => 'lsd_auth_login_password_placeholder',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_login_password_placeholder',
                'name' => 'lsd[login][password_placeholder]',
                'value' => $auth['login']['password_placeholder']
            ]); ?>
        </div>
    </div>
</div>
