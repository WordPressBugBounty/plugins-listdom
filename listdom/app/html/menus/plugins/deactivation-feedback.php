<?php
// no direct access
defined('ABSPATH') || die();

// Reasons
$reasons = [
    'no-longer-needed' => [
        'title' => esc_html__('I no longer need the plugin', 'listdom'),
        'placeholder' => '',
    ],
    'found-a-better-plugin' => [
        'title' => esc_html__('I found a better plugin', 'listdom'),
        'placeholder' => esc_html__('Please share which plugin', 'listdom'),
    ],
    'cannot-get-the-plugin-to-work' => [
        'title' => esc_html__("I couldn't get the plugin to work", 'listdom'),
        'placeholder' => '',
    ],
    'temporary-deactivation' => [
        'title' => esc_html__("It's a temporary deactivation", 'listdom'),
        'placeholder' => '',
    ],
    'other' => [
        'title' => esc_html__('Other', 'listdom'),
        'placeholder' => esc_html__('Please share the reason', 'listdom'),
    ],
];
?>
<div id="lsd-deactivate-feedback-dialog-wrapper" class="lsd-util-hide">
    <form id="lsd-deactivate-feedback-dialog-form" method="post">
        <div id="lsd_deactivation_feedback_message"></div>
        <?php wp_nonce_field('_lsd_deactivation_feedback_nonce'); ?>
        <input type="hidden" name="action" value="lsd_deactivation_feedback">

        <div id="lsd-deactivate-feedback-dialog-form-caption">
            <?php echo esc_html__('If you have a moment, please share why you are deactivating this plugin:', 'listdom'); ?>
        </div>
        <div id="lsd-deactivate-feedback-dialog-form-body" class="lsd-my-4">
            <?php foreach ($reasons as $reason_key => $reason): ?>
                <div class="lsd-deactivate-feedback-dialog-input-wrapper">
                    <div class="lsd--deactivate-feedback-dialog-radio-input-wrapper">
                        <input id="lsd-deactivate-feedback-<?php echo esc_attr($reason_key); ?>"
                               class="lsd-deactivate-feedback-dialog-input" type="radio" name="reason_key"
                               value="<?php echo esc_attr($reason_key); ?>">
                        <label for="lsd-deactivate-feedback-<?php echo esc_attr($reason_key); ?>"
                               class="lsd-deactivate-feedback-dialog-label"><?php echo esc_html($reason['title']); ?></label>
                    </div>
                    <?php if (trim($reason['placeholder'])): ?>
                        <div class="lsd-feedback-text-wrapper lsd-util-hide">
                            <input class="lsd-feedback-text" type="text"
                                   name="reason_<?php echo esc_attr($reason_key); ?>"
                                   placeholder="<?php echo esc_attr($reason['placeholder']); ?>"
                                   title="<?php echo esc_attr__('Details', 'listdom'); ?>">
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="lsd-feedback-buttons lsd-mt-4">
            <button type="submit" name="action_type" value="skip_deactivate" id="skip-deactivate-plugin"
                    class="lsd-button-skip">
                <?php echo esc_html__('Skip & Deactivate', 'listdom'); ?>
            </button>
            <button type="submit" name="action_type" value="submit_feedback" id="submit-feedback"
                    class="button button-primary">
                <?php echo esc_html__('Submit & Deactivate', 'listdom'); ?>
            </button>
        </div>
    </form>
</div>
