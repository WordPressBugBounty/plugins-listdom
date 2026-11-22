<?php
defined('ABSPATH') || die();
?>
<div class="lsd-alert lsd-info lsd-mt-0">
    <strong><?php esc_html_e('Listdom roles:', 'listdom'); ?></strong>
    <?php echo esc_html__('Listdom adds two new user roles. Listdom Authors can create listings and manage their own submissions, while Listdom Publishers can publish listings directly and manage published ones.', 'listdom'); ?>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Registration Policy & Roles', 'listdom'); ?></h3>
    <?php $row('lsd_registration_policy', __('Public Registration', 'listdom'), LSD_Form::select([
        'class' => 'lsd-admin-input lsd-trigger-select-options', 'id' => 'lsd_registration_policy', 'name' => 'lsd[register][policy]', 'value' => $auth['register']['policy'],
        'options' => [
            'wordpress' => ['label' => __('Follow WordPress', 'listdom'), 'attributes' => ['data-lsd-show' => '#lsd_registration_role_fields']],
            'enabled' => ['label' => __('Enabled', 'listdom'), 'attributes' => ['data-lsd-show' => '#lsd_registration_role_fields']],
            'disabled' => ['label' => __('Disabled', 'listdom'), 'attributes' => ['data-lsd-hide' => '#lsd_registration_role_fields']],
        ],
    ]), __('Controls Listdom and Listdomer public forms and REST registration. Enabled can override the single-site WordPress setting; multisite network restrictions still apply. Native WordPress registration keeps its own setting. Explicitly authorized imports, approvals, guest-submission workflows, and API push integrations keep their separate authorization.', 'listdom')); ?>
    <div id="lsd_registration_role_fields" class="<?php echo $auth['register']['policy'] === 'disabled' ? 'lsd-util-hide' : ''; ?>">
        <div class="lsd-settings-fields-sub-wrapper">
            <div class="lsd-form-row">
                <div class="lsd-col-3"><span class="lsd-fields-label"><?php esc_html_e('Public Roles', 'listdom'); ?></span></div>
                <div class="lsd-col-5">
                    <input type="hidden" name="lsd[register][public_roles][]" value="">
                    <?php echo LSD_Form::checkboxes([
                        'class' => 'lsd-my-0 lsd-ai-checkboxes',
                        'name' => 'lsd[register][public_roles][]',
                        'value' => LSD_Security::public_roles(),
                        'options' => $safe_roles,
                    ]); ?>
                    <p class="lsd-admin-description-tiny"><?php esc_html_e('Only checked roles can be assigned by public registration, including role-specific shortcodes. Roles with administrative capabilities cannot be approved.', 'listdom'); ?></p>
                </div>
            </div>
            <?php $row('lsd_registration_default_role', __('Default Role', 'listdom'), LSD_Form::select(['class' => 'lsd-admin-input', 'id' => 'lsd_registration_default_role', 'name' => 'lsd[register][default_role]', 'value' => $auth['register']['default_role'], 'options' => $safe_roles, 'show_empty' => true]), __('Select an approved role. This replaces the default user role for Listdom-created users.', 'listdom')); ?>
        </div>
    </div>
</div>
<p class="lsd-admin-description lsd-m-0"><?php echo sprintf(
    /* translators: %s: Register shortcode. */
    esc_html__('Insert the %s shortcode into any page to display a register form.', 'listdom'),
    '<code>[listdom-register]</code>'
); ?></p>
<div class="lsd-alert lsd-info lsd-my-0">
    <?php echo esc_html__('You can restrict the register form to a specific role using the role parameter (subscriber, contributor, listdom_author or listdom_publisher). Example:', 'listdom'); ?>
    <strong>[listdom-register role="listdom_author"]</strong>
</div>
<p class="lsd-admin-description"><a href="<?php echo esc_url(LSD_Portal::admin_url('profiles-directory', 'fields')); ?>"><?php esc_html_e('Manage registration fields in Users → Profiles & Directory → Fields', 'listdom'); ?></a></p>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Email Verification', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Require Email Verification', 'listdom'),
            'for' => 'lsd_auth_register_email_verification',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_auth_register_email_verification',
                'name' => 'lsd[register][email_verification]',
                'value' => $auth['register']['email_verification'] ?? 0,
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('When enabled, newly registered users must verify their email address before they can log in. Configure the verification email in Listdom &gt; Notifications.', 'listdom'); ?></p>
        </div>
    </div>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title lsd-m-0"><?php esc_html_e('Privacy Consent', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Enable', 'listdom'),
            'for' => 'lsd_auth_register_pc_enabled',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_auth_register_pc_enabled',
                'name' => 'lsd[register][pc_enabled]',
                'value' => $auth['register']['pc_enabled'],
                'toggle' => '#lsd_auth_register_pc_label_row',
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('This control also depends on the global privacy consent setting.', 'listdom'); ?></p>
        </div>
    </div>
    <div class="lsd-form-row<?php echo empty($auth['register']['pc_enabled']) ? ' lsd-util-hide' : ''; ?>" id="lsd_auth_register_pc_label_row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Label', 'listdom'),
            'for' => 'lsd_auth_register_pc_label',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_register_pc_label',
                'name' => 'lsd[register][pc_label]',
                'value' => $auth['register']['pc_label'],
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Leave empty to use the default label. Use {{privacy_policy}} to automatically include the default privacy policy page link.', 'listdom'); ?></p>
        </div>
    </div>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Auto Login', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Auto Login', 'listdom'),
            'for' => 'lsd_auth_login_after_register',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_auth_login_after_register',
                'name' => 'lsd[register][login_after_register]',
                'value' => $auth['register']['login_after_register'],
                'toggle' => '#lsd_redirect_setting',
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('If email verification is enabled, new users will remain logged out until they confirm their email regardless of this setting.', 'listdom'); ?></p>
        </div>
    </div>

    <div class="lsd-form-row <?php echo $auth['register']['login_after_register'] == 0 ? 'lsd-util-hide' : '' ?>" id="lsd_redirect_setting">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('After Register Redirect Page', 'listdom'),
            'for' => 'lsd_auth_register_redirect',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::pages([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_register_redirect',
                'name' => 'lsd[register][redirect]',
                'value' => $auth['register']['redirect']
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("After the user registers, they will be redirected to the designated page.", 'listdom'); ?></p>
        </div>
    </div>

    <h3 class="lsd-admin-title"><?php esc_html_e('Auto Login Per User Role', 'listdom'); ?></h3>
    <?php foreach (LSD_Roles::supported() as $role => $label): ?>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'for' => 'lsd_auth_register_redirect_role_'. $role,
                'title' => $label
            ]); ?></div>
            <div class="lsd-col-9"><?php echo LSD_Form::switcher([
                'id' => 'lsd_auth_register_redirect_role_' . $role,
                'name' => 'lsd[register][redirect_'. $role.']',
                'value' => $auth['register']['redirect_'. $role],
                'toggle' => '#lsd-auth-register-redirect-page-' . esc_attr($role) . '-content',
            ]); ?></div>
        </div>
        <div class="lsd-form-row <?php echo ($auth['register']['redirect_'. $role]) == 0 ? 'lsd-util-hide' : ''; ?>" id="<?php echo esc_attr('lsd-auth-register-redirect-page-' . esc_attr($role) . '-content'); ?>" >
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Redirect Page', 'listdom'),
                'for' => 'lsd_auth_register_redirect_' . $role,
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::pages([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_register_redirect_' . $role,
                    'name' => 'lsd[register]['. $role .'][redirect]',
                    'value' => $auth['register'][$role]['redirect'] ?? $auth['register']['redirect'],
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo sprintf(
    /* translators: %s: User role label. */
    esc_html__("After registering, the %s user will be redirected to the designated page.", 'listdom'),
    '<strong>'.$label.'</strong>'
); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<p class="lsd-admin-description"><a href="<?php echo esc_url(LSD_Portal::admin_url('security', 'password-policy')); ?>"><?php esc_html_e('Manage password requirements in Account Security → Password Policy', 'listdom'); ?></a></p>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Labels', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Username', 'listdom'),
            'for' => 'lsd_auth_register_username_label',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_register_username_label',
                'name' => 'lsd[register][username_label]',
                'value' => $auth['register']['username_label']
            ]); ?>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Password', 'listdom'),
            'for' => 'lsd_auth_register_password_label',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_register_password_label',
                'name' => 'lsd[register][password_label]',
                'value' => $auth['register']['password_label']
            ]); ?>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Email', 'listdom'),
            'for' => 'lsd_auth_register_email_label',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_register_email_label',
                'name' => 'lsd[register][email_label]',
                'value' => $auth['register']['email_label']
            ]); ?>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Button', 'listdom'),
            'for' => 'lsd_auth_register_submit_label',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_register_submit_label',
                'name' => 'lsd[register][submit_label]',
                'value' => $auth['register']['submit_label']
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
            'for' => 'lsd_auth_register_username_placeholder',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_register_username_placeholder',
                'name' => 'lsd[register][username_placeholder]',
                'value' => $auth['register']['username_placeholder']
            ]); ?>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Password', 'listdom'),
            'for' => 'lsd_auth_register_password_placeholder',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_register_password_placeholder',
                'name' => 'lsd[register][password_placeholder]',
                'value' => $auth['register']['password_placeholder']
            ]); ?>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Email', 'listdom'),
            'for' => 'lsd_auth_register_email_placeholder',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_register_email_placeholder',
                'name' => 'lsd[register][email_placeholder]',
                'value' => $auth['register']['email_placeholder']
            ]); ?>
        </div>
    </div>
</div>
