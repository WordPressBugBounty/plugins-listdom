<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Notifications Email Prepare Class.
 *
 * @class LSD_Notifications_Email_Prepare
 * @version    1.0.0
 */
class LSD_Notifications_Email_Prepare extends LSD_Notifications
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
        // Contact Email
        add_action('lsd_contact_owner', [$this, 'contact']);

        // New Listing
        add_action('lsd_new_listing', [$this, 'new_listing']);

        // Listing Status Changed
        add_action('lsd_listing_status_changed', [$this, 'listing_status_changed'], 10, 2);

        // Listing Status Changed
        add_action('lsd_listing_report_abuse', [$this, 'abuse'], 10, 2);
    }

    public function contact($args)
    {
        return $this->form($args, 'lsd_contact_owner');
    }

    public function new_listing($listing_id)
    {
        $owner_id = get_post_field('post_author', $listing_id);
        $owner_name = get_the_author_meta('display_name', $owner_id);
        $owner_email = get_the_author_meta('user_email', $owner_id);

        // Results
        $mails = [];

        $notifications = $this->get('lsd_new_listing');
        foreach ($notifications as $notification)
        {
            $content = get_post_meta($notification->ID, 'lsd_content', true);
            $subject = get_the_title($notification);

            // Send to original recipient?
            $original_to = get_post_meta($notification->ID, 'lsd_original_to', true);

            // Original Recipient
            if ($original_to) $to = get_bloginfo('admin_email');
            // Custom Recipient
            else $to = trim(get_post_meta($notification->ID, 'lsd_to', true), ', ');

            $cc = trim(get_post_meta($notification->ID, 'lsd_cc', true), ', ');
            $bcc = trim(get_post_meta($notification->ID, 'lsd_bcc', true), ', ');

            // Specific Placeholders
            foreach (['content', 'subject'] as $item)
            {
                $$item = str_replace('#owner_name#', $owner_name, $$item);
                $$item = str_replace('#owner_email#', $owner_email, $$item);
            }

            $sender = new LSD_Notifications_Email_Sender();
            $mails[] = $sender->boot($notification->ID)->to($to)->cc($cc)->bcc($bcc)->subject($subject)->content($content)->render($listing_id)->send();

            // Trigger Action
            do_action('lsd_send_notification', $this->admin_id(), [
                'owner_name' => $owner_name,
                'owner_email' => $owner_email,
            ], $notification->ID, $listing_id);
        }

        return $mails;
    }

    public function listing_status_changed($listing_id, $previous)
    {
        // Do not send anything for new listings
        if ($previous === 'new') return false;

        $owner_id = get_post_field('post_author', $listing_id);
        $owner_name = get_the_author_meta('display_name', $owner_id);
        $owner_email = get_the_author_meta('user_email', $owner_id);

        // Previous Status
        $status = get_post_status_object($previous);

        // Results
        $mails = [];

        $notifications = $this->get('lsd_listing_status_changed');
        foreach ($notifications as $notification)
        {
            $content = get_post_meta($notification->ID, 'lsd_content', true);
            $subject = get_the_title($notification);

            // Send to original recipient?
            $original_to = get_post_meta($notification->ID, 'lsd_original_to', true);

            // Original Recipient
            if ($original_to) $to = get_the_author_meta('email', $owner_id);
            // Custom Recipient
            else $to = trim(get_post_meta($notification->ID, 'lsd_to', true), ', ');

            $cc = trim(get_post_meta($notification->ID, 'lsd_cc', true), ', ');
            $bcc = trim(get_post_meta($notification->ID, 'lsd_bcc', true), ', ');

            // Specific Placeholders
            foreach (['content', 'subject'] as $item)
            {
                $$item = str_replace('#previous_status#', $status->label ?? '', $$item);
                $$item = str_replace('#owner_name#', $owner_name, $$item);
                $$item = str_replace('#owner_email#', $owner_email, $$item);
            }

            $sender = new LSD_Notifications_Email_Sender();
            $mails[] = $sender->boot($notification->ID)->to($to)->cc($cc)->bcc($bcc)->subject($subject)->content($content)->render($listing_id)->send();

            // Trigger Action
            do_action('lsd_send_notification', $owner_id, [
                'previous_status' => $status->label ?? '',
                'owner_name' => $owner_name,
                'owner_email' => $owner_email,
            ], $notification->ID, $listing_id);
        }

        return $mails;
    }

    public function abuse($args)
    {
        return $this->form($args, 'lsd_listing_report_abuse');
    }

    public function form($args, $hook)
    {
        $listing_id = $args['post_id'] ?? null;
        $owner_id = get_post_field('post_author', $listing_id);

        $name = $args['name'] ?? '';
        $email = $args['email'] ?? '';
        $phone = $args['phone'] ?? '';
        $message = $args['message'] ?? '';

        // Results
        $mails = [];

        $notifications = $this->get($hook);
        foreach ($notifications as $notification)
        {
            $content = get_post_meta($notification->ID, 'lsd_content', true);
            $subject = get_the_title($notification);

            // Send to original recipient?
            $original_to = get_post_meta($notification->ID, 'lsd_original_to', true);

            // Original Recipient
            if ($original_to)
            {
                if ($hook === 'lsd_listing_report_abuse') $to = get_bloginfo('admin_email');
                else $to = get_the_author_meta('email', $owner_id);
            }
            // Custom Recipient
            else $to = trim(get_post_meta($notification->ID, 'lsd_to', true), ', ');

            $cc = trim(get_post_meta($notification->ID, 'lsd_cc', true), ', ');
            $bcc = trim(get_post_meta($notification->ID, 'lsd_bcc', true), ', ');

            // Specific Placeholders
            foreach (['content', 'subject'] as $item)
            {
                $$item = str_replace('#name#', $name, $$item);
                $$item = str_replace('#email#', $email, $$item);
                $$item = str_replace('#phone#', $phone, $$item);
                $$item = str_replace('#message#', '<i>' . nl2br($message) . '</i>', $$item);
            }

            $sender = new LSD_Notifications_Email_Sender();
            $mails[] = $sender->boot($notification->ID)
                ->to($to)
                ->cc($cc)
                ->bcc($bcc)
                ->subject($subject)
                ->content($content)
                ->render($listing_id)
                ->send();

            // Trigger Action
            do_action('lsd_send_notification', ($hook === 'lsd_listing_report_abuse' ? $this->admin_id() : $owner_id), [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
            ], $notification->ID, $listing_id);
        }

        return $mails;
    }
}
