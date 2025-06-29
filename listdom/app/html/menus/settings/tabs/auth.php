<?php
// no direct access
defined('ABSPATH') || die();

// Auth Settings
$auth = LSD_Options::auth();

// Settings
$settings = LSD_Options::settings();
?>
<div class="lsd-auth-wrap">
    <form id="lsd_auth_form">
        <div class="lsd-accordion-title lsd-accordion-active lsd-accordion-title-auth">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Authentication', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-auth-form-group lsd-accordion-panel lsd-accordion-open lsd-accordion-panel-auth">
            <p class="description lsd-mb-4"><?php echo sprintf(esc_html__('In order to use this options you can put %s shortcode into any page.', 'listdom'), '<code>[listdom-auth]</code>'); ?></p>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Form Switcher', 'listdom'),
                    'for' => 'lsd_auth_switch_style',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::select([
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

            <h3><?php esc_html_e('Labels', 'listdom'); ?></h3>
            <div id="lsd-tabs-labels">
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Login Tab', 'listdom'),
                        'for' => 'lsd_auth_login_tab_label',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_auth_login_tab_label',
                            'name' => 'lsd[auth][login_tab_label]',
                            'value' => $auth['auth']['login_tab_label']
                        ]); ?>
                    </div>
                </div>

                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Register Tab', 'listdom'),
                        'for' => 'lsd_auth_register_tab_label',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_auth_register_tab_label',
                            'name' => 'lsd[auth][register_tab_label]',
                            'value' => $auth['auth']['register_tab_label']
                        ]); ?>
                    </div>
                </div>

                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Forgot Password Tab', 'listdom'),
                        'for' => 'lsd_auth_forgot_password_tab_label',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_auth_forgot_password_tab_label',
                            'name' => 'lsd[auth][forgot_password_tab_label]',
                            'value' => $auth['auth']['forgot_password_tab_label']
                        ]); ?>
                    </div>
                </div>
            </div>

            <div id="lsd-links-labels">
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Login Link', 'listdom'),
                        'for' => 'lsd_auth_login_link_label',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_auth_login_link_label',
                            'name' => 'lsd[auth][login_link_label]',
                            'value' => $auth['auth']['login_link_label'],
                        ]); ?>
                    </div>
                </div>

                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Register Link', 'listdom'),
                        'for' => 'lsd_auth_register_link_label',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_auth_register_link_label',
                            'name' => 'lsd[auth][register_link_label]',
                            'value' => $auth['auth']['register_link_label'],
                        ]); ?>
                    </div>
                </div>

                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Forgot Password Link', 'listdom'),
                        'for' => 'lsd_auth_forgot_password_link_label',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::text([
                            'id' => 'lsd_auth_forgot_password_link_label',
                            'name' => 'lsd[auth][forgot_password_link_label]',
                            'value' => $auth['auth']['forgot_password_link_label']
                        ]); ?>
                    </div>
                </div>
            </div>

            <h3><?php esc_html_e('Forms', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Hide Login', 'listdom'),
                    'for' => 'lsd_auth_hide_login_form',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_auth_hide_login_form',
                        'name' => 'lsd[auth][hide_login_form]',
                        'value' => $auth['auth']['hide_login_form'],
                        'toggle' => '#lsd-login-default-form'
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Hide Register', 'listdom'),
                    'for' => 'lsd_auth_hide_register_form',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_auth_hide_register_form',
                        'name' => 'lsd[auth][hide_register_form]',
                        'value' => $auth['auth']['hide_register_form'],
                        'toggle' => '#lsd-register-default-form'
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Hide Forgot Password', 'listdom'),
                    'for' => 'lsd_auth_hide_forgot_password_form',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_auth_hide_forgot_password_form',
                        'name' => 'lsd[auth][hide_forgot_password_form]',
                        'value' => $auth['auth']['hide_forgot_password_form'],
                        'toggle' => '#lsd-forgot-password-default-form'
                    ]); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Change Default Pages', 'listdom'); ?></h3>
            <p class="description lsd-mb-4"><?php echo esc_html__('Select the desired login, registration, and forgot password pages. These changes will apply site-wide.', 'listdom'); ?></p>

            <div class="lsd-default-forms <?php echo $auth['auth']['hide_login_form'] ? 'lsd-util-hide' : ''; ?>" id="lsd-login-default-form">
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Login', 'listdom'),
                        'for' => 'lsd_auth_login_form',
                    ]); ?></div>
                    <div class="lsd-col-4">
			            <?php echo LSD_Form::switcher([
				            'id' => 'lsd_auth_login_form',
				            'name' => 'lsd[auth][login_form]',
				            'value' => $auth['auth']['login_form'] ?? 0,
				            'toggle' => '#lsd_login_page_select'
			            ]); ?>
                    </div>
                </div>
                <div id="lsd_login_page_select" class="lsd-form-row lsd-mb-4 <?php echo $auth['auth']['login_form'] ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Login Page', 'listdom'),
                        'for' => 'lsd_auth_login_page',
                    ]); ?></div>
                    <div class="lsd-col-4">
			            <?php echo LSD_Form::pages([
				            'id' => 'lsd_auth_login_page',
				            'name' => 'lsd[auth][login_page]',
				            'value' => $auth['auth']['login_page'] ?? null,
				            'show_empty' => true,
			            ]); ?>
                    </div>
                </div>
            </div>

            <div class="lsd-default-forms <?php echo $auth['auth']['hide_register_form'] ? 'lsd-util-hide' : ''; ?>" id="lsd-register-default-form">
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Register', 'listdom'),
                        'for' => 'lsd_auth_register_form',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_auth_register_form',
                            'name' => 'lsd[auth][register_form]',
                            'value' => $auth['auth']['register_form'] ?? 0,
                            'toggle' => '#lsd_register_page_select'
                        ]); ?>
                    </div>
                </div>
                <div id="lsd_register_page_select" class="lsd-form-row lsd-mb-4 <?php echo $auth['auth']['register_form'] ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Register Page', 'listdom'),
                        'for' => 'lsd_auth_register_page',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::pages([
                            'id' => 'lsd_auth_register_page',
                            'name' => 'lsd[auth][register_page]',
                            'value' => $auth['auth']['register_page'] ?? null,
                            'show_empty' => true,
                        ]); ?>
                    </div>
                </div>
            </div>

            <div class="lsd-default-forms <?php echo $auth['auth']['hide_forgot_password_form'] ? 'lsd-util-hide' : ''; ?>" id="lsd-forgot-password-default-form">
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Forgot Password', 'listdom'),
                        'for' => 'lsd_auth_forgot_password_form',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_auth_forgot_password_form',
                            'name' => 'lsd[auth][forgot_password_form]',
                            'value' => $auth['auth']['forgot_password_form'] ?? 0,
                            'toggle' => '#lsd_forgot_password_page_select'
                        ]); ?>
                    </div>
                </div>
                <div id="lsd_forgot_password_page_select" class="lsd-form-row <?php echo $auth['auth']['forgot_password_form'] ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Forgot Password Page', 'listdom'),
                        'for' => 'lsd_auth_forgot_password_page',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::pages([
                            'id' => 'lsd_auth_forgot_password_page',
                            'name' => 'lsd[auth][forgot_password_page]',
                            'value' => $auth['auth']['forgot_password_page'] ?? null,
                            'show_empty' => true,
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title lsd-accordion-title-auth-login">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Login', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-accordion-panel-auth-login lsd-auth-form-group lsd-accordion-panel">
            <p class="description lsd-mb-4"><?php echo sprintf(esc_html__('In order to use this options you can put %s shortcode into any page.', 'listdom'), '<code>[listdom-login]</code>'); ?></p>

            <h3><?php esc_html_e('Labels', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Username', 'listdom'),
                    'for' => 'lsd_auth_login_username_label',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_login_username_label',
                        'name' => 'lsd[login][username_label]',
                        'value' => $auth['login']['username_label']
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Password', 'listdom'),
                    'for' => 'lsd_auth_login_password_label',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_login_password_label',
                        'name' => 'lsd[login][password_label]',
                        'value' => $auth['login']['password_label']
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Remember me', 'listdom'),
                    'for' => 'lsd_auth_login_remember_label',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_login_remember_label',
                        'name' => 'lsd[login][remember_label]',
                        'value' => $auth['login']['remember_label']
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Button', 'listdom'),
                    'for' => 'lsd_auth_login_submit_label',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_login_submit_label',
                        'name' => 'lsd[login][submit_label]',
                        'value' => $auth['login']['submit_label']
                    ]); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Placeholders', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Username', 'listdom'),
                    'for' => 'lsd_auth_login_username_placeholder',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_login_username_placeholder',
                        'name' => 'lsd[login][username_placeholder]',
                        'value' => $auth['login']['username_placeholder']
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Password', 'listdom'),
                    'for' => 'lsd_auth_login_password_placeholder',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_login_password_placeholder',
                        'name' => 'lsd[login][password_placeholder]',
                        'value' => $auth['login']['password_placeholder']
                    ]); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Redirect', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('After Login Redirect Page', 'listdom'),
                    'for' => 'lsd_auth_login_redirect',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::pages([
                        'id' => 'lsd_auth_login_redirect',
                        'name' => 'lsd[login][redirect]',
                        'value' => $auth['login']['redirect']
                    ]); ?>
                    <p class="description lsd-mb-0"><?php esc_html_e("After the user logs in, they will be redirected to the designated page.", 'listdom'); ?></p>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title lsd-accordion-title-auth-register">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Register', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>

        <div class="lsd-auth-form-group lsd-accordion-panel lsd-accordion-panel-auth-register">
            <p class="description lsd-mb-4"><?php echo sprintf(esc_html__('In order to use this options you can put %s shortcode into any page.', 'listdom'), '<code>[listdom-register]</code>'); ?></p>

            <h3><?php esc_html_e('Labels', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Username', 'listdom'),
                    'for' => 'lsd_auth_register_username_label',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_register_username_label',
                        'name' => 'lsd[register][username_label]',
                        'value' => $auth['register']['username_label']
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Password', 'listdom'),
                    'for' => 'lsd_auth_register_password_label',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_register_password_label',
                        'name' => 'lsd[register][password_label]',
                        'value' => $auth['register']['password_label']
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Email', 'listdom'),
                    'for' => 'lsd_auth_register_email_label',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_register_email_label',
                        'name' => 'lsd[register][email_label]',
                        'value' => $auth['register']['email_label']
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Button', 'listdom'),
                    'for' => 'lsd_auth_register_submit_label',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_register_submit_label',
                        'name' => 'lsd[register][submit_label]',
                        'value' => $auth['register']['submit_label']
                    ]); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Placeholders', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Username', 'listdom'),
                    'for' => 'lsd_auth_register_username_placeholder',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_register_username_placeholder',
                        'name' => 'lsd[register][username_placeholder]',
                        'value' => $auth['register']['username_placeholder']
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Password', 'listdom'),
                    'for' => 'lsd_auth_register_password_placeholder',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_register_password_placeholder',
                        'name' => 'lsd[register][password_placeholder]',
                        'value' => $auth['register']['password_placeholder']
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Email', 'listdom'),
                    'for' => 'lsd_auth_register_email_placeholder',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_register_email_placeholder',
                        'name' => 'lsd[register][email_placeholder]',
                        'value' => $auth['register']['email_placeholder']
                    ]); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Auto Login', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Auto Login', 'listdom'),
                    'for' => 'lsd_auth_login_after_register',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_auth_login_after_register',
                        'name' => 'lsd[register][login_after_register]',
                        'value' => $auth['register']['login_after_register'],
                        'toggle' => '#lsd_redirect_setting',
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row <?php echo $auth['register']['login_after_register'] == 0 ? 'lsd-util-hide' : '' ?>"
                 id="lsd_redirect_setting">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('After Register Redirect Page', 'listdom'),
                    'for' => 'lsd_auth_register_redirect',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::pages([
                        'id' => 'lsd_auth_register_redirect',
                        'name' => 'lsd[register][redirect]',
                        'value' => $auth['register']['redirect']
                    ]); ?>
                    <p class="description lsd-mb-0"><?php esc_html_e("After the user registers, they will be redirected to the designated page.", 'listdom'); ?></p>
                </div>
            </div>

            <h3><?php esc_html_e('Password Policy', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Strong Password', 'listdom'),
                    'for' => 'lsd_auth_strong_password',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_auth_strong_password',
                        'name' => 'lsd[register][strong_password]',
                        'value' => $auth['register']['strong_password'],
                        'toggle' => "#lsd-strong-password-setting"
                    ]); ?>
                </div>
            </div>

            <div id="lsd-strong-password-setting" class="<?php echo $auth['register']['strong_password'] == 0 ? 'lsd-util-hide' : '' ?>">
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Password Length', 'listdom'),
                        'for' => 'lsd_auth_password_length',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::number([
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
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Contain Uppercase', 'listdom'),
                        'for' => 'lsd_auth_contain_uppercase',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_auth_contain_uppercase',
                            'name' => 'lsd[register][contain_uppercase]',
                            'value' => $auth['register']['contain_uppercase'],
                        ]); ?>
                    </div>
                </div>

                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Contain Lowercase', 'listdom'),
                        'for' => 'lsd_auth_contain_lowercase',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_auth_contain_lowercase',
                            'name' => 'lsd[register][contain_lowercase]',
                            'value' => $auth['register']['contain_lowercase'],
                        ]); ?>
                    </div>
                </div>

                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Contain Numbers', 'listdom'),
                        'for' => 'lsd_auth_contain_numbers',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_auth_contain_numbers',
                            'name' => 'lsd[register][contain_numbers]',
                            'value' => $auth['register']['contain_numbers'],
                        ]); ?>
                    </div>
                </div>

                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Contain Special Characters', 'listdom'),
                        'for' => 'lsd_auth_contain_specials',
                    ]); ?></div>
                    <div class="lsd-col-4">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_auth_contain_specials',
                            'name' => 'lsd[register][contain_specials]',
                            'value' => $auth['register']['contain_specials'],
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title lsd-accordion-title-auth-forgot-password">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Forgot Password', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>

        <div class="lsd-auth-form-group lsd-accordion-panel lsd-accordion-panel-auth-forgot-password">
            <p class="description lsd-mb-4"><?php echo sprintf(esc_html__('In order to use this options you can put %s shortcode into any page.', 'listdom'), '<code>[listdom-forgot-password]</code>'); ?></p>

            <h3><?php esc_html_e('Labels', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Email', 'listdom'),
                    'for' => 'lsd_auth_forgot_password_email_label',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_forgot_password_email_label',
                        'name' => 'lsd[forgot_password][email_label]',
                        'value' => $auth['forgot_password']['email_label']
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Button Label', 'listdom'),
                    'for' => 'lsd_auth_forgot_password_submit_label',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_forgot_password_submit_label',
                        'name' => 'lsd[forgot_password][submit_label]',
                        'value' => $auth['forgot_password']['submit_label']
                    ]); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Placeholders', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Email', 'listdom'),
                    'for' => 'lsd_auth_forgot_password_email_placeholder',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::text([
                        'id' => 'lsd_auth_forgot_password_email_placeholder',
                        'name' => 'lsd[forgot_password][email_placeholder]',
                        'value' => $auth['forgot_password']['email_placeholder']
                    ]); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Redirect', 'listdom'); ?></h3>
            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Page', 'listdom'),
                    'for' => 'lsd_auth_forgot_password_redirect',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::pages([
                        'id' => 'lsd_auth_forgot_password_redirect',
                        'name' => 'lsd[forgot_password][redirect]',
                        'value' => $auth['forgot_password']['redirect']
                    ]); ?>
                    <p class="description lsd-mb-0"><?php esc_html_e("After the user receives the password recovery email, they will be redirected to the designated page.", 'listdom'); ?></p>
                </div>
            </div>
        </div>

        <div class="lsd-accordion-title lsd-accordion-title-auth-profile">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Profile & User Directory', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>

        <div class="lsd-auth-form-group lsd-accordion-panel lsd-accordion-panel-auth-profile">
            <h3 class="lsd-mt-0"><?php esc_html_e('User Profile', 'listdom'); ?></h3>
            <p class="description lsd-mb-4"><?php echo sprintf(esc_html__('In order to use this options you can put %s shortcode into any page.', 'listdom'), '<code>[listdom-profile]</code>'); ?></p>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Profile Page', 'listdom'),
                    'for' => 'lsd_author_profile_page',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::pages([
                        'id' => 'lsd_author_profile_page',
                        'name' => 'lsd[profile][page]',
                        'show_empty' => true,
                        'value' => $auth['profile']['page']
                    ]); ?>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Author Listings Shortcode', 'listdom'),
                    'for' => 'lsd_author_shortcode',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::shortcodes([
                        'id' => 'lsd_author_shortcode',
                        'name' => 'lsd[profile][shortcode]',
                        'value' => $auth['profile']['shortcode'] ?? '',
                        'only_archive_skins' => true,
                        'show_empty' => true,
                    ]); ?>
                </div>
            </div>

            <h3 class="lsd-mt-5"><?php esc_html_e('User Directory', 'listdom'); ?></h3>
            <p class="description lsd-mb-4"><?php echo sprintf(esc_html__("To use the user directory, you can place the %s shortcode on any page. The shortcode supports both List and Grid styles. Feel free to use one of the following shortcodes:", 'listdom'), '<code>[listdom-users]</code>'); ?></p>
            <ul>
                <li class="lsd-mb-2">[listdom-users style="list" limit="24"]</li>
                <li>[listdom-users style="grid" limit="12" columns="3"]</li>
            </ul>
        </div>

        <div class="lsd-accordion-title lsd-accordion-title-auth-logged-in-users">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Logged In Users', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>

        <div class="lsd-auth-form-group lsd-accordion-panel lsd-accordion-panel-auth-logged-in-users">

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Redirect After Logout Page', 'listdom'),
                    'for' => 'lsd_auth_logout_redirect',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::pages([
                        'id' => 'lsd_auth_logout_redirect',
                        'name' => 'lsd[logout][redirect]',
                        'show_empty' => true,
                        'value' => $auth['logout']['redirect']
                    ]); ?>
                    <p class="description"><?php esc_html_e("After the user logs out, they will be redirected to the designated page.", 'listdom'); ?></p>
                </div>
            </div>

            <div class="lsd-form-row">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Account URL', 'listdom'),
                    'for' => 'lsd_auth_account_redirect',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::pages([
                        'id' => 'lsd_auth_account_redirect',
                        'name' => 'lsd[account][redirect]',
                        'value' => $auth['account']['redirect']
                    ]); ?>
                    <p class="description lsd-mb-0"><?php esc_html_e("If the user is logged in and clicks on the account button, they will be redirected to the designated page.", 'listdom'); ?></p>
                </div>
            </div>

        </div>
        <div class="lsd-accordion-title lsd-accordion-title-auth-block-admin-access">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Block Admin Access', 'listdom'); ?></h3>
                <div class="lsd-accordion-icons">
                    <i class="lsd-icon fa fa-plus"></i>
                    <i class="lsd-icon fa fa-minus"></i>
                </div>
            </div>
        </div>
        <div class="lsd-settings-form-group lsd-accordion-panel lsd-accordion-panel-auth-block-admin-access">
            <div class="lsd-form-row lsd-mt-0">
                <div class="lsd-col-12">
                    <p class="description lsd-mt-0 lsd-mb-5"><?php esc_html_e('You can block WordPress admin access for the following user roles, if needed. Check to block access, or uncheck to allow it.', 'listdom'); ?></p>

                    <?php foreach ([
                        'subscriber' => esc_html__('Subscriber', 'listdom'),
                        'contributor' => esc_html__('Contributor', 'listdom'),
                        'listdom_author' => esc_html__('Listdom Author', 'listdom'),
                        'listdom_publisher' => esc_html__('Listdom Publisher', 'listdom'),
                    ] as $role => $label): ?>
                        <div class="lsd-form-row">
                            <div class="lsd-col-2"><?php echo LSD_Form::label([
                                'for' => 'lsd_block_admin_role_'.$role,
                                'title' => $label
                            ]); ?></div>
                            <div class="lsd-col-10"><?php echo LSD_Form::switcher([
                                'id' => 'lsd_block_admin_role_'.$role,
                                'name' => 'settings[block_admin_'.$role.']',
                                'value' => $settings['block_admin_'.$role] ?? 1
                            ]); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php do_action('lsd_auth_form_general', $auth); ?>

        <div class="lsd-spacer-10"></div>
        <div class="lsd-form-row">
            <div class="lsd-col-12 lsd-flex lsd-gap-3">
                <?php LSD_Form::nonce('lsd_auth_form'); ?>
                <?php echo LSD_Form::submit([
                    'label' => esc_html__('Save', 'listdom'),
                    'id' => 'lsd_auth_save_button',
                    'class' => 'button button-hero button-primary',
                ]); ?>
                <div>
                    <p class="lsd-util-hide lsd-auth-success-message lsd-alert lsd-success lsd-m-0"><?php esc_html_e('Options saved successfully.', 'listdom'); ?></p>
                    <p class="lsd-util-hide lsd-auth-error-message lsd-alert lsd-error lsd-m-0"><?php esc_html_e('Error: Unable to save options.', 'listdom'); ?></p>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
jQuery('#lsd_auth_form').on('submit', function (e)
{
    e.preventDefault();

    // Elements
    const $button = jQuery("#lsd_auth_save_button");
    const $success = jQuery(".lsd-auth-success-message");
    const $error = jQuery(".lsd-auth-error-message");
    const $tab = jQuery('.nav-tab-active');

    // Loading Styles
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    const auth = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_auth&" + auth,
        success: function()
        {
            $tab.attr('data-saved', 'true');

            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($success, 2000);
        },
        error: function()
        {
            $tab.attr('data-saved', 'false');

            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($error, 2000);
        }
    });
});
</script>
