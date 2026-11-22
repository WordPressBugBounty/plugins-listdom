<?php
// No Direct Access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Profile $this */

$username = get_query_var('user');
$user = LSD_User::get_user_info($username);

if (!isset($user['ID']) || !$user['ID'])
{
    echo LSD_Main::alert(esc_html__("User not found!", 'listdom'), 'error');
    return;
}

$auth = LSD_Options::auth();
$shortcode = isset($auth['profile']['shortcode']) && $auth['profile']['shortcode'] ? $auth['profile']['shortcode'] : '';

$listings = get_posts([
    'post_type' => LSD_Base::PTYPE_LISTING,
    'author' => $user['ID'],
    'posts_per_page' => -1,
]);

$current_user = wp_get_current_user();
$current_id = $current_user->ID;

// General Settings
$settings = LSD_Options::settings();

$user_object = get_userdata($user['ID']);
$user_roles = $user_object->roles;

// Check if user has allowed role
$allowed_roles = ['listdom_author', 'listdom_publisher', 'administrator'];

$profile_privacy_field = LSD_Privacy::consent_field([
    'id' => 'lsd_profile_privacy_consent_' . absint($user['ID']),
    'wrapper_class' => 'lsd-profile-privacy-consent-field',
    'context' => 'profile',
]);
?>
<div class="lsd-profile-wrapper lsd-mb-4">
    <div class="lsd-hero-image">
        <?php if (LSD_Portal_Fieldlayout::is_public('hero_image')): ?>
            <img class="lsd-hero" src="<?php
                $hero_image_url = LSD_User::get_user_image_url((int) $user['ID'], 'lsd_hero_image');
                echo $hero_image_url
                    ? esc_url($hero_image_url)
                    : esc_url($this->lsd_asset_url('img/no-image.jpg'));
            ?>" alt="<?php echo esc_attr__('Hero Image', 'listdom'); ?>">
        <?php endif; ?>
        <div class="lsd-profile-avatar">
            <?php echo $this->user_profile($user); ?>
        </div>
    </div>

    <div class="lsd-profile-details">
        <div class="lsd-profile-box lsd-profile-name-section lsd-flex lsd-flex-align-items-center lsd-flex-row lsd-flex-wrap lsd-gap-4">
            <div class="lsd-flex lsd-gap-5 lsd-flex-align-items-center lsd-name-section">
                <div class="lsd-flex lsd-flex-col lsd-gap-3 lsd-flex-items-start">
                    <h2 class="lsd-profile-name"><?php echo !empty($user['first_name']) || !empty($user['last_name']) ? esc_html(trim($user['first_name'] . ' ' . $user['last_name'])) : esc_html($user['display_name'] ?? ''); ?></h2>
                    <?php if (LSD_Portal_Fieldlayout::is_public('job_title')): ?>
                        <p class="lsd-job-title"><?php echo esc_html($user['job_title'] ?? ''); ?></p>
                    <?php endif; ?>
                </div>
                <div class="lsd-flex lsd-gap-2 lsd-author-listing-count">
                    <i class="lsd-fe-icon fa fa-clone"></i>
                    <div class="lsd-flex lsd-gap-1 lsd-flex-align-items-center">
                        <a href="#author_listings">
                            <span><?php echo count($listings);  ?> </span>
                            <span><?php echo count($listings) > 1 ? esc_html__('Listings', 'listdom') : esc_html__('Listing', 'listdom'); ?></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="lsd-message-buttons">
                <button class="lsd-message-modal-button">
                    <?php
                        $name = !empty($user['first_name']) ? $user['first_name'] : (!empty($user['last_name']) ? $user['last_name'] : $user['display_name']);
                        echo sprintf(
                            /* translators: %s: Profile owner name. */
                            esc_html__('Message %s', 'listdom'),
                            esc_html($name)
                        );
                    ?>
                </button>
                <div id="lsd-message-modal" class="lsd-modal">
                    <div class="lsd-modal-content">
                        <div class="lsd-profile-contact-form-wrapper">
                            <form class="lsd-profile-contact-form" id="lsd_profile_contact_form_<?php echo esc_attr($user['ID']); ?>" data-id="<?php echo esc_attr($user['ID']); ?>">

                                <div class="lsd-profile-contact-form-name-email-phone-wrapper">
                                    <div class="lsd-profile-contact-form-row lsd-profile-contact-form-row-name">
                                        <input
                                            class="lsd-form-control-input"
                                            type="text"
                                            name="lsd_name"
                                            placeholder="<?php esc_attr_e('Your Name', 'listdom') ?>"
                                            title="<?php esc_attr_e('Your Name', 'listdom') ?>"
                                            value="<?php echo $current_id ? esc_attr(trim($current_user->first_name . ' ' . $current_user->last_name)) : ''; ?>"
                                            required
                                        >
                                        <i class="lsd-fe-icon fa fa-user lsd-profile-contact-form-icon" aria-hidden="true"></i>
                                    </div>

                                    <div class="lsd-profile-contact-form-row lsd-profile-contact-form-row-email">
                                        <input
                                            class="lsd-form-control-input"
                                            type="email"
                                            name="lsd_email"
                                            placeholder="<?php esc_attr_e('Your Email', 'listdom') ?>"
                                            title="<?php esc_attr_e('Your Email', 'listdom') ?>"
                                            value="<?php echo $current_id ? esc_attr($current_user->user_email) : ''; ?>"
                                            required
                                        >
                                        <i class="lsd-fe-icon fa fa-envelope lsd-profile-contact-form-icon" aria-hidden="true"></i>
                                    </div>

                                    <div class="lsd-profile-contact-form-row lsd-profile-contact-form-row-phone">
                                        <input
                                            class="lsd-form-control-input"
                                            type="tel"
                                            name="lsd_phone"
                                            placeholder="<?php esc_attr_e('Your Phone', 'listdom') ?>"
                                            title="<?php esc_attr_e('Your Phone', 'listdom') ?>"
                                            value="<?php echo $current_id ? esc_attr(get_user_meta($current_id, 'lsd_phone', true)) : ''; ?>"
                                            required
                                        >
                                        <i class="lsd-fe-icon fas fa-phone-alt lsd-profile-contact-form-icon" aria-hidden="true"></i>
                                    </div>
                                </div>

                                <div class="lsd-profile-contact-form-row">
                                    <textarea
                                        class="lsd-form-control-textarea"
                                        name="lsd_message"
                                        placeholder="<?php esc_attr_e("I'm interested in ....", 'listdom') ?>"
                                        title="<?php esc_attr_e('Your Message', 'listdom') ?>"
                                        required
                                    ></textarea>
                                </div>

                                <?php if (isset($settings['mailchimp_status']) && $settings['mailchimp_status']): ?>
                                <div class="lsd-profile-contact-form-row">
                                    <label>
                                        <input type="checkbox" name="lsd_subscribe" value="1" <?php echo isset($settings['mailchimp_checked']) && $settings['mailchimp_checked'] ? 'checked' : ''; ?>>
                                        <?php echo esc_html($settings['mailchimp_message'] ?: esc_html__('Subscribe to our newsletter.', 'listdom')); ?>
                                    </label>
                                </div>
                                <?php endif; ?>

                                <?php if ($profile_privacy_field !== ''): ?>
                                <div class="lsd-profile-contact-form-row lsd-profile-contact-form-row-consent">
                                    <?php echo $profile_privacy_field; ?>
                                </div>
                                <?php endif; ?>

                                <div class="lsd-profile-contact-form-row lsd-profile-contact-form-third-row">
                                    <?php echo LSD_Main::grecaptcha_field('transform-95'); ?>
                                    <button class="lsd-general-button" type="submit">
                                        <?php esc_html_e('Send', 'listdom'); ?>
                                    </button>

                                    <?php wp_nonce_field('lsd_contact_' . $user['ID']); ?>
                                    <input type="hidden" name="lsd_user_id" value="<?php echo esc_attr($user['ID']); ?>">
                                    <input type="hidden" name="action" value="lsd_profile_contact">
                                </div>
                            </form>

                            <div class="lsd-profile-contact-form-alert"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $profile_body = LSD_Portal_Fieldlayout::view('public', [], (int) $user['ID']);
        if ($profile_body)
        {
            echo '<div class="lsd-profile-box lsd-profile-information">';
            include lsd_template('profile/field-body.php');
            echo '</div>';
        }
        ?>
        <?php if (array_intersect($allowed_roles, $user_roles)) : ?>
            <div class="lsd-profile-box" id="author_listings">
                <h3 class="lsd-profile-title"><?php esc_html_e('Author Listing', 'listdom'); ?></h3>
                <div class="lsd-author-listing">
                    <?php
                    $LSD = new LSD_Shortcodes_Listdom();
                    echo $LSD->output([
                        'id' => $shortcode,
                    ], [
                        'lsd_filter' => [
                            'authors' => [$user['ID']],
                        ],
                    ]);
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
