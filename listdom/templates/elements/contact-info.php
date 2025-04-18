<?php
// no direct access
defined('ABSPATH') || die();

/** @var integer $post_id */
/** @var array $args */

$email = !isset($args['show_email']) || $args['show_email']
    ? get_post_meta($post_id, 'lsd_email', true)
    : '';

$phone = !isset($args['show_phone']) || $args['show_phone']
    ? get_post_meta($post_id, 'lsd_phone', true)
    : '';

$website = !isset($args['show_website']) || $args['show_website']
    ? get_post_meta($post_id, 'lsd_website', true)
    : '';

$contact_address = !isset($args['show_address']) || $args['show_address']
    ? get_post_meta($post_id, 'lsd_contact_address', true)
    : '';

$display_icon = !isset($args['display_icon']) || $args['display_icon'];
$display_label = isset($args['display_label']) && $args['display_label'];

// Social Networks
$socials = '';
if (!isset($args['show_socials']) || $args['show_socials']) $socials = (new LSD_Socials())->list($post_id, 'listing');

// No data
if (!$email && !$phone && !$website && !$contact_address && !$socials) return '';
?>
<div class="lsd-contact-info">
    <ul>

        <?php if ($phone): ?>
            <li>
                <?php if ($display_icon): ?>
                    <strong><i class="lsd-icon fas fa-phone-alt"></i></strong>
                <?php endif; ?>
                <?php if ($display_label): ?>
                    <span class="lsd-contact-info-label"><?php esc_html_e('Phone', 'listdom'); ?><span class="lsd-colon-mark">: </span></span>
                <?php endif; ?>
                <span <?php echo lsd_schema()->telephone(); ?>>
                    <a href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_html($phone); ?></a>
                </span>
            </li>
        <?php endif; ?>

        <?php if ($email): ?>
            <li>
                <?php if ($display_icon): ?>
                    <strong><i class="lsd-icon fa fa-envelope"></i></strong>
                <?php endif; ?>
                <?php if ($display_label): ?>
                    <span class="lsd-contact-info-label"><?php esc_html_e('Email', 'listdom'); ?><span class="lsd-colon-mark">: </span></span>
                <?php endif; ?>
                <span <?php echo lsd_schema()->email(); ?>>
                    <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                </span>
            </li>
        <?php endif; ?>

        <?php if ($website): ?>
            <li>
                <?php if ($display_icon): ?>
                    <strong><i class="lsd-icon fas fa-link"></i></strong>
                <?php endif; ?>
                <?php if ($display_label): ?>
                    <span class="lsd-contact-info-label"><?php esc_html_e('Website', 'listdom'); ?><span class="lsd-colon-mark">: </span></span>
                <?php endif; ?>
                <span <?php echo lsd_schema()->url(); ?>>
                    <a href="<?php echo esc_url_raw($website); ?>"
                       target="_blank"><?php echo esc_html(LSD_Base::remove_protocols($website)); ?></a>
                </span>
            </li>
        <?php endif; ?>

        <?php if ($contact_address): ?>
            <li>
                <?php if ($display_icon): ?>
                    <strong><i class="lsd-icon fas fa-search-location"></i></strong>
                <?php endif; ?>
                <?php if ($display_label): ?>
                    <span class="lsd-contact-info-label"><?php esc_html_e('Address', 'listdom'); ?><span class="lsd-colon-mark">: </span></span>
                <?php endif; ?>
                <span <?php echo lsd_schema()->address(); ?>><?php echo esc_html($contact_address); ?></span>
            </li>
        <?php endif; ?>

    </ul>

    <?php if (trim($socials)): ?>
        <div class="lsd-listing-social-networks">
            <ul><?php echo LSD_Kses::element($socials); ?></ul>
        </div>
    <?php endif; ?>
</div>
