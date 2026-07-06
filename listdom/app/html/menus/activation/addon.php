<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Activation $this */
/** @var string $key */
/** @var array $product */

$licensing = $product['licensing'] ?? null;
if (!$licensing) return;

$status = LSD_Licensing::getStatus($licensing->getBasename(), $licensing->getPrefix());
$prefix = $licensing->getPrefix();
$shop_url = $this->getWebiliaShopURL();
$state = $status['state'];

$primary_message_payload = LSD_Licensing::getMessagePayload($status, $product, 'primary', $prefix, $shop_url);
$trial_notice_payload = LSD_Licensing::getMessagePayload($status, $product, 'trial_notice', $prefix, $shop_url);
$trial_inline_payload = LSD_Licensing::getMessagePayload($status, $product, 'trial_inline', $prefix, $shop_url);
$expiring_notice_payload = LSD_Licensing::getMessagePayload($status, $product, 'expiring_notice', $prefix, $shop_url);
?>
<div id="lsd-license-card-<?php echo esc_attr($key); ?>" class="lsd-license-card <?php echo esc_attr($status['card_class']); ?>">
    <div class="lsd-validation">
        <div class="lsd-addon-image">
            <?php if (isset($product['icon'])): ?>
                <img src="<?php echo esc_url($product['icon']); ?>" alt="<?php echo esc_attr($product['name']); ?>">
            <?php endif; ?>
        </div>
        <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html($product['name']); ?></h3>
        <div class="lsd-badge <?php echo esc_attr($status['badge']['class']); ?>">
            <i class="listdom-icon <?php echo esc_attr($status['badge']['icon']); ?>"></i>
            <?php echo esc_html($status['badge']['text']); ?>
        </div>
    </div>
    <?php if ($status['show_status']): ?>
        <div class="lsd-license-status">
            <div class="lsd-expiry-status">
                <div class="lsd-progress-bar">
                    <div class="lsd-progress-fill <?php echo esc_attr($status['progress_class']); ?>" style="width: <?php echo esc_attr($status['progress']); ?>%;"></div>
                </div>
                <div class="lsd-expiry-details">
                    <?php if ($status['status_text']): ?>
                        <span><?php echo esc_html($status['status_text']); ?></span>
                    <?php endif; ?>
                    <?php if ($status['show_valid_from']): ?>
                        <span class="lsd-valid-date"><?php echo sprintf("Valid from %s", $status['installed_date']); ?></span>
                    <?php endif; ?>
                    <?php if ($status['expiry_mode'] !== 'none'): ?>
                        <div class="lsd-expiry lsd-expiry-<?php echo esc_attr($status['state']); ?>">
                            <?php if ($status['expiry_mode'] === 'error'): ?>
                                <span><?php esc_html_e('Error loading the details', 'listdom'); ?></span>
                            <?php elseif ($status['expiry_mode'] === 'days_remaining'): ?>
                                <span class="lsd-circle"></span>
                                <span class="lsd-expiry-text"><?php
                                    echo wp_kses(
                                        sprintf(
                                        /* translators: %s: Number of days remaining on the license. */
                                            __('%s days remaining', 'listdom'),
                                            '<span class="lsd-days">' . intval($status['days_remaining']) . '</span>'
                                        ),
                                        [
                                            'span' => ['class' => []],
                                        ]
                                    );
                                ?></span>
                            <?php else: ?>
                                <?php if($status['state'] === 'active'): ?>
                                    <span class="lsd-circle"></span>
                                    <span class="lsd-expiry-text lsd-expiry-lifetime"><?php echo esc_html($status['validation_status']['expiry']); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($status['action'] === 'renew'): ?>
                <div>
                    <a class="lsd-primary-button" href="<?php echo esc_url($shop_url); ?>"
                       target="_blank">
                        <?php esc_html_e('Renew License', 'listdom'); ?>
                        <i class="webilia-icon wbli-key"></i>
                    </a>
                </div>
            <?php elseif ($status['action'] === 'get_license'): ?>
                <div>
                    <a class="lsd-primary-button" href="<?php echo esc_url($shop_url); ?>"
                       target="_blank">
                        <?php esc_html_e('Get License', 'listdom'); ?>
                        <i class="webilia-icon wbli-key"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if (!$status['valid_license']): ?>
        <?php if ($state !== 'trial' && !empty($primary_message_payload['message'])): ?>
            <div class="lsd-form-row lsd-activation-guide">
                <div class="lsd-col-12">
                    <?php echo $this->alert($primary_message_payload['message'], $primary_message_payload['type']); ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($state === 'trial' && !empty($trial_notice_payload['message'])): ?>
            <div class="lsd-trial">
                <div class="lsd-form-row lsd-mb-0">
                    <div class="lsd-col-12">
                        <p class="lsd-m-0"><?php echo $trial_notice_payload['message']; ?></p>
                    </div>
                </div>
                <a class="lsd-primary-button" href="<?php echo esc_url($shop_url); ?>" target="_blank">
                    <?php esc_html_e('Get License', 'listdom'); ?>
                    <i class="webilia-icon wbli-key"></i>
                </a>
            </div>
        <?php endif; ?>

        <div class="lsd-activation">
            <form class="lsd-activation-form lsd-alert-no-my" data-key="<?php echo esc_attr($key); ?>">
                <div class="lsd-form-row lsd-my-0">
                    <div class="lsd-col-12">
                        <div class="lsd-w-full">
                            <?php echo LSD_Form::text([
                                'id' => $key . '_license_key',
                                'class' => 'lsd-admin-input',
                                'name' => 'license_key',
                                'value' => $licensing->getLicenseKey(),
                                'placeholder' => esc_attr__('Enter the license here', 'listdom'),
                            ]); ?>
                        </div>
                        <div>
                            <?php echo LSD_Form::hidden([
                                'name' => 'key',
                                'value' => $key,
                            ]); ?>
                            <?php echo LSD_Form::hidden([
                                'name' => 'basename',
                                'value' => $licensing->getBasename(),
                            ]); ?>
                            <?php LSD_Form::nonce($key . '_activation_form'); ?>
                            <?php echo LSD_Form::submit([
                                'label' => esc_html__('Activate', 'listdom'),
                                'id' => $key . '_activation_button',
                                'class' => 'lsd-secondary-button',
                            ]); ?>
                        </div>
                    </div>
                </div>
                <?php if ($state === 'trial' && !empty($trial_inline_payload['message'])): ?>
                    <?php echo $this->alert($trial_inline_payload['message'], $trial_inline_payload['type']); ?>
                <?php endif; ?>
                <div class="lsd-w-full lsd-activation-form-alert-wrapper">
                    <div class="lsd-activation-form-alert" id="<?php echo esc_attr($key); ?>_activation_alert"></div>
                </div>
            </form>
        </div>
        <?php if (isset($product['activation_notice']) && trim($product['activation_notice'])): ?>
            <div class="lsd-form-row lsd-mb-0">
                <div class="lsd-col-12 lsd-alert-no-mb">
                    <?php echo LSD_Kses::element($product['activation_notice']); ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($state === 'expiring' && !empty($expiring_notice_payload['message'])): ?>
            <?php echo $this->alert($expiring_notice_payload['message'], $expiring_notice_payload['type']); ?>
        <?php endif; ?>
        <?php if ($status['state'] === 'active'): ?>
            <div class="lsd-alert lsd-success lsd-my-0"><?php esc_html_e("The functionality, auto update, and customer service are activated.", 'listdom'); ?></div>
        <?php endif; ?>
        <div class="lsd-license-manage">
            <div class="lsd-license-key">
                <h3 class="lsd-my-0 lsd-fields-label">
                    <?php echo esc_html__('License Key:', 'listdom'); ?>
                </h3>
                <div class="lsd-flex lsd-gap-1 lsd-flex-align-items-center">
                    <code class="lsd-license-code lsd-license-code-<?php echo esc_attr($key); ?>"
                          data-lsd-copy="<?php echo esc_attr($licensing->getLicenseKey()); ?>">
                        <?php echo esc_html($licensing->getLicenseKey(true)); ?>
                    </code>
                    <a data-copied="<?php echo esc_html__('Copied!', 'listdom'); ?>"
                       class="lsd-copy lsd-w-auto"
                       data-target="lsd-license-code-<?php echo esc_attr($key); ?>">
                        <i class="webilia-icon wbli-copy"></i>
                    </a>
                </div>
            </div>
            <a class="lsd-neutral-button lsd-w-auto" href="<?php echo esc_url($this->getManageLicensesURL()); ?>"
               target="_blank">
                <?php esc_html_e('Manage Licenses', 'listdom'); ?>
                <i class="webilia-icon wbli-link-square"></i>
            </a>
        </div>
    <div class="lsd-w-full lsd-deactivation">
            <form class="lsd-deactivation-form" data-key="<?php echo esc_attr($key); ?>">
                <div class="lsd-form-row lsd-deactivation-wrapper lsd-m-0">
                    <div class="lsd-col-12">
                        <div class="lsd-w-full">
                            <?php echo LSD_Form::text([
                                'name' => 'confirmation',
                                'id' => $key . '_deactivation_confirm',
                                'class' => 'lsd-admin-input',
                                'placeholder' => esc_attr__('Type deactivate here to confirm ...', 'listdom'),
                            ]); ?>
                        </div>
                        <div>
                            <?php echo LSD_Form::hidden([
                                'name' => 'license_key',
                                'value' => $licensing->getLicenseKey(),
                            ]); ?>
                            <?php echo LSD_Form::hidden([
                                'name' => 'key',
                                'value' => $key,
                            ]); ?>
                            <?php echo LSD_Form::hidden([
                                'name' => 'basename',
                                'value' => $licensing->getBasename(),
                            ]); ?>
                            <?php LSD_Form::nonce($key . '_deactivation_form'); ?>
                            <?php echo LSD_Form::submit([
                                'label' => esc_html__('Deactivate', 'listdom'),
                                'id' => $key . '_deactivation_button',
                                'class' => 'lsd-secondary-button',
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="lsd-form-row lsd-mb-0">
                    <div class="lsd-col-12">
                        <div class="lsd-my-0" id="<?php echo esc_attr($key); ?>_deactivation_alert"></div>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>
