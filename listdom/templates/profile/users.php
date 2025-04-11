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
    'number' => $limit,
    'orderby' => 'registered',
    'order' => 'DESC',
]);
?>
<div class="lsd-users-wrapper lsd-mb-4 lsd-users-style-<?php echo esc_attr($style); ?> <?php echo $style === 'grid' ? 'lsd-grid lsd-g-' . esc_attr($columns) . '-columns' : ''; ?>">
    <?php if (count($users)): ?>
        <?php foreach ($users as $user): ?>
            <div class="lsd-user-card">
                <div class="lsd-user-avatar">
                    <?php
                        $info = LSD_User::get_user_info($user->user_login);
                        echo $this->user_profile($info, 100);
                    ?>
                </div>
                <div class="lsd-user-info">
                    <a href="<?php echo esc_url_raw(LSD_User::profile_link($user->ID)); ?>">
                        <h3 class="lsd-user-name"><?php echo esc_html($user->display_name); ?></h3>
                    </a>
                    <p class="lsd-user-email"><?php echo esc_html($user->user_email); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="lsd-no-users"><?php echo esc_html__('No users found.', 'listdom'); ?></p>
    <?php endif; ?>
</div>
