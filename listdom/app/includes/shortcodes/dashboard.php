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

    /**
     * @var WP_Query
     */
    public $q;

    private $is_pro;

    public function __construct()
    {
        // Settings
        $this->settings = LSD_Options::settings();

        // Listdom Pro?
        $this->is_pro = $this->isPro();

        // Guest Status
        $this->guest_status = $this->is_pro && isset($this->settings['submission_guest']) && $this->settings['submission_guest'];

        // Registration Method
        $this->guest_registration = $this->is_pro && isset($this->settings['submission_guest_registration'])
            ? $this->settings['submission_guest_registration']
            : 'approval';
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
        $this->mode = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'manage';

        // Listdom Bar
        $bar = LSD_Bar::instance();

        if ($this->mode === 'form')
        {
            $id = isset($_GET['id']) ? (int) sanitize_text_field($_GET['id']) : 0;

            if ($id > 0) $bar->add($id);
            else $bar->menu(
                admin_url('post-new.php?post_type=' . LSD_Base::PTYPE_LISTING),
                __('Add Listing', 'listdom')
            );
        }

        // Payload
        LSD_Payload::set('dashboard', $this);

        // Dashboard Settings in Listdom Bar
        $bar->menu(
            admin_url('admin.php?page=listdom-settings&tab=frontend-dashboard'),
            __('Dashboard Settings', 'listdom')
        );

        // Dashboard
        if ($this->mode === 'manage') return $this->manage();
        // Form
        else if ($this->mode === 'form') return $this->form();
        // Profile
        else if ($this->mode === 'profile') return $this->profile();
        // Other Modes
        else return apply_filters('lsd_dashboard_modes', $this->alert(esc_html__('Not found!', 'listdom'), 'error'), $this);
    }

    public function manage()
    {
        if (!get_current_user_id())
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
        if (!get_current_user_id() && !$this->guest_status)
        {
            return $this->auth();
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
        if (!get_current_user_id())
        {
            return $this->auth();
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
        if (!get_current_user_id() && !$this->guest_status && isset($this->settings['submission_guest_redirect']) && $this->settings['submission_guest_redirect'] && is_page())
        {
            // Dashboard Page
            $dashboard_page = LSD_Options::post_id('submission_page');

            // Add Listing
            $add_listing_page = LSD_Options::post_id('add_listing_page');

            // Page
            $page = get_post();

            if (($dashboard_page || $add_listing_page) && in_array($page->ID, [$dashboard_page, $add_listing_page]))
            {
                $url = wp_login_url();

                wp_redirect($url);
                exit;
            }
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
        $current = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'manage';

        $output = '<ul class="lsd-dashboard-menus">';
        foreach ($menus as $key => $menu)
        {
            $target = $menu['target'] ?? '_self';
            $icon = $menu['icon'] ?? 'fas fa-tachometer-alt';
            $id = $menu['id'] ?? 'lsd_dashboard_menus_' . $key;

            $output .= '<li id="' . esc_attr($id) . '" ' . ($current == $key ? 'class="lsd-active"' : '') . '><a href="' . esc_url($menu['url']) . '" target="' . esc_attr($target) . '"><i class="lsd-icon ' . esc_attr($icon) . '"></i>' . esc_html($menu['label']) . '</a></li>';
        }

        $output .= '</ul>';
        return $output;
    }

    public function menu_ids(): array
    {
        // Order Menus
        $order_menus = $this->settings['dashboard_menus'] ?? [];

        // Default Menus
        $menus = [
            'manage' => ['label' => esc_html__('Dashboard', 'listdom'), 'id' => 'lsd_dashboard_menus_manage', 'url' => $this->url, 'icon' => 'fas fa-tachometer-alt'],
        ];

        // Add Listing Menu
        if (LSD_Capability::can('edit_listings', 'edit_posts') || $this->guest_status) $menus['form'] = ['label' => esc_html__('Add Listing', 'listdom'), 'id' => 'lsd_dashboard_menus_form', 'url' => $this->add_qs_var('mode', 'form', $this->url), 'icon' => 'far fa-plus-square'];

        // Profile Menu
        if (get_current_user_id()) $menus['profile'] = ['label' => esc_html__('Profile Setting', 'listdom'), 'id' => 'lsd_dashboard_menus_profile', 'url' => $this->add_qs_var('mode', 'profile', $this->url), 'icon' => 'fas fa-user'];

        // Logout Menu
        if (get_current_user_id()) $menus['logout'] = ['label' => esc_html__('Logout', 'listdom'), 'id' => 'lsd_dashboard_menus_logout', 'url' => wp_logout_url(), 'icon' => 'fas fa-sign-out-alt'];

        // Apply Filters
        $menus = apply_filters('lsd_dashboard_menus', $menus, $this);

        // Order Menus
        if (isset($order_menus) && $this->is_pro)
        {
            $ordered_menus = [];
            if (isset($menus['manage'])) $ordered_menus['manage'] = $menus['manage'];
            unset($menus['manage']);

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
            if (isset($menus['logout'])) $ordered_menus['logout'] = $menus['logout'];

            $menus = $ordered_menus;
        }

        return $menus;
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
        $lsd = $_POST['lsd'] ?? [];
        $social = $lsd['sc'] ?? []; // Social
        $tax = isset($_POST['tax_input']) && is_array($_POST['tax_input']) ? $_POST['tax_input'] : [];

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

        // Required Fields
        $fields = (new LSD_Dashboard())->fields();

        // Validate
        $errors = [];
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
                        $off = isset($day['off']) ? (bool) $day['off'] : false;
                        if ($hours === '' && !$off)
                        {
                            $valid = false;
                            break;
                        }
                    }
                }

                if (!$valid) $errors[] = esc_html__('Please set work hours or mark off for all days!', 'listdom');
                continue;
            }

            if (is_array($value) && !count($value)) $errors[] = sprintf(esc_html__('At-least one value for %s field is required!', 'listdom'), strtolower($field['label']));
            else if (!is_array($value) && trim((string) $value) === '') $errors[] = sprintf(esc_html__('%s field is required!', 'listdom'), $field['label']);
        }

        // Restrictions
        $restrictions = $this->get_restriction_rules();

        // Maximum Gallery Images
        if (trim($restrictions['max_gallery_images']) != '' && isset($lsd['gallery']) && is_array($lsd['gallery']) && count($lsd['gallery']) > $restrictions['max_gallery_images'])
        {
            $errors[] = sprintf(esc_html__("You can only upload a maximum of %s gallery images. You've uploaded %s.", 'listdom'), $restrictions['max_gallery_images'], count($lsd['gallery']));
        }

        // Maximum Image Size
        if (!empty($restrictions['max_upload_size']) && isset($lsd['gallery']) && is_array($lsd['gallery']))
        {
            // Convert the maximum image size from KB to bytes
            $max_image_size_bytes = $restrictions['max_upload_size'] * 1024;

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
                        $errors[] = sprintf(
                            esc_html__("The image '%s' exceeds the maximum allowed size of %s KB. The image size is %s KB.", 'listdom'),
                            esc_html($image_name),
                            $restrictions['max_upload_size'],
                            round($file_size / 1024) // Convert bytes to KB
                        );
                    }
                }
            }
        }

        // Description Length
        if (trim($restrictions['description_length']) != '' && strlen(trim(strip_tags($post_content))) > $restrictions['description_length'])
        {
            $errors[] = sprintf(esc_html__("The maximum length for listing content is %s characters. You've written %s characters.", 'listdom'), $restrictions['description_length'], strlen(trim(strip_tags($post_content))));
        }

        // Maximum Tags
        if (trim($restrictions['max_tags']) != '' && $this->is_enabled('tags') && isset($_POST['tags']) && count(explode(',', $_POST['tags'])) > $restrictions['max_tags'])
        {
            $errors[] = sprintf(esc_html__("The maximum allowed tags is %s tags. You've added %s tags.", 'listdom'), $restrictions['max_tags'], count(explode(',', $_POST['tags'])));
        }

        // There are some Errors
        if (count($errors)) $this->response(['success' => 0, 'message' => '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>']);

        // Trigger Event
        do_action('lsd_dashboard_validation', $lsd, $id);

        // Post Status
        $status = 'pending';
        if (current_user_can('publish_posts')) $status = 'publish';

        // Filter Listing Status
        $status = apply_filters('lsd_default_listing_status', $status, $lsd);
        $status = apply_filters('lsd_dashboard_listing_status', $status, $lsd);

        // Status by Request
        if (current_user_can('publish_posts') && isset($lsd['listing_status']) && trim($lsd['listing_status'])) $status = $lsd['listing_status'];

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

        // Save the Data
        $entity = new LSD_Entity_Listing($id);
        $entity->save($lsd);

        if ($status == 'publish') $message = sprintf(esc_html__('The listing has been published. %s', 'listdom'), '<a href="' . get_permalink($id) . '" target="_blank">' . esc_html__('View Listing', 'listdom') . '</a>');
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

        $lsd = $_POST['lsd'] ?? [];

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        // Save the Data
        $user_data = [
            'ID' => $user_id,
            'first_name' => $lsd['first_name'] ?? '',
            'last_name' => $lsd['last_name'] ?? '',
            'description' => $lsd['bio'] ?? '',
            'user_email' => $lsd['email'] ?? '',
        ];

        // Update core user fields
        wp_update_user($user_data);

        $meta_keys = [
            'lsd_job_title' => 'job_title',
            'lsd_profile_image' => 'profile_image',
            'lsd_hero_image' => 'hero_image',
            'lsd_phone' => 'phone',
            'lsd_mobile' => 'mobile',
            'lsd_website' => 'website',
            'lsd_fax' => 'fax',
            'lsd_facebook' => 'facebook',
            'lsd_twitter' => 'twitter',
            'lsd_pinterest' => 'pinterest',
            'lsd_linkedin' => 'linkedin',
            'lsd_instagram' => 'instagram',
            'lsd_whatsapp' => 'whatsapp',
            'lsd_youtube' => 'youtube',
            'lsd_tiktok' => 'tiktok',
            'lsd_telegram' => 'telegram',
        ];

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
            echo json_encode(['success' => 1, 'message' => esc_html__('Password updated successfully! Please log in again.', 'listdom')], JSON_NUMERIC_CHECK);

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
                'message' => sprintf(esc_html__('The uploaded image exceeds the maximum allowed size of %s KB.', 'listdom'), $restrictions['max_upload_size']),
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

        // No images
        if (!count($images)) $this->response(['success' => 0, 'message' => esc_html__('Please upload an image!', 'listdom')]);

        // Get restrictions
        $restrictions = $this->get_restriction_rules();

        // Allowed Extensions
        $allowed = ['jpeg', 'jpg', 'png', 'webp'];

        // Check for maximum upload size if set in restrictions (size in KB converted to bytes)
        $max_size = !empty($restrictions['max_upload_size']) ? $restrictions['max_upload_size'] * 1024 : 0;

        $data = [];
        $errors = [];

        $count = count($images['name']);
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
                    esc_html__("The image '%s' exceeds the maximum allowed size of %s KB. The image size is %s KB.", 'listdom'),
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
}
