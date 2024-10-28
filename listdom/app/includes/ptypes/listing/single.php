<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Listing Post Types Single Class.
 *
 * @class LSD_PTypes_Listing_Single
 * @version    1.0.0
 */
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

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        // Details Page options
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

    public function bootstrap($post, $content = null)
    {
        // Single Page Pattern
        $this->pattern = LSD_Options::details_page_pattern();

        $style = $this->details_page_options['general']['style'] ?? 'style1';
        $listing_style = '';
        $listing_elements = [];

        // Apply Listing Display Options
        if ($this->isPro() && isset($this->details_page_options['general']['displ']) && $this->details_page_options['general']['displ'])
        {
            $displ = get_post_meta($post->ID, 'lsd_displ', true);
            if (!is_array($displ)) $displ = [];

            // Listing Style
            if (isset($displ['style']) && trim($displ['style'])) $listing_style = $displ['style'];

            if (isset($displ['elements']) && is_array($displ['elements']) && count($displ['elements']) && isset($displ['elements_global']) && !$displ['elements_global']) $listing_elements = $displ['elements'];
        }

        // Get Listing Style by Category and Others
        $listing_style = apply_filters('lsd_listing_details_style', $listing_style, $post->ID);

        // Get Listing Elements by Category and Others
        $listing_elements = apply_filters('lsd_listing_details_elements', $listing_elements, $post->ID);

        // Listing Elements
        if (is_array($listing_elements) && count($listing_elements))
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

        $this->details_page_options['general']['style'] = $listing_style ?: $style;
        $this->style = $listing_style ?: $style;
        $this->filtered_content = $content;

        $this->entity = new LSD_Entity_Listing($post);

        // Filter The Details Page Options
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
            $theme_template = isset($this->details_page_options['general']['theme_template']) && trim($this->details_page_options['general']['theme_template']) ? $this->details_page_options['general']['theme_template'] : null;
            if ($theme_template)
            {
                $located_template = locate_template($theme_template);

                // Template Found
                if ($located_template) $template = $located_template;
            }
        }

        return $template;
    }

    public function filter_content($content)
    {
        // It's not singular page of listing
        if (!is_singular($this->PT)) return $content;

        // Body Started?
        if (!LSD_LifeCycle::isBodyStarted()) return $content;

        // Content Already Printed
        if (LSD_LifeCycle::isContentPrinted()) return $content;

        global $post;
        if ($post->post_type != $this->PT) return $content;

        // Is it an endpoint?
        if ($ep = LSD_Endpoints::is()) return LSD_Endpoints::output($ep);

        // Mark Content as Printed
        LSD_LifeCycle::setContentPrinted(true);

        // Get Listing Details Content
        return $this->get($content);
    }

    public function get($content)
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

        // Listing Labels
        if (strpos($this->pattern, '{labels}') !== false)
        {
            $rendered = str_replace('{labels}', LSD_Kses::element($this->labels()), $rendered);
        }

        // Listing Featured Image
        if (strpos($this->pattern, '{image}') !== false)
        {
            $rendered = str_replace('{image}', LSD_Kses::element($this->image()), $rendered);
        }

        // Listing Gallery
        if (strpos($this->pattern, '{gallery}') !== false)
        {
            $gallery = $this->gallery();
            $rendered = str_replace('{gallery}', LSD_Kses::element($gallery), $rendered);
        }

        // Listing Embeds
        if (strpos($this->pattern, '{embed}') !== false)
        {
            $embeds = $this->embeds();
            $rendered = str_replace('{embed}', LSD_Kses::rich($embeds), $rendered);
        }

        // Listing Featured Video
        if (strpos($this->pattern, '{video}') !== false)
        {
            $video = $this->featured_video();
            $rendered = str_replace('{video}', LSD_Kses::rich($video), $rendered);
        }

        // Listing Address
        if (strpos($this->pattern, '{address}') !== false)
        {
            $address = $this->address();
            $rendered = str_replace('{address}', LSD_Kses::element($address), $rendered);
        }

        // Listing Locations
        if (strpos($this->pattern, '{locations}') !== false)
        {
            $locations = $this->locations();
            if (trim($locations)) $locations = '<div class="lsd-single-locations-box">' . $locations . '</div>';

            $rendered = str_replace('{locations}', LSD_Kses::element($locations), $rendered);
        }

        // Listing Owner
        if (strpos($this->pattern, '{owner}') !== false)
        {
            $owner = $this->owner();
            $rendered = str_replace('{owner}', LSD_Kses::form($owner), $rendered);
        }

        // Listing Attributes
        if (strpos($this->pattern, '{attributes}') !== false)
        {
            $attributes = $this->attributes();
            $rendered = str_replace('{attributes}', LSD_Kses::element($attributes), $rendered);
        }

        // Listing Availability
        if (strpos($this->pattern, '{availability}') !== false)
        {
            $availability = $this->availability();
            $rendered = str_replace('{availability}', LSD_Kses::element($availability), $rendered);
        }

        // Listing Categories
        if (strpos($this->pattern, '{categories}') !== false)
        {
            $categories = $this->categories();
            $rendered = str_replace('{categories}', LSD_Kses::element($categories), $rendered);
        }

        // Post Content
        if (strpos($this->pattern, '{content}') !== false)
        {
            $content = $this->content($this->filtered_content);
            $rendered = str_replace('{content}', LSD_Kses::element($content), $rendered);
        }

        // Remark
        if (strpos($this->pattern, '{remark}') !== false)
        {
            $remark = $this->remark();
            $rendered = str_replace('{remark}', LSD_Kses::element($remark), $rendered);
        }

        // Listing Features
        if (strpos($this->pattern, '{features}') !== false)
        {
            $features = $this->features();
            $rendered = str_replace('{features}', LSD_Kses::element($features), $rendered);
        }

        // Listing Map
        if (strpos($this->pattern, '{map}') !== false)
        {
            $map = $this->map();
            $rendered = str_replace('{map}', LSD_Kses::form($map), $rendered);
        }

        // Listing Title
        if (strpos($this->pattern, '{title}') !== false)
        {
            $title = $this->title();
            $rendered = str_replace('{title}', LSD_Kses::element($title), $rendered);
        }

        // Listing Price
        if (strpos($this->pattern, '{price}') !== false)
        {
            $rendered = str_replace('{price}', LSD_Kses::element($this->price()), $rendered);
        }

        // Listing Share
        if (strpos($this->pattern, '{share}') !== false)
        {
            $share = $this->share();
            $rendered = str_replace('{share}', LSD_Kses::element($share), $rendered);
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
            $contact_info = $this->contact_info();
            $rendered = str_replace('{contact}', LSD_Kses::element($contact_info), $rendered);
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
        $output = (new LSD_Builders())->single($this)->build($this->style);

        // Add Filter
        add_filter('the_content', [$this, 'filter_content']);

        // Wrap the content
        return $this->wrapper($output);
    }

    public function wrapper($content)
    {
        // Style Wrapper Class
        $style = (is_numeric($this->style) ? 'builder' : $this->style);

        $schema = lsd_schema()->scope()->type(null, $this->entity->get_data_category());
        $rendered = '<div class="lsd-single-page-wrapper lsd-font-m lsd-single-' . $style . '" ' . $schema . '>' . $content . '</div>';

        // Remove remained placeholders
        return preg_replace('/{.*}/', '', apply_filters('lsd_listing_single_content', $rendered, $this));
    }

    public function address(): string
    {
        $address = $this->entity->get_address(false);
        $title_alignment = !empty($this->details_page_options['elements']['address']['title_align']) ? ' ' . $this->details_page_options['elements']['address']['title_align'] : '';

        // Don't show anything when there is no Address!
        if (!trim($address)) return '';

        // Schema
        $schema = lsd_schema()->address();

        $output = '<div class="lsd-single-page-section">';
        if ($this->details_page_options['elements']['address']['show_title']) $output .= '<div class="lsd-single-label' . $title_alignment . '">' . esc_html__('Address', 'listdom') . '</div>';
        $output .= '<div class="lsd-single-element lsd-single-address" ' . $schema . '>' . $address . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function locations(): string
    {
        $locations = $this->entity->get_locations();
        $title_alignment = !empty($this->details_page_options['elements']['locations']['title_align']) ? ' ' . $this->details_page_options['elements']['locations']['title_align'] : '';

        // Don't show anything when there is no locations!
        if (!trim($locations)) return '';

        $output = '';
        if ($this->details_page_options['elements']['locations']['show_title']) $output .= '<div class="lsd-single-label-inline' . $title_alignment . '">' . esc_html__('Locations', 'listdom') . '</div>';
        $output .= '<div class="lsd-single-element lsd-single-locations">' . $locations . '</div>';

        return $output;
    }

    public function owner(): string
    {
        $owner = $this->entity->get_owner('details', $this->details_page_options['elements']['owner']);
        $title_alignment = !empty($this->details_page_options['elements']['owner']['title_align']) ? ' ' . $this->details_page_options['elements']['owner']['title_align'] : '';

        // Don't show anything if nothing found
        if (trim($owner) === '') return '';

        $output = '<div class="lsd-single-page-section lsd-single-page-owner">';
        if ($this->details_page_options['elements']['owner']['show_title']) $output .= '<h2 class="lsd-single-page-section-title' . $title_alignment . '">' . esc_html__('Author Info', 'listdom') . '</h2>';
        $output .= '<div class="lsd-single-owner-box lsd-single-element lsd-single-owner">' . $owner . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function abuse(): string
    {
        $abuse = $this->entity->get_abuse();
        $title_alignment = !empty($this->details_page_options['elements']['abuse']['title_align']) ? ' ' . $this->details_page_options['elements']['abuse']['title_align'] : '';

        // Don't show anything if nothing found
        if (trim($abuse) == '') return '';

        $output = '<div class="lsd-single-page-section lsd-single-page-abuse">';
        if ($this->details_page_options['elements']['abuse']['show_title']) $output .= '<h2 class="lsd-single-page-section-title' . $title_alignment . '">' . esc_html__('Report Abuse', 'listdom') . '</h2>';
        $output .= '<div class="lsd-single-abuse-box lsd-single-element lsd-single-abuse">' . $abuse . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function attributes(): string
    {
        $show_icons = $this->details_page_options['elements']['attributes']['show_icons'] ?? 0;
        $show_attribute_title = $this->details_page_options['elements']['attributes']['show_attribute_title'] ?? 1;
        $attributes = $this->entity->get_attributes($show_icons, $show_attribute_title);

        $title_alignment = !empty($this->details_page_options['elements']['attributes']['title_align']) ? ' ' . $this->details_page_options['elements']['attributes']['title_align'] : '';

        // Don't show anything when there is no attribute!
        if (!trim($attributes)) return '';

        $output = '<div class="lsd-single-page-section lsd-single-page-attributes">';
        if ($this->details_page_options['elements']['attributes']['show_title']) $output .= '<h2 class="lsd-single-page-section-title' . $title_alignment . '">' . esc_html__('Listing Details', 'listdom') . '</h2>';

        $output .= '<div class="lsd-single-attributes-box lsd-single-element lsd-single-attributes">' . $attributes . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function availability(): string
    {
        $availability = $this->entity->get_availability();
        $title_alignment = !empty($this->details_page_options['elements']['availability']['title_align']) ? ' ' . $this->details_page_options['elements']['availability']['title_align'] : '';

        // Don't show anything if nothing found
        if (trim($availability) == '') return '';

        $output = '<div class="lsd-single-page-section lsd-single-page-section-availability">';
        if ($this->details_page_options['elements']['availability']['show_title']) $output .= '<h2 class="lsd-single-page-section-title' . $title_alignment . '">' . esc_html__('Availability Time', 'listdom') . '</h2>';
        $output .= '<div class="lsd-single-availability-box"><div class="lsd-single-element lsd-single-availability">' . $availability . '</div></div>';
        $output .= '</div>';

        return $output;
    }

    public function categories($show_color = true, $color_method = 'bg'): string
    {
        // Display Main Category or All Categories
        $multiple = apply_filters('lsd_listing_display_multiple_categories', false);
        $title_alignment = !empty($this->details_page_options['elements']['categories']['title_align']) ? ' ' . $this->details_page_options['elements']['categories']['title_align'] : '';

        // Get Categories
        $categories = $this->entity->get_categories($show_color, $multiple, $color_method);

        // Don't show anything when there is no category!
        if (!trim($categories)) return '';

        $output = '';
        if ($this->details_page_options['elements']['categories']['show_title']) $output .= '<div class="lsd-single-label-inline' . $title_alignment . '"><i class="lsd-icon fa fa-folder-o fa-lg" aria-hidden="true"></i> ' . esc_html__('Category', 'listdom') . '</div>';
        $output .= '<div class="lsd-single-element lsd-single-category lsd-listing-category">' . $categories . '</div>';

        return $output;
    }

    public function content($content): string
    {
        $content = $this->entity->get_content($content);
        $title_alignment = !empty($this->details_page_options['elements']['content']['title_align']) ? ' ' . $this->details_page_options['elements']['content']['title_align'] : '';

        // Don't show anything when there is no content!
        if (!trim($content)) return '';

        // Schema
        $schema = lsd_schema()->description();

        $output = '<div class="lsd-single-page-section lsd-single-page-section-content">';
        if ($this->details_page_options['elements']['content']['show_title']) $output .= '<h2 class="lsd-single-page-section-title' . $title_alignment . '">' . esc_html__('Description', 'listdom') . '</h2>';
        $output .= '<div class="lsd-single-content-wrapper lsd-single-element lsd-single-content" ' . $schema . '>' . $content . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function remark(): string
    {
        $remark = $this->entity->get_remark();
        $title_alignment = !empty($this->details_page_options['elements']['remark']['title_align']) ? ' ' . $this->details_page_options['elements']['remark']['title_align'] : '';

        // Don't show anything when there is no remark!
        if (!trim($remark)) return '';

        $schema = lsd_schema()->scope()->type('https://schema.org/UserComments');

        $output = '<div class="lsd-single-page-section lsd-single-page-section-remark" ' . $schema . '>';
        if ($this->details_page_options['elements']['remark']['show_title']) $output .= '<h2 class="lsd-single-page-section-title' . $title_alignment . '">' . esc_html__('Owner Message', 'listdom') . '</h2>';
        $output .= '<div class="lsd-single-content-wrapper lsd-single-element lsd-single-remark">' . $remark . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function features(): string
    {
        $show_icons = $this->details_page_options['elements']['features']['show_icons'] ?? 0;
        $list_style = $this->details_page_options['elements']['features']['list_style'] ?? 'per-row';

        $features = $this->entity->get_features('list', $show_icons, $list_style);
        $title_alignment = !empty($this->details_page_options['elements']['features']['title_align']) ? ' ' . $this->details_page_options['elements']['features']['title_align'] : '';

        // Don't show anything when there is no features!
        if (!trim($features)) return '';

        $output = '<div class="lsd-single-page-section lsd-single-page-section-features">';
        if ($this->details_page_options['elements']['features']['show_title']) $output .= '<h2 class="lsd-single-page-section-title' . $title_alignment . '">' . esc_html__('Features', 'listdom') . '</h2>';

        $output .= '<div class=" lsd-single-features-wrapper lsd-single-element lsd-single-features">' . $features . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function map(): string
    {
        $map = $this->entity->get_map([
            'provider' => $this->details_page_options['elements']['map']['map_provider'] ?? LSD_Map_Provider::def(),
            'style' => $this->details_page_options['elements']['map']['style'] ?? null,
            'gplaces' => $this->details_page_options['elements']['map']['gplaces'] ?? 0,
            'mapcontrols' => [
                'zoom' => $this->details_page_options['elements']['map']['control_zoom'] ?? 'RIGHT_BOTTOM',
                'maptype' => $this->details_page_options['elements']['map']['control_maptype'] ?? 'TOP_LEFT',
                'streetview' => $this->details_page_options['elements']['map']['control_streetview'] ?? 'RIGHT_BOTTOM',
                'scale' => $this->details_page_options['elements']['map']['control_scale'] ?? '0',
                'fullscreen' => $this->details_page_options['elements']['map']['control_fullscreen'] ?? '1',
            ],
            'args' => $this->details_page_options['elements']['map'],
        ]);

        $title_alignment = !empty($this->details_page_options['elements']['map']['title_align']) ? ' ' . $this->details_page_options['elements']['map']['title_align'] : '';

        // Don't show anything when there is no Map!
        if (!trim($map)) return '';

        // Include Map Assets to the page
        LSD_Assets::map();

        $output = '<div class="lsd-single-page-section lsd-single-page-section-map">';
        if ($this->details_page_options['elements']['map']['show_title']) $output .= '<h2 class="lsd-single-page-section-title' . $title_alignment . '">' . esc_html__('See on the Map', 'listdom') . '</h2>';
        $output .= '<div class="lsd-listing-googlemap lsd-single-element lsd-single-map">' . $map . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function image(): string
    {
        $featured_image = $this->entity->get_featured_image();
        $title_alignment = !empty($this->details_page_options['elements']['image']['title_align']) ? ' ' . $this->details_page_options['elements']['image']['title_align'] : '';

        // Don't show anything when there is no Featured Image!
        if (!trim($featured_image)) return '';

        $output = '';
        if ($this->details_page_options['elements']['image']['show_title']) $output .= '<div class="lsd-single-label' . $title_alignment . '">' . esc_html__('Listing Image', 'listdom') . '</div>';
        $output .= '<div class="lsd-single-element lsd-single-featured-image" ' . lsd_schema()->scope()->type('https://schema.org/ImageObject') . '>' . $featured_image . '</div>';

        return $output;
    }

    public function gallery($style = ''): string
    {
        $gallery = $this->entity->get_gallery([
            'lightbox' => $this->details_page_options['elements']['gallery']['lightbox'] ?? 1,
            'style' => $style ?: ($this->details_page_options['elements']['gallery']['style'] ?? 'lightbox'),
            'include_thumbnail' => ($this->details_page_options['elements']['gallery']['thumbnail'] ?? 0),
        ]);

        $title_alignment = !empty($this->details_page_options['elements']['gallery']['title_align']) ? ' ' . $this->details_page_options['elements']['gallery']['title_align'] : '';

        // Don't show anything when there is no Gallery!
        if (!trim($gallery)) return '';

        $output = '<div class="lsd-single-gallery-box">';
        if ($this->details_page_options['elements']['gallery']['show_title']) $output .= '<div class="lsd-single-label' . $title_alignment . '">' . esc_html__('Listing Gallery', 'listdom') . '</div>';
        $output .= '<div class="lsd-single-element lsd-single-gallery">' . $gallery . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function embeds(): string
    {
        $embeds = $this->entity->get_embeds();
        $title_alignment = !empty($this->details_page_options['elements']['embed']['title_align']) ? ' ' . $this->details_page_options['elements']['embed']['title_align'] : '';

        // Don't show anything when there is no Embeds!
        if (!trim($embeds)) return '';

        $output = '<div class="lsd-single-page-section lsd-single-page-embeds"><div class="lsd-single-embeds-box">';
        if ($this->details_page_options['elements']['embed']['show_title']) $output .= '<div class="lsd-single-label' . $title_alignment . '">' . esc_html__('Embed Codes', 'listdom') . '</div>';
        $output .= '<div class="lsd-single-element lsd-single-embed">' . $embeds . '</div>';
        $output .= '</div></div>';

        return $output;
    }

    public function featured_video(): string
    {
        $video = $this->entity->get_featured_video();
        $title_alignment = !empty($this->details_page_options['elements']['embed']['title_align']) ? ' ' . $this->details_page_options['elements']['embed']['title_align'] : '';

        // Don't show anything when there is no Embeds!
        if (!trim($video)) return '';

        $output = '<div class="lsd-single-page-section lsd-single-page-video"><div class="lsd-single-video-box">';
        if ($this->details_page_options['elements']['embed']['show_title']) $output .= '<div class="lsd-single-label' . $title_alignment . '">' . esc_html__('Video', 'listdom') . '</div>';
        $output .= '<div class="lsd-single-element lsd-single-video">' . $video . '</div>';
        $output .= '</div></div>';

        return $output;
    }

    public function labels(): string
    {
        $labels = $this->entity->get_labels();
        $title_alignment = !empty($this->details_page_options['elements']['labels']['title_align']) ? ' ' . $this->details_page_options['elements']['labels']['title_align'] : '';

        // Don't show anything when nothing found!
        if (!trim($labels)) return '';

        $output = '<div class="lsd-single-page-section lsd-single-page-section-labels">';
        if ($this->details_page_options['elements']['labels']['show_title']) $output .= '<div class="lsd-single-label' . $title_alignment . '">' . esc_html__('Labels', 'listdom') . '</div>';
        $output .= '<div class="lsd-single-element lsd-single-labels">' . $labels . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function price(): string
    {
        $price = $this->entity->get_price();
        $title_alignment = !empty($this->details_page_options['elements']['price']['title_align']) ? ' ' . $this->details_page_options['elements']['price']['title_align'] : '';

        // Don't show anything when nothing found!
        if (!trim($price)) return '';

        // Schema
        $schema = lsd_schema()->priceRange();

        $output = '';
        if ($this->details_page_options['elements']['price']['show_title']) $output .= '<div class="lsd-single-label' . $title_alignment . '">' . esc_html__('Price', 'listdom') . '</div>';
        $output .= '<div class="lsd-single-element lsd-single-price" ' . $schema . '><div class="lsd-color-m-bg ' . $this->get_text_class() . '">' . $price . '</div></div>';

        return $output;
    }

    public function share(): string
    {
        $share = $this->entity->get_share_buttons('single');
        // Don't show anything when nothing found!
        if (!trim($share)) return '';

        $output = '<div class="lsd-single-page-section lsd-single-page-section-share">';
        if ($this->details_page_options['elements']['share']['show_title']) $output .= '<h2 class="lsd-single-page-section-title">' . esc_html__('Share', 'listdom') . '</h2>';
        $output .= '<div class="lsd-single-share-box lsd-single-element lsd-single-share">' . $share . '</div>';
        $output .= '</div>';

        return $output;
    }

    public function tags(): string
    {
        $tags = $this->entity->get_tags();
        // Don't show anything when there is no tag!
        if (!trim($tags)) return '';

        $output = '<div class="lsd-single-tags-wrapper">';
        if ($this->details_page_options['elements']['tags']['show_title']) $output .= '<div class="lsd-single-label-inline"><i class="lsd-icon fa fa-tags fa-lg" aria-hidden="true"></i> <span>' . esc_html__('Tags', 'listdom') . '</span></div>';
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

        $output = '<div class="lsd-single-title-wrapper">';
        if ($this->details_page_options['elements']['title']['show_title']) $output .= '<div class="lsd-single-label">' . esc_html__('Title', 'listdom') . '</div>';

        // Title
        $output .= '<h1 class="lsd-single-element lsd-single-title" ' . $schema . '>' . $title . '</h1>';

        // Claim & Favorite Wrapper
        $output .= '<div class="lsd-single-title-claim-favorite-wrapper">';

        // Claim
        if ($claim) $output .= LSD_Kses::element($this->entity->get_claim_button());

        // Favorite
        if ($compare) $output .= LSD_Kses::element($this->entity->get_compare_button());
        if ($favorite) $output .= LSD_Kses::element($this->entity->get_favorite_button('button'));

        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

    public function contact_info(): string
    {
        $contact = $this->entity->get_contact_info();
        $title_alignment = !empty($this->details_page_options['elements']['contact']['title_align']) ? ' ' . $this->details_page_options['elements']['contact']['title_align'] : '';

        // Don't show anything when there is no contact information!
        if (!trim($contact)) return '';

        $output = '<div class="lsd-single-page-section lsd-single-page-section-contact">';
        if ($this->details_page_options['elements']['contact']['show_title']) $output .= '<h2 class="lsd-single-page-section-title' . $title_alignment . '">' . esc_html__('Contact Info', 'listdom') . '</h2>';
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

            if ($key === 'title') $content .= '<div class="lsd-single-page-section">' . LSD_Kses::element($this->title()) . '</div>';
            else if ($key === 'price') $content .= LSD_Kses::element($this->price());
            else if ($key === 'address') $content .= LSD_Kses::element($this->address());
            else if ($key === 'locations') $content .= LSD_Kses::element($this->locations());
            else if ($key === 'share') $content .= LSD_Kses::element($this->share());
            else if ($key === 'categories') $content .= LSD_Kses::element($this->categories());
            else if ($key === 'image') $content .= LSD_Kses::element($this->image());
            else if ($key === 'gallery') $content .= LSD_Kses::element($this->gallery());
            else if ($key === 'embed') $content .= LSD_Kses::rich($this->embeds());
            else if ($key === '$video') $content .= LSD_Kses::rich($this->featured_video());
            else if ($key === 'labels') $content .= LSD_Kses::element($this->labels());
            else if ($key === 'content') $content .= LSD_Kses::element($this->content($this->filtered_content));
            else if ($key === 'remark') $content .= LSD_Kses::element($this->remark());
            else if ($key === 'tags') $content .= LSD_Kses::element($this->tags());
            else if ($key === 'contact') $content .= LSD_Kses::element($this->contact_info());
            else if ($key === 'features') $content .= LSD_Kses::element($this->features());
            else if ($key === 'attributes') $content .= LSD_Kses::element($this->attributes());
            else if ($key === 'map') $content .= LSD_Kses::form($this->map());
            else if ($key === 'owner') $content .= LSD_Kses::form($this->owner());
            else if ($key === 'abuse') $content .= LSD_Kses::form($this->abuse());
            else if ($key === 'availability') $content .= LSD_Kses::element($this->availability());
            else $content .= '{' . $key . '}';
        }

        return $content;
    }
}
