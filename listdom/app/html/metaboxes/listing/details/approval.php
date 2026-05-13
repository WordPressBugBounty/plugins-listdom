<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing $this */
/** @var WP_Post $post */

$guest_email = get_post_meta($post->ID, 'lsd_guest_email', true);
$guest_fullname = get_post_meta($post->ID, 'lsd_guest_fullname', true);
$guest_message = get_post_meta($post->ID, 'lsd_guest_message', true);
?>
<div class="lsd-approval <?php echo LSD_Base::get_lsd_class('box-white'); ?>">
    <div class="lsd-form-row">

        <div class="lsd-col-8"><h3 class="lsd-mt-0"><?php esc_html_e('Submitter Request', 'listdom'); ?></h3></div>
    </div>
    <div class="lsd-alert lsd-info">
        <?php if ($guest_fullname): ?>
        <div class="lsd-form-row">
            <div class="lsd-col-2 "><?php esc_html_e('Name', 'listdom'); ?></div>
            <div class="lsd-col-8"><?php echo esc_html($guest_fullname); ?></div>
        </div>
        <?php endif; ?>
        <div class="lsd-form-row">
            <div class="lsd-col-2 "><?php esc_html_e('Email', 'listdom'); ?></div>
            <div class="lsd-col-8"><?php echo esc_html($guest_email); ?></div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-2 "><?php esc_html_e('Message', 'listdom'); ?></div>
            <div class="lsd-col-8"><?php echo nl2br($guest_message); ?></div>
        </div>
    </div>
</div>
