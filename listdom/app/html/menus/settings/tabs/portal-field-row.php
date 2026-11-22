<?php
defined('ABSPATH') || die();

$kind = $field['kind'];
$locked = !empty($field['locked']);
$enabled = !empty($field['enabled']);
$label = $field['label'];
if ($label === '')
{
    if ($kind === 'custom') $label = __('New custom field', 'listdom');
    else $label = $field['type'] === 'separator' ? __('Separator', 'listdom') : __('New layout row', 'listdom');
}
$groups = [];
if ($kind !== 'layout')
{
    if ($kind === 'custom' || !empty($field['collectable']))
    {
        $groups[] = [
            'title' => $kind === 'custom' ? __('Registration and validation', 'listdom') : __('Registration', 'listdom'),
            'flags' => [
                'register' => __('Collect during registration', 'listdom'),
                'required' => $kind === 'custom' ? __('Required when collecting a value', 'listdom') : __('Required during registration', 'listdom'),
            ],
        ];
    }
    $groups[] = [
        'title' => __('Profile', 'listdom'),
        'flags' => $locked ? [] : [
            'profile_editor' => __('Profile editor', 'listdom'),
            'public' => __('Show on public profiles', 'listdom'),
        ],
    ];
}
?>
<li class="lsd-dashboard-menu-item<?php echo $enabled ? '' : ' lsd-dashboard-menu-item-disabled'; ?>" data-row="<?php echo esc_attr($row_id); ?>" data-kind="<?php echo esc_attr($kind); ?>">
    <p class="lsd-flex lsd-flex-content-between">
        <span data-label="<?php echo esc_attr($label); ?>"><?php echo esc_html($label); ?></span>
        <span class="lsd-menu-actions">
            <?php if ($kind !== 'core'): ?>
                <button type="button" class="lsd-user-field-action" data-remove aria-label="<?php esc_attr_e('Remove field', 'listdom'); ?>">
                    <i class="fas fa-trash" aria-hidden="true"></i>
                </button>
            <?php endif; ?>
            <?php if (!$locked): ?>
                <button type="button" class="lsd-dashboard-menu-toggle" aria-label="<?php esc_attr_e('Enable field', 'listdom'); ?>" aria-pressed="<?php echo $enabled ? 'true' : 'false'; ?>">
                    <i class="fas <?php echo $enabled ? 'fa-check-circle' : 'fa-minus-circle'; ?>" aria-hidden="true"></i>
                </button>
            <?php endif; ?>
            <button type="button" class="lsd-user-field-action" data-expand aria-label="<?php esc_attr_e('Field settings', 'listdom'); ?>" aria-expanded="false">
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
            </button>
        </span>
    </p>
    <?php if (!$locked): ?>
        <input type="hidden" class="lsd-dashboard-menu-status" data-field="enabled" value="<?php echo $enabled ? '1' : '0'; ?>">
    <?php endif; ?>
    <div class="lsd-dashboard-menu-content lsd-util-hide">
        <?php if ($kind !== 'core'): ?>
            <div class="lsd-user-field-section">
                <?php if ($kind === 'custom'): ?>
                    <h4 class="lsd-user-field-section-title"><?php esc_html_e('Field details', 'listdom'); ?></h4>
                <?php endif; ?>
                <label class="lsd-fields-label lsd-user-field-control">
                    <span class="lsd-fields-label-tiny"><?php esc_html_e('Label', 'listdom'); ?></span>
                    <input type="text" class="lsd-admin-input" data-field="label" value="<?php echo esc_attr($field['label']); ?>">
                </label>
                <label class="lsd-fields-label lsd-user-field-control">
                    <span class="lsd-fields-label-tiny"><?php esc_html_e('Type', 'listdom'); ?></span>
                    <select class="lsd-admin-input" data-field="type">
                        <?php foreach ($kind === 'layout' ? ['heading' => __('Heading', 'listdom'), 'separator' => __('Separator', 'listdom')] : LSD_Portal_Fields::types() as $type => $name): ?>
                            <option value="<?php echo esc_attr($type); ?>" <?php selected($field['type'], $type); ?>><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <?php if ($kind === 'custom'): ?>
                    <label data-choices class="lsd-fields-label lsd-user-field-control<?php echo in_array($field['type'], ['select', 'multi', 'radio'], true) ? '' : ' lsd-util-hide'; ?>">
                        <span class="lsd-fields-label-tiny"><?php esc_html_e('Choices, separated by commas', 'listdom'); ?></span>
                        <input type="text" class="lsd-admin-input" data-field="choices" value="<?php echo esc_attr(implode(', ', $field['choices'] ?? [])); ?>">
                    </label>
                    <?php if ($row_id !== ''): ?>
                        <p class="lsd-admin-description-tiny lsd-user-field-id"><?php esc_html_e('Field ID', 'listdom'); ?> <code><?php echo esc_html($field['key']); ?></code></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php foreach ($groups as $group): ?>
            <div class="lsd-user-field-section">
                <h4 class="lsd-user-field-section-title"><?php echo esc_html($group['title']); ?></h4>
                <?php foreach ($group['flags'] as $flag => $name): ?>
                    <?php if ($flag === 'profile_editor'): ?>
                        <?php $profile_editor = LSD_Portal_Fields::profile_editor($field); ?>
                        <label class="lsd-fields-label lsd-user-field-control">
                            <span class="lsd-fields-label-tiny"><?php echo esc_html($name); ?></span>
                            <select class="lsd-admin-input" data-field="profile_editor">
                                <option value="hidden" <?php selected($profile_editor, 'hidden'); ?>><?php esc_html_e('Hidden', 'listdom'); ?></option>
                                <option value="readonly" <?php selected($profile_editor, 'readonly'); ?>><?php esc_html_e('Read-only', 'listdom'); ?></option>
                                <option value="editable" <?php selected($profile_editor, 'editable'); ?> <?php disabled(($field['key'] ?? '') === 'email'); ?>><?php esc_html_e('Editable', 'listdom'); ?></option>
                            </select>
                        </label>
                        <?php if (($field['key'] ?? '') === 'email'): ?>
                            <p class="lsd-admin-description-tiny"><?php esc_html_e('Email changes are managed in Account Security.', 'listdom'); ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="lsd-user-field-option">
                            <label class="lsd-fields-label lsd-user-field-checkbox">
                                <input type="checkbox" data-field="<?php echo esc_attr($flag); ?>" <?php checked(!empty($field[$flag])); ?>>
                                <span><?php echo esc_html($name); ?></span>
                            </label>
                            <?php if ($kind === 'custom' && $flag === 'required'): ?>
                                <p class="lsd-admin-description-tiny"><?php esc_html_e('Applies during registration and when editing this field in the profile.', 'listdom'); ?></p>
                            <?php elseif ($kind === 'custom' && $flag === 'public'): ?>
                                <p class="lsd-admin-description-tiny"><?php esc_html_e('Public visibility is independent of Profile editor and Allowed roles.', 'listdom'); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if ($locked && !$group['flags']): ?>
                    <p class="lsd-admin-description-tiny"><?php esc_html_e('This system field always appears and can be edited in the signed-in profile.', 'listdom'); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($kind === 'custom'): ?>
            <div class="lsd-user-field-section">
                <fieldset class="lsd-user-field-roles">
                    <legend class="lsd-user-field-section-title"><?php esc_html_e('Allowed roles', 'listdom'); ?></legend>
                    <div class="lsd-user-field-role-list">
                        <?php foreach (LSD_Dashboard_Access::role_options() as $role => $role_name): ?>
                            <label class="lsd-fields-label lsd-user-field-checkbox">
                                <input type="checkbox" data-field="roles" value="<?php echo esc_attr($role); ?>" <?php checked(in_array($role, $field['roles'] ?? [], true)); ?>>
                                <span><?php echo esc_html(translate_user_role($role_name)); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="lsd-admin-description-tiny"><?php esc_html_e('Select who can fill in this field. Leave all unchecked to allow everyone.', 'listdom'); ?></p>
                </fieldset>
            </div>
        <?php endif; ?>
    </div>
</li>
