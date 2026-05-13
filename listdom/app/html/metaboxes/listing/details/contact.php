<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing $this */
/** @var LSD_Shortcodes_Dashboard|null $dashboard */
/** @var WP_Post $post */

$email = get_post_meta($post->ID, 'lsd_email', true);
$phone = get_post_meta($post->ID, 'lsd_phone', true);
$website = get_post_meta($post->ID, 'lsd_website', true);
$contact_address = get_post_meta($post->ID, 'lsd_contact_address', true);

$use_dashboard_settings = $dashboard instanceof LSD_Shortcodes_Dashboard;
$contact_field_settings = $use_dashboard_settings ? ($this->settings['submission_contact_fields'] ?? []) : [];
$show_email = !$use_dashboard_settings || !isset($contact_field_settings['email']) || $contact_field_settings['email'];
$show_phone = !$use_dashboard_settings || !isset($contact_field_settings['phone']) || $contact_field_settings['phone'];
$show_website = !$use_dashboard_settings || !isset($contact_field_settings['website']) || $contact_field_settings['website'];
$show_contact_address = !$use_dashboard_settings || !isset($contact_field_settings['contact_address']) || $contact_field_settings['contact_address'];
?>
<div class="lsd-listing-module-contact <?php echo LSD_Base::get_lsd_class('box-white'); ?>">
    <div class="lsd-form-row">

        <div class="lsd-col-8"><h3 class="<?php echo LSD_Base::get_lsd_class('title'); ?>"><?php esc_html_e('Contact Details', 'listdom'); ?></h3></div>
    </div>
    <div class="lsd-form-row">
        <?php if ($show_email): ?>
            <div class="lsd-col-2">
                <label class="lsd-fields-label" for="lsd_email"><?php esc_html_e('Email', 'listdom'); ?><?php $dashboard && $dashboard->required_html('email'); ?></label>
            </div>
            <div class="lsd-col-8">
                <input class="lsd-admin-input" type="email" name="lsd[email]" id="lsd_email" placeholder="<?php esc_attr_e('Email', 'listdom'); ?>" value="<?php echo esc_attr($email); ?>">
            </div>
        <?php elseif ($use_dashboard_settings): ?>
            <input type="hidden" name="lsd[email]" value="<?php echo esc_attr($email); ?>">
        <?php endif; ?>
    </div>
    <div class="lsd-form-row">
        <?php if ($show_phone): ?>
            <div class="lsd-col-2">
                <label class="lsd-fields-label" for="lsd_phone"><?php esc_html_e('Phone', 'listdom'); ?><?php $dashboard && $dashboard->required_html('phone'); ?></label>
            </div>
            <div class="lsd-col-8">
                <input class="lsd-admin-input" type="tel" name="lsd[phone]" id="lsd_phone" placeholder="<?php esc_attr_e('Phone', 'listdom'); ?>" value="<?php echo esc_attr($phone); ?>">
            </div>
        <?php elseif ($use_dashboard_settings): ?>
            <input type="hidden" name="lsd[phone]" value="<?php echo esc_attr($phone); ?>">
        <?php endif; ?>
    </div>
    <div class="lsd-form-row">
        <?php if ($show_website): ?>
            <div class="lsd-col-2">
                <label class="lsd-fields-label" for="lsd_website"><?php esc_html_e('Website', 'listdom'); ?><?php $dashboard && $dashboard->required_html('website'); ?></label>
            </div>
            <div class="lsd-col-8">
                <input class="lsd-admin-input" type="url" name="lsd[website]" id="lsd_website" placeholder="<?php esc_attr_e('https://yourwebsite.com', 'listdom'); ?>" value="<?php echo esc_url($website); ?>">
            </div>
        <?php elseif ($use_dashboard_settings): ?>
            <input type="hidden" name="lsd[website]" value="<?php echo esc_url($website); ?>">
        <?php endif; ?>
    </div>
    <div class="lsd-form-row">
        <?php if ($show_contact_address): ?>
            <div class="lsd-col-2">
                <label class="lsd-fields-label" for="lsd_contact_address"><?php esc_html_e('Contact Address', 'listdom'); ?><?php $dashboard && $dashboard->required_html('contact_address'); ?></label>
            </div>
            <div class="lsd-col-8">
                <input class="lsd-admin-input" type="text" name="lsd[contact_address]" id="lsd_contact_address" placeholder="<?php echo esc_attr($this->settings['address_placeholder'] ?? ''); ?>" value="<?php echo esc_attr($contact_address); ?>">
            </div>
        <?php elseif ($use_dashboard_settings): ?>
            <input type="hidden" name="lsd[contact_address]" value="<?php echo esc_attr($contact_address); ?>">
        <?php endif; ?>
    </div>

    <?php do_action('lsd_social_networks_listing_form', $post, $dashboard); ?>

    <?php
        $this->include_html_file('metaboxes/listing/details/link.php', [
            'parameters' => compact('dashboard', 'post'),
        ]);
    ?>
</div>
