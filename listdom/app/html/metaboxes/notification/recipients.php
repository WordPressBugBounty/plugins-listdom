<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$original_to = get_post_meta($post->ID, 'lsd_original_to', true);
if(trim($original_to) == '') $original_to = 1;

$to = get_post_meta($post->ID, 'lsd_to', true);
$cc = get_post_meta($post->ID, 'lsd_cc', true);
$bcc = get_post_meta($post->ID, 'lsd_bcc', true);
?>
<div class="lsd-metabox lsd-notification-recipients-metabox">
    <div class="lsd-form-row">
        <div class="lsd-col-3">
            <?php /* Security Nonce */ LSD_Form::nonce('lsd_notification_cpt', '_lsdnonce'); ?>
            <?php echo LSD_Form::switcher([
                'id' => 'lsd_notification_recipients_original_to',
                'name' => 'lsd[original_to]',
                'value' => $original_to,
                'toggle' => '#lsd_notification_recipients_to',
            ]); ?>
        </div>
        <div class="lsd-col-9">
            <?php echo LSD_Form::label([
                'for' => 'lsd_notification_recipients_original_to',
                'title' => esc_html__('Original Recipient', 'listdom'),
            ]); ?>
        </div>
    </div>
    <div class="<?php echo ($original_to ? 'lsd-util-hide' : ''); ?>" id="lsd_notification_recipients_to">
        <div class="lsd-form-row">
            <div class="lsd-col-12">
                <?php echo LSD_Form::textarea([
                    'id' => 'lsd_notification_recipients_to',
                    'name' => 'lsd[to]',
                    'placeholder' => esc_html__('Comma separated emails ...', 'listdom'),
                    'value' => $to,
                ]); ?>
            </div>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-12">
            <?php echo LSD_Form::label([
                'for' => 'lsd_notification_recipients_cc',
                'title' => esc_html__('CC Recipients', 'listdom'),
            ]); ?>
            <?php echo LSD_Form::textarea([
                'id' => 'lsd_notification_recipients_cc',
                'name' => 'lsd[cc]',
                'placeholder' => esc_html__('Comma separated emails ...', 'listdom'),
                'value' => $cc,
            ]); ?>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-12">
            <?php echo LSD_Form::label([
                'for' => 'lsd_notification_recipients_bcc',
                'title' => esc_html__('BCC Recipients', 'listdom'),
            ]); ?>
            <?php echo LSD_Form::textarea([
                'id' => 'lsd_notification_recipients_bcc',
                'name' => 'lsd[bcc]',
                'placeholder' => esc_html__('Comma separated emails ...', 'listdom'),
                'value' => $bcc,
            ]); ?>
        </div>
    </div>
</div>