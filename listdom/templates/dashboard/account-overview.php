<?php
defined('ABSPATH') || die();

$wrapper = $this->get_dashboard_wrapper();
$profile = $this->add_qs_var('mode', 'profile', $this->url);
$can_manage_profile = isset($this->menu_ids()['profile']);
$public_profile = LSD_Portal::url('profile') !== '' ? LSD_User::profile_link(get_current_user_id()) : '';
?>
<div class="<?php echo esc_attr($wrapper['class']); ?>"<?php echo $wrapper['attributes']; ?>>
    <div class="lsd-row lsd-dashboard-wrapper">
        <div class="lsd-dashboard-menus-wrapper"><?php echo LSD_Kses::full($this->menus()); ?></div>
        <div class="lsd-dashboard-content-wrapper">
            <div class="lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-4">
                <div class="lsd-fe-section-heading">
                    <div class="lsd-fe-title-icon">
                        <i class="lsd-fe-icon fa-solid fa-user" aria-hidden="true"></i>
                        <h2 class="lsd-fe-title"><?php esc_html_e('My Account', 'listdom'); ?></h2>
                    </div>
                    <p class="lsd-fe-description"><?php
                        /* translators: %s: Current user's display name. */
                        echo esc_html(sprintf(__('Welcome, %s.', 'listdom'), wp_get_current_user()->display_name));
                    ?></p>
                </div>
                <?php if ($can_manage_profile || $public_profile !== ''): ?>
                    <div class="lsd-fe-box-white">
                        <div class="lsd-fe-section-heading">
                            <h3 class="lsd-fe-title"><?php esc_html_e('Your profile', 'listdom'); ?></h3>
                            <?php if ($can_manage_profile): ?>
                                <p class="lsd-fe-description-small"><?php esc_html_e('Manage your profile, security, and linked sign-in methods.', 'listdom'); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="lsd-flex lsd-flex-wrap lsd-flex-align-items-center lsd-gap-3">
                            <?php if ($can_manage_profile): ?>
                                <a class="lsd-general-button" href="<?php echo esc_url($profile); ?>"><?php esc_html_e('Manage profile', 'listdom'); ?></a>
                            <?php endif; ?>
                            <?php if ($public_profile !== ''): ?>
                                <a class="lsd-light-button" href="<?php echo esc_url($public_profile); ?>"><?php esc_html_e('View your profile', 'listdom'); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
