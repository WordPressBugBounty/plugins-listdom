<?php
defined('ABSPATH') || die();
?>
<p class="lsd-admin-description lsd-m-0"><?php echo sprintf(
    /* translators: %s: Forgot password shortcode. */
    esc_html__('Insert the %s shortcode into any page to display a forgot password form.', 'listdom'),
    '<code>[listdom-forgot-password]</code>'
); ?></p>
                <div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Labels', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Email', 'listdom'),
            'for' => 'lsd_auth_forgot_password_email_label',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_forgot_password_email_label',
                'name' => 'lsd[forgot_password][email_label]',
                'value' => $auth['forgot_password']['email_label']
            ]); ?>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Button Label', 'listdom'),
            'for' => 'lsd_auth_forgot_password_submit_label',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_forgot_password_submit_label',
                'name' => 'lsd[forgot_password][submit_label]',
                'value' => $auth['forgot_password']['submit_label']
            ]); ?>
        </div>
    </div>
</div>
                <div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Placeholders', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Email', 'listdom'),
            'for' => 'lsd_auth_forgot_password_email_placeholder',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_forgot_password_email_placeholder',
                'name' => 'lsd[forgot_password][email_placeholder]',
                'value' => $auth['forgot_password']['email_placeholder']
            ]); ?>
        </div>
    </div>
</div>

<p class="lsd-admin-description"><a href="<?php echo esc_url(LSD_Portal::admin_url('security', 'password-policy')); ?>"><?php esc_html_e('Manage password requirements in Account Security → Password Policy', 'listdom'); ?></a></p>
