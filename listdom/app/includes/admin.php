<?php

class LSD_Admin extends LSD_Base
{
    private $settings;

    public function __construct()
    {
        $this->settings = LSD_Options::settings();
    }

    public function init()
    {
        // Activation Redirect
        add_action('admin_init', [$this, 'post_activate']);

        // Notices
        add_action('admin_notices', ['LSD_Flash', 'show']);

        // Database Tables
        add_action('admin_init', function ()
        {
            // Create Table
            if ((new LSD_Main())->is_db_update_required())
            {
                LSD_Plugin_Hooks::db_update();
            }
        });

        // Block Admin Access
        add_action('admin_init', [$this, 'block_admin']);
        add_filter('show_admin_bar', [$this, 'show_admin_bar']);

        // WordPress Dashboard Widget
        add_action('wp_dashboard_setup', [$this, 'dashboard_widget']);
    }

    public function post_activate()
    {
        // No need to run
        if (!get_option('lsd_activation_redirect', false) || wp_doing_ajax()) return;

        // Delete the option to don't do it again
        delete_option('lsd_activation_redirect');

        // Uncategorized Category
        LSD_Base::add_uncategorized();

        // Redirect to Listdom Dashboard
        wp_redirect(admin_url('/admin.php?page=' . LSD_Base::WELCOME_SLUG));
        exit;
    }

    public function block_admin(): void
    {
        if (wp_doing_ajax() || !is_user_logged_in()) return;

        // Redirect User to Home Page
        if ($this->is_admin_blocked())
        {
            wp_redirect(get_home_url());
            exit;
        }
    }

    public function show_admin_bar(): bool
    {
        return is_user_logged_in() && $this->is_admin_allowed();
    }

    private function is_admin_allowed(): bool
    {
        return !$this->is_admin_blocked();
    }

    private function is_admin_blocked(): bool
    {
        if (!is_user_logged_in() || is_super_admin()) return false;

        $roles = wp_get_current_user()->roles;
        foreach ($roles as $role)
        {
            if (!isset($this->settings['block_admin_' . $role]) || !$this->settings['block_admin_' . $role]) return false;
        }

        return true;
    }

    public function dashboard_widget(): void
    {
        wp_add_dashboard_widget(
            'lsd_news_updates',
            esc_html__('Listdom', 'listdom'),
            [$this, 'dashboard_widget_content']
        );
    }

    public function dashboard_widget_content(): void
    {
        if (!function_exists('fetch_feed')) include_once ABSPATH . WPINC . '/feed.php';

        $rss = fetch_feed('https://listdom.net/blog/feed/');
        $items = [];
        $error = null;

        if (is_wp_error($rss)) $error = esc_html__('Unable to retrieve news.', 'listdom');
        else
        {
            $max = $rss->get_item_quantity(5);
            $items = $rss->get_items(0, $max);

            if (!$items) $error = esc_html__('No news items found.', 'listdom');
        }

        $this->include_html_file('menus/plugins/dashboard-widget.php', [
            'parameters' => [
                'items' => $items,
                'error' => $error,
            ],
        ]);
    }
}
