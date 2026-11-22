<?php
defined('ABSPATH') || die();
?>
<div class="lsd-profile-wrapper">
    <div class="lsd-profile-box">
        <?php echo $this->user_profile($public_user); ?>
        <h2><?php echo esc_html($public_user['display_name']); ?></h2>
        <?php if (LSD_Portal_Fieldlayout::is_public('job_title')): ?>
            <p><?php echo esc_html($public_user['job_title']); ?></p>
        <?php endif; ?>
        <?php
        $profile_body = LSD_Portal_Fieldlayout::view('public', [], (int) $public_user['ID'], true);
        include lsd_template('profile/field-body.php');
        ?>
        <?php if ((int) $public_user['ID'] === get_current_user_id() && LSD_Portal::url('account') !== ''): ?>
            <a href="<?php echo esc_url(add_query_arg('mode', 'profile', LSD_Portal::url('account'))); ?>"><?php esc_html_e('Edit profile', 'listdom'); ?></a>
        <?php endif; ?>
    </div>
</div>
