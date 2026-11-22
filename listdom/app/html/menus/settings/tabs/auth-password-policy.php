<?php
defined('ABSPATH') || die();
?>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Password Policy', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Strong Password', 'listdom'),
            'for' => 'lsd_auth_strong_password',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_auth_strong_password',
                'name' => 'lsd[register][strong_password]',
                'value' => $auth['register']['strong_password'],
                'toggle' => "#lsd-strong-password-setting"
            ]); ?>
        </div>
    </div>

    <div id="lsd-strong-password-setting" class="lsd-settings-fields-sub-wrapper <?php echo $auth['register']['strong_password'] == 0 ? 'lsd-util-hide' : '' ?>">
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Password Length', 'listdom'),
                'for' => 'lsd_auth_password_length',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::number([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_auth_password_length',
                    'name' => 'lsd[register][password_length]',
                    'value' => $auth['register']['password_length'],
                    'attributes' => [
                        'min' => 8,
                        'max' => 24,
                        'step' => 1
                    ],
                ]); ?>
            </div>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Contain Uppercase', 'listdom'),
                'for' => 'lsd_auth_contain_uppercase',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_auth_contain_uppercase',
                    'name' => 'lsd[register][contain_uppercase]',
                    'value' => $auth['register']['contain_uppercase'],
                ]); ?>
            </div>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Contain Lowercase', 'listdom'),
                'for' => 'lsd_auth_contain_lowercase',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_auth_contain_lowercase',
                    'name' => 'lsd[register][contain_lowercase]',
                    'value' => $auth['register']['contain_lowercase'],
                ]); ?>
            </div>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Contain Numbers', 'listdom'),
                'for' => 'lsd_auth_contain_numbers',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_auth_contain_numbers',
                    'name' => 'lsd[register][contain_numbers]',
                    'value' => $auth['register']['contain_numbers'],
                ]); ?>
            </div>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Contain Special Characters', 'listdom'),
                'for' => 'lsd_auth_contain_specials',
            ]); ?></div>
            <div class="lsd-col-5">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_auth_contain_specials',
                    'name' => 'lsd[register][contain_specials]',
                    'value' => $auth['register']['contain_specials'],
                ]); ?>
            </div>
        </div>
    </div>
</div>
