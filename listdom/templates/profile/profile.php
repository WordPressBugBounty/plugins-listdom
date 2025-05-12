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

$socials = (new LSD_Socials())->list($user['ID']);

$auth = LSD_Options::auth();
$shortcode = isset($auth['profile']['shortcode']) && $auth['profile']['shortcode'] ? $auth['profile']['shortcode'] : '';

$listings = get_posts([
    'post_type' => LSD_Base::PTYPE_LISTING,
    'author' => $user['ID'],
    'posts_per_page' => -1,
]);

$current_user = wp_get_current_user();
$current_id = $current_user->ID;

$user_object = get_userdata($user['ID']);
$user_roles = (array) $user_object->roles;

// Check if user has allowed role
$allowed_roles = ['listdom_author', 'listdom_publisher', 'administrator'];
?>
<div class="lsd-profile-wrapper lsd-mb-4">
    <div class="lsd-hero-image">
        <img class="lsd-hero" src="<?php
            echo !empty($user['hero_image'])
                ? esc_url(wp_get_attachment_url($user['hero_image']))
                : esc_url($this->lsd_asset_url('img/no-image.jpg'));
        ?>" alt="<?php echo esc_attr__('Hero Image', 'listdom'); ?>">
        <div class="lsd-profile-avatar">
            <?php echo $this->user_profile($user); ?>
        </div>
    </div>

    <div class="lsd-profile-details">
        <div class="lsd-profile-box lsd-profile-name-section lsd-flex lsd-flex-align-items-center lsd-flex-row lsd-flex-wrap lsd-gap-4">
            <div class="lsd-flex lsd-gap-5 lsd-flex-align-items-center lsd-name-section">
                <div class="lsd-flex lsd-flex-col lsd-gap-3 lsd-flex-items-start">
                    <h2 class="lsd-profile-name"><?php echo !empty($user['first_name']) || !empty($user['last_name']) ? $user['first_name'] .' '. $user['last_name'] : esc_html($user['display_name'] ?? ''); ?></h2>
                    <p class="lsd-job-title"><?php echo esc_html($user['job_title'] ?? ''); ?></p>
                </div>
                <div class="lsd-flex lsd-gap-2 lsd-author-listing-count">
                    <i class="lsd-icon fa fa-clone"></i>
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
                        echo sprintf(esc_html__('Message %s', 'listdom'), esc_html($name));
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
                                        <i class="lsd-icon fa fa-user"></i>
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
                                        <i class="lsd-icon fa fa-envelope"></i>
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
                                        <i class="lsd-icon fas fa-phone-alt"></i>
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

                                <div class="lsd-profile-contact-form-row lsd-profile-contact-form-third-row">
                                    <?php echo LSD_Main::grecaptcha_field('transform-95'); ?>
                                    <button class="lsd-form-submit lsd-widefat lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?>" type="submit">
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

        <div class="lsd-grid lsd-g-6-columns">
            <div class="lsd-profile-box lsd-col-span-2 lsd-flex lsd-flex-col lsd-flex-align-items-start lsd-gap-4">
                <h3 class="lsd-profile-title"><?php esc_html_e('Contact Info', 'listdom'); ?></h3>
                <div class="lsd-profile-information">
                    <?php if (!empty($user['mobile'])): ?>
                        <div class="lsd-profile-mobile" title="<?php esc_attr_e('Mobile', 'listdom'); ?>" <?php echo lsd_schema()->telephone(); ?>>
                            <i class="lsd-icon fa fa-mobile"></i>
                            <a href="tel:<?php echo esc_html($user['mobile']); ?>"><?php echo esc_html($user['mobile']); ?></a>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($user['phone'])): ?>
                        <div class="lsd-profile-phone" title="<?php esc_attr_e('Phone', 'listdom'); ?>" <?php echo lsd_schema()->telephone(); ?>>
                            <i class="lsd-icon fas fa-phone-alt"></i>
                            <a href="tel:<?php echo esc_html($user['phone']); ?>"><?php echo esc_html($user['phone']); ?></a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($user['email'])): ?>
                        <div class="lsd-profile-email" title="<?php esc_attr_e('Email', 'listdom'); ?>" <?php echo lsd_schema()->email(); ?>>
                            <i class="lsd-icon fa fa-envelope"></i>
                            <a href="mailto:<?php echo esc_html($user['email']); ?>"><?php echo esc_html($user['email']); ?></a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($user['website'])): ?>
                        <div class="lsd-profile-website" title="<?php esc_attr_e('Website', 'listdom'); ?>">
                            <i class="lsd-icon fas fa-link"></i>
                            <a href="<?php echo esc_html($user['website']); ?>"><?php echo esc_html($user['website']); ?></a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($user['fax'])): ?>
                        <div class="lsd-profile-fax" title="<?php esc_attr_e('Fax', 'listdom'); ?>" <?php echo lsd_schema()->faxNumber(); ?>>
                            <i class="lsd-icon fa fa-fax"></i>
                            <span><?php echo esc_html($user['fax']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="lsd-profile-socials">
                    <ul class="lsd-flex lsd-flex-align-items-center lsd-gap-2 lsd-flex-wrap">
                        <?php echo LSD_Kses::element($socials); ?>
                    </ul>
                </div>
            </div>
            <div class="lsd-profile-box lsd-col-span-4">
                <h3 class="lsd-profile-title"><?php esc_html_e('About', 'listdom'); ?></h3>
                <p class="lsd-profile-bio"><?php echo esc_html($user['bio'] ?? esc_html__('No bio available', 'listdom')); ?></p>
            </div>
        </div>
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
