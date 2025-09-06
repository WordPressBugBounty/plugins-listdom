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
    public $description_length = 12;
    public $content_type = 'excerpt';

    public $image_method = 'cover';
    public $image_fit = 'cover';
    public $display_share_buttons = false;
    public $columns = 1;
    public $default_view = 'grid';
    public $html_class = '';
    public $widget = false;
    public $post_id;
    public $mapsearch = false;
    public $autoplay = true;
    public $autoGPS = false;
    public $maxBounds = [];
    public $map_provider = 'leaflet';
    public $map_height;
    public $ignore_map_exclusion = false;
    public $price_components = [];
    public $connected_shortcodes = [];
    public $map_position;
    public $map_component = true;

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
        (new LSD_Skins_Gallery())->init();
    }

    public function start($atts)
    {
        $this->atts = apply_filters('lsd_skins_atts', $atts);
        $this->id = LSD_id::get(isset($this->atts['id']) ? sanitize_text_field($this->atts['id']) : mt_rand(100, 999));

        // Skin Options
        $this->skin_options = $this->atts['lsd_display'][$this->skin] ?? [];

        // Map Position
        $this->map_position = isset($this->skin_options['map_position']) && trim($this->skin_options['map_position']) ? $this->skin_options['map_position'] : 'top';

        // Search Options
        $this->search_options = $this->atts['lsd_search'] ?? [];
        $this->sm_shortcode = isset($this->search_options['shortcode']) && trim($this->search_options['shortcode']) ? $this->search_options['shortcode'] : null;
        $this->sm_position = isset($this->search_options['position']) && trim($this->search_options['position']) ? $this->search_options['position'] : 'top';
        $this->sm_ajax = isset($this->search_options['ajax']) && trim($this->search_options['ajax']) !== '' ? (int) $this->search_options['ajax'] : 0;

        // Requested Page
        $this->page = max(1, get_query_var('paged', isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1));

        // Filter Options
        $this->filter_options = $this->apply_current_query(
            isset($this->atts['lsd_filter']) && is_array($this->atts['lsd_filter'])
                ? $this->atts['lsd_filter']
                : []
        );

        $this->exclude_options = $this->atts['lsd_exclude'] ?? [];

        // Map Controls Options
        $this->mapcontrols = $this->atts['lsd_mapcontrols'] ?? [];

        // Default Options
        $this->map_provider = isset($this->skin_options['map_provider']) && $this->skin_options['map_provider'] ? sanitize_text_field($this->skin_options['map_provider']) : false;
        $this->style = isset($this->skin_options['style']) && $this->skin_options['style'] ? sanitize_text_field($this->skin_options['style']) : $this->default_style;
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
        $this->display_favorite_icon = !isset($this->skin_options['display_favorite_icon']) || $this->skin_options['display_favorite_icon'];
        $this->display_compare_icon = !isset($this->skin_options['display_compare_icon']) || $this->skin_options['display_compare_icon'];
        $this->display_review_stars = !isset($this->skin_options['display_review_stars']) || $this->skin_options['display_review_stars'];
        $this->display_labels = !isset($this->skin_options['display_labels']) || $this->skin_options['display_labels'];
        $this->display_title = !isset($this->skin_options['display_title']) || $this->skin_options['display_title'];
        $this->display_is_claimed = !isset($this->skin_options['display_is_claimed']) || $this->skin_options['display_is_claimed'];
        $this->display_share_buttons = (!isset($this->skin_options['display_share_buttons']) || $this->skin_options['display_share_buttons']) && LSD_Components::socials();
        $this->display_slider_arrows = !isset($this->skin_options['display_slider_arrows']) || $this->skin_options['display_slider_arrows'];
        $this->default_view = isset($this->skin_options['default_view']) ? sanitize_text_field($this->skin_options['default_view']) : 'grid';
        $this->description_length = isset($this->skin_options['description_length']) && is_numeric($this->skin_options['description_length']) ? $this->skin_options['description_length'] : 12;
        $this->content_type = $this->skin_options['content_type'] ?? 'excerpt';

        $this->columns = isset($this->skin_options['columns']) && $this->skin_options['columns'] ? sanitize_text_field($this->skin_options['columns']) : 3;

        // Style4 Columns
        if ($this->style === 'style4') $this->columns = 2;

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

        foreach ([
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_LOCATION,
            LSD_Base::TAX_FEATURE,
            LSD_Base::TAX_LABEL,
        ] as $tax)
        {
            if (isset($this->filter_options[$tax]) && is_array($this->filter_options[$tax]) && count($this->filter_options[$tax]))
            {
                $tax_query[] = [
                    'taxonomy' => $tax,
                    'field' => 'term_id',
                    'terms' => $this->filter_options[$tax],
                    'operator' => apply_filters('lsd_search_' . $tax . '_operator', 'IN', $tax),
                ];
            }

            if (isset($this->exclude_options[$tax]) && is_array($this->exclude_options[$tax]) && count($this->exclude_options[$tax]))
            {
                $tax_query[] = [
                    'taxonomy' => $tax,
                    'field' => 'term_id',
                    'terms' => $this->exclude_options[$tax],
                    'operator' => apply_filters('lsd_search_' . $tax . '_exclude_operator', 'NOT IN', $tax),
                ];
            }
        }

        // Tags Include
        if (isset($this->filter_options[LSD_Base::TAX_TAG]))
        {
            if (is_array($this->filter_options[LSD_Base::TAX_TAG]))
            {
                $tax_query[] = [
                    'taxonomy' => LSD_Base::TAX_TAG,
                    'field' => 'term_id',
                    'terms' => $this->filter_options[LSD_Base::TAX_TAG],
                    'operator' => apply_filters('lsd_search_' . LSD_Base::TAX_TAG . '_operator', 'IN', LSD_Base::TAX_TAG),
                ];
            }
            else if (trim($this->filter_options[LSD_Base::TAX_TAG]))
            {
                $tax_query[] = [
                    'taxonomy' => LSD_Base::TAX_TAG,
                    'field' => 'name',
                    'terms' => explode(',', sanitize_text_field(trim($this->filter_options[LSD_Base::TAX_TAG], ', '))),
                    'operator' => apply_filters('lsd_search_' . LSD_Base::TAX_TAG . '_operator', 'IN', LSD_Base::TAX_TAG),
                ];
            }
        }

        // Tags Exclude
        if (isset($this->exclude_options[LSD_Base::TAX_TAG]))
        {
            if (is_array($this->exclude_options[LSD_Base::TAX_TAG]))
            {
                $tax_query[] = [
                    'taxonomy' => LSD_Base::TAX_TAG,
                    'field' => 'term_id',
                    'terms' => $this->exclude_options[LSD_Base::TAX_TAG],
                    'operator' => apply_filters('lsd_search_' . LSD_Base::TAX_TAG . '_exclude_operator', 'NOT IN', LSD_Base::TAX_TAG),
                ];
            }
            else if (trim($this->exclude_options[LSD_Base::TAX_TAG]))
            {
                $tax_query[] = [
                    'taxonomy' => LSD_Base::TAX_TAG,
                    'field' => 'name',
                    'terms' => explode(',', sanitize_text_field(trim($this->exclude_options[LSD_Base::TAX_TAG], ', '))),
                    'operator' => apply_filters('lsd_search_' . LSD_Base::TAX_TAG . '_exclude_operator', 'NOT IN', LSD_Base::TAX_TAG),
                ];
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

        // Sortbar Status
        $this->sortbar = isset($this->sorts['display']) && $this->sorts['display'];
        $this->sort_style = isset($this->sorts['sort_style']) && $this->sorts['sort_style'];

        // Order and Order By and Style
        $this->orderby = $this->sorts['default']['orderby'] ?? 'post_date';
        $this->order = $this->sorts['default']['order'] ?? 'DESC';

        // Sort Query
        $this->args = $this->query_sort($this->args, $this->orderby, $this->order);
    }

    public function query_sort($args, $orderby, $order = 'DESC')
    {
        // Sort by Meta
        if (strpos($this->orderby, 'lsd_') !== false)
        {
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = $this->orderby;
        }
        else $args['orderby'] = $this->orderby;

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
            if ($boundary = $wp_query->get('lsd-boundary', false))
            {
                $where .= " AND lsddata.`latitude` >= '" . $boundary['min_latitude'] . "' AND lsddata.`latitude` <= '" . $boundary['max_latitude'] . "' AND lsddata.`longitude` >= '" . $boundary['min_longitude'] . "' AND lsddata.`longitude` <= '" . $boundary['max_longitude'] . "'";
            }

            // Circle Search
            if ($circle = $wp_query->get('lsd-circle', false))
            {
                $where .= " AND ((6371000 * acos(cos(radians(" . $circle['center'][0] . ")) * cos(radians(lsddata.`latitude`)) * cos(radians(lsddata.`longitude`) - radians(" . $circle['center'][1] . ")) + sin(radians(" . $circle['center'][0] . ")) * sin(radians(lsddata.`latitude`)))) < " . ($circle['radius']) . ")";
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
            $seed = isset($this->atts['seed']) && isset($args['paged']) && $args['paged'] != 1 ? $this->atts['seed'] : rand(10000, 99999);

            $args['orderby'] = 'RAND(' . $seed . ')';
            $this->atts['seed'] = $seed;
        }

        // The Query
        $query = new WP_Query($args);

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

    /**
     * @param array $search
     * @param string $limitType
     * @return array
     */
    public function apply_search(array $search, string $limitType = 'listings'): array
    {
        // Search Args
        $args = [];

        // Order
        $this->orderby = isset($search['orderby']) ? sanitize_text_field($search['orderby']) : $this->orderby;
        $this->order = isset($search['order']) ? sanitize_text_field($search['order']) : $this->order;

        $args = $this->query_sort($args, $this->orderby, $this->order);

        // Limit
        $limit = (isset($search['limit']) && trim($search['limit']) ? $search['limit'] : ($limitType == 'map' && isset($this->skin_options['maplimit']) ? sanitize_text_field($this->skin_options['maplimit']) : (isset($this->skin_options['limit']) ? sanitize_text_field($this->skin_options['limit']) : 12)));

        $args['posts_per_page'] = $limit;
        $this->limit = $limit;

        // Page
        $args['paged'] = isset($search['page']) ? sanitize_text_field($search['page']) : (get_query_var('paged') ?: 1);

        // Search Parameters
        $sf = isset($search['sf']) && is_array($search['sf']) ? $search['sf'] : [];
        $shape = isset($sf['shape']) ? sanitize_text_field($sf['shape']) : null;

        // Boundary Search
        if (!$shape && isset($sf['min_latitude']) && trim($sf['min_latitude']) &&
            isset($sf['max_latitude']) && trim($sf['max_latitude']) &&
            isset($sf['min_longitude']) && trim($sf['min_longitude']) &&
            isset($sf['max_longitude']) && trim($sf['max_longitude']))
        {
            $args['lsd-boundary'] = [
                'min_latitude' => $sf['min_latitude'],
                'max_latitude' => $sf['max_latitude'],
                'min_longitude' => $sf['min_longitude'],
                'max_longitude' => $sf['max_longitude'],
            ];
        }

        // Rectangle Search
        if ($shape == 'rectangle')
        {
            $args['lsd-boundary'] = [
                'min_latitude' => $sf['rect_min_latitude'],
                'max_latitude' => $sf['rect_max_latitude'],
                'min_longitude' => $sf['rect_min_longitude'],
                'max_longitude' => $sf['rect_max_longitude'],
            ];
        }

        // Circle Search
        if ($shape == 'circle')
        {
            $args['lsd-circle'] = [
                'center' => [$sf['circle_latitude'], $sf['circle_longitude']],
                'radius' => $sf['circle_radius'],
            ];
        }

        // Polygon Search
        if ($shape == 'polygon')
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
        $atts = $_POST['atts'] ?? [];

        // Sanitization
        array_walk_recursive($atts, 'sanitize_text_field');

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
        return ob_get_clean();
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
        ]);

        if (!LSD_Components::map()) unset($skins['singlemap'], $skins['halfmap']);

        return $skins;
    }

    public function get_search_module(string $style = ''): string
    {
        global $post;

        $shortcode_id = isset($this->atts['id']) ? (int) $this->atts['id'] : $this->id;
        return do_shortcode('[listdom-search id="' . $this->sm_shortcode . '" style="' . ($style ?: (in_array($this->sm_position, ['left', 'right']) ? 'sidebar' : '')) . '" page="' . (is_singular() && $post && isset($post->ID) ? $post->ID : '') . '" shortcode="' . $shortcode_id . '" ajax="' . $this->sm_ajax . '"]');
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

        return $listing->get_title_tag($method, $style);
    }

    public function get_not_found_message(): string
    {
        if (isset($this->settings['no_listings_message']) && trim($this->settings['no_listings_message'])) return do_shortcode(stripslashes($this->settings['no_listings_message']));

        return $this->alert(esc_html__('No Listing Found!', 'listdom'));
    }

    public function get_listing_link_method()
    {
        return $this->isPro() && isset($this->skin_options['listing_link']) && trim($this->skin_options['listing_link'])
            ? $this->skin_options['listing_link']
            : 'normal';
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
            'mapstyle' => $this->skin_options['mapstyle'] ?? '',
            'id' => $this->id,
            'onclick' => $this->skin_options['mapobject_onclick'] ?? 'infowindow',
            'mapcontrols' => $this->mapcontrols,
            'map_height' => $this->map_height,
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
        if ($search && $this->sm_shortcode && $this->sm_position === 'left') $content .= LSD_Kses::form($this->get_search_module());

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
        if ($search && $this->sm_shortcode && $this->sm_position === 'right') $content .= LSD_Kses::form($this->get_search_module());

        return trim($content) ? '<div class="lsd-skin-right-bar-wrapper">' . $content . '</div>' : '';
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
