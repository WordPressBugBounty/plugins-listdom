<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Profile $this */

// Get shortcode attributes
$style = isset($atts['style']) && in_array($atts['style'], ['list', 'grid']) ? $atts['style'] : 'list';
$limit = isset($atts['limit']) ? (int) $atts['limit'] : 12;
$columns = isset($atts['columns']) ? (int) $atts['columns'] : 4;

// Fetch users
$users = get_users([
    'number'    => $limit,
    'orderby'   => 'registered',
    'order'     => 'DESC',
    'role__in'  => ['listdom_author', 'listdom_publisher', 'administrator'],
]);
?>
<div class="lsd-users-wrapper lsd-mb-4 lsd-users-style-<?php echo esc_attr($style); ?> <?php echo $style === 'grid' ? 'lsd-grid lsd-g-' . esc_attr($columns) . '-columns' : ''; ?>">
    <?php if (count($users)): ?>
        <?php foreach ($users as $user): ?>
            <?php $info = LSD_User::get_user_info($user->user_login); ?>
            <div class="lsd-user-card">
                <div class="lsd-user-avatar">
                    <a href="<?php echo esc_url_raw(LSD_User::profile_link($user->ID)); ?>">
                        <?php echo $this->user_profile($info, 191); ?>
                    </a>
                </div>
                <div class="lsd-user-top-bar">
                    <a href="<?php echo esc_url_raw(LSD_User::profile_link($user->ID)); ?>">
                        <h3 class="lsd-user-name"><?php echo esc_html($user->display_name); ?></h3>
                    </a>
                    <p class="lsd-user-job-title"><?php echo esc_html($info['job_title']); ?></p>

                    <?php if($style === 'list') : ?>
                        <div class="lsd-user-bio"><?php echo esc_html($info['bio']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="lsd-user-info">
                    <div class="lsd-user-bottom-bar">
                        <div class="lsd-user-socials lsd-flex">
                            <?php if (!empty($info['phone'])): ?>
                                <div class="lsd-profile-phone" title="<?php esc_attr_e('Phone', 'listdom'); ?>" <?php echo lsd_schema()->telephone(); ?>>

                                    <a href="tel:<?php echo esc_html($info['phone']); ?>"><i class="lsd-icon fas fa-phone-alt"></i></a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($info['email'])): ?>
                                <div class="lsd-profile-email" title="<?php esc_attr_e('Email', 'listdom'); ?>" <?php echo lsd_schema()->email(); ?>>
                                    <a href="mailto:<?php echo esc_html($info['email']); ?>"><i class="lsd-icon fa fa-envelope"></i></a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($info['website'])): ?>
                                <div class="lsd-profile-website" title="<?php esc_attr_e('Website', 'listdom'); ?>">
                                    <a href="<?php echo esc_html($info['website']); ?>"><i class="lsd-icon fas fa-link"></i></a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo esc_url_raw(LSD_User::profile_link($user->ID)); ?>" class="lsd-view-profile-button"><?php echo esc_html__('Profile', 'listdom'); ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="lsd-no-users"><?php echo esc_html__('No users found.', 'listdom'); ?></p>
    <?php endif; ?>
</div>
