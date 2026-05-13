<?php

class LSD_API_Controllers_Profile extends LSD_API_Controller
{
    public function get(WP_REST_Request $request): WP_REST_Response
    {
        // Get Current User
        $user = wp_get_current_user();

        /** @var WP_Error $user */
        if (is_wp_error($user)) return $this->response([
            'data' => new WP_Error('400', $user->get_error_message()),
            'status' => 400,
        ]);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'user' => LSD_API_Resources_User::get($user),
            ],
            'status' => 200,
        ]);
    }

    public function update(WP_REST_Request $request): WP_REST_Response
    {
        $vars = $request->get_params();
        $profile_fields = LSD_User::profile_edit_fields();

        $first_name = isset($vars['first_name']) ? sanitize_text_field($vars['first_name']) : null;
        $last_name = isset($vars['last_name']) ? sanitize_text_field($vars['last_name']) : null;
        $description = isset($vars['description']) ? sanitize_textarea_field($vars['description']) : null;
        $phone = isset($vars['phone']) ? sanitize_text_field($vars['phone']) : null;
        $mobile = isset($vars['mobile']) ? sanitize_text_field($vars['mobile']) : null;
        $fax = isset($vars['fax']) ? sanitize_text_field($vars['fax']) : null;
        $website = isset($vars['website']) ? sanitize_text_field($vars['website']) : null;
        $job_title = isset($vars['job_title']) ? sanitize_text_field($vars['job_title']) : null;

        // Current User ID
        $user_id = get_current_user_id();

        // Update User
        $user_data = [
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => trim(($first_name ?? '') . ' ' . ($last_name ?? '')),
        ];
        if (!empty($profile_fields['bio'])) $user_data['description'] = $description;
        wp_update_user($user_data);

        // Update Meta Data
        if (!empty($profile_fields['phone'])) update_user_meta($user_id, 'lsd_phone', $phone);
        if (!empty($profile_fields['mobile'])) update_user_meta($user_id, 'lsd_mobile', $mobile);
        if (!empty($profile_fields['fax'])) update_user_meta($user_id, 'lsd_fax', $fax);
        if (!empty($profile_fields['website'])) update_user_meta($user_id, 'lsd_website', $website);
        if (!empty($profile_fields['job_title'])) update_user_meta($user_id, 'lsd_job_title', $job_title);
        if (!empty($profile_fields['profile_image']) && array_key_exists('profile_image', $vars)) self::update_user_image_meta($user_id, 'lsd_profile_image', $vars['profile_image']);
        if (!empty($profile_fields['hero_image']) && array_key_exists('hero_image', $vars)) self::update_user_image_meta($user_id, 'lsd_hero_image', $vars['hero_image']);

        $social_options = LSD_Options::socials();
        $social_handler = new LSD_Socials();
        $social_fields = isset($profile_fields['social']) && is_array($profile_fields['social']) ? $profile_fields['social'] : [];
        foreach ($social_fields as $network => $enabled)
        {
            if ((int) $enabled !== 1) continue;

            $network = sanitize_key($network);
            if ($network === '') continue;
            if (!array_key_exists($network, $vars)) continue;

            $social = self::sanitize_social_value($network, $vars[$network], $social_handler, $social_options);
            update_user_meta($user_id, 'lsd_' . $network, $social);
        }

        // Trigger Action
        do_action('lsd_api_user_profile_updated', $user_id, $request);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'user' => LSD_API_Resources_User::get($user_id),
            ],
            'status' => 200,
        ]);
    }

    private static function update_user_image_meta(int $user_id, string $meta_key, $value): void
    {
        $attachment_id = absint($value);
        update_user_meta($user_id, $meta_key, $attachment_id ?: '');
    }

    private static function sanitize_social_value(string $network, $value, LSD_Socials $social_handler, array $social_options): string
    {
        if (is_array($value) || is_object($value)) return '';

        $options = isset($social_options[$network]) && is_array($social_options[$network]) ? $social_options[$network] : [];
        $social = $social_handler->get($network, $options);
        $input_type = $social ? $social->get_input_type() : '';

        if ($input_type === 'url') return esc_url_raw((string) $value);
        if ($input_type === 'email') return sanitize_email((string) $value);

        return sanitize_text_field((string) $value);
    }
}
