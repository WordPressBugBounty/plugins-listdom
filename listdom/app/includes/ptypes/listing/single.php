<?php

class LSD_PTypes_Listing_Single extends LSD_PTypes_Listing
{
    protected $pattern;
    public $details_page_options;

    /**
     * @var LSD_Entity_Listing
     */
    public $entity;
    public $style;
    protected $filtered_content;
    protected ?bool $suppress_legacy_schema = null;

    public function __construct()
    {
        parent::__construct();

        // Single Listing options
        $this->details_page_options = LSD_Options::details_page();
    }

    public function hooks()
    {
        add_filter('the_content', [$this, 'filter_content']);
        add_filter('lsd_ptype_listing_supports', [$this, 'add_comments_support']);
        add_filter('template_include', [$this, 'template']);
    }

    public function unhook()
    {
        remove_filter('the_content', [$this, 'filter_content']);
        remove_filter('lsd_ptype_listing_supports', [$this, 'add_comments_support']);
        remove_filter('template_include', [$this, 'template']);
    }

    /**
     * @param bool $return_listing
     * @return LSD_Entity_Listing|LSD_PTypes_Listing_Single
     */
    public static function preview(bool $return_listing = false)
    {
        // Current Listing
        if (is_singular(LSD_Base::PTYPE_LISTING)) $listing = get_post();
        else
        {
            // Listings
            $listings = get_posts([
                'post_type' => LSD_Base::PTYPE_LISTING,
                'post_status' => ['publish'],
                'posts_per_page' => 1,
                'orderby' => 'rand',
            ]);

            // Sample Listing
            $listing = $listings[0];
        }

        // Return listing only
        if ($return_listing) return new LSD_Entity_Listing($listing);

        // Self Object
        $single = new LSD_PTypes_Listing_Single();

        // Bootstrap the Parameters
        $single->bootstrap($listing, $listing->post_content);

        return $single;
    }

    public static function get_listing_style(int $listing_id): string
    {
        $listing_style = '';

        $single_listing_options = LSD_Options::details_page();
        $general_style = $single_listing_options['general']['style'] ?? 'style1';

        // Apply Listing Display Options
        if (LSD_Base::isPro() && isset($single_listing_options['general']['displ']) && $single_listing_options['general']['displ'])
        {
            $displ = get_post_meta($listing_id, 'lsd_displ', true);
            if (!is_array($displ)) $displ = [];

            // Listing Style
            if (isset($displ['style']) && trim($displ['style'])) $listing_style = $displ['style'];
        }

        // Get Listing Style by Category and Others
        $listing_style = (string) apply_filters('lsd_listing_details_style', $listing_style, $listing_id);

        return $listing_style ?: $general_style;
    }

    public static function get_listing_elements(int $listing_id): array
    {
        $single_listing_options = LSD_Options::details_page();

        $listing_elements = [];

        // Apply Listing Display Options
        if (LSD_Base::isPro() && isset($single_listing_options['general']['displ']) && $single_listing_options['general']['displ'])
        {
            $displ = get_post_meta($listing_id, 'lsd_displ', true);
            if (!is_array($displ)) $displ = [];

            if (isset($displ['elements']) && is_array($displ['elements']) && count($displ['elements']) && isset($displ['elements_global']) && !$displ['elements_global']) $listing_elements = $displ['elements'];
        }

        // Get Listing Elements by Category and Others
        return (array) apply_filters('lsd_listing_details_elements', $listing_elements, $listing_id);
    }

    public function bootstrap($post, $content = null)
    {
        // Single Page Pattern
        $this->pattern = LSD_Options::details_page_pattern();

        $listing_style = $this->get_listing_style($post->ID);
        $listing_elements = $this->get_listing_elements($post->ID);

        // Listing Elements
        if (count($listing_elements))
        {
            $this->pattern = '';
            foreach ($listing_elements as $key => $elm)
            {
                if (isset($elm['enabled']))
                {
                    $this->details_page_options['elements'][$key]['enabled'] = $elm['enabled'];
                    if ($elm['enabled']) $this->pattern .= '{' . $key . '}';
                }
            }
        }

        $this->details_page_options['general']['style'] = $listing_style;
        $this->style = $listing_style;
        $this->filtered_content = $content;
        $this->suppress_legacy_schema = null;

        $this->entity = new LSD_Entity_Listing($post);

        // Filter The Single Listing Options
        $this->details_page_options = apply_filters('lsd_details_page_options', $this->details_page_options, $post);
    }

    public function add_comments_support($supports)
    {
        $comments = isset($this->details_page_options['general']['comments']) && $this->details_page_options['general']['comments'];
        if ($comments) $supports[] = 'comments';

        return $supports;
    }

    public function template($template)
    {
        // We're in an embed post
        if (is_embed()) return $template;

        if (is_single() && get_post_type() === LSD_Base::PTYPE_LISTING)
        {
            $listing_style = self::get_listing_style(get_the_ID());
            $builder_template = '';

            if (is_string($listing_style) && strpos($listing_style, 'tb_') === 0)
            {
                $template_id = (int) str_replace('tb_', '', $listing_style);
                if ($template_id > 0)
                {
                    $template_post = get_post($template_id);
                    if (
                        $template_post instanceof WP_Post
                        && $template_post->post_type === LSD_Base::PTYPE_TEMPLATE
                        && $template_post->post_status === 'publish'
                    ) {
                        $builder_template = get_post_meta($template_id, '_wp_page_template', true);
                        $builder_template = is_string($builder_template) ? trim($builder_template) : '';
                    }
                }
            }

            // Prefer template-builder page template when the listing uses it
            $preferred_template = ($builder_template !== '' && $builder_template !== 'default')
                ? $builder_template
                : '';

            $theme_template = isset($this->details_page_options['general']['theme_template']) && trim($this->details_page_options['general']['theme_template'])
                ? $this->details_page_options['general']['theme_template']
                : '';

            if ($preferred_template === '' && $theme_template !== '')
            {
                $preferred_template = $theme_template;
            }

            if ($preferred_template !== '')
            {
                // WordPress Method
                $located_template = locate_template($preferred_template);

                // Elementor Fix
                if (!$located_template && class_exists(\Elementor\Plugin::class))
                {
                    $module = \Elementor\Plugin::instance()->modules_manager->get_modules('page-templates');
                    if ($module && method_exists($module, 'get_template_path'))
                    {
                        $located_template = $module->get_template_path($preferred_template);
                    }
                }

                // Template Found
                if ($located_template) $template = $located_template;
            }
        }

        return $template;
    }

    public function filter_content($content)
    {
        // It's not singular page of listing
        if (!is_singular($this->PT) || !in_the_loop() || !is_main_query()) return $content;

        global $post;
        if ($post->post_type !== $this->PT) return $content;

        // Is it an endpoint?
        if ($ep = LSD_Endpoints::is()) return LSD_Endpoints::output($ep);

        // Remove Loop
        remove_filter('the_content', [$this, 'filter_content']);

        // Get Single Listing Content
        $output = $this->get($content);

        // Add Filter
        add_filter('the_content', [$this, 'filter_content']);

        // Content
        return $output;
    }

    public function get($content, ?string $style = null)
    {
        global $post;

        // Bootstrap the Parameters
        $this->bootstrap($post, $content);

        // Filtered Content
        if ($filtered = apply_filters('lsd_listing_before_single_content', false, $this)) return $filtered;

        // Update Listing Visits
        $this->entity->update_visits();

        // Trigger Action
        do_action('lsd_listing_visited', $post);

        // Force Style
        if ($style)
        {
            $valid_styles = LSD_Styles::details();
            if (isset($valid_styles[$style]) && $valid_styles[$style]) $this->style = $style;
        }

        switch ($this->style)
        {
            case 'dynamic':

                return $this->dynamic();

            case 'style4':

                return $this->style4();

            case 'style3':

                return $this->style3();

            case 'style2':

                return $this->style2();

            case 'style1':

                return $this->style1();

            default:

                return $this->builders();
        }
    }

    public function style1()
    {
        // Rendered Listing Content
        $rendered = $this->pattern;

        // Abuse
        if (strpos($this->pattern, '{abuse}') !== false)
        {
            $abuse = $this->abuse();
            if (trim($abuse)) $abuse = '<div class="lsd-single-abuse-wrapper">' . $abuse . '</div>';

            $rendered = str_replace('{abuse}', LSD_Kses::form($abuse), $rendered);
        }

        // Listing Breadcrumb
        if (strpos($this->pattern, '{breadcrumb}') !== false)
        {
            $rendered = str_replace('{breadcrumb}', $this->replaced_listing_section('breadcrumb'), $rendered);
        }

        // Back Button
        if (strpos($this->pattern, '{backbutton}') !== false)
        {
            $backbutton = $this->backbutton();
            $rendered = str_replace('{backbutton}', LSD_Kses::element($backbutton), $rendered);
        }

        // Listing Labels
        if (strpos($this->pattern, '{labels}') !== false)
        {
            $rendered = str_replace('{labels}', LSD_Kses::element($this->labels()), $rendered);
        }

        // Listing Featured Image
        if (strpos($this->pattern, '{image}') !== false)
        {
            $rendered = str_replace('{image}', $this->replaced_listing_section('image'), $rendered);
        }

        // Listing Gallery
        if (strpos($this->pattern, '{gallery}') !== false)
        {
            $rendered = str_replace('{gallery}', $this->replaced_listing_section('gallery'), $rendered);
        }

        // Listing Embeds
        if (strpos($this->pattern, '{embed}') !== false)
        {
            $rendered = str_replace('{embed}', $this->replaced_listing_section('embeds', 'rich'), $rendered);
        }

        // Listing Featured Video
        if (strpos($this->pattern, '{video}') !== false)
        {
            $rendered = str_replace('{video}', $this->replaced_listing_section('featured_video', 'rich'), $rendered);
        }

        // Listing Address
        if (strpos($this->pattern, '{address}') !== false)
        {
            $rendered = str_replace('{address}', $this->replaced_listing_section('address'), $rendered);
        }

        // Listing Locations
        if (strpos($this->pattern, '{locations}') !== false)
        {
            $locations = $this->replaced_listing_section('locations');
            if (trim($locations)) $locations = '<div class="lsd-single-locations-box">' . $locations . '</div>';

            $rendered = str_replace('{locations}', $locations, $rendered);
        }

        // Listing Owner
        if (strpos($this->pattern, '{owner}') !== false)
        {
            $rendered = str_replace('{owner}', $this->replaced_listing_section('owner', 'form'), $rendered);
        }

        // Listing Attributes
        if (strpos($this->pattern, '{attributes}') !== false)
        {
            $rendered = str_replace('{attributes}', $this->replaced_listing_section('attributes'), $rendered);
        }

        // Listing Availability
        if (strpos($this->pattern, '{availability}') !== false)
        {
            $rendered = str_replace('{availability}', $this->replaced_listing_section('availability'), $rendered);
        }

        // Listing Categories
        if (strpos($this->pattern, '{categories}') !== false)
        {
            $rendered = str_replace('{categories}', $this->replaced_listing_section('categories'), $rendered);
        }

        // Post Content
        if (strpos($this->pattern, '{content}') !== false)
        {
            $rendered = str_replace('{content}', $this->replaced_listing_section('content', 'element', [$this->filtered_content]), $rendered);
        }

        // FAQs
        if (strpos($this->pattern, '{faq}') !== false)
        {
            $faq = $this->faq();
            $rendered = str_replace('{faq}', LSD_Kses::element($faq), $rendered);
        }

        // Remark
        if (strpos($this->pattern, '{remark}') !== false)
        {
            $rendered = str_replace('{remark}', $this->replaced_listing_section('remark'), $rendered);
        }

        // Listing Features
        if (strpos($this->pattern, '{features}') !== false)
        {
            $rendered = str_replace('{features}', $this->replaced_listing_section('features'), $rendered);
        }

        // Listing Map
        if (strpos($this->pattern, '{map}') !== false)
        {
            $rendered = str_replace('{map}', $this->replaced_listing_section('map', 'form'), $rendered);
        }

        // Listing Title
        if (strpos($this->pattern, '{title}') !== false)
        {
            $rendered = str_replace('{title}', $this->replaced_listing_section('title'), $rendered);
        }

        // Listing Price
        if (strpos($this->pattern, '{price}') !== false)
        {
            $rendered = str_replace('{price}', $this->replaced_listing_section('price'), $rendered);
        }

        // Listing Share
        if (strpos($this->pattern, '{share}') !== false)
        {
            $share = $this->share();
            $rendered = str_replace('{share}', LSD_Kses::element($share), $rendered);
        }

        // Listing Call To Action
        if (strpos($this->pattern, '{cta}') !== false)
        {
            $cta = $this->cta();
            $rendered = str_replace('{cta}', LSD_Kses::element($cta), $rendered);
        }

        // Listing Tags
        if (strpos($this->pattern, '{tags}') !== false)
        {
            $tags = $this->tags();
            $rendered = str_replace('{tags}', LSD_Kses::element($tags), $rendered);
        }

        // Listing Contact Info
        if (strpos($this->pattern, '{contact}') !== false)
        {
            $rendered = str_replace('{contact}', $this->replaced_listing_section('contact_info'), $rendered);
        }

        // Listing Excerpt
        if (strpos($this->pattern, '{excerpt}') !== false)
        {
            $rendered = str_replace('{excerpt}', $this->replaced_listing_section('excerpt'), $rendered);
        }

        // Listing Related
        if (strpos($this->pattern, '{related}') !== false)
        {
            $rendered = str_replace('{related}', $this->replaced_listing_section('related', 'full'), $rendered);
        }

        // Wrap the content
        return $this->wrapper($rendered);
    }

    public function style2()
    {
        // Generate Output
        ob_start();
        include lsd_template('single/style2.php');
        $output = ob_get_clean();

        // Wrap the content
        return $this->wrapper($output);
    }

    public function style3()
    {
        // Generate Output
        ob_start();
        include lsd_template('single/style3.php');
        $output = ob_get_clean();

        // Wrap the content
        return $this->wrapper($output);
    }

    public function style4()
    {
        // Generate Output
        ob_start();
        include lsd_template('single/style4.php');
        $output = ob_get_clean();

        // Wrap the content
        return $this->wrapper($output);
    }

    public function dynamic()
    {
        // Generate Output
        ob_start();
        include lsd_template('single/dynamic.php');
        $output = ob_get_clean();

        // Wrap the content
        return $this->wrapper($output);
    }

    public function builders()
    {
        // Remove Loop
        remove_filter('the_content', [$this, 'filter_content']);

        // Build and Return
        $output = (new LSD_Builders())
            ->single($this)
            ->listing($this->entity)
            ->build($this->style);

        // Add Filter
        add_filter('the_content', [$this, 'filter_content']);

        // Wrap the content
        return $this->wrapper($output);
    }

    public function wrapper($content)
    {
        // Style Wrapper Class
        $style = is_numeric($this->style) ? 'builder' : $this->style;
        $suppress_schema = $this->should_suppress_legacy_schema();

        $schema = lsd_schema()->reset();
        $meta = '';

        if (!$suppress_schema)
        {
            // Reliability fallback for Google-required LocalBusiness fields
            $meta .= $schema->meta('name', get_the_title($this->entity->id()));
            $meta .= $schema->meta('url', get_permalink($this->entity->id()));

            $image_id = get_post_thumbnail_id($this->entity->id());
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
            if ($image_url) $meta .= $schema->meta('image', $image_url);
        }

        $wrapper_schema = $suppress_schema ? '' : ' ' . lsd_schema()->scope()->type(null, $this->entity->get_data_category());

        $rendered = '<div class="lsd-single-page-wrapper lsd-font-m lsd-single-' . $style . '"' . $wrapper_schema . '>'
            . $meta . $content . $this->powered_by_message()
        . '</div>';

        // Remove remained placeholders
        return preg_replace('/{.*}/', '', apply_filters('lsd_listing_single_content', $rendered, $this));
    }

    /**
     * Check whether replacement JSON-LD suppresses legacy listing markup.
     * @return bool
     */
    protected function should_suppress_legacy_schema(): bool
    {
        // Cached Status
        if ($this->suppress_legacy_schema === null)
        {
            // AI Visibility Service
            $ai_visibility = class_exists('LSD_AI_Visibility') ? LSD_AI_Visibility::instance() : null;
            $this->suppress_legacy_schema = $ai_visibility instanceof LSD_AI_Visibility && $ai_visibility->schema_service()->replaces_single_listing_schema();
        }

        return $this->suppress_legacy_schema;
    }

    /**
     * Render a listing section with optional legacy schema suppression.
     * @param string $method
     * @param string $sanitizer
     * @param array $arguments
     * @return string
     */
    protected function replaced_listing_section(string $method, string $sanitizer = 'element', array $arguments = []): string
    {
        // Suppression Status
        $suppress_schema = $this->should_suppress_legacy_schema_for_section($method);

        // Suppress Markup
        if ($suppress_schema) LSD_Schema::suppress_markup();

        try
        {
            // Section Content
            $content = $this->{$method}(...$arguments);
        }
        finally
        {
            // Restore Markup
            if ($suppress_schema) LSD_Schema::restore_markup();
        }

        // Sanitized Content
        if ($sanitizer === 'form') return LSD_Kses::form($content);
        if ($sanitizer === 'full') return LSD_Kses::full($content);
        if ($sanitizer === 'rich') return LSD_Kses::rich($content);

        return LSD_Kses::element($content);
    }

    /**
     * Check whether a section should hide legacy schema markup.
     * @param string $method
     * @return bool
     */
    protected function should_suppress_legacy_schema_for_section(string $method): bool
    {
        // These sections expose their own independent visible entities; the
        // replacement listing graph does not model them as listing properties.
        if (in_array($method, ['owner', 'related'], true)) return false;

        return $this->should_suppress_legacy_schema();
    }

    protected function powered_by_message(): string
    {
        $settings = LSD_Options::settings();
        if (empty($settings['powered_by_message'])) return '';

        $message = sprintf(
            /* translators: %s: Listdom website link. */
            wp_kses(__('Powered by %s', 'listdom'), ['a' => ['href' => [], 'target' => [], 'rel' => []]]),
            '<a href="' . esc_url('https://listdom.net/?utm_source=listdom&utm_medium=powered_by&utm_campaign=listing_single') . '" target="_blank" rel="noopener">' . esc_html__('Listdom', 'listdom') . '</a>'
        );

        return '<p class="lsd-single-powered-by lsd-m-0">' . $message . '</p>';
    }

    public function breadcrumb(): string
    {
        $icon = $this->details_page_options['elements']['breadcrumb']['icon'] ?? true;
        $taxonomy = $this->details_page_options['elements']['breadcrumb']['taxonomy'];

        $breadcrumb = $this->entity->get_breadcrumb($icon, $taxonomy);

        // Schema
        $schema = lsd_schema()->breadcrumb();

        // Heading
        $heading = isset($this->details_page_options['elements']['breadcrumb']['custom_title']) && trim($this->details_page_options['elements']['breadcrumb']['custom_title'])
            ? $this->details_page_options['elements']['breadcrumb']['custom_title']
            : esc_html__('Breadcrumb', 'listdom');

        $output = '<div class="lsd-single-page-section">';
        if ($this->details_page_options['elements']['breadcrumb']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title lsd-single-page-section-breadcrumb">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-element lsd-single-breadcrumb" ' . $schema . '>' . $breadcrumb . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function backbutton(): string
    {
        $element = new LSD_Element_Backbutton();

        return $element->get($this->entity->id());
    }

    public function address(): string
    {
        $address = $this->entity->get_address(false);

        // Don't show anything when there is no Address!
        if (!trim($address)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['address']['title_align']) ? ' ' . $this->details_page_options['elements']['address']['title_align'] : '';

        // Schema
        $schema = lsd_schema()->address();

        // Heading
        $heading = isset($this->details_page_options['elements']['address']['custom_title']) && trim($this->details_page_options['elements']['address']['custom_title'])
            ? $this->details_page_options['elements']['address']['custom_title']
            : esc_html__('Address', 'listdom');

        $output = '<div class="lsd-single-page-section">';
        if ($this->details_page_options['elements']['address']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title lsd-single-page-section-address ' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-element lsd-single-address" ' . $schema . '>' . $address . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function locations(): string
    {
        $locations = $this->entity->get_locations([
            'enable_link' => $this->details_page_options['elements']['locations']['enable_link'] ?? 1,
        ]);

        // Don't show anything when there is no locations!
        if (!trim($locations)) return '';

        return '<div class="lsd-single-element lsd-single-locations">' . $locations . '</div>';
    }

    public function owner(): string
    {
        $owner = $this->entity->get_owner('details', $this->details_page_options['elements']['owner']);

        // Don't show anything if nothing found
        if (trim($owner) === '') return '';

        $title_alignment = !empty($this->details_page_options['elements']['owner']['title_align']) ? ' ' . $this->details_page_options['elements']['owner']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['owner']['custom_title']) && trim($this->details_page_options['elements']['owner']['custom_title'])
            ? $this->details_page_options['elements']['owner']['custom_title']
            : esc_html__('Author Info', 'listdom');

        $output = '<div class="lsd-single-page-section lsd-single-page-owner">';
        if ($this->details_page_options['elements']['owner']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-owner-box lsd-single-element lsd-single-owner">' . $owner . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function abuse(): string
    {
        $abuse = $this->entity->get_abuse($this->details_page_options['elements']['abuse']);

        // Don't show anything if nothing found
        if (trim($abuse) == '') return '';

        $title_alignment = !empty($this->details_page_options['elements']['abuse']['title_align']) ? ' ' . $this->details_page_options['elements']['abuse']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['abuse']['custom_title']) && trim($this->details_page_options['elements']['abuse']['custom_title'])
            ? $this->details_page_options['elements']['abuse']['custom_title']
            : esc_html__('Report Abuse', 'listdom');

        $output = '<div class="lsd-single-page-section lsd-single-page-abuse">';
        if ($this->details_page_options['elements']['abuse']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-abuse-box lsd-single-element lsd-single-abuse">' . $abuse . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function attributes(): string
    {
        $show_icons = $this->details_page_options['elements']['attributes']['show_icons'] ?? 0;
        $show_attribute_title = $this->details_page_options['elements']['attributes']['show_attribute_title'] ?? 1;
        $show_separator = $this->details_page_options['elements']['attributes']['show_separator'] ?? 0;
        $layout = $this->details_page_options['elements']['attributes']['layout'] ?? 'column';
        $attributes = $this->entity->get_attributes($show_icons, $show_attribute_title, $show_separator, $layout);

        // Don't show anything when there is no attribute!
        if (!trim($attributes)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['attributes']['title_align']) ? ' ' . $this->details_page_options['elements']['attributes']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['attributes']['custom_title']) && trim($this->details_page_options['elements']['attributes']['custom_title'])
            ? $this->details_page_options['elements']['attributes']['custom_title']
            : esc_html__('Details', 'listdom');

        $output = '<div class="lsd-single-page-section lsd-single-page-attributes">';
        if ($this->details_page_options['elements']['attributes']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';

        $output .= '<div class="lsd-single-attributes-box lsd-single-element lsd-single-attributes">' . $attributes . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function availability(): string
    {
        if (!LSD_Components::work_hours()) return '';

        $availability = $this->entity->get_availability();

        // Don't show anything if nothing found
        if (trim($availability) == '') return '';

        $title_alignment = !empty($this->details_page_options['elements']['availability']['title_align']) ? ' ' . $this->details_page_options['elements']['availability']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['availability']['custom_title']) && trim($this->details_page_options['elements']['availability']['custom_title'])
            ? $this->details_page_options['elements']['availability']['custom_title']
            : esc_html__('Working Hours', 'listdom');

        $output = '<div class="lsd-single-page-section lsd-single-page-section-availability">';
        if ($this->details_page_options['elements']['availability']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-availability-box"><div class="lsd-single-element lsd-single-availability">' . $availability . '</div></div>';
        $output .= '</div>';

        return $output;
    }

    public function categories($show_color = true, $color_method = 'bg'): string
    {
        // Display Main Category or All Categories
        $multiple = apply_filters('lsd_listing_display_multiple_categories', false);

        // Get Categories
        $categories = $this->entity->get_categories([
            'show_color' => $show_color,
            'multiple_categories' => $multiple,
            'color_method' => $color_method,
            'enable_link' => $this->details_page_options['elements']['categories']['enable_link'] ?? 1,
        ]);

        // Don't show anything when there is no category!
        if (!trim($categories)) return '';

        return '<div class="lsd-single-element lsd-single-category lsd-listing-category">' . $categories . '</div>';
    }

    public function content($content): string
    {
        $content = $this->entity->get_content($content);

        // Don't show anything when there is no content!
        if (!trim($content)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['content']['title_align']) ? ' ' . $this->details_page_options['elements']['content']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['content']['custom_title']) && trim($this->details_page_options['elements']['content']['custom_title'])
            ? $this->details_page_options['elements']['content']['custom_title']
            : esc_html__('Description', 'listdom');

        // Schema
        $schema = lsd_schema()->description();

        $output = '<div class="lsd-single-page-section lsd-single-page-section-content">';
        if ($this->details_page_options['elements']['content']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-content-wrapper lsd-single-element lsd-single-content" ' . $schema . '>' . $content . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function excerpt($limit = -1, $read_more = false): string
    {
        $excerpt = $this->entity->get_excerpt($limit, $read_more);

        // Don't show anything when there is no content!
        if (!trim($excerpt)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['excerpt']['title_align']) ? ' ' . $this->details_page_options['elements']['excerpt']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['excerpt']['custom_title']) && trim($this->details_page_options['elements']['excerpt']['custom_title'])
            ? $this->details_page_options['elements']['excerpt']['custom_title']
            : esc_html__('Excerpt', 'listdom');

        // Schema
        $schema = lsd_schema()->description();

        $output = '<div class="lsd-single-page-section lsd-single-page-section-excerpt">';
        if ($this->details_page_options['elements']['excerpt']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-excerpt-wrapper lsd-single-element lsd-single-excerpt" ' . $schema . '>' . $excerpt . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function remark(): string
    {
        $remark = $this->entity->get_remark();

        // Don't show anything when there is no remark!
        if (!trim($remark)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['remark']['title_align']) ? ' ' . $this->details_page_options['elements']['remark']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['remark']['custom_title']) && trim($this->details_page_options['elements']['remark']['custom_title'])
            ? $this->details_page_options['elements']['remark']['custom_title']
            : esc_html__('Owner Message', 'listdom');

        $schema = lsd_schema()->scope()->type('https://schema.org/UserComments');

        $output = '<div class="lsd-single-page-section lsd-single-page-section-remark" ' . $schema . '>';
        if ($this->details_page_options['elements']['remark']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-content-wrapper lsd-single-element lsd-single-remark">' . wpautop($remark) . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function features(): string
    {
        $show_icons = $this->details_page_options['elements']['features']['show_icons'] ?? 0;
        $list_style = $this->details_page_options['elements']['features']['list_style'] ?? 'per-row';

        $features = $this->entity->get_features(
            'list',
            $show_icons,
            $this->details_page_options['elements']['features']['enable_link'] ?? 1,
            $list_style
        );

        // Don't show anything when there is no features!
        if (!trim($features)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['features']['title_align']) ? ' ' . $this->details_page_options['elements']['features']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['features']['custom_title']) && trim($this->details_page_options['elements']['features']['custom_title'])
            ? $this->details_page_options['elements']['features']['custom_title']
            : esc_html(lsd_t_label(LSD_Base::TAX_FEATURE, 'plural'));

        $output = '<div class="lsd-single-page-section lsd-single-page-section-features">';
        if ($this->details_page_options['elements']['features']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';

        $output .= '<div class=" lsd-single-features-wrapper lsd-single-element lsd-single-features">' . $features . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function map(): string
    {
        if (!LSD_Components::map()) return '';

        $map = $this->entity->get_map([
            'provider' => $this->details_page_options['elements']['map']['map_provider'] ?? LSD_Map_Provider::def(),
            'style' => $this->details_page_options['elements']['map']['style'] ?? null,
            'gplaces' => $this->details_page_options['elements']['map']['gplaces'] ?? 0,
            'infowindow' => $this->details_page_options['elements']['map']['infowindow'] ?? 1,
            'zoomlevel' => $this->details_page_options['elements']['map']['zoomlevel'] ?? 14,
            'mapcontrols' => [
                'zoom' => $this->details_page_options['elements']['map']['control_zoom'] ?? 'RIGHT_BOTTOM',
                'maptype' => $this->details_page_options['elements']['map']['control_maptype'] ?? 'TOP_LEFT',
                'streetview' => $this->details_page_options['elements']['map']['control_streetview'] ?? 'RIGHT_BOTTOM',
                'scale' => $this->details_page_options['elements']['map']['control_scale'] ?? '0',
                'camera' => $this->details_page_options['elements']['map']['control_camera'] ?? '0',
                'fullscreen' => $this->details_page_options['elements']['map']['control_fullscreen'] ?? '1',
            ],
            'args' => $this->details_page_options['elements']['map'],
        ]);

        // Don't show anything when there is no Map!
        if (!trim($map)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['map']['title_align']) ? ' ' . $this->details_page_options['elements']['map']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['map']['custom_title']) && trim($this->details_page_options['elements']['map']['custom_title'])
            ? $this->details_page_options['elements']['map']['custom_title']
            : esc_html__('See on the Map', 'listdom');

        // Include Map Assets to the page
        LSD_Assets::map();

        $output = '<div class="lsd-single-page-section lsd-single-page-section-map">';
        if ($this->details_page_options['elements']['map']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-listing-googlemap lsd-single-element lsd-single-map">' . $map . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function image(): string
    {
        $featured_image = $this->entity->get_featured_image('full', 'url');

        // Don't show anything when there is no Featured Image!
        if (!trim($featured_image)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['image']['title_align']) ? ' ' . $this->details_page_options['elements']['image']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['image']['custom_title']) && trim($this->details_page_options['elements']['image']['custom_title'])
            ? $this->details_page_options['elements']['image']['custom_title']
            : esc_html__('Listing Image', 'listdom');

        $output = '';
        if ($this->details_page_options['elements']['image']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-element lsd-single-featured-image" ' . lsd_schema()->scope()->type('https://schema.org/ImageObject')->prop('image') . '>' . $featured_image . '</div>';

        return $output;
    }

    public function gallery($style = ''): string
    {
        $gallery = $this->entity->get_gallery([
            'lightbox' => $this->details_page_options['elements']['gallery']['lightbox'] ?? 1,
            'style' => $style ?: ($this->details_page_options['elements']['gallery']['style'] ?? 'list'),
            'thumbnail_status' => $this->details_page_options['elements']['gallery']['thumbnail_status'] ?? 'image',
            'include_thumbnail' => ($this->details_page_options['elements']['gallery']['thumbnail'] ?? 0),
            'image_limit' => ($this->details_page_options['elements']['gallery']['image_limit'] ?? 4),
            'image_height' => ($this->details_page_options['elements']['gallery']['image_height'] ?? '300'),
            'image_fit' => ($this->details_page_options['elements']['gallery']['image_fit'] ?? 'covers'),
        ]);

        // Don't show anything when there is no Gallery!
        if (!trim($gallery)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['gallery']['title_align']) ? ' ' . $this->details_page_options['elements']['gallery']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['gallery']['custom_title']) && trim($this->details_page_options['elements']['gallery']['custom_title'])
            ? $this->details_page_options['elements']['gallery']['custom_title']
            : esc_html__('Listing Gallery', 'listdom');

        $output = '<div class="lsd-single-gallery-box">';
        if ($this->details_page_options['elements']['gallery']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-element lsd-single-gallery">' . $gallery . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function embeds(): string
    {
        $embeds = $this->entity->get_embeds();

        // Don't show anything when there is no Embeds!
        if (!trim($embeds)) return '';

        return '<div class="lsd-single-page-section lsd-single-page-embeds">
            <div class="lsd-single-embeds-box">
                <div class="lsd-single-element lsd-single-embed">' . $embeds . '</div>
            </div>
        </div>';
    }

    public function faq(): string
    {
        $limit = $this->details_page_options['elements']['faq']['count'] ?? 0;
        $faqs = $this->entity->get_faqs($limit);

        // Don't show anything when there is no FAQs!
        if (!trim($faqs)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['faq']['title_align']) ? ' ' . $this->details_page_options['elements']['faq']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['faq']['custom_title']) && trim($this->details_page_options['elements']['faq']['custom_title'])
            ? $this->details_page_options['elements']['faq']['custom_title']
            : esc_html__('FAQs', 'listdom');

        $output = '<div class="lsd-single-page-section lsd-single-page-section-faq">';
        if ($this->details_page_options['elements']['faq']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-element lsd-single-faq">' . $faqs . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function featured_video(): string
    {
        $video = $this->entity->get_featured_video();

        // Don't show anything when there is no Embeds!
        if (!trim($video)) return '';

        return '<div class="lsd-single-page-section lsd-single-page-video">
            <div class="lsd-single-video-box">
                <div class="lsd-single-element lsd-single-video">' . $video . '</div>
            </div>
        </div>';
    }

    public function labels(): string
    {
        $labels = $this->entity->get_labels(
            'tags',
            $this->details_page_options['elements']['labels']['enable_link'] ?? 1
        );

        // Don't show anything when nothing found!
        if (!trim($labels)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['labels']['title_align']) ? ' ' . $this->details_page_options['elements']['labels']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['labels']['custom_title']) && trim($this->details_page_options['elements']['labels']['custom_title'])
            ? $this->details_page_options['elements']['labels']['custom_title']
            : esc_html__('Labels', 'listdom');

        $output = '<div class="lsd-single-page-section lsd-single-page-section-labels">';
        if ($this->details_page_options['elements']['labels']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-element lsd-single-labels">' . $labels . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function price(): string
    {
        // Pricing
        if (!LSD_Components::pricing()) return '';

        $price = $this->entity->get_price();

        // Don't show anything when nothing found!
        if (!trim($price)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['price']['title_align']) ? ' ' . $this->details_page_options['elements']['price']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['price']['custom_title']) && trim($this->details_page_options['elements']['price']['custom_title'])
            ? $this->details_page_options['elements']['price']['custom_title']
            : esc_html__('Price', 'listdom');

        // Schema
        $schema = lsd_schema()->priceRange();

        $output = '';
        if ($this->details_page_options['elements']['price']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-element lsd-single-price" ' . $schema . '><div class="lsd-color-m-bg ' . $this->get_text_class() . '">' . $price . '</div></div>';

        return $output;
    }

    public function share(): string
    {
        if (!LSD_Components::socials()) return '';

        $share = $this->entity->get_share_buttons('single');

        // Don't show anything when nothing found!
        if (!trim($share)) return '';

        // Heading
        $heading = isset($this->details_page_options['elements']['share']['custom_title']) && trim($this->details_page_options['elements']['share']['custom_title'])
            ? $this->details_page_options['elements']['share']['custom_title']
            : esc_html__('Share', 'listdom');

        $output = '<div class="lsd-single-page-section lsd-single-page-section-share">';
        if ($this->details_page_options['elements']['share']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-share-box lsd-single-element lsd-single-share">' . $share . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function cta(): string
    {
        if (!LSD_Components::cta()) return '';

        $cta_element = new LSD_Element_Cta();
        $cta_data = $cta_element->get_data($this->entity);
        $cta_html = $this->entity->get_cta('single', [
            'button_class' => 'lsd-general-button lsd-single-cta-button',
        ]);

        if (!trim($cta_html)) return '';

        $alignment = $this->details_page_options['elements']['cta']['alignment'] ?? 'center';
        $alignment = trim($alignment) ? $alignment : 'center';
        $alignment_class = 'lsd-cta-align-' . sanitize_html_class($alignment);

        $output = '<div class="lsd-single-page-section lsd-single-page-cta ' . $alignment_class . '">';

        if (!empty($this->details_page_options['elements']['cta']['show_title']))
        {
            $custom_title = $this->details_page_options['elements']['cta']['custom_title'] ?? '';
            $default_heading = trim($cta_data['title'] ?? '') ?: esc_html__('Call to Action', 'listdom');
            $heading = trim($custom_title) ? $custom_title : $default_heading;

            $output .= '<h2 class="lsd-single-page-section-title">' . esc_html($heading) . '</h2>';
        }

        $output .= '<div class="lsd-single-cta">' . LSD_Kses::element($cta_html) . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function related(): string
    {
        if (!LSD_Components::related()) return '';

        $related = $this->entity->get_related_listings($this->details_page_options['elements']['related']);

        // Don't show anything when nothing found!
        if (!trim($related)) return '';

        // Heading
        $heading = isset($this->details_page_options['elements']['related']['custom_title']) && trim($this->details_page_options['elements']['related']['custom_title'])
            ? $this->details_page_options['elements']['related']['custom_title']
            : esc_html__('Related', 'listdom');

        $output = '<div class="lsd-single-page-section lsd-single-page-section-related">';
        if ($this->details_page_options['elements']['related']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-related-box lsd-single-element lsd-single-related">' . $related . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function tags(): string
    {
        $tags = $this->entity->get_tags(
            $this->details_page_options['elements']['tags']['enable_link'] ?? 1
        );

        // Don't show anything when there is no tag!
        if (!trim($tags)) return '';

        // Heading
        $heading = isset($this->details_page_options['elements']['tags']['custom_title']) && trim($this->details_page_options['elements']['tags']['custom_title'])
            ? $this->details_page_options['elements']['tags']['custom_title']
            : esc_html__('Tags', 'listdom');

        $output = '<div class="lsd-single-tags-wrapper lsd-fe-icon-wrapper">';
        if ($this->details_page_options['elements']['tags']['show_title']) $output .= '<div class="lsd-single-label-inline"><i class="lsd-fe-icon fa fa-tags fa-lg" aria-hidden="true"></i> <span>' . $heading . '</span></div>';
        $output .= '<div class="lsd-single-element lsd-single-tags">' . $tags . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function title($claim = true, $favorite = true, $compare = true): string
    {
        $title = $this->entity->get_title();

        // Don't show anything when there is no title!
        if (!trim($title)) return '';

        // Schema
        $schema = lsd_schema()->name();

        // Title Wrapper
        $output = '<div class="lsd-single-title-wrapper">';

        // Title (with claim icon inside <h1>)
        $output .= '<h1 class="lsd-single-element lsd-single-title" ' . $schema . '>';
        $output .= $title;

        // Add claimed icon inside h1
        if ($this->entity->is_claimed()) $output .= ' <span class="lsd-tooltip" data-lsd-tooltip="' . esc_attr__('Verified', 'listdom') . '"><i class="lsd-fe-icon fas fa-check-circle lsd-claimed-icon"></i></span>';

        $output .= '</h1>';

        // Claim & Favorite Wrapper
        $output .= '<div class="lsd-single-title-claim-favorite-wrapper">';

        // Claim button
        if ($claim) $output .= LSD_Kses::element($this->entity->get_claim_button());

        // Compare & Favorite buttons
        if ($compare) $output .= LSD_Kses::element($this->entity->get_compare_button());
        if ($favorite) $output .= LSD_Kses::element($this->entity->get_favorite_button('button'));

        $output .= '</div>'; // close claim/favorite wrapper
        $output .= '</div>'; // close title wrapper

        return $output;
    }

    public function contact_info(): string
    {
        $contact = $this->entity->get_contact_info($this->details_page_options['elements']['contact'] ?? []);

        // Don't show anything when there is no contact information!
        if (!trim($contact)) return '';

        $title_alignment = !empty($this->details_page_options['elements']['contact']['title_align']) ? ' ' . $this->details_page_options['elements']['contact']['title_align'] : '';

        // Heading
        $heading = isset($this->details_page_options['elements']['contact']['custom_title']) && trim($this->details_page_options['elements']['contact']['custom_title'])
            ? $this->details_page_options['elements']['contact']['custom_title']
            : esc_html__('Contact Info', 'listdom');

        $output = '<div class="lsd-single-page-section lsd-single-page-section-contact">';
        if ($this->details_page_options['elements']['contact']['show_title']) $output .= '<h2 class="lsd-single-page-section-title lsd-fe-title' . $title_alignment . '">' . $heading . '</h2>';
        $output .= '<div class="lsd-single-element lsd-single-contact-info lsd-single-contact-box">' . $contact . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function section(array $elements): string
    {
        // Global Element Options
        $global_elements = $this->details_page_options['elements'] ?? [];

        $content = '';
        foreach ($elements as $key => $element)
        {
            $enabled = isset($element['enabled']) && $element['enabled'];
            if (!$enabled) continue;

            $global_enabled = isset($global_elements[$key]['enabled']) && $global_elements[$key]['enabled'];
            if (!$global_enabled) continue;

            if ($key === 'title') $content .= '<div class="lsd-single-page-section">' . $this->replaced_listing_section('title') . '</div>';
            else if ($key === 'price') $content .= $this->replaced_listing_section('price');
            else if ($key === 'address') $content .= $this->replaced_listing_section('address');
            else if ($key === 'locations') $content .= $this->replaced_listing_section('locations');
            else if ($key === 'share') $content .= LSD_Kses::element($this->share());
            else if ($key === 'categories') $content .= $this->replaced_listing_section('categories');
            else if ($key === 'image') $content .= $this->replaced_listing_section('image');
            else if ($key === 'gallery') $content .= $this->replaced_listing_section('gallery');
            else if ($key === 'embed') $content .= $this->replaced_listing_section('embeds', 'rich');
            else if ($key === 'faq') $content .= LSD_Kses::element($this->faq());
            else if ($key === 'video') $content .= $this->replaced_listing_section('featured_video', 'rich');
            else if ($key === 'labels') $content .= LSD_Kses::element($this->labels());
            else if ($key === 'content') $content .= $this->replaced_listing_section('content', 'element', [$this->filtered_content]);
            else if ($key === 'remark') $content .= $this->replaced_listing_section('remark');
            else if ($key === 'tags') $content .= LSD_Kses::element($this->tags());
            else if ($key === 'contact') $content .= $this->replaced_listing_section('contact_info');
            else if ($key === 'features') $content .= $this->replaced_listing_section('features');
            else if ($key === 'attributes') $content .= $this->replaced_listing_section('attributes');
            else if ($key === 'map') $content .= $this->replaced_listing_section('map', 'form');
            else if ($key === 'owner') $content .= $this->replaced_listing_section('owner', 'form');
            else if ($key === 'abuse') $content .= LSD_Kses::form($this->abuse());
            else if ($key === 'availability') $content .= $this->replaced_listing_section('availability');
            else if ($key === 'excerpt') $content .= $this->replaced_listing_section('excerpt');
            else if ($key === 'backbutton') $content .= LSD_Kses::element($this->backbutton());
            else if ($key === 'breadcrumb') $content .= $this->replaced_listing_section('breadcrumb');
            else if ($key === 'cta') $content .= LSD_Kses::element($this->cta());
            else $content .= '{' . $key . '}';
        }

        return $content;
    }
}
