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

        // Backend Header for post types
        add_action('all_admin_notices', [$this, 'backend_post_type_header']);

        // Backend Header for taxonomies
        add_action('all_admin_notices', [$this, 'backend_taxonomy_header']);

        // Show post states for Listdom system pages
        add_filter('display_post_states', [$this, 'post_states'], 10, 2);
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

        $this->include_html_file('plugin/dashboard-widget.php', [
            'parameters' => [
                'items' => $items,
                'error' => $error,
            ],
        ]);
    }

    public function backend_post_types(): array
    {
        $post_types = [
            LSD_Base::PTYPE_LISTING,
            LSD_Base::PTYPE_SHORTCODE,
            LSD_Base::PTYPE_SEARCH,
            LSD_Base::PTYPE_NOTIFICATION,
        ];

        return apply_filters('lsd_backend_header_post_types', $post_types);
    }

    public function backend_post_type_header()
    {
        if (!is_admin()) return;

        $screen = get_current_screen();
        if (!$screen || !in_array($screen->post_type, $this->backend_post_types())) return;

        if (!in_array($screen->base, ['edit', 'post'])) return;

        $obj = get_post_type_object($screen->post_type);
        if (!$obj) return;

        if ($screen->base === 'edit')
        {
            $title = $obj->labels->name;
            if (current_user_can($obj->cap->create_posts ?? 'edit_posts')) $title .= ' <a href="' . esc_url(admin_url('post-new.php?post_type=' . $screen->post_type)) . '" class="lsd-primary-button">' . esc_html($obj->labels->add_new) . '</a>';

            $url = admin_url('edit.php?post_type=' . $screen->post_type);
        }
        else
        {
            if (isset($_GET['action']) && $_GET['action'] === 'edit')
            {
                $title = $obj->labels->edit_item;
                if (current_user_can($obj->cap->create_posts ?? 'edit_posts')) $title .= ' <a href="' . esc_url(admin_url('post-new.php?post_type=' . $screen->post_type)) . '" class="lsd-primary-button">' . esc_html($obj->labels->add_new) . '</a>';

                $url = admin_url('post.php?post=' . (isset($_GET['post']) ? intval($_GET['post']) : 0) . '&action=edit');
            }
            else
            {
                $title = $obj->labels->add_new_item;
                $url = admin_url('post-new.php?post_type=' . $screen->post_type);
            }
        }

        $menus = apply_filters('lsd_post_type_header_menus', [], $screen);

        $this->include_html_file('plugin/post-type-header.php', [
            'parameters' => [
                'title' => $title,
                'url' => $url,
                'menus' => $menus,
            ],
        ]);
    }

    public function backend_taxonomies(): array
    {
        $taxonomies = LSD_Base::taxonomies();
        $taxonomies[] = LSD_Base::TAX_ATTRIBUTE;

        return apply_filters('lsd_backend_header_taxonomies', $taxonomies);
    }

    public function backend_taxonomy_header()
    {
        if (!is_admin()) return;

        $screen = get_current_screen();
        if (!$screen || !isset($screen->taxonomy) || !in_array($screen->taxonomy, $this->backend_taxonomies())) return;

        if (!in_array($screen->base, ['edit-tags', 'term'])) return;

        $obj = get_taxonomy($screen->taxonomy);
        if (!$obj) return;

        if ($screen->base === 'edit-tags')
        {
            $title = $obj->labels->name;
            $url = admin_url('edit-tags.php?taxonomy=' . $screen->taxonomy . '&post_type=' . $screen->post_type);
        }
        else
        {
            if (isset($_GET['tag_ID']))
            {
                $title = $obj->labels->edit_item;
                $url = admin_url('term.php?taxonomy=' . $screen->taxonomy . '&tag_ID=' . intval($_GET['tag_ID']) . '&post_type=' . $screen->post_type);
            }
            else
            {
                $title = $obj->labels->add_new_item;
                $url = admin_url('term-new.php?taxonomy=' . $screen->taxonomy . '&post_type=' . $screen->post_type);
            }
        }

        $menus = apply_filters('lsd_taxonomy_header_menus', [], $screen);

        $this->include_html_file('plugin/taxonomy-header.php', [
            'parameters' => [
                'title' => $title,
                'url' => $url,
                'show_links' => $screen->base === 'edit-tags',
                'menus' => $menus,
            ],
        ]);
    }

    public function post_states($states, $post)
    {
        if ($post->post_type !== 'page') return $states;

        $pages = $this->post_state_pages();
        foreach ($pages as $id => $label)
        {
            $id = (int) $id;
            if ($id && $post->ID === $id) $states['listdom_' . $id] = $label;
        }

        return $states;
    }

    public function post_state_pages(): array
    {
        $cached = get_transient('lsd_post_state_pages');
        if (is_array($cached)) return $cached;

        $settings = LSD_Options::settings();
        $auth = LSD_Options::auth();

        $pages = [
            $settings['submission_page'] ?? 0 => esc_html__('Listdom Dashboard Page', 'listdom'),
            $settings['add_listing_page'] ?? 0 => esc_html__('Listdom Add Listing Page', 'listdom'),
            $auth['auth']['login_page'] ?? 0 => esc_html__('Listdom Login Page', 'listdom'),
            $auth['auth']['register_page'] ?? 0 => esc_html__('Listdom Register Page', 'listdom'),
            $auth['auth']['forgot_password_page'] ?? 0 => esc_html__('Listdom Forgot Password Page', 'listdom'),
            $auth['profile']['page'] ?? 0 => esc_html__('Listdom Profile Page', 'listdom'),
            $auth['login']['redirect'] ?? 0 => esc_html__('Listdom Login Redirect Page', 'listdom'),
            $auth['register']['redirect'] ?? 0 => esc_html__('Listdom Register Redirect Page', 'listdom'),
            $auth['forgot_password']['redirect'] ?? 0 => esc_html__('Listdom Forgot Password Redirect Page', 'listdom'),
            $auth['logout']['redirect'] ?? 0 => esc_html__('Listdom Logout Redirect Page', 'listdom'),
            $auth['account']['redirect'] ?? 0 => esc_html__('Listdom Account Redirect Page', 'listdom'),
        ];

        // Include search form results pages
        $search_forms = get_posts([
            'post_type' => LSD_Base::PTYPE_SEARCH,
            'posts_per_page' => 100,
            'post_status' => 'publish',
        ]);

        foreach ($search_forms as $search)
        {
            $form = get_post_meta($search->ID, 'lsd_form', true);
            if (is_array($form) && isset($form['page']) && $form['page'])
            {
                $pages[(int) $form['page']] = sprintf(
                    esc_html__('Search Results (%s)', 'listdom'),
                    $search->post_title
                );
            }
        }

        $pages = apply_filters('lsd_post_state_pages', $pages);
        set_transient('lsd_post_state_pages', $pages, HOUR_IN_SECONDS);

        return $pages;
    }
}
