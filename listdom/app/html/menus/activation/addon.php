<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Activation $this */
/** @var string $key */
/** @var array $product */

$licensing = $product['licensing'] ?? null;
if (!$licensing) return;

$basename = $licensing->getBasename();
$prefix = $licensing->getPrefix();
$license_key = $licensing->getLicenseKey();

$status = LSD_Licensing::getStatus($basename, $prefix);

$valid = $status['valid'];
$trial = $status['trial'];
$grace = $status['grace'];
$validation_status = $status['validation_status'];
$installed_date = $status['installed_date'];
$expired = $status['expired'];
$expiring = $status['expiring'];
$progress = $status['progress'];
$days_remaining = $status['days_remaining'];
$valid_license = $status['valid_license'];
$badge = $status['badge'];
$progress_class = $status['progress_class'];
?>
<div id="lsd-license-card-<?php echo esc_attr($key); ?>"
     class="lsd-license-card <?php echo $valid_license ? 'lsd-activation-valid' : ($grace ? 'lsd-activation-grace' : ($expiring ? 'lsd-activation-expiring' : ($expired ? 'lsd-activation-expired' : ''))); ?>">
    <div class="lsd-validation">
        <div class="lsd-addon-image">
            <?php if (isset($product['icon'])): ?>
                <img src="<?php echo esc_url($product['icon']); ?>" alt="<?php echo esc_attr($product['name']); ?>">
            <?php endif; ?>
        </div>
        <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html($product['name']); ?></h3>
        <div class="lsd-badge <?php echo esc_attr($badge['class']); ?>">
            <i class="listdom-icon <?php echo esc_attr($badge['icon']); ?>"></i>
            <?php echo $badge['text']; ?>
        </div>
    </div>
    <?php if (isset($validation_status['status']) && !$trial): ?>
        <div class="lsd-license-status">
            <div class="lsd-expiry-status">
                <div class="lsd-progress-bar">
                    <div class="lsd-progress-fill <?php echo esc_attr($progress_class); ?>"
                         style="width: <?php echo esc_attr($progress); ?>%;"></div>
                </div>
                <div class="lsd-expiry-details">
                    <?php if (!$grace): ?>
                        <span class="lsd-valid-date"><?php echo sprintf("Valid from %s", $installed_date); ?></span>
                    <?php endif; ?>
                    <?php if ($grace || isset($validation_status['expiry'])): ?>
                        <div class="lsd-expiry">
                            <span class="lsd-circle"></span>
                            <?php if ($grace): ?>
                                <span><?php esc_html_e('Error loading the details', 'listdom'); ?></span>
                            <?php elseif ($expired || $expiring): ?>
                                <span><?php echo sprintf(esc_html__("%d days remaining", 'listdom'), $days_remaining); ?></span>
                            <?php else: ?>
                                <span><?php esc_html_e($validation_status['expiry']); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($expired || $expiring || $grace): ?>
                <div>
                    <a class="lsd-primary-button" href="<?php echo esc_url_raw($this->getWebiliaShopUrl()); ?>"
                       target="_blank">
                        <?php esc_html_e('Renew License', 'listdom'); ?>
                        <i class="listdom-icon lsdi-key"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if (!$valid_license): ?>
        <?php if ($expired || $valid === 0 || $grace || $expiring): ?>
            <div class="lsd-form-row lsd-activation-guide">
                <div class="lsd-col-12">
                    <?php
                    $message = '';
                    $type = 'warning';

                    if ($expired || (trim($license_key) && $valid === 0))
                    {
                        $message = esc_html__('The license Key / Purchase Code is expired. It is required for functionality, auto update, and customer service!', 'listdom');
                        $type = 'error';
                    }
                    else if ($expiring && $valid === 1)
                    {
                        $message = sprintf(
                            esc_html__('Your license will expire in %s days. Please renew it to avoid any interruption in using Elementor Addon.', 'listdom'),
                            $days_remaining
                        );
                    }
                    else if ($valid === 0)
                    {
                        $message = sprintf(
                            esc_html__("To use %s addon you need to activate it first. If you don't have a valid license key or yours has expired, you can obtain one from the %s website.", 'listdom'),
                            '<strong>' . $product['name'] . '</strong>',
                            '<a href="' . $this->getWebiliaShopURL() . '" target="_blank"><strong>Webilia</strong></a>'
                        );
                    }
                    else if ($grace)
                    {
                        $message = sprintf(
                            esc_html__('There seems to be an issue verifying your license, which may be due to a connection problem between our server and yours, or because your license has expired. You are now in a 7-day grace period. If your license is expired, please renew or activate your license within the next %s days to avoid any disruption in using %s. If you believe this is an error, kindly check your server connection or contact Webilia support for assistance.', 'listdom'),
                            '<strong style="color: red;">' . esc_html(LSD_Licensing::remainingGracePeriod($prefix)) . '</strong>',
                            '<strong>' . esc_html($product['name']) . '</strong>'
                        );
                    }

                    if ($message) echo $this->alert($message, $type);
                    ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($trial && !$expired && !$grace): ?>
            <div class="lsd-trial">
                <div class="lsd-form-row lsd-mb-0">
                    <div class="lsd-col-12">
                        <p class="lsd-m-0"><?php echo sprintf(
                            esc_html__('Please activate your license promptly. You have less than %1$s days remaining to activate %2$s; after that, %2$s will no longer be operational.', 'listdom'),
                            '<strong style="color: red;">' . esc_html(LSD_Licensing::remainingTrialPeriod($prefix)) . '</strong>',
                            '<strong>' . esc_html($product['name']) . '</strong>'
                        ); ?></p>
                    </div>
                </div>
                <a class="lsd-primary-button" href="<?php echo esc_url_raw($this->getWebiliaShopUrl()); ?>" target="_blank">
                    <?php esc_html_e('Get License', 'listdom'); ?>
                    <i class="listdom-icon lsdi-key"></i>
                </a>
            </div>
        <?php endif; ?>

        <div class="lsd-activation">
            <form class="lsd-activation-form" data-key="<?php echo esc_attr($key); ?>">
                <div class="lsd-form-row lsd-my-0">
                    <div class="lsd-col-12">
                        <div class="lsd-w-full">
                            <?php echo LSD_Form::text([
                                'id' => $key . '_license_key',
                                'class' => 'lsd-admin-input',
                                'name' => 'license_key',
                                'value' => $license_key,
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
                <?php if ($trial): ?>
                    <div class="lsd-alert lsd-info lsd-my-0"><?php esc_html_e("License Key / Purchase Code is required for functionality, auto update, and customer service!", 'listdom'); ?></div>
                <?php endif; ?>
                <div class="lsd-w-full">
                    <div id="<?php echo esc_attr($key); ?>_activation_alert"></div>
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
        <?php if ($expiring): ?>
            <div class="lsd-alert lsd-warning lsd-my-0"><?php echo sprintf(esc_html__('Your license will expire in %s days. Please renew it to avoid any interruption in using %s.', 'listdom'), '<strong style="color: red;">' . esc_html($days_remaining) . '</strong>', '<strong>' . esc_html($product['name']) . '</strong>'); ?></div>
        <?php endif; ?>
        <div class="lsd-alert lsd-success lsd-my-0"><?php esc_html_e("The functionality, auto update, and customer service are activated.", 'listdom'); ?></div>
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
                        <i class="listdom-icon lsdi-copy"></i>
                    </a>
                </div>
            </div>
            <a class="lsd-neutral-button lsd-w-auto" href="<?php echo esc_url_raw($this->getManageLicensesURL()); ?>"
               target="_blank">
                <?php esc_html_e('Manage Licenses', 'listdom'); ?>
                <i class="listdom-icon lsdi-link-square"></i>
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
