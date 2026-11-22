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

        $data = self::user_data($user);

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'user' => $data,
            ],
            'status' => 200,
        ]);
    }

    public function update(WP_REST_Request $request): WP_REST_Response
    {
        $vars = $request->get_params();
        $profile_fields = LSD_User::profile_edit_fields();

        $requested = $vars;
        // Account email changes go through Account Security.
        unset($requested['email']);
        if (array_key_exists('description', $vars)) $requested['bio'] = $vars['description'];
        else unset($requested['bio']);
        $allowed = LSD_Portal_Fieldlayout::check_profile_write($requested, $profile_fields);
        if (is_wp_error($allowed)) return $this->response(['data' => $allowed, 'status' => 400]);

        $first_name = isset($vars['first_name']) ? sanitize_text_field($vars['first_name']) : null;
        $last_name = isset($vars['last_name']) ? sanitize_text_field($vars['last_name']) : null;
        $description = isset($vars['description']) ? sanitize_textarea_field($vars['description']) : null;
        $phone = isset($vars['phone']) ? sanitize_text_field($vars['phone']) : null;
        $mobile = isset($vars['mobile']) ? sanitize_text_field($vars['mobile']) : null;
        $fax = isset($vars['fax']) ? sanitize_text_field($vars['fax']) : null;
        $website = isset($vars['website']) ? sanitize_text_field($vars['website']) : null;
        $job_title = isset($vars['job_title']) ? sanitize_text_field($vars['job_title']) : null;

        // Current User ID
        $user = wp_get_current_user();
        $user_id = $user->ID;
        $custom_fields = LSD_Portal_Fields::validate($vars['custom_fields'] ?? [], $user->roles, 'edit');
        if (is_wp_error($custom_fields)) return $this->response(['data' => $custom_fields, 'status' => 400]);

        // Update User
        $user_data = ['ID' => $user_id];
        if ($first_name !== null) $user_data['first_name'] = $first_name;
        if ($last_name !== null) $user_data['last_name'] = $last_name;
        if ($first_name !== null || $last_name !== null) $user_data['display_name'] = trim(($first_name ?? $user->first_name) . ' ' . ($last_name ?? $user->last_name));
        if (!empty($profile_fields['bio']) && $description !== null) $user_data['description'] = $description;
        $updated = wp_update_user($user_data);
        if (is_wp_error($updated)) return $this->response(['data' => $updated, 'status' => 400]);

        // Update Meta Data
        if (!empty($profile_fields['phone']) && $phone !== null) update_user_meta($user_id, 'lsd_phone', $phone);
        if (!empty($profile_fields['mobile']) && $mobile !== null) update_user_meta($user_id, 'lsd_mobile', $mobile);
        if (!empty($profile_fields['fax']) && $fax !== null) update_user_meta($user_id, 'lsd_fax', $fax);
        if (!empty($profile_fields['website']) && $website !== null) update_user_meta($user_id, 'lsd_website', $website);
        if (!empty($profile_fields['job_title']) && $job_title !== null) update_user_meta($user_id, 'lsd_job_title', $job_title);
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

        LSD_Portal_Fields::persist($user_id, $custom_fields);

        // Trigger Action
        do_action('lsd_api_user_profile_updated', $user_id, $request);

        $data = self::user_data(get_userdata($user_id));

        // Response
        return $this->response([
            'data' => [
                'success' => 1,
                'user' => $data,
            ],
            'status' => 200,
        ]);
    }

    private static function user_data(WP_User $user): array
    {
        $data = LSD_API_Resources_User::get($user);
        $visible = [];
        foreach (LSD_Portal_Fieldlayout::view('edit', $user->roles) as $field)
        {
            if ($field['kind'] === 'core') $visible[] = $field['key'];
        }
        foreach (LSD_Portal_Fieldlayout::core() as $key => $field)
        {
            // Keep the account identifier even when it is hidden in the profile editor.
            if ($key === 'email' || in_array($key, $visible, true)) continue;
            unset($data['data'][$key === 'bio' ? 'description' : $key]);
            if ($key === 'profile_image') unset($data['media']['avatar']);
        }
        $custom = LSD_Portal_Fields::read($user);
        if ($custom) $data['custom_fields'] = $custom;
        return $data;
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
