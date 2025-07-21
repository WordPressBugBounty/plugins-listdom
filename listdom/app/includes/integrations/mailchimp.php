<?php

class LSD_Integrations_Mailchimp extends LSD_Integrations
{
    public function init()
    {
        add_action('lsd_send_notification', [$this, 'subscribe'], 10, 4);
    }

    public function subscribe($user_id, $args, $notification_id, $listing_id = null)
    {
        if (!isset($args['subscribe']) || !$args['subscribe']) return;

        $settings = LSD_Options::settings();
        if (!isset($settings['mailchimp_status']) || !$settings['mailchimp_status']) return;

        $api_key = $settings['mailchimp_api_key'] ?? '';
        $list_id = $settings['mailchimp_list_id'] ?? '';
        $email = $args['email'] ?? '';

        if (!$api_key || !$list_id || !is_email($email)) return;

        $parts = explode('-', $api_key);
        if (count($parts) < 2) return;
        $dc = end($parts);

        $url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/';
        $body = json_encode([
            'email_address' => $email,
            'status_if_new' => 'subscribed',
            'status' => 'subscribed',
        ]);

        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'apikey ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => $body,
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 400)
        {
            $error = is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_body($response);
            error_log('Listdom Mailchimp Error: ' . $error);
        }
    }
}
