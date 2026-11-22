<?php
defined('ABSPATH') || die();
$definitions = LSD_Security_Sections::definitions();
$totp_limits = [
    'challenge_limit' => [__('Code Attempt Limit', 'listdom'), __('Maximum authenticator or recovery-code attempts per account within five minutes (1–5). General rate limiting also applies this limit per challenge.', 'listdom')],
    'ip_limit' => [__('Code IP Limit', 'listdom'), __('Maximum authenticator or recovery-code attempts from one IP address within 15 minutes.', 'listdom')],
];
?>
<div class="lsd-settings-fields-wrapper">
    <h3 class="lsd-admin-title"><?php esc_html_e('Account Security Sections', 'listdom'); ?></h3>
    <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Drag sections to change their order. Open a section to edit its heading, description, and settings. Password Change is always enabled.', 'listdom'); ?></p>
    <ul class="lsd-settings-dashboard-menus lsd-security-sections lsd-m-0">
        <?php foreach ($security['section_order'] as $id): ?>
            <?php
            $definition = $definitions[$id];
            $section = $security['sections'][$id];
            $heading = LSD_Security_Sections::heading($id, $section);
            $enabled = !empty($section['enabled']);
            $prefix = 'lsd_security_section_' . $id;
            $name = 'lsd[security][sections][' . $id . ']';
            ?>
            <li class="lsd-dashboard-menu-item<?php echo $enabled ? '' : ' lsd-dashboard-menu-item-disabled'; ?>" data-section="<?php echo esc_attr($id); ?>" data-default-heading="<?php echo esc_attr($definition['heading']); ?>" data-default-description="<?php echo esc_attr($definition['description']); ?>">
                <p class="lsd-flex lsd-flex-content-between lsd-security-section-handle">
                    <span>
                        <i class="lsd-icon <?php echo esc_attr($definition['icon']); ?>" aria-hidden="true"></i>
                        <span class="lsd-security-section-title"><?php echo esc_html($heading); ?></span>
                        <span class="lsd-dashboard-menu-default-label<?php echo $heading === $definition['heading'] ? ' lsd-util-hide' : ''; ?>">(<?php echo esc_html($definition['heading']); ?>)</span>
                    </span>
                    <span class="lsd-menu-actions">
                        <?php if ($id === 'password'): ?>
                            <span class="lsd-admin-description-tiny"><?php esc_html_e('Always enabled', 'listdom'); ?></span>
                        <?php else: ?>
                            <button type="button" class="lsd-dashboard-menu-toggle lsd-security-section-toggle" aria-pressed="<?php echo $enabled ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr(sprintf(__('Enable or disable %s', 'listdom'), $definition['heading'])); ?>">
                                <i class="fas <?php echo $enabled ? 'fa-check-circle' : 'fa-minus-circle'; ?>" aria-hidden="true"></i>
                            </button>
                        <?php endif; ?>
                        <button type="button" class="lsd-dashboard-menu-toggle lsd-security-section-expand" aria-expanded="false" aria-controls="<?php echo esc_attr($prefix . '_config'); ?>" aria-label="<?php echo esc_attr(sprintf(__('Configure %s', 'listdom'), $definition['heading'])); ?>">
                            <i class="fas fa-chevron-down" aria-hidden="true"></i>
                        </button>
                    </span>
                </p>
                <input type="hidden" name="lsd[security][section_order][]" value="<?php echo esc_attr($id); ?>">
                <input type="hidden" class="lsd-dashboard-menu-status" name="<?php echo esc_attr($name . '[enabled]'); ?>" value="<?php echo $enabled ? '1' : '0'; ?>">
                <div id="<?php echo esc_attr($prefix . '_config'); ?>" class="lsd-dashboard-menu-content lsd-util-hide">
                    <div class="lsd-field-group">
                        <?php echo LSD_Form::label(['for' => $prefix . '_heading', 'title' => __('Heading', 'listdom'), 'class' => 'lsd-fields-label-tiny']); ?>
                        <?php echo LSD_Form::text(['id' => $prefix . '_heading', 'class' => 'lsd-admin-input lsd-security-section-heading', 'name' => $name . '[heading]', 'value' => $section['heading'], 'placeholder' => $definition['heading'], 'attributes' => ['maxlength' => 512]]); ?>
                    </div>
                    <div class="lsd-field-group">
                        <?php echo LSD_Form::label(['for' => $prefix . '_description', 'title' => __('Description', 'listdom'), 'class' => 'lsd-fields-label-tiny']); ?>
                        <?php echo LSD_Form::textarea(['id' => $prefix . '_description', 'class' => 'lsd-admin-input lsd-security-description-editor', 'name' => $name . '[description]', 'value' => $section['description'], 'rows' => 5, 'attributes' => ['maxlength' => 20000]]); ?>
                        <p class="lsd-admin-description-tiny"><?php esc_html_e('Supports paragraphs, emphasis, lists, and links. Leave empty to hide this description. Required security notices are shown separately.', 'listdom'); ?></p>
                    </div>
                    <div><button type="button" class="lsd-text-button lsd-security-section-reset"><?php esc_html_e('Restore Default Text', 'listdom'); ?></button></div>
                    <?php if ($id === 'totp' || $id === 'privacy'): ?>
                        <div class="lsd-security-feature-fields<?php echo $enabled ? '' : ' lsd-util-hide'; ?>">
                            <div class="lsd-settings-fields-sub-wrapper">
                                <?php if ($id === 'totp'): ?>
                                    <div class="lsd-alert lsd-info lsd-m-0"><?php esc_html_e('Enrolled accounts require a second factor for browser and Listdom API sign-in. Access to the current email address can reset two-factor authentication. Shared multisite accounts remain protected if another active member site enables it.', 'listdom'); ?></div>
                                    <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('An account can attempt verification at most five times in five minutes, even when Rate Limiting is off. A stricter saved Code Attempt Limit still applies.', 'listdom'); ?></p>
                                    <?php if (!LSD_Security_Totp::available()): ?><div class="lsd-alert lsd-error lsd-m-0"><?php esc_html_e('Authenticator enrollment and role enforcement require OpenSSL with AES-256-GCM support.', 'listdom'); ?></div><?php endif; ?>
                                    <div class="lsd-form-row">
                                        <div class="lsd-col-3"><span class="lsd-fields-label"><?php esc_html_e('Required Roles', 'listdom'); ?></span></div>
                                        <div class="lsd-col-5">
                                            <input type="hidden" name="lsd[security][sections][totp][roles][]" value="">
                                            <?php echo LSD_Form::checkboxes(['class' => 'lsd-my-0 lsd-ai-checkboxes', 'name' => $name . '[roles][]', 'value' => $section['roles'], 'options' => LSD_Roles::all()]); ?>
                                            <p class="lsd-admin-description-tiny"><?php esc_html_e('Selected user roles must enroll after a seven-day grace period. Leave all roles unchecked for optional enrollment.', 'listdom'); ?></p>
                                        </div>
                                    </div>
                                    <div id="lsd_security_totp_rate_fields" class="<?php echo empty($security['rate_enabled']) ? 'lsd-util-hide' : ''; ?>">
                                        <div class="lsd-settings-fields-sub-wrapper">
                                            <?php foreach ($totp_limits as $key => [$label, $description]): ?>
                                                <?php
                                                $maximum = $key === 'challenge_limit' ? 5 : 3600;
                                                $row($prefix . '_' . $key, $label, LSD_Form::number(['class' => 'lsd-admin-input', 'id' => $prefix . '_' . $key, 'name' => $name . '[' . $key . ']', 'value' => $section[$key], 'min' => 1, 'max' => $maximum]), $description);
                                                ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ([
                                        'export' => [__('Export Requests', 'listdom'), __('Allow requests for an export of personal data.', 'listdom')],
                                        'erasure' => [__('Erasure Requests', 'listdom'), __('Allow personal data erasure and administrator-approved account deletion requests.', 'listdom')],
                                    ] as $key => [$label, $description]): ?>
                                        <?php $row($prefix . '_' . $key, $label, LSD_Form::switcher(['id' => $prefix . '_' . $key, 'name' => $name . '[' . $key . ']', 'value' => $section[$key]]), $description); ?>
                                    <?php endforeach; ?>
                                    <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Requests require email confirmation and administrator review. Account deletion, listing ownership, subscriptions, payments, and retained records remain administrator responsibilities.', 'listdom'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
