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
        $pre = !LSD_Portal::public_profiles() ? '' : apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-profile');
        if (trim($pre)) return $pre;

        // Get username from URL
        $username = get_query_var('user');

        // If no username, check if user is logged in
        if (!$username)
        {
            if (!is_user_logged_in()) {
                echo '<div class="lsd-p-5">'. do_shortcode('[listdom-auth]') . '</div>';
                return '';
            }

            $user = wp_get_current_user();
            $username = $user->user_login;
        }

        // Slugify email or username
        $username = $this->slugify_username($username);
        if (!LSD_Portal::public_profiles())
        {
            $user = LSD_User::get_user_info(get_query_var('user') ?: get_current_user_id());
            if (!$user || (int) $user['ID'] !== get_current_user_id())
            {
                return $this->alert(esc_html__('Profile unavailable.', 'listdom'), 'info');
            }
            $public_user = array_intersect_key($user, array_flip(['ID', 'display_name', 'bio', 'job_title']));
            ob_start();
            include lsd_template('profile/portal.php');
            return ob_get_clean();
        }

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
        if (!LSD_Portal::public_profiles()) return '';

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
        if (!LSD_Portal_Fieldlayout::is_public('profile_image')) return '';

        $user_id = isset($user['ID']) ? (int) $user['ID'] : 0;
        $profile_image_url = $user_id ? LSD_User::get_user_image_url($user_id, 'lsd_profile_image') : '';

        if ($profile_image_url)
        {
            return '<img src="' . esc_url($profile_image_url) . '" class="avatar avatar-'.esc_attr($size).' photo" height="'.esc_attr($size).'" width="'.esc_attr($size).'" alt="'.esc_attr__('Profile Image', 'listdom').'">';
        }

        return LSD_User::get_user_avatar(LSD_Portal_Fieldlayout::is_public('email') ? ($user['email'] ?? '') : '', $size);
    }
}
