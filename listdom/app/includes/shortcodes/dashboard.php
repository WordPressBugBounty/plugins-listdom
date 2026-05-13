<?php

class LSD_Shortcodes_Dashboard extends LSD_Shortcodes
{
    public $atts = [];
    public $page;
    public $url;
    public $mode;
    public $alert;
    public $settings;
    public $guest_status;
    public $guest_registration;
    public $listings;
    public $post;
    public $limit;
    public $form_type;
    public $search;
    public $category;
    public $form_columns;
    public $sidebar_status;
    public $sidebar_horizontal_mode;

    /**
     * @var WP_Query
     */
    public $q;

    private $is_pro;

    public function __construct()
    {
        // Settings
        $this->settings = LSD_Options::settings();

        // Form Columns
        $form_columns = isset($this->settings['dashboard_form_columns']) ? (int) $this->settings['dashboard_form_columns'] : 2;
        $this->form_columns = $form_columns === 1 ? 1 : 2;

        // Sidebar Status
        $sidebar_status = $this->settings['dashboard_menu_sidebar_status'] ?? 'default';
        $allowed_sidebar_status = ['default', 'compact', 'horizontal'];
        $this->sidebar_status = in_array($sidebar_status, $allowed_sidebar_status, true) ? $sidebar_status : 'default';

        // Horizontal Sidebar Mode
        $horizontal_mode = $this->settings['dashboard_menu_sidebar_horizontal_mode'] ?? 'default';
        $allowed_horizontal_modes = ['default', 'dropdown', 'carousel'];
        $this->sidebar_horizontal_mode = in_array($horizontal_mode, $allowed_horizontal_modes, true) ? $horizontal_mode : 'default';

        // Listdom Pro?
        $this->is_pro = $this->isPro();

        // Guest Status
        $this->guest_status = $this->is_pro && isset($this->settings['submission_guest']) && $this->settings['submission_guest'];

        // Registration Method
        $this->guest_registration = $this->is_pro && isset($this->settings['submission_guest_registration'])
            ? $this->settings['submission_guest_registration']
            : 'approval';
    }

    public function get_dashboard_wrapper(array $options = []): array
    {
        $options = wp_parse_args($options, [
            'classes' => ['lsd-dashboard'],
            'attributes' => [],
            'sidebar_status' => $this->sidebar_status ?? 'default',
            'sidebar_horizontal_mode' => $this->sidebar_horizontal_mode ?? 'default',
        ]);

        $sidebar_status = in_array($options['sidebar_status'], ['default', 'compact', 'horizontal'], true)
            ? $options['sidebar_status']
            : 'default';
        $sidebar_horizontal_mode = in_array($options['sidebar_horizontal_mode'], ['default', 'dropdown', 'carousel'], true)
            ? $options['sidebar_horizontal_mode']
            : 'default';

        $classes = is_array($options['classes']) ? array_values(array_filter(array_map('trim', $options['classes']))) : [];
        if (!in_array('lsd-dashboard', $classes, true)) array_unshift($classes, 'lsd-dashboard');

        if ($sidebar_status === 'horizontal')
        {
            $classes[] = 'lsd-dashboard-sidebar-horizontal';

            $horizontal_class_map = [
                'dropdown' => 'lsd-dashboard-sidebar-horizontal-dropdown',
                'carousel' => 'lsd-dashboard-sidebar-horizontal-carousel',
            ];

            if (isset($horizontal_class_map[$sidebar_horizontal_mode])) $classes[] = $horizontal_class_map[$sidebar_horizontal_mode];
        }
        else
        {
            $classes[] = 'lsd-dashboard-sidebar-vertical';
            $classes[] = $sidebar_status === 'compact' ? 'lsd-dashboard-sidebar-compact' : 'lsd-dashboard-sidebar-default';
        }

        $attributes = is_array($options['attributes']) ? $options['attributes'] : [];
        $attributes['data-sidebar-status'] = $sidebar_status;
        $attributes['data-horizontal-mode'] = $sidebar_horizontal_mode;

        return [
            'class' => implode(' ', array_unique($classes)),
            'attributes' => $this->format_html_attributes($attributes),
        ];
    }

    private function format_html_attributes(array $attributes): string
    {
        $result = '';

        foreach ($attributes as $attribute => $value)
        {
            if ($value === null) continue;

            $attribute = esc_attr($attribute);
            $value = is_scalar($value) ? (string) $value : '';

            $result .= $value === ''
                ? sprintf(' %s', $attribute)
                : sprintf(' %s="%s"', $attribute, esc_attr($value));
        }

        return $result;
    }

    public function init()
    {
        // WP Libraries
        if (!function_exists('wp_terms_checklist')) include ABSPATH . 'wp-admin/includes/template.php';

        // Shortcode
        add_shortcode('listdom-dashboard', [$this, 'output']);

        // Attributes Form
        add_action('lsd_dashboard_attributes_metabox', [$this, 'attributes']);

        // Redirect Guest Users
        add_action('wp', [$this, 'redirect']);

        // Save Listing
        add_action('wp_ajax_lsd_dashboard_listing_save', [$this, 'save']);
        add_action('wp_ajax_nopriv_lsd_dashboard_listing_save', [$this, 'save']);

        // Upload Featured Image
        add_action('wp_ajax_lsd_dashboard_listing_upload_featured_image', [$this, 'upload']);
        add_action('wp_ajax_nopriv_lsd_dashboard_listing_upload_featured_image', [$this, 'upload']);

        // Delete Listing
        add_action('wp_ajax_lsd_dashboard_listing_delete', [$this, 'delete']);
        add_action('wp_ajax_nopriv_lsd_dashboard_listing_delete', [$this, 'delete']);

        // Upload Gallery
        add_action('wp_ajax_lsd_dashboard_listing_upload_gallery', [$this, 'gallery']);
        add_action('wp_ajax_nopriv_lsd_dashboard_listing_upload_gallery', [$this, 'gallery']);

        // Upload Profile Image
        add_action('wp_ajax_lsd_dashboard_upload_profile_image', [$this, 'upload']);
        add_action('wp_ajax_nopriv_lsd_dashboard_upload_profile_image', [$this, 'upload']);

        // Save Profile
        add_action('wp_ajax_lsd_dashboard_profile_save', [$this, 'save_profile']);
        add_action('wp_ajax_nopriv_lsd_dashboard_profile_save', [$this, 'save_profile']);

        // refresh additional categories
        add_action('wp_ajax_lsd_refresh_additional_categories', [$this, 'ajax_refresh_additional_categories']);
        add_action('wp_ajax_nopriv_lsd_refresh_additional_categories', [$this, 'ajax_refresh_additional_categories']);

        add_filter('body_class', [$this, 'listdom_dashboard_class']);
    }

    public function listdom_dashboard_class($classes)
    {
        if (!is_array($classes)) $classes = [];

        if (LSD_Dashboard::is_dashboard()) $classes[] = 'lsd-dashboard-page';

        return $classes;
    }

    public function output($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-dashboard');
        if (trim($pre)) return $pre;

        // Include WordPress Media
        LSD_Assets::media();

        // Shortcode attributes
        $this->atts = is_array($atts) ? $atts : [];

        // Dashboard Page
        global $post;
        $this->page = $post;

        // Dashboard URL
        $this->url = get_permalink($this->page);

        // Mode
        $this->mode = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : $this->get_default_mode();

        // Listdom Bar
        $bar = LSD_Bar::instance();

        if ($this->mode === 'form')
        {
            $id = isset($_GET['id']) ? (int) sanitize_text_field($_GET['id']) : 0;

            if ($id > 0) $bar->add($id);
            else $bar->menu(
                admin_url('post-new.php?post_type=' . LSD_Base::PTYPE_LISTING),
                esc_html__('Add Listing', 'listdom')
            );
        }

        // Payload
        LSD_Payload::set('dashboard', $this);

        // Dashboard Settings in Listdom Bar
        $bar->menu(
            admin_url('admin.php?page=listdom-settings&tab=frontend-dashboard'),
            esc_html__('Dashboard Settings', 'listdom')
        );

        // Dashboard
        if ($this->mode === 'manage') return $this->manage();
        // Form
        else if ($this->mode === 'form') return $this->form();
        // Profile
        else if ($this->mode === 'profile') return $this->profile();
        // Other Modes
        else return apply_filters('lsd_dashboard_modes', $this->alert(esc_html__('Not found!', 'listdom'), 'warning'), $this);
    }

    public function manage()
    {
        if (!get_current_user_id() && !$this->guest_status)
        {
            return $this->auth();
        }

        // Listing Per Page
        $this->limit = 20;

        // Current Page
        $paged = max(1, get_query_var('paged'));

        // Status
        $status = $_GET['status'] ?? '';

        // Search
        $this->search = isset($_GET['lsd_s']) ? sanitize_text_field($_GET['lsd_s']) : '';
        $this->category = isset($_GET['lsd_category']) ? (int) sanitize_text_field($_GET['lsd_category']) : 0;

        // Get Listings
        $query = [
            'post_type' => LSD_Base::PTYPE_LISTING,
            'posts_per_page' => $this->limit,
            'post_status' => $status ?: ['publish', 'pending', 'draft', 'trash', LSD_Base::STATUS_HOLD, LSD_Base::STATUS_EXPIRED],
            'paged' => $paged,
        ];

        if ($this->search) $query['s'] = $this->search;
        if ($this->category)
        {
            $query['tax_query'] = [
                [
                    'taxonomy' => LSD_Base::TAX_CATEGORY,
                    'field' => 'term_id',
                    'terms' => $this->category,
                ],
            ];
        }

        // Filter by Author
        if (!current_user_can('edit_others_posts')) $query['author'] = get_current_user_id();

        // Apply Filters
        $query = apply_filters('lsd_dashboard_manage_query', $query);

        // Query
        $this->q = new WP_Query($query);

        // Search Listings
        if (get_current_user_id() && $this->q->have_posts())
        {
            while ($this->q->have_posts())
            {
                $this->q->the_post();
                $this->listings[] = get_post();
            }
        }
        else $this->listings = [];

        // Restore original Post Data
        LSD_LifeCycle::reset();

        // Dashboard
        ob_start();
        include lsd_template('dashboard/manage.php');
        return ob_get_clean();
    }

    public function item($listing)
    {
        include lsd_template('dashboard/item.php');
    }

    public function form()
    {
        if (!get_current_user_id() && !$this->guest_status) return $this->auth();

        $menu_ids = $this->menu_ids();
        if (!isset($menu_ids[$this->mode]))
        {
            return apply_filters('lsd_dashboard_modes', $this->alert(esc_html__('Not found!', 'listdom'), 'warning'), $this);
        }

        if (!LSD_Capability::can('edit_listings', 'edit_posts') && !$this->guest_status)
        {
            return $this->alert(esc_html__("Unfortunately you don't have permission to create or edit listings.", 'listdom'), 'error');
        }

        $id = isset($_GET['id']) ? (int) sanitize_text_field($_GET['id']) : 0;

        // Selected post is not a listing
        if ($id > 0 && get_post_type($id) != LSD_Base::PTYPE_LISTING)
        {
            return $this->alert(esc_html__("Sorry! Selected post is not a listing.", 'listdom'), 'error');
        }

        // Show a warning to current user if modification of post is not possible for him/her
        if ($id > 0 && !LSD_Capability::can('edit_listing', 'edit_post', $id))
        {
            return $this->alert(esc_html__("Sorry! You don't have access to modify this listing.", 'listdom'), 'error');
        }

        // Get Post Data
        $this->post = get_post($id);

        if ($id <= 0)
        {
            $this->post = new stdClass();
            $this->post->ID = 0;
        }

        // Dashboard
        ob_start();
        include lsd_template('dashboard/form.php');
        return ob_get_clean();
    }

    public function profile()
    {
        if (!get_current_user_id()) return $this->auth();

        $menu_ids = $this->menu_ids();
        if (!isset($menu_ids[$this->mode]))
        {
            return apply_filters('lsd_dashboard_modes', $this->alert(esc_html__('Not found!', 'listdom'), 'warning'), $this);
        }

        // Dashboard
        ob_start();
        include lsd_template('dashboard/profile.php');
        return ob_get_clean();
    }

    public function attributes(LSD_Shortcodes_Dashboard $dashboard)
    {
        (new LSD_PTypes_Listing())
            ->metabox_attributes($dashboard->post);
    }

    public function redirect()
    {
        if (
            !get_current_user_id() &&
            !$this->guest_status &&
            isset($this->settings['submission_guest_redirect']) &&
            $this->settings['submission_guest_redirect'] &&
            is_page() &&
            LSD_Dashboard::is_dashboard()
        )
        {
            $url = wp_login_url();

            wp_redirect($url);
            exit;
        }
    }

    public function is_enabled($module)
    {
        $enabled = true;

        // Module is disabled
        if (isset($this->settings['submission_module'][$module]) && !$this->settings['submission_module'][$module]) $enabled = false;

        // Module is enabled only for admin and editor
        if (isset($this->settings['submission_module'][$module]) && $this->settings['submission_module'][$module] == 2 && !current_user_can('edit_others_pages')) $enabled = false;

        // Apply Filters
        return apply_filters('lsd_dashboard_modules_status', $enabled, $module);
    }

    public function is_required($field)
    {
        $required = false;

        // Field is required
        if (isset($this->settings['submission_fields'][$field]['required']) && $this->settings['submission_fields'][$field]['required']) $required = true;

        if ($required && isset($this->settings['submission_contact_fields']) && is_array($this->settings['submission_contact_fields']))
        {
            $contact_fields = $this->settings['submission_contact_fields'];
            $contact_field_keys = ['email', 'phone', 'website', 'contact_address'];

            if (in_array($field, $contact_field_keys, true) && isset($contact_fields[$field]) && !$contact_fields[$field]) $required = false;
        }

        // Apply Filters
        return apply_filters('lsd_dashboard_field_required', $required, $field);
    }

    public function required_html($field)
    {
        echo $this->is_required($field) ? ' ' . LSD_Base::REQ_HTML : '';
    }

    public function menus(): string
    {
        // Apply Filters
        $menus = $this->menu_ids();

        // Current Page
        $current = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : $this->get_default_mode($menus);

        $sidebar_status = $this->sidebar_status ?? 'default';
        $horizontal_mode = $this->sidebar_horizontal_mode ?? 'default';
        $initial_mode = $sidebar_status === 'compact' ? 'compact' : 'default';

        $output = '<div class="lsd-dashboard-menu-container" data-sidebar-status="' . esc_attr($sidebar_status) . '" data-horizontal-mode="' . esc_attr($horizontal_mode) . '">';

        $menu_classes = ['lsd-dashboard-menus'];

        if ($sidebar_status === 'horizontal' && $horizontal_mode === 'carousel')
        {
            $menu_classes[] = 'lsd-dashboard-menus-carousel';
            $menu_classes[] = 'lsd-owl-carousel';
        }

        $output .= '<ul class="' . esc_attr(implode(' ', $menu_classes)) . '">';

        $visible_menus = $menus;
        $dropdown_menus = [];
        $dropdown_limit = 5;

        if ($sidebar_status === 'horizontal' && $horizontal_mode === 'dropdown' && count($menus) > $dropdown_limit)
        {
            $visible_menus = array_slice($menus, 0, $dropdown_limit, true);
            $dropdown_menus = array_slice($menus, $dropdown_limit, null, true);
        }

        $render_menu_item = static function ($key, $menu, $current, $extra_classes = '')
        {
            $target = $menu['target'] ?? '_self';
            $icon = $menu['icon'] ?? 'fas fa-tachometer-alt';
            $id = $menu['id'] ?? 'lsd_dashboard_menus_' . $key;
            $url = $menu['url'] ?? '#';
            $label = $menu['label'] ?? '';

            $classes = [];
            if (trim($extra_classes) !== '') $classes[] = trim($extra_classes);
            if ($current == $key) $classes[] = 'lsd-active';

            $class_attribute = count($classes) ? ' class="' . esc_attr(implode(' ', $classes)) . '"' : '';

            $item  = '<li id="' . esc_attr($id) . '"' . $class_attribute . '>';
            $item .= '<a href="' . esc_url($url) . '" target="' . esc_attr($target) . '"><i class="lsd-fe-icon ' . esc_attr($icon) . '"></i><span class="lsd-dashboard-menu-label">' . esc_html($label) . '</span></a>';
            $item .= '</li>';

            return $item;
        };

        foreach ($visible_menus as $key => $menu)
        {
            $output .= $render_menu_item($key, $menu, $current);
        }

        if (!empty($dropdown_menus))
        {
            $more_classes = 'lsd-dashboard-menu-more';
            if (isset($dropdown_menus[$current])) $more_classes .= ' lsd-active';

            $output .= '<li class="' . esc_attr($more_classes) . '">';
            $output .= '<a class="lsd-dashboard-menu-more-trigger" aria-haspopup="true" aria-expanded="false">';
            $output .= '<i class="lsd-fe-icon fa-solid fa-ellipsis" aria-hidden="true"></i>';
            $output .= '<span class="screen-reader-text">' . esc_html__('Open additional dashboard links', 'listdom') . '</span>';
            $output .= '</a>';
            $output .= '<ul class="lsd-dashboard-menu-more-list">';
            foreach ($dropdown_menus as $key => $menu)
            {
                $output .= $render_menu_item($key, $menu, $current, 'lsd-dashboard-menu-more-item');
            }
            $output .= '</ul>';
            $output .= '</li>';
        }

        if ($sidebar_status === 'compact')
        {
            $is_expanded = $initial_mode !== 'compact';
            $aria_expanded = $is_expanded ? 'true' : 'false';
            $icon_class = $is_expanded ? 'fa-long-arrow-left' : 'fa-long-arrow-right';
            $output .= '<li class="lsd-dashboard-menu-toggle">';
            $output .= '<button type="button" class="lsd-dashboard-menu-toggle-trigger" aria-label="' . esc_attr__('Toggle dashboard menu', 'listdom') . '" aria-expanded="' . esc_attr($aria_expanded) . '" data-initial-mode="' . esc_attr($initial_mode) . '">';
            $output .= '<i class="lsd-fe-icon fa-solid ' . esc_attr($icon_class) . '" aria-hidden="true"></i>';
            $output .= '<span class="screen-reader-text">' . esc_html__('Toggle dashboard menu', 'listdom') . '</span>';
            $output .= '</button>';
            $output .= '</li>';
        }
        $output .= '</ul>';

        $output .= '</div>';
        return $output;
    }

    public function menu_ids(bool $include_disabled = false): array
    {
        // Menus Data
        $order_menus = $this->settings['dashboard_menus'] ?? [];
        $statuses = $this->settings['dashboard_menu_builtin_status'] ?? [];
        $data = $this->settings['dashboard_menu_builtin'] ?? [];

        // Default Menus
        $menus = [];
        if ($include_disabled || !isset($statuses['manage']) || (int) $statuses['manage'] === 1)
        {
            $manage_label = $data['manage']['label'] ?? '';
            $manage_icon = $data['manage']['icon'] ?? '';
            $menus['manage'] = [
                'label' => $manage_label !== '' ? $manage_label : esc_html__('Dashboard', 'listdom'),
                'default_label' => esc_html__('Dashboard', 'listdom'),
                'id' => 'lsd_dashboard_menus_manage',
                'url' => $this->add_qs_var('mode', 'manage', $this->url),
                'icon' => $manage_icon !== '' ? $manage_icon : 'fas fa-tachometer-alt',
            ];
        }

        // Add Listing Menu
        if ((LSD_Capability::can('edit_listings', 'edit_posts') || $this->guest_status) && ($include_disabled || !isset($statuses['form']) || (int) $statuses['form'] === 1))
        {
            $form_label = $data['form']['label'] ?? '';
            $form_icon = $data['form']['icon'] ?? '';
            $menus['form'] = [
                'label' => $form_label !== '' ? $form_label : esc_html__('Add Listing', 'listdom'),
                'default_label' => esc_html__('Add Listing', 'listdom'),
                'id' => 'lsd_dashboard_menus_form',
                'url' => $this->add_qs_var('mode', 'form', $this->url),
                'icon' => $form_icon !== '' ? $form_icon : 'far fa-plus-square',
            ];
        }

        // Profile Menu
        if (get_current_user_id() && ($include_disabled || !isset($statuses['profile']) || (int) $statuses['profile'] === 1))
        {
            $profile_label = $data['profile']['label'] ?? '';
            $profile_icon = $data['profile']['icon'] ?? '';
            $menus['profile'] = [
                'label' => $profile_label !== '' ? $profile_label : esc_html__('Profile Setting', 'listdom'),
                'default_label' => esc_html__('Profile Setting', 'listdom'),
                'id' => 'lsd_dashboard_menus_profile',
                'url' => $this->add_qs_var('mode', 'profile', $this->url),
                'icon' => $profile_icon !== '' ? $profile_icon : 'fas fa-user',
            ];
        }

        // Logout Menu
        if (get_current_user_id() && ($include_disabled || !isset($statuses['logout']) || (int) $statuses['logout'] === 1))
        {
            $logout_label = $data['logout']['label'] ?? '';
            $logout_icon = $data['logout']['icon'] ?? '';
            $menus['logout'] = [
                'label' => $logout_label !== '' ? $logout_label : esc_html__('Logout', 'listdom'),
                'default_label' => esc_html__('Logout', 'listdom'),
                'id' => 'lsd_dashboard_menus_logout',
                'url' => wp_logout_url(),
                'icon' => $logout_icon !== '' ? $logout_icon : 'fas fa-sign-out-alt',
            ];
        }

        // Apply Filters
        $menus = apply_filters('lsd_dashboard_menus', $menus, $this);

        foreach ($menus as $key => $menu)
        {
            if (!$include_disabled && isset($statuses[$key]) && (int) $statuses[$key] === 0)
            {
                unset($menus[$key]);
                continue;
            }

            if (!isset($data[$key]) || !is_array($data[$key])) continue;

            $override_label = $data[$key]['label'] ?? '';
            $override_icon = $data[$key]['icon'] ?? '';

            if ($override_label !== '') $menus[$key]['label'] = $override_label;
            if ($override_icon !== '') $menus[$key]['icon'] = $override_icon;
        }

        // Order Menus
        if (isset($order_menus) && $this->is_pro)
        {
            $ordered_menus = [];
            foreach ($order_menus as $menu_id)
            {
                foreach ($menus as $key => $menu)
                {
                    if ($menu['id'] === $menu_id)
                    {
                        $ordered_menus[$key] = $menu;
                        unset($menus[$key]);
                        break;
                    }
                }
            }

            $ordered_menus = array_merge($ordered_menus, $menus);
            $menus = $ordered_menus;
        }

        return $menus;
    }

    public function get_default_mode(array $menus = []): string
    {
        if (!count($menus)) $menus = $this->menu_ids();

        foreach ($menus as $key => $menu)
        {
            if ($key === 'logout') continue;

            $url = $menu['url'] ?? '';
            if ($url !== '' && strpos($url, 'mode=') !== false) return $key;
        }

        return 'manage';
    }

    protected function get_form_link($listing_id = null): string
    {
        $url = $this->add_qs_var('mode', 'form', $this->url);

        // Edit Mode
        if ($listing_id) $url = $this->add_qs_var('id', $listing_id, $url);

        return $url;
    }

    public function save()
    {
        // Nonce is not set!
        if (!isset($_POST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing!', 'listdom')]);

        // Nonce is not valid!
        if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'lsd_dashboard')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $g_recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : null;
        if (!LSD_Main::grecaptcha_check($g_recaptcha_response)) $this->response(['success' => 0, 'message' => esc_html__("Google recaptcha is invalid.", 'listdom')]);

        // Registration Method
        $guest_registration = $this->settings['submission_guest_registration'] ?? 'approval';

        // Additional Validations
        $valid = apply_filters('lsd_dashboard_validate_request', true);
        if ($valid !== true) $this->response(['success' => 0, 'message' => $valid]);

        $id = isset($_POST['id']) ? (int) sanitize_text_field($_POST['id']) : 0;

        if ($id > 0)
        {
            $listing = get_post($id);

            if (!isset($listing->ID) || $listing->post_type !== LSD_Base::PTYPE_LISTING) $this->response(['success' => 0, 'message' => esc_html__('The listing is not available!', 'listdom')]);
            if (!current_user_can('edit_post', $id) || (get_current_user_id() !== (int) $listing->post_author && !current_user_can('edit_others_posts'))) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to edit this listing!', 'listdom')]);
        }

        $lsd = $_POST['lsd'] ?? [];
        $raw_lsd = $lsd;
        $social = $lsd['sc'] ?? []; // Social
        $tax = isset($_POST['tax_input']) && is_array($_POST['tax_input']) ? $_POST['tax_input'] : [];
        $listing_categories = isset($lsd['listing_categories']) && is_array($lsd['listing_categories'])
            ? array_values(array_filter(array_map('intval', $lsd['listing_categories'])))
            : [];
        $additional_categories = isset($lsd['additional_categories']) && is_array($lsd['additional_categories'])
            ? array_values(array_filter(array_map('intval', $lsd['additional_categories'])))
            : [];
        $lsd['listing_categories'] = array_values(array_unique(array_merge($listing_categories, $additional_categories)));

        $consent = isset($lsd['privacy_consent']) ? sanitize_text_field($lsd['privacy_consent']) : '';
        $consent_enabled = LSD_Privacy::is_consent_enabled('dashboard');
        if ($consent_enabled && $consent !== '1') $this->response(['success' => 0, 'message' => LSD_Privacy::consent_required_text()]);
        if (!$consent_enabled && isset($lsd['privacy_consent'])) unset($lsd['privacy_consent']);

        $post_title = isset($lsd['title']) ? sanitize_text_field($lsd['title']) : '';
        $post_content = $lsd['content'] ?? '';
        $post_excerpt = $lsd['excerpt'] ?? '';

        if (!trim($post_title)) $this->response(['success' => 0, 'message' => esc_html__('Please fill listing title field!', 'listdom')]);

        $guest_email = $lsd['guest_email'] ?? '';
        if (!get_current_user_id() && !trim($guest_email)) $this->response(['success' => 0, 'message' => esc_html__('Please insert your email!', 'listdom')]);

        // Password Validation
        $guest_password = $lsd['guest_password'] ?? '';
        if (!get_current_user_id() && $guest_registration === 'submission' && strlen($guest_password) < 8) $this->response(['success' => 0, 'message' => esc_html__('Please insert your password! It should be at-least 8 characters.', 'listdom')]);

        // Gallery
        if (isset($lsd['_gallery']) && is_array($lsd['_gallery'])) $lsd['gallery'] = $lsd['_gallery'];

        // Embeds
        if (isset($lsd['_embeds']) && is_array($lsd['_embeds']))
        {
            $lsd['embeds'] = $lsd['_embeds'];
            unset($lsd['_embeds']);
        }

        // FAQs
        if (isset($lsd['_faqs']) && is_array($lsd['_faqs']))
        {
            $lsd['faqs'] = $lsd['_faqs'];
            unset($lsd['_faqs']);
        }

        // Required Fields
        $fields = (new LSD_Dashboard())->fields();

        // Validate
        $errors = [
            'list' => [],
            'fields' => [],
        ];
        foreach ($fields as $f => $field)
        {
            // Not Required
            if (!$this->is_required($f)) continue;

            // Field module
            $module = $field['module'] ?? null;

            // Related module is not enabled
            if ($module && !$this->is_enabled($module)) continue;

            // Needed Capability
            $capability = $field['capability'] ?? null;

            // Current user is not permitted
            if ($capability && !$this->guest_status && !LSD_Capability::can($capability)) continue;

            $value = null;
            if (isset($lsd[$f])) $value = $lsd[$f];
            else if (isset($tax[$f])) $value = $tax[$f];
            else if (isset($social[$f])) $value = $social[$f];
            else if (isset($_POST[$f])) $value = $_POST[$f];
            if ($f === 'ava')
            {
                $valid = true;
                if (!is_array($value)) $valid = false;
                else
                {
                    foreach (LSD_Main::get_weekdays() as $weekday)
                    {
                        $daycode = $weekday['code'];
                        $day = $value[$daycode] ?? [];
                        $hours = isset($day['hours']) ? trim((string) $day['hours']) : '';
                        $off = isset($day['off']) && $day['off'];
                        if ($hours === '' && !$off)
                        {
                            $valid = false;
                            break;
                        }
                    }
                }

                if (!$valid) $errors['list'][] = esc_html__('Please set work hours or mark off for all days!', 'listdom');
                continue;
            }

            if (is_array($value) && !count($value)) $errors['list'][] = sprintf(
                /* translators: %s: Field label. */
                esc_html__('At-least one value for %s field is required!', 'listdom'),
                strtolower($field['label'])
            );
            else if (!is_array($value) && trim((string) $value) === '') $errors['list'][] = sprintf(
                /* translators: %s: Field label. */
                esc_html__('%s field is required!', 'listdom'),
                $field['label']
            );
        }

        // Restrictions
        $restrictions = $this->get_restriction_rules();

        $max_upload_size_kb = !empty($restrictions['max_upload_size'])
            ? (int) $restrictions['max_upload_size']
            : 0;

        // Maximum Gallery Images
        if (trim($restrictions['max_gallery_images']) != '' && isset($lsd['gallery']) && is_array($lsd['gallery']) && count($lsd['gallery']) > $restrictions['max_gallery_images'])
        {
            $errors['list'][] = sprintf(
                /* translators: 1: Allowed number of gallery images, 2: Number of uploaded images. */
                esc_html__("You can only upload a maximum of %1\$s gallery images. You've uploaded %2\$s.", 'listdom'),
                $restrictions['max_gallery_images'],
                count($lsd['gallery'])
            );
        }

        // Maximum Image Size
        if ($max_upload_size_kb && isset($lsd['gallery']) && is_array($lsd['gallery']))
        {
            // Convert the maximum image size from KB to bytes
            $max_image_size_bytes = $max_upload_size_kb * 1024;

            foreach ($lsd['gallery'] as $image_id)
            {
                $image_path = get_attached_file($image_id);
                if (file_exists($image_path))
                {
                    $file_size = filesize($image_path);

                    // Get the image name
                    $image_name = get_post_meta($image_id, '_wp_attached_file', true);
                    $image_name = basename($image_name); // Get the file name from the path

                    // Check if the file size exceeds the maximum allowed size
                    if ($file_size > $max_image_size_bytes)
                    {
                        $errors['list'][] = sprintf(
                            /* translators: 1: Image file name, 2: Maximum allowed size in KB, 3: Current size in KB. */
                            esc_html__("The image '%1\$s' exceeds the maximum allowed size of %2\$s KB. The image size is %3\$s KB.", 'listdom'),
                            esc_html($image_name),
                            $max_upload_size_kb,
                            round($file_size / 1024) // Convert bytes to KB
                        );
                    }
                }
            }
        }

        // Featured image restriction
        if ($max_upload_size_kb && $this->is_enabled('image'))
        {
            $featured_image_id = isset($lsd['featured_image']) ? (int) $lsd['featured_image'] : 0;
            if ($featured_image_id)
            {
                $image_path = get_attached_file($featured_image_id);
                if ($image_path && file_exists($image_path))
                {
                    $file_size = filesize($image_path);

                    if ($file_size > $max_upload_size_kb * 1024)
                    {
                        $image_name = basename($image_path);
                        $message = sprintf(
                            /* translators: 1: Image file name, 2: Maximum allowed size in KB, 3: Current size in KB. */
                            esc_html__("The featured image '%1\$s' exceeds the maximum allowed size of %2\$s KB. The image size is %3\$s KB.", 'listdom'),
                            esc_html($image_name),
                            $max_upload_size_kb,
                            round($file_size / 1024)
                        );

                        $errors['list'][] = $message;
                        $errors['fields']['featured_image'] = $message;
                    }
                }
            }
        }

        // Custom attribute image restrictions
        if ($max_upload_size_kb && isset($lsd['attributes']) && is_array($lsd['attributes']))
        {
            $attribute_details = [];
            foreach (LSD_Main::get_attributes_details() as $attribute_detail)
            {
                if (($attribute_detail['field_type'] ?? '') !== 'image') continue;

                $slug = $attribute_detail['slug'] ?? '';
                if (!$slug) continue;

                $attribute_details[$slug] = $attribute_detail;
            }

            if (!empty($attribute_details))
            {
                foreach ($lsd['attributes'] as $attribute_slug => $attribute_value)
                {
                    if (!isset($attribute_details[$attribute_slug])) continue;

                    $image_id = (int) $attribute_value;
                    if (!$image_id) continue;

                    $image_path = get_attached_file($image_id);
                    if (!$image_path || !file_exists($image_path)) continue;

                    $file_size = filesize($image_path);
                    if ($file_size <= $max_upload_size_kb * 1024) continue;

                    $attribute_name = $attribute_details[$attribute_slug]['name'] ?? '';
                    $attribute_label = trim((string) $attribute_name) !== ''
                        ? sanitize_text_field($attribute_name)
                        : $attribute_slug;

                    $image_name = basename($image_path);
                    $message = sprintf(
                        /* translators: 1: Custom field label, 2: Image file name, 3: Maximum allowed size in KB, 4: Current size in KB. */
                        esc_html__('The image selected for "%1$s" (%2$s) exceeds the maximum allowed size of %3$s KB. The image size is %4$s KB.', 'listdom'),
                        esc_html($attribute_label),
                        esc_html($image_name),
                        $max_upload_size_kb,
                        round($file_size / 1024)
                    );

                    $errors['list'][] = $message;
                    if (!isset($errors['fields']['attribute_images'])) $errors['fields']['attribute_images'] = [];
                    $errors['fields']['attribute_images'][$attribute_slug] = $message;
                }
            }
        }

        // Custom attribute file restrictions
        if (isset($lsd['attributes']) && is_array($lsd['attributes']))
        {
            $attribute_details = [];
            foreach (LSD_Main::get_attributes_details() as $attribute_detail)
            {
                if (($attribute_detail['field_type'] ?? '') !== 'file') continue;

                $slug = $attribute_detail['slug'] ?? '';
                if (!$slug) continue;

                $attribute_details[$slug] = $attribute_detail;
            }

            if (!empty($attribute_details))
            {
                foreach ($lsd['attributes'] as $attribute_slug => $attribute_value)
                {
                    if (!isset($attribute_details[$attribute_slug])) continue;

                    $attribute_name = $attribute_details[$attribute_slug]['name'] ?? '';
                    $attribute_label = trim((string) $attribute_name) !== ''
                        ? sanitize_text_field($attribute_name)
                        : $attribute_slug;

                    $term_id = isset($attribute_details[$attribute_slug]['id']) ? (int) $attribute_details[$attribute_slug]['id'] : 0;
                    $validation = LSD_Entity_Listing::validate_file_attribute_value($attribute_value, $term_id, $attribute_label, true);

                    if ($validation['valid'])
                    {
                        $lsd['attributes'][$attribute_slug] = $validation['value'] ?? '';
                        continue;
                    }

                    $message = $validation['message'] ?? '';
                    if (!$message) continue;

                    $errors['list'][] = $message;
                    if (!isset($errors['fields']['attribute_files'])) $errors['fields']['attribute_files'] = [];
                    $errors['fields']['attribute_files'][$attribute_slug] = $message;
                }
            }
        }

        // Description Length
        if (trim($restrictions['description_length']) != '' && strlen(trim(wp_strip_all_tags($post_content))) > $restrictions['description_length'])
        {
            $errors['list'][] = sprintf(
                /* translators: 1: Allowed character count, 2: Current character count. */
                esc_html__("The maximum length for listing content is %1\$s characters. You've written %2\$s characters.", 'listdom'),
                $restrictions['description_length'],
                strlen(trim(wp_strip_all_tags($post_content)))
            );
        }

        $tags = isset($_POST['tags']) ?
            (is_array($_POST['tags']) ? $_POST['tags'] : explode(',', $_POST['tags']))
            : [];

        // Maximum Tags
        if (trim($restrictions['max_tags']) != '' && $this->is_enabled('tags') && count($tags) > $restrictions['max_tags'])
        {
            $errors['list'][] = sprintf(
                /* translators: 1: Maximum allowed tags, 2: Current tag count. */
                esc_html__("The maximum allowed tags is %1\$s tags. You've added %2\$s tags.", 'listdom'),
                $restrictions['max_tags'],
                count(explode(',', $_POST['tags']))
            );
        }

        // There are some Errors
        if (count($errors['list']))
        {
            $response = [
                'success' => 0,
                'message' => '<ul><li>' . implode('</li><li>', $errors['list']) . '</li></ul>',
            ];

            if (!empty($errors['fields'])) $response['data'] = $errors['fields'];

            $this->response($response);
        }

        // Trigger Event
        do_action('lsd_dashboard_validation', $lsd, $id);

        // Post Status
        $status = 'pending';
        if (current_user_can('publish_posts')) $status = 'publish';

        // Forced Status by Settings
        $forced_listing_status = $this->settings['dashboard_listing_status'] ?? '';
        $apply_forced_status_to_guest = !empty($this->settings['dashboard_listing_status_guest']);
        $is_guest_submission = !get_current_user_id();
        if (
            $id <= 0
            && in_array($forced_listing_status, ['publish', 'pending'], true)
            && (!$is_guest_submission || $apply_forced_status_to_guest)
        ) $status = $forced_listing_status;

        // Filter Listing Status
        $status = apply_filters('lsd_default_listing_status', $status, $lsd);
        $status = apply_filters('lsd_dashboard_listing_status', $status, $lsd);

        // Status by Request
        if (
            (!$forced_listing_status || $id > 0)
            && current_user_can('publish_posts')
            && isset($lsd['listing_status'])
            && trim($lsd['listing_status'])
        )
            $status = $lsd['listing_status'];

        // Create New Listing
        if ($id <= 0)
        {
            $id = wp_insert_post([
                'post_title' => $post_title,
                'post_content' => $post_content,
                'post_excerpt' => $post_excerpt,
                'post_type' => LSD_Base::PTYPE_LISTING,
                'post_status' => $status,
            ]);
        }

        // Update Listing
        wp_update_post([
            'ID' => $id,
            'post_title' => $post_title,
            'post_name' => sanitize_title($post_title),
            'post_content' => $post_content,
            'post_excerpt' => $post_excerpt,
            'post_status' => $status,
        ]);

        // Tags
        if ($this->is_enabled('tags'))
        {
            $tags = isset($_POST['tags']) && is_array($_POST['tags'])
                ? LSD_Taxonomies::name($_POST['tags'], LSD_Base::TAX_TAG)
                : (isset($_POST['tags']) ? sanitize_text_field($_POST['tags']) : '');

            wp_set_post_terms($id, $tags, LSD_Base::TAX_TAG);
        }

        // Locations
        if ($this->is_enabled('locations'))
        {
            $locations = isset($tax[LSD_Base::TAX_LOCATION]) && is_array($tax[LSD_Base::TAX_LOCATION]) ? $tax[LSD_Base::TAX_LOCATION] : [];
            wp_set_post_terms($id, $locations, LSD_Base::TAX_LOCATION);
        }

        // Features
        if ($this->is_enabled('features'))
        {
            $features = isset($tax[LSD_Base::TAX_FEATURE]) && is_array($tax[LSD_Base::TAX_FEATURE]) ? $tax[LSD_Base::TAX_FEATURE] : [];
            wp_set_post_terms($id, LSD_Taxonomies::name($features, LSD_Base::TAX_FEATURE), LSD_Base::TAX_FEATURE);
        }

        // Labels
        if ($this->is_enabled('labels'))
        {
            $labels = isset($tax[LSD_Base::TAX_LABEL]) && is_array($tax[LSD_Base::TAX_LABEL]) ? $tax[LSD_Base::TAX_LABEL] : [];
            wp_set_post_terms($id, LSD_Taxonomies::name($labels, LSD_Base::TAX_LABEL), LSD_Base::TAX_LABEL);
        }

        // Normalize Category Selections
        $primary_category_id = isset($lsd['listing_category']) ? (int) $lsd['listing_category'] : 0;
        $lsd['listing_categories'] = $primary_category_id
            ? array_values(array_unique(array_merge([$primary_category_id], $lsd['listing_categories'])))
            : array_values(array_unique($lsd['listing_categories']));

        // Featured Image
        if ($this->is_enabled('image'))
        {
            $featured_image = isset($lsd['featured_image']) ? sanitize_text_field($lsd['featured_image']) : '';

            // Save Featured Image
            if ($featured_image) set_post_thumbnail($id, $featured_image);
            // Delete Featured Image
            else if (has_post_thumbnail($id)) delete_post_thumbnail($id);
        }

        // Publish Listing
        if ($status === 'publish' && get_post_status($id) != 'published') wp_publish_post($id);

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');
        $lsd = LSD_Entity_Listing::restore_rich_attribute_values($lsd, $raw_lsd);
        $lsd['_lsd_submission'] = 1;

        // Save the Data
        $entity = new LSD_Entity_Listing($id);
        $entity->save($lsd);

        // Assign Author for approved guest listings
        $guest_registration = $this->settings['submission_guest_registration'] ?? 'approval';
        if ($status === 'publish' && $is_guest_submission && $guest_registration === 'approval')
        {
            $guest_email = get_post_meta($id, 'lsd_guest_email', true);
            if (is_email($guest_email))
            {
                $owner_id = (int) get_post_field('post_author', $id);
                if (!$owner_id)
                {
                    $fullname = (string) get_post_meta($id, 'lsd_guest_fullname', true);
                    LSD_User::listing($id, $guest_email, '', $fullname);
                }
            }
        }

        if ($status == 'publish') $message = sprintf(
            /* translators: %s: Link to view the published listing. */
            esc_html__('The listing has been published. %s', 'listdom'),
            '<a href="' . get_permalink($id) . '" target="_blank">' . esc_html__('View Listing', 'listdom') . '</a>'
        );
        else $message = esc_html__('The listing has been submitted. It will be reviewed as soon as possible.', 'listdom');

        // Trigger Event
        do_action('lsd_dashboard_save', $id, $lsd);

        // Response
        $this->response(['success' => 1, 'message' => $message, 'data' => ['id' => $id]]);
    }

    public function save_profile()
    {
        // Nonce is not set!
        if (!isset($_POST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing!', 'listdom')]);

        // Nonce is not valid!
        if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'lsd_dashboard_profile')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $user_id = get_current_user_id();
        if (!$user_id) $this->response(['success' => 0, 'message' => esc_html__('User not found!', 'listdom')]);

        if (!current_user_can('edit_user', $user_id)) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to update this profile.', 'listdom')]);

        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        $profile_fields = LSD_User::profile_edit_fields();
        $current_user = get_userdata($user_id);
        if (!($current_user instanceof WP_User)) $this->response(['success' => 0, 'message' => esc_html__('User not found!', 'listdom')]);

        // Save the Data
        $user_data = [
            'ID' => $user_id,
            'first_name' => $lsd['first_name'] ?? '',
            'last_name' => $lsd['last_name'] ?? '',
            'description' => !empty($profile_fields['bio']) ? ($lsd['bio'] ?? '') : $current_user->description,
            'user_email' => !empty($profile_fields['email']) ? ($lsd['email'] ?? '') : $current_user->user_email,
        ];

        // Update core user fields
        wp_update_user($user_data);

        $meta_keys = [];

        if (!empty($profile_fields['profile_image'])) $meta_keys['lsd_profile_image'] = 'profile_image';
        if (!empty($profile_fields['hero_image'])) $meta_keys['lsd_hero_image'] = 'hero_image';
        if (!empty($profile_fields['job_title'])) $meta_keys['lsd_job_title'] = 'job_title';
        if (!empty($profile_fields['phone'])) $meta_keys['lsd_phone'] = 'phone';
        if (!empty($profile_fields['mobile'])) $meta_keys['lsd_mobile'] = 'mobile';
        if (!empty($profile_fields['website'])) $meta_keys['lsd_website'] = 'website';
        if (!empty($profile_fields['fax'])) $meta_keys['lsd_fax'] = 'fax';

        $social_fields = isset($profile_fields['social']) && is_array($profile_fields['social']) ? $profile_fields['social'] : [];

        foreach ($social_fields as $network => $enabled)
        {
            if ((int) $enabled !== 1) continue;

            $network = sanitize_key($network);
            if ($network === '') continue;

            $field_name = $network;
            if ($network === 'tiktok' && !isset($lsd[$field_name]) && isset($lsd['Tiktok'])) $field_name = 'Tiktok';

            $meta_keys['lsd_' . $network] = $field_name;
        }

        // Update user meta
        foreach ($meta_keys as $meta_key => $field_name)
        {
            if (isset($lsd[$field_name]))
            {
                update_user_meta($user_id, $meta_key, $lsd[$field_name]);
            }
        }

        // Password Validation and Update
        if (!empty($lsd['password']) && !empty($lsd['confirm_password']))
        {
            if (strlen($lsd['password']) < 6)
            {
                $this->response(['success' => 0, 'message' => esc_html__('Password must be at least 6 characters long!', 'listdom')]);
            }

            if ($lsd['password'] !== $lsd['confirm_password'])
            {
                $this->response(['success' => 0, 'message' => esc_html__('Passwords do not match!', 'listdom')]);
            }

            // Update Password
            wp_set_password($lsd['password'], $user_id);

            // Success Message
            echo wp_json_encode(['success' => 1, 'message' => esc_html__('Password updated successfully! Please log in again.', 'listdom')], JSON_NUMERIC_CHECK);

            // Force the user to re-login
            wp_destroy_current_session();
            wp_clear_auth_cookie();
            wp_set_current_user(0);

            exit;
        }

        // Success response
        $this->response(['success' => 1, 'message' => esc_html__('The Profile has been updated.', 'listdom')]);
    }

    public function get_restriction_rules(): array
    {
        return apply_filters('lsd_dashboard_restriction_rules', [
            'max_gallery_images' => $this->settings['submission_max_gallery_images'] ?? '',
            'max_upload_size' => $this->settings['submission_max_image_upload_size'] ?? '',
            'description_length' => $this->settings['submission_max_description_length'] ?? '',
            'max_tags' => $this->settings['submission_max_tags_count'] ?? '',
        ]);
    }

    public function upload()
    {
        // User is not allowed to upload files
        if (!$this->guest_status && !LSD_Capability::can('upload_files')) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to upload files!', 'listdom')]);

        // Nonce is not set!
        if (!isset($_POST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing!', 'listdom')]);

        // Nonce is not valid!
        if (
            !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'lsd_dashboard') &&
            !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'lsd_dashboard_profile')
        ) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        // Include the function
        if (!function_exists('wp_handle_upload')) require_once ABSPATH . 'wp-admin/includes/file.php';

        $image = $_FILES['file'] ?? null;

        // No file
        if (!$image) $this->response(['success' => 0, 'message' => esc_html__('Please upload an image!', 'listdom')]);

        $allowed = ['jpeg', 'jpg', 'png', 'webp'];

        $ex = explode('.', $image['name']);
        $extension = end($ex);

        // Invalid Extension
        if (!in_array(strtolower($extension), $allowed)) $this->response(['success' => 0, 'message' => esc_html__('Only JPG, PNG, and WebP images are allowed!', 'listdom')]);

        // Get restrictions
        $restrictions = $this->get_restriction_rules();
        $max_size = !empty($restrictions['max_upload_size']) ? $restrictions['max_upload_size'] * 1024 : 0; // Max size in bytes

        // Check file size against restriction
        if ($max_size > 0 && $image['size'] > $max_size)
        {
            $this->response([
                'success' => 0,
                'message' => sprintf(
                    /* translators: %s: Maximum allowed image size in kilobytes. */
                    esc_html__('The uploaded image exceeds the maximum allowed size of %s KB.', 'listdom'),
                    $restrictions['max_upload_size']
                ),
            ]);
        }

        $uploaded = wp_handle_upload($image, ['test_form' => false]);

        $success = 0;
        $data = [];

        if ($uploaded && !isset($uploaded['error']))
        {
            $success = 1;
            $message = esc_html__('The image is uploaded!', 'listdom');

            $attachment = [
                'post_mime_type' => $uploaded['type'],
                'post_title' => '',
                'post_content' => '',
                'post_status' => 'inherit',
            ];

            // Add as Attachment
            $attachment_id = wp_insert_attachment($attachment, $uploaded['file']);

            // Update Metadata
            require_once ABSPATH . 'wp-admin/includes/image.php';
            wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $uploaded['file']));

            $data['attachment_id'] = $attachment_id;
            $data['url'] = $uploaded['url'];
        }
        else
        {
            $message = $uploaded['error'];
        }

        $this->response(['success' => $success, 'message' => $message, 'data' => $data]);
    }

    public function delete()
    {
        // Nonce is not set!
        if (!isset($_POST['_lsdnonce'])) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing!', 'listdom')]);

        // Nonce is not valid!
        if (!wp_verify_nonce(sanitize_text_field($_POST['_lsdnonce']), 'lsd_dashboard')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $id = isset($_POST['id']) ? (int) sanitize_text_field($_POST['id']) : 0;
        $listing = get_post($id);

        // Listing not Found!
        if (!isset($listing->ID)) $this->response(['success' => 0]);

        // Current User Cannot Remove Listing of Others
        if ($listing->post_author != get_current_user_id() && !current_user_can('delete_others_posts')) $this->response(['success' => 0]);

        // Delete The Post
        wp_delete_post($id);

        // Response
        $this->response(['success' => 1]);
    }

    public function gallery()
    {
        // User is not allowed to upload files
        if (!$this->guest_status && !LSD_Capability::can('upload_files')) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to upload files!', 'listdom')]);

        // Nonce is not set!
        if (!isset($_POST['_wpnonce'])) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is missing!', 'listdom')]);

        // Nonce is not valid!
        if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'lsd_dashboard')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        // Include the function
        if (!function_exists('wp_handle_upload')) require_once ABSPATH . 'wp-admin/includes/file.php';

        $images = isset($_FILES['files']) && is_array($_FILES['files']) ? $_FILES['files'] : [];
        $count = isset($images['name']) && is_array($images['name']) ? count($images['name']) : 0;

        // No images
        if (!$count) $this->response(['success' => 0, 'message' => esc_html__('Please upload an image!', 'listdom')]);

        // Get restrictions
        $restrictions = $this->get_restriction_rules();
        $max_gallery_images = isset($restrictions['max_gallery_images']) ? (int) $restrictions['max_gallery_images'] : 0;

        $existing_ids = isset($_POST['existing_ids']) ? array_map('intval', (array) wp_unslash($_POST['existing_ids'])) : [];
        $existing_ids = array_filter($existing_ids);
        $existing_count = count($existing_ids);

        // Allowed Extensions
        $allowed = ['jpeg', 'jpg', 'png', 'webp'];

        // Check for maximum upload size if set in restrictions (size in KB converted to bytes)
        $max_size = !empty($restrictions['max_upload_size']) ? $restrictions['max_upload_size'] * 1024 : 0;

        $data = [];
        $errors = [];

        if ($max_gallery_images > 0)
        {
            $available_slots = $max_gallery_images - $existing_count;
            if ($available_slots <= 0)
            {
                $this->response([
                    'success' => 0,
                    'message' => sprintf(
                        /* translators: %s: Maximum number of gallery images allowed. */
                        esc_html__('You can upload up to %s gallery images.', 'listdom'),
                        $max_gallery_images
                    ),
                    'data' => [],
                ]);
            }

            if ($count > $available_slots)
            {
                $errors[] = sprintf(
                    /* translators: %s: Number of images that can still be uploaded. */
                    esc_html__('You can only upload %s more image(s).', 'listdom'),
                    max(0, $available_slots)
                );

                $count = $available_slots;
            }
        }

        for ($i = 0; $i < $count; $i++)
        {
            $image = [
                'name' => $images['name'][$i],
                'type' => $images['type'][$i],
                'tmp_name' => $images['tmp_name'][$i],
                'error' => $images['error'][$i],
                'size' => $images['size'][$i],
            ];

            $image_name = esc_html($image['name']);

            // Check file size against the restriction
            if ($max_size > 0 && $image['size'] > $max_size)
            {
                $errors[] = sprintf(
                    /* translators: 1: Image file name, 2: Maximum allowed size in KB, 3: Current size in KB. */
                    esc_html__("The image '%1\$s' exceeds the maximum allowed size of %2\$s KB. The image size is %3\$s KB.", 'listdom'),
                    $image_name,
                    $restrictions['max_upload_size'],
                    round($image['size'] / 1024)
                );
                continue;
            }

            $ex = explode('.', $image['name']);
            $extension = end($ex);

            // Invalid Extension
            if (!in_array(strtolower($extension), $allowed))
            {
                $errors[] = sprintf(
                    /* translators: %s: Image file name. */
                    esc_html__("The image '%s' has an invalid extension. Only JPG, PNG, and WebP files are allowed.", 'listdom'),
                    $image_name
                );
                continue;
            }

            $uploaded = wp_handle_upload($image, ['test_form' => false]);
            if ($uploaded && !isset($uploaded['error']))
            {
                $attachment = [
                    'post_mime_type' => $uploaded['type'],
                    'post_title' => '',
                    'post_content' => '',
                    'post_status' => 'inherit',
                ];

                // Add as Attachment
                $attachment_id = wp_insert_attachment($attachment, $uploaded['file']);

                // Update Metadata
                require_once ABSPATH . 'wp-admin/includes/image.php';
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $uploaded['file']));

                $data[] = [
                    'id' => $attachment_id,
                    'url' => $uploaded['url'],
                ];
            }
        }

        // If there are any errors, append them to the message and set success to 0
        $message = count($errors) ? implode('<br>', $errors) : esc_html__('The images are uploaded!', 'listdom');
        $success = count($errors) ? 0 : 1;

        $this->response(['success' => $success, 'message' => $message, 'data' => $data]);
    }

    public function auth(): string
    {
        // Dashboard
        ob_start();
        include lsd_template('dashboard/auth.php');
        return ob_get_clean();
    }

    public function listing_counts(): array
    {
        global $wp_post_statuses;
        $counts = wp_count_posts(LSD_Base::PTYPE_LISTING, 'readable');

        $valid = [];
        foreach ($counts as $s => $count)
        {
            if (!$count || !isset($wp_post_statuses[$s])) continue;
            if (!isset($wp_post_statuses[$s]->show_in_admin_status_list) || !$wp_post_statuses[$s]->show_in_admin_status_list) continue;

            $valid[$s] = $count;
        }

        return $valid;
    }

    public function ajax_refresh_additional_categories()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'lsd_dashboard')) $this->response(['success' => 0 ,'message' => esc_html__('Security nonce is not valid!', 'listdom')]);

        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        $primary_id = isset($_POST['primary_id']) ? (int) $_POST['primary_id'] : 0;

        $selected = isset($_POST['selected']) && is_array($_POST['selected']) ? array_map('intval', $_POST['selected']) : [];
        $selected = array_values(array_filter(array_unique($selected)));

        $settings = LSD_Options::settings();
        $children_only = !empty($settings['submission_category_children_only']);

        if (empty($selected) && $post_id)
        {
            $selected = wp_get_post_terms($post_id, LSD_Base::TAX_CATEGORY, ['fields' => 'ids']);
            if (is_wp_error($selected)) $selected = [];
        }

        if ($children_only && !$primary_id) $this->response(['success' => 0, 'html' => '<p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">' . esc_html__('Please select a primary category first.', 'listdom') . '</p>']);
        if ($children_only && $primary_id)
        {
            $selected = array_values(array_filter($selected, function ($cat_id) use ($primary_id) {
                $cat_id = (int) $cat_id;
                if (!$cat_id || $cat_id === $primary_id) return false;

                $ancestors = get_ancestors($cat_id, LSD_Base::TAX_CATEGORY);
                return in_array($primary_id, $ancestors, true);
            }));
        }

        $parent = $children_only ? $primary_id : 0;
        $html = LSD_Dashboard_Terms::category([
            'taxonomy' => LSD_Base::TAX_CATEGORY,
            'key' => 'additional_' . LSD_Base::TAX_CATEGORY,
            'parent' => $parent,
            'level' => 0,
            'hide_empty' => 0,
            'orderby' => 'name',
            'order' => 'ASC',
            'name' => 'lsd[additional_categories]',
            'pre' => '',
            'post_id' => $post_id,
            'selected' => $selected,
        ]);

        $this->response(['success' => 1,'html' => $html]);
    }
}
