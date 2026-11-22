<?php
defined('ABSPATH') || die();
?>
<div class="lsd-field-group">
    <label class="lsd-fields-label lsd-dashboard-menu-control">
        <span class="lsd-fields-label-tiny"><?php esc_html_e('Membership access', 'listdom'); ?></span>
        <select class="lsd-admin-input" name="<?php echo esc_attr($membership_name); ?>" data-field="entitlement">
            <?php foreach (LSD_Dashboard_Access::entitlement_options([$membership_entitlement]) as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($membership_entitlement, $key); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <p class="lsd-admin-description-tiny"><?php esc_html_e('Choose an entitlement defined in a Membership package, or select no membership restriction. Restricted content stays locked if the Membership add-on is inactive.', 'listdom'); ?></p>
    <?php if (count(LSD_Dashboard_Access::entitlement_options()) === 1): ?>
        <p class="lsd-admin-description-tiny">
            <?php esc_html_e('No package entitlements are available yet. Add values under Portal Entitlements in a Membership package. Save your work and reload this page after adding them.', 'listdom'); ?>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=listdom-package')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Membership packages', 'listdom'); ?></a>
        </p>
    <?php endif; ?>
</div>
