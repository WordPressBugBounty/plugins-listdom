<?php

class LSD_AI_Visibility_Schema extends LSD_Base
{
    protected LSD_AI_Visibility $owner;
    protected LSD_AI_Visibility_Settings $settings;
    protected LSD_AI_Visibility_Payload $payload;
    protected ?array $graph = null;
    protected array $rendered_shortcode_collections = [];

    /**
     * Initialize service dependencies.
     * @param LSD_AI_Visibility $owner
     * @param LSD_AI_Visibility_Settings $settings
     * @param LSD_AI_Visibility_Payload $payload
     * @return void
     */
    public function __construct(LSD_AI_Visibility $owner, LSD_AI_Visibility_Settings $settings, LSD_AI_Visibility_Payload $payload)
    {
        // Dependencies
        $this->owner = $owner;
        $this->settings = $settings;
        $this->payload = $payload;
    }

    /**
     * Register service hooks.
     * @return void
     */
    public function init(): void
    {
        // Register Hooks
        add_action('wp_head', [$this, 'render'], 5);
    }

    /**
     * Render replacement JSON-LD.
     * @return void
     */
    public function render(): void
    {
        // Render Guards
        if (is_admin() || wp_doing_ajax() || (function_exists('wp_is_json_request') && wp_is_json_request())) return;
        if (!$this->settings->structured_data_enabled()) return;
        if (!apply_filters('lsd_ai_visibility_should_render_schema', true, $this->owner)) return;
        if ($this->uses_shortcode_collection_page()) return;

        $graph = $this->schema_graph();
        if (!is_array($graph) || !count($graph)) return;

        echo $this->json_ld_markup($graph);
    }

    /**
     * Build shortcode collection JSON-LD markup.
     * @param LSD_Skins $skin
     * @return string
     */
    public function shortcode_collection_markup(LSD_Skins $skin): string
    {
        // Collection Graph
        $graph = $this->renderable_shortcode_collection_graph($skin);
        if (!count($graph)) return '';

        $fragment = $this->shortcode_collection_fragment($skin);
        $this->rendered_shortcode_collections[$fragment] = true;
        return $this->json_ld_markup($graph);
    }

    /**
     * Check whether shortcode collection schema is replaced.
     * @param LSD_Skins $skin
     * @return bool
     */
    public function replaces_shortcode_collection_schema(LSD_Skins $skin): bool
    {
        // Eligible Graph
        return count($this->eligible_shortcode_collection_graph($skin)) > 0;
    }

    /**
     * Check whether single listing schema is replaced.
     * @return bool
     */
    public function replaces_single_listing_schema(): bool
    {
        // Listing Context
        $listing_id = get_queried_object_id();
        if ($listing_id <= 0) return false;

        $url = (string) get_permalink($listing_id);
        $title = (string) get_the_title($listing_id);

        foreach ($this->single_listing_graph($listing_id) as $node)
        {
            if (!is_array($node)) continue;
            if (($node['@type'] ?? '') === 'BreadcrumbList') continue;
            if (($node['@type'] ?? '') === 'FAQPage') continue;

            if ($this->node_has_fragment($node, 'listing')) return true;
            if (($node['url'] ?? '') === $url && ($node['name'] ?? '') === $title) return true;
        }

        return false;
    }

    /**
     * Check whether listing FAQ schema is replaced.
     * @param int $listing_id
     * @return bool
     */
    public function replaces_listing_faq_schema(int $listing_id = 0): bool
    {
        // Listing Context
        if ($listing_id <= 0) $listing_id = get_queried_object_id();
        if ($listing_id <= 0) return false;

        foreach ($this->single_listing_graph($listing_id) as $node)
        {
            if (!is_array($node)) continue;

            if (($node['@type'] ?? '') === 'FAQPage' && !empty($node['mainEntity'])) return true;
            if ($this->node_has_fragment($node, 'faq')) return true;
        }

        return false;
    }

    /**
     * Build single listing graph.
     * @param int $listing_id
     * @return array
     */
    protected function single_listing_graph(int $listing_id = 0): array
    {
        // Render Guards
        if (!$this->settings->structured_data_enabled()) return [];
        if (is_admin() || wp_doing_ajax()) return [];
        if (function_exists('wp_is_json_request') && wp_is_json_request()) return [];
        if (!is_singular(LSD_Base::PTYPE_LISTING)) return [];
        if (!apply_filters('lsd_ai_visibility_should_render_schema', true, $this->owner)) return [];

        if ($listing_id <= 0) $listing_id = get_queried_object_id();
        if ($listing_id <= 0) return [];
        if (!$this->supports_single_listing_schema($listing_id)) return [];

        $graph = $this->schema_graph();
        return is_array($graph) ? $graph : [];
    }

    /**
     * Check whether a schema node has a URL fragment.
     * @param array $node
     * @param string $fragment
     * @return bool
     */
    protected function node_has_fragment(array $node, string $fragment): bool
    {
        // Node ID
        if (!isset($node['@id']) || !is_string($node['@id'])) return false;

        return substr($node['@id'], -strlen('#' . $fragment)) === '#' . $fragment;
    }

    /**
     * Build the active schema graph.
     * @return array
     */
    public function schema_graph(): array
    {
        // Cached Graph
        if ($this->graph !== null) return $this->graph;

        $graph = [];

        if (is_singular(LSD_Base::PTYPE_LISTING))
        {
            $listing_id = get_queried_object_id();
            $post = get_post($listing_id);
            if (!$this->payload->is_public_listing($post)) return [];
            if (!$this->supports_single_listing_schema($listing_id)) return [];

            $listing = $this->listing_schema($listing_id);

            if (count($listing)) $graph[] = $listing;

            $breadcrumb = $this->single_breadcrumb_schema($listing_id);
            if (count($breadcrumb)) $graph[] = $breadcrumb;

            $faq = $this->faq_schema($listing_id);
            if (count($faq)) $graph[] = $faq;
        }
        else if ($this->is_listing_collection_page())
        {
            $collection = $this->collection_schema();
            if (count($collection))
            {
                if (isset($collection['collection'])) $graph[] = $collection['collection'];
                if (isset($collection['item_list'])) $graph[] = $collection['item_list'];
                if (isset($collection['breadcrumb'])) $graph[] = $collection['breadcrumb'];
            }
        }

        $this->graph = (array) apply_filters('lsd_ai_visibility_schema_graph', $graph, $this->owner);

        return $this->graph;
    }

    /**
     * Build single listing schema.
     * @param int $listing_id
     * @return array
     */
    public function listing_schema(int $listing_id): array
    {
        // Listing Post
        $post = get_post($listing_id);
        if (!$this->payload->is_public_listing($post)) return [];

        $entity = new LSD_Entity_Listing($post);

        $schema = $this->base_listing_schema($post, $entity);

        $this->apply_listing_details($schema, $post, $entity);
        $this->apply_taxonomy_schema($schema, $post->ID, $entity);
        if ($this->payload->listing_element_enabled($post->ID, 'attributes')) $this->apply_attribute_schema($schema, $post->ID);
        $this->apply_addon_schema($schema, $post, $entity);
        $this->avoid_undated_event_schema($schema);

        return (array) apply_filters('lsd_ai_visibility_listing_schema', $schema, $post->ID, $entity, $this->owner);
    }

    /**
     * Build base listing schema.
     * @param WP_Post $post
     * @param LSD_Entity_Listing $entity
     * @return array
     */
    protected function base_listing_schema(WP_Post $post, LSD_Entity_Listing $entity): array
    {
        // Base Properties
        return [
            '@type' => $this->schema_type($entity),
            '@id' => $this->with_fragment((string) get_permalink($post), 'listing'),
            'url' => get_permalink($post),
            'name' => get_the_title($post),
        ];
    }

    /**
     * Apply visible listing details.
     * @param array &$schema
     * @param WP_Post $post
     * @param LSD_Entity_Listing $entity
     * @return void
     */
    protected function apply_listing_details(array &$schema, WP_Post $post, LSD_Entity_Listing $entity): void
    {
        // Content Visibility
        $content_enabled = $this->payload->listing_element_enabled($post->ID, 'content');
        $excerpt_enabled = $this->payload->listing_element_enabled($post->ID, 'excerpt');

        $description = '';

        if ($content_enabled) $description = $this->payload->listing_description($post);
        if ($description === '' && $excerpt_enabled) $description = $this->payload->listing_excerpt($post);

        if ($description !== '') $schema['description'] = $description;

        $image_enabled = $this->payload->listing_element_enabled($post->ID, 'image');
        $gallery_enabled = $this->payload->listing_element_enabled($post->ID, 'gallery');

        if ($image_enabled || $gallery_enabled)
        {
            $gallery_config = $gallery_enabled ? $this->payload->listing_element_config($post->ID, 'gallery') : [];
            $images = $this->payload->listing_image_urls($post->ID, $image_enabled, $gallery_enabled, $gallery_config);
            if (count($images)) $schema['image'] = $images;
        }

        if ($this->payload->listing_element_enabled($post->ID, 'address'))
        {
            $address = $this->postal_address($post->ID, $entity);
            if (count($address)) $schema['address'] = $address;
        }

        if ($this->payload->listing_element_enabled($post->ID, 'map'))
        {
            $latitude = get_post_meta($post->ID, 'lsd_latitude', true);
            $longitude = get_post_meta($post->ID, 'lsd_longitude', true);
            $coordinates = $this->payload->coordinates($latitude, $longitude);

            if (count($coordinates))
            {
                $schema['geo'] = [
                    '@type' => 'GeoCoordinates',
                    'latitude' => $coordinates['latitude'],
                    'longitude' => $coordinates['longitude'],
                ];
            }
        }

        $contact = $this->payload->listing_element_config($post->ID, 'contact');

        if (!empty($contact['enabled']))
        {
            $show_phone = !array_key_exists('show_phone', $contact) || !empty($contact['show_phone']);
            $show_email = !array_key_exists('show_email', $contact) || !empty($contact['show_email']);
            $show_website = !array_key_exists('show_website', $contact) || !empty($contact['show_website']);
            $show_address = !array_key_exists('show_address', $contact) || !empty($contact['show_address']);

            if ($show_phone)
            {
                $phone = get_post_meta($post->ID, 'lsd_phone', true);
                if (is_string($phone) && trim($phone) !== '') $schema['telephone'] = trim($phone);
            }

            if ($show_email)
            {
                $email = get_post_meta($post->ID, 'lsd_email', true);
                if (is_string($email) && is_email($email)) $schema['email'] = sanitize_email($email);
            }

            if ($show_website)
            {
                $website = get_post_meta($post->ID, 'lsd_website', true);

                if (is_string($website) && trim($website) !== '')
                {
                    $website = $this->payload->absolute_url($website);
                    if ($website !== '') $schema['sameAs'] = [$website];
                }
            }

            if ($show_address && !isset($schema['address']))
            {
                $address = $this->contact_postal_address($post->ID);
                if (count($address)) $schema['address'] = $address;
            }
        }

        if ($this->payload->listing_element_enabled($post->ID, 'price'))
        {
            $price_range = $this->payload->price_range($post->ID);
            if ($price_range !== '') $schema['priceRange'] = $price_range;
        }

        if ($this->payload->listing_element_enabled($post->ID, 'availability'))
        {
            $opening_hours = $this->opening_hours_schema($post->ID);

            if (count($opening_hours)) $schema['openingHoursSpecification'] = $opening_hours;
        }
        else if ($this->payload->listing_availability_summary_visible($post->ID))
        {
            $opening_hours = $this->opening_hours_schema($post->ID, (int) current_time('N'));

            if (count($opening_hours)) $schema['openingHoursSpecification'] = $opening_hours;
        }

        if ($this->payload->listing_element_enabled($post->ID, 'video')) $this->append_video_object_schema($schema, $post->ID, true);

        if ($this->payload->listing_element_enabled($post->ID, 'embed')) $this->append_video_object_schema($schema, $post->ID, false);
    }

    /**
     * Apply visible add-on schema.
     * @param array &$schema
     * @param WP_Post $post
     * @param LSD_Entity_Listing $entity
     * @return void
     */
    protected function apply_addon_schema(array &$schema, WP_Post $post, LSD_Entity_Listing $entity): void
    {
        // Verified Status
        if ($entity->is_claimed())
        {
            $schema['additionalProperty'][] = [
                '@type' => 'PropertyValue',
                'name' => 'verified',
                'value' => true,
            ];
        }

        $discussion_enabled = $this->payload->listing_element_enabled($post->ID, 'discussion');
        if ($discussion_enabled || $this->payload->listing_rating_summary_visible($post->ID))
        {
            $review_data = apply_filters('lsd_ai_visibility_reviews_schema', [], $post->ID, $entity, $this->owner);

            if (isset($review_data['aggregateRating']) && is_array($review_data['aggregateRating'])) $schema['aggregateRating'] = $review_data['aggregateRating'];
            if ($discussion_enabled && isset($review_data['review']) && is_array($review_data['review']) && count($review_data['review'])) $schema['review'] = $review_data['review'];
        }

        if ($this->payload->listing_element_enabled($post->ID, 'booking'))
        {
            $booking_schema = apply_filters('lsd_ai_visibility_booking_schema', $schema, $post->ID, $entity, $this->owner);

            if (is_array($booking_schema)) $schema = $booking_schema;
        }
    }

    /**
     * Build collection page schema.
     * @return array
     */
    public function collection_schema(): array
    {
        // Queried Listings
        global $wp_query;

        if (!($wp_query instanceof WP_Query) || !count($wp_query->posts)) return [];

        $items = [];
        $position = 1;

        foreach ($wp_query->posts as $post)
        {
            if (!$this->payload->is_public_listing($post)) continue;

            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'url' => get_permalink($post),
                'name' => get_the_title($post),
            ];
        }

        if (!count($items)) return [];

        $url = $this->canonical_current_url();
        $collection = [
            '@type' => 'CollectionPage',
            '@id' => $this->with_fragment($url, 'collection'),
            'url' => $url,
            'name' => $this->collection_title(),
        ];

        $description = $this->collection_description();
        if ($description !== '') $collection['description'] = $description;

        $about = $this->collection_about();
        if (count($about)) $collection['about'] = $about;

        $item_list = [
            '@type' => 'ItemList',
            '@id' => $this->with_fragment($url, 'itemlist'),
            'url' => $url,
            'name' => $this->collection_title(),
            'numberOfItems' => count($items),
            'itemListElement' => $items,
        ];

        $result = [
            'collection' => apply_filters('lsd_ai_visibility_collection_schema', $collection, $wp_query, $this->owner),
            'item_list' => apply_filters('lsd_ai_visibility_itemlist_schema', $item_list, $wp_query, $this->owner),
        ];

        $breadcrumb = $this->collection_breadcrumb_schema();
        if (count($breadcrumb)) $result['breadcrumb'] = $breadcrumb;

        return $result;
    }

    /**
     * Build shortcode collection graph.
     * @param LSD_Skins $skin
     * @return array
     */
    public function shortcode_collection_graph(LSD_Skins $skin): array
    {
        // Listing Items
        $items = [];
        $position = 1;

        foreach ($skin->listings as $listing_id)
        {
            $listing_id = (int) $listing_id;
            if ($listing_id <= 0) continue;

            $post = get_post($listing_id);
            if (!$this->payload->is_public_listing($post)) continue;

            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'url' => get_permalink($post),
                'name' => get_the_title($post),
                'item' => $this->shortcode_listing_item_schema($post, $skin),
            ];
        }

        if (!count($items)) return [];

        $url = $this->canonical_current_url();
        $fragment = $this->shortcode_collection_fragment($skin);

        $collection = [
            '@type' => 'CollectionPage',
            '@id' => $this->with_fragment($url, 'collection-' . $fragment),
            'url' => $url,
            'name' => $this->collection_title(),
        ];

        $description = $this->collection_description();
        if ($description !== '') $collection['description'] = $description;

        $about = $this->collection_about();
        if (count($about)) $collection['about'] = $about;

        $item_list = [
            '@type' => 'ItemList',
            '@id' => $this->with_fragment($url, 'itemlist-' . $fragment),
            'url' => $url,
            'name' => $this->collection_title(),
            'numberOfItems' => count($items),
            'itemListElement' => $items,
        ];

        $graph = [
            apply_filters('lsd_ai_visibility_shortcode_collection_schema', $collection, $skin, $this->owner),
            apply_filters('lsd_ai_visibility_shortcode_itemlist_schema', $item_list, $skin, $this->owner),
        ];

        $breadcrumb = $this->shortcode_collection_breadcrumb_schema();
        if (count($breadcrumb)) $graph[] = $breadcrumb;

        return $graph;
    }

    /**
     * Build shortcode listing item schema.
     * @param WP_Post $post
     * @param LSD_Skins $skin
     * @return array
     */
    protected function shortcode_listing_item_schema(WP_Post $post, LSD_Skins $skin): array
    {
        // Listing Entity
        $entity = new LSD_Entity_Listing($post);
        $item = [
            '@type' => $this->payload->schema_type($entity),
            '@id' => $this->with_fragment((string) get_permalink($post), 'listing'),
            'url' => get_permalink($post),
            'name' => get_the_title($post),
        ];

        if ($this->skin_displays($skin, 'display_image'))
        {
            $images = $this->payload->listing_image_urls($post->ID, true, false);
            if (count($images)) $item['image'] = $images;
        }

        if ($this->skin_displays($skin, 'display_description'))
        {
            $description = $this->shortcode_listing_description($entity, $skin);
            if ($description !== '') $item['description'] = $description;
        }

        $main_type = isset($item['@type']) && is_string($item['@type']) ? $item['@type'] : '';
        if ($this->skin_displays($skin, 'display_categories')) $this->apply_category_schema($item, $post->ID, $entity, $main_type);

        if ($this->skin_displays($skin, 'display_location')) $this->apply_location_schema($item, $post->ID);

        if ($this->skin_displays($skin, 'display_address'))
        {
            $address = $this->postal_address($post->ID, $entity);
            if (count($address)) $item['address'] = $address;
        }

        if ($this->skin_displays($skin, 'display_price'))
        {
            $price_range = $this->payload->price_range($post->ID);
            if ($price_range !== '') $item['priceRange'] = $price_range;
        }

        if ($this->skin_displays($skin, 'display_contact_info')) $this->apply_shortcode_contact_schema($item, $post->ID);

        if ($this->skin_displays($skin, 'display_availability'))
        {
            $opening_hours = $this->opening_hours_schema($post->ID, (int) current_time('N'));
            if (count($opening_hours)) $item['openingHoursSpecification'] = $opening_hours;
        }

        if ($this->skin_displays($skin, 'display_review_stars')) $this->apply_shortcode_review_summary_schema($item, $post->ID, $entity);

        $this->avoid_undated_event_schema($item);

        return $item;
    }

    /**
     * Build shortcode listing description.
     * @param LSD_Entity_Listing $entity
     * @param LSD_Skins $skin
     * @return string
     */
    protected function shortcode_listing_description(LSD_Entity_Listing $entity, LSD_Skins $skin): string
    {
        // Description Text
        $length = isset($skin->description_length) && is_scalar($skin->description_length) ? (int) $skin->description_length : 12;
        $content_type = isset($skin->content_type) && is_scalar($skin->content_type) ? (string) $skin->content_type : '';
        $description = $entity->get_excerpt($length, false, $content_type === 'description');

        return $this->payload->plain_text_content((string) $description);
    }

    /**
     * Apply shortcode contact schema.
     * @param array &$item
     * @param int $listing_id
     * @return void
     */
    protected function apply_shortcode_contact_schema(array &$item, int $listing_id): void
    {
        // Contact Fields
        $phone = get_post_meta($listing_id, 'lsd_phone', true);
        if (is_string($phone) && trim($phone) !== '') $item['telephone'] = trim($phone);

        $email = get_post_meta($listing_id, 'lsd_email', true);
        if (is_string($email) && is_email($email)) $item['email'] = sanitize_email($email);

        $website = get_post_meta($listing_id, 'lsd_website', true);
        if (is_string($website) && trim($website) !== '')
        {
            $website = $this->payload->absolute_url($website);
            if ($website !== '') $item['sameAs'] = [$website];
        }

        if (!isset($item['address']))
        {
            $address = $this->contact_postal_address($listing_id);
            if (count($address)) $item['address'] = $address;
        }
    }

    /**
     * Apply shortcode review summary schema.
     * @param array &$item
     * @param int $listing_id
     * @param LSD_Entity_Listing $entity
     * @return void
     */
    protected function apply_shortcode_review_summary_schema(array &$item, int $listing_id, LSD_Entity_Listing $entity): void
    {
        // Review Summary
        $summary = apply_filters('lsd_ai_visibility_public_review_summary', [], $listing_id, $entity, $this->owner);
        if (!is_array($summary) || !count($summary)) return;

        $rating = isset($summary['average_rating']) && is_numeric($summary['average_rating']) ? (float) $summary['average_rating'] : 0;
        $count = isset($summary['review_count']) && is_numeric($summary['review_count']) ? (int) $summary['review_count'] : 0;
        if ($rating <= 0 || $count <= 0) return;

        $item['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => $rating,
            'reviewCount' => $count,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }

    /**
     * Check whether a skin displays a property.
     * @param LSD_Skins $skin
     * @param string $property
     * @return bool
     */
    protected function skin_displays(LSD_Skins $skin, string $property): bool
    {
        // Skin Properties
        $properties = get_object_vars($skin);

        return !empty($properties[$property]);
    }

    /**
     * Build single listing breadcrumb schema.
     * @param int $listing_id
     * @return array
     */
    public function single_breadcrumb_schema(int $listing_id): array
    {
        // Breadcrumb Visibility
        if (!$this->payload->listing_element_enabled($listing_id, 'breadcrumb')) return [];

        $items = $this->single_breadcrumb_items($listing_id);
        if (!count($items)) return [];

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->with_fragment((string) get_permalink($listing_id), 'breadcrumb'),
            'itemListElement' => $items,
        ];
    }

    /**
     * Build collection breadcrumb schema.
     * @return array
     */
    public function collection_breadcrumb_schema(): array
    {
        // Root Item
        $url = $this->canonical_current_url();
        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => get_bloginfo('name'),
                'item' => home_url('/'),
            ],
        ];

        $position = 2;
        $queried = get_queried_object();

        if ($queried instanceof WP_Term)
        {
            if ($queried->parent)
            {
                $ancestors = array_reverse(get_ancestors($queried->term_id, $queried->taxonomy, 'taxonomy'));
                foreach ($ancestors as $ancestor_id)
                {
                    $ancestor = get_term($ancestor_id, $queried->taxonomy);
                    if (!($ancestor instanceof WP_Term)) continue;

                    $items[] = [
                        '@type' => 'ListItem',
                        'position' => $position++,
                        'name' => $ancestor->name,
                        'item' => $this->payload->safe_url(get_term_link($ancestor)),
                    ];
                }
            }

            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $queried->name,
                'item' => $url,
            ];
        }
        else
        {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $this->collection_title(),
                'item' => $url,
            ];
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->with_fragment($url, 'breadcrumb'),
            'itemListElement' => $items,
        ];
    }

    /**
     * Build shortcode collection breadcrumb schema.
     * @return array
     */
    protected function shortcode_collection_breadcrumb_schema(): array
    {
        // Shortcode Page
        if (!$this->uses_shortcode_collection_page()) return [];

        return $this->collection_breadcrumb_schema();
    }

    /**
     * Build single listing breadcrumb items.
     * @param int $listing_id
     * @return array
     */
    protected function single_breadcrumb_items(int $listing_id): array
    {
        // Root Item
        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => get_bloginfo('name'),
                'item' => home_url('/'),
            ],
        ];

        $post = get_post($listing_id);
        if (!($post instanceof WP_Post)) return $items;

        $position = 2;
        $post_type = get_post_type_object($post->post_type);

        if ($post_type && $post_type->has_archive)
        {
            $archive_link = get_post_type_archive_link($post_type->name);
            if (is_string($archive_link) && $archive_link !== '')
            {
                $items[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $post_type->labels->name,
                    'item' => $archive_link,
                ];
            }
        }

        $taxonomy = $this->single_breadcrumb_taxonomy($listing_id);
        foreach ($this->single_breadcrumb_terms($listing_id, $taxonomy) as $term)
        {
            $term_link = $this->payload->safe_url(get_term_link($term));
            if ($term_link === '') continue;

            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $term->name,
                'item' => $term_link,
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => get_the_title($listing_id),
            'item' => get_permalink($listing_id),
        ];

        return $items;
    }

    /**
     * Resolve single listing breadcrumb taxonomy.
     * @param int $listing_id
     * @return string
     */
    protected function single_breadcrumb_taxonomy(int $listing_id): string
    {
        // Breadcrumb Config
        $config = $this->payload->listing_element_config($listing_id, 'breadcrumb');
        $taxonomy = isset($config['taxonomy']) && is_scalar($config['taxonomy']) ? (string) $config['taxonomy'] : '';

        return taxonomy_exists($taxonomy) ? $taxonomy : LSD_Base::TAX_CATEGORY;
    }

    /**
     * Resolve single listing breadcrumb terms.
     * @param int $listing_id
     * @param string $taxonomy
     * @return array
     */
    protected function single_breadcrumb_terms(int $listing_id, string $taxonomy): array
    {
        // Taxonomy Guard
        if (!taxonomy_exists($taxonomy)) return [];

        $terms = get_the_terms($listing_id, $taxonomy);
        if (!is_array($terms) || is_wp_error($terms) || !count($terms)) return [];

        $term = reset($terms);
        if (!($term instanceof WP_Term)) return [];

        $items = [];
        $ancestor_ids = array_reverse(get_ancestors($term->term_id, $taxonomy));

        foreach ($ancestor_ids as $ancestor_id)
        {
            $ancestor = get_term($ancestor_id, $taxonomy);
            if ($ancestor instanceof WP_Term) $items[] = $ancestor;
        }

        $items[] = $term;

        return $items;
    }

    /**
     * Build FAQ schema.
     * @param int $listing_id
     * @return array
     */
    public function faq_schema(int $listing_id): array
    {
        // FAQ Config
        $faq_config = $this->payload->listing_element_config($listing_id, 'faq');
        if (empty($faq_config['enabled'])) return [];

        $element = new LSD_Element_Faq();
        $count = isset($faq_config['count']) && is_scalar($faq_config['count']) ? (int) $faq_config['count'] : 0;
        $faqs = $element->items($listing_id, $count);
        if (!is_array($faqs) || !count($faqs)) return [];

        $items = [];
        foreach ($faqs as $faq)
        {
            if (!is_array($faq)) continue;

            $question = isset($faq['question']) ? trim(wp_strip_all_tags((string) $faq['question'])) : '';
            $answer = isset($faq['answer']) ? trim(wp_strip_all_tags((string) $faq['answer'])) : '';

            if ($question === '' || $answer === '') continue;

            $items[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        if (!count($items)) return [];

        return [
            '@type' => 'FAQPage',
            '@id' => $this->with_fragment((string) get_permalink($listing_id), 'faq'),
            'mainEntity' => $items,
        ];
    }

    /**
     * Build opening hours schema.
     * @param int $listing_id
     * @param int|null $only_day
     * @return array
     */
    public function opening_hours_schema(int $listing_id, ?int $only_day = null): array
    {
        // Availability
        $availability = get_post_meta($listing_id, 'lsd_ava', true);
        if (!is_array($availability) || !count($availability)) return [];

        $map = [
            1 => 'https://schema.org/Monday',
            2 => 'https://schema.org/Tuesday',
            3 => 'https://schema.org/Wednesday',
            4 => 'https://schema.org/Thursday',
            5 => 'https://schema.org/Friday',
            6 => 'https://schema.org/Saturday',
            7 => 'https://schema.org/Sunday',
        ];

        $items = [];
        foreach ($availability as $daycode => $day)
        {
            $daycode = (int) $daycode;
            if ($only_day !== null && $daycode !== $only_day) continue;
            if (!isset($map[$daycode]) || !is_array($day) || !empty($day['off'])) continue;

            $hours = isset($day['hours']) && is_scalar($day['hours']) ? trim((string) $day['hours']) : '';
            if ($hours === '') continue;

            $ranges = preg_split('/\s*,\s*/', $hours);
            foreach ($ranges as $range)
            {
                $parts = preg_split('/\s*(?:-|\bto\b)\s*/i', $range);
                if (count($parts) !== 2) continue;

                $opens = $this->normalize_time($parts[0]);
                $closes = $this->normalize_time($parts[1]);
                if ($opens === '' || $closes === '') continue;

                $items[] = [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => $map[$daycode],
                    'opens' => $opens,
                    'closes' => $closes,
                ];
            }
        }

        return $items;
    }

    /**
     * Resolve listing schema type.
     * @param LSD_Entity_Listing $entity
     * @return string
     */
    public function schema_type(LSD_Entity_Listing $entity): string
    {
        // Payload Type
        return $this->payload->schema_type($entity);
    }

    /**
     * Resolve listing price range.
     * @param int $listing_id
     * @return string
     */
    public function price_range(int $listing_id): string
    {
        // Payload Price
        return $this->payload->price_range($listing_id);
    }

    /**
     * Resolve listing description.
     * @param WP_Post $post
     * @return string
     */
    public function listing_description(WP_Post $post): string
    {
        // Payload Description
        return $this->payload->listing_description($post);
    }

    /**
     * Build collection title.
     * @return string
     */
    public function collection_title(): string
    {
        // Queried Object
        $queried = get_queried_object();

        if ($queried instanceof WP_Term) return $queried->name;
        if (is_post_type_archive(LSD_Base::PTYPE_LISTING)) return post_type_archive_title('', false);
        if (is_search()) return sprintf(
            /* translators: %s: search query. */
            esc_html__('Search results for %s', 'listdom'),
            $this->payload->plain_text_content((string) get_search_query(false))
        );

        return wp_get_document_title();
    }

    /**
     * Build collection description.
     * @return string
     */
    public function collection_description(): string
    {
        // Queried Object
        $queried = get_queried_object();
        if ($queried instanceof WP_Term) return trim(wp_strip_all_tags((string) term_description($queried)));

        return '';
    }

    /**
     * Build collection about node.
     * @return array
     */
    public function collection_about(): array
    {
        // Queried Term
        $queried = get_queried_object();
        if (!($queried instanceof WP_Term)) return [];

        $type = $this->term_schema_type($queried);
        if (empty($type))
        {
            $type = 'Thing';
            if ($queried->taxonomy === LSD_Base::TAX_LOCATION) $type = 'Place';
            else if ($queried->taxonomy === LSD_Base::TAX_FEATURE) $type = 'LocationFeatureSpecification';
        }

        $about = [
            '@type' => $type,
            'name' => $queried->name,
        ];

        $description = trim(wp_strip_all_tags((string) term_description($queried)));
        if ($description !== '') $about['description'] = $description;

        $url = $this->payload->safe_url(get_term_link($queried));
        if ($url !== '') $about['url'] = $url;

        return $about;
    }

    /**
     * Build primary postal address.
     * @param int $listing_id
     * @param LSD_Entity_Listing $entity
     * @return array
     */
    protected function postal_address(int $listing_id, LSD_Entity_Listing $entity): array
    {
        // Street Address
        $street_address = get_post_meta($listing_id, 'lsd_address', true);
        if (!is_string($street_address) || trim($street_address) === '') return [];

        $address = ['@type' => 'PostalAddress', 'streetAddress' => trim($street_address)];

        return (array) apply_filters('lsd_ai_visibility_postal_address', $address, $listing_id, $entity, $this->owner);
    }

    /**
     * Build contact postal address.
     * @param int $listing_id
     * @return array
     */
    protected function contact_postal_address(int $listing_id): array
    {
        // Contact Address
        $street_address = get_post_meta($listing_id, 'lsd_contact_address', true);
        if (!is_string($street_address) || trim($street_address) === '') return [];

        return ['@type' => 'PostalAddress', 'streetAddress' => trim($street_address)];
    }

    /**
     * Append video object schema.
     * @param array &$schema
     * @param int $listing_id
     * @param bool $featured
     * @return void
     */
    protected function append_video_object_schema(array &$schema, int $listing_id, bool $featured): void
    {
        // Video Objects
        $videos = $this->video_object_schema($listing_id, $featured);
        if (!count($videos)) return;

        $existing = isset($schema['subjectOf']) ? $schema['subjectOf'] : [];
        $items = [];
        if (is_array($existing) && $this->is_list_array($existing)) $items = $existing;
        else if (!empty($existing)) $items = [$existing];

        foreach ($videos as $video) $items[] = $video;

        $schema['subjectOf'] = count($items) === 1 ? reset($items) : array_values($items);
    }

    /**
     * Check whether an array is a list.
     * @param array $items
     * @return bool
     */
    protected function is_list_array(array $items): bool
    {
        // List Keys
        if (!count($items)) return true;

        return array_keys($items) === range(0, count($items) - 1);
    }

    /**
     * Build video object schema.
     * @param int $listing_id
     * @param bool $featured
     * @return array
     */
    protected function video_object_schema(int $listing_id, bool $featured): array
    {
        // Stored Embeds
        $stored = get_post_meta($listing_id, 'lsd_embeds', true);
        if (!is_array($stored) || !count($stored)) return [];

        $videos = [];
        foreach ($stored as $embed)
        {
            if (!is_array($embed)) continue;

            $is_featured = isset($embed['featured']) && (int) $embed['featured'] === 1;
            if ($featured !== $is_featured) continue;

            $code = isset($embed['code']) && is_scalar($embed['code']) ? trim((string) $embed['code']) : '';
            if ($code === '') continue;

            $video = ['@type' => 'VideoObject'];

            $name = isset($embed['name']) && is_scalar($embed['name']) ? trim(wp_strip_all_tags((string) $embed['name'])) : '';
            if ($name !== '') $video['name'] = $name;

            $embed_url = $this->video_embed_url($code);
            if ($embed_url !== '') $video['embedUrl'] = $embed_url;

            if (count($video) > 1) $videos[] = $video;
        }

        return $videos;
    }

    /**
     * Build video embed URL.
     * @param string $code
     * @return string
     */
    protected function video_embed_url(string $code): string
    {
        // Embed Code
        if (filter_var($code, FILTER_VALIDATE_URL)) return esc_url_raw($code);

        if (!preg_match('/\ssrc=(["\'])(.*?)\1/i', $code, $matches)) return '';

        $url = isset($matches[2]) && is_string($matches[2]) ? html_entity_decode($matches[2], ENT_QUOTES, get_bloginfo('charset')) : '';

        return $this->payload->absolute_url($url);
    }

    /**
     * Append keyword text.
     * @param mixed $keywords
     * @param string $value
     * @return string
     */
    protected function append_keywords($keywords, string $value): string
    {
        // Keyword Value
        $items = [];

        if (is_string($keywords) && trim($keywords) !== '')
        {
            $items = preg_split('/\s*,\s*/', trim($keywords));
            if (!is_array($items)) $items = [];
        }
        else if (is_array($keywords))
        {
            $items = $keywords;
        }

        $items[] = $value;
        $items = array_values(array_unique(array_filter(array_map('trim', $items))));

        return implode(', ', $items);
    }

    /**
     * Apply taxonomy schema.
     * @param array &$schema
     * @param int $listing_id
     * @param LSD_Entity_Listing $entity
     * @return void
     */
    protected function apply_taxonomy_schema(array &$schema, int $listing_id, LSD_Entity_Listing $entity): void
    {
        // Main Type
        $main_type = isset($schema['@type']) && is_string($schema['@type']) ? $schema['@type'] : '';

        if ($this->payload->listing_element_enabled($listing_id, 'categories')) $this->apply_category_schema($schema, $listing_id, $entity, $main_type);

        if ($this->payload->listing_element_enabled($listing_id, 'locations')) $this->apply_location_schema($schema, $listing_id);

        $keyword_taxonomies = [];

        if ($this->payload->listing_element_enabled($listing_id, 'tags')) $keyword_taxonomies[] = LSD_Base::TAX_TAG;

        if ($this->payload->listing_element_enabled($listing_id, 'labels')) $keyword_taxonomies[] = LSD_Base::TAX_LABEL;

        if (count($keyword_taxonomies)) $this->apply_keyword_taxonomy_schema($schema, $listing_id, $keyword_taxonomies);

        if ($this->payload->listing_element_enabled($listing_id, 'features'))
        {
            $features = $this->listing_taxonomy_terms($listing_id, LSD_Base::TAX_FEATURE);
            $this->apply_feature_schema($schema, $features);
        }
    }

    /**
     * Apply category schema.
     * @param array &$schema
     * @param int $listing_id
     * @param LSD_Entity_Listing $entity
     * @param string $main_type
     * @return void
     */
    protected function apply_category_schema(array &$schema, int $listing_id, LSD_Entity_Listing $entity, string $main_type): void
    {
        // Category Terms
        $categories = $this->payload->visible_listing_category_terms($listing_id, $entity);

        $category_names = [];
        $category_types = [];

        foreach ($categories as $category)
        {
            $name = trim($category->name);
            if ($name !== '')
            {
                $category_names[] = $name;
                $schema['keywords'] = $this->append_keywords($schema['keywords'] ?? [], $name);
            }

            $type = $this->term_schema_type($category);
            if ($type !== '' && $type !== $main_type) $category_types[] = $type;
        }

        $category_names = array_values(array_unique(array_filter($category_names)));
        if (count($category_names))
        {
            $schema['category'] = count($category_names) === 1 ? $category_names[0] : $category_names;
        }

        $primary_category = $entity->get_data_category();
        if ($primary_category instanceof WP_Term && count($category_names))
        {
            $schema['additionalProperty'][] = $this->property_value('listdom_category', $primary_category->name);
        }

        $category_types = array_values(array_unique(array_filter($category_types)));
        if (count($category_types))
        {
            $schema['additionalType'] = count($category_types) === 1 ? $category_types[0] : $category_types;
        }
    }

    /**
     * Apply location schema.
     * @param array &$schema
     * @param int $listing_id
     * @return void
     */
    protected function apply_location_schema(array &$schema, int $listing_id): void
    {
        // Location Terms
        $locations = $this->listing_taxonomy_terms($listing_id, LSD_Base::TAX_LOCATION);
        $area_served = [];

        foreach ($locations as $location)
        {
            $name = trim($location->name);
            if ($name === '') continue;

            $schema['keywords'] = $this->append_keywords($schema['keywords'] ?? [], $name);

            $item = [
                '@type' => $this->term_schema_type($location) ?: 'Place',
                'name' => $name,
            ];

            $url = $this->payload->safe_url(get_term_link($location));
            if ($url !== '') $item['url'] = $url;

            $area_served[] = $item;
        }

        if (count($area_served)) $schema['areaServed'] = count($area_served) === 1 ? $area_served[0] : $area_served;
    }

    /**
     * Apply keyword taxonomy schema.
     * @param array &$schema
     * @param int $listing_id
     * @param array $taxonomies
     * @return void
     */
    protected function apply_keyword_taxonomy_schema(array &$schema, int $listing_id, array $taxonomies): void
    {
        // Keyword Terms
        foreach ($taxonomies as $taxonomy)
        {
            foreach ($this->listing_taxonomy_terms($listing_id, $taxonomy) as $term)
            {
                $name = trim($term->name);
                if ($name === '') continue;

                $schema['keywords'] = $this->append_keywords($schema['keywords'] ?? [], $name);
            }
        }
    }

    /**
     * Apply feature schema.
     * @param array &$schema
     * @param array $features
     * @return void
     */
    protected function apply_feature_schema(array &$schema, array $features): void
    {
        // Feature Terms
        $amenities = [];

        foreach ($features as $feature)
        {
            if (!$feature instanceof WP_Term) continue;

            $name = trim($feature->name);
            if ($name === '') continue;

            $schema['keywords'] = $this->append_keywords($schema['keywords'] ?? [], $name);

            $property = $this->term_schema_property($feature);
            if ($property === '') $property = 'amenityFeature';

            if ($property === 'keywords')
            {
                $schema['keywords'] = $this->append_keywords($schema['keywords'] ?? [], $name);
                continue;
            }

            if (in_array($property, ['knowsAbout', 'serviceType', 'category'], true))
            {
                $schema[$property] = $this->append_text_array_value($schema[$property] ?? [], $name);
                continue;
            }

            if ($property === 'amenityFeature')
            {
                $item = [
                    '@type' => $this->term_schema_type($feature) ?: 'LocationFeatureSpecification',
                    'name' => $name,
                    'value' => true,
                ];

                $url = $this->payload->safe_url(get_term_link($feature));
                if ($url !== '') $item['url'] = $url;

                $amenities[] = $item;
                continue;
            }

            $schema['additionalProperty'][] = $this->property_value($name, true, $property);
        }

        if (count($amenities)) $schema['amenityFeature'] = $amenities;
    }

    /**
     * Apply attribute schema.
     * @param array &$schema
     * @param int $listing_id
     * @return void
     */
    protected function apply_attribute_schema(array &$schema, int $listing_id): void
    {
        // Attributes
        $stored_attributes = get_post_meta($listing_id, 'lsd_attributes', true);
        if (!is_array($stored_attributes) || !count($stored_attributes)) return;

        $attribute_context = LSD_Taxonomies_Attribute::context(['post_id' => $listing_id]);

        foreach (LSD_Main::get_attributes() as $attribute)
        {
            if (!$attribute instanceof WP_Term) continue;

            $slug = sanitize_title($attribute->slug);
            if ($slug === '' || !array_key_exists($slug, $stored_attributes)) continue;
            if (!LSD_Taxonomies_Attribute::applies((int) $attribute->term_id, $attribute_context)) continue;

            $raw_value = $stored_attributes[$slug];
            if (!$this->attribute_has_value($raw_value)) continue;

            $property = LSD_AI_Visibility_Schema_Normalizer::schema_property(get_term_meta($attribute->term_id, 'lsd_itemprop', true));
            $field_type = get_term_meta($attribute->term_id, 'lsd_field_type', true);

            $value = $this->attribute_schema_value(
                $raw_value,
                is_scalar($field_type) ? (string) $field_type : '',
                (bool) get_term_meta($attribute->term_id, 'lsd_editor', true),
                $property
            );

            if ($value === null || $value === '' || (is_array($value) && !count($value))) continue;

            $this->append_attribute_schema_value($schema, $attribute->name, $value, $property);
        }
    }

    /**
     * Get listing taxonomy terms.
     * @param int $listing_id
     * @param string $taxonomy
     * @return array
     */
    protected function listing_taxonomy_terms(int $listing_id, string $taxonomy): array
    {
        // Taxonomy Terms
        $terms = wp_get_post_terms($listing_id, $taxonomy);
        if (is_wp_error($terms) || !is_array($terms)) return [];

        $items = [];
        foreach ($terms as $term)
        {
            if ($term instanceof WP_Term) $items[] = $term;
        }

        return $items;
    }

    /**
     * Resolve term schema type.
     * @param WP_Term $term
     * @return string
     */
    protected function term_schema_type(WP_Term $term): string
    {
        // Term Schema Type
        if (!in_array($term->taxonomy, [LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION, LSD_Base::TAX_FEATURE], true)) return '';

        return LSD_AI_Visibility_Schema_Normalizer::schema_type(get_term_meta($term->term_id, 'lsd_schema', true));
    }

    /**
     * Resolve term schema property.
     * @param WP_Term $term
     * @return string
     */
    protected function term_schema_property(WP_Term $term): string
    {
        // Term Schema Property
        if ($term->taxonomy !== LSD_Base::TAX_FEATURE) return '';

        return LSD_AI_Visibility_Schema_Normalizer::schema_property(get_term_meta($term->term_id, 'lsd_itemprop', true));
    }

    /**
     * Append text to an array value.
     * @param mixed $values
     * @param string $value
     * @return array
     */
    protected function append_text_array_value($values, string $value): array
    {
        // Text Value
        $items = is_array($values) ? $values : [$values];
        $items[] = $value;

        return array_values(array_unique(array_filter(array_map('trim', $items))));
    }

    /**
     * Build a PropertyValue schema node.
     * @param string $name
     * @param mixed $value
     * @param string $property_id
     * @return array
     */
    protected function property_value(string $name, $value, string $property_id = ''): array
    {
        // Property Value
        if (is_array($value))
        {
            $value = implode(', ', $this->property_value_items($value));
        }

        $property = [
            '@type' => 'PropertyValue',
            'name' => $name,
            'value' => $value,
        ];

        if ($property_id !== '') $property['propertyID'] = $property_id;

        return $property;
    }

    /**
     * Build nested PropertyValue item nodes.
     * @param array $value
     * @return array
     */
    protected function property_value_items(array $value): array
    {
        // Property Value Items
        $items = [];

        foreach ($value as $item)
        {
            if (is_array($item))
            {
                foreach ($this->property_value_items($item) as $nested_item)
                {
                    $items[] = $nested_item;
                }

                continue;
            }

            if (!is_scalar($item)) continue;

            if (is_bool($item))
            {
                $items[] = $item ? 'true' : 'false';
                continue;
            }

            $text = $this->attribute_schema_text($item);
            if ($text !== '') $items[] = $text;
        }

        return array_values(array_unique($items));
    }

    /**
     * Append an attribute schema value.
     * @param array &$schema
     * @param string $name
     * @param mixed $value
     * @param string $property
     * @return void
     */
    protected function append_attribute_schema_value(array &$schema, string $name, $value, string $property = ''): void
    {
        // Attribute Value
        if ($property === '' || $property === 'additionalProperty')
        {
            $schema['additionalProperty'][] = $this->property_value($name, $value);
            return;
        }

        if ($property === 'keywords')
        {
            $items = is_array($value) ? $value : [$value];

            foreach ($items as $item)
            {
                $item = $this->attribute_schema_text($item);
                if ($item === '') continue;

                $schema['keywords'] = $this->append_keywords($schema['keywords'] ?? [], $item);
            }

            return;
        }

        if (in_array($property, ['knowsAbout', 'serviceType', 'category'], true))
        {
            $items = is_array($value) ? $value : [$value];

            foreach ($items as $item)
            {
                $item = $this->attribute_schema_text($item);
                if ($item === '') continue;

                $schema[$property] = $this->append_text_array_value($schema[$property] ?? [], $item);
            }

            return;
        }

        if ($property === 'sameAs')
        {
            $items = is_array($value) ? $value : [$value];

            foreach ($items as $item)
            {
                $item = $this->attribute_url_value($item);
                if ($item === '') continue;

                $schema['sameAs'] = $this->append_text_array_value($schema['sameAs'] ?? [], $item);
            }

            return;
        }

        if ($property === 'image')
        {
            $items = is_array($value) ? $value : [$value];

            foreach ($items as $item)
            {
                $item = $this->attribute_url_value($item);
                if ($item === '') continue;

                $schema['image'] = $this->append_text_array_value($schema['image'] ?? [], $item);
            }

            return;
        }

        if (is_array($value))
        {
            $schema['additionalProperty'][] = $this->property_value($name, $value, $property);
            return;
        }

        if (!array_key_exists($property, $schema))
        {
            $schema[$property] = $value;
            return;
        }

        if ($schema[$property] === $value) return;

        $schema['additionalProperty'][] = $this->property_value($name, $value, $property);
    }

    /**
     * Check whether an attribute value is present.
     * @param mixed $value
     * @return bool
     */
    protected function attribute_has_value($value): bool
    {
        // Attribute Has Value
        if (is_array($value))
        {
            foreach ($value as $item) if ($this->attribute_has_value($item)) return true;

            return false;
        }

        if (!is_scalar($value)) return false;

        return trim((string) $value) !== '';
    }

    /**
     * Avoid undated Event schema types.
     * @param array &$schema
     * @return void
     */
    protected function avoid_undated_event_schema(array &$schema): void
    {
        // Event Type
        if (!$this->schema_contains_event_type($schema['@type'] ?? '')) return;
        if (!empty($schema['startDate']) || !empty($schema['endDate'])) return;

        if (!empty($schema['subEvent']) && is_array($schema['subEvent']))
        {
            foreach ($schema['subEvent'] as $event)
            {
                if (!is_array($event)) continue;
                if (!empty($event['startDate']) || !empty($event['endDate'])) return;
            }
        }

        $types = is_array($schema['@type']) ? $schema['@type'] : [$schema['@type']];
        $normalized = [];

        foreach ($types as $type)
        {
            if (!is_scalar($type)) continue;
            if (LSD_AI_Visibility_Schema_Normalizer::is_event_type($type)) continue;

            $type = trim((string) $type);
            if ($type !== '') $normalized[] = $type;
        }

        $schema['@type'] = count($normalized) === 1 ? $normalized[0] : (count($normalized) ? array_values(array_unique($normalized)) : 'https://schema.org/LocalBusiness');
    }

    /**
     * Check whether schema types contain Event.
     * @param mixed $types
     * @return bool
     */
    protected function schema_contains_event_type($types): bool
    {
        // Schema Type
        $types = is_array($types) ? $types : [$types];

        foreach ($types as $type)
        {
            if (!is_scalar($type)) continue;
            if (LSD_AI_Visibility_Schema_Normalizer::is_event_type($type)) return true;
        }

        return false;
    }

    /**
     * Normalize an attribute schema value.
     * @param mixed $value
     * @param string $type
     * @param bool $rich_editor
     * @param string $property
     * @return void
     */
    protected function attribute_schema_value($value, string $type, bool $rich_editor = false, string $property = '')
    {
        // Attribute Schema Value
        if ($type === 'separator') return null;

        if ($type === 'checkbox')
        {
            $items = is_array($value) ? $value : [$value];
            $normalized = [];

            foreach ($items as $item)
            {
                $item = $this->attribute_schema_text($item);
                if ($item === '') continue;

                $normalized[] = $item;
            }

            return array_values(array_unique($normalized));
        }

        if (is_array($value) || is_object($value)) return null;

        switch ($type)
        {
            case 'number':
                $number = trim((string) $value);
                if ($number === '') return null;

                if (!is_numeric($number)) return null;

                $number = (float) $number;
                if (!is_finite($number) || $number > PHP_INT_MAX || $number < (-PHP_INT_MAX - 1)) return null;

                return (int) $number;

            case 'email':
                $email = sanitize_email((string) $value);
                return is_email($email) ? $email : null;

            case 'url':
                $url = $this->attribute_url_value($value);
                return $url !== '' ? $url : null;

            case 'file':
            case 'image':
                $url = $this->attribute_media_url($value);
                return $url !== '' ? $url : null;

            case 'textarea':
                return $this->attribute_schema_text($value, $rich_editor);

            case 'date':
                $date = $this->normalize_date_value((string) $value);
                if ($date !== '') return $date;
                if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', trim((string) $value))) return null;

                return in_array($property, ['', 'additionalProperty'], true) ? $this->attribute_schema_text($value) : null;

            case 'time':
                $time = $this->normalize_time((string) $value);
                if ($time !== '') return $time;
                if (preg_match('/^\d{1,2}(?::\d{2}){0,2}$/', trim((string) $value))) return null;

                return in_array($property, ['', 'additionalProperty'], true) ? $this->attribute_schema_text($value) : null;

            case 'datetime':
                $datetime = $this->normalize_datetime_value((string) $value);
                if ($datetime !== '') return $datetime;
                if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}[ T]\d{1,2}:\d{2}(?::\d{2})?$/', trim((string) $value))) return null;

                return in_array($property, ['', 'additionalProperty'], true) ? $this->attribute_schema_text($value) : null;

            case 'dropdown':
            case 'radio':
            default:
                return $this->attribute_schema_text($value);
        }
    }

    /**
     * Normalize attribute text.
     * @param mixed $value
     * @param bool $rich_editor
     * @return string
     */
    protected function attribute_schema_text($value, bool $rich_editor = false): string
    {
        // Attribute Schema Text
        if (!is_scalar($value)) return '';

        $text = (string) $value;
        if ($rich_editor) $text = wp_strip_all_tags($text);

        $text = preg_replace('/\s+/u', ' ', $text);
        return trim((string) $text);
    }

    /**
     * Normalize attribute media URL.
     * @param mixed $value
     * @return string
     */
    protected function attribute_media_url($value): string
    {
        // Attribute Media URL
        if (!is_scalar($value)) return '';

        if (is_numeric($value))
        {
            $url = wp_get_attachment_url((int) $value);
            return $url ? esc_url_raw($url) : '';
        }

        $url = trim((string) $value);
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) return '';

        return esc_url_raw($url);
    }

    /**
     * Normalize attribute URL value.
     * @param mixed $value
     * @return string
     */
    protected function attribute_url_value($value): string
    {
        // Attribute URL Value
        if (!is_scalar($value)) return '';

        $url = trim((string) $value);
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) return '';

        return esc_url_raw($url);
    }

    /**
     * Normalize Date Value.
     * @param string $date
     * @return string
     */
    protected function normalize_date_value(string $date): string
    {
        // Normalize Date Value
        $date = trim($date);
        if ($date === '') return '';

        $dt = DateTime::createFromFormat('!Y-m-d', $date);
        $errors = DateTime::getLastErrors();

        if ($dt instanceof DateTime && (!$errors || (!$errors['warning_count'] && !$errors['error_count']))) return $dt->format('Y-m-d');
        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $date)) return '';

        $timestamp = strtotime($date);
        if ($timestamp === false) return '';

        return wp_date('Y-m-d', $timestamp);
    }

    /**
     * Normalize Datetime Value.
     * @param string $datetime
     * @return string
     */
    protected function normalize_datetime_value(string $datetime): string
    {
        // Normalize Datetime Value
        $datetime = trim(str_replace(' ', 'T', $datetime));
        if ($datetime === '') return '';

        foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s'] as $format)
        {
            $dt = DateTime::createFromFormat($format, $datetime);
            $errors = DateTime::getLastErrors();

            if ($dt instanceof DateTime && (!$errors || (!$errors['warning_count'] && !$errors['error_count']))) return $dt->format('Y-m-d\TH:i');
        }

        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}T\d{1,2}:\d{2}(?::\d{2})?$/', $datetime)) return '';

        $timestamp = strtotime(str_replace('T', ' ', $datetime));
        if ($timestamp === false) return '';

        return wp_date('Y-m-d\TH:i', $timestamp);
    }

    /**
     * Normalize Time.
     * @param string $time
     * @return string
     */
    protected function normalize_time(string $time): string
    {
        // Normalize Time
        $time = trim($time);
        if ($time === '') return '';

        foreach (['G', 'H', 'H:i', 'H:i:s', 'g:i a', 'g:i A', 'g a', 'g A'] as $format)
        {
            $dt = DateTime::createFromFormat($format, $time);
            $errors = DateTime::getLastErrors();

            if ($dt instanceof DateTime && (!$errors || (!$errors['warning_count'] && !$errors['error_count']))) return $dt->format('H:i');
        }

        if (preg_match('/^\d{1,2}(?::\d{2}){0,2}$/', $time)) return '';

        $timestamp = strtotime($time);
        if ($timestamp === false) return '';

        return gmdate('H:i', $timestamp);
    }

    /**
     * Append a URL fragment.
     * @param string $url
     * @param string $fragment
     * @return string
     */
    protected function with_fragment(string $url, string $fragment): string
    {
        // Fragment
        $fragment = ltrim($fragment, '#');
        $parts = wp_parse_url($url);
        if (!is_array($parts)) return untrailingslashit($url) . '/#' . $fragment;

        $base = '';

        if (isset($parts['scheme'])) $base .= $parts['scheme'] . '://';
        if (isset($parts['user'])) $base .= $parts['user'];
        if (isset($parts['pass'])) $base .= ':' . $parts['pass'];
        if (isset($parts['user'])) $base .= '@';
        if (isset($parts['host'])) $base .= $parts['host'];
        if (isset($parts['port'])) $base .= ':' . $parts['port'];

        $path = isset($parts['path']) ? $parts['path'] : '/';
        $base .= $path !== '' ? $path : '/';

        if (isset($parts['query']) && $parts['query'] !== '') $base .= '?' . $parts['query'];

        return $base . '#' . $fragment;
    }

    /**
     * Check whether the current page is a listing collection.
     * @return bool
     */
    protected function is_listing_collection_page(): bool
    {
        // Collection Context
        if ($this->uses_shortcode_collection_page()) return false;
        if (is_post_type_archive(LSD_Base::PTYPE_LISTING)) return true;
        if (is_tax([LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION, LSD_Base::TAX_TAG, LSD_Base::TAX_FEATURE, LSD_Base::TAX_LABEL])) return true;

        if (is_search())
        {
            global $wp_query;

            if (!($wp_query instanceof WP_Query) || !count($wp_query->posts)) return false;

            $found_listing = false;
            foreach ($wp_query->posts as $post)
            {
                if (!$post instanceof WP_Post) continue;
                if ($post->post_type !== LSD_Base::PTYPE_LISTING) return false;

                $found_listing = true;
            }

            return $found_listing;
        }

        return false;
    }

    /**
     * Check whether a shortcode collection should render.
     * @param LSD_Skins $skin
     * @return bool
     */
    protected function should_render_shortcode_collection(LSD_Skins $skin): bool
    {
        // Render Context
        if ($skin->widget) return false;
        if (!empty($skin->atts['embed'])) return false;
        if (is_singular(LSD_Base::PTYPE_LISTING)) return false;

        if ($this->is_listing_collection_page() && !$this->uses_shortcode_collection_page()) return false;

        return true;
    }

    /**
     * Build eligible shortcode collection graph.
     * @param LSD_Skins $skin
     * @return array
     */
    protected function eligible_shortcode_collection_graph(LSD_Skins $skin): array
    {
        // Render Guards
        if (is_admin() || wp_doing_ajax() || (function_exists('wp_is_json_request') && wp_is_json_request())) return [];
        if (!$this->settings->structured_data_enabled()) return [];

        if (!apply_filters('lsd_ai_visibility_should_render_schema', true, $this->owner)) return [];
        if (!$this->should_render_shortcode_collection($skin)) return [];

        $fragment = $this->shortcode_collection_fragment($skin);
        if (isset($this->rendered_shortcode_collections[$fragment])) return [];

        $graph = $this->shortcode_collection_graph($skin);
        return count($graph) ? $graph : [];
    }

    /**
     * Build renderable shortcode collection graph.
     * @param LSD_Skins $skin
     * @return array
     */
    protected function renderable_shortcode_collection_graph(LSD_Skins $skin): array
    {
        // Eligible Graph
        $graph = $this->eligible_shortcode_collection_graph($skin);
        if (!count($graph)) return [];

        if (method_exists($skin, 'rendered_collection_output') && !$skin->rendered_collection_output()) return [];

        $fragment = $this->shortcode_collection_fragment($skin);
        if (isset($this->rendered_shortcode_collections[$fragment])) return [];

        return $graph;
    }

    /**
     * Check whether the page uses shortcode collection output.
     * @return bool
     */
    protected function uses_shortcode_collection_page(): bool
    {
        // Queried Term
        $queried = get_queried_object();
        if (!($queried instanceof WP_Term)) return false;

        $archive_shortcode_id = $this->owner->archive_shortcode_id($queried);
        if ($archive_shortcode_id <= 0) return false;

        $file = 'taxonomy-' . $queried->taxonomy . '.php';
        $located_template = locate_template($file);

        // Listdomer taxonomy template
        if ($located_template !== '' && $this->template_runs_taxonomy_archive_shortcode($located_template)) return true;

        // Listdom
        return $located_template === '';
    }

    /**
     * Check whether shortcode collection page rendering is active.
     * @return bool
     */
    public function shortcode_collection_page_active(): bool
    {
        // Page Status
        return $this->uses_shortcode_collection_page();
    }

    /**
     * Check whether a template runs taxonomy archive shortcode output.
     * @param string $template
     * @return bool
     */
    protected function template_runs_taxonomy_archive_shortcode(string $template): bool
    {
        // Template File
        if (!is_readable($template)) return false;

        $contents = file_get_contents($template);
        if (!is_string($contents) || $contents === '') return false;

        return strpos($contents, 'do_action') !== false && strpos($contents, '_archive_content') !== false;
    }

    /**
     * Build shortcode collection fragment.
     * @param LSD_Skins $skin
     * @return string
     */
    protected function shortcode_collection_fragment(LSD_Skins $skin): string
    {
        // Fragment ID
        $id = isset($skin->collection_schema_fragment_id) && is_scalar($skin->collection_schema_fragment_id) && trim((string) $skin->collection_schema_fragment_id) !== ''
            ? $skin->collection_schema_fragment_id
            : $skin->id;
        $fragment = 'shortcode-' . sanitize_title_with_dashes((string) $id);

        return $fragment === 'shortcode-' ? 'shortcode-listdom' : $fragment;
    }

    /**
     * Build JSON-LD markup.
     * @param array $graph
     * @return string
     */
    protected function json_ld_markup(array $graph): string
    {
        // JSON-LD Script
        return "\n<script type=\"application/ld+json\">" . wp_json_encode([
            '@context' => 'https://schema.org',
            '@graph' => array_values($graph),
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . "</script>\n";
    }

    /**
     * Check whether single listing schema is supported.
     * @param int $listing_id
     * @return bool
     */
    protected function supports_single_listing_schema(int $listing_id): bool
    {
        // Listing Style
        $style = LSD_PTypes_Listing_Single::get_listing_style($listing_id);

        // Supported Styles
        return in_array($style, ['style1', 'style2', 'style3', 'style4', 'dynamic'], true);
    }
}
