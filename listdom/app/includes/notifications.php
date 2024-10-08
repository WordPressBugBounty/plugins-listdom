<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Notifications Class.
 *
 * @class LSD_Notifications
 * @version    1.0.0
 */
class LSD_Notifications extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        // Notification Post Type
        $PType = new LSD_PTypes_Notification();
        $PType->init();

        // Dispatcher
        $Dispatcher = new LSD_Notifications_Dispatcher();
        $Dispatcher->init();

        // Send Emails
        $EmailPrepare = new LSD_Notifications_Email_Prepare();
        $EmailPrepare->init();
    }

    public static function get_notification_hooks()
    {
        $hooks = [
            'lsd_contact_owner' => esc_html__('Contact Owner', 'listdom'),
            'lsd_new_listing' => esc_html__('New Listing', 'listdom'),
            'lsd_listing_status_changed' => esc_html__('Listing Status Changed', 'listdom'),
            'lsd_listing_report_abuse' => esc_html__('Abuse Report', 'listdom'),
        ];

        return apply_filters('lsd_notification_hooks', $hooks);
    }

    public static function placeholders()
    {
        $placeholders = [
            'lsd_contact_owner' => [
                'name' => esc_html__('Name field of the contact form', 'listdom'),
                'email' => esc_html__('Email field of the contact form', 'listdom'),
                'phone' => esc_html__('Phone field of the contact form', 'listdom'),
                'message' => esc_html__('Sent message', 'listdom'),
                'listing_link' => esc_html__('Link to the listing', 'listdom'),
            ],
            'lsd_new_listing' => [
                'listing_status' => esc_html__('Status of new listing', 'listdom'),
                'listing_date' => esc_html__('Listing creation date', 'listdom'),
                'listing_title' => esc_html__('Title to the listing', 'listdom'),
                'owner_name' => esc_html__('Name of listing owner', 'listdom'),
                'owner_email' => esc_html__('Email or listing owner', 'listdom'),
                'listing_link' => esc_html__('Link to the listing', 'listdom'),
                'admin_link' => esc_html__('Link to the listing manage page in WordPress backend', 'listdom'),
            ],
            'lsd_listing_status_changed' => [
                'previous_status' => esc_html__('Previous Status', 'listdom'),
                'listing_status' => esc_html__('New Status', 'listdom'),
                'listing_date' => esc_html__('Listing creation date', 'listdom'),
                'listing_title' => esc_html__('Title to the listing', 'listdom'),
                'owner_name' => esc_html__('Name of listing owner', 'listdom'),
                'owner_email' => esc_html__('Email or listing owner', 'listdom'),
                'listing_link' => esc_html__('Link to the listing', 'listdom'),
            ],
            'lsd_listing_report_abuse' => [
                'name' => esc_html__('Name field of abuse form', 'listdom'),
                'email' => esc_html__('Email field of abuse form', 'listdom'),
                'phone' => esc_html__('Phone field of abuse form', 'listdom'),
                'message' => esc_html__('Sent message', 'listdom'),
                'listing_link' => esc_html__('Link to the listing', 'listdom'),
            ],
            'general' => [
                'date' => esc_html__('Current date while sending', 'listdom'),
                'time' => esc_html__('Current time while sending', 'listdom'),
                'datetime' => esc_html__('Current date & time while sending', 'listdom'),
                'admin_email' => esc_html__('Email of the website admin', 'listdom'),
                'site_name' => esc_html__('Subject of the website', 'listdom'),
                'site_url' => esc_html__('Home URL of the website', 'listdom'),
                'site_description' => esc_html__('Description of the website', 'listdom'),
                'site_link' => esc_html__('Link to the website. Subject of the website will be used as the link title', 'listdom'),
            ],
        ];

        return apply_filters('lsd_notification_placeholders', $placeholders);
    }

    public function get($hook)
    {
        return get_posts([
            'post_type' => LSD_Base::PTYPE_NOTIFICATION,
            'posts_per_page' => '-1',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => 'lsd_hook',
                    'value' => $hook,
                ],
            ],
        ]);
    }
}
