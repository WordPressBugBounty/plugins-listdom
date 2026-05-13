<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_User $user */
$profile_fields = LSD_User::profile_edit_fields();
$show_additional_information = !empty($profile_fields['job_title']) || LSD_User::has_enabled_profile_social_fields() || !empty($profile_fields['phone']) || !empty($profile_fields['mobile']) || !empty($profile_fields['website']) || !empty($profile_fields['fax']);
?>
<?php if ($show_additional_information): ?>
<div class="lsd-metabox">

    <h3><?php esc_html_e('Additional Information', 'listdom'); ?></h3>
    <table class="form-table">
        <?php if (!empty($profile_fields['job_title'])): ?>
        <tr>
            <th><label for="lsd_job_title"><?php esc_html_e('Job Title', 'listdom'); ?></label></th>
            <td>
                <input type="text" name="lsd_job_title" id="lsd_job_title" value="<?php echo esc_attr(get_the_author_meta('lsd_job_title', $user->ID)); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('It shows below your name on your profile information.', 'listdom'); ?></p>
            </td>
        </tr>
        <?php endif; ?>

        <?php if (LSD_User::has_enabled_profile_social_fields()) do_action('lsd_social_networks_profile_form', $user); ?>

        <?php if (!empty($profile_fields['phone'])): ?>
        <tr>
            <th><label for="lsd_phone"><?php esc_html_e('Phone', 'listdom'); ?></label></th>
            <td>
                <input type="tel" name="lsd_phone" id="lsd_phone" value="<?php echo esc_attr(get_the_author_meta('lsd_phone', $user->ID)); ?>" class="regular-text ltr">
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($profile_fields['mobile'])): ?>
        <tr>
            <th><label for="lsd_mobile"><?php esc_html_e('Mobile', 'listdom'); ?></label></th>
            <td>
                <input type="tel" name="lsd_mobile" id="lsd_mobile" value="<?php echo esc_attr(get_the_author_meta('lsd_mobile', $user->ID)); ?>" class="regular-text ltr">
                <p class="description"><?php esc_html_e('Please insert your mobile number.', 'listdom'); ?></p>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($profile_fields['website'])): ?>
        <tr>
            <th><label for="lsd_website"><?php esc_html_e('Website', 'listdom'); ?></label></th>
            <td>
                <input type="url" name="lsd_website" id="lsd_website" value="<?php echo esc_url(get_the_author_meta('lsd_website', $user->ID)); ?>" class="regular-text ltr" placeholder="<?php esc_attr_e('https://yourwebsite.com', 'listdom'); ?>">
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($profile_fields['fax'])): ?>
        <tr>
            <th><label for="lsd_fax"><?php esc_html_e('Fax', 'listdom'); ?></label></th>
            <td>
                <input type="tel" name="lsd_fax" id="lsd_fax" value="<?php echo esc_attr(get_the_author_meta('lsd_fax', $user->ID)); ?>" class="regular-text ltr">
            </td>
        </tr>
        <?php endif; ?>
    </table>

</div>
<?php endif; ?>
