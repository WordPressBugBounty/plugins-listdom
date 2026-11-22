<?php
defined('ABSPATH') || die();
?>
<div class="lsd-native-methods" data-passkey-error="<?php esc_attr_e('Passkey sign-in is unavailable or was cancelled. Please try again or use another method.', 'listdom'); ?>">
    <?php if ($form === 'login'): ?>
        <div class="lsd-login-methods-divider"><?php esc_html_e('Or sign in with', 'listdom'); ?></div>
    <?php endif; ?>
    <?php if ($providers): ?>
        <div class="lsd-native-providers">
            <?php foreach ($providers as $provider => $definition): ?>
                <form method="post" action="<?php echo esc_url(LSD_Auth::url('start')); ?>" class="lsd-native-start">
                    <?php wp_nonce_field('lsd_native_auth', '_wpnonce', false); ?>
                    <input type="hidden" name="context" value="<?php echo esc_attr($context); ?>">
                    <input type="hidden" name="provider" value="<?php echo esc_attr($provider); ?>">
                    <?php echo LSD_Auth_Buttons::render($provider, $definition['label'], LSD_Auth_Buttons::settings(), false, $form === 'login'); ?>
                </form>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($magic || $passkeys): ?>
        <div class="lsd-native-passwordless">
            <?php if ($passkeys): ?>
                <form method="post" action="<?php echo esc_url(LSD_Auth::url('passkey')); ?>" class="lsd-native-passkey" data-operation="login_options">
                    <?php wp_nonce_field('lsd_native_auth', '_wpnonce', false); ?>
                    <input type="hidden" name="context" value="<?php echo esc_attr($context); ?>">
                    <button class="lsd-light-button lsd-w-full" type="submit"><?php esc_html_e('Sign in with a passkey', 'listdom'); ?></button>
                    <div class="lsd-native-message" role="status" aria-live="polite" hidden></div>
                </form>
            <?php endif; ?>
            <?php if ($magic): ?>
                <details class="lsd-native-magic">
                    <summary class="lsd-light-button"><?php esc_html_e('Email me a sign-in link', 'listdom'); ?></summary>
                    <form method="post" action="<?php echo esc_url(LSD_Auth::url('magic')); ?>" class="lsd-native-magic-form login-form-content" data-error="<?php esc_attr_e('Unable to request a sign-in link. Please try again.', 'listdom'); ?>">
                        <?php wp_nonce_field('lsd_native_auth', '_wpnonce', false); ?>
                        <input type="hidden" name="context" value="<?php echo esc_attr($context); ?>">
                        <div class="form-group">
                            <?php
                            echo LSD_Form::label(['for' => $instance . '-identifier', 'title' => __('Username or email address', 'listdom')]);
                            echo LSD_Form::text([
                                'id' => $instance . '-identifier',
                                'name' => 'identifier',
                                'attributes' => ['autocomplete' => 'username'],
                                'required' => true,
                            ]);
                            ?>
                        </div>
                        <div class="form-group">
                            <button class="lsd-general-button" type="submit"><?php esc_html_e('Send sign-in link', 'listdom'); ?></button>
                        </div>
                    </form>
                    <div class="lsd-native-message lsd-mt-3" role="status" tabindex="-1" hidden></div>
                    <div class="lsd-native-magic-back lsd-mt-3" hidden>
                        <button type="button" class="lsd-text-button"><?php esc_html_e('Back to sign in', 'listdom'); ?></button>
                    </div>
                </details>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
