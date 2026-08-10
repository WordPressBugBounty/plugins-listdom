<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */
/** @var bool $mirror */
?>
<div class="lsd-dashboard-box lsd-dashboard-submit <?php echo esc_attr($mirror ? 'lsd-dashboard-submit-mirror lsd-dashboard-submit-secondary' : 'lsd-dashboard-submit-primary'); ?> lsd-fe-box-white">
    <?php if (!$mirror): ?>
        <input type="hidden" name="id" value="<?php echo esc_attr($this->post->ID); ?>" id="lsd_dashboard_id">
        <input type="hidden" name="action" value="lsd_dashboard_listing_save">

        <?php LSD_Form::nonce('lsd_dashboard'); ?>
        <?php /* Security Nonce */ LSD_Form::nonce('lsd_listing_cpt', '_lsdnonce'); ?>
    <?php endif; ?>

    <div class="lsd-fe-subsections">
        <?php if ((!($this->settings['dashboard_listing_status'] ?? '') || $this->post->ID > 0) && current_user_can('publish_posts')): ?>
            <div class="lsd-dashboard-listing-status">
                <?php echo LSD_Form::select([
                    'id' => $mirror ? 'lsd_listing_status_mirror_bottom' : 'lsd_listing_status',
                    'name' => $mirror ? 'lsd_dashboard_listing_status_mirror_bottom' : 'lsd[listing_status]',
                    'class' => $mirror ? 'lsd-dashboard-submit-mirror-status' : 'lsd-dashboard-submit-real-status',
                    'value' => $this->post->post_status ?? 'publish',
                    'options' => [
                        'publish' => esc_html__('Published', 'listdom'),
                        'pending' => esc_html__('Pending Review', 'listdom'),
                        'draft' => esc_html__('Draft', 'listdom'),
                    ],
                    'required' => !$mirror,
                ]); ?>
            </div>
        <?php endif; ?>

        <?php
            $privacy_field = LSD_Privacy::consent_field([
                'id' => $mirror ? 'lsd_dashboard_privacy_consent_mirror_' . $this->post->ID : 'lsd_dashboard_privacy_consent_' . $this->post->ID,
                'name' => $mirror ? 'lsd_dashboard_privacy_consent_mirror' : 'lsd[privacy_consent]',
                'class' => $mirror ? 'lsd-privacy-consent-checkbox lsd-dashboard-submit-mirror-consent' : 'lsd-privacy-consent-checkbox lsd-dashboard-submit-real-consent',
                'required' => !$mirror,
                'wrapper_class' => $mirror ? 'lsd-dashboard-privacy-consent-field lsd-dashboard-submit-mirror-consent-field' : 'lsd-dashboard-privacy-consent-field lsd-dashboard-submit-real-consent-field',
                'context' => 'dashboard',
            ]);
        ?>
        <?php if ($privacy_field !== ''): ?>
            <div class="lsd-dashboard-privacy-consent">
                <?php echo LSD_Kses::form($privacy_field); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="lsd-dashboard-submit-wrapper">
        <?php if (!$mirror): ?>
            <div class="lsd-dashboard-grecaptcha">
                <?php echo LSD_Main::grecaptcha_field(); ?>
            </div>
        <?php endif; ?>

        <button type="submit" class="<?php echo esc_attr('lsd-general-button ' . $this->get_text_class()); ?>">
            <?php esc_html_e('Save', 'listdom'); ?>
            <i class="lsd-fe-icon fa-solid fa-long-arrow-right"></i>
        </button>
    </div>

    <?php if (!$mirror) do_action('lsd_dashboard_after_submit_button', $this); ?>
</div>
