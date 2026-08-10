<?php

class LSD_PTypes_Template extends LSD_PTypes
{
    public $PT;
    protected $creation_nonce;

    public function __construct()
    {
        $this->PT = LSD_Base::PTYPE_TEMPLATE;
    }

    public function init()
    {
        add_action('init', [$this, 'register_post_type']);
        add_action('current_screen', [$this, 'customize_screen']);
        add_action('pre_get_posts', [$this, 'show_all_template_statuses']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'maybe_render_preview_iframe'], 0);
        add_action('save_post_' . $this->PT, [$this, 'save_template_meta']);
        add_action('wp_creating_autosave', [$this, 'persist_autosave_layout_revision'], 20, 1);
        add_action('wp_restore_post_revision', [$this, 'restore_template_layout_css'], 20, 2);
        add_action('wp_ajax_lsd_create_template', [$this, 'ajax_create_template']);

        // Template Editor hooks
        add_action('wp_ajax_lsd_render_template_element', [$this, 'ajax_render_element']);
        add_action('wp_ajax_lsd_render_template_preview', [$this, 'ajax_render_preview']);
        add_action('wp_ajax_lsd_get_element_settings', [$this, 'ajax_get_element_settings']);
        add_action('wp_ajax_lsd_save_template_settings', [$this, 'ajax_save_template_settings']);
        add_action('wp_ajax_lsd_template_elements_list', [$this, 'ajax_template_elements_list']);

        add_filter('template_include', [$this, 'template'], 99);

        // Expose Template Builder layouts as selectable styles in shortcodes and single listing settings
        add_filter('lsd_styles', [$this, 'styles'], 10, 2);

        new LSD_Duplicate($this->PT);
    }

    public function add_query_vars($vars)
    {
        $vars[] = 'lsd_template_preview';
        $vars[] = 'lsd_template_id';
        $vars[] = 'lsd_preview_listing';
        $vars[] = 'lsd_preview_nonce';
        $vars[] = 'lsd_preview_template_type';

        return $vars;
    }

    public static function get_active_preview_request(): array
    {
        static $preview_request = null;
        if ($preview_request !== null) return $preview_request;

        $template_id = absint(get_query_var('lsd_template_id'));
        if (!$template_id && isset($_REQUEST['lsd_template_id'])) $template_id = absint(wp_unslash($_REQUEST['lsd_template_id']));

        $nonce = sanitize_text_field((string) get_query_var('lsd_preview_nonce'));
        if ($nonce === '' && isset($_REQUEST['lsd_preview_nonce'])) $nonce = sanitize_text_field(wp_unslash($_REQUEST['lsd_preview_nonce']));

        if (!$template_id || $nonce === '' || !wp_verify_nonce($nonce, 'lsd-template-preview-' . $template_id)) {
            return $preview_request = [];
        }

        $template = get_post($template_id);
        if (
            !$template instanceof WP_Post
            || $template->post_type !== LSD_Base::PTYPE_TEMPLATE
            || !current_user_can('edit_post', $template_id)
        ) {
            return $preview_request = [];
        }

        $template_type = sanitize_key((string) get_query_var('lsd_preview_template_type'));
        if ($template_type === '' && isset($_REQUEST['lsd_preview_template_type'])) {
            $template_type = sanitize_key(wp_unslash($_REQUEST['lsd_preview_template_type']));
        }

        $preview_listing_id = absint(get_query_var('lsd_preview_listing'));
        if (!$preview_listing_id && isset($_REQUEST['lsd_preview_listing'])) {
            $preview_listing_id = absint(wp_unslash($_REQUEST['lsd_preview_listing']));
        }

        return $preview_request = [
            'template_id' => $template_id,
            'template_type' => $template_type,
            'preview_listing_id' => $preview_listing_id,
        ];
    }

    /*--------------------------------------------------------------
    # Registration
    --------------------------------------------------------------*/

    public function register_post_type()
    {
        $args = [
            'labels' => [
                'name' => esc_html__('Templates', 'listdom'),
                'singular_name' => esc_html__('Template', 'listdom'),
                'add_new' => esc_html__('Add Template', 'listdom'),
                'add_new_item' => esc_html__('Add New Template', 'listdom'),
                'edit_item' => esc_html__('Edit Template', 'listdom'),
                'new_item' => esc_html__('New Template', 'listdom'),
                'view_item' => esc_html__('View Template', 'listdom'),
                'view_items' => esc_html__('View Templates', 'listdom'),
                'search_items' => esc_html__('Search Templates', 'listdom'),
                'not_found' => esc_html__('No templates found!', 'listdom'),
                'not_found_in_trash' => esc_html__('No templates found in Trash!', 'listdom'),
                'all_items' => esc_html__('All Templates', 'listdom'),
                'archives' => esc_html__('Template Archives', 'listdom'),
            ],
            'public' => false,
            'has_archive' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'supports' => ['title', 'editor', 'thumbnail'],
            'capabilities' => array_fill_keys([
                'edit_post', 'read_post', 'delete_post',
                'edit_posts', 'edit_others_posts',
                'delete_posts', 'publish_posts',
                'read_private_posts'
            ], 'manage_options'),
        ];

        register_post_type($this->PT, apply_filters('lsd_ptype_template_args', $args));
    }

    protected function get_post_type_capability(string $capability, string $fallback = ''): string
    {
        $post_type = get_post_type_object($this->PT);

        if (
            $post_type
            && isset($post_type->cap)
            && isset($post_type->cap->{$capability})
            && is_string($post_type->cap->{$capability})
            && $post_type->cap->{$capability} !== ''
        ) {
            return $post_type->cap->{$capability};
        }

        return $fallback;
    }

    protected function current_user_can_template_capability(string $capability, string $fallback = 'manage_options'): bool
    {
        $resolved = $this->get_post_type_capability($capability, $fallback);

        return $resolved !== '' && current_user_can($resolved);
    }

    protected function current_user_can_create_templates(): bool
    {
        $capability = $this->get_post_type_capability('create_posts');
        if ($capability === '') $capability = $this->get_post_type_capability('edit_posts', 'manage_options');

        return $capability !== '' && current_user_can($capability);
    }

    protected function current_user_can_publish_templates(): bool
    {
        return $this->current_user_can_template_capability('publish_posts', 'manage_options');
    }

    protected function is_publish_like_status(string $status): bool
    {
        return in_array(sanitize_key($status), ['publish', 'future', 'private'], true);
    }

    protected function current_user_can_assign_template_status(WP_Post $post, string $status): bool
    {
        $status = sanitize_key($status);
        $current_status = sanitize_key($post->post_status);

        if ($status === '' || $status === $current_status)
        {
            return true;
        }

        if ($this->is_publish_like_status($status) || $this->is_publish_like_status($current_status))
        {
            return $this->current_user_can_publish_templates();
        }

        return current_user_can('edit_post', $post->ID);
    }

    /*--------------------------------------------------------------
    # Screen Customization
    --------------------------------------------------------------*/

    public function customize_screen($screen)
    {
        if ($this->is_template_screen($screen))
        {
            add_filter('views_edit-' . $this->PT, [$this, 'templates_screen'], 100);
            if ($this->current_user_can_create_templates()) add_action('admin_footer', [$this, 'creation_modal']);
        }

        if ($this->is_template_screen($screen, 'post'))
        {
            add_filter('admin_body_class', function ($classes) {
                return trim($classes . ' lsd-template-editor');
            });

            add_action('edit_form_top', [$this, 'editor_content']);
        }
    }

    public function editor_content($post)
    {
        if (!$post instanceof WP_Post || $post->post_type !== $this->PT) return;

        $this->include_html_file('template-builder/editor-workspace.php');
    }

    public function template($template)
    {
        if (is_embed()) return $template;

        $post = get_queried_object();
        $post_id = 0;

        if ($post instanceof WP_Post) {
            $post_id = (int) $post->ID;
        } elseif (is_preview()) {
            $post_id = isset($_GET['preview_id']) ? absint($_GET['preview_id']) : 0;
            if (!$post_id && isset($_GET['p'])) $post_id = absint($_GET['p']);
        }

        if (!$post_id) return $template;

        $post = get_post($post_id);
        if (!$post instanceof WP_Post || $post->post_type !== $this->PT) return $template;

        $page_template = get_post_meta($post_id, '_wp_page_template', true);
        $page_template = is_string($page_template) ? trim($page_template) : '';
        if ($page_template === '' || $page_template === 'default') return $template;

        $located_template = locate_template($page_template);

        if (!$located_template && class_exists(\Elementor\Plugin::class))
        {
            $module = \Elementor\Plugin::instance()->modules_manager->get_modules('page-templates');
            if ($module && method_exists($module, 'get_template_path'))
            {
                $located_template = $module->get_template_path($page_template);
            }
        }

        if ($located_template) $template = $located_template;

        return $template;
    }

    public function maybe_render_preview_iframe()
    {
        if (!get_query_var('lsd_template_preview')) return;

        $template_id = absint(get_query_var('lsd_template_id'));
        $preview_listing_id = absint(get_query_var('lsd_preview_listing'));
        $nonce = sanitize_text_field((string) get_query_var('lsd_preview_nonce'));
        $template_type = sanitize_key((string) get_query_var('lsd_preview_template_type'));
        $preview_surface = isset($_REQUEST['lsd_preview_surface'])
            ? sanitize_key(wp_unslash($_REQUEST['lsd_preview_surface']))
            : '';

        if (!$template_id || !$nonce || !wp_verify_nonce($nonce, 'lsd-template-preview-' . $template_id)) {
            status_header(403);
            wp_die(esc_html__('Invalid template preview request.', 'listdom'));
        }

        if (!current_user_can('edit_post', $template_id) || get_post_type($template_id) !== $this->PT) {
            status_header(403);
            wp_die(esc_html__('You are not allowed to preview this template.', 'listdom'));
        }

        if ($preview_listing_id && get_post_type($preview_listing_id) !== LSD_Base::PTYPE_LISTING) {
            $preview_listing_id = 0;
        }

        if (!$preview_listing_id) $preview_listing_id = $this->get_preview_listing_id($template_id);

        $template_type = $this->resolve_preview_template_type($template_id, $template_type);

        add_filter('lsd_force_include_frontend_assets', '__return_true');
        wp_enqueue_style('lsd-template-builder-backend-preview', $this->lsd_asset_url('css/backend.css'), ['lsd-frontend'], LSD_Assets::version());

        $body = $this->get_preview_iframe_body($template_id, $preview_listing_id, $template_type);
        $preview_root_id = 'lsd-template-preview-root';
        $class = 'lsd-iframe-page lsd-template-editor';
        if ($preview_surface === 'card') $class .= ' lsd-template-preview-surface-card';

        nocache_headers();
        header('X-Robots-Tag: noindex, nofollow', true);

        echo $this->iframe($body, $class);
        exit;
    }

    protected function get_preview_iframe_body(int $template_id, int $preview_listing_id = 0, string $template_type = ''): string
    {
        $template_id = absint($template_id);
        if (!$template_id) return '';

        $args = [
            'context' => $template_type !== '' ? 'preview' : 'frontend',
            'apply_filters' => false,
        ];

        if ($preview_listing_id) $args['listing_id'] = $preview_listing_id;
        if ($template_type !== '') $args['template_type'] = $this->resolve_preview_template_type($template_id, $template_type);

        if ($preview_listing_id)
        {
            LSD_LifeCycle::post($preview_listing_id);
            $html = LSD_Template::get_template_builder_content_for_display($template_id, $args);
            LSD_LifeCycle::reset();
        }
        else
        {
            $html = LSD_Template::get_template_builder_content_for_display($template_id, $args);
        }

        return LSD_Kses::full($html);
    }

    public function ajax_render_preview()
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'lsd-template-element')) $this->response([
            'success' => 0,
            'message' => esc_html__('Security check failed.', 'listdom'),
        ]);

        $template_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (!$template_id || !current_user_can('edit_post', $template_id) || get_post_type($template_id) !== $this->PT) {
            $this->response([
                'success' => 0,
                'message' => esc_html__('You are not allowed to preview this template.', 'listdom'),
            ]);
        }

        $preview_listing_id = isset($_POST['preview_listing']) ? absint($_POST['preview_listing']) : 0;
        if ($preview_listing_id && get_post_type($preview_listing_id) !== LSD_Base::PTYPE_LISTING) $preview_listing_id = 0;
        if (!$preview_listing_id) $preview_listing_id = $this->get_preview_listing_id($template_id);

        $template_type = isset($_POST['template_type']) ? sanitize_key(wp_unslash($_POST['template_type'])) : '';

        $raw_layout = isset($_POST['layout_state']) ? wp_unslash($_POST['layout_state']) : '';
        $layout_state = json_decode(is_string($raw_layout) ? $raw_layout : '', true);
        $layout_state = is_array($layout_state) ? $this->sanitize_template_layout_state($layout_state) : [];

        $html = $this->render_preview_body_from_state($template_id, $layout_state, $preview_listing_id, $template_type);

        $this->response([
            'success' => 1,
            'content' => LSD_Kses::full($html),
        ]);
    }

    protected function render_preview_body_from_state(int $template_id, array $layout_state, int $preview_listing_id = 0, string $template_type = ''): string
    {
        if (!$template_id || empty($layout_state['layout']) || empty($layout_state['elements'])) return '';

        $args = [
            'context' => 'preview',
            'apply_filters' => false,
        ];

        if ($preview_listing_id) $args['listing_id'] = $preview_listing_id;
        if ($template_type !== '') $args['template_type'] = sanitize_key($template_type);

        if ($preview_listing_id)
        {
            LSD_LifeCycle::post($preview_listing_id);
            $html = LSD_Template::get_template_builder_content_from_state($template_id, $layout_state, $args);
            LSD_LifeCycle::reset();
        }
        else
        {
            $html = LSD_Template::get_template_builder_content_from_state($template_id, $layout_state, $args);
        }

        return LSD_Kses::full($html);
    }

    public function get_preview_iframe_url(int $template_id, int $preview_listing_id = 0, string $template_type = ''): string
    {
        $template_id = absint($template_id);
        if (!$template_id) return '';

        $template_type = $this->resolve_preview_template_type($template_id, $template_type);

        $args = [
            'lsd_template_preview' => 1,
            'lsd_template_id' => $template_id,
            'lsd_preview_nonce' => wp_create_nonce('lsd-template-preview-' . $template_id),
        ];

        $preview_listing_id = absint($preview_listing_id);
        if ($preview_listing_id) $args['lsd_preview_listing'] = $preview_listing_id;

        if ($template_type !== '') $args['lsd_preview_template_type'] = $template_type;

        return esc_url(add_query_arg($args, home_url('/')));
    }

    /**
     * Build sidebar menus for the editor
     *
     * @return array
     */
    protected function sidebar_menus(): array
    {
        return [
            'elements' => [
                'elements'  => esc_html__('Elements', 'listdom'),
            ],
            'options' => [
                'content'   => esc_html__('Content', 'listdom'),
                'style'     => esc_html__('Style', 'listdom'),
                'advanced'  => esc_html__('Advanced', 'listdom'),
            ],
        ];
    }

    protected function last_saved_text(WP_Post $post): string
    {
        $default = esc_html__('Last Saved', 'listdom');

        $modified_time = get_post_modified_time('U', true, $post);
        $current_time = current_time('timestamp', true);

        if (!$modified_time || !$current_time) return $default;

        $diff = max(0, $current_time - $modified_time);
        $minutes = max(1, (int) round($diff / 60));

        if ($minutes < 60) $time = ['count'=> $minutes, 'singular' => 'minute', 'plural' => 'minutes',];
        elseif ($minutes < 1440) $time = ['count'=> max(1, (int) round($minutes / 60)), 'singular' => 'hour', 'plural' => 'hours',];
        else $time = ['count'=> max(1, (int) round($minutes / 1440)), 'singular' => 'day', 'plural' => 'days',];

        $label = _n(
            "Last Saved %d {$time['singular']} ago",
            "Last Saved %d {$time['plural']} ago",
            $time['count'],
            'listdom'
        );

        return sprintf(esc_html($label), $time['count']);
    }

    protected function autosave_enabled(int $post_id = 0): bool
    {
        $post_id = $post_id > 0 ? $post_id : $this->current_template_post_id();

        if ($post_id > 0)
        {
            $autosave_status = get_post_meta($post_id, '_lsd_template_autosave_status', true);
            if ($autosave_status !== '') return absint($autosave_status) === 1;
        }

        $settings = LSD_Options::settings();

        return !isset($settings['autosave_status']) || (bool) $settings['autosave_status'];
    }

    protected function current_template_post_id(): int
    {
        global $post;

        if ($post instanceof WP_Post && $post->post_type === $this->PT) return (int) $post->ID;

        $post_id = isset($_GET['post']) ? absint(wp_unslash($_GET['post'])) : 0;
        if ($post_id && get_post_type($post_id) === $this->PT) return $post_id;

        return 0;
    }

    protected function persist_autosave_setting(int $post_id, int $status): void
    {
        update_post_meta($post_id, '_lsd_template_autosave_status', $status === 1 ? '1' : '0');
    }

    protected function decode_template_layout_state($raw_layout): array
    {
        $layout_state = json_decode(is_string($raw_layout) ? $raw_layout : '', true);
        $layout_state = is_array($layout_state) ? $layout_state : [];

        return $this->sanitize_template_layout_state($layout_state);
    }

    protected function persist_layout_state(int $post_id, array $post): void
    {
        if (!array_key_exists('lsd_template_layout', $post)) return;

        $layout_state = $this->decode_template_layout_state($post['lsd_template_layout']);

        update_post_meta($post_id, '_lsd_template_layout', wp_json_encode($layout_state));

        LSD_Template_Generator_CSS::save_post_css($post_id, $layout_state);
        LSD_Template_Generator_CSS::save_post_css($post_id, $layout_state, 'preview');
    }

    public function persist_autosave_layout_revision(array $new_autosave): void
    {
        $autosave_id = isset($new_autosave['ID']) ? absint($new_autosave['ID']) : 0;
        $post_id = isset($new_autosave['post_parent']) ? absint($new_autosave['post_parent']) : 0;

        if (!$autosave_id || !$post_id || get_post_type($post_id) !== $this->PT) return;
        if (!$this->autosave_enabled($post_id)) return;

        $posted_data = $_POST['data']['wp_autosave'] ?? $_POST;
        $posted_data = is_array($posted_data) ? wp_unslash($posted_data) : [];

        if (!array_key_exists('lsd_template_layout', $posted_data)) return;

        $layout_state = $this->decode_template_layout_state($posted_data['lsd_template_layout']);

        delete_metadata('post', $autosave_id, '_lsd_template_layout');
        add_metadata('post', $autosave_id, '_lsd_template_layout', wp_json_encode($layout_state));
    }

    public function restore_template_layout_css(int $post_id, int $revision_id): void
    {
        if (get_post_type($post_id) !== $this->PT) return;

        if (metadata_exists('post', $revision_id, '_lsd_template_layout'))
        {
            $layout_state = $this->decode_template_layout_state(get_metadata('post', $revision_id, '_lsd_template_layout', true));
            update_post_meta($post_id, '_lsd_template_layout', wp_json_encode($layout_state));
        }
        else
        {
            $layout_state = $this->decode_template_layout_state(get_post_meta($post_id, '_lsd_template_layout', true));
        }

        LSD_Template_Generator_CSS::save_post_css($post_id, $layout_state);
        LSD_Template_Generator_CSS::save_post_css($post_id, $layout_state, 'preview');
    }

    public function page_templates(): array
    {
        $page_templates = wp_get_theme()->get_post_templates();
        $page_templates = is_array($page_templates) ? $page_templates : [];
        $page_templates = array_reduce($page_templates, function ($carry, $templates) {
            if (!is_array($templates)) return $carry;

            foreach ($templates as $file => $label)
            {
                if (in_array($file, ['elementor_canvas', 'elementor_header_footer'], true)) continue;
                if (!isset($carry[$file])) $carry[$file] = $label;
            }

            return $carry;
        }, []);

        return ['default' => esc_html__('Default Template', 'listdom')] + $page_templates;
    }

    public function disable_autosave_script()
    {
        wp_dequeue_script('autosave');
        wp_deregister_script('autosave');
    }

    protected function is_template_screen($screen = null, $base = 'edit'): bool
    {
        if (!$screen) $screen = get_current_screen();
        return ($screen && $screen->post_type === $this->PT && $screen->base === $base);
    }

    public function ajax_save_template_settings()
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';

        if (!$nonce || !wp_verify_nonce($nonce, 'lsd-save-template-settings')) $this->response(['success' => 0, 'message' => esc_html__('Security check failed.', 'listdom')]);

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

        if (!$post_id) $this->response(['success' => 0, 'message' => esc_html__('Invalid template.', 'listdom')]);

        if (!current_user_can('edit_post', $post_id)) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to perform this action.', 'listdom')]);

        $post_obj = get_post($post_id);

        if (!$post_obj || $post_obj->post_type !== $this->PT) $this->response(['success' => 0, 'message' => esc_html__('Invalid template.', 'listdom')]);

        $submitted = isset($_POST['post']) && is_array($_POST['post']) ? wp_unslash($_POST['post']) : [];

        // Title
        $raw_title = $submitted['title'] ?? '';
        $title     = sanitize_text_field(is_string($raw_title) ? $raw_title : '');
        $title     = $title !== '' ? $title : $post_obj->post_title;

        // Template type
        $types = array_keys($this->template_types());
        $type  = isset($submitted['template_type']) ? sanitize_key($submitted['template_type']) : '';
        if (!in_array($type, $types, true)) $type = $this->default_template_type();

        // Status
        $status           = isset($submitted['status']) ? sanitize_key($submitted['status']) : '';
        $allowed_statuses = ['draft', 'publish', 'pending'];
        if (!in_array($status, $allowed_statuses, true)) $status = $post_obj->post_status;
        if (!$this->current_user_can_assign_template_status($post_obj, $status)) $status = $post_obj->post_status;

        $page_templates = array_keys($this->page_templates());
        $page_template = isset($submitted['page_template']) ? sanitize_text_field($submitted['page_template']) : 'default';
        if ($page_template !== 'default' && !in_array($page_template, $page_templates, true)) $page_template = 'default';

        $original_page_template = $_POST['page_template'] ?? null;
        $original_post_page_template = isset($_POST['post']) && is_array($_POST['post']) ? ($_POST['post']['page_template'] ?? null) : null;

        $_POST['page_template'] = 'default';
        if (isset($_POST['post']) && is_array($_POST['post'])) $_POST['post']['page_template'] = 'default';

        $result = wp_update_post([
            'ID'          => $post_id,
            'post_title'  => $title,
            'post_status' => $status,
            'page_template' => 'default',
        ], true);

        if ($original_page_template === null) unset($_POST['page_template']);
        else $_POST['page_template'] = $original_page_template;

        if (isset($_POST['post']) && is_array($_POST['post']))
        {
            if ($original_post_page_template === null) unset($_POST['post']['page_template']);
            else $_POST['post']['page_template'] = $original_post_page_template;
        }

        if (is_wp_error($result)) $this->response(['success' => 0, 'message' => esc_html__('Unable to save settings.', 'listdom')]);

        update_post_meta($post_id, '_lsd_template_type', $type);

        $layout_saved = false;
        if (array_key_exists('lsd_template_layout', $submitted))
        {
            $this->persist_layout_state($post_id, $submitted);
            $layout_saved = true;
        }

        if (array_key_exists('autosave_status', $submitted)) {
            $autosave_status = absint($submitted['autosave_status']) === 1 ? 1 : 0;
            $this->persist_autosave_setting($post_id, $autosave_status);
        }

        $preview_listing_id = isset($submitted['preview_listing']) ? absint($submitted['preview_listing']) : 0;
        if ($preview_listing_id && get_post_type($preview_listing_id) !== LSD_Base::PTYPE_LISTING) $preview_listing_id = 0;

        if ($preview_listing_id) update_post_meta($post_id, '_lsd_template_preview_listing', $preview_listing_id);
        else delete_post_meta($post_id, '_lsd_template_preview_listing');

        update_post_meta($post_id, '_wp_page_template', $page_template);

        $type_info = $this->template_type_info($type);

        $this->response([
            'success' => 1,
            'data'    => [
                'title'              => get_the_title($post_id),
                'template_type'      => $type,
                'template_type_label' => $type_info['label'] ?? '',
                'status'             => $status,
                'status_label'       => $this->status_label($status),
                'autosave_enabled'   => $this->autosave_enabled($post_id),
                'layout_saved'       => $layout_saved,
                'preview_listing'    => $preview_listing_id ?: $this->get_preview_listing_id($post_id),
            ],
        ]);
    }

    public function ajax_render_element()
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';

        if (!$nonce || !wp_verify_nonce($nonce, 'lsd-template-element')) $this->response(['success' => 0, 'message' => esc_html__('Security check failed.', 'listdom')]);

        if (!$this->current_user_can_template_capability('edit_posts', 'manage_options')) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to perform this action.', 'listdom')]);

        $element_type  = isset($_POST['element_type']) ? sanitize_key(wp_unslash($_POST['element_type'])) : '';
        $template_type = isset($_POST['template_type']) ? sanitize_key(wp_unslash($_POST['template_type'])) : '';
        $element_id    = isset($_POST['element_id']) ? sanitize_key(wp_unslash($_POST['element_id'])) : '';
        $template_id   = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

        if ($element_type === '') $this->response(['success' => 0, 'message' => esc_html__('Unknown element type.', 'listdom')]);

        $element_key = $element_type;

        if ($template_id && (!current_user_can('edit_post', $template_id) || get_post_type($template_id) !== $this->PT)) $template_id = 0;

        // Settings come from the editor UI
        $settings = $this->sanitize_element_settings($this->parse_element_settings_request(), $element_type);

        /** @var LSD_Template|false $element */
        $element = LSD_Template::instance($element_key, $settings);

        if (!$element) $this->response(['success' => 0, 'message' => esc_html__('Unable to load the requested element.', 'listdom')]);

        $settings = $element->resolve_settings($settings);

        $preview_listing_id = isset($_POST['preview_listing']) ? absint($_POST['preview_listing']) : 0;
        $listing_id = ($preview_listing_id && get_post_type($preview_listing_id) === LSD_Base::PTYPE_LISTING)
            ? $preview_listing_id
            : $this->get_preview_listing_id($template_id);

        if ($element_id === '') {
            $element_id = 'preview-' . uniqid('', true);
            $element_id = sanitize_key($element_id);
        }

        $render_args = [
            'listing_id'    => $listing_id ?: null,
            'settings'      => $settings,
            'template_type' => $template_type,
            'context'       => 'preview',
        ];

        if ($listing_id)
        {
            LSD_LifeCycle::post($listing_id);
            $content = $element->canvas($render_args);
            LSD_LifeCycle::reset();
        }
        else
        {
            $content = $element->canvas($render_args);
        }

        $is_empty_preview = $this->is_empty_preview_content($content);
        if ($is_empty_preview) $content = '<div class="lsd-template-editor-canvas__content-empty" aria-hidden="true"></div>';

        $wrapper_class = class_exists('LSD_Template_Generator_CSS') ? LSD_Template_Generator_CSS::wrapper_class($element_id) : 'lsd-template-builder-element-' . sanitize_html_class($element_id);
        if ($is_empty_preview) $wrapper_class .= ' lsd-template-builder-element--empty-state';

        $css = class_exists('LSD_Template_Generator_CSS') ? LSD_Template_Generator_CSS::compile_for_elements([
                $element_id => [
                    'id'       => $element_id,
                    'type'     => $element_key,
                    'settings' => $settings,
                ],
            ], 'preview') : '';

        $wrapped = '<div class="' . esc_attr($wrapper_class) . '" data-lsd-element-id="' . esc_attr($element_id) . '">' . $content . '</div>';

        if ($css !== '') $wrapped = '<style data-lsd-template-css="' . esc_attr($element_key) . '">' . $css . '</style>' . $wrapped;

        $this->response([
            'success'     => 1,
            'content'     => LSD_Kses::full($wrapped),
            'element_key' => $element_key,
            'element_id'  => $element_id,
        ]);
    }

    public function ajax_get_element_settings()
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';

        if (!$nonce || !wp_verify_nonce($nonce, 'lsd-template-element-settings')) $this->response(['success' => 0, 'message' => esc_html__('Security check failed.', 'listdom')]);

        if (!$this->current_user_can_template_capability('edit_posts', 'manage_options')) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to perform this action.', 'listdom')]);

        $element_type  = isset($_POST['element_type']) ? sanitize_key(wp_unslash($_POST['element_type'])) : '';
        $template_type = isset($_POST['template_type']) ? sanitize_key(wp_unslash($_POST['template_type'])) : '';
        $element_id    = isset($_POST['element_id']) ? sanitize_key(wp_unslash($_POST['element_id'])) : '';

        if ($element_type === '') $this->response(['success' => 0, 'message' => esc_html__('Unknown element type.', 'listdom')]);

        $element_key = $element_type;

        $settings = $this->sanitize_element_settings($this->parse_element_settings_request(), $element_type);
        $element  = LSD_Template::instance($element_key, $settings);

        if (!$element) $this->response(['success' => 0, 'message' => esc_html__('Unable to load the requested element.', 'listdom')]);

        $settings = $element->resolve_settings($settings);

        $this->response([
            'success'      => 1,
            'content'      => LSD_Kses::form($element->element_settings($settings['content'] ?? [])),
            'style'        => LSD_Kses::form($element->style_settings($settings['style'] ?? [])),
            'advanced'     => LSD_Kses::form($element->advanced_settings($settings['advanced'] ?? [])),
            'element_id'   => $element_id,
            'element_type' => $element_type,
            'element_key'  => $element_key,
        ]);
    }

    public function ajax_template_elements_list()
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';

        if (!$nonce || !wp_verify_nonce($nonce, 'lsd-template-element-settings')) $this->response(['success' => 0, 'message' => esc_html__('Security check failed.', 'listdom')]);

        if (!$this->current_user_can_template_capability('edit_posts', 'manage_options')) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to perform this action.', 'listdom')]);

        $types = array_keys($this->template_types());
        $template_type = isset($_POST['template_type']) ? sanitize_key(wp_unslash($_POST['template_type'])) : '';
        if (!in_array($template_type, $types, true)) $template_type = $this->default_template_type();

        $html = $this->include_html_file('template-builder/elements-list.php', [
            'parameters' => [
                'current_type' => $template_type,
            ],
            'return_output' => true,
        ]);

        $this->response([
            'success' => 1,
            'content' => LSD_Kses::full($html),
            'template_type' => $template_type,
        ]);
    }

    protected function parse_element_settings_request(): array
    {
        if (!isset($_POST['settings'])) return [];

        $raw = wp_unslash($_POST['settings']);

        if (is_array($raw)) return $raw;

        if (!is_string($raw) || $raw === '') return [];

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function is_empty_preview_content($content): bool
    {
        $content = is_string($content) ? trim($content) : '';
        if ($content === '') return true;

        if (trim(wp_strip_all_tags($content)) !== '') return false;

        return !preg_match('/<(img|svg|iframe|video|audio|canvas|input|textarea|select|button)\b/i', $content)
            && !preg_match('/<i\b[^>]*class\s*=\s*(["\'])[^"\']*(icon|wbli-)[^"\']*\1/i', $content)
            && !preg_match('/<div\b[^>]*class\s*=\s*(["\'])[^"\']*lsd-template-editor-canvas__container[^"\']*\1/i', $content);
    }

    protected function get_html_capable_fields(string $element_type): array
    {
        static $cache = [];

        $element_type = sanitize_key($element_type);
        if ($element_type === '') return [];

        if (isset($cache[$element_type])) return $cache[$element_type];

        $element = LSD_Template::instance($element_type, []);
        if (!$element)
        {
            $cache[$element_type] = [];
            return $cache[$element_type];
        }

        $sections = $element->get_controls_sections();
        $html_types = ['textarea', 'editor', 'wysiwyg'];
        $keys = [];

        $collect_fields = function ($fields) use (&$collect_fields, &$keys, $html_types) {
            if (!is_array($fields)) return;

            foreach ($fields as $field)
            {
                if (!is_array($field)) continue;

                $type = isset($field['type']) ? (string) $field['type'] : '';
                $id = isset($field['id']) ? sanitize_key((string) $field['id']) : '';

                if ($type === 'repeater' && !empty($field['fields']) && is_array($field['fields']))
                {
                    $collect_fields($field['fields']);
                }

                if ($id === '') continue;

                if (in_array($type, $html_types, true)) $keys[$id] = true;
            }
        };

        foreach ($sections as $section)
        {
            if (!empty($section['fields']) && is_array($section['fields']))
            {
                $collect_fields($section['fields']);
            }
        }

        $cache[$element_type] = $keys;

        return $cache[$element_type];
    }

    protected function sanitize_element_settings($settings, string $element_type = '', array $html_fields = null): array
    {
        if (!is_array($settings)) return [];

        if ($html_fields === null) $html_fields = $this->get_html_capable_fields($element_type);

        $sanitized = [];

        foreach ($settings as $key => $value)
        {
            $clean_key = sanitize_key((string) $key);

            if ($clean_key === '') continue;

            if (is_array($value))
            {
                $sanitized[$clean_key] = $this->sanitize_element_settings($value, $element_type, $html_fields);
                continue;
            }

            if (is_scalar($value))
            {
                $string_value = (string) $value;
                $sanitized[$clean_key] = isset($html_fields[$clean_key])
                    ? wp_kses_post($string_value)
                    : sanitize_text_field($string_value);
                continue;
            }

            // Fallback for objects/resources/etc.
            $sanitized[$clean_key] = '';
        }

        return $sanitized;
    }

    protected function sanitize_template_layout_state($state): array
    {
        $sanitized = [
            'layout'   => [],
            'elements' => [],
        ];

        if (!is_array($state)) return $sanitized;

        $elements = [];

        // 1) Sanitize explicit "elements"
        if (!empty($state['elements']) && is_array($state['elements']))
        {
            foreach ($state['elements'] as $element_id => $data)
            {
                if (!is_array($data)) continue;

                $clean_id   = sanitize_key((string) $element_id);
                $clean_type = sanitize_key((string) ($data['type'] ?? ''));

                if ($clean_id === '' || $clean_type === '') continue;

                $elements[$clean_id] = [
                    'id'       => $clean_id,
                    'type'     => $clean_type,
                    'label'    => isset($data['label']) ? sanitize_text_field((string) $data['label']) : '',
                    'settings' => $this->sanitize_element_settings($data['settings'] ?? [], $clean_type),
                ];
            }
        }

        // 2) Recursive walker for layout nodes (containers + elements)
        $walkNode = function ($node) use (&$walkNode, &$elements) {
            if (!is_array($node)) return null;

            $id   = sanitize_key((string) ($node['id'] ?? ''));
            $type = sanitize_key((string) ($node['type'] ?? ''));

            if ($id === '' || $type === '') return null;

            // Ensure element entry exists / is updated
            if (!isset($elements[$id]))
            {
                $elements[$id] = [
                    'id'       => $id,
                    'type'     => $type,
                    'label'    => isset($node['label']) ? sanitize_text_field((string) $node['label']) : '',
                    'settings' => (isset($node['settings']) && is_array($node['settings']))
                        ? $this->sanitize_element_settings($node['settings'], $type)
                        : [],
                ];
            }
            else
            {
                // Fill missing label from layout node if needed
                if ((!isset($elements[$id]['label']) || $elements[$id]['label'] === '') && !empty($node['label'])) $elements[$id]['label'] = sanitize_text_field((string) $node['label']);
            }

            // Recursively sanitize children
            $children = [];

            if (!empty($node['children']) && is_array($node['children']))
            {
                foreach ($node['children'] as $child)
                {
                    $childNode = $walkNode($child);
                    if ($childNode !== null) $children[] = $childNode;
                }
            }

            return [
                'id'       => $id,
                'type'     => $type,
                'children' => $children,
            ];
        };

        // 3) Build the full (nested) layout tree, restricting root to containers
        if (!empty($state['layout']) && is_array($state['layout']))
        {
            foreach ($state['layout'] as $row)
            {
                $node = $walkNode($row);

                if ($node === null) continue;

                // Keep your assumption: only containers at root level
                if (($node['type'] ?? '') !== 'container') continue;

                $sanitized['layout'][] = $node;
            }
        }

        $sanitized['elements'] = $elements;

        return $sanitized;
    }

    protected function get_preview_listing_id(int $template_id = 0): int
    {
        static $preview_listing_ids = [];

        $template_id = $template_id ?: 0;

        if (array_key_exists($template_id, $preview_listing_ids)) return (int) $preview_listing_ids[$template_id];

        $saved_listing_id = 0;

        if ($template_id)
        {
            $saved_listing_id = (int) get_post_meta($template_id, '_lsd_template_preview_listing', true);

            $saved_post = $saved_listing_id ? get_post($saved_listing_id) : null;
            if (!$saved_post instanceof WP_Post || $saved_post->post_type !== LSD_Base::PTYPE_LISTING) $saved_listing_id = 0;
        }

        if (!$saved_listing_id)
        {
            $ids = get_posts([
                'post_type'      => LSD_Base::PTYPE_LISTING,
                'posts_per_page' => 1,
                'post_status'    => ['publish', 'pending', 'draft', 'future'],
                'fields'         => 'ids',
                'no_found_rows'  => true,
            ]);

            $saved_listing_id = (!empty($ids) && is_array($ids)) ? (int) $ids[0] : 0;
        }

        $preview_listing_ids[$template_id] = $saved_listing_id;

        return (int) $saved_listing_id;
    }

    /*--------------------------------------------------------------
    # Query Adjustment — Show all statuses
    --------------------------------------------------------------*/

    public function show_all_template_statuses($query)
    {
        if (!is_admin() || !$query->is_main_query()) return;

        $post_type = $query->get('post_type') ?: (isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '');
        if ($post_type !== $this->PT) return;

        $status = $this->get_current_status_filter();
        if ($status)
        {
            $query->set('post_status', [$status]);
        }
        else
        {
            $all_statuses = array_keys(get_post_stati(['show_in_admin_all_list' => true]));

            if (empty($all_statuses)) $all_statuses = ['publish'];

            $query->set('post_status', $all_statuses);
        }

        $search = $this->get_current_search_query();
        if ($search !== '') $query->set('s', $search);
    }

    /*--------------------------------------------------------------
    # Templates Screen
    --------------------------------------------------------------*/

    public function templates_screen($views = [])
    {
        global $wp_query;

        if (!$this->is_template_screen()) return $views;

        // Posts
        $posts        = ($wp_query instanceof WP_Query) ? $wp_query->posts : [];
        $has_templates = !empty($posts);

        // Filters & search
        $current_status = $this->get_current_status_filter();
        $search_query   = $this->get_current_search_query();
        $statuses       = $this->template_status_filters($current_status);
        $total_templates = isset($statuses[0]['count']) ? (int) $statuses[0]['count'] : 0;

        // Screen states
        $show_blank_screen = $total_templates === 0 && $current_status === '' && $search_query === '';
        $no_results        = !$has_templates && !$show_blank_screen;

        // Templates & pagination
        $template_items = $posts ? array_map([$this, 'format_template_item'], $posts) : [];
        $pagination     = $this->pagination($wp_query);
        $can_create_templates = $this->current_user_can_create_templates();

        // Render
        $this->include_html_file('template-builder/screen.php', [
            'parameters' => [
                'has_templates'    => $has_templates,
                'show_blank_screen'=> $show_blank_screen,
                'no_results'       => $no_results,
                'templates'        => $template_items,
                'pagination'       => $pagination,
                'statuses'         => $statuses,
                'current_status'   => $current_status,
                'search_query'     => $search_query,
                'modal_id'         => 'lsd-template-modal',
                'reset_url'        => $this->templates_screen_url([], ['lsd_status', 'lsd_template_search']),
                'can_create_templates' => $can_create_templates,
            ],
        ]);

        return $views;
    }

    protected function format_template_item($post): array
    {
        if (!$post instanceof WP_Post) return [];

        $thumbnail = get_the_post_thumbnail_url($post, 'medium') ?: $this->lsd_asset_url('img/image-placeholder.svg');
        $type_info = $this->template_type_info(get_post_meta($post->ID, '_lsd_template_type', true));
        $status_label = $this->status_label($post->post_status);
        $preview_url = $this->get_template_preview_url($post);

        $actions = [];

        $is_trashed = ($post->post_status === 'trash');
        if ($is_trashed && ($restore_url = $this->get_restore_link($post->ID)))
        {
            $actions[] = [
                'name' => 'restore',
                'label' => esc_html__('Restore', 'listdom'),
                'url' => $restore_url,
                'icon' => 'wbli-reset',
            ];
        }

        if ($del_url = get_delete_post_link($post->ID, '', $is_trashed))
        {
            $actions[] = [
                'name' => 'delete',
                'label' => $is_trashed ? esc_html__('Delete Permanently', 'listdom') : esc_html__('Move to Trash', 'listdom'),
                'url' => $del_url,
                'icon' => 'wbli-cross',
                'data' => [
                    'lsd-template-delete' => 'true',
                    'lsd-template-delete-type' => $is_trashed ? 'delete' : 'trash',
                    'lsd-template-confirm-message' => $is_trashed
                        ? esc_html__('Delete this template permanently? This action cannot be undone.', 'listdom')
                        : esc_html__('Are you sure you want to move this template to the trash?', 'listdom'),
                    'lsd-template-confirm-text' => $is_trashed
                        ? esc_html__('Delete Permanently', 'listdom')
                        : esc_html__('Move to Trash', 'listdom'),
                    'lsd-template-cancel-text' => esc_html__('Cancel', 'listdom'),
                ],
            ];
        }

        if ($edit_url = get_edit_post_link($post->ID))
        {
            $actions[] = ['name' => 'edit', 'label' => __('Edit Template', 'listdom'), 'url' => $edit_url, 'icon' => 'wbli-file-edit-pencil'];
        }
        if ($dup_url = $this->get_duplicate_link($post->ID))
        {
            $actions[] = ['name' => 'duplicate', 'label' => __('Duplicate', 'listdom'), 'url' => $dup_url, 'icon' => 'wbli-copy'];
        }

        return [
            'title' => get_the_title($post),
            'thumbnail' => $thumbnail,
            'preview_url' => $preview_url,
            'type' => $type_info['label'],
            'type_icon' => $type_info['icon'],
            'status' => $post->post_status,
            'status_label' => $status_label,
            'created' => mysql2date(get_option('date_format'), $post->post_date),
            'edit_url' => $edit_url ?? '',
            'actions' => $actions,
        ];
    }

    protected function get_template_preview_url(WP_Post $post): string
    {
        $layout_state = get_post_meta($post->ID, '_lsd_template_layout', true);
        if (!$layout_state) return '';

        $preview_listing_id = (int) get_post_meta($post->ID, '_lsd_template_preview_listing', true);
        if (!$preview_listing_id) $preview_listing_id = $this->get_preview_listing_id($post->ID);
        if (!$preview_listing_id || get_post_type($preview_listing_id) !== LSD_Base::PTYPE_LISTING) return '';

        return esc_url(add_query_arg(
            'lsd_preview_surface',
            'card',
            wp_specialchars_decode($this->get_preview_iframe_url($post->ID, $preview_listing_id))
        ));
    }

    protected function get_editor_template_layout_data(int $post_id): string
    {
        $raw_state = get_post_meta($post_id, '_lsd_template_layout', true);
        $raw_state = is_string($raw_state) ? $raw_state : '';

        if (!$post_id || !$this->autosave_enabled($post_id)) return $raw_state;

        $current_user_id = get_current_user_id();
        if (!$current_user_id) return $raw_state;

        $autosave = wp_get_post_autosave($post_id, $current_user_id);
        if (!$autosave instanceof WP_Post || !metadata_exists('post', $autosave->ID, '_lsd_template_layout')) return $raw_state;

        $autosave_modified = (int) get_post_modified_time('U', true, $autosave);
        $post_modified = (int) get_post_modified_time('U', true, $post_id);

        if ($autosave_modified <= $post_modified) return $raw_state;

        $autosave_state = get_post_meta($autosave->ID, '_lsd_template_layout', true);

        return is_string($autosave_state) ? $autosave_state : $raw_state;
    }

    protected function get_template_layout_state(int $post_id): array
    {
        $raw_state = get_post_meta($post_id, '_lsd_template_layout', true);
        if (!$raw_state) return [];

        $state = json_decode($raw_state, true);
        return is_array($state) ? $state : [];
    }

    protected function get_template_layout_elements(int $post_id): array
    {
        $state = $this->get_template_layout_state($post_id);
        return isset($state['elements']) && is_array($state['elements']) ? $state['elements'] : [];
    }

    protected function get_template_layout_tree(int $post_id): array
    {
        $state = $this->get_template_layout_state($post_id);
        return isset($state['layout']) && is_array($state['layout']) ? $state['layout'] : [];
    }

    protected function pagination($query): string
    {
        if (!$query instanceof WP_Query || $query->max_num_pages <= 1) return '';
        $current = max(1, (int) ($query->get('paged') ?: (isset($_GET['paged']) ? $_GET['paged'] : 1)));

        return sprintf(
            '<nav class="lsd-template-pagination" aria-label="%s">%s</nav>',
            esc_attr__('Template pagination', 'listdom'),
            paginate_links([
                'base' => add_query_arg('paged', '%#%'),
                'current' => $current,
                'total' => (int) $query->max_num_pages,
                'prev_text' => __('Previous', 'listdom'),
                'next_text' => __('Next', 'listdom'),
                'type' => 'list',
            ])
        );
    }

    /*--------------------------------------------------------------
    # Modal + Meta Boxes
    --------------------------------------------------------------*/

    public function creation_modal()
    {
        if (!$this->is_template_screen() || !$this->current_user_can_create_templates()) return;

        $this->include_html_file('template-builder/modal.php', [
            'parameters' => [
                'types' => $this->template_types(),
                'nonce' => $this->get_creation_nonce(),
                'modal_id' => 'lsd-template-modal',
                'default_type' => $this->default_template_type(),
            ],
        ]);
    }

    public function save_template_meta($post_id)
    {
        $is_autosave = defined('DOING_AUTOSAVE') && DOING_AUTOSAVE;

        // Capability check
        if (!current_user_can('edit_post', $post_id)) return;

        // Nonce
        if (!isset($_POST['lsd-template-type-nonce'])) return;

        $nonce = sanitize_text_field(wp_unslash($_POST['lsd-template-type-nonce']));
        if (!wp_verify_nonce($nonce, 'lsd-template-type')) return;

        // POST
        $post = wp_unslash($_POST);

        if ($is_autosave)
        {
            // Autosave revisions are persisted via wp_creating_autosave.
            // Writing the parent post here would push draft builder changes live.
            return;
        }

        // Template type
        $types = array_keys($this->template_types());
        $type = sanitize_key($post['lsd_template_type'] ?? '');
        if (!in_array($type, $types, true)) $type = reset($types);

        update_post_meta($post_id, '_lsd_template_type', $type);

        // Preview listing
        $preview_listing = $post['lsd_template_preview_listing'] ?? 0;
        if (is_array($preview_listing)) $preview_listing = reset($preview_listing);

        $preview_listing_id = absint($preview_listing);
        if ($preview_listing_id && get_post_type($preview_listing_id) !== LSD_Base::PTYPE_LISTING) $preview_listing_id = 0;

        if ($preview_listing_id) update_post_meta($post_id, '_lsd_template_preview_listing', $preview_listing_id);
        else delete_post_meta($post_id, '_lsd_template_preview_listing');

        // Page template
        $page_templates = array_keys($this->page_templates());
        $page_template = isset($post['lsd_template_page_template']) ? sanitize_text_field($post['lsd_template_page_template']) : 'default';
        if ($page_template !== 'default' && !in_array($page_template, $page_templates, true)) $page_template = 'default';

        update_post_meta($post_id, '_wp_page_template', $page_template);

        // Builder autosave setting
        if (array_key_exists('lsd_template_autosave_status', $post))
        {
            $autosave_status = absint($post['lsd_template_autosave_status']) === 1 ? 1 : 0;
            $this->persist_autosave_setting($post_id, $autosave_status);
        }

        $this->persist_layout_state($post_id, $post);
    }

    /*--------------------------------------------------------------
    # AJAX Creation
    --------------------------------------------------------------*/

    public function ajax_create_template()
    {
        // Capability check
        if (!$this->current_user_can_create_templates()) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to create templates.', 'listdom'),]);

        // Get and sanitize POST
        $post    = wp_unslash($_POST);
        $wpnonce = isset($post['nonce']) ? sanitize_text_field($post['nonce']) : '';

        // Nonce checks
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code'    => 'NONCE_MISSING', 'message' => esc_html__('Security token is missing.', 'listdom'),]);

        if (!wp_verify_nonce($wpnonce, 'lsd-create-template')) $this->response(['success' => 0, 'code'    => 'NONCE_IS_INVALID', 'message' => esc_html__('Security token is invalid.', 'listdom'),]);

        // Data
        $title = sanitize_text_field($post['title'] ?? '');
        $type  = sanitize_key($post['type'] ?? '');
        $types = array_keys($this->template_types());

        if (!$type || !in_array($type, $types, true)) $type = reset($types);

        if (trim($title) === '') $this->response(['success' => 0, 'message' => esc_html__('Template name is required.', 'listdom'),]);

        // Insert post
        $post_id = wp_insert_post([
            'post_type'  => $this->PT,
            'post_status'=> 'draft',
            'post_title' => $title,
            'meta_input' => [
                '_lsd_template_type' => $type,
            ],
        ], true);

        if (is_wp_error($post_id) || !$post_id) $this->response(['success' => 0, 'message' => esc_html__('Unable to create template.', 'listdom'),]);

        $edit_url = get_edit_post_link($post_id, '');
        $redirect_url = $edit_url ?: $this->templates_screen_url();

        // Success response
        $this->response([
            'success'     => 1,
            'postId'      => (int) $post_id,
            'editUrl'     => $edit_url ?: '',
            'redirectUrl' => $redirect_url,
            'message'     => esc_html__('Template created successfully.', 'listdom'),
        ]);
    }

    /*--------------------------------------------------------------
    # Helpers
    --------------------------------------------------------------*/

    protected function template_types(): array
    {
        return [
            'single_listing' => ['label' => __('Single Listing', 'listdom'), 'icon' => 'wbli-file', 'description' => __('Full page layout for individual listings', 'listdom')],
            'listing_card' => ['label' => __('Listing Card', 'listdom'), 'icon' => 'wbli-card', 'description' => __('Card layout for listing grids and archives', 'listdom')],
            'info_window' => ['label' => __('Info Window', 'listdom'), 'icon' => 'wbli-location', 'description' => __('Compact layout for map info windows', 'listdom')],
        ];
    }

    protected function template_type_info($type): array
    {
        $types = $this->template_types();
        if (isset($types[$type])) return $types[$type];

        return [
            'label' => __('Unassigned', 'listdom'),
            'icon' => 'dashicons dashicons-layout',
            'description' => 'Template layout for listings'
        ];
    }

    protected function status_label($status)
    {
        $obj = get_post_status_object($status);
        return $obj ? $obj->label : ucwords(str_replace('_', ' ', $status));
    }

    /**
     * Get normalized post status options for the template builder.
     *
     * @param string $post_status       Current post status (e.g. 'draft', 'publish', 'auto-draft', etc.)
     * @param string $post_status_label Human-readable label for the current status.
     * @return array
     */
    public function get_status_options($post_status, $post_status_label): array
    {
        if ($this->current_user_can_publish_templates()) $status_options = [
            'draft'   => __('Draft', 'listdom'),
            'publish' => __('Published', 'listdom'),
            'pending' => __('Pending Review', 'listdom'),
        ];
        elseif ($this->is_publish_like_status((string) $post_status)) $status_options = [
            $post_status => $post_status_label ?: $this->status_label($post_status),
        ];
        else $status_options = [
            'draft'   => __('Draft', 'listdom'),
            'pending' => __('Pending Review', 'listdom'),
        ];

        // Ensure current status exists in case it's something different (e.g. auto-draft)
        if (!isset($status_options[$post_status])) $status_options[$post_status] = $post_status_label;

        return $status_options;
    }

    protected function default_template_type()
    {
        $keys = array_keys($this->template_types());
        return (count($keys)) ? $keys[0] : '';
    }

    protected function resolve_preview_template_type(int $template_id, string $template_type = ''): string
    {
        $types = array_keys($this->template_types());
        $template_type = sanitize_key($template_type);

        if ($template_type === '') {
            $template_type = sanitize_key((string) get_post_meta($template_id, '_lsd_template_type', true));
        }

        if (!in_array($template_type, $types, true)) $template_type = $this->default_template_type();

        return $template_type;
    }

    protected function get_creation_nonce()
    {
        if (!$this->creation_nonce) $this->creation_nonce = wp_create_nonce('lsd-create-template');

        return $this->creation_nonce;
    }

    protected function get_duplicate_link($post_id)
    {
        if (!current_user_can('edit_post', $post_id) || !$this->current_user_can_create_templates()) return '';
        $url = add_query_arg(['action' => 'duplicate_post_as_draft', 'post' => $post_id], 'admin.php');
        return wp_nonce_url($url, 'duplicate.php', 'lsd_duplicate_nonce');
    }

    protected function get_restore_link($post_id): string
    {
        if (!current_user_can('delete_post', $post_id)) return '';

        $url = add_query_arg([
            'action' => 'untrash',
            'post' => (int) $post_id,
        ], admin_url('post.php'));

        return wp_nonce_url($url, 'untrash-post_' . (int) $post_id);
    }

    protected function get_current_status_filter(): string
    {
        if (!isset($_GET['lsd_status'])) return '';

        $status = sanitize_key(wp_unslash($_GET['lsd_status']));
        return get_post_status_object($status) ? $status : '';
    }

    protected function get_current_search_query(): string
    {
        if (!isset($_GET['lsd_template_search'])) return '';

        return sanitize_text_field(wp_unslash($_GET['lsd_template_search']));
    }

    protected function templates_screen_url(array $args = [], array $remove = []): string
    {
        $base = admin_url('edit.php');
        $query_args = ['post_type' => $this->PT];

        if (!in_array('lsd_status', $remove, true))
        {
            $status = $this->get_current_status_filter();
            if ($status !== '') $query_args['lsd_status'] = $status;
        }

        if (!in_array('lsd_template_search', $remove, true))
        {
            $search = $this->get_current_search_query();
            if ($search !== '') $query_args['lsd_template_search'] = $search;
        }

        foreach ($remove as $key)
        {
            unset($query_args[$key]);
        }

        $query_args = array_merge($query_args, $args);

        return remove_query_arg('paged', add_query_arg($query_args, $base));
    }

    protected function template_status_filters(string $current_status): array
    {
        $counts = wp_count_posts($this->PT, 'readable');
        $counts_array = is_object($counts) ? array_map('intval', get_object_vars($counts)) : [];

        $status_objects = get_post_stati(['show_in_admin_status_list' => true], 'objects');

        $manual_statuses = ['draft', 'pending'];
        foreach ($manual_statuses as $st)
        {
            $obj = get_post_status_object($st);
            if ($obj) $status_objects[$st] = $obj;
        }

        $trash_object = get_post_status_object('trash');
        if ($trash_object) $status_objects['trash'] = $trash_object;

        $statuses = [];
        $total = 0;

        foreach ($status_objects as $slug => $status_obj)
        {
            if ($slug === 'trash') continue;

            $count = (int) ($counts_array[$slug] ?? 0);
            $total += $count;
        }

        $statuses[] = [
            'slug' => '',
            'label' => __('All', 'listdom'),
            'count' => $total,
            'url' => $this->templates_screen_url([], ['lsd_status']),
            'active' => ($current_status === ''),
        ];

        foreach ($status_objects as $slug => $status_obj)
        {
            $count = (int) ($counts_array[$slug] ?? 0);
            if ($count === 0 && $slug !== $current_status) continue;

            $statuses[] = [
                'slug' => $slug,
                'label' => $status_obj->label ?? ucwords(str_replace('_', ' ', $slug)),
                'count' => $count,
                'url' => $this->templates_screen_url(['lsd_status' => $slug]),
                'active' => ($slug === $current_status),
            ];
        }

        return $statuses;
    }

    public function styles($styles, $skin)
    {
        // Add Template Builder layouts as styles based on their assigned template type
        $templates = get_posts([
            'post_type'      => LSD_Base::PTYPE_TEMPLATE,
            'numberposts'    => -1,
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'fields'         => 'ids',
        ]);

        if ($skin === 'detail_types') $styles['template_builder'] = esc_html__('Template Builder', 'listdom');

        foreach ($templates as $template_id)
        {
            $template_type = get_post_meta($template_id, '_lsd_template_type', true) ?: 'single_listing';
            $title         = get_the_title($template_id);
            $label         = $title ? $title . ' (Template Builder)' : esc_html__('Template Builder', 'listdom');

            // Give template builder a custom prefix, so we can detect it in JS
            $key = 'tb_' . $template_id;

            if ($skin === 'details' && $template_type === 'single_listing')
            {
                $styles[$key] = $label;
            }
            else if (LSD_Skins::is_cardable($skin) && $template_type === 'listing_card')
            {
                $styles[$key] = $label;
            }
            else if ($skin === 'infowindow' && $template_type === 'info_window')
            {
                $styles[$key] = $label;
            }
        }

        return $styles;
    }
}
