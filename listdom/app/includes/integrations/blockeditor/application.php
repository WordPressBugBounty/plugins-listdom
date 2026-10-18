<?php

/** Dynamic application blocks backed by Listdom's existing shortcode renderers. */
class LSD_Integrations_Blockeditor_Application
{
    public static function definitions(): array
    {
        $settings = [
            'search' => [
                'title' => esc_html__('Listdom Search', 'listdom'),
                'icon' => 'search',
                'attributes' => ['searchId' => ['type' => 'integer', 'default' => 0]],
                'message' => esc_html__('Select a saved Listdom search form.', 'listdom'),
                'missing' => esc_html__('This search form is no longer available. Select another form.', 'listdom'),
            ],
            'add-listing' => [
                'title' => esc_html__('Listdom Add Listing', 'listdom'),
                'icon' => 'plus-alt2',
                'message' => esc_html__('The add listing form appears on the published page.', 'listdom'),
            ],
            'dashboard' => [
                'title' => esc_html__('Listdom Dashboard', 'listdom'),
                'icon' => 'dashboard',
                'message' => esc_html__('The dashboard appears on the published page for permitted users.', 'listdom'),
            ],
            'auth' => [
                'title' => esc_html__('Listdom Authentication', 'listdom'),
                'icon' => 'admin-users',
                'attributes' => ['authLayout' => ['type' => 'string', 'default' => 'inline']],
                'message' => esc_html__('The login and registration forms appear on the published page.', 'listdom'),
                'controls' => [[
                    'key' => 'authLayout',
                    'label' => esc_html__('Layout', 'listdom'),
                    'type' => 'select',
                    'options' => [
                        ['label' => esc_html__('Inline', 'listdom'), 'value' => 'inline'],
                        ['label' => esc_html__('Dropdown', 'listdom'), 'value' => 'dropdown'],
                    ],
                ]],
            ],
            'profile' => [
                'title' => esc_html__('Listdom Profile', 'listdom'),
                'icon' => 'id',
                'message' => esc_html__('The relevant user profile appears on the published page.', 'listdom'),
            ],
            'checkout' => [
                'title' => esc_html__('Listdom Checkout', 'listdom'),
                'icon' => 'cart',
                'message' => esc_html__('Checkout appears on the published page when a cart is available.', 'listdom'),
            ],
            'order-summary' => [
                'title' => esc_html__('Listdom Order Summary', 'listdom'),
                'icon' => 'media-document',
                'attributes' => ['showInvoice' => ['type' => 'boolean', 'default' => true]],
                'message' => esc_html__('The order summary appears on the published page after checkout.', 'listdom'),
                'controls' => [[
                    'key' => 'showInvoice',
                    'label' => esc_html__('Show Invoice Button', 'listdom'),
                    'type' => 'toggle',
                ]],
            ],
            'simple-map' => [
                'title' => esc_html__('Listdom Simple Map', 'listdom'),
                'icon' => 'location-alt',
                'attributes' => [
                    'mapAddress' => ['type' => 'string', 'default' => ''],
                    'mapLat' => ['type' => 'number'],
                    'mapLng' => ['type' => 'number'],
                    'mapZoom' => ['type' => 'integer', 'default' => 14],
                ],
                'message' => esc_html__('The configured map appears on the published page.', 'listdom'),
                'controls' => [
                    ['key' => 'mapAddress', 'label' => esc_html__('Address', 'listdom'), 'type' => 'text'],
                    ['key' => 'mapLat', 'label' => esc_html__('Latitude', 'listdom'), 'type' => 'number'],
                    ['key' => 'mapLng', 'label' => esc_html__('Longitude', 'listdom'), 'type' => 'number'],
                    ['key' => 'mapZoom', 'label' => esc_html__('Zoom Level', 'listdom'), 'type' => 'integer', 'min' => 1, 'max' => 20],
                ],
            ],
        ];

        $definitions = [];
        foreach ($settings as $kind => $setting)
        {
            // Pro registers the add-listing shortcode only when the feature is available.
            if ($kind === 'add-listing' && !shortcode_exists('listdom-add-listing')) continue;

            $definitions[] = [
                'name' => 'listdom/' . $kind,
                'title' => $setting['title'],
                'icon' => $setting['icon'],
                'category' => 'listdom',
                'attributes' => $setting['attributes'] ?? [],
                'supports' => ['html' => false, 'customClassName' => true],
                'render_callback' => [self::class, str_replace('-', '_', $kind)],
                'editor_style' => 'lsd-blockeditor',
                'editor_dependencies' => ['lsd-blockeditor-app'],
                'editor' => [
                    'type' => 'application',
                    'kind' => $kind,
                    'panel' => esc_html__('Listdom Settings', 'listdom'),
                    'message' => $setting['message'],
                    'missing' => $setting['missing'] ?? '',
                    'controls' => $setting['controls'] ?? [],
                    'searchLabel' => esc_html__('Saved Search Form', 'listdom'),
                    'searchPrompt' => esc_html__('Select a search form', 'listdom'),
                    'searchLoading' => esc_html__('Loading saved search forms…', 'listdom'),
                    'searchUnavailable' => esc_html__('Saved search forms could not be loaded.', 'listdom'),
                ],
            ];
        }

        return $definitions;
    }

    public static function searches(): array
    {
        $posts = get_posts([
            'post_type' => LSD_Base::PTYPE_SEARCH,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'no_found_rows' => true,
        ]);

        $searches = [];
        foreach ($posts as $post) $searches[] = ['id' => (int) $post->ID, 'title' => get_the_title($post)];
        return $searches;
    }

    public static function search($attributes): string
    {
        return self::output('search', $attributes);
    }

    public static function add_listing($attributes): string
    {
        return self::output('add-listing', $attributes);
    }

    public static function dashboard($attributes): string
    {
        return self::output('dashboard', $attributes);
    }

    public static function auth($attributes): string
    {
        return self::output('auth', $attributes);
    }

    public static function profile($attributes): string
    {
        return self::output('profile', $attributes);
    }

    public static function checkout($attributes): string
    {
        return self::output('checkout', $attributes);
    }

    public static function order_summary($attributes): string
    {
        return self::output('order-summary', $attributes);
    }

    public static function simple_map($attributes): string
    {
        return self::output('simple-map', $attributes);
    }

    private static function output(string $kind, $attributes): string
    {
        $attributes = is_array($attributes) ? $attributes : [];
        $preview = defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['lsd_editor_preview']);
        if (!$preview && is_admin() && function_exists('get_current_screen'))
        {
            $screen = get_current_screen();
            $preview = $screen && method_exists($screen, 'is_block_editor') && $screen->is_block_editor();
        }

        $tags = [
            'search' => 'listdom-search',
            'add-listing' => 'listdom-add-listing',
            'dashboard' => 'listdom-dashboard',
            'auth' => 'listdom-auth',
            'profile' => 'listdom-profile',
            'checkout' => 'listdom-checkout',
            'order-summary' => 'listdom-order-summary',
        ];
        if ($kind === 'simple-map') $available = class_exists(LSD_Shortcodes_Simplemap::class);
        else $available = isset($tags[$kind]) && shortcode_exists($tags[$kind]);
        if (!$available)
        {
            return $preview ? self::placeholder(__('This Listdom feature is not available.', 'listdom')) : '';
        }

        $id = 0;

        if ($kind === 'search')
        {
            $id = isset($attributes['searchId']) ? absint($attributes['searchId']) : 0;
            $search = $id ? get_post($id) : null;
            if (!$search || $search->post_type !== LSD_Base::PTYPE_SEARCH || $search->post_status !== 'publish')
            {
                if ($preview) return self::placeholder($id ? __('This search form is no longer available. Select another form.', 'listdom') : __('Select a saved Listdom search form.', 'listdom'));
                return '';
            }
        }
        else if ($preview)
        {
            $messages = [
                'add-listing' => __('The add listing form appears on the published page.', 'listdom'),
                'dashboard' => __('The dashboard appears on the published page for permitted users.', 'listdom'),
                'auth' => __('The login and registration forms appear on the published page.', 'listdom'),
                'profile' => __('The relevant user profile appears on the published page.', 'listdom'),
                'checkout' => __('Checkout appears on the published page when a cart is available.', 'listdom'),
                'order-summary' => __('The order summary appears on the published page after checkout.', 'listdom'),
                'simple-map' => __('The configured map appears on the published page.', 'listdom'),
            ];
            return self::placeholder($messages[$kind] ?? '');
        }

        if ($kind === 'simple-map')
        {
            $address = sanitize_text_field($attributes['mapAddress'] ?? '');
            $has_coordinates = isset($attributes['mapLat'], $attributes['mapLng'])
                && is_numeric($attributes['mapLat']) && is_numeric($attributes['mapLng']);
            if ($address === '' && !$has_coordinates) return '';
            $atts = [
                'address' => $address,
                'zoomlevel' => max(1, min(20, (int) ($attributes['mapZoom'] ?? 14))),
            ];
            if ($has_coordinates)
            {
                $atts['lat'] = (float) $attributes['mapLat'];
                $atts['lng'] = (float) $attributes['mapLng'];
            }
        }
        else
        {
            $shortcodes = [
                'search' => '[listdom-search id="' . $id . '"]',
                'add-listing' => '[listdom-add-listing]',
                'dashboard' => '[listdom-dashboard]',
                'auth' => '[listdom-auth layout="' . (($attributes['authLayout'] ?? 'inline') === 'dropdown' ? 'dropdown' : 'inline') . '"]',
                'profile' => '[listdom-profile]',
                'checkout' => '[listdom-checkout]',
                'order-summary' => '[listdom-order-summary invoice="' . (!isset($attributes['showInvoice']) || $attributes['showInvoice'] ? '1' : '0') . '"]',
            ];
            $shortcode = $shortcodes[$kind] ?? '';
            if ($shortcode === '') return '';
        }

        $styles_before = wp_styles()->queue;
        ob_start();
        $html = $kind === 'simple-map' ? (new LSD_Shortcodes_Simplemap())->output($atts) : do_shortcode($shortcode);
        $html = (string) ob_get_clean() . (string) $html;
        if (trim($html) === '') return '';
        if ($preview) return $html;

        $class = 'wp-block-listdom-' . $kind;
        if (function_exists('get_block_wrapper_attributes')) $wrapper = get_block_wrapper_attributes(['class' => $class]);
        else
        {
            if (!empty($attributes['className'])) $class .= ' ' . $attributes['className'];
            $wrapper = 'class="' . esc_attr($class) . '"';
        }

        return LSD_Integrations_Blockeditor_Assets::frontend($styles_before) . '<div ' . $wrapper . '>' . $html . '</div>';
    }

    private static function placeholder(string $message): string
    {
        return '<div class="lsd-listing-block-placeholder">' . esc_html($message) . '</div>';
    }
}
