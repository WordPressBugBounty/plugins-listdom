<?php

class LSD_Shortcodes_Profile extends LSD_Base
{
    public function init()
    {
        // Profile Shortcode
        add_shortcode('listdom-profile', [$this, 'profile']);
        add_shortcode('listdom-users', [$this, 'users']);

        // Add Rewrite Rule
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
    }

    public function add_rewrite_rules()
    {
        $auth = LSD_Options::auth();

        $profile_page = !empty($auth['profile']['page']) ? get_post_field('post_name', $auth['profile']['page']) : '';
        if ($profile_page) add_rewrite_rule('^' . $profile_page . '/([^/]+)/?$', 'index.php?pagename=' . $profile_page . '&user=$matches[1]', 'top');
    }

    public function add_query_vars($vars)
    {
        $vars[] = 'user';
        return $vars;
    }

    public function profile($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-profile');
        if (trim($pre)) return $pre;

        // Get username from URL
        $username = get_query_var('user');

        // If no username, check if user is logged in
        if (!$username)
        {
            if (!is_user_logged_in()) return '';

            $user = wp_get_current_user();
            $username = $user->user_login;
        }

        // Slugify email or username
        $username = $this->slugify_username($username);

        ob_start();
        include lsd_template('profile/profile.php');
        return ob_get_clean();
    }

    /**
     * Convert a username or email into a slug
     */
    private function slugify_username($username)
    {
        // If it's an email, convert it to a slug
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) $username = str_replace(['@', '.'], ['-at-', '-dot-'], strtolower($username));
        else $username = sanitize_title($username);

        return $username;
    }

    public function users($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-users');
        if (trim($pre)) return $pre;

        $atts = shortcode_atts([
            'style' => 'list',
            'limit' => 12,
            'columns' => 4,
        ], $atts, 'listdom-users');

        ob_start();
        include lsd_template('profile/users.php');
        return ob_get_clean();
    }

    public function user_profile($user, $size = 264)
    {
        $profile_image_id = $user['profile_image'] ?? null;

        if ($profile_image_id)
        {
            $profile_image_url = wp_get_attachment_url($profile_image_id);
            return '<img src="' . esc_url($profile_image_url) . '" class="avatar avatar-'.esc_attr($size).' photo" height="'.esc_attr($size).'" width="'.esc_attr($size).'" alt="'.esc_attr__('Profile Image', 'listdom').'">';
        }
        else
        {
            return LSD_User::get_user_avatar($user['email'] ?? '', $size);
        }
    }
}
