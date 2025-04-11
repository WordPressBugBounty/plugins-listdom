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
        if (!is_user_logged_in()) return false;

        $roles = wp_get_current_user()->roles;
        foreach ($roles as $role)
        {
            if (!isset($this->settings['block_admin_' . $role]) || !$this->settings['block_admin_' . $role]) return false;
        }

        return true;
    }
}
