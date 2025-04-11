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
        $post_id = isset($_POST['lsd_post_id']) ? sanitize_text_field($_POST['lsd_post_id']) : '';
        $this->listing('lsd_contact_owner', 'lsd_contact_' . $post_id);

        // Update Listing Contacts
        (new LSD_Entity_Listing($post_id))->update_contacts();

        $this->response(['success' => 1, 'message' => esc_html__("Your message sent successfully.", 'listdom')]);
    }

    public function abuse()
    {
        $post_id = isset($_POST['lsd_post_id']) ? sanitize_text_field($_POST['lsd_post_id']) : '';
        $this->listing('lsd_listing_report_abuse', 'lsd_abuse_' . $post_id);

        $this->response(['success' => 1, 'message' => esc_html__("Your report sent successfully.", 'listdom')]);
    }

    public function profile()
    {
        $name = isset($_POST['lsd_name']) ? sanitize_text_field($_POST['lsd_name']) : '';
        $email = isset($_POST['lsd_email']) ? sanitize_email($_POST['lsd_email']) : '';
        $phone = isset($_POST['lsd_phone']) ? sanitize_text_field($_POST['lsd_phone']) : '';
        $message = isset($_POST['lsd_message']) ? strip_tags($_POST['lsd_message']) : '';
        $user_id = isset($_POST['lsd_user_id']) ? sanitize_text_field($_POST['lsd_user_id']) : '';

        // User ID is not set
        if (!trim($user_id)) $this->response(['success' => 0, 'message' => esc_html__("Invalid request!", 'listdom')]);

        // Check if security nonce is set
        if (!isset($_POST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is required.", 'listdom')]);

        // Verify that the nonce is valid
        if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'lsd_contact_' . $user_id)) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is invalid.", 'listdom')]);

        // Email is not valid
        if (!is_email($email)) $this->response(['success' => 0, 'message' => esc_html__("Invalid email!", 'listdom')]);

        $g_recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : null;
        if (!LSD_Main::grecaptcha_check($g_recaptcha_response)) $this->response(['success' => 0, 'message' => esc_html__("Google recaptcha is invalid.", 'listdom')]);

        do_action('lsd_profile_contact', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'user_id' => $user_id,
        ]);

        $this->response(['success' => 1, 'message' => esc_html__("Your message sent successfully.", 'listdom')]);
    }

    public function listing($hook, $nonce_action)
    {
        $name = isset($_POST['lsd_name']) ? sanitize_text_field($_POST['lsd_name']) : '';
        $email = isset($_POST['lsd_email']) ? sanitize_email($_POST['lsd_email']) : '';
        $phone = isset($_POST['lsd_phone']) ? sanitize_text_field($_POST['lsd_phone']) : '';
        $message = isset($_POST['lsd_message']) ? strip_tags($_POST['lsd_message']) : '';
        $post_id = isset($_POST['lsd_post_id']) ? sanitize_text_field($_POST['lsd_post_id']) : '';

        // Post ID is not set
        if (!trim($post_id)) $this->response(['success' => 0, 'message' => esc_html__("Invalid request!", 'listdom')]);

        // Check if security nonce is set
        if (!isset($_POST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is required.", 'listdom')]);

        // Verify that the nonce is valid
        if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $nonce_action)) $this->response(['success' => 0, 'message' => esc_html__("Security nonce is invalid.", 'listdom')]);

        // Email is not valid
        if (!is_email($email)) $this->response(['success' => 0, 'message' => esc_html__("Invalid email!", 'listdom')]);

        $g_recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : null;
        if (!LSD_Main::grecaptcha_check($g_recaptcha_response)) $this->response(['success' => 0, 'message' => esc_html__("Google recaptcha is invalid.", 'listdom')]);

        do_action($hook, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'post_id' => $post_id,
        ]);
    }
}
