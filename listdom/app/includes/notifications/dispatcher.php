<?php

class LSD_Notifications_Dispatcher extends LSD_Notifications
{
    public function init()
    {
        // Contact Form
        add_action('wp_ajax_lsd_owner_contact', [$this, 'contact']);
        add_action('wp_ajax_nopriv_lsd_owner_contact', [$this, 'contact']);

        // Profile Contact Form
        add_action('wp_ajax_lsd_profile_contact', [$this, 'profile']);
        add_action('wp_ajax_nopriv_lsd_profile_contact', [$this, 'profile']);

        // Report Abuse Form
        add_action('wp_ajax_lsd_report_abuse', [$this, 'abuse']);
        add_action('wp_ajax_nopriv_lsd_report_abuse', [$this, 'abuse']);
    }

    public function contact()
    {
        $post_id = isset($_POST['lsd_post_id']) ? absint(wp_unslash($_POST['lsd_post_id'])) : 0;
        $this->listing('lsd_contact_owner', 'lsd_contact_' . $post_id);

        // Update Listing Contacts
        (new LSD_Entity_Listing($post_id))->update_contacts();

        $this->response(['success' => 1, 'message' => esc_html__("Your message sent successfully.", 'listdom')]);
    }

    public function abuse()
    {
        $post_id = isset($_POST['lsd_post_id']) ? absint(wp_unslash($_POST['lsd_post_id'])) : 0;
        $this->listing('lsd_listing_report_abuse', 'lsd_abuse_' . $post_id);

        $this->response(['success' => 1, 'message' => esc_html__("Your report sent successfully.", 'listdom')]);
    }

    public function profile()
    {
        $name = isset($_POST['lsd_name']) ? sanitize_text_field(wp_unslash($_POST['lsd_name'])) : '';
        $email = isset($_POST['lsd_email']) ? sanitize_email(wp_unslash($_POST['lsd_email'])) : '';
        $phone = isset($_POST['lsd_phone']) ? sanitize_text_field(wp_unslash($_POST['lsd_phone'])) : '';
        $message = isset($_POST['lsd_message']) ? sanitize_textarea_field(wp_unslash($_POST['lsd_message'])) : '';
        $subscribe = isset($_POST['lsd_subscribe']) ? sanitize_text_field(wp_unslash($_POST['lsd_subscribe'])) : '';
        $user_id = isset($_POST['lsd_user_id']) ? absint(wp_unslash($_POST['lsd_user_id'])) : 0;

        // User ID is not set
        if (!$user_id) $this->response(['success' => 0, 'message' => esc_html__("Invalid request!", 'listdom')]);

        // Check if security nonce is set
        if (!isset($_POST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is required.", 'listdom')]);

        // Verify that the nonce is valid
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'lsd_contact_' . $user_id)) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is invalid.", 'listdom')]);

        $consent = isset($_POST['lsd_privacy_consent']) ? sanitize_text_field(wp_unslash($_POST['lsd_privacy_consent'])) : '';
        $profile_consent_enabled = LSD_Privacy::is_consent_enabled('profile');
        if ($profile_consent_enabled && $consent !== '1') $this->response(['success' => 0, 'message' => LSD_Privacy::consent_required_text()]);

        // Email is not valid
        if (!is_email($email)) $this->response(['success' => 0, 'message' => esc_html__("Invalid email!", 'listdom')]);

        $g_recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : null;
        if (!LSD_Main::grecaptcha_check($g_recaptcha_response)) $this->response(['success' => 0, 'message' => esc_html__("Google recaptcha is invalid.", 'listdom')]);

        $profile_consent_granted = $profile_consent_enabled && $consent === '1';
        $consent_log = $profile_consent_granted ? LSD_Privacy::create_log([
            'context' => 'lsd_profile_contact',
            'user_id' => $user_id,
            'email' => $email,
        ]) : [];

        do_action('lsd_profile_contact', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'subscribe' => $subscribe,
            'user_id' => $user_id,
            'consent' => $profile_consent_granted,
            'consent_log' => $consent_log,
        ]);

        $this->response(['success' => 1, 'message' => esc_html__("Your message sent successfully.", 'listdom')]);
    }

    public function listing($hook, $nonce_action)
    {
        $name = isset($_POST['lsd_name']) ? sanitize_text_field(wp_unslash($_POST['lsd_name'])) : '';
        $email = isset($_POST['lsd_email']) ? sanitize_email(wp_unslash($_POST['lsd_email'])) : '';
        $phone = isset($_POST['lsd_phone']) ? sanitize_text_field(wp_unslash($_POST['lsd_phone'])) : '';
        $message = isset($_POST['lsd_message']) ? sanitize_textarea_field(wp_unslash($_POST['lsd_message'])) : '';
        $subscribe = isset($_POST['lsd_subscribe']) ? sanitize_text_field(wp_unslash($_POST['lsd_subscribe'])) : '';
        $post_id = isset($_POST['lsd_post_id']) ? absint(wp_unslash($_POST['lsd_post_id'])) : 0;

        // Post ID is not set
        if (!$post_id) $this->response(['success' => 0, 'message' => esc_html__("Invalid request!", 'listdom')]);
  
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';

        // Check if security nonce is set
        if (!$nonce) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is required.", 'listdom')]);

        // Verify that the nonce is valid
        if (!wp_verify_nonce($nonce, $nonce_action)) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is invalid.", 'listdom')]);

        $consent = isset($_POST['lsd_privacy_consent']) ? sanitize_text_field(wp_unslash($_POST['lsd_privacy_consent'])) : '';
        $consent_context = $hook === 'lsd_listing_report_abuse' ? 'report' : 'contact';
        $listing_consent_enabled = LSD_Privacy::is_consent_enabled($consent_context);
        if ($listing_consent_enabled && $consent !== '1') $this->response(['success' => 0, 'message' => LSD_Privacy::consent_required_text()]);

        // Email is not valid
        if (!is_email($email)) $this->response(['success' => 0, 'message' => esc_html__("Invalid email!", 'listdom')]);

        $g_recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : null;
        if (!LSD_Main::grecaptcha_check($g_recaptcha_response)) $this->response(['success' => 0, 'message' => esc_html__("Google recaptcha is invalid.", 'listdom')]);

        $listing_consent_granted = $listing_consent_enabled && $consent === '1';
        $consent_log = $listing_consent_granted ? LSD_Privacy::create_log([
            'context' => $hook,
            'post_id' => $post_id,
            'email' => $email,
        ]) : [];

        do_action($hook, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'subscribe' => $subscribe,
            'post_id' => $post_id,
            'consent' => $listing_consent_granted,
            'consent_log' => $consent_log,
        ]);

        return $post_id;
    }
}
