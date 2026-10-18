<?php

class LSD_Integrations_BE extends LSD_Integrations
{
    private $settings;
    private $registry;

    public function __construct()
    {
        // Listdom Settings
        $this->settings = LSD_Options::settings();

        $this->registry = new LSD_Integrations_Blockeditor_Registry();
    }

    public function init()
    {
        // Block Editor is not Available!
        if (!function_exists('register_block_type')) return;

        // WordPress 5.8 replaced block_categories with block_categories_all.
        $category_hook = version_compare(get_bloginfo('version'), '5.8', '<') ? 'block_categories' : 'block_categories_all';
        add_filter($category_hook, [$this, 'category'], 9999);
        add_filter('lsd_block_editor_blocks', [$this, 'blocks']);
        add_filter('lsd_is_listdom_page', [$this, 'layout_page']);
        add_filter('render_block', [$this, 'legacy_shortcode'], 10, 2);

        // Core renders post_content with do_blocks at priority 9.
        add_filter('the_content', [LSD_Integrations_Blockeditor_Listing::class, 'begin_content'], 7);
        add_filter('the_content', [LSD_Integrations_Blockeditor_Listing::class, 'end_content'], 10);
        add_action('init', [$this, 'register_blocks'], 20);
        add_action('rest_api_init', [$this, 'editor_routes']);
        add_action('enqueue_block_editor_assets', [$this, 'assets']);
        add_action('enqueue_block_assets', [$this, 'content_assets']);

        foreach (['listing', 'shortcode', 'search'] as $post_type)
        {
            add_filter('lsd_ptype_' . $post_type . '_args', [$this, 'enable_be'], 9999);
        }

        // Block Editor Status for Taxonomies
        foreach (['location', 'tag', 'feature', 'label'] as $taxonomy)
        {
            add_filter('lsd_taxonomy_' . $taxonomy . '_args', [$this, 'enable_be'], 9999);
        }
    }

    public function category($categories): array
    {
        $existing = array_column($categories, 'slug');
        $groups = [
            ['slug' => 'listdom-single-listing', 'title' => esc_html__('Single Listing', 'listdom'), 'icon' => 'list-view'],
            ['slug' => 'listdom', 'title' => esc_html__('Listdom', 'listdom'), 'icon' => 'list-view'],
        ];

        foreach (array_reverse($groups) as $group)
        {
            if (!in_array($group['slug'], $existing, true)) array_unshift($categories, $group);
        }

        return $categories;
    }

    public function register_blocks(): void
    {
        // Block editor styles reach iframed editors on WordPress versions before 6.3.
        wp_register_style('lsd-frontend', $this->lsd_asset_url('css/frontend.min.css'), [], LSD_Assets::version());
        $styles = ['lsd-frontend'];
        if (is_rtl())
        {
            wp_register_style('lsd-frontend-rtl', $this->lsd_asset_url('css/frontend-rtl.min.css'), ['lsd-frontend'], LSD_Assets::version());
            $styles[] = 'lsd-frontend-rtl';
        }
        if (!isset($this->settings['fontawesome_status']) || $this->settings['fontawesome_status'])
        {
            wp_register_style('fontawesome', $this->lsd_asset_url('packages/font-awesome/css/font-awesome.min.css'), [], LSD_Assets::version());
            $styles[] = 'fontawesome';
        }
        wp_register_style('lsd-blockeditor', $this->lsd_asset_url('css/blockeditor.min.css'), $styles, LSD_Assets::version());

        $this->registry->register();

        // The original parent block remains registered for existing integrations.
        register_block_type('listdom/shortcodes', [
            'api_version' => 3,
            'category' => 'listdom',
            'editor_script' => 'lsd-blockeditor',
            'editor_style' => 'lsd-blockeditor',
        ]);
    }

    public function blocks($blocks): array
    {
        $blocks[] = [
            'name' => 'listdom/layout',
            'title' => esc_html__('Listdom Layout', 'listdom'),
            'description' => esc_html__('Display a saved Listdom layout.', 'listdom'),
            'icon' => 'list-view',
            'category' => 'listdom',
            'attributes' => ['layoutId' => ['type' => 'integer']],
            'defaults' => ['layoutId' => 0],
            'supports' => ['html' => false],
            'render_callback' => LSD_Integrations_Blockeditor_Layout::class,
            'editor_style' => 'lsd-blockeditor',
            'editor_dependencies' => ['lsd-blockeditor-layout'],
            'editor' => [
                'type' => 'layout',
                'selectLabel' => esc_html__('Saved Layout', 'listdom'),
                'selectPrompt' => esc_html__('Select a layout', 'listdom'),
                'empty' => esc_html__('Select a saved layout to display.', 'listdom'),
                'missing' => esc_html__('This layout is no longer available. Select another layout.', 'listdom'),
                'previewUnavailable' => esc_html__('Preview is unavailable in this WordPress version.', 'listdom'),
            ],
        ];

        foreach (LSD_Integrations_Blockeditor_Application::definitions() as $definition) $blocks[] = $definition;

        $supports = [
            'html' => false,
            'align' => ['wide', 'full'],
            'anchor' => true,
            'customClassName' => true,
            'spacing' => ['margin' => true, 'padding' => true],
            'typography' => ['fontSize' => true, 'lineHeight' => true],
            'color' => ['text' => true, 'background' => true],
        ];

        foreach ([
            'title' => [
                'title' => esc_html__('Listing Title', 'listdom'),
                'icon' => 'heading',
                'attributes' => [
                    'titleTag' => ['type' => 'string', 'default' => 'h2'],
                    'linkMethod' => ['type' => 'string', 'default' => 'normal'],
                ],
            ],
            'address' => [
                'title' => esc_html__('Listing Address', 'listdom'),
                'icon' => 'location',
                'attributes' => ['showIcon' => ['type' => 'boolean', 'default' => true]],
            ],
            'excerpt' => [
                'title' => esc_html__('Listing Excerpt', 'listdom'),
                'icon' => 'editor-paragraph',
                'attributes' => ['words' => ['type' => 'number', 'default' => 12]],
            ],
            'image' => [
                'title' => esc_html__('Listing Image', 'listdom'),
                'icon' => 'format-image',
                'attributes' => ['imageSize' => ['type' => 'string', 'default' => 'full']],
            ],
            'gallery' => [
                'title' => esc_html__('Listing Gallery', 'listdom'),
                'icon' => 'format-gallery',
                'attributes' => [
                    'galleryStyle' => ['type' => 'string', 'default' => 'list'],
                    'lightbox' => ['type' => 'boolean', 'default' => true],
                    'includeThumbnail' => ['type' => 'boolean', 'default' => false],
                    'imageLimit' => ['type' => 'number', 'default' => 4],
                    'autoplay' => ['type' => 'boolean', 'default' => true],
                    'linkMethod' => ['type' => 'string', 'default' => 'normal'],
                    'thumbnailStatus' => ['type' => 'string', 'default' => 'image'],
                    'navigationMethod' => ['type' => 'string', 'default' => 'dots'],
                    'autoHeight' => ['type' => 'boolean', 'default' => true],
                    'loop' => ['type' => 'boolean', 'default' => false],
                ],
            ],
            'content' => [
                'title' => esc_html__('Listing Content', 'listdom'),
                'icon' => 'text-page',
                'attributes' => [
                    'contentLength' => ['type' => 'number', 'default' => 0],
                    'autoParagraph' => ['type' => 'boolean', 'default' => false],
                ],
            ],
            'video' => [
                'title' => esc_html__('Listing Video', 'listdom'),
                'icon' => 'video-alt3',
                'attributes' => [],
            ],
            'embeds' => [
                'title' => esc_html__('Listing Embeds', 'listdom'),
                'icon' => 'embed-generic',
                'attributes' => [],
            ],
            'faqs' => [
                'title' => esc_html__('Listing FAQs', 'listdom'),
                'icon' => 'editor-help',
                'attributes' => ['faqLimit' => ['type' => 'number', 'default' => 0]],
            ],
            'categories' => [
                'title' => esc_html__('Listing Categories', 'listdom'),
                'icon' => 'category',
                'attributes' => [
                    'showColors' => ['type' => 'boolean', 'default' => true],
                    'multipleCategories' => ['type' => 'boolean', 'default' => (bool) apply_filters('lsd_listing_display_multiple_categories', false)],
                    'showNames' => ['type' => 'boolean', 'default' => true],
                    'showIcons' => ['type' => 'boolean', 'default' => false],
                    'enableLinks' => ['type' => 'boolean', 'default' => true],
                ],
                'controls' => [
                    ['key' => 'showColors', 'label' => esc_html__('Show Category Colors', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'multipleCategories', 'label' => esc_html__('Show All Categories', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showNames', 'label' => esc_html__('Show Names', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showIcons', 'label' => esc_html__('Show Icons', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'enableLinks', 'label' => esc_html__('Link to Archives', 'listdom'), 'type' => 'toggle'],
                ],
            ],
            'locations' => [
                'title' => esc_html__('Listing Locations', 'listdom'),
                'icon' => 'location',
                'attributes' => ['enableLinks' => ['type' => 'boolean', 'default' => true]],
                'controls' => [['key' => 'enableLinks', 'label' => esc_html__('Link to Archives', 'listdom'), 'type' => 'toggle']],
            ],
            'tags' => [
                'title' => esc_html__('Listing Tags', 'listdom'),
                'icon' => 'tag',
                'attributes' => ['enableLinks' => ['type' => 'boolean', 'default' => true]],
                'controls' => [['key' => 'enableLinks', 'label' => esc_html__('Link to Archives', 'listdom'), 'type' => 'toggle']],
            ],
            'labels' => [
                'title' => esc_html__('Listing Labels', 'listdom'),
                'icon' => 'tag',
                'attributes' => [
                    'labelStyle' => ['type' => 'string', 'default' => 'tags'],
                    'enableLinks' => ['type' => 'boolean', 'default' => true],
                ],
                'controls' => [
                    ['key' => 'labelStyle', 'label' => esc_html__('Display Type', 'listdom'), 'type' => 'select', 'options' => [
                        ['label' => esc_html__('Tags', 'listdom'), 'value' => 'tags'],
                        ['label' => esc_html__('Links', 'listdom'), 'value' => 'links'],
                    ]],
                    ['key' => 'enableLinks', 'label' => esc_html__('Link to Archives', 'listdom'), 'type' => 'toggle'],
                ],
            ],
            'features' => [
                'title' => esc_html__('Listing Features', 'listdom'),
                'icon' => 'yes-alt',
                'attributes' => [
                    'featureMethod' => ['type' => 'string', 'default' => 'list'],
                    'featureStyle' => ['type' => 'string', 'default' => 'per-row'],
                    'showIcons' => ['type' => 'boolean', 'default' => false],
                    'enableLinks' => ['type' => 'boolean', 'default' => true],
                ],
                'controls' => [
                    ['key' => 'featureMethod', 'label' => esc_html__('Display Type', 'listdom'), 'type' => 'select', 'options' => [
                        ['label' => esc_html__('List', 'listdom'), 'value' => 'list'],
                        ['label' => esc_html__('Text', 'listdom'), 'value' => 'text'],
                    ]],
                    ['key' => 'featureStyle', 'label' => esc_html__('Layout', 'listdom'), 'type' => 'select', 'when' => ['key' => 'featureMethod', 'value' => 'list'], 'options' => [
                        ['label' => esc_html__('One Per Row', 'listdom'), 'value' => 'per-row'],
                        ['label' => esc_html__('Inline', 'listdom'), 'value' => 'inline'],
                    ]],
                    ['key' => 'showIcons', 'label' => esc_html__('Show Icons', 'listdom'), 'type' => 'toggle', 'when' => ['key' => 'featureMethod', 'value' => 'list']],
                    ['key' => 'enableLinks', 'label' => esc_html__('Link to Archives', 'listdom'), 'type' => 'toggle', 'when' => ['key' => 'featureMethod', 'value' => 'list']],
                ],
            ],
            'attributes' => [
                'title' => esc_html__('Listing Attributes', 'listdom'),
                'icon' => 'list-view',
                'attributes' => [
                    'attributeLayout' => ['type' => 'string', 'default' => 'column'],
                    'showIcons' => ['type' => 'boolean', 'default' => false],
                    'showTitles' => ['type' => 'boolean', 'default' => true],
                    'showSeparators' => ['type' => 'boolean', 'default' => false],
                ],
                'controls' => [
                    ['key' => 'attributeLayout', 'label' => esc_html__('Layout', 'listdom'), 'type' => 'select', 'options' => [
                        ['label' => esc_html__('Default', 'listdom'), 'value' => 'column'],
                        ['label' => esc_html__('Inline', 'listdom'), 'value' => 'row'],
                        ['label' => esc_html__('Table', 'listdom'), 'value' => 'table'],
                    ]],
                    ['key' => 'showIcons', 'label' => esc_html__('Show Icons', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showTitles', 'label' => esc_html__('Show Field Titles', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showSeparators', 'label' => esc_html__('Show Separators', 'listdom'), 'type' => 'toggle'],
                ],
            ],
            'price' => [
                'title' => esc_html__('Listing Price', 'listdom'),
                'icon' => 'money-alt',
                'attributes' => ['minimized' => ['type' => 'boolean', 'default' => false]],
                'controls' => [['key' => 'minimized', 'label' => esc_html__('Minimized Price', 'listdom'), 'type' => 'toggle']],
            ],
            'price-class' => [
                'title' => esc_html__('Listing Price Class', 'listdom'),
                'icon' => 'money-alt',
                'attributes' => [],
            ],
            'availability' => [
                'title' => esc_html__('Listing Availability', 'listdom'),
                'icon' => 'clock',
                'attributes' => ['oneDay' => ['type' => 'boolean', 'default' => true]],
                'controls' => [['key' => 'oneDay', 'label' => esc_html__('Show Current Day Only', 'listdom'), 'type' => 'toggle']],
            ],
            'remark' => [
                'title' => esc_html__('Listing Remark', 'listdom'),
                'icon' => 'format-chat',
                'attributes' => [],
            ],
            'rate' => [
                'title' => esc_html__('Listing Rate', 'listdom'),
                'icon' => 'star-filled',
                'attributes' => [
                    'rateType' => ['type' => 'string', 'default' => 'stars'],
                    'showCount' => ['type' => 'boolean', 'default' => true],
                ],
                'controls' => [
                    ['key' => 'rateType', 'label' => esc_html__('Display Type', 'listdom'), 'type' => 'select', 'options' => [
                        ['label' => esc_html__('Stars', 'listdom'), 'value' => 'stars'],
                        ['label' => esc_html__('Summary', 'listdom'), 'value' => 'summary'],
                    ]],
                    ['key' => 'showCount', 'label' => esc_html__('Enable Link', 'listdom'), 'type' => 'toggle', 'when' => ['key' => 'rateType', 'value' => 'stars']],
                ],
            ],
            'contact' => [
                'title' => esc_html__('Listing Contact', 'listdom'),
                'icon' => 'email',
                'attributes' => [
                    'showEmail' => ['type' => 'boolean', 'default' => true],
                    'showPhone' => ['type' => 'boolean', 'default' => true],
                    'showWebsite' => ['type' => 'boolean', 'default' => true],
                    'showAddress' => ['type' => 'boolean', 'default' => true],
                    'showSocials' => ['type' => 'boolean', 'default' => true],
                    'showIcons' => ['type' => 'boolean', 'default' => true],
                    'showLabels' => ['type' => 'boolean', 'default' => false],
                ],
                'controls' => [
                    ['key' => 'showEmail', 'label' => esc_html__('Show Email', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showPhone', 'label' => esc_html__('Show Phone', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showWebsite', 'label' => esc_html__('Show Website', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showAddress', 'label' => esc_html__('Show Address', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showSocials', 'label' => esc_html__('Show Social Networks', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showIcons', 'label' => esc_html__('Show Icons', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showLabels', 'label' => esc_html__('Show Labels', 'listdom'), 'type' => 'toggle'],
                ],
            ],
            'owner' => [
                'title' => esc_html__('Listing Owner', 'listdom'),
                'icon' => 'admin-users',
                'attributes' => [
                    'showAvatar' => ['type' => 'boolean', 'default' => true],
                    'showName' => ['type' => 'boolean', 'default' => true],
                    'showBio' => ['type' => 'boolean', 'default' => true],
                    'showSocials' => ['type' => 'boolean', 'default' => true],
                    'showPhone' => ['type' => 'boolean', 'default' => true],
                    'showEmail' => ['type' => 'boolean', 'default' => true],
                    'showForm' => ['type' => 'boolean', 'default' => true],
                ],
                'controls' => [
                    ['key' => 'showAvatar', 'label' => esc_html__('Show Avatar', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showName', 'label' => esc_html__('Show Name', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showBio', 'label' => esc_html__('Show Bio', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showSocials', 'label' => esc_html__('Show Social Networks', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showPhone', 'label' => esc_html__('Show Phone', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showEmail', 'label' => esc_html__('Show Email', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'showForm', 'label' => esc_html__('Show Contact Form', 'listdom'), 'type' => 'toggle'],
                ],
            ],
            'map' => [
                'title' => esc_html__('Listing Map', 'listdom'),
                'icon' => 'location-alt',
                'attributes' => [
                    'mapProvider' => ['type' => 'string', 'default' => 'default'],
                    'zoomLevel' => ['type' => 'number', 'default' => 14],
                    'showInfowindow' => ['type' => 'boolean', 'default' => true],
                ],
                'controls' => [
                    ['key' => 'mapProvider', 'label' => esc_html__('Map Provider', 'listdom'), 'type' => 'select', 'options' => [
                        ['label' => esc_html__('Site Default', 'listdom'), 'value' => 'default'],
                        ['label' => esc_html__('Leaflet (OpenStreetMap)', 'listdom'), 'value' => LSD_MP_LEAFLET],
                        ['label' => esc_html__('Google Maps', 'listdom'), 'value' => LSD_MP_GOOGLE],
                    ]],
                    ['key' => 'zoomLevel', 'label' => esc_html__('Zoom Level', 'listdom'), 'type' => 'number', 'min' => 4, 'max' => 16],
                    ['key' => 'showInfowindow', 'label' => esc_html__('Show Infowindow', 'listdom'), 'type' => 'toggle'],
                ],
            ],
            'direction' => [
                'title' => esc_html__('Listing Direction', 'listdom'),
                'icon' => 'location',
                'attributes' => [
                    'directionLabel' => ['type' => 'string', 'default' => esc_html__('Directions', 'listdom')],
                    'showIcon' => ['type' => 'boolean', 'default' => true],
                ],
                'controls' => [
                    ['key' => 'directionLabel', 'label' => esc_html__('Link Label', 'listdom'), 'type' => 'text'],
                    ['key' => 'showIcon', 'label' => esc_html__('Show Icon', 'listdom'), 'type' => 'toggle'],
                ],
            ],
            'breadcrumb' => [
                'title' => esc_html__('Listing Breadcrumb', 'listdom'),
                'icon' => 'admin-links',
                'attributes' => [
                    'showHomeIcon' => ['type' => 'boolean', 'default' => true],
                    'breadcrumbTaxonomy' => ['type' => 'string', 'default' => LSD_Base::TAX_CATEGORY],
                ],
                'controls' => [
                    ['key' => 'showHomeIcon', 'label' => esc_html__('Show Home Icon', 'listdom'), 'type' => 'toggle'],
                    ['key' => 'breadcrumbTaxonomy', 'label' => esc_html__('Second Layer', 'listdom'), 'type' => 'select', 'options' => [
                        ['label' => esc_html__('Category', 'listdom'), 'value' => LSD_Base::TAX_CATEGORY],
                        ['label' => esc_html__('Location', 'listdom'), 'value' => LSD_Base::TAX_LOCATION],
                        ['label' => esc_html__('Label', 'listdom'), 'value' => LSD_Base::TAX_LABEL],
                        ['label' => esc_html__('Tag', 'listdom'), 'value' => LSD_Base::TAX_TAG],
                        ['label' => esc_html__('Feature', 'listdom'), 'value' => LSD_Base::TAX_FEATURE],
                    ]],
                ],
            ],
            'share' => [
                'title' => esc_html__('Listing Share', 'listdom'),
                'icon' => 'share',
                'attributes' => ['shareLayout' => ['type' => 'string', 'default' => 'archive']],
                'controls' => [['key' => 'shareLayout', 'label' => esc_html__('Display Type', 'listdom'), 'type' => 'select', 'options' => [
                    ['label' => esc_html__('Popup', 'listdom'), 'value' => 'archive'],
                    ['label' => esc_html__('Inline', 'listdom'), 'value' => 'full'],
                ]]],
            ],
            'back-button' => [
                'title' => esc_html__('Listing Back Button', 'listdom'),
                'icon' => 'undo',
                'attributes' => ['buttonStyle' => ['type' => 'string', 'default' => 'text']],
                'controls' => [['key' => 'buttonStyle', 'label' => esc_html__('Button Style', 'listdom'), 'type' => 'select', 'options' => [
                    ['label' => esc_html__('Text', 'listdom'), 'value' => 'text'],
                    ['label' => esc_html__('Light', 'listdom'), 'value' => 'light'],
                    ['label' => esc_html__('Solid', 'listdom'), 'value' => 'solid'],
                ]]],
            ],
        ] as $kind => $settings)
        {
            $blocks[] = [
                'name' => 'listdom/listing-' . $kind,
                'title' => $settings['title'],
                'icon' => $settings['icon'],
                'category' => 'listdom-single-listing',
                'attributes' => $settings['attributes'] + ['anchor' => ['type' => 'string']],
                'supports' => $supports,
                'render_callback' => [LSD_Integrations_Blockeditor_Elements::class, str_replace('-', '_', $kind)],
                'editor_style' => 'lsd-blockeditor',
                'editor_dependencies' => ['lsd-blockeditor-listing'],
                'editor' => [
                    'type' => 'listing',
                    'kind' => $kind,
                    'controls' => $settings['controls'] ?? [],
                    'panel' => esc_html__('Listing', 'listdom'),
                    'titleTag' => esc_html__('Title Tag', 'listdom'),
                    'linkMethod' => esc_html__('Link Method', 'listdom'),
                    'showIcon' => esc_html__('Show Icon', 'listdom'),
                    'words' => esc_html__('Content Length (words)', 'listdom'),
                    'imageSize' => esc_html__('Image Size', 'listdom'),
                    'imageSizes' => array_merge([['label' => esc_html__('Full Size', 'listdom'), 'value' => 'full']], array_map(function ($size)
                    {
                        return ['label' => $size, 'value' => $size];
                    }, get_intermediate_image_sizes())),
                    'galleryStyle' => esc_html__('Gallery Layout', 'listdom'),
                    'galleryStyles' => [
                        ['label' => esc_html__('List', 'listdom'), 'value' => 'list'],
                        ['label' => esc_html__('Slider', 'listdom'), 'value' => 'slider'],
                        ['label' => esc_html__('Linear Gallery', 'listdom'), 'value' => 'linear'],
                    ],
                    'lightbox' => esc_html__('Image Lightbox', 'listdom'),
                    'includeThumbnail' => esc_html__('Include Featured Image', 'listdom'),
                    'imageLimit' => esc_html__('Number of Images', 'listdom'),
                    'autoplay' => esc_html__('Autoplay', 'listdom'),
                    'thumbnailStatus' => esc_html__('Thumbnails', 'listdom'),
                    'thumbnailStatuses' => [
                        ['label' => esc_html__('On the Image', 'listdom'), 'value' => 'image'],
                        ['label' => esc_html__('List', 'listdom'), 'value' => 'list'],
                        ['label' => esc_html__('Disabled', 'listdom'), 'value' => 'disabled'],
                    ],
                    'navigationMethod' => esc_html__('Navigation', 'listdom'),
                    'navigationMethods' => [
                        ['label' => esc_html__('Dots', 'listdom'), 'value' => 'dots'],
                        ['label' => esc_html__('Arrows', 'listdom'), 'value' => 'nav'],
                        ['label' => esc_html__('Disabled', 'listdom'), 'value' => 'disabled'],
                    ],
                    'autoHeight' => esc_html__('Auto Height', 'listdom'),
                    'loop' => esc_html__('Loop', 'listdom'),
                    'contentLength' => esc_html__('Content Length (0 for all)', 'listdom'),
                    'autoParagraph' => esc_html__('Auto Paragraph', 'listdom'),
                    'faqLimit' => esc_html__('Question Count (0 for all)', 'listdom'),
                    'empty' => esc_html__('This listing has no content for this block.', 'listdom'),
                    'titleTags' => [
                        ['label' => esc_html__('Heading 1', 'listdom'), 'value' => 'h1'],
                        ['label' => esc_html__('Heading 2', 'listdom'), 'value' => 'h2'],
                        ['label' => esc_html__('Heading 3', 'listdom'), 'value' => 'h3'],
                        ['label' => esc_html__('Heading 4', 'listdom'), 'value' => 'h4'],
                        ['label' => esc_html__('Paragraph', 'listdom'), 'value' => 'p'],
                        ['label' => esc_html__('Strong', 'listdom'), 'value' => 'strong'],
                    ],
                    'linkMethods' => in_array($kind, ['title', 'gallery'], true) ? $this->listing_link_methods() : [],
                    'previewUnavailable' => esc_html__('Preview is unavailable in this WordPress version.', 'listdom'),
                ],
            ];
        }

        return $blocks;
    }

    private function listing_link_methods(): array
    {
        $methods = [];
        foreach (LSD_Base::get_listing_link_methods() as $value => $label)
        {
            $methods[] = ['label' => $label, 'value' => $value];
        }

        return $methods;
    }

    public function should_include($include)
    {
        $screen = get_current_screen();
        if ($screen && method_exists($screen, 'is_block_editor') && $screen->is_block_editor()) $include = true;

        return $include;
    }

    public function layout_page($status): bool
    {
        if ($status || is_admin()) return (bool) $status;

        $names = array_fill_keys(array_keys($this->registry->blocks()), true);
        if (!$names) return false;

        $seen = [];
        $post = get_post();
        if ($post instanceof WP_Post && $this->content_has_registered_block($post->post_content, $names, $seen)) return true;

        // WordPress resolves the active block template before wp_head.
        global $_wp_current_template_content;
        if (is_string($_wp_current_template_content) && $this->content_has_registered_block($_wp_current_template_content, $names, $seen)) return true;

        return $this->block_widgets_have_registered_block($names, $seen);
    }

    private function block_widgets_have_registered_block(array $names, array &$seen): bool
    {
        $widgets = get_option('widget_block', []);
        if (!is_array($widgets) || !$widgets) return false;

        // Block widgets render after wp_head, so inspect active sidebars before assets are printed.
        foreach (wp_get_sidebars_widgets() as $sidebar => $ids)
        {
            if ($sidebar === 'wp_inactive_widgets' || strpos($sidebar, 'orphaned_widgets') === 0 || !is_array($ids)) continue;

            foreach ($ids as $widget)
            {
                if (strpos($widget, 'block-') !== 0) continue;

                $number = (int) substr($widget, 6);
                $content = $widgets[$number]['content'] ?? '';
                if (is_string($content) && $this->content_has_registered_block($content, $names, $seen)) return true;
            }
        }

        return false;
    }

    private function content_has_registered_block(string $content, array $names, array &$seen): bool
    {
        // Shortcodes inside patterns, template parts, and block widgets also render after wp_head.
        if (preg_match('/\[(listdom(?:[-_][\w-]+)?)(\s|]|\/)/i', $content)) return true;
        if (!has_blocks($content)) return false;
        return $this->parsed_content_has_registered_block(parse_blocks($content), $names, $seen);
    }

    private function parsed_content_has_registered_block(array $blocks, array $names, array &$seen): bool
    {
        foreach ($blocks as $block)
        {
            $type = $block['blockName'] ?? '';
            $attrs = $block['attrs'] ?? [];
            if (isset($names[$type])) return true;
            if (is_string($type) && preg_match('~^listdom/shortcodes-[0-9]+$~', $type)) return true;

            if ($type === 'core/template-part' && !empty($attrs['slug']) && function_exists('get_block_templates'))
            {
                $theme = $attrs['theme'] ?? get_stylesheet();
                if ($theme === get_stylesheet())
                {
                    $id = $theme . '//' . $attrs['slug'];
                    if (!isset($seen[$id]))
                    {
                        $seen[$id] = true;
                        // Match the published customization or theme file used on the frontend.
                        foreach (get_block_templates(['slug__in' => [$attrs['slug']]], 'wp_template_part') as $part)
                        {
                            if ($part->id === $id && $this->content_has_registered_block($part->content, $names, $seen)) return true;
                        }
                    }
                }
            }
            elseif ($type === 'core/block' && !empty($attrs['ref']))
            {
                $id = 'wp_block:' . (int) $attrs['ref'];
                if (!isset($seen[$id]))
                {
                    $seen[$id] = true;
                    $pattern = get_post((int) $attrs['ref']);
                    if ($pattern instanceof WP_Post && $pattern->post_type === 'wp_block' && $pattern->post_status === 'publish' && empty($pattern->post_password) && $this->content_has_registered_block($pattern->post_content, $names, $seen)) return true;
                }
            }
            elseif ($type === 'core/pattern' && !empty($attrs['slug']) && class_exists('WP_Block_Patterns_Registry'))
            {
                $id = 'pattern:' . $attrs['slug'];
                if (!isset($seen[$id]))
                {
                    $seen[$id] = true;
                    $pattern = WP_Block_Patterns_Registry::get_instance()->get_registered($attrs['slug']);
                    if (isset($pattern['content']) && $this->content_has_registered_block($pattern['content'], $names, $seen)) return true;
                }
            }

            if (!empty($block['innerBlocks']) && $this->parsed_content_has_registered_block($block['innerBlocks'], $names, $seen)) return true;
        }

        return false;
    }

    public function legacy_shortcode($content, $block): string
    {
        $name = $block['blockName'] ?? '';
        if (!is_string($name) || !preg_match('~^listdom/shortcodes-[0-9]+$~', $name)) return $content;

        // The old block saved its layout ID in the shortcode, never in the numeric block name.
        $saved = $block['innerHTML'] ?? '';
        if (!is_string($saved)) return $content;

        $saved = html_entity_decode($saved, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (!preg_match('~^\s*\[listdom id="([1-9][0-9]*)"\]\s*$~', $saved, $matches)) return $content;

        $id = (int) $matches[1];
        $layout = get_post($id);
        if (!$layout || $layout->post_type !== LSD_Base::PTYPE_SHORTCODE || $layout->post_status !== 'publish') return '';

        $styles_before = wp_styles()->queue;
        $html = do_shortcode('[listdom id="' . $id . '"]');
        return LSD_Integrations_Blockeditor_Assets::frontend($styles_before) . $html;
    }

    private function legacy_blocks(string $content): array
    {
        $names = [];
        $seen = [];
        foreach (parse_blocks($content) as $block) $this->collect_legacy_blocks($block, $names, $seen);
        return array_keys($names);
    }

    private function collect_legacy_blocks(array $block, array &$names, array &$seen): void
    {
        $name = $block['blockName'] ?? '';
        if (is_string($name) && preg_match('~^listdom/shortcodes-[0-9]+$~', $name)) $names[$name] = true;

        if ($name === 'core/block' && !empty($block['attrs']['ref']))
        {
            $id = absint($block['attrs']['ref']);
            if ($id && !isset($seen[$id]))
            {
                $seen[$id] = true;
                $pattern = get_post($id);
                if ($pattern instanceof WP_Post && $pattern->post_type === 'wp_block' && $pattern->post_status === 'publish' && empty($pattern->post_password))
                {
                    foreach (parse_blocks($pattern->post_content) as $inner) $this->collect_legacy_blocks($inner, $names, $seen);
                }
            }
        }

        foreach ($block['innerBlocks'] ?? [] as $inner) $this->collect_legacy_blocks($inner, $names, $seen);
    }

    private function editor_legacy_blocks(): array
    {
        $names = [];
        if (isset($GLOBALS['post']) && $GLOBALS['post'] instanceof WP_Post)
        {
            foreach ($this->legacy_blocks($GLOBALS['post']->post_content) as $name) $names[$name] = true;
        }

        $screen = get_current_screen();
        if (!$screen) return array_keys($names);

        if ($screen->id === 'site-editor')
        {
            // Site Editor entities are fetched later by JavaScript, so register their saved names first.
            global $wpdb;
            $like = '%' . $wpdb->esc_like('<!-- wp:listdom/shortcodes-') . '%';
            $contents = $wpdb->get_col($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_status NOT IN ('trash', 'auto-draft') AND post_type != 'revision'", $like));
            foreach ($contents as $content)
            {
                foreach ($this->legacy_blocks($content) as $name) $names[$name] = true;
            }

            if (function_exists('get_block_templates'))
            {
                foreach (['wp_template', 'wp_template_part'] as $type)
                {
                    foreach (get_block_templates([], $type) as $template)
                    {
                        foreach ($this->legacy_blocks($template->content) as $name) $names[$name] = true;
                    }
                }
            }
        }
        elseif ($screen->id === 'widgets')
        {
            foreach (get_option('widget_block', []) as $widget)
            {
                if (!is_array($widget) || !isset($widget['content']) || !is_string($widget['content'])) continue;
                foreach ($this->legacy_blocks($widget['content']) as $name) $names[$name] = true;
            }
        }

        return array_keys($names);
    }

    public function assets(): void
    {
        LSD_Assets::select2();
        LSD_Assets::nouislider();

        $dependencies = ['wp-blocks', 'wp-element', 'wp-hooks'];
        $editor_handle = wp_script_is('wp-block-editor', 'registered') ? 'wp-block-editor' : 'wp-editor';
        $dependencies[] = $editor_handle;

        $layout_dependencies = ['wp-element', 'wp-components', 'wp-hooks', $editor_handle];
        if (wp_script_is('wp-server-side-render', 'registered')) $layout_dependencies[] = 'wp-server-side-render';

        wp_register_script(
            'lsd-blockeditor-layout',
            $this->lsd_asset_url('js/blockeditor-layout.min.js'),
            $layout_dependencies,
            LSD_Assets::version(),
            true
        );

        $listing_dependencies = ['wp-element', 'wp-components', 'wp-hooks', 'wp-data', $editor_handle];
        if (wp_script_is('wp-server-side-render', 'registered')) $listing_dependencies[] = 'wp-server-side-render';
        wp_register_script(
            'lsd-blockeditor-listing',
            $this->lsd_asset_url('js/blockeditor-listing.min.js'),
            $listing_dependencies,
            LSD_Assets::version(),
            true
        );

        $app_dependencies = ['jquery', 'select2', 'no-ui-slider', 'wp-element', 'wp-components', 'wp-hooks', 'wp-api-fetch', $editor_handle];
        if (wp_script_is('wp-server-side-render', 'registered')) $app_dependencies[] = 'wp-server-side-render';
        wp_register_script(
            'lsd-blockeditor-app',
            $this->lsd_asset_url('js/blockeditor-app.min.js'),
            $app_dependencies,
            LSD_Assets::version(),
            true
        );

        foreach ($this->registry->editor_dependencies() as $handle) $dependencies[] = $handle;

        // Include Listdom Block Dependencies
        wp_enqueue_script(
            'lsd-blockeditor',
            $this->lsd_asset_url('js/blockeditor.min.js'),
            array_unique($dependencies),
            LSD_Assets::version(),
            true
        );

        $shortcodes = $this->getShortcodes(true);

        // Keep the data exposed to existing editor integrations.
        wp_localize_script('lsd-blockeditor', 'lsd', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'shortcodes' => $shortcodes,
        ]);

        wp_localize_script('lsd-blockeditor', 'lsdBlockEditor', [
            'blocks' => $this->registry->editor_blocks(),
            'shortcodes' => $shortcodes,
            'legacyBlocks' => $this->editor_legacy_blocks(),
            'legacyTitle' => esc_html__('Listdom Shortcode', 'listdom'),
        ]);

    }

    public function editor_routes(): void
    {
        register_rest_route('listdom/v1', '/block-searches', [
            'methods' => 'GET',
            'callback' => [LSD_Integrations_Blockeditor_Application::class, 'searches'],
            'permission_callback' => function () { return current_user_can('edit_posts') || current_user_can('edit_theme_options'); },
        ]);
    }

    public function content_assets(): void
    {
        if (!is_admin()) return;

        // Native previews need the same frontend assets as the published blocks.
        (new LSD_Assets())->site(true);

        wp_enqueue_style('lsd-blockeditor');
    }

    public function enable_be($args)
    {
        if (!empty($this->settings['blockeditor_status'])) $args['show_in_rest'] = true;
        return $args;
    }
}
