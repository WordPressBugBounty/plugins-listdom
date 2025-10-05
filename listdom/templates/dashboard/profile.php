<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */

$user = LSD_User::get_user_info();

$profile_privacy_field = LSD_Privacy::consent_field([
    'id' => 'lsd_profile_privacy_consent_' . absint($user['ID']),
    'wrapper_class' => 'lsd-profile-privacy-consent-field',
    'context' => 'dashboard',
]);

// Add JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_dashboard_profile").listdomDashboardProfile(
    {
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        nonce: "'.wp_create_nonce('lsd_dashboard_profile').'"
    });
});
</script>');
?>
<div class="lsd-dashboard lsd-dashboard-profile" id="lsd_dashboard">
    <div class="lsd-row">
        <div class="lsd-col-2 lsd-dashboard-menus-wrapper">
            <?php echo LSD_Kses::element($this->menus()); ?>
        </div>
        <div class="lsd-col-10">
            <div class="lsd-dashboard-profile-wrapper">
                <form id="lsd_dashboard_profile" enctype="multipart/form-data">
                    <div class="lsd-row">
                        <div class="lsd-col-2">
                            <h4><?php echo esc_html__('About', 'listdom'); ?></h4>
                        </div>
                        <div class="lsd-col-6">
                            <div class="lsd-form-group lsd-no-border lsd-mt-0 lsd-profile-module-about">
                                <div class="lsd-form-row lsd-first-name-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_first_name"><?php esc_html_e('First Name', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_first_name" name="lsd[first_name]" value="<?php echo esc_attr($user['first_name']); ?>" placeholder="<?php esc_attr_e('John', 'listdom'); ?>">
                                    </div>
                                </div>

                                <div class="lsd-form-row lsd-last-name-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_last_name"><?php esc_html_e('Last Name', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_last_name" name="lsd[last_name]" value="<?php echo esc_attr($user['last_name']); ?>" placeholder="<?php esc_attr_e('Anderson', 'listdom'); ?>">
                                    </div>
                                </div>
                                <div class="lsd-form-row lsd-job-title-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_job_title"><?php esc_html_e('Job Title', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_job_title" name="lsd[job_title]" value="<?php echo esc_attr($user['job_title']); ?>" placeholder="<?php esc_attr_e('Agent at Company', 'listdom'); ?>">
                                    </div>
                                </div>

                                <div class="lsd-form-row lsd-bio-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_bio"><?php esc_html_e('Bio', 'listdom'); ?><?php $this->required_html('bio'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <textarea id="lsd_bio" name="lsd[bio]" placeholder="<?php esc_attr_e('I am ...', 'listdom'); ?>"><?php echo esc_textarea($user['bio']); ?></textarea>
                                    </div>
                                </div>
                                <div class="lsd-image-row lsd-flex lsd-flex-col lsd-gap-2 lsd-profile-image-container">
                                    <div class="lsd-col-12" id="lsd_profile_image_message"></div>
                                    <div class="lsd-flex lsd-flex-row lsd-gap-2 lsd-w-full">
                                        <div class="lsd-col-4 lsd-text-left">
                                            <label class="lsd-fields-label" for="lsd_profile_image"><?php esc_html_e('Profile Image', 'listdom'); ?><?php $this->required_html('profile_image'); ?></label>
                                        </div>
                                        <div class="lsd-col-8 lsd-profile-buttons">
                                            <?php
                                            $attachment_id = $user['profile_image'];

                                            $profile_image = wp_get_attachment_image_src($attachment_id, 'medium');
                                            if (isset($profile_image[0])) $profile_image = $profile_image[0];
                                            ?>
                                            <input type="hidden" id="lsd_profile_image" name="lsd[profile_image]" value="<?php echo esc_attr($attachment_id); ?>">
                                            <input type="file" id="lsd_profile_image_file" class="lsd-util-hide">
                                            <label class="lsd-fields-label" for="lsd_profile_image_file" class="lsd-choose-file lsd-light-button <?php echo !empty($profile_image) ? 'lsd-util-hide': ''; ?>"><?php echo esc_html__('Choose File', 'listdom'); ?></label>
                                            <div class="lsd-dashboard-feature-image-remove-wrapper lsd-flex lsd-flex-content-end">
                                                <span id="lsd_profile_image_remove_button" class="lsd-remove-image-button <?php echo trim($profile_image) ? '' : 'lsd-util-hide'; ?>">
                                                    <i class="fa fa-times"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="lsd-col-12">
                                        <span id="lsd_dashboard_profile_image_preview"><?php echo trim($profile_image) ? '<img src="'.esc_url($profile_image).'">' : ''; ?></span>
                                    </div>
                                </div>

                                <div class="lsd-image-row lsd-flex lsd-flex-col lsd-gap-2 lsd-profile-image-container">
                                <div class="lsd-col-12" id="lsd_hero_image_message"></div>
                                <div class="lsd-flex lsd-flex-row lsd-gap-2 lsd-w-full">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_cover_image"><?php esc_html_e('Cover Image', 'listdom'); ?><?php $this->required_html('profile_hero'); ?></label>
                                        <p class="description"><?php echo esc_html__('Suggested: 1920*490px', 'listdom'); ?></p>
                                    </div>
                                    <div class="lsd-col-8 lsd-profile-buttons">
                                        <?php
                                        $attachment_id = $user['hero_image'];

                                        $hero_image = wp_get_attachment_image_src($attachment_id, 'medium');
                                        if (isset($hero_image[0])) $hero_image = $hero_image[0];
                                        ?>
                                        <input type="hidden" id="lsd_hero_image" name="lsd[hero_image]" value="<?php echo esc_attr($attachment_id); ?>">
                                        <input type="file" id="lsd_hero_image_file" class="lsd-util-hide">
                                        <label class="lsd-fields-label" for="lsd_hero_image_file" class="lsd-choose-file lsd-light-button <?php echo !empty($hero_image) ? 'lsd-util-hide': ''; ?>"><?php echo esc_html__('Choose File', 'listdom'); ?></label>
                                        <div class="lsd-dashboard-feature-image-remove-wrapper lsd-flex lsd-flex-content-end">
                                            <span id="lsd_hero_image_remove_button" class="lsd-remove-image-button <?php echo (trim($attachment_id) ? '' : 'lsd-util-hide'); ?>">
                                                <i class="fa fa-times"></i>
                                            </span>
                                        </div>
                                    </div>

                                </div>
                                <div class="lsd-col-12">
                                    <span id="lsd_dashboard_hero_image_preview"><?php echo trim($hero_image) ? '<img src="'.esc_url($hero_image).'">' : ''; ?></span>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="lsd-row">
                        <div class="lsd-col-2 lsd-mt-4">
                            <h4><?php echo esc_html__('Contact Info', 'listdom'); ?></h4>
                        </div>
                        <div class="lsd-col-6 lsd-mt-4">
                            <div class="lsd-form-group lsd-no-border lsd-mt-0 lsd-listing-module-excerpt">
                                <div class="lsd-form-row lsd-email-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_email"><?php esc_html_e('Email', 'listdom'); ?><?php $this->required_html('email'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_email" name="lsd[email]" required value="<?php echo esc_attr($user['email']); ?>" placeholder="<?php esc_attr_e('John@email.com', 'listdom'); ?>">
                                    </div>
                                </div>

                                <div class="lsd-form-row lsd-phone-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_phone"><?php esc_html_e('Phone', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_phone" name="lsd[phone]" value="<?php echo esc_attr($user['phone']); ?>" placeholder="<?php esc_attr_e('123-456-789', 'listdom'); ?>">
                                    </div>
                                </div>
                                <div class="lsd-form-row lsd-mobile-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_mobile"><?php esc_html_e('Mobile', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_mobile" name="lsd[mobile]" value="<?php echo esc_attr($user['mobile']); ?>" placeholder="<?php esc_attr_e('+123456789', 'listdom'); ?>">
                                    </div>
                                </div>

                                <div class="lsd-form-row lsd-website-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_website"><?php esc_html_e('Website', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_website" name="lsd[website]" value="<?php echo esc_attr($user['website']); ?>" placeholder="<?php esc_attr_e('john.com', 'listdom'); ?>">
                                    </div>
                                </div>

                                <div class="lsd-form-row lsd-fax-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_fax"><?php esc_html_e('Fax', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_fax" name="lsd[fax]" value="<?php echo esc_attr($user['fax']); ?>" placeholder="<?php esc_attr_e('123-456-789', 'listdom'); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lsd-row">
                        <div class="lsd-col-2 lsd-mt-4">
                            <h4><?php echo esc_html__('Social Media', 'listdom'); ?></h4>
                        </div>
                        <div class="lsd-col-6 lsd-mt-4">
                            <div class="lsd-form-group lsd-no-border lsd-mt-0 lsd-listing-module-excerpt">
                                <div class="lsd-form-row lsd-facebook-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_facebook"><?php esc_html_e('Facebook', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_facebook" name="lsd[facebook]" value="<?php echo esc_attr($user['facebook']); ?>">
                                    </div>
                                </div>
                                <div class="lsd-form-row lsd-twitter-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_twitter"><?php esc_html_e("X", 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_twitter" name="lsd[twitter]" value="<?php echo esc_attr($user['twitter']); ?>">
                                    </div>
                                </div>
                                <div class="lsd-form-row lsd-pinterest-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_pinterest"><?php esc_html_e('Pinterest', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_pinterest" name="lsd[pinterest]" value="<?php echo esc_attr($user['pinterest']); ?>">
                                    </div>
                                </div>
                                <div class="lsd-form-row lsd-linkedin-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_linkedin"><?php esc_html_e('Linkedin', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_linkedin" name="lsd[linkedin]" value="<?php echo esc_attr($user['linkedin']); ?>">
                                    </div>
                                </div>

                                <div class="lsd-form-row lsd-instagram-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_instagram"><?php esc_html_e('Instagram', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_instagram" name="lsd[instagram]" value="<?php echo esc_attr($user['instagram']); ?>">
                                    </div>
                                </div>
                                <div class="lsd-form-row lsd-whatsapp-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_whatsapp"><?php esc_html_e('WhatsApp', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_whatsapp" name="lsd[whatsapp]" value="<?php echo esc_attr($user['whatsapp']); ?>">
                                    </div>
                                </div>
                                <div class="lsd-form-row lsd-youtube-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_youtube"><?php esc_html_e('Youtube', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_youtube" name="lsd[youtube]" value="<?php echo esc_attr($user['youtube']); ?>">
                                    </div>
                                </div>
                                <div class="lsd-form-row lsd-Tiktok-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_Tiktok"><?php esc_html_e('Tiktok', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_Tiktok" name="lsd[Tiktok]" value="<?php echo esc_attr($user['tiktok']); ?>">
                                    </div>
                                </div>
                                <div class="lsd-form-row lsd-telegram-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_telegram"><?php esc_html_e('Telegram', 'listdom'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="text" id="lsd_telegram" name="lsd[telegram]" value="<?php echo esc_attr($user['telegram']); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lsd-row">
                        <div class="lsd-col-2 lsd-mt-4">
                            <h4><?php echo esc_html__('Password', 'listdom'); ?></h4>
                        </div>
                        <div class="lsd-col-6 lsd-mt-4">
                            <div class="lsd-form-group lsd-no-border lsd-mt-0 lsd-listing-module-excerpt">
                                <div class="lsd-password-message"></div>
                                <div class="lsd-form-row lsd-password-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_password"><?php esc_html_e('New Password', 'listdom'); ?><?php $this->required_html('password'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="password" id="lsd_password" name="lsd[password]">
                                    </div>
                                </div>
                                <div class="lsd-form-row lsd-retype_password-row">
                                    <div class="lsd-col-4 lsd-text-left">
                                        <label class="lsd-fields-label" for="lsd_confirm_password"><?php esc_html_e('Repeat New Password', 'listdom'); ?><?php $this->required_html('retype_password'); ?></label>
                                    </div>
                                    <div class="lsd-col-8">
                                        <input type="password" id="lsd_confirm_password" name="lsd[confirm_password]">
                                    </div>
                                </div>
                            </div>
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

                                <div id="lsd_dashboard_profile_message" class="lsd-alert-no-mt"></div>
                            </div>
                            <?php if ($profile_privacy_field !== ''): ?>
                                <div class="lsd-profile-contact-form-row lsd-profile-contact-form-row-consent">
                                    <?php echo $profile_privacy_field; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
