<?php
defined('ABSPATH') || die();

$key = $field['key'];
$image_id = $this->form_id('lsd_' . $key);
$message_id = $this->form_id('lsd_' . $key . '_message');
?>
<div class="lsd-image-row lsd-flex lsd-flex-col lsd-gap-2 <?php echo $key === 'hero_image' ? 'lsd-profile-hero-image-container' : 'lsd-profile-image-container'; ?>">
    <div class="lsd-col-12" id="<?php echo esc_attr($message_id); ?>"></div>
    <div class="lsd-flex lsd-flex-row lsd-gap-2 lsd-w-full">
        <div class="lsd-col-4 lsd-text-left">
            <label class="lsd-fields-label" for="<?php echo esc_attr($image_id); ?>"><?php echo esc_html($field['label']); ?></label>
            <?php if ($field['editable'] && $key === 'hero_image'): ?>
                <p class="description"><?php esc_html_e('Suggested: 1920*490px', 'listdom'); ?></p>
            <?php endif; ?>
        </div>
        <div class="lsd-col-8 lsd-profile-buttons">
            <?php if ($field['editable']): ?>
                <?php echo LSD_Form::imagepicker([
                    'id' => $image_id,
                    'name' => 'lsd[' . $key . ']',
                    'value' => $field['value'],
                    'upload_action' => 'lsd_dashboard_upload_profile_image',
                    'upload_nonce' => $profile_nonce,
                    'upload_message' => '#' . $message_id,
                ]); ?>
            <?php else: ?>
                <?php echo wp_get_attachment_image((int) $field['value'], 'medium'); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
