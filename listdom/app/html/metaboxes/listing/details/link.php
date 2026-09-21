<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing $this */
/** @var LSD_Shortcodes_Dashboard|null $dashboard */
/** @var WP_Post $post */

$listing_link_enabled = !isset($this->settings['listing_link_status'])
    || (isset($this->settings['listing_link_status']) && $this->settings['listing_link_status']);

$link = get_post_meta($post->ID, 'lsd_link', true);
$link_id = $dashboard instanceof LSD_Shortcodes_Dashboard ? $dashboard->form_id('lsd_link') : 'lsd_link';
?>
<?php if ($listing_link_enabled): ?>
    <div class="lsd-form-row">
        <div class="lsd-col-2">
            <label class="lsd-fields-label" for="<?php echo esc_attr($link_id); ?>"><?php esc_html_e('Listing Custom Link', 'listdom'); ?><?php $dashboard && $dashboard->required_html('link'); ?></label>
            <span class="lsd-tooltip" data-lsd-tooltip="<?php esc_html_e("If you fill it, then it will be used to override the default single listing page link. You can use it for linking the listing to a custom internal or external address.", 'listdom'); ?>">
                <i class="fa fa-question-circle"></i>
            </span>
        </div>
        <div class="lsd-col-8">
            <input class="lsd-admin-input" type="url" name="lsd[link]" id="<?php echo esc_attr($link_id); ?>" placeholder="<?php esc_attr_e('https://anothersite.com/listing-page/', 'listdom'); ?>" value="<?php echo esc_attr($link); ?>">
        </div>
    </div>
    <?php else: ?>
    <input type="hidden" name="lsd[link]" value="">
    <?php endif; ?>
