<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */

$user = LSD_User::get_user_info();
$profile_privacy_field = LSD_Privacy::consent_field([
    'id' => $this->form_id('lsd_profile_privacy_consent_' . absint($user['ID'])),
    'wrapper_class' => 'lsd-profile-privacy-consent-field',
    'context' => 'profile',
]);
$profile_nonce = wp_create_nonce('lsd_dashboard_profile');
$dashboard_id = $this->form_id('lsd_dashboard');
$profile_form_id = $this->form_id('lsd_dashboard_profile');
$profile_message_id = $this->form_id('lsd_dashboard_profile_message');
$dashboard_wrapper = $this->get_dashboard_wrapper([
    'classes' => ['lsd-dashboard', 'lsd-dashboard-profile'],
    'attributes' => [
        'data-profile-nonce' => $profile_nonce,
    ],
]);

// Add JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#'.esc_js($profile_form_id).'").listdomDashboardProfile(
        {
            ajax_url: "'.admin_url('admin-ajax.php', null).'",
            nonce: "'.$profile_nonce.'"
        });
});
</script>');
?>
<div class="<?php echo esc_attr($dashboard_wrapper['class']); ?>" id="<?php echo esc_attr($dashboard_id); ?>"<?php echo $dashboard_wrapper['attributes']; ?>>
    <div class="lsd-row lsd-dashboard-wrapper">
        <div class="lsd-dashboard-menus-wrapper">
            <?php echo LSD_Kses::full($this->menus()); ?>
        </div>
        <div class="lsd-dashboard-content-wrapper">
            <div class="lsd-dashboard-profile-wrapper">
                <form class="lsd-dashboard-profile-form" id="<?php echo esc_attr($profile_form_id); ?>" enctype="multipart/form-data">
                    <div class="lsd-row">
                        <div class="lsd-col-2"></div>
                        <div class="lsd-col-8">
                            <?php foreach (LSD_Portal_Fieldlayout::view('edit', wp_get_current_user()->roles, (int) $user['ID']) as $field): ?>
                                <?php if ($field['kind'] === 'layout'): ?>
                                    <?php include lsd_template('auth/user-field-layout.php'); ?>
                                <?php elseif ($field['type'] === 'image'): ?>
                                    <?php include lsd_template('dashboard/profile-image-field.php'); ?>
                                <?php else: ?>
                                    <?php
                                    $key = $field['key'];
                                    $id = $field['kind'] === 'core' ? $this->form_id('lsd_' . $key) : wp_unique_id('lsd-user-field-');
                                    $name = ($field['kind'] === 'core' ? 'lsd' : 'lsd_user_fields') . '[' . $key . ']';
                                    $value = $field['value'];
                                    $editable = $field['editable'];
                                    if ($field['kind'] === 'core')
                                    {
                                        $field['required'] = false;
                                        if ($field['social'] || $key === 'website') $field['type'] = 'text';
                                    }
                                    include lsd_template('auth/user-field.php');
                                    ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ($profile_privacy_field !== ''): ?>
                                <div class="form-group lsd-profile-contact-form-row lsd-profile-contact-form-row-consent lsd-my-4">
                                    <?php echo $profile_privacy_field; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="lsd-row">
                        <div class="lsd-col-2"></div>
                        <div class="lsd-dashboard-box lsd-dashboard-profile-submit lsd-col-8">
                            <input type="hidden" name="action" value="lsd_dashboard_profile_save">
                            <?php LSD_Form::nonce('lsd_dashboard_profile'); ?>

                            <div class="lsd-flex lsd-flex-col lsd-flex-align-items-start lsd-gap-2">
                                <button type="submit" class="lsd-general-button">
                                    <?php esc_html_e('Save', 'listdom'); ?>
                                </button>

                                <div id="<?php echo esc_attr($profile_message_id); ?>" class="lsd-alert-no-mt"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <?php if ((int) ($this->settings['dashboard_menu_builtin']['profile']['show_security'] ?? 1) === 1): ?>
                <?php echo (new LSD_Security_Account())->render(); ?>
            <?php endif; ?>
        </div>
    </div>

</div>
