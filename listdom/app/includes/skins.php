<?php

class LSD_Skins extends LSD_Base
{
    public $args = [];
    public $listings = [];
    public $atts = [];
    public $skin_options = [];
    public $filter_options = [];
    public $exclude_options = [];

    public $search_options = [];
    public $sm_shortcode;
    public $sm_position;
    public $sm_sticky = 0;
    public $sm_sticky_offset = null;
    public $sm_ajax = 0;
    public $mapcontrols = [];
    public $sorts = [];
    public $sortbar = false;
    public $orderby = 'post_date';
    public $order = 'DESC';
    public $sort_style = '';
    public $skin = 'list';
    public $settings = [];
    public $id;
    public $next_page = 1;
    public $page = 1;
    public $limit = 300;
    public $found_listings;
    public $style;
    public $default_style;
    public $load_more = false;
    public $pagination = 'loadmore';

    public $display_title = true;
    public $display_is_claimed = true;
    public $display_labels = false;
    public $display_image = true;
    public $display_contact_info = true;
    public $display_location = true;
    public $display_price_class = true;
    public $display_description = true;
    public $display_address = true;
    public $display_availability = true;
    public $display_categories = true;
    public $display_price = true;
    public $display_favorite_icon = true;
    public $display_compare_icon = true;
    public $display_review_stars = true;
    public $display_slider_arrows = true;
    public $display_read_more_button = true;
    public $display_cta = false;
    public $description_length = 12;
    public $content_type = 'excerpt';

    public $image_method = 'cover';
    public $image_fit = 'cover';
    public $display_share_buttons = false;
    public $columns = 1;
    public $default_view = 'grid';
    public $html_class = '';
    public $widget = false;
    public $cta_mode = 'inherit';
    public $cta_alignment = 'left';
    public $post_id;
    public $mapsearch = false;
    public $autoplay = true;
    public $autoGPS = false;
    public $maxBounds = [];
    public $map_provider = 'leaflet';
    public $map_height;
    public $mousewheel_zoom = false;
    public $ignore_map_exclusion = false;
    public $price_components = [];
    public $connected_shortcodes = [];
    public $collection_schema_fragment_id = '';
    public $map_position;
    public $map_component = true;
    public $collection_output_rendered = false;
    protected $cta_override = [];

    protected $sort_meta_key = null;
    protected $sort_meta_orderby = null;
    protected $sort_meta_type = null;
    protected $sort_custom_mode = null;
    protected $sort_distance_reference = null;
    protected $sort_search_context = [];

    public function __construct()
    {
        // Settings
        $this->settings = LSD_Options::settings();

        // Price Components
        $this->price_components = LSD_Options::price_components();

        // Map Component
        $this->map_component = LSD_Components::map();
    }

    public function init()
    {
        // Add Filters
        add_filter('posts_join', [$this, 'query_join'], 10, 2);
        add_filter('posts_where', [$this, 'query_where'], 10, 2);

        (new LSD_Skins_Singlemap())->init();
        (new LSD_Skins_List())->init();
        (new LSD_Skins_Grid())->init();
        (new LSD_Skins_Side())->init();
        (new LSD_Skins_Listgrid())->init();
        (new LSD_Skins_Halfmap())->init();
        (new LSD_Skins_Table())->init();
        (new LSD_Skins_Cover())->init();
        (new LSD_Skins_Carousel())->init();
        (new LSD_Skins_Slider())->init();
        (new LSD_Skins_Masonry())->init();
        (new LSD_Skins_Accordion())->init();
        (new LSD_Skins_Mosaic())->init();
        (new LSD_Skins_Timeline())->init();
        (new LSD_Skins_Gallery())->init();
    }

    public function start($atts)
    {
        $this->atts = apply_filters('lsd_skins_atts', $atts);
        $this->id = LSD_id::get(isset($this->atts['id'])
            ? (int) $this->atts['id']
            : wp_rand(100, 999)
        );

        $update_address_bar = apply_filters('lsd_update_page_address', true, $this);
        LSD_Assets::update_address_bar($update_address_bar, $this->id);

        // Skin Options
        $this->skin_options = $this->atts['lsd_display'][$this->skin] ?? [];

        // Map Position
        $this->map_position = isset($this->skin_options['map_position']) && trim($this->skin_options['map_position']) ? $this->skin_options['map_position'] : 'top';

        // Search Options
        $this->search_options = $this->atts['lsd_search'] ?? [];
        $this->sm_shortcode = isset($this->search_options['shortcode']) && trim($this->search_options['shortcode']) ? $this->search_options['shortcode'] : null;
        $this->sm_position = isset($this->search_options['position']) && trim($this->search_options['position']) ? $this->search_options['position'] : 'top';
        $this->sm_sticky = isset($this->search_options['sticky']) && trim((string) $this->search_options['sticky']) !== '' ? (int) $this->search_options['sticky'] : 0;
        $sm_sticky_offset = isset($this->search_options['sticky_offset']) ? trim((string) $this->search_options['sticky_offset']) : '';
        $this->sm_sticky_offset = $sm_sticky_offset === '' ? null : max(0, (int) $sm_sticky_offset);
        $this->sm_ajax = isset($this->search_options['ajax']) && trim($this->search_options['ajax']) !== '' ? (int) $this->search_options['ajax'] : 0;

        // Requested Page
        $this->page = max(1, get_query_var('paged', isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1));

        // Filter Options
        $this->filter_options = $this->apply_current_query(
            isset($this->atts['lsd_filter']) && is_array($this->atts['lsd_filter'])
                ? $this->atts['lsd_filter']
                : []
        );

        if (isset($this->filter_options['circle']) && is_array($this->filter_options['circle']))
        {
            $circle = LSD_Sanitize::search(['circle' => $this->filter_options['circle']]);

            if (isset($circle['circle']) && is_array($circle['circle']) && count($circle['circle']))
                $this->filter_options['circle'] = $circle['circle'];
            else unset($this->filter_options['circle']);
        }

        $this->exclude_options = $this->atts['lsd_exclude'] ?? [];

        foreach ([
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_LOCATION,
            LSD_Base::TAX_FEATURE,
            LSD_Base::TAX_LABEL,
        ] as $tax)
        {
            if (isset($this->filter_options[$tax]))
            {
                $this->filter_options[$tax] = LSD_Taxonomies::resolve_term_ids($tax, $this->filter_options[$tax]);
            }

            if (isset($this->exclude_options[$tax]))
            {
                $this->exclude_options[$tax] = LSD_Taxonomies::resolve_term_ids($tax, $this->exclude_options[$tax]);
            }
        }

        if (isset($this->filter_options[LSD_Base::TAX_TAG]))
        {
            if (is_array($this->filter_options[LSD_Base::TAX_TAG]) || is_numeric($this->filter_options[LSD_Base::TAX_TAG]))
            {
                $this->filter_options[LSD_Base::TAX_TAG] = LSD_Taxonomies::resolve_term_ids(
                    LSD_Base::TAX_TAG,
                    $this->filter_options[LSD_Base::TAX_TAG]
                );
            }
        }

        if (isset($this->exclude_options[LSD_Base::TAX_TAG]))
        {
            if (is_array($this->exclude_options[LSD_Base::TAX_TAG]) || is_numeric($this->exclude_options[LSD_Base::TAX_TAG]))
            {
                $this->exclude_options[LSD_Base::TAX_TAG] = LSD_Taxonomies::resolve_term_ids(
                    LSD_Base::TAX_TAG,
                    $this->exclude_options[LSD_Base::TAX_TAG]
                );
            }
        }

        // Map Controls Options
        $this->mapcontrols = $this->atts['lsd_mapcontrols'] ?? [];

        // Default Options
        $this->map_provider = isset($this->skin_options['map_provider']) && $this->skin_options['map_provider'] ? sanitize_text_field($this->skin_options['map_provider']) : false;
        $this->style = isset($this->skin_options['style']) && $this->skin_options['style'] ? sanitize_text_field($this->skin_options['style']) : $this->default_style;
        $this->style = $this->normalize_style($this->style);
        $this->display_image = $this->isLite() || !isset($this->skin_options['display_image']) || $this->skin_options['display_image'];
        $this->image_method = isset($this->skin_options['image_method']) && $this->skin_options['image_method'] ? $this->skin_options['image_method'] : 'cover';
        $this->image_fit = isset($this->skin_options['image_fit']) && $this->skin_options['image_fit'] ? $this->skin_options['image_fit'] : 'cover';
        $this->load_more = isset($this->skin_options['load_more']) && $this->skin_options['load_more'];
        $this->pagination = $this->skin_options['pagination'] ?? (!$this->load_more ? 'disabled' : 'loadmore');
        $this->display_contact_info = !isset($this->skin_options['display_contact_info']) || $this->skin_options['display_contact_info'];
        $this->display_read_more_button = !isset($this->skin_options['display_read_more_button']) || $this->skin_options['display_read_more_button'];
        $this->display_location = !isset($this->skin_options['display_location']) || $this->skin_options['display_location'];

        // Price Class
        $this->display_price_class = (!isset($this->skin_options['display_price_class']) || $this->skin_options['display_price_class']) && LSD_Components::pricing();

        // Price class is disabled globally
        if (isset($this->price_components['class']) && !$this->price_components['class']) $this->display_price_class = false;

        $this->display_description = !isset($this->skin_options['display_description']) || $this->skin_options['display_description'];
        $this->display_address = (!isset($this->skin_options['display_address']) || $this->skin_options['display_address']) && LSD_Components::map();
        $this->display_availability = (!isset($this->skin_options['display_availability']) || $this->skin_options['display_availability']) && LSD_Components::work_hours();
        $this->display_categories = !isset($this->skin_options['display_categories']) || $this->skin_options['display_categories'];
        $this->display_price = (!isset($this->skin_options['display_price']) || $this->skin_options['display_price']) && LSD_Components::pricing();
        $this->display_favorite_icon = (!isset($this->skin_options['display_favorite_icon']) || $this->skin_options['display_favorite_icon']) && class_exists(LSDPACFAV\Base::class);
        $this->display_compare_icon = (!isset($this->skin_options['display_compare_icon']) || $this->skin_options['display_compare_icon']) && class_exists(LSDPACCMP\Base::class);
        $this->display_review_stars = !isset($this->skin_options['display_review_stars']) || $this->skin_options['display_review_stars'];
        $this->display_labels = !isset($this->skin_options['display_labels']) || $this->skin_options['display_labels'];
        $this->display_title = !isset($this->skin_options['display_title']) || $this->skin_options['display_title'];
        $this->display_is_claimed = (!isset($this->skin_options['display_is_claimed']) || $this->skin_options['display_is_claimed']) && class_exists(LSDPACCLM\Base::class);
        $this->display_share_buttons = (!isset($this->skin_options['display_share_buttons']) || $this->skin_options['display_share_buttons']) && LSD_Components::socials();
        $this->display_slider_arrows = !isset($this->skin_options['display_slider_arrows']) || $this->skin_options['display_slider_arrows'];
        $this->default_view = isset($this->skin_options['default_view']) ? sanitize_text_field($this->skin_options['default_view']) : 'grid';

        $cta_settings = isset($this->skin_options['cta']) && is_array($this->skin_options['cta'])
            ? $this->skin_options['cta']
            : [];

        $cta_mode = $cta_settings['mode'] ?? null;
        if (!in_array($cta_mode, ['inherit', 'custom', 'disabled'], true))
        {
            if (isset($cta_settings['inherit'])) $cta_mode = $cta_settings['inherit'] ? 'inherit' : 'custom';
            else if (isset($cta_settings['enabled'])) $cta_mode = $cta_settings['enabled'] ? 'inherit' : 'disabled';
            else
            {
                $legacy = $this->skin_options['display_cta'] ?? null;

                if ($legacy === '0' || $legacy === 0 || $legacy === false) $cta_mode = 'disabled';
                else if ($legacy === '1' || $legacy === 1 || $legacy === true) $cta_mode = 'inherit';
                else $cta_mode = empty($cta_settings) ? 'disabled' : 'inherit';
            }
        }

        if (!LSD_Components::cta()) $cta_mode = 'disabled';

        $alignment_options = ['left', 'center', 'right', 'stretch'];
        $alignment = isset($cta_settings['alignment']) ? sanitize_text_field($cta_settings['alignment']) : '';
        if (!in_array($alignment, $alignment_options, true)) $alignment = 'left';

        $this->cta_mode = $cta_mode;
        $this->cta_alignment = $alignment;

        $this->cta_override = $cta_mode === 'custom'
            ? [
                'text' => $cta_settings['text'] ?? '',
                'target' => $cta_settings['target'] ?? '',
                'url' => $cta_settings['url'] ?? '',
                'content' => $cta_settings['content'] ?? '',
            ]
            : [];

        $this->display_cta = $this->is_cta_enabled();
        $this->description_length = isset($this->skin_options['description_length']) && is_numeric($this->skin_options['description_length']) ? $this->skin_options['description_length'] : 12;
        $this->content_type = $this->skin_options['content_type'] ?? 'excerpt';

        $this->columns = isset($this->skin_options['columns']) && $this->skin_options['columns'] ? sanitize_text_field($this->skin_options['columns']) : 3;

        // Autoplay
        $this->autoplay = !isset($this->skin_options['autoplay']) || $this->skin_options['autoplay'];

        // Map Search Options
        $this->mapsearch = isset($this->skin_options['mapsearch']) && $this->skin_options['mapsearch'];
        $this->autoGPS = isset($this->skin_options['auto_gps']) && $this->skin_options['auto_gps'];
        $this->maxBounds = apply_filters('lsd_map_max_bounds', isset($this->skin_options['max_bounds']) && is_array($this->skin_options['max_bounds']) ? $this->skin_options['max_bounds'] : []);
        $this->ignore_map_exclusion = isset($this->skin_options['show_excluded_listings']) && $this->skin_options['show_excluded_listings'];

        // Map height
        $this->map_height = isset($this->skin_options['map_height']) && trim($this->skin_options['map_height']) ? $this->skin_options['map_height'] : '500px';
        if (is_numeric($this->map_height)) $this->map_height .= 'px';
        $this->mousewheel_zoom = isset($this->skin_options['mousewheel_zoom']) && $this->skin_options['mousewheel_zoom'];

        // HTML Class
        $this->html_class = isset($this->atts['html_class']) && trim($this->atts['html_class']) ? sanitize_text_field($this->atts['html_class']) : '';

        // Is it Widget?
        $this->widget = isset($this->atts['widget']) && $this->atts['widget'];

        // Connected Shortcodes
        $this->connected_shortcodes = isset($this->skin_options['connected_shortcodes']) && is_array($this->skin_options['connected_shortcodes'])
            ? $this->skin_options['connected_shortcodes']
            : [];

        // Disable Pro features
        if ($this->isLite())
        {
            // Disable Map Search
            $this->mapsearch = false;

            // Disable GPS feature
            if (isset($this->mapcontrols['gps'])) $this->mapcontrols['gps'] = '0';

            // Disable Draw feature
            if (isset($this->mapcontrols['draw'])) $this->mapcontrols['draw'] = '0';
        }

        // Set to Payload Options
        LSD_Payload::set('shortcode', $this);
    }

    public function is_cta_enabled(): bool
    {
        if (!LSD_Components::cta() || $this->cta_mode === 'disabled') return false;

        $cta_settings = isset($this->skin_options['cta']) && is_array($this->skin_options['cta'])
            ? $this->skin_options['cta']
            : [];

        $cta_enabled = $this->skin_options['display_cta'] ?? ($cta_settings['enabled'] ?? false);

        $filtered = filter_var($cta_enabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return is_null($filtered) ? (bool) $cta_enabled : $filtered;
    }

    protected function has_listing_price(LSD_Entity_Listing $listing): bool
    {
        return $this->display_price && $listing->get_price();
    }

    protected function has_listing_address(LSD_Entity_Listing $listing): bool
    {
        return $this->display_address && $listing->get_address();
    }

    public function has_bottom_bar(LSD_Entity_Listing $listing): bool
    {
        return false;
    }

    public function has_body(LSD_Entity_Listing $listing): bool
    {
        return true;
    }

    public function has_after_content_hook(): bool
    {
        return false !== has_action('lsd_skins_after_content');
    }

    public function after_start()
    {
    }

    public function query()
    {
        // Post Type
        $this->args['post_type'] = LSD_Base::PTYPE_LISTING;
        $this->args['ignore_sticky_posts'] = true;

        // Status
        $this->args['post_status'] = $this->query_status();

        // Keyword
        $this->args['s'] = $this->query_keyword();

        // Taxonomy
        $this->args['tax_query'] = $this->query_tax();

        // Meta
        $this->args['meta_query'] = $this->query_meta();

        // Author
        $this->query_author();

        // Include / Exclude
        $this->query_ixclude();

        // Radius
        $this->query_radius();

        // Pagination Options
        $this->limit = isset($this->skin_options['limit']) && trim($this->skin_options['limit'])
            ? sanitize_text_field($this->skin_options['limit'])
            : 300;

        $this->args['posts_per_page'] = $this->limit;
        $this->args['paged'] = $this->page;

        // Sort Query
        $this->sort();

        // Init the Data Search
        $this->args['lsd-init'] = true;
    }

    public function query_keyword(): string
    {
        return isset($this->filter_options['s']) && trim($this->filter_options['s']) !== ''
            ? sanitize_text_field($this->filter_options['s'])
            : '';
    }

    public function query_status()
    {
        return $this->filter_options['status'] ?? ['publish'];
    }

    public function query_tax(): array
    {
        $tax_query = ['relation' => 'AND'];

        $normalize_terms = function (string $taxonomy, $values): array
        {
            if (is_array($values))
            {
                return LSD_Taxonomies::resolve_term_ids($taxonomy, $values);
            }

            if (is_scalar($values))
            {
                $value = trim((string) $values);
                if ($value === '') return [];

                $parts = array_map('trim', explode(',', $value));
                return LSD_Taxonomies::resolve_term_ids($taxonomy, $parts);
            }

            return [];
        };

        foreach ([
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_LOCATION,
            LSD_Base::TAX_FEATURE,
            LSD_Base::TAX_LABEL,
            LSD_Base::TAX_TAG,
        ] as $tax)
        {
            if (isset($this->filter_options[$tax]))
            {
                $terms = $normalize_terms($tax, $this->filter_options[$tax]);
                if (count($terms))
                {
                    $tax_query[] = [
                        'taxonomy' => $tax,
                        'field' => 'term_id',
                        'terms' => $terms,
                        'operator' => apply_filters('lsd_search_' . $tax . '_operator', 'IN', $tax),
                    ];
                }
            }

            if (isset($this->exclude_options[$tax]))
            {
                $terms = $normalize_terms($tax, $this->exclude_options[$tax]);
                if (count($terms))
                {
                    $tax_query[] = [
                        'taxonomy' => $tax,
                        'field' => 'term_id',
                        'terms' => $terms,
                        'operator' => apply_filters('lsd_search_' . $tax . '_exclude_operator', 'NOT IN', $tax),
                    ];
                }
            }
        }

        return $tax_query;
    }

    public function query_meta(): array
    {
        $meta_query = [];

        // Attributes
        if (isset($this->filter_options['attributes']) && is_array($this->filter_options['attributes']) && count($this->filter_options['attributes']))
        {
            foreach ($this->filter_options['attributes'] as $key => $value)
            {
                if ((is_array($value) && !count($value)) || (!is_array($value) && trim($value) == '')) continue;

                $qa = LSD_Query::attribute($key, $value);
                if (!$qa) continue;

                // Add to Meta Query
                $meta_query[] = $qa;
            }
        }

        // ACF Fields
        if (isset($this->filter_options['acf_fields']['acf_values']) && is_array($this->filter_options['acf_fields']['acf_values']) && count($this->filter_options['acf_fields']['acf_values']))
        {
            foreach ($this->filter_options['acf_fields']['acf_values'] as $key => $value)
            {
                if ((is_array($value) && !count($value)) || (!is_array($value) && trim($value) == '')) continue;

                $qf = LSD_Query::acf_fields($key, $value);
                if (!$qf) continue;

                // Add to Meta Query
                $meta_query[] = $qf;
            }
        }

        return $meta_query;
    }

    public function query_author(): void
    {
        // Include
        if (isset($this->filter_options['authors']) && is_array($this->filter_options['authors']) && count($this->filter_options['authors']))
        {
            $this->args['author__in'] = array_map('sanitize_text_field', $this->filter_options['authors']);
        }

        // Exclude
        if (isset($this->exclude_options['authors']) && is_array($this->exclude_options['authors']) && count($this->exclude_options['authors']))
        {
            $this->args['author__not_in'] = array_map('sanitize_text_field', $this->exclude_options['authors']);
        }
    }

    public function query_ixclude()
    {
        // Include
        if (isset($this->filter_options['include']) && is_array($this->filter_options['include']) && count($this->filter_options['include']))
        {
            $this->args['post__in'] = $this->filter_options['include'];
        }

        // Exclude
        if (isset($this->filter_options['exclude']) && is_array($this->filter_options['exclude']) && count($this->filter_options['exclude']))
        {
            $this->args['post__not_in'] = $this->filter_options['exclude'];
        }
    }

    public function query_radius()
    {
        // Include
        if (isset($this->filter_options['circle']['center']) && isset($this->filter_options['circle']['radius']) && is_array($this->filter_options['circle']) && count($this->filter_options['circle']))
        {
            $main = new LSD_Main();
            if(is_array($this->filter_options['circle']['center'])) $geopoint = $this->filter_options['circle']['center'];
            else $geopoint = $main->geopoint($this->filter_options['circle']['center']);

            if (isset($geopoint[0]) && $geopoint[0] && isset($geopoint[1]) && $geopoint[1])
            {
                $this->args['lsd-circle'] = [
                    'center' => [$geopoint[0], $geopoint[1]],
                    'radius' => (int) $this->filter_options['circle']['radius'],
                ];
            }
        }
    }

    public function sort()
    {
        // Sort Options
        $this->sorts = $this->atts['lsd_sorts'] ?? LSD_Options::defaults('sorts');

        $available_options = $this->get_available_sort_options();
        if (!isset($this->sorts['options']) || !is_array($this->sorts['options']))
        {
            $this->sorts['options'] = $available_options;
        }
        else
        {
            foreach ($available_options as $key => $option)
            {
                if (!isset($this->sorts['options'][$key]) || !is_array($this->sorts['options'][$key]))
                {
                    $this->sorts['options'][$key] = $option;
                }
                else
                {
                    $this->sorts['options'][$key] = wp_parse_args($this->sorts['options'][$key], $option);
                }
            }
        }

        // Sortbar Status
        $this->sortbar = isset($this->sorts['display']) && $this->sorts['display'];
        $this->sort_style = isset($this->sorts['sort_style']) && $this->sorts['sort_style'];

        // Order and Order By and Style
        $this->orderby = $this->sorts['default']['orderby'] ?? 'post_date';

        if (!isset($this->sorts['options'][$this->orderby])) $this->orderby = 'post_date';

        $default_option = $this->sorts['options'][$this->orderby] ?? [];
        $order = $this->sorts['default']['order'] ?? ($default_option['order'] ?? 'DESC');
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'], true))
        {
            $order = strtoupper($default_option['order'] ?? 'DESC');
            if (!in_array($order, ['ASC', 'DESC'], true)) $order = 'DESC';
        }

        $this->order = $order;

        // Sort Query
        $this->args = $this->query_sort($this->args, $this->orderby, $this->order);
    }

    public function query_sort($args, $orderby, $order = 'DESC')
    {
        $orderby = (string) $orderby;
        $order = strtoupper((string) $order);
        if (!in_array($order, ['ASC', 'DESC'], true)) $order = 'DESC';
        $this->order = $order;

        $option = $this->sorts['options'][$orderby] ?? [];

        $meta_key = $option['meta_key'] ?? null;
        if (!$meta_key && strpos($orderby, 'lsd_') === 0) $meta_key = $orderby;

        $this->sort_meta_key = null;
        $this->sort_meta_orderby = null;
        $this->sort_meta_type = null;
        $this->sort_custom_mode = null;
        $this->sort_distance_reference = null;

        if ($orderby === 'lsd_categories')
        {
            $this->sort_custom_mode = 'categories';
            unset($args['meta_key'], $args['meta_type']);
            $args['orderby'] = 'post_date';
        }
        else if ($orderby === 'lsd_locations')
        {
            $this->sort_custom_mode = 'locations';
            unset($args['meta_key'], $args['meta_type']);
            $args['orderby'] = 'post_date';
        }
        else if ($orderby === 'lsd_distance')
        {
            $reference = $this->resolve_distance_sort_reference();

            if ($reference)
            {
                $this->sort_custom_mode = 'distance';
                $this->sort_distance_reference = $reference;

                unset($args['meta_key'], $args['meta_type']);
                $args['orderby'] = 'post_date';
            }
            else
            {
                $fallback_orderby = $this->sorts['default']['orderby'] ?? 'post_date';
                if (
                    !isset($this->sorts['options'][$fallback_orderby])
                    || $fallback_orderby === 'lsd_distance'
                )
                {
                    $fallback_orderby = 'post_date';
                }

                $fallback_option = $this->sorts['options'][$fallback_orderby] ?? [];
                $fallback_order = strtoupper($this->sorts['default']['order'] ?? ($fallback_option['order'] ?? 'DESC'));
                if (!in_array($fallback_order, ['ASC', 'DESC'], true))
                {
                    $fallback_order = strtoupper($fallback_option['order'] ?? 'DESC');
                    if (!in_array($fallback_order, ['ASC', 'DESC'], true)) $fallback_order = 'DESC';
                }

                $this->order = $fallback_order;

                return $this->query_sort($args, $fallback_orderby, $fallback_order);
            }
        }
        else if ($meta_key)
        {
            $orderby_type = $option['orderby'] ?? null;
            if ($orderby_type !== 'meta_value' && $orderby_type !== 'meta_value_num')
            {
                $orderby_type = strpos($meta_key, 'lsd_') === 0 ? 'meta_value_num' : 'meta_value';
            }

            $this->sort_meta_key = $meta_key;
            $this->sort_meta_orderby = $orderby_type;
            $this->sort_meta_type = null;

            if (isset($option['meta_type']) && trim((string) $option['meta_type']))
            {
                $meta_type = strtoupper(trim((string) $option['meta_type']));

                // Only allow the cast types the plugin actually uses for sortable date fields.
                if (in_array($meta_type, ['DATE', 'DATETIME', 'TIME'], true)) $this->sort_meta_type = $meta_type;
            }

            unset($args['meta_key'], $args['meta_type']);
            $args['orderby'] = 'post_date';
        }
        else
        {
            $args['orderby'] = $orderby;
            unset($args['meta_key'], $args['meta_type']);
        }

        // Order
        $args['order'] = $this->order;

        return $args;
    }

    public function query_join($join, $wp_query)
    {
        if (is_string($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === LSD_Base::PTYPE_LISTING && $wp_query->get('lsd-init', false))
        {
            global $wpdb;
            $join .= " LEFT JOIN `" . $wpdb->prefix . "lsd_data` AS lsddata ON `" . $wpdb->prefix . "posts`.`ID` = lsddata.`id` ";
        }

        return $join;
    }

    public function query_where($where, $wp_query)
    {
        if (is_string($wp_query->query_vars['post_type']) and $wp_query->query_vars['post_type'] == LSD_Base::PTYPE_LISTING and $wp_query->get('lsd-init', false))
        {
            // Boundary Search
            if (
                ($boundary = $wp_query->get('lsd-boundary', false))
                && isset($boundary['min_latitude'], $boundary['max_latitude'], $boundary['min_longitude'], $boundary['max_longitude'])
            )
            {
                $min_latitude = esc_sql((string) (float) $boundary['min_latitude']);
                $max_latitude = esc_sql((string) (float) $boundary['max_latitude']);
                $min_longitude = esc_sql((string) (float) $boundary['min_longitude']);
                $max_longitude = esc_sql((string) (float) $boundary['max_longitude']);

                $where .= " AND lsddata.`latitude` >= $min_latitude AND lsddata.`latitude` <= $max_latitude AND lsddata.`longitude` >= $min_longitude AND lsddata.`longitude` <= $max_longitude";
            }

            // Circle Search
            if (
                ($circle = $wp_query->get('lsd-circle', false))
                && isset($circle['center'][0], $circle['center'][1], $circle['radius'])
            )
            {
                $circle_latitude = esc_sql((string) (float) $circle['center'][0]);
                $circle_longitude = esc_sql((string) (float) $circle['center'][1]);
                $circle_radius = esc_sql((string) (float) $circle['radius']);

                $where .= " AND ((6371000 * acos(cos(radians($circle_latitude)) * cos(radians(lsddata.`latitude`)) * cos(radians(lsddata.`longitude`) - radians($circle_longitude)) + sin(radians($circle_latitude)) * sin(radians(lsddata.`latitude`)))) < $circle_radius)";
            }

            // Polygon Search
            if ($polygon = $wp_query->get('lsd-polygon', false))
            {
                // Libraries
                $db = new LSD_db();
                $shape = new LSD_Shape();

                if (version_compare($db->version(), '5.6.1', '>='))
                {
                    $sql_function1 = 'ST_Contains';
                    $sql_function2 = 'ST_GeomFromText';
                }
                else
                {
                    $sql_function1 = 'Contains';
                    $sql_function2 = 'GeomFromText';
                }

                $polygon = $shape->toPolygon($polygon['points'] ?? []);

                $polygon_str = '';
                foreach ($polygon as $polygon_point) $polygon_str .= $polygon_point[0] . ' ' . $polygon_point[1] . ', ';
                $polygon_str = trim($polygon_str, ', ');

                $where .= " AND " . $sql_function1 . "($sql_function2('Polygon((" . esc_sql($polygon_str) . "))'), lsddata.`point`) = 1";
            }

            // Apply Filters
            $where = apply_filters('lsd_where_query', $where, $this, $wp_query);
        }

        return $where;
    }

    public function search($params = []): array
    {
        $args = wp_parse_args($params, $this->args);

        // Apply Filter
        $args = apply_filters('lsd_before_search', $args, $this);

        // Random Order
        if (isset($args['orderby']) && $args['orderby'] === 'rand')
        {
            $seed = isset($this->atts['seed']) && isset($args['paged']) && $args['paged'] != 1 ? $this->atts['seed'] : wp_rand(10000, 99999);

            $args['orderby'] = 'RAND(' . $seed . ')';
            $this->atts['seed'] = $seed;
        }

        $clauses_filter_added = false;
        if ($this->sort_meta_key || $this->sort_custom_mode)
        {
            add_filter('posts_clauses', [$this, 'apply_sort_clauses'], 10, 2);
            $clauses_filter_added = true;
        }

        // The Query
        $query = new WP_Query($args);

        if ($clauses_filter_added)
        {
            remove_filter('posts_clauses', [$this, 'apply_sort_clauses']);
            $this->sort_meta_key = null;
            $this->sort_meta_orderby = null;
            $this->sort_meta_type = null;
            $this->sort_custom_mode = null;
            $this->sort_distance_reference = null;
        }

        $ids = [];
        if ($query->have_posts())
        {
            // The Loop
            while ($query->have_posts())
            {
                $query->the_post();
                $ids[] = get_the_ID();
            }

            // Total Count of Results
            $this->found_listings = $query->found_posts;

            // Next Page
            $this->next_page = isset($args['paged']) ? $args['paged'] + 1 : 1;
        }

        // Restore original Post Data
        LSD_LifeCycle::reset();

        return $ids;
    }

    public function apply_sort_clauses(array $clauses, WP_Query $wp_query): array
    {
        if (!$wp_query->get('lsd-init', false))
        {
            return $clauses;
        }

        if ($this->sort_custom_mode === 'categories')
        {
            return $this->apply_taxonomy_sort_clauses($clauses, self::TAX_CATEGORY);
        }
        else if ($this->sort_custom_mode === 'locations')
        {
            return $this->apply_taxonomy_sort_clauses($clauses, self::TAX_LOCATION);
        }
        else if (
            $this->sort_custom_mode === 'distance'
            && is_array($this->sort_distance_reference)
            && isset($this->sort_distance_reference['lat'], $this->sort_distance_reference['lng'])
        )
        {
            return $this->apply_distance_sort_clauses($clauses);
        }
        else if (!$this->sort_meta_key)
        {
            return $clauses;
        }

        global $wpdb;

        $alias = 'lsd_sort_meta';
        if (strpos($clauses['join'], $alias) === false)
        {
            $meta_key = esc_sql($this->sort_meta_key);
            $clauses['join'] .= " LEFT JOIN $wpdb->postmeta AS $alias ON $alias.post_id = $wpdb->posts.ID AND $alias.meta_key = '$meta_key'";
        }

        $order = strtoupper($this->order);
        if (!in_array($order, ['ASC', 'DESC'], true)) $order = 'DESC';

        $orderby_type = $this->sort_meta_orderby;
        $meta_type = $this->sort_meta_type;

        $value_expression = "$alias.meta_value";
        if ($orderby_type === 'meta_value_num')
        {
            $value_expression = "CAST($alias.meta_value AS DECIMAL(20,6))";
        }

        if ($meta_type && trim($meta_type))
        {
            if (in_array($meta_type, ['DATE', 'DATETIME', 'TIME'], true))
            {
                if ($meta_type === 'DATETIME')
                {
                    $value_expression = "CAST(REPLACE($alias.meta_value, 'T', ' ') AS DATETIME)";
                }
                else
                {
                    $value_expression = "CAST($alias.meta_value AS $meta_type)";
                }
            }
        }

        $ordered_value = "CASE WHEN $alias.meta_value IS NULL OR $alias.meta_value = '' THEN NULL ELSE $value_expression END";
        $empties_last = "CASE WHEN $alias.meta_value IS NULL OR $alias.meta_value = '' THEN 1 ELSE 0 END ASC";

        $orderby_clause = $empties_last . ', ' . $ordered_value . ' ' . $order;
        if (!empty($clauses['orderby'])) $orderby_clause .= ', ' . $clauses['orderby'];

        $fallback_order = "$wpdb->posts.ID $order";
        if (strpos($orderby_clause, $fallback_order) === false)
        {
            $orderby_clause .= ', ' . $fallback_order;
        }

        $clauses['orderby'] = $orderby_clause;

        return $clauses;
    }

    protected function apply_taxonomy_sort_clauses(array $clauses, string $taxonomy): array
    {
        $db = new LSD_db();
        $posts_table = $db->_prefix('#__posts');
        $term_relationships_table = $db->_prefix('#__term_relationships');
        $term_taxonomy_table = $db->_prefix('#__term_taxonomy');
        $terms_table = $db->_prefix('#__terms');

        $order = strtoupper($this->order);
        if (!in_array($order, ['ASC', 'DESC'], true)) $order = 'ASC';

        $term_order_aggregate = $order === 'DESC' ? 'MAX' : 'MIN';
        $term_name_aggregate = $order === 'DESC' ? 'MAX' : 'MIN';

        $alias = 'lsd_sort_terms';
        if (strpos($clauses['join'], $alias) === false)
        {
            $clauses['join'] .= $db->prepare("
                LEFT JOIN (
                    SELECT sort_terms.object_id, sort_terms.sort_term_order, $term_name_aggregate(t.name) AS sort_term_name
                    FROM (
                        SELECT tr.object_id, $term_order_aggregate(tr.term_order) AS sort_term_order
                        FROM `$term_relationships_table` AS tr
                        INNER JOIN `$term_taxonomy_table` AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = %s
                        GROUP BY tr.object_id
                    ) AS sort_terms
                    INNER JOIN `$term_relationships_table` AS tr ON tr.object_id = sort_terms.object_id AND tr.term_order = sort_terms.sort_term_order
                    INNER JOIN `$term_taxonomy_table` AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = %s
                    INNER JOIN `$terms_table` AS t ON t.term_id = tt.term_id
                    GROUP BY sort_terms.object_id, sort_terms.sort_term_order
                ) AS $alias ON $alias.object_id = `$posts_table`.ID
            ", $taxonomy, $taxonomy);
        }

        $empties_last = "CASE WHEN $alias.sort_term_name IS NULL OR $alias.sort_term_name = '' THEN 1 ELSE 0 END ASC";
        $term_order = "CASE WHEN $alias.sort_term_order IS NULL THEN NULL ELSE $alias.sort_term_order END $order";
        $term_name = "$alias.sort_term_name $order";

        $orderby_clause = $empties_last . ', ' . $term_order . ', ' . $term_name;
        if (!empty($clauses['orderby'])) $orderby_clause .= ', ' . $clauses['orderby'];

        $fallback_order = "`$posts_table`.ID $order";
        if (strpos($orderby_clause, $fallback_order) === false)
        {
            $orderby_clause .= ', ' . $fallback_order;
        }

        $clauses['orderby'] = $orderby_clause;

        return $clauses;
    }

    protected function apply_distance_sort_clauses(array $clauses): array
    {
        $db = new LSD_db();
        $posts_table = $db->_prefix('#__posts');

        $lat = (float) $this->sort_distance_reference['lat'];
        $lng = (float) $this->sort_distance_reference['lng'];

        $order = strtoupper($this->order);
        if (!in_array($order, ['ASC', 'DESC'], true)) $order = 'ASC';

        $distance_expression = "(6371000 * ACOS(LEAST(1, GREATEST(-1, COS(RADIANS($lat)) * COS(RADIANS(lsddata.`latitude`)) * COS(RADIANS(lsddata.`longitude`) - RADIANS($lng)) + SIN(RADIANS($lat)) * SIN(RADIANS(lsddata.`latitude`))))))";
        $ordered_value = "CASE WHEN lsddata.`latitude` IS NULL OR lsddata.`longitude` IS NULL OR lsddata.`latitude` = '' OR lsddata.`longitude` = '' THEN NULL ELSE $distance_expression END";
        $empties_last = "CASE WHEN lsddata.`latitude` IS NULL OR lsddata.`longitude` IS NULL OR lsddata.`latitude` = '' OR lsddata.`longitude` = '' THEN 1 ELSE 0 END ASC";

        $orderby_clause = $empties_last . ', ' . $ordered_value . ' ' . $order;
        if (!empty($clauses['orderby'])) $orderby_clause .= ', ' . $clauses['orderby'];

        $fallback_order = "`$posts_table`.ID $order";
        if (strpos($orderby_clause, $fallback_order) === false)
        {
            $orderby_clause .= ', ' . $fallback_order;
        }

        $clauses['orderby'] = $orderby_clause;

        return $clauses;
    }

    protected function resolve_distance_sort_reference(): ?array
    {
        if (is_array($this->sort_search_context) && count($this->sort_search_context))
        {
            $reference = $this->extract_gps_distance_reference_from_search($this->sort_search_context);
            if ($reference) return $reference;

            $reference = $this->extract_distance_reference_from_search($this->sort_search_context);
            if ($reference) return $reference;
        }

        $request = wp_unslash(array_merge($_GET, $_POST));
        if (is_array($request) && count($request))
        {
            $reference = $this->extract_gps_distance_reference_from_search($request);
            if ($reference) return $reference;

            $reference = $this->extract_distance_reference_from_search($request);
            if ($reference) return $reference;
        }

        $sf = $this->get_sf([]);
        if (is_array($sf) && count($sf))
        {
            $reference = $this->extract_distance_reference_from_search($sf);
            if ($reference) return $reference;
        }

        $reference = $this->default_distance_sort_reference();
        if ($reference) return $reference;

        return null;
    }

    protected function extract_gps_distance_reference_from_search(array $search): ?array
    {
        if (isset($search['sf']) && is_array($search['sf']))
        {
            $sf = LSD_Sanitize::search($search['sf']);
            if ($this->is_gps_distance_reference($sf)) return [
                'lat' => (float) $sf['gps_latitude'],
                'lng' => (float) $sf['gps_longitude'],
            ];
        }

        if ($this->is_gps_distance_reference($search))
        {
            if (isset($search['distance_reference_source'], $search['gps_latitude'], $search['gps_longitude'])) return [
                'lat' => (float) $search['gps_latitude'],
                'lng' => (float) $search['gps_longitude'],
            ];

            return [
                'lat' => (float) $search['sf-gps-latitude'],
                'lng' => (float) $search['sf-gps-longitude'],
            ];
        }

        return null;
    }

    protected function extract_distance_reference_from_search(array $search): ?array
    {
        if (isset($search['sf']) && is_array($search['sf']))
        {
            $sf = LSD_Sanitize::search($search['sf']);
            $shape = $sf['shape'] ?? null;

            if (
                $shape === 'circle'
                && isset($sf['circle_latitude'], $sf['circle_longitude'])
                && is_numeric($sf['circle_latitude'])
                && is_numeric($sf['circle_longitude'])
            )
            {
                return [
                    'lat' => (float) $sf['circle_latitude'],
                    'lng' => (float) $sf['circle_longitude'],
                ];
            }

            if (isset($sf['circle']) && is_array($sf['circle']))
            {
                $reference = $this->normalize_distance_reference($sf['circle']);
                if ($reference) return $reference;
            }
        }

        if (
            isset($search['sf-circle-center-lat'], $search['sf-circle-center-lng'])
            && is_numeric($search['sf-circle-center-lat'])
            && is_numeric($search['sf-circle-center-lng'])
        )
        {
            return [
                'lat' => (float) $search['sf-circle-center-lat'],
                'lng' => (float) $search['sf-circle-center-lng'],
            ];
        }

        if (
            isset($search['shape'], $search['circle_latitude'], $search['circle_longitude'])
            && $search['shape'] === 'circle'
            && is_numeric($search['circle_latitude'])
            && is_numeric($search['circle_longitude'])
        )
        {
            return [
                'lat' => (float) $search['circle_latitude'],
                'lng' => (float) $search['circle_longitude'],
            ];
        }

        if (isset($search['circle']) && is_array($search['circle']))
        {
            $reference = $this->normalize_distance_reference($search['circle']);
            if ($reference) return $reference;
        }

        return null;
    }

    protected function normalize_distance_reference(array $circle): ?array
    {
        if (isset($circle['center-lat'], $circle['center-lng']))
        {
            if (!is_numeric($circle['center-lat']) || !is_numeric($circle['center-lng'])) return null;

            return [
                'lat' => (float) $circle['center-lat'],
                'lng' => (float) $circle['center-lng'],
            ];
        }

        if (!isset($circle['center'])) return null;

        $center = $circle['center'];
        if (!is_array($center) || !isset($center[0], $center[1])) return null;

        if (!is_numeric($center[0]) || !is_numeric($center[1])) return null;

        return [
            'lat' => (float) $center[0],
            'lng' => (float) $center[1],
        ];
    }

    protected function default_distance_sort_reference(): ?array
    {
        $latitude = $this->settings['map_backend_lt'] ?? null;
        $longitude = $this->settings['map_backend_ln'] ?? null;

        if (!is_numeric($latitude) || !is_numeric($longitude)) return null;

        return [
            'lat' => (float) $latitude,
            'lng' => (float) $longitude,
        ];
    }

    protected function is_gps_distance_reference(array $search): bool
    {
        if (!isset($search['distance_reference_source'], $search['gps_latitude'], $search['gps_longitude']))
        {
            if (!isset($search['sf-distance-reference-source'], $search['sf-gps-latitude'], $search['sf-gps-longitude'])) return false;

            return $search['sf-distance-reference-source'] === 'gps'
                && is_numeric($search['sf-gps-latitude'])
                && is_numeric($search['sf-gps-longitude']);
        }

        return $search['distance_reference_source'] === 'gps'
            && is_numeric($search['gps_latitude'])
            && is_numeric($search['gps_longitude']);
    }

    /**
     * @param array $search
     * @param string $limitType
     * @return array
     */
    public function apply_search(array $search, string $limitType = 'listings'): array
    {
        // Search Args
        $args = [];
        $this->sort_search_context = $search;

        // Order
        $requested_orderby = isset($search['orderby']) ? sanitize_text_field($search['orderby']) : $this->orderby;
        $requested_order = isset($search['order']) ? sanitize_text_field($search['order']) : $this->order;

        if (isset($this->sorts['options'][$requested_orderby])) $this->orderby = $requested_orderby;
        else if (!isset($this->sorts['options'][$this->orderby])) $this->orderby = 'post_date';

        $order_upper = strtoupper($requested_order);
        if (!in_array($order_upper, ['ASC', 'DESC'], true))
        {
            $order_upper = strtoupper($this->sorts['options'][$this->orderby]['order'] ?? 'DESC');
            if (!in_array($order_upper, ['ASC', 'DESC'], true)) $order_upper = 'DESC';
        }

        $this->order = $order_upper;

        $args = $this->query_sort($args, $this->orderby, $this->order);

        // Limit
        $limit = (isset($search['limit']) && trim($search['limit']) ? $search['limit'] : ($limitType == 'map' && isset($this->skin_options['maplimit']) ? sanitize_text_field($this->skin_options['maplimit']) : (isset($this->skin_options['limit']) ? sanitize_text_field($this->skin_options['limit']) : 12)));

        $args['posts_per_page'] = $limit;
        $this->limit = $limit;

        // Page
        $args['paged'] = isset($search['page']) ? sanitize_text_field($search['page']) : (get_query_var('paged') ?: 1);

        // Search Parameters
        $sf = isset($search['sf']) && is_array($search['sf']) ? LSD_Sanitize::search($search['sf']) : [];
        $shape = $sf['shape'] ?? null;

        // Boundary Search
        if (!$shape && isset($sf['min_latitude'], $sf['max_latitude'], $sf['min_longitude'], $sf['max_longitude']))
        {
            $args['lsd-boundary'] = [
                'min_latitude' => $sf['min_latitude'],
                'max_latitude' => $sf['max_latitude'],
                'min_longitude' => $sf['min_longitude'],
                'max_longitude' => $sf['max_longitude'],
            ];
        }

        // Rectangle Search
        if ($shape === 'rectangle' && isset($sf['rect_min_latitude'], $sf['rect_max_latitude'], $sf['rect_min_longitude'], $sf['rect_max_longitude']))
        {
            $args['lsd-boundary'] = [
                'min_latitude' => $sf['rect_min_latitude'],
                'max_latitude' => $sf['rect_max_latitude'],
                'min_longitude' => $sf['rect_min_longitude'],
                'max_longitude' => $sf['rect_max_longitude'],
            ];
        }

        // Circle Search
        if ($shape === 'circle' && isset($sf['circle_latitude'], $sf['circle_longitude'], $sf['circle_radius']))
        {
            $args['lsd-circle'] = [
                'center' => [$sf['circle_latitude'], $sf['circle_longitude']],
                'radius' => $sf['circle_radius'],
            ];
        }

        // Polygon Search
        if ($shape === 'polygon' && isset($sf['polygon']) && trim($sf['polygon']) !== '')
        {
            $args['lsd-polygon'] = [
                'points' => $sf['polygon'],
            ];
        }

        return $this->args = wp_parse_args($args, $this->args);
    }

    public function apply_current_query(array $filter_options = []): array
    {
        // Current Query
        $q = get_queried_object();

        // It's not a taxonomy query
        if (!isset($q->taxonomy) || !isset($q->term_id)) return $filter_options;

        // It's not a Listdom taxonomy
        if (!in_array($q->taxonomy, $this->taxonomies())) return $filter_options;

        if (isset($filter_options[$q->taxonomy]) && is_array($filter_options[$q->taxonomy]))
        {
            $filter_options[$q->taxonomy][] = $q->term_id;
            $this->atts['lsd_filter'][$q->taxonomy][] = $q->term_id;
        }
        else
        {
            $filter_options[$q->taxonomy] = [$q->term_id];
            $this->atts['lsd_filter'][$q->taxonomy] = [$q->term_id];
        }

        return $filter_options;
    }

    public function fetch()
    {
        // Get Listings
        $this->listings = $this->search();
    }

    public function filter()
    {
        // Get attributes
        $atts = isset($_POST['atts']) && is_array($_POST['atts']) ? wp_unslash($_POST['atts']) : [];

        // Sanitization
        $atts = LSD_Sanitize::deep($atts);

        // Start the skin
        $this->start($atts);
        $this->after_start();

        // Generate the Query
        $this->query();

        // Apply Search Parameters
        $this->apply_search($_POST);

        // Fetch the listings
        $this->fetch();

        // Generate the output
        $output = $this->listings_html();

        $this->response([
            'success' => 1,
            'html' => LSD_Kses::full($output),
            'next_page' => $this->next_page,
            'count' => count($this->listings),
            'total' => $this->found_listings,
            'seed' => $this->atts['seed'] ?? null,
            'pagination' => $this->get_pagination(),
            'filters' => $this->filters(),
        ]);
    }

    public function setLimit($type = 'listings', $limit = null)
    {
        if (!$limit)
        {
            if ($type === 'map' && isset($this->skin_options['maplimit'])) $skin_limit = $this->skin_options['maplimit'];
            else $skin_limit = $this->skin_options['limit'] ?? 300;

            $limit = $skin_limit;
        }

        $this->args['posts_per_page'] = $limit;
        $this->limit = $limit;
    }

    public function tpl()
    {
        return lsd_template('skins/' . $this->skin . '/tpl.php');
    }

    public function listings_html()
    {
        $path = lsd_template('skins/' . $this->skin . '/render.php');

        // File not Found!
        if (!LSD_File::exists($path)) return '';

        ob_start();
        include $path;
        $output = ob_get_clean();

        // No Listing Found
        if (trim($output) === '') $output = $this->get_not_found_message();

        return $output;
    }

    public function output()
    {
        ob_start();
        include $this->tpl();
        $output = ob_get_clean();

        $this->collection_output_rendered = true;

        return $output;
    }

    /**
     * Check whether this skin rendered collection output.
     * @return bool
     */
    public function rendered_collection_output(): bool
    {
        // Render Status
        return $this->collection_output_rendered;
    }

    /**
     * Normalize custom styles (e.g. template builder styles like tb_123).
     *
     * @param mixed $style
     * @return mixed
     */
    protected function normalize_style($style)
    {
        if (is_string($style) && preg_match('/^tb_(\d+)$/', $style, $matches)) return (int) $matches[1];

        return $style;
    }

    protected static function get_mappable_skin_keys(): array
    {
        return apply_filters('lsd_mappable_skins', [
            'grid',
            'halfmap',
            'list',
            'listgrid',
            'singlemap',
            'accordion',
            'mosaic',
            'gallery',
            'timeline',
        ]);
    }

    protected static function get_listable_skin_keys(): array
    {
        return apply_filters('lsd_listable_skins', [
            'list',
            'grid',
            'listgrid',
            'halfmap',
            'table',
            'masonry',
            'side',
            'accordion',
            'mosaic',
            'timeline',
            'gallery',
        ]);
    }

    protected static function get_cardable_skin_keys(): array
    {
        return apply_filters('lsd_cardable_skins', [
            'list',
            'grid',
            'side',
            'masonry',
            'carousel',
            'slider',
            'cover',
            'halfmap',
            'accordion',
            'mosaic',
            'timeline',
        ]);
    }

    protected static function get_filterable_skin_keys(): array
    {
        $skins = array_keys(self::get_searchable_skins());

        return apply_filters('lsd_filterable_skins', $skins);
    }

    public static function is_mappable(string $skin): bool
    {
        $skin = trim($skin);
        if ($skin === '') return false;

        return in_array($skin, self::get_mappable_skin_keys(), true);
    }

    public static function is_listable(string $skin): bool
    {
        $skin = trim($skin);
        if ($skin === '') return false;

        return in_array($skin, self::get_listable_skin_keys(), true);
    }

    public static function is_filterable(string $skin): bool
    {
        $skin = trim($skin);
        if ($skin === '') return false;

        return in_array($skin, self::get_filterable_skin_keys(), true);
    }

    public static function is_cardable(string $skin): bool
    {
        $skin = trim($skin);
        if ($skin === '') return false;

        return in_array($skin, self::get_cardable_skin_keys(), true);
    }

    public static function get_listable_skins(): array
    {
        return self::get_listable_skin_keys();
    }

    public static function get_mappable_skins(): array
    {
        return self::get_mappable_skin_keys();
    }

    public static function get_filterable_skins(): array
    {
        return self::get_filterable_skin_keys();
    }

    public static function get_skins()
    {
        $skins = apply_filters('lsd_skins', [
            'singlemap' => esc_html__('Single Map', 'listdom'),
            'list' => esc_html__('List View', 'listdom'),
            'grid' => esc_html__('Grid View', 'listdom'),
            'listgrid' => esc_html__('List + Grid View', 'listdom'),
            'halfmap' => esc_html__('Half Map / Split View', 'listdom'),
            'table' => esc_html__('Table View', 'listdom'),
            'masonry' => esc_html__('Masonry View', 'listdom'),
            'carousel' => esc_html__('Carousel', 'listdom'),
            'slider' => esc_html__('Slider', 'listdom'),
            'cover' => esc_html__('Cover View', 'listdom'),
            'side' => esc_html__('Side by Side View', 'listdom'),
            'accordion' => esc_html__('Accordion View', 'listdom'),
            'mosaic' => esc_html__('Mosaic View', 'listdom'),
            'timeline' => esc_html__('Timeline View', 'listdom'),
            'gallery' => esc_html__('Gallery View', 'listdom'),
        ]);

        if (!LSD_Components::map()) unset($skins['singlemap'], $skins['halfmap']);

        return $skins;
    }

    public static function get_searchable_skins()
    {
        $skins = apply_filters('lsd_searchable_skins', [
            'singlemap' => esc_html__('Single Map', 'listdom'),
            'list' => esc_html__('List View', 'listdom'),
            'grid' => esc_html__('Grid View', 'listdom'),
            'side' => esc_html__('Side by Side View', 'listdom'),
            'listgrid' => esc_html__('List + Grid View', 'listdom'),
            'halfmap' => esc_html__('Half Map / Split View', 'listdom'),
            'table' => esc_html__('Table View', 'listdom'),
            'masonry' => esc_html__('Masonry View', 'listdom'),
            'timeline' => esc_html__('Timeline View', 'listdom'),
        ]);

        if (!LSD_Components::map()) unset($skins['singlemap'], $skins['halfmap']);

        return $skins;
    }

    public function get_search_module(string $style = ''): string
    {
        global $post;

        $shortcode_id = isset($this->atts['id']) ? (int) $this->atts['id'] : $this->id;
        $search_shortcode_id = $this->translated_search_shortcode_id();

        return do_shortcode('[listdom-search id="' . $search_shortcode_id . '" style="' . ($style ?: (in_array($this->sm_position, ['left', 'right']) ? 'sidebar' : '')) . '" page="' . (is_singular() && $post && isset($post->ID) ? $post->ID : '') . '" shortcode="' . $shortcode_id . '" ajax="' . $this->sm_ajax . '"]');
    }

    /**
     * Resolve the configured Search & Filter form for the active Polylang language.
     * @return string
     */
    protected function translated_search_shortcode_id(): string
    {
        if (!is_numeric($this->sm_shortcode) || !function_exists('pll_get_post')) return (string) $this->sm_shortcode;

        $language = function_exists('pll_current_language') ? pll_current_language('slug') : '';
        $translated_shortcode_id = pll_get_post((int) $this->sm_shortcode, $language);

        return is_numeric($translated_shortcode_id) && (int) $translated_shortcode_id > 0 ? (string) $translated_shortcode_id : (string) $this->sm_shortcode;
    }

    public function get_sortbar()
    {
        if (!$this->sortbar) return '';

        ob_start();
        include lsd_template('elements/sortbar.php');
        return ob_get_clean();
    }

    public function get_pagination()
    {
        if ($this->found_listings <= $this->limit && !$this->sm_ajax) return '';

        if ($this->pagination === 'loadmore') return $this->get_loadmore_button();
        else if ($this->pagination === 'scroll') return $this->get_scroll_pagination();
        else if ($this->pagination === 'numeric') return $this->get_numeric_pagination();

        return '';
    }

    public function get_loadmore_button()
    {
        ob_start();
        include lsd_template('paginations/loadmore.php');
        return ob_get_clean();
    }

    public function get_numeric_pagination()
    {
        ob_start();
        include lsd_template('paginations/numeric.php');
        return ob_get_clean();
    }

    public function get_scroll_pagination()
    {
        ob_start();
        include lsd_template('paginations/scroll.php');
        return ob_get_clean();
    }

    public function get_switcher_buttons(bool $display_switcher = true)
    {
        ob_start();
        include lsd_template('elements/switcher.php');
        return ob_get_clean();
    }

    public function filters(): string
    {
        return '';
    }

    /**
     * @param LSD_Entity_Listing $listing
     * @return string
     */
    public function get_title_tag(LSD_Entity_Listing $listing): string
    {
        $method = $this->get_listing_link_method();
        $style = $this->get_single_listing_style();

        $title = $listing->get_title_tag($method, $style);

        if ($this->skin === 'table' || !$this->display_is_claimed || !$listing->is_claimed()) return $title;

        $icon = '<span class="lsd-tooltip" data-lsd-tooltip="' . esc_attr__('Verified', 'listdom') . '"><i class="lsd-fe-icon fas fa-check-circle lsd-claimed-icon"></i></span>';

        if (in_array($method, ['normal', 'blank', 'lightbox', 'right-panel', 'left-panel', 'bottom-panel']))
        {
            if (strpos($title, '</a>') !== false) return str_replace('</a>', ' ' . $icon . '</a>', $title);

            return $title . ' ' . $icon;
        }

        return $title . ' ' . $icon;
    }

    public function listing_cta(LSD_Entity_Listing $listing, string $context = 'archive'): string
    {
        if (!$this->display_cta || $this->cta_mode === 'disabled' || !LSD_Components::cta()) return '';

        $args = [
            'button_class' => 'lsd-light-button lsd-cta-button',
            'lightbox_style' => $this->get_single_listing_style(),
        ];

        if ($this->cta_mode === 'custom' && !empty($this->cta_override)) $args['cta_override'] = $this->cta_override;

        $cta = $listing->get_cta($context, $args);
        if (!trim($cta)) return '';

        $alignment_options = ['left', 'center', 'right', 'stretch'];
        $alignment = in_array($this->cta_alignment, $alignment_options, true) ? $this->cta_alignment : 'left';

        $classes = ['lsd-listing-cta', 'lsd-cta-align-' . sanitize_html_class($alignment)];

        $output = '<div class="' . esc_attr(implode(' ', $classes)) . '">' . LSD_Kses::element($cta) . '</div>';

        return apply_filters('lsd_listing_cta', $output, $listing, $this, $context);
    }

    public function get_not_found_message(): string
    {
        if (isset($this->settings['no_listings_message']) && trim($this->settings['no_listings_message'])) $message = do_shortcode(stripslashes($this->settings['no_listings_message']));
        else $message = $this->alert(esc_html__('No Listing Found!', 'listdom'));

        if ($this->skin === 'table' && method_exists($this, 'get_table_columns_display'))
        {
            $display = $this->get_table_columns_display();

            $colspan = isset($display['columns_union']) ? count($display['columns_union']) : 1;
            if ($colspan < 1) $colspan = 1;

            return '<tr class="lsd-no-listing"><td class="lsd-table-no-listing" colspan="' . esc_attr($colspan) . '">' . $message . '</td></tr>';
        }

        return $message;
    }

    public function get_listing_link_method()
    {
        $method = $this->isPro() && isset($this->skin_options['listing_link']) && trim($this->skin_options['listing_link'])
            ? $this->skin_options['listing_link']
            : 'normal';

        if ($method === 'map' && (!$this->map_component || !$this->map_provider || !self::is_mappable($this->skin))) {
            return 'normal';
        }

        return $method;
    }

    public function get_single_listing_style()
    {
        return $this->isPro() && isset($this->skin_options['single_style']) && trim($this->skin_options['single_style'])
            ? $this->skin_options['single_style']
            : '';
    }

    public function get_map(bool $force_to_show = false, $limit = null)
    {
        if (!$this->map_component) return '';

        return lsd_map($this->search([
            'posts_per_page' => $limit ?? ($this->skin_options['maplimit'] ?? -1),
            'paged' => 1,
        ]), [
            'provider' => $this->map_provider,
            'clustering' => $this->skin_options['clustering'] ?? true,
            'clustering_images' => $this->skin_options['clustering_images'] ?? '',
            'clustering_color' => $this->skin_options['clustering_color'] ?? '#3f51b5',
            'mapstyle' => $this->skin_options['mapstyle'] ?? '',
            'id' => $this->id,
            'onclick' => $this->skin_options['mapobject_onclick'] ?? 'infowindow',
            'listing_link_method' => $this->get_listing_link_method(),
            'infowindow_trigger' => $this->skin_options['mapobject_infowindow_trigger'] ?? 'click',
            'mapcontrols' => $this->mapcontrols,
            'map_height' => $this->map_height,
            'mousewheel_zoom' => $this->mousewheel_zoom,
            'atts' => $this->atts,
            'mapsearch' => $this->mapsearch,
            'autoGPS' => $this->autoGPS,
            'max_bounds' => $this->maxBounds,
            'force_to_show' => $force_to_show,
            'ignore_map_exclusion' => $this->ignore_map_exclusion,
        ]);
    }

    public function get_left_bar(bool $map = true, bool $search = true): string
    {
        $content = '';

        // Map
        if ($map && $this->map_provider && $this->map_position === 'left' && $this->map_component)
        {
            $content .= '<div class="lsd-map-left-wrapper">';
            $content .= $this->get_map(true);
            $content .= '</div>';
        }

        // Search
        if ($search && $this->sm_shortcode && $this->sm_position === 'left')
        {
            $classes = ['lsd-search-bar-wrapper', 'lsd-search-bar-left-wrapper'];
            if ($this->sm_sticky) $classes[] = 'lsd-search-bar-sticky';

            $content .= '<div class="' . esc_attr(implode(' ', $classes)) . '"' . $this->get_search_sticky_style() . '>';
            $content .= LSD_Kses::form($this->get_search_module());
            $content .= '</div>';
        }

        return trim($content) ? '<div class="lsd-skin-left-bar-wrapper">' . $content . '</div>' : '';
    }

    public function get_right_bar(bool $map = true, bool $search = true): string
    {
        $content = '';

        // Map
        if ($map && $this->map_provider && $this->map_position === 'right' && $this->map_component)
        {
            $content .= '<div class="lsd-map-right-wrapper">';
            $content .= $this->get_map(true);
            $content .= '</div>';
        }

        // Search
        if ($search && $this->sm_shortcode && $this->sm_position === 'right')
        {
            $classes = ['lsd-search-bar-wrapper', 'lsd-search-bar-right-wrapper'];
            if ($this->sm_sticky) $classes[] = 'lsd-search-bar-sticky';

            $content .= '<div class="' . esc_attr(implode(' ', $classes)) . '"' . $this->get_search_sticky_style() . '>';
            $content .= LSD_Kses::form($this->get_search_module());
            $content .= '</div>';
        }

        return trim($content) ? '<div class="lsd-skin-right-bar-wrapper">' . $content . '</div>' : '';
    }

    public function get_search_sticky_style(): string
    {
        if (!$this->sm_sticky || $this->sm_sticky_offset === null) return '';

        return ' style="--lsd-search-sticky-top:' . esc_attr($this->sm_sticky_offset) . 'px;"';
    }

    public function get_bar_class(bool $map = true, bool $search = true): string
    {
        $left = false;
        $right = false;

        // Left Bar
        if (
            ($map && $this->map_provider && $this->map_position === 'left' && $this->map_component)
            || ($search && $this->sm_shortcode && $this->sm_position === 'left')
        ) $left = true;

        // right Bar
        if (
            ($map && $this->map_provider && $this->map_position === 'right' && $this->map_component)
            || ($search && $this->sm_shortcode && $this->sm_position === 'right')
        ) $right = true;

        // Both Bars Enabled
        if ($left && $right) return 'lsd-skin-right-left-bars';

        // Left Bar Enabled
        if ($left) return 'lsd-skin-left-bar';

        // Right Bar Enabled
        if ($right) return 'lsd-skin-right-bar';

        // No Bar
        return '';
    }

    public function getField($field)
    {
        return $this->{$field} ?? null;
    }

    public function setField($field, $value)
    {
        if (isset($this->{$field})) $this->{$field} = $value;
    }
}
