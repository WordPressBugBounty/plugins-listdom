<?php
defined('ABSPATH') || die();
?>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Shortcode & Usage', 'listdom'); ?></h3>
    <p class="lsd-admin-description lsd-m-0"><?php echo sprintf(
        /* translators: %s: Authentication shortcode. */
        esc_html__('Insert the %s shortcode into any page to display  a full login, register, and forgot password form.', 'listdom'),
        '<code>[listdom-auth]</code>'
    ); ?></p>
    <div class="lsd-alert lsd-info lsd-my-0">
        <?php echo esc_html__('You can restrict the login and register forms to a specific role using the role parameter (subscriber, contributor, listdom_author or listdom_publisher). Example:', 'listdom'); ?>
        <strong>[listdom-auth role="listdom_author"]</strong>
    </div>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Forms & Switcher', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Form Switcher', 'listdom'),
            'for' => 'lsd_auth_switch_style',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::select([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_switch_style',
                'name' => 'lsd[auth][switch_style]',
                'options' => [
                    'both' => esc_html__('Links & Tabs', 'listdom'),
                    'tabs' => esc_html__('Tabs', 'listdom'),
                    'links' => esc_html__('Links', 'listdom'),
                ],
                'value' => $auth['auth']['switch_style'],
            ]); ?>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Hide Login', 'listdom'),
            'for' => 'lsd_auth_hide_login_form',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_auth_hide_login_form',
                'name' => 'lsd[auth][hide_login_form]',
                'value' => $auth['auth']['hide_login_form'],
                'toggle' => '#lsd-login-default-form'
            ]); ?>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Hide Register', 'listdom'),
            'for' => 'lsd_auth_hide_register_form',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_auth_hide_register_form',
                'name' => 'lsd[auth][hide_register_form]',
                'value' => $auth['auth']['hide_register_form'],
                'toggle' => '#lsd-register-default-form'
            ]); ?>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Hide Forgot Password', 'listdom'),
            'for' => 'lsd_auth_hide_forgot_password_form',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_auth_hide_forgot_password_form',
                'name' => 'lsd[auth][hide_forgot_password_form]',
                'value' => $auth['auth']['hide_forgot_password_form'],
                'toggle' => '#lsd-forgot-password-default-form'
            ]); ?>
        </div>
    </div>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Tab Labels', 'listdom'); ?></h3>
    <div id="lsd-tabs-labels">
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Login Tab', 'listdom'),
                'for' => 'lsd_auth_login_tab_label',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_login_tab_label',
                    'name' => 'lsd[auth][login_tab_label]',
                    'value' => $auth['auth']['login_tab_label']
                ]); ?>
            </div>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Register Tab', 'listdom'),
                'for' => 'lsd_auth_register_tab_label',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_register_tab_label',
                    'name' => 'lsd[auth][register_tab_label]',
                    'value' => $auth['auth']['register_tab_label']
                ]); ?>
            </div>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Forgot Password Tab', 'listdom'),
                'for' => 'lsd_auth_forgot_password_tab_label',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_forgot_password_tab_label',
                    'name' => 'lsd[auth][forgot_password_tab_label]',
                    'value' => $auth['auth']['forgot_password_tab_label']
                ]); ?>
            </div>
        </div>
    </div>
</div>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Link Labels', 'listdom'); ?></h3>
    <div id="lsd-links-labels">
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Login Link', 'listdom'),
                'for' => 'lsd_auth_login_link_label',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_login_link_label',
                    'name' => 'lsd[auth][login_link_label]',
                    'value' => $auth['auth']['login_link_label'],
                ]); ?>
            </div>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Register Link', 'listdom'),
                'for' => 'lsd_auth_register_link_label',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_register_link_label',
                    'name' => 'lsd[auth][register_link_label]',
                    'value' => $auth['auth']['register_link_label'],
                ]); ?>
            </div>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Forgot Password Link', 'listdom'),
                'for' => 'lsd_auth_forgot_password_link_label',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_forgot_password_link_label',
                    'name' => 'lsd[auth][forgot_password_link_label]',
                    'value' => $auth['auth']['forgot_password_link_label']
                ]); ?>
            </div>
        </div>
    </div>
</div>
