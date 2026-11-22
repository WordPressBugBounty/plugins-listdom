<?php
defined('ABSPATH') || die();
?>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Account Links & Logout', 'listdom'); ?></h3>
    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Redirect After Logout Page', 'listdom'),
            'for' => 'lsd_auth_logout_redirect',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::pages([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_logout_redirect',
                'name' => 'lsd[logout][redirect]',
                'show_empty' => true,
                'value' => $auth['logout']['redirect']
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("After the user logs out, they will be redirected to the designated page.", 'listdom'); ?></p>
        </div>
    </div>

    <div class="lsd-form-row">
        <div class="lsd-col-3"><?php echo LSD_Form::label([
            'class' => 'lsd-fields-label',
            'title' => esc_html__('Account URL', 'listdom'),
            'for' => 'lsd_auth_account_redirect',
        ]); ?></div>
        <div class="lsd-col-5">
            <?php echo LSD_Form::pages([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_auth_account_redirect',
                'name' => 'lsd[account][redirect]',
                'value' => $auth['account']['redirect']
            ]); ?>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("If the user is logged in and clicks on the account button, they will be redirected to the designated page.", 'listdom'); ?></p>
        </div>
    </div>
</div>
