<?php
abstract class LSD_Template extends LSD_Base
{
    const TAB_CONTENT = 'content';
    const TAB_STYLE = 'style';

    public $key;
    public $label;
    public $category = 'basic';
    public $template_types = [];
    public $icon = 'fa fa-puzzle-piece';

    protected $settings = [];
    protected $controls_sections = null;
    protected $current_controls_section = null;
    protected $current_controls_tabs = null;
    protected $current_controls_tab = null;
    protected $current_controls_tab_condition = [];

    public function __construct($settings = [])
    {
        $this->settings = is_array($settings) ? $settings : [];
    }

    /* -------------------------
     * Basic getters
     * ------------------------- */

    public function get_key(): string
    {
        return (string) $this->key;
    }

    public function get_label(): string
    {
        return (string) $this->label;
    }

    public function get_category(): string
    {
        return (string) $this->category;
    }

    public function get_template_types(): array
    {
        return (array) $this->template_types;
    }

    public function get_icon(): string
    {
        return (string) $this->icon;
    }

    /* -------------------------
     * Must be implemented by children
     * ------------------------- */

    public function style_settings(array $data): string
    {
        return $this->render_controls_tab('style', $data);
    }

    public function controls(): void
    {
    }

    public function advanced_settings(array $data): string
    {
        $sections = LSD_Template_Presets_Advanced::responsive_visibility($this->key);

        return $this->settings_sections($data, $sections);
    }

    public function element_settings(array $data): string
    {
        return $this->render_controls_tab('content', $data);
    }

    abstract public function render(array $args = []): string;

    /* -------------------------
     * Helpers for settings rendering
     * ------------------------- */

    /**
     * Create a template element instance by key.
     *
     * "title" -> class LSD_Template_Elements_Title
     *
     * @param string $key
     * @param array $settings
     * @return LSD_Template|false
     */
    public static function instance(string $key, array $settings = [])
    {
        $key   = preg_replace('/[^a-z0-9_\-]/i', '', (string) $key);
        $class = 'LSD_Template_Elements_' . ucfirst($key);

        if (!class_exists($class))
        {
            return apply_filters('lsd_addon_template_elements', false, $key, $settings);
        }

        $instance = new $class($settings);

        if ($instance instanceof self) $instance->set_settings($settings);

        return $instance;
    }

    /**
     * Render element output with canvas controls when applicable.
     *
     * @param array $args
     * @return string
     */
    public function canvas(array $args = []): string
    {
        // Always render the element itself
        $content = $this->render($args);

        $context = isset($args['context']) ? (string) $args['context'] : '';

        if (!in_array($context, ['preview', 'editor-preview'], true)) return $content;

        return $content;
    }

    /**
     * Safely read a setting value with a fallback.
     *
     * @param array $data
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    protected function get_setting_value(array $data, string $key, $default = null)
    {
        if (array_key_exists($key, $data) && $data[$key] !== '') return $data[$key];

        return $default;
    }

    public function set_settings(array $settings = []): void
    {
        $this->settings = $this->resolve_settings($settings);
    }

    public function resolve_settings(array $settings = []): array
    {
        $resolved = [
            'content' => [],
            'style' => [],
            'advanced' => [],
        ];

        if (isset($settings['content']) && is_array($settings['content'])) $resolved['content'] = $settings['content'];
        if (isset($settings['style']) && is_array($settings['style'])) $resolved['style'] = $settings['style'];
        if (isset($settings['advanced']) && is_array($settings['advanced'])) $resolved['advanced'] = $settings['advanced'];

        $defaults = $this->get_default_settings();

        foreach (['content', 'style', 'advanced'] as $scope)
        {
            $resolved[$scope] = $this->apply_default_scope_values($defaults[$scope] ?? [], $resolved[$scope]);
        }

        return $resolved;
    }

    protected function get_default_settings(): array
    {
        static $cache = [];

        $cache_key = static::class . '::' . $this->get_key();

        if (isset($cache[$cache_key])) return $cache[$cache_key];

        $defaults = [
            'content' => [],
            'style' => [],
            'advanced' => [],
        ];

        $this->collect_default_settings_from_sections($this->get_controls_sections(), $defaults);
        $this->collect_default_settings_from_sections(LSD_Template_Presets_Advanced::responsive_visibility($this->get_key()), $defaults, 'advanced');

        $cache[$cache_key] = $defaults;

        return $defaults;
    }

    protected function collect_default_settings_from_sections(array $sections, array &$defaults, string $forced_scope = ''): void
    {
        foreach ($sections as $section)
        {
            if (!is_array($section)) continue;

            $scope = $forced_scope !== '' ? $forced_scope : sanitize_key((string) ($section['tab'] ?? ''));
            if (!in_array($scope, ['content', 'style', 'advanced'], true)) continue;

            $fields = isset($section['fields']) && is_array($section['fields']) ? $section['fields'] : [];
            $defaults[$scope] = $this->apply_default_scope_values($this->collect_default_settings_from_fields($fields), $defaults[$scope]);
        }
    }

    protected function collect_default_settings_from_fields(array $fields): array
    {
        $defaults = [];

        foreach ($fields as $field)
        {
            if (!is_array($field)) continue;

            $field_id = isset($field['id']) ? sanitize_key((string) $field['id']) : '';
            if ($field_id === '') continue;

            if (array_key_exists('default', $field)) $defaults[$field_id] = $field['default'];
        }

        return $defaults;
    }

    protected function apply_default_scope_values(array $defaults, array $settings): array
    {
        foreach ($defaults as $key => $default)
        {
            if (!array_key_exists($key, $settings) || $settings[$key] === '') $settings[$key] = $default;
        }

        return $settings;
    }

    /**
     * Render grouped settings sections using config arrays.
     *
     * @param array $data
     * @param array $sections
     * @return string
     */
    protected function settings_sections(array $data, array $sections): string
    {
        if(empty($sections)) return '';

        ob_start();
        include lsd_template('template-builder/settings.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'element_key' => $this->key,
                'data'        => $data,
                'sections'    => $sections,
            ]
        );
    }

    /**
     * Build and cache controls sections.
     *
     * @return array
     */
    public function get_controls_sections(): array
    {
        if (is_array($this->controls_sections)) return $this->controls_sections;

        $this->controls_sections = [];
        $this->current_controls_section = null;
        $this->current_controls_tabs = null;
        $this->current_controls_tab = null;
        $this->current_controls_tab_condition = [];

        $this->controls();

        $this->current_controls_section = null;
        $this->current_controls_tabs = null;
        $this->current_controls_tab = null;
        $this->current_controls_tab_condition = [];

        return $this->controls_sections;
    }

    /**
     * Start a new controls section.
     *
     * @param string $id
     * @param array $args
     */
    protected function start_controls_section(string $id, array $args = []): void
    {
        $id = sanitize_key($id);
        if ($id === '') return;

        $section = $args;
        $section['fields'] = [];

        $this->controls_sections[$id] = $section;
        $this->current_controls_section = $id;
    }

    /**
     * Add a control to the current section.
     *
     * @param string $id
     * @param array $args
     */
    protected function add_control(string $id, array $args = []): void
    {
        if (!$this->current_controls_section) return;

        $args['id'] = $args['id'] ?? $id;

        if ($this->current_controls_tabs && $this->current_controls_tab)
        {
            $args['control_tabs'] = $this->current_controls_tabs;
            $args['control_tab'] = $this->current_controls_tab;
        }

        if (!empty($this->current_controls_tab_condition))
        {
            $field_condition = isset($args['condition']) && is_array($args['condition']) ? $args['condition'] : [];

            $args['condition'] = array_merge($this->current_controls_tab_condition, $field_condition);
        }

        $this->controls_sections[$this->current_controls_section]['fields'][] = $args;
    }

    /**
     * End the current controls section.
     */
    protected function end_controls_section(): void
    {
        $this->current_controls_section = null;
    }

    /**
     * Start a group of control tabs.
     *
     * @param string $id
     * @param array $args
     */
    protected function start_controls_tabs(string $id, array $args = []): void
    {
        if (!$this->current_controls_section) return;

        $id = sanitize_key($id);
        if ($id === '') return;

        $section_id = $this->current_controls_section;

        if (!isset($this->controls_sections[$section_id]['tabs']) || !is_array($this->controls_sections[$section_id]['tabs']))
        {
            $this->controls_sections[$section_id]['tabs'] = [];
        }

        if (!isset($this->controls_sections[$section_id]['tabs'][$id]))
        {
            $this->controls_sections[$section_id]['tabs'][$id] = [
                'id'   => $id,
                'tabs' => [],
            ];
        }

        $this->current_controls_tabs = $id;
    }

    /**
     * Start a control tab within the current tabs group.
     *
     * @param string $id
     * @param array $args
     */
    protected function start_controls_tab(string $id, array $args = []): void
    {
        if (!$this->current_controls_section || !$this->current_controls_tabs) return;

        $id = sanitize_key($id);
        if ($id === '') return;

        $section_id = $this->current_controls_section;
        $tabs_group = $this->current_controls_tabs;

        if (!isset($this->controls_sections[$section_id]['tabs'][$tabs_group]))
        {
            $this->controls_sections[$section_id]['tabs'][$tabs_group] = [
                'id'   => $tabs_group,
                'tabs' => [],
            ];
        }

        $condition = isset($args['condition']) && is_array($args['condition']) ? $args['condition'] : [];

        $this->controls_sections[$section_id]['tabs'][$tabs_group]['tabs'][$id] = [
            'id' => $id,
            'label' => $args['label'] ?? '',
            'condition' => $condition,
        ];

        $this->current_controls_tab = $id;
        $this->current_controls_tab_condition = $condition;
    }

    /**
     * End the current control tab.
     */
    protected function end_controls_tab(): void
    {
        $this->current_controls_tab = null;
        $this->current_controls_tab_condition = [];
    }

    /**
     * End the current control tabs group.
     */
    protected function end_controls_tabs(): void
    {
        $this->current_controls_tabs = null;
        $this->current_controls_tab = null;
        $this->current_controls_tab_condition = [];
    }

    /**
     * Render controls for a specific tab.
     *
     * @param string $tab
     * @param array $data
     * @return string
     */
    protected function render_controls_tab(string $tab, array $data): string
    {
        $sections = $this->filter_controls_sections($tab, $this->get_controls_sections());

        return $this->settings_sections($data, $sections);
    }

    /**
     * Filter controls sections for a specific tab.
     *
     * @param string $tab
     * @param array $sections
     * @return array
     */
    protected function filter_controls_sections(string $tab, array $sections): array
    {
        $filtered = [];

        foreach ($sections as $section_id => $section)
        {
            $section_tab = isset($section['tab']) ? (string) $section['tab'] : '';

            $section_condition = isset($section['condition']) && is_array($section['condition'])
                ? $section['condition']
                : [];

            $fields = isset($section['fields']) && is_array($section['fields']) ? $section['fields'] : [];
            $next_fields = [];

            foreach ($fields as $field)
            {
                $field_tab = isset($field['tab']) ? (string) $field['tab'] : '';
                $resolved_tab = $field_tab !== '' ? $field_tab : ($section_tab !== '' ? $section_tab : 'content');

                if ($resolved_tab !== $tab) continue;

                if (!empty($section_condition))
                {
                    $field_condition = isset($field['condition']) && is_array($field['condition'])
                        ? $field['condition']
                        : [];

                    $field['condition'] = array_merge($section_condition, $field_condition);
                }

                $next_fields[] = $field;
            }

            if (!$next_fields) continue;

            $next_section = $section;
            $next_section['fields'] = $next_fields;

            $filtered[$section_id] = $next_section;
        }

        return $filtered;
    }

    /* -------------------------
     * Condition helpers
     * ------------------------- */

    protected function condition_matches(array $condition, array $data): bool
    {
        if (empty($condition)) return true;

        foreach ($condition as $control_id => $expected)
        {
            $control_id = (string) $control_id;
            $current = $this->get_condition_control_value($data, $control_id);

            if (!$this->condition_value_matches($current, $expected))
            {
                return false;
            }
        }

        return true;
    }

    protected function condition_value_matches($current, $expected): bool
    {
        if (is_array($expected))
        {
            foreach ($expected as $single_expected)
            {
                if ($this->condition_value_matches($current, $single_expected))
                {
                    return true;
                }
            }

            return false;
        }

        return $this->normalize_condition_value($current) === $this->normalize_condition_value($expected);
    }

    protected function normalize_condition_value($value): string
    {
        if (is_bool($value))
        {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value))
        {
            return (string) $value;
        }

        if ($value === null)
        {
            return '';
        }

        $value = strtolower(trim((string) $value));

        if (in_array($value, ['true', 'yes', 'on', 'checked'], true))
        {
            return '1';
        }

        if (in_array($value, ['false', 'no', 'off', 'unchecked'], true))
        {
            return '0';
        }

        return $value;
    }

    protected function get_condition_control_value(array $data, string $control_id)
    {
        if (array_key_exists($control_id, $data) && $data[$control_id] !== '')
        {
            return $data[$control_id];
        }

        $field = $this->find_control_field($control_id);

        if (is_array($field) && array_key_exists('default', $field))
        {
            return $field['default'];
        }

        return null;
    }

    protected function find_control_field(string $control_id): ?array
    {
        foreach ($this->get_controls_sections() as $section)
        {
            $fields = isset($section['fields']) && is_array($section['fields']) ? $section['fields'] : [];

            foreach ($fields as $field)
            {
                if (($field['id'] ?? '') === $control_id) return $field;
            }
        }

        return null;
    }

    protected function field_initially_visible(array $data, array $field): bool
    {
        $condition = isset($field['condition']) && is_array($field['condition'])
            ? $field['condition']
            : [];

        return $this->condition_matches($condition, $data);
    }

    protected function control_condition_attrs(array $field): string
    {
        $condition = $field['condition'] ?? [];

        if (!is_array($condition) || empty($condition)) return '';

        return ' data-lsd-control-condition="' . esc_attr(wp_json_encode($condition)) . '"';
    }

    protected function control_wrapper_attrs(string $id, array $field): string
    {
        return ' data-lsd-control-id="' . esc_attr($id) . '"' . $this->control_condition_attrs($field);
    }

    /**
     * Render a single field based on its configuration.
     *
     * @param array $data
     * @param array $field
     * @return string
     */
    protected function fields(array $data, array $field): string
    {
        if(empty($field['id'])) return '';

        $id    = (string) $field['id'];
        $type  = isset($field['type']) ? (string) $field['type'] : 'text';
        $value = $this->get_setting_value($data, $id, $field['default'] ?? null);

        ob_start();
        include lsd_template('template-builder/fields.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'id' => $id,
                'type' => $type,
                'value' => $value,
            ]
        );
    }

    /**
     * Get templates by template type.
     *
     * @param string $template_type
     * @param array $args
     * @return array
     */
    public static function get_templates_by_type(string $template_type, array $args = []): array
    {
        $template_type = sanitize_key($template_type);
        if ($template_type === '') return [];

        $defaults = [
            'post_type'   => LSD_Base::PTYPE_TEMPLATE,
            'post_status' => ['publish', 'pending', 'draft'],
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
            'meta_query'  => [
                [
                    'key'   => '_lsd_template_type',
                    'value' => $template_type,
                ],
            ],
        ];

        $query_args = wp_parse_args($args, $defaults);

        return get_posts($query_args);
    }

    /* -------------------------
     * Factory + registry
     * ------------------------- */

    /**
     * Get all registered template elements.
     *
     * @return LSD_Template[]
     */
    public static function all(): array
    {
        // The list of internal keys. Must match your child class names.
        $keys = apply_filters('lsd_template_elements', [
            'container',
            'spacer',
            'heading',
            'title',
            'breadcrumb',
            'backbutton',
            'excerpt',
            'content',
            'remark',
            'categories',
            'locations',
            'features',
            'tags',
            'labels',
            'image',
            'video',
            'gallery',
            'embed',
            'price',
            'ads',
            'auction',
            'booking',
            'franchise',
            'application',
            'contact',
            'owner',
            'address',
            'map',
            'availability',
            'attributes',
            'faq',
            'share',
            'compare',
            'favorite',
            'abuse',
            'stats',
            'acf',
            'google_places',
            'cta',
            'team',
            'related',
            'discussion',
        ]);

        $elements = [];

        foreach ($keys as $key)
        {
            $element = self::instance($key);

            if ($element instanceof self) $elements[$key] = $element;
        }

        return $elements;
    }

    public static function icons($key): string
    {
        $icons = [
            // Structure
            'container'       => 'wbli-inbox',
            'spacer'          => 'wbli-subtitle',
            'heading'         => 'wbli-text-square',
            'breadcrumb'      => 'wbli-curvy-right-direction',
            'backbutton'      => 'wbli-arrow-turn-backward',

            // Main content
            'title'           => 'wbli-text-square',
            'excerpt'         => 'wbli-text-select',
            'content'         => 'wbli-subtitle',
            'remark'          => 'wbli-quotes',

            // Taxonomies
            'categories'      => 'wbli-dashboard-square-add',
            'locations'       => 'wbli-map-pinpoint',
            'features'        => 'wbli-left-to-right-list-star',
            'tags'            => 'wbli-hashtag',
            'labels'          => 'wbli-label-important',

            // Media
            'image'           => 'wbli-card',
            'video'           => 'wbli-computer-video',
            'gallery'         => 'wbli-image-composition-oval',
            'embed'           => 'wbli-second-bracket-square',

            // Commerce
            'price'           => 'wbli-hot-price',
            'ads'             => 'wbli-file-audio',
            'auction'         => 'wbli-auction',
            'booking'         => 'wbli-ticket',
            'franchise'       => 'wbli-git-fork',
            'application'     => 'wbli-job-link',
            'cta'             => 'wbli-cursor-pointer',

            // Contact and owner
            'contact'         => 'wbli-calling',
            'owner'           => 'wbli-identity-card',
            'team'            => 'wbli-add-team',
            'abuse'           => 'wbli-flag',

            // Location
            'address'         => 'wbli-map-pin',
            'map'             => 'wbli-maps-location',
            'google_places'   => 'wbli-google-places',

            // Listing data
            'availability'    => 'wbli-clipboard-clock',
            'attributes'      => 'wbli-custom-field',
            'faq'             => 'wbli-bubble-chat-question',
            'acf'             => 'wbli-acf',

            // Engagement
            'share'           => 'wbli-share',
            'compare'         => 'wbli-chart-relationship',
            'favorite'        => 'wbli-stars',
            'stats'           => 'wbli-chart-increase',
            'related'         => 'wbli-chart-relationship',
            'discussion'      => 'wbli-comment',
        ];

        return $icons[$key] ?? 'wbli-listdom-logo';

    }

    /**
     * Render a Listdom template-builder layout on the frontend or preview.
     *
     * @param int   $template_id
     * @param array $args
     *   @type bool               $apply_filters Whether to run the_content filters on the result. Default true.
     *   @type string             $context       Render context, defaults to 'frontend'.
     *   @type string             $template_type Optional template type override.
     *   @type int                $listing_id    Listing ID to use for rendering.
     *   @type LSD_Entity_Listing $listing       Listing entity to use for rendering.
     *
     * @return string
     */
    public static function get_template_builder_content_for_display(int $template_id, array $args = []): string
    {
        $template_id = (int) $template_id;
        if ($template_id <= 0) return '';

        $apply_filters = self::resolve_apply_filters($args);
        $context       = self::resolve_context($args);

        if (!self::should_render_template_builder_content($template_id, $context)) return '';

        $state = self::load_template_state($template_id);
        if (!$state) return '';

        return self::build_template_builder_content($template_id, $state, $args, $apply_filters, $context);
    }

    public static function get_template_builder_content_from_state(int $template_id, array $state, array $args = []): string
    {
        $template_id = (int) $template_id;
        if ($template_id <= 0) return '';

        $apply_filters = self::resolve_apply_filters($args);
        $context       = self::resolve_context($args);

        if (!self::should_render_template_builder_content($template_id, $context)) return '';

        $state         = self::normalize_template_state($state);

        if (!$state) return '';

        return self::build_template_builder_content($template_id, $state, $args, $apply_filters, $context);
    }

    private static function should_render_template_builder_content(int $template_id, string $context): bool
    {
        $template = get_post($template_id);
        if (!$template instanceof WP_Post || $template->post_type !== LSD_Base::PTYPE_TEMPLATE) return false;

        if ($template->post_status === 'publish') return true;

        if (class_exists('LSD_PTypes_Template') && method_exists('LSD_PTypes_Template', 'get_active_preview_request'))
        {
            $preview_request = LSD_PTypes_Template::get_active_preview_request();
            if ((int) ($preview_request['template_id'] ?? 0) === $template_id) return true;
        }

        return in_array($context, ['preview', 'editor-preview'], true) && current_user_can('edit_post', $template_id);
    }

    private static function build_template_builder_content(int $template_id, array $state, array $args, bool $apply_filters, string $context): string
    {
        $elements_map = $state['elements'];
        $layout_tree  = $state['layout'];

        $stored_type = (string) get_post_meta($template_id, '_lsd_template_type', true);
        $stored_type = sanitize_key($stored_type);

        $requested_type = isset($args['template_type']) ? sanitize_key($args['template_type']) : '';
        $actual_type = $stored_type !== '' ? $stored_type : 'single_listing';

        $is_preview_context = in_array($context, ['preview', 'editor-preview'], true);

        if (!$is_preview_context && $requested_type !== '' && $requested_type !== $actual_type) return '';

        $template_type = $requested_type !== '' ? $requested_type : $actual_type;
        $listing_id    = self::resolve_listing_id($args);

        $render_args = [
            'listing_id'    => $listing_id,
            'template_type' => $template_type,
            'context'       => $context,
        ];

        $html = self::layout_tree($layout_tree, $elements_map, $render_args);
        $html = self::inject_css($html, $template_id, $elements_map, $layout_tree, $context);

        if ($apply_filters) $html = self::apply_the_content_filters($html);

        return $html;
    }

    public static function is_template_builder_edit_page(): bool
    {
        if (!is_admin()) return false;

        $screen = get_current_screen();

        if (!$screen instanceof WP_Screen) return false;

        return $screen->base === 'post'
            && $screen->post_type === 'listdom-template';
    }

    public static function get_template_builder_preview_listing_id(): int
    {
        static $preview_listing_id = null;

        if ($preview_listing_id !== null) return $preview_listing_id;

        $preview_listing_id = 0;

        if (isset($_REQUEST['lsd_preview_listing']))
        {
            $preview_listing_id = absint(wp_unslash($_REQUEST['lsd_preview_listing']));
        }
        elseif (isset($_POST['preview_listing']))
        {
            $preview_listing_id = absint(wp_unslash($_POST['preview_listing']));
        }

        if (self::is_valid_template_builder_preview_listing($preview_listing_id))
        {
            return $preview_listing_id;
        }

        $template_id = self::get_template_builder_post_id();

        if ($template_id)
        {
            $preview_listing_id = (int) get_post_meta($template_id, '_lsd_template_preview_listing', true);
        }

        if (!self::is_valid_template_builder_preview_listing($preview_listing_id))
        {
            $preview_listing_id = 0;
        }

        if (!$preview_listing_id)
        {
            $ids = get_posts([
                'post_type'      => LSD_Base::PTYPE_LISTING,
                'posts_per_page' => 1,
                'post_status'    => ['publish', 'pending', 'draft', 'future'],
                'fields'         => 'ids',
                'no_found_rows'  => true,
            ]);

            $preview_listing_id = (!empty($ids) && is_array($ids)) ? (int) $ids[0] : 0;
        }

        return $preview_listing_id;
    }

    private static function get_template_builder_post_id(): int
    {
        foreach (['lsd_template_id', 'post_id', 'post'] as $key)
        {
            if (!isset($_REQUEST[$key])) continue;

            $post_id = absint(wp_unslash($_REQUEST[$key]));
            if ($post_id && get_post_type($post_id) === LSD_Base::PTYPE_TEMPLATE) return $post_id;
        }

        if (isset($GLOBALS['post']) && $GLOBALS['post'] instanceof WP_Post)
        {
            $post = $GLOBALS['post'];
            if ($post->post_type === LSD_Base::PTYPE_TEMPLATE) return (int) $post->ID;
        }

        return 0;
    }

    private static function is_valid_template_builder_preview_listing(int $listing_id): bool
    {
        return $listing_id > 0 && get_post_type($listing_id) === LSD_Base::PTYPE_LISTING;
    }

    /* -------------------------------------------------------------------------
     * State loading / validation
     * ---------------------------------------------------------------------- */

    private static function load_template_state(int $template_id): array
    {
        $raw_state = get_post_meta($template_id, '_lsd_template_layout', true);
        if (!$raw_state) return [];

        $state = json_decode($raw_state, true);

        return self::normalize_template_state($state);
    }

    private static function normalize_template_state($state): array
    {
        if (!is_array($state)) return [];

        $layout   = (isset($state['layout']) && is_array($state['layout'])) ? $state['layout'] : [];
        $elements = (isset($state['elements']) && is_array($state['elements'])) ? $state['elements'] : [];

        if (!$layout || !$elements) return [];

        return [
            'layout'   => $layout,
            'elements' => $elements,
        ];
    }

    /* -------------------------------------------------------------------------
     * Args resolvers
     * ---------------------------------------------------------------------- */

    private static function resolve_apply_filters(array $args): bool
    {
        return !isset($args['apply_filters']) || (bool) $args['apply_filters'];
    }

    private static function resolve_context(array $args): string
    {
        return isset($args['context']) ? (string) $args['context'] : 'frontend';
    }

    private static function resolve_listing_id(array $args): int
    {
        if (isset($args['listing']) && $args['listing'] instanceof LSD_Entity_Listing)
        {
            return (int) $args['listing']->id();
        }

        if (isset($args['listing_id']))
        {
            return (int) $args['listing_id'];
        }

        if (isset($GLOBALS['post']) && $GLOBALS['post'] instanceof WP_Post)
        {
            return (int) $GLOBALS['post']->ID;
        }

        return 0;
    }

    /* -------------------------------------------------------------------------
     * Rendering
     * ---------------------------------------------------------------------- */

    private static function layout_tree(array $layout_tree, array $elements_map, array $render_args): string
    {
        $html = '';

        foreach ($layout_tree as $row)
        {
            $html .= self::node($row, $elements_map, $render_args);
        }

        return $html;
    }

    private static function node($node, array $elements_map, array $render_args): string
    {
        if (!is_array($node)) return '';

        $element_id   = sanitize_key($node['id'] ?? '');
        $element_type = sanitize_key($node['type'] ?? '');
        if (!$element_id || !$element_type) return '';

        $element_data = $elements_map[$element_id] ?? [];
        $settings     = (isset($element_data['settings']) && is_array($element_data['settings'])) ? $element_data['settings'] : [];

        /** @var LSD_Template|false $element */
        $element = LSD_Template::instance($element_type, $settings);
        if (!$element) return '';

        $settings = $element->resolve_settings($settings);

        if (!self::is_element_supported(
            $element,
            (string) ($render_args['template_type'] ?? ''),
            (string) ($render_args['context'] ?? '')
        ))
        {
            return '';
        }

        $children_html = self::children($node['children'] ?? null, $elements_map, $render_args);

        $args = array_merge($render_args, [
            'settings' => $settings,
            'children' => $children_html,
        ]);

        $content = $element->render($args);
        if (trim((string) $content) === '') return '';

        return self::wrap_element($element_id, $content);
    }

    private static function children($children, array $elements_map, array $render_args): string
    {
        if (empty($children) || !is_array($children)) return '';

        $html = '';
        foreach ($children as $child_node) $html .= self::node($child_node, $elements_map, $render_args);

        return $html;
    }

    private static function is_element_supported($element, string $template_type, string $context = ''): bool
    {
        $supported_types = array_values(array_filter(array_map(
            'sanitize_key',
            (array) $element->get_template_types()
        )));

        return $template_type === '' || empty($supported_types) || in_array($template_type, $supported_types, true);
    }

    private static function wrap_element(string $element_id, string $content): string
    {
        $wrapper_class = class_exists('LSD_Template_Generator_CSS')
            ? LSD_Template_Generator_CSS::wrapper_class($element_id)
            : 'lsd-template-builder-element-' . sanitize_html_class($element_id);

        return '<div class="' . esc_attr($wrapper_class) . '" data-lsd-element-id="' . esc_attr($element_id) . '">' . $content . '</div>';
    }

    /* -------------------------------------------------------------------------
     * CSS
     * ---------------------------------------------------------------------- */

    private static function inject_css(string $html, int $template_id, array $elements_map, array $layout_tree, string $context): string
    {
        if (!class_exists('LSD_Template_Generator_CSS')) return $html;

        $style_markup = LSD_Template_Generator_CSS::load_post_css($template_id, [
            'elements' => $elements_map,
            'layout'   => $layout_tree,
        ], $context);

        return $style_markup . $html;
    }

    /* -------------------------------------------------------------------------
     * Filters
     * ---------------------------------------------------------------------- */

    private static function apply_the_content_filters(string $html): string
    {
        $wpautop_priority = has_filter('the_content', 'wpautop');
        $shortcode_unautop_priority = has_filter('the_content', 'shortcode_unautop');

        if (false !== $wpautop_priority) remove_filter('the_content', 'wpautop', $wpautop_priority);
        if (false !== $shortcode_unautop_priority) remove_filter('the_content', 'shortcode_unautop', $shortcode_unautop_priority);

        $html = apply_filters('the_content', $html);

        if (false !== $wpautop_priority) add_filter('the_content', 'wpautop', $wpautop_priority);
        if (false !== $shortcode_unautop_priority) add_filter('the_content', 'shortcode_unautop', $shortcode_unautop_priority);

        return (string) $html;
    }
    /* -------------------------
     * Category labels & grouping
     * ------------------------- */

    /**
     * Get human-readable label for a category key.
     *
     * @param string $category_key
     * @return string
     */
    public static function category_label(string $category_key): string
    {
        $labels = [
            'layout'        => esc_html__('Layout', 'listdom'),
            'general'       => esc_html__('General', 'listdom'),
            'basic'         => esc_html__('Basic', 'listdom'),
            'taxonomies'    => esc_html__('Taxonomies', 'listdom'),
            'media'         => esc_html__('Media', 'listdom'),
            'commerce'      => esc_html__('Commerce', 'listdom'),
            'contact_owner' => esc_html__('Contact & Owner', 'listdom'),
            'location'      => esc_html__('Location', 'listdom'),
            'advanced'      => esc_html__('Advanced', 'listdom'),
        ];

        if (isset($labels[$category_key])) return $labels[$category_key];

        return esc_html(ucwords(str_replace('_', ' ', $category_key)));
    }

    protected static function element_sort_order(string $key): int
    {
        static $order = [
            'container'     => 10,
            'spacer'        => 20,
            'heading'       => 30,
            'title'         => 40,
            'excerpt'       => 50,
            'content'       => 60,
            'remark'        => 70,
            'image'         => 80,
            'gallery'       => 90,
            'video'         => 100,
            'embed'         => 110,
            'categories'    => 120,
            'locations'     => 130,
            'features'      => 140,
            'tags'          => 150,
            'labels'        => 160,
            'price'         => 170,
            'cta'           => 180,
            'contact'       => 190,
            'owner'         => 200,
            'address'       => 210,
            'map'           => 220,
            'availability'  => 230,
            'attributes'    => 240,
            'faq'           => 250,
            'share'         => 260,
            'breadcrumb'    => 270,
            'backbutton'    => 280,
            'related'       => 290,
            'abuse'         => 300,
            'compare'       => 310,
            'favorite'      => 320,
            'ads'           => 900,
            'auction'       => 910,
            'booking'       => 920,
            'franchise'     => 930,
            'application'   => 940,
            'stats'         => 950,
            'acf'           => 960,
            'google_places' => 970,
            'team'          => 980,
            'discussion'    => 990,
        ];

        return $order[$key] ?? 1000;
    }

    protected static function category_sort_order(string $category_key): int
    {
        static $order = [
            'layout'        => 10,
            'general'       => 20,
            'basic'         => 30,
            'media'         => 40,
            'taxonomies'    => 50,
            'commerce'      => 60,
            'contact_owner' => 70,
            'location'      => 80,
            'advanced'      => 90,
        ];

        return $order[$category_key] ?? 1000;
    }

    /**
     * Get elements grouped by category and filtered by template type.
     *
     * @param string $template_type
     * @return array
     */
    public static function grouped_elements(string $template_type = ''): array
    {
        $grouped = [];

        $all = self::all();

        foreach ($all as $element)
        {
            if (!$element instanceof self) continue;

            $supported_types = $element->get_template_types();

            // If the element declares supported types, filter by current template type.
            if (!empty($supported_types) && $template_type && !in_array($template_type, $supported_types, true)) continue;

            $category = $element->get_category() ?: 'advanced';

            if (!isset($grouped[$category])) $grouped[$category] = [];

            $grouped[$category][] = [
                'key'   => $element->get_key(),
                'label' => $element->get_label(),
                'icon'  => $element->get_icon(),
                'sort'  => self::element_sort_order($element->get_key()),
            ];
        }

        uksort($grouped, static function ($left, $right) {
            return self::category_sort_order((string) $left) <=> self::category_sort_order((string) $right);
        });

        foreach ($grouped as $category => $items)
        {
            usort($items, static function (array $left, array $right) {
                $priority = ((int) ($left['sort'] ?? 1000)) <=> ((int) ($right['sort'] ?? 1000));
                if ($priority !== 0) return $priority;

                return strcmp((string) ($left['label'] ?? ''), (string) ($right['label'] ?? ''));
            });

            $grouped[$category] = array_map(static function (array $item) {
                unset($item['sort']);
                return $item;
            }, $items);
        }

        return apply_filters('lsd_template_grouped_elements', $grouped, $template_type);
    }

    /**
     * @param string $content
     * @param LSD_Template $element
     * @param array $args
     * @return mixed|void
     */
    final protected function content(string $content, LSD_Template $element, array $args = [])
    {
        // Hook Name
        $hook = strtolower(get_called_class()) . '_content'; // e.g.

        // Filter the Results
        return apply_filters($hook, $content, $element, $args);
    }

    protected function template_field_input_id(string $id, string $suffix = ''): string
    {
        $input_id = 'lsd_template_elements_' . esc_attr($this->key) . '_' . sanitize_key($id);

        if ($suffix !== '') $input_id .= '_' . sanitize_key($suffix);

        return $input_id;
    }

    protected function template_field_input_name(string $id, string $suffix = ''): string
    {
        $name = 'lsd[template_elements][' . esc_attr($this->key) . '][' . esc_attr($id) . ']';

        if ($suffix !== '') $name .= '[' . esc_attr($suffix) . ']';

        return $name;
    }

    protected function template_field_base_args(string $id, array $field, $value, $default = null): array
    {
        $base = array_merge($field, [
            'id'      => $this->template_field_input_id($id),
            'name'    => $this->template_field_input_name($id),
            'value'   => $value,
            'default' => $default,
        ]);

        if (!empty($field['multiple']))
        {
            $base['name'] .= '[]';
            $base['attributes'] = isset($base['attributes']) && is_array($base['attributes']) ? $base['attributes'] : [];

            if (!isset($base['attributes']['multiple'])) $base['attributes']['multiple'] = 'multiple';

            $base['class'] = trim((isset($base['class']) ? (string) $base['class'] : '') . ' lsd-template-editor-select2');
        }

        return $base;
    }

    protected function template_field_label_html(string $label, string $for = '', string $class = 'lsd-fields-label-tiny'): string
    {
        if ($label === '') return '';

        $for_attr = $for !== '' ? ' for="' . esc_attr($for) . '"' : '';

        return '<label class="' . esc_attr($class) . '"' . $for_attr . '>' . esc_html($label) . '</label>';
    }

    protected function template_field_description_html(string $description): string
    {
        if ($description === '') return '';

        return '<p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">' . esc_html($description) . '</p>';
    }

    protected function template_field_is_iconset(string $type): bool
    {
        return strncmp($type, 'iconset_', 8) === 0;
    }

    protected function template_field_devices(): array
    {
        return ['desktop' => 'desktop', 'tablet'  => 'tablet', 'mobile'  => 'mobile',];
    }

    protected function template_field_is_responsive_value($value): bool
    {
        return is_array($value) && (isset($value['desktop']) || isset($value['tablet']) || isset($value['mobile']));
    }

    protected function template_field_is_empty_value($value): bool
    {
        return $value === null || $value === '';
    }

    protected function template_field_resolve_device_value($source, string $device)
    {
        if (!is_array($source)) return $source;

        if (!$this->template_field_is_responsive_value($source)) return $source;

        $desktop_value = $source['desktop'] ?? ($source['tablet'] ?? ($source['mobile'] ?? null));
        $device_value = $source[$device] ?? null;

        if ($device !== 'desktop' && $this->template_field_is_empty_value($device_value)) $device_value = $desktop_value;

        if (is_array($desktop_value) && is_array($device_value))
        {
            $merged = $desktop_value;

            foreach ($device_value as $key => $val)
            {
                if ($this->template_field_is_empty_value($val)) continue;

                $merged[$key] = $val;
            }

            return $merged;
        }

        return $device_value;
    }

    protected function template_field_device_context(string $id, array $base, $value, $default, string $device_key): array
    {
        $has_responsive_value = $this->template_field_is_responsive_value($value);
        $has_responsive_default = $this->template_field_is_responsive_value($default);

        $desktop_value = $has_responsive_value && is_array($value) && array_key_exists('desktop', $value) ? $value['desktop'] : null;

        $device_has_explicit = $has_responsive_value && is_array($value) && array_key_exists($device_key, $value) && ($device_key === 'desktop' || !array_key_exists('desktop', $value) || $value[$device_key] !== $desktop_value);

        $desktop_resolved = $has_responsive_value ? $this->template_field_resolve_device_value($value, 'desktop') : $value;

        $device_value = $has_responsive_value ? $this->template_field_resolve_device_value($value, $device_key) : $value;

        $device_default = $has_responsive_default ? $this->template_field_resolve_device_value($default, $device_key) : $default;

        $device_placeholder = '';

        if ($device_key !== 'desktop' && !$device_has_explicit && is_scalar($desktop_resolved))
        {
            $device_value = '';
            $device_placeholder = trim((string) $desktop_resolved);
        }

        $device_base = array_merge($base, [
            'id'      => $this->template_field_input_id($id, $device_key),
            'name'    => $this->template_field_input_name($id, $device_key),
            'value'   => $device_value,
            'default' => $device_default,
        ]);

        if ($device_placeholder !== '' && empty($device_base['placeholder'])) $device_base['placeholder'] = $device_placeholder;

        return [
            'args' => $device_base,
            'has_explicit_value' => $device_has_explicit,
        ];
    }

    protected function template_field_form_html(string $type, array $args): string
    {
        if ($type === 'iconpicker')
        {
            $class = isset($args['class']) ? trim((string) $args['class']) : '';
            if (!preg_match('/(^|\s)lsd-iconpicker(\s|$)/', $class)) $args['class'] = trim($class . ' lsd-iconpicker');
        }

        if (method_exists(LSD_Form::class, $type)) return LSD_Form::$type($args);

        return LSD_Form::text($args);
    }

    protected function template_field_repeater_items($value, $default): array
    {
        $items = is_array($value) ? $value : [];

        if (!$items && is_array($default)) $items = $default;

        return array_values(array_filter($items, static function ($item) {
            return is_array($item);
        }));
    }

    protected function template_field_repeater_base_name(string $id): string
    {
        return $this->template_field_input_name($id);
    }

    protected function template_field_repeater_base_id(string $id): string
    {
        return $this->template_field_input_id($id);
    }

    protected function template_field_repeater_subfield_condition_attrs(array $subfield, string $sub_id): string
    {
        $attrs = ' data-lsd-repeater-control-id="' . esc_attr($sub_id) . '"';

        $condition = isset($subfield['condition']) && is_array($subfield['condition']) ? $subfield['condition'] : [];

        if (!empty($condition)) $attrs .= ' data-lsd-repeater-control-condition="' . esc_attr(wp_json_encode($condition)) . '"';

        return $attrs;
    }

    protected function template_field_repeater_subfield_html(array $subfield, array $item_value, $index, string $base_name, string $base_id): string
    {
        $sub_id = isset($subfield['id']) ? sanitize_key((string) $subfield['id']) : '';
        if ($sub_id === '') return '';

        $sub_type = isset($subfield['type']) ? (string) $subfield['type'] : 'text';
        $sub_label = isset($subfield['label']) ? (string) $subfield['label'] : '';
        $sub_default = $subfield['default'] ?? null;

        $input_id = $base_id . '_' . $index . '_' . $sub_id;
        $name = $base_name . '[' . $index . '][' . $sub_id . ']';

        $sub_args = array_merge($subfield, [
            'id'    => $input_id,
            'name'  => $name,
            'value' => $item_value[$sub_id] ?? $sub_default,
        ]);

        $condition_attrs = ' data-lsd-control-id="' . esc_attr($sub_id) . '"';

        if (isset($subfield['condition']) && is_array($subfield['condition'])) $condition_attrs .= ' data-lsd-control-condition="' . esc_attr(wp_json_encode($subfield['condition'])) . '"';

        return '<div class="lsd-template-editor-repeater__field lsd-template-editor-settings-field-' . esc_attr($sub_type) . '"' . $condition_attrs . '>'
            . $this->template_field_label_html($sub_label, $input_id)
            . $this->template_field_form_html($sub_type, $sub_args)
            . '</div>';
    }

    protected function enabled(array $settings, string $key, bool $default = false): int
    {
        if (!array_key_exists($key, $settings)) return $default ? 1 : 0;

        return !empty($settings[$key]) ? 1 : 0;
    }
}
