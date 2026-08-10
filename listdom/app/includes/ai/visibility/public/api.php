<?php

class LSD_AI_Visibility_Public_API extends LSD_Base
{
    protected LSD_AI_Visibility $owner;
    protected LSD_AI_Visibility_Settings $settings;
    protected LSD_AI_Visibility_Payload $payload;
    protected array $public_term_counts = [];
    protected array $hierarchical_public_term_count_cache = [];

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
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register public REST routes.
     * @return void
     */
    public function register_routes(): void
    {
        // Routes
        register_rest_route('listdom/v1', '/public/listings', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'public_listings'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('listdom/v1', '/public/listings/(?P<id>\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'public_listing'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'validate_callback' => [$this, 'validate_numeric_value'],
                ],
            ],
        ]);

        register_rest_route('listdom/v1', '/public/categories', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'public_categories'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('listdom/v1', '/public/locations', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'public_locations'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Validate numeric route values.
     * @param mixed $value
     * @return bool
     */
    public function validate_numeric_value($value): bool
    {
        return is_numeric($value);
    }

    /**
     * Handle public listings feed requests.
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function public_listings(WP_REST_Request $request): WP_REST_Response
    {
        // Availability
        if (!$this->settings->public_feed_available()) return $this->feed_unavailable_response();

        $page = $this->integer_request_param($request, 'page', 1);
        if (is_wp_error($page)) return $this->error_response($page);
        $page = max(1, $page);

        $per_page = $this->integer_request_param($request, 'per_page', 20);
        if (is_wp_error($per_page)) return $this->error_response($per_page);
        $per_page = $per_page > 0 ? min($per_page, 100) : 20;

        $args = $this->public_listing_query_args([
            'posts_per_page' => $per_page,
            'paged' => $page,
        ]);

        $search = $this->string_request_param($request, 'search');
        if (is_wp_error($search)) return $this->error_response($search);
        if ($search !== '') $args['s'] = $search;

        $authors = $this->integer_request_param_list($request, 'authors');
        if (is_wp_error($authors)) return $this->error_response($authors);
        if (count($authors)) $args['author__in'] = $authors;

        $include = $this->integer_request_param_list($request, 'include');
        if (is_wp_error($include)) return $this->error_response($include);
        if (count($include)) $args['post__in'] = $include;

        $exclude = $this->integer_request_param_list($request, 'exclude');
        if (is_wp_error($exclude)) return $this->error_response($exclude);
        if (count($exclude)) $args['post__not_in'] = $exclude;

        $tax_query = [];
        foreach ($this->listing_filter_taxonomies() as $query_key => $taxonomy)
        {
            $filter = $this->taxonomy_request_param($request, $query_key);
            if (is_wp_error($filter)) return $this->error_response($filter);
            if (!count($filter['terms'])) continue;

            $tax_query[] = $this->payload->term_query($taxonomy, $filter);
        }

        if (count($tax_query))
        {
            if (isset($args['tax_query']) && is_array($args['tax_query']) && count($args['tax_query']))
            {
                $merged_tax_query = ['relation' => 'AND'];

                foreach ($args['tax_query'] as $key => $clause)
                {
                    if ($key === 'relation' || !is_array($clause)) continue;

                    $merged_tax_query[] = $clause;
                }

                foreach ($tax_query as $clause)
                {
                    if (is_array($clause)) $merged_tax_query[] = $clause;
                }

                $args['tax_query'] = $merged_tax_query;
            }
            else
            {
                if (count($tax_query) > 1) $tax_query['relation'] = 'AND';
                $args['tax_query'] = $tax_query;
            }
        }

        $query = new WP_Query($args);

        $items = [];
        foreach ($query->posts as $post) $items[] = $this->payload->public_listing_payload((int) $post->ID);

        return $this->response([
            'success' => 1,
            'items' => array_values(array_filter($items)),
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total_items' => (int) $query->found_posts,
                'total_pages' => (int) $query->max_num_pages,
            ],
            'fields' => $this->public_response_fields(),
        ]);
    }

    /**
     * Handle public listing feed requests.
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function public_listing(WP_REST_Request $request): WP_REST_Response
    {
        // Availability
        if (!$this->settings->public_feed_available()) return $this->feed_unavailable_response();

        $id = absint($request->get_param('id'));
        $payload = $this->payload->public_listing_payload($id);

        if (!count($payload)) return $this->response(['success' => 0, 'message' => esc_html__('Listing not found.', 'listdom'),], 404);

        return $this->response(['success' => 1, 'item' => $payload]);
    }

    /**
     * Handle public category feed requests.
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function public_categories(WP_REST_Request $request): WP_REST_Response
    {
        // Availability
        if (!$this->settings->public_feed_available()) return $this->feed_unavailable_response();

        return $this->public_terms($request, LSD_Base::TAX_CATEGORY);
    }

    /**
     * Handle public location feed requests.
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function public_locations(WP_REST_Request $request): WP_REST_Response
    {
        // Availability
        if (!$this->settings->public_feed_available()) return $this->feed_unavailable_response();

        return $this->public_terms($request, LSD_Base::TAX_LOCATION);
    }

    /**
     * Handle public taxonomy feed requests.
     * @param WP_REST_Request $request
     * @param string $taxonomy
     * @return WP_REST_Response
     */
    protected function public_terms(WP_REST_Request $request, string $taxonomy): WP_REST_Response
    {
        // Pagination
        $page = $this->integer_request_param($request, 'page', 1);
        if (is_wp_error($page)) return $this->error_response($page);
        $page = max(1, $page);

        $per_page = $this->integer_request_param($request, 'per_page', 50);
        if (is_wp_error($per_page)) return $this->error_response($per_page);
        $per_page = $per_page > 0 ? min($per_page, 100) : 50;
        $search = $this->string_request_param($request, 'search');
        if (is_wp_error($search)) return $this->error_response($search);
        $hide_empty = $this->boolean_request_param($request, 'hide_empty', true);
        if (is_wp_error($hide_empty)) return $this->error_response($hide_empty);

        $args = [
            'hide_empty' => $hide_empty,
            'number' => $per_page,
            'offset' => ($page - 1) * $per_page,
        ];

        if ($search !== '') $args['search'] = $search;

        $terms = $this->public_terms_query($taxonomy, $args);
        $total_items = $this->public_terms_count($taxonomy, [
            'hide_empty' => $hide_empty,
            'search' => $search,
        ]);
        $total_pages = (int) ceil($total_items / $per_page);
        $counts = $this->public_term_counts($taxonomy, $terms);

        $items = [];
        foreach ($terms as $term)
        {
            if (!$term instanceof WP_Term) continue;
            $items[] = $this->payload->public_term_payload($term, $counts[$term->term_id] ?? 0);
        }

        return $this->response([
            'success' => 1,
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total_items' => $total_items,
                'total_pages' => $total_pages,
            ],
        ]);
    }

    /**
     * Get public response field names.
     * @return array
     */
    protected function public_response_fields(): array
    {
        // Enabled Fields
        $fields = array_keys(array_filter($this->settings->fields()));

        $optional_fields = [
            'include_verified_status' => 'verified',
            'include_reviews' => 'reviews',
            'include_booking_summary' => 'booking_summary',
        ];

        foreach ($optional_fields as $setting => $field)
        {
            if ($this->settings->enabled($setting)) $fields[] = $field;
        }

        $fields = apply_filters('lsd_ai_visibility_public_response_fields', $fields, $this->owner);

        if (!is_array($fields)) return [];

        $normalized = [];
        foreach ($fields as $field)
        {
            if (!is_string($field)) continue;

            $field = trim($field);
            if ($field === '') continue;

            $normalized[] = $field;
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Build a REST response.
     * @param array $data
     * @param int $status
     * @return WP_REST_Response
     */
    public function response(array $data, int $status = 200): WP_REST_Response
    {
        // REST Response
        $response = new WP_REST_Response($data);
        $response->set_status($status);

        return $response;
    }

    /**
     * Build a REST error response.
     * @param WP_Error $error
     * @return WP_REST_Response
     */
    protected function error_response(WP_Error $error): WP_REST_Response
    {
        // Error Status
        $status = (int) $error->get_error_data();
        if ($status <= 0) $status = 400;

        return $this->response([
            'success' => 0,
            'message' => $error->get_error_message(),
        ], $status);
    }

    /**
     * Build a feed unavailable response.
     * @return WP_REST_Response
     */
    protected function feed_unavailable_response(): WP_REST_Response
    {
        // Unavailable Response
        return $this->response([
            'success' => 0,
            'message' => esc_html__('The public listings feed is unavailable.', 'listdom'),
        ], 403);
    }

    /**
     * Build public feed URLs.
     * @param array $context
     * @return array
     */
    public function feed_urls(array $context = []): array
    {
        // Base URLs
        if (isset($context['representable']) && !$context['representable']) return [];

        $urls = [
            'listings' => rest_url('listdom/v1/public/listings'),
            'categories' => rest_url('listdom/v1/public/categories'),
            'locations' => rest_url('listdom/v1/public/locations'),
        ];

        $listing_id = isset($context['listing_id']) ? absint($context['listing_id']) : 0;
        if ($listing_id > 0 && $this->payload->is_public_listing(get_post($listing_id))) $urls['listing'] = rest_url('listdom/v1/public/listings/' . $listing_id);

        $listing_feed_args = $this->listing_feed_query_args($context);
        if (count($listing_feed_args)) $urls['listings'] = add_query_arg($listing_feed_args, $urls['listings']);

        $term = $context['term'] ?? null;
        if ($term instanceof WP_Term)
        {
            $term_filter = $this->term_feed_query_args($term);

            if ($term->taxonomy === LSD_Base::TAX_CATEGORY) $urls['category_listings'] = add_query_arg($term_filter, $urls['listings']);
            else if ($term->taxonomy === LSD_Base::TAX_LOCATION) $urls['location_listings'] = add_query_arg($term_filter, $urls['listings']);
            else if ($term->taxonomy === LSD_Base::TAX_TAG) $urls['tag_listings'] = add_query_arg($term_filter, $urls['listings']);
            else if ($term->taxonomy === LSD_Base::TAX_FEATURE) $urls['feature_listings'] = add_query_arg($term_filter, $urls['listings']);
            else if ($term->taxonomy === LSD_Base::TAX_LABEL) $urls['label_listings'] = add_query_arg($term_filter, $urls['listings']);
        }

        $normalized = [];
        foreach ($urls as $key => $url)
        {
            if (!is_string($url)) continue;

            $url = trim($url);
            if ($url === '') continue;

            $normalized[$key] = $url;
        }

        return (array) apply_filters('lsd_ai_public_feed_urls', $normalized, $context, $this->owner);
    }

    /**
     * Build listing feed URL query arguments.
     * @param array $context
     * @return array
     */
    protected function listing_feed_query_args(array $context): array
    {
        // Query Args
        $args = [];

        $search = isset($context['search']) && is_scalar($context['search']) ? sanitize_text_field((string) $context['search']) : '';
        if ($search !== '') $args['search'] = $search;

        foreach ($this->listing_filter_taxonomies() as $query_key => $taxonomy)
        {
            $filter = $this->context_term_filter($context, $query_key, $taxonomy);
            if (!count($filter)) continue;

            $args[$query_key] = count($filter) === 1 ? reset($filter) : array_values($filter);
            $args[$query_key . '_field'] = 'slug';
        }

        foreach (['authors', 'include', 'exclude'] as $key)
        {
            $values = $this->context_integer_filter($context, $key);
            if (!count($values)) continue;

            $args[$key] = count($values) === 1 ? reset($values) : array_values($values);
        }

        return $args;
    }

    /**
     * Resolve a taxonomy filter from discovery context.
     * @param array $context
     * @param string $query_key
     * @param string $taxonomy
     * @return array
     */
    protected function context_term_filter(array $context, string $query_key, string $taxonomy): array
    {
        // Filter Values
        $filters = isset($context['filters']) && is_array($context['filters']) ? $context['filters'] : [];
        $values = $filters[$query_key] ?? ($filters[$taxonomy] ?? ($context[$query_key] ?? null));
        if ($values === null || $values === '') return [];

        $values = is_array($values) ? $values : [$values];
        $normalized = [];

        foreach ($values as $value)
        {
            if (!is_scalar($value)) continue;

            $value = trim(sanitize_text_field((string) $value));
            if ($value === '') continue;

            $term = preg_match('/^\d+$/', $value) ? get_term_by('id', (int) $value, $taxonomy) : false;
            if (!$term instanceof WP_Term) $term = get_term_by('slug', $value, $taxonomy);
            if (!$term instanceof WP_Term) $term = get_term_by('name', $value, $taxonomy);
            if (!$term instanceof WP_Term) continue;

            $normalized[] = $term->slug;
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Build listing feed arguments for a term context.
     * @param WP_Term $term
     * @return array
     */
    protected function term_feed_query_args(WP_Term $term): array
    {
        // Term Filter
        if ($term->taxonomy === LSD_Base::TAX_CATEGORY) return ['category' => $term->slug, 'category_field' => 'slug'];
        if ($term->taxonomy === LSD_Base::TAX_LOCATION) return ['location' => $term->slug, 'location_field' => 'slug'];
        if ($term->taxonomy === LSD_Base::TAX_TAG) return ['tag' => $term->slug, 'tag_field' => 'slug'];
        if ($term->taxonomy === LSD_Base::TAX_FEATURE) return ['feature' => $term->slug, 'feature_field' => 'slug'];
        if ($term->taxonomy === LSD_Base::TAX_LABEL) return ['label' => $term->slug, 'label_field' => 'slug'];

        return [];
    }

    /**
     * Resolve an integer-list filter from discovery context.
     * @param array $context
     * @param string $key
     * @return array
     */
    protected function context_integer_filter(array $context, string $key): array
    {
        // Filter Values
        $filters = isset($context['filters']) && is_array($context['filters']) ? $context['filters'] : [];
        $values = $filters[$key] ?? ($context[$key] ?? null);
        if ($values === null || $values === '') return [];

        $values = is_array($values) ? $values : [$values];
        $normalized = [];

        foreach ($values as $value)
        {
            if (!is_scalar($value)) continue;

            $value = absint($value);
            if ($value > 0) $normalized[] = $value;
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Build public listing query arguments.
     * @param array $overrides
     * @return array
     */
    public function public_listing_query_args(array $overrides = []): array
    {
        // Query Defaults
        $args = self::parse_args($overrides, [
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => 'publish',
            'has_password' => false,
            'ignore_sticky_posts' => true,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        return (array) apply_filters('lsd_ai_visibility_public_listing_query_args', $args, $this->owner);
    }

    /**
     * Get supported listing filter taxonomies.
     * @return array
     */
    protected function listing_filter_taxonomies(): array
    {
        // Taxonomies
        return [
            'category' => LSD_Base::TAX_CATEGORY,
            'location' => LSD_Base::TAX_LOCATION,
            'tag' => LSD_Base::TAX_TAG,
            'feature' => LSD_Base::TAX_FEATURE,
            'label' => LSD_Base::TAX_LABEL,
        ];
    }

    /**
     * Read a scalar string request parameter.
     * @param WP_REST_Request $request
     * @param string $key
     * @return string|WP_Error
     */
    protected function string_request_param(WP_REST_Request $request, string $key)
    {
        // Request Value
        $value = $request->get_param($key);
        if ($value === null) return '';

        if (!is_scalar($value))
        {
            return new WP_Error(
                'lsd_ai_visibility_invalid_param',
                sprintf(
                    /* translators: %s: request parameter name. */
                    esc_html__('%s must be a string.', 'listdom'),
                    $key
                ),
                400
            );
        }

        return sanitize_text_field((string) $value);
    }

    /**
     * Read a scalar boolean request parameter.
     * @param WP_REST_Request $request
     * @param string $key
     * @param bool $default
     * @return bool|WP_Error
     */
    protected function boolean_request_param(WP_REST_Request $request, string $key, bool $default)
    {
        // Request Value
        $value = $request->get_param($key);
        if ($value === null) return $default;

        if (!is_scalar($value))
        {
            return new WP_Error(
                'lsd_ai_visibility_invalid_param',
                sprintf(
                    /* translators: %s: request parameter name. */
                    esc_html__('%s must be a boolean.', 'listdom'),
                    $key
                ),
                400
            );
        }

        $validated = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($validated === null)
        {
            return new WP_Error(
                'lsd_ai_visibility_invalid_param',
                sprintf(
                    /* translators: %s: request parameter name. */
                    esc_html__('%s must be a boolean.', 'listdom'),
                    $key
                ),
                400
            );
        }

        return (bool) $validated;
    }

    /**
     * Read a scalar integer request parameter.
     * @param WP_REST_Request $request
     * @param string $key
     * @param int $default
     * @return int|WP_Error
     */
    protected function integer_request_param(WP_REST_Request $request, string $key, int $default)
    {
        // Request Value
        $value = $request->get_param($key);
        if ($value === null || $value === '') return $default;

        if (!is_scalar($value))
        {
            return new WP_Error(
                'lsd_ai_visibility_invalid_param',
                sprintf(
                    /* translators: %s: request parameter name. */
                    esc_html__('%s must be an integer.', 'listdom'),
                    $key
                ),
                400
            );
        }

        $value = trim((string) $value);
        if (!preg_match('/^\d+$/', $value))
        {
            return new WP_Error(
                'lsd_ai_visibility_invalid_param',
                sprintf(
                    /* translators: %s: request parameter name. */
                    esc_html__('%s must be an integer.', 'listdom'),
                    $key
                ),
                400
            );
        }

        return (int) $value;
    }

    /**
     * Read a list of integer request parameters.
     * @param WP_REST_Request $request
     * @param string $key
     * @return array|WP_Error
     */
    protected function integer_request_param_list(WP_REST_Request $request, string $key)
    {
        // Request Value
        $value = $request->get_param($key);
        if ($value === null || $value === '') return [];

        if (is_scalar($value)) $value = array_map('trim', explode(',', (string) $value));

        else if (!is_array($value))
        {
            return new WP_Error(
                'lsd_ai_visibility_invalid_param',
                sprintf(
                    /* translators: %s: request parameter name. */
                    esc_html__('%s must be a list of integers.', 'listdom'),
                    $key
                ),
                400
            );
        }

        $normalized = [];

        foreach ($value as $item)
        {
            if (!is_scalar($item) || !preg_match('/^\d+$/', trim((string) $item)))
            {
                return new WP_Error(
                    'lsd_ai_visibility_invalid_param',
                    sprintf(
                        /* translators: %s: request parameter name. */
                        esc_html__('%s must be a list of integers.', 'listdom'),
                        $key
                    ),
                    400
                );
            }

            $item = (int) trim((string) $item);
            if ($item > 0) $normalized[] = $item;
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Read a taxonomy request filter.
     * @param WP_REST_Request $request
     * @param string $key
     * @return array|WP_Error
     */
    protected function taxonomy_request_param(WP_REST_Request $request, string $key)
    {
        // Request Terms
        $terms = $request->get_param($key);
        if ($terms === null || $terms === '') return ['field' => 'slug', 'terms' => [],];

        $field = $request->get_param($key . '_field');
        if ($field === null || $field === '') $field = 'slug';

        if (!is_scalar($field))
        {
            return new WP_Error(
                'lsd_ai_visibility_invalid_param',
                sprintf(
                    /* translators: %s: request parameter name. */
                    esc_html__('%s must be a string.', 'listdom'),
                    $key . '_field'
                ),
                400
            );
        }

        $field = sanitize_key((string) $field);
        if (!in_array($field, ['slug', 'term_id'], true))
        {
            return new WP_Error(
                'lsd_ai_visibility_invalid_param',
                sprintf(
                    /* translators: %s: request parameter name. */
                    esc_html__('%s must be slug or term_id.', 'listdom'),
                    $key . '_field'
                ),
                400
            );
        }

        $values = is_array($terms) ? $terms : [$terms];
        $normalized = [];

        foreach ($values as $term)
        {
            if (!is_scalar($term))
            {
                return new WP_Error(
                    'lsd_ai_visibility_invalid_param',
                    sprintf(
                        /* translators: %s: request parameter name. */
                        esc_html__('%s must be a list of terms.', 'listdom'),
                        $key
                    ),
                    400
                );
            }

            $term = trim(sanitize_text_field((string) $term));
            if ($term === '') continue;

            if ($field === 'term_id' && !preg_match('/^\d+$/', $term))
            {
                return new WP_Error(
                    'lsd_ai_visibility_invalid_param',
                    sprintf(
                        /* translators: %s: request parameter name. */
                        esc_html__('%s must be a list of term IDs.', 'listdom'),
                        $key
                    ),
                    400
                );
            }

            $normalized[] = $term;
        }

        return [
            'field' => $field,
            'terms' => array_values(array_unique($normalized)),
        ];
    }

    /**
     * Query public taxonomy terms.
     * @param string $taxonomy
     * @param array $args
     * @return array
     */
    public function public_terms_query(string $taxonomy, array $args = []): array
    {
        // Query Args
        $number = isset($args['number']) ? max(0, absint($args['number'])) : 0;
        $offset = isset($args['offset']) ? max(0, absint($args['offset'])) : 0;
        $search = isset($args['search']) ? sanitize_text_field((string) $args['search']) : '';
        $hide_empty = !array_key_exists('hide_empty', $args) || (bool) $args['hide_empty'];

        if ($hide_empty && $this->taxonomy_uses_descendant_term_counts($taxonomy))
        {
            $term_ids = $this->matching_public_term_ids($taxonomy, $search, $number, $offset);
            if (!count($term_ids)) return [];

            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'include' => $term_ids,
                'hide_empty' => false,
                'orderby' => 'include',
            ]);

            return is_wp_error($terms) || !is_array($terms) ? [] : $terms;
        }

        [$joins, $where, $parameters] = $this->public_terms_where($taxonomy, $search, $hide_empty);

        $limit = '';
        if ($number > 0)
        {
            $limit = 'LIMIT %d OFFSET %d';
            $parameters[] = $number;
            $parameters[] = $offset;
        }

        $db = new LSD_db();
        $query = $db->prepare("
        SELECT DISTINCT t.term_id
        FROM `#__terms` AS t
        INNER JOIN `#__term_taxonomy` AS tt ON tt.term_id = t.term_id
        {$joins}
        WHERE {$where}
        ORDER BY t.name ASC, t.term_id ASC
    {$limit}
    ", $parameters);

        $rows = $db->select($query, 'loadAssocList');
        if (!is_array($rows)) return [];

        $term_ids = [];
        foreach ($rows as $row)
        {
            $term_id = isset($row['term_id']) ? absint($row['term_id']) : 0;
            if ($term_id > 0) $term_ids[] = $term_id;
        }

        $term_ids = array_values(array_unique($term_ids));
        if (!count($term_ids)) return [];

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'include' => $term_ids,
            'hide_empty' => false,
            'orderby' => 'include',
        ]);

        return is_wp_error($terms) || !is_array($terms) ? [] : $terms;
    }

    /**
     * Count public taxonomy terms.
     * @param string $taxonomy
     * @param array $args
     * @return int
     */
    public function public_terms_count(string $taxonomy, array $args = []): int
    {
        // Query Args
        $search = isset($args['search']) ? sanitize_text_field((string) $args['search']) : '';
        $hide_empty = !array_key_exists('hide_empty', $args) || (bool) $args['hide_empty'];

        if ($hide_empty && $this->taxonomy_uses_descendant_term_counts($taxonomy)) return $this->matching_public_term_count($taxonomy, $search);

        [$joins, $where, $parameters] = $this->public_terms_where($taxonomy, $search, $hide_empty);

        $db = new LSD_db();
        $query = $db->prepare("
        SELECT COUNT(DISTINCT t.term_id)
        FROM `#__terms` AS t
        INNER JOIN `#__term_taxonomy` AS tt ON tt.term_id = t.term_id
        {$joins}
        WHERE {$where}
    ", $parameters);

        return max(0, (int) $db->select($query, 'loadResult'));
    }

    /**
     * Build public term query SQL parts.
     * @param string $taxonomy
     * @param string $search
     * @param bool $hide_empty
     * @return array
     */
    protected function public_terms_where(string $taxonomy, string $search = '', bool $hide_empty = true): array
    {
        // SQL Parts
        $joins = '';
        $conditions = ['tt.taxonomy = %s'];
        $parameters = [$taxonomy];
        $listing_constraints = $this->public_listing_sql_constraints('p');
        $needs_listing_join = $hide_empty;

        if ($needs_listing_join)
        {
            $joins = "
            INNER JOIN `#__term_relationships` AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN `#__posts` AS p ON p.ID = tr.object_id
        ";

            $conditions[] = 'p.post_type = %s';
            $conditions[] = 'p.post_status = %s';
            $conditions[] = 'p.post_password = %s';

            $parameters[] = LSD_Base::PTYPE_LISTING;
            $parameters[] = 'publish';
            $parameters[] = '';

            if ($listing_constraints['joins'] !== '') $joins .= "\n" . $listing_constraints['joins'];
            foreach ($listing_constraints['where'] as $condition) $conditions[] = $condition;
            $parameters = array_merge($parameters, $listing_constraints['parameters']);
        }

        if ($search !== '')
        {
            $like = '%' . addcslashes($search, '_%\\') . '%';
            $conditions[] = '(t.name LIKE %s OR t.slug LIKE %s)';
            $parameters[] = $like;
            $parameters[] = $like;
        }

        return [$joins, implode(' AND ', $conditions), $parameters];
    }

    /**
     * Build public listing SQL constraints.
     * @param string $post_alias
     * @return array
     */
    protected function public_listing_sql_constraints(string $post_alias): array
    {
        // Filtered Constraints
        $constraints = apply_filters('lsd_ai_visibility_public_listing_sql_constraints', [
            'joins' => '',
            'where' => [],
            'parameters' => [],
        ], $post_alias, $this->owner);

        if (!is_array($constraints)) $constraints = [];

        $joins = isset($constraints['joins']) && is_string($constraints['joins']) ? trim($constraints['joins']) : '';
        $where = isset($constraints['where']) && is_array($constraints['where']) ? $constraints['where'] : [];
        $parameters = isset($constraints['parameters']) && is_array($constraints['parameters']) ? $constraints['parameters'] : [];

        $conditions = [];
        foreach ($where as $condition)
        {
            if (!is_scalar($condition)) continue;

            $condition = trim((string) $condition);
            if ($condition !== '') $conditions[] = $condition;
        }

        return [
            'joins' => $joins,
            'where' => $conditions,
            'parameters' => $parameters,
        ];
    }

    /**
     * Count public listings for terms.
     * @param string $taxonomy
     * @param array $terms
     * @return array
     */
    protected function public_term_counts(string $taxonomy, array $terms): array
    {
        // Term IDs
        $term_ids = [];
        foreach ($terms as $term)
        {
            if (!$term instanceof WP_Term) continue;

            $term_id = (int) $term->term_id;
            if ($term_id > 0) $term_ids[] = $term_id;
        }

        $term_ids = array_values(array_unique($term_ids));
        if (!count($term_ids)) return [];

        if ($this->taxonomy_uses_descendant_term_counts($taxonomy)) return $this->hierarchical_public_term_counts($taxonomy, $term_ids);

        if (!isset($this->public_term_counts[$taxonomy])) $this->public_term_counts[$taxonomy] = [];

        $missing = [];
        foreach ($term_ids as $term_id)
        {
            if (!array_key_exists($term_id, $this->public_term_counts[$taxonomy])) $missing[] = $term_id;
        }

        if (count($missing))
        {
            $db = new LSD_db();
            $listing_constraints = $this->public_listing_sql_constraints('p');

            foreach ($missing as $term_id) $this->public_term_counts[$taxonomy][$term_id] = 0;

            $placeholders = implode(',', array_fill(0, count($missing), '%d'));
            $constraint_where = '';
            if (count($listing_constraints['where'])) $constraint_where = ' AND ' . implode(' AND ', $listing_constraints['where']);
            $query = $db->prepare(
                "
                SELECT tt.term_id, COUNT(DISTINCT p.ID) AS public_count
                FROM `#__term_relationships` AS tr
                INNER JOIN `#__term_taxonomy` AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                INNER JOIN `#__posts` AS p ON p.ID = tr.object_id
                {$listing_constraints['joins']}
                WHERE tt.taxonomy = %s
                    AND p.post_type = %s
                    AND p.post_status = %s
                    AND p.post_password = %s
                    AND tt.term_id IN ($placeholders)
                    {$constraint_where}
                GROUP BY tt.term_id
                ",
                array_merge([$taxonomy, LSD_Base::PTYPE_LISTING, 'publish', ''], $missing, $listing_constraints['parameters'])
            );

            $rows = $db->select($query, 'loadAssocList');
            if (is_array($rows))
            {
                foreach ($rows as $row)
                {
                    $term_id = isset($row['term_id']) ? (int) $row['term_id'] : 0;
                    if ($term_id <= 0) continue;

                    $this->public_term_counts[$taxonomy][$term_id] = isset($row['public_count']) ? max(0, (int) $row['public_count']) : 0;
                }
            }
        }

        $counts = [];
        foreach ($term_ids as $term_id) $counts[$term_id] = $this->public_term_counts[$taxonomy][$term_id] ?? 0;

        return $counts;
    }

    /**
     * Check whether taxonomy counts include descendants.
     * @param string $taxonomy
     * @return bool
     */
    protected function taxonomy_uses_descendant_term_counts(string $taxonomy): bool
    {
        // Taxonomy Object
        $object = get_taxonomy($taxonomy);

        return $object instanceof WP_Taxonomy && !empty($object->hierarchical);
    }

    /**
     * Get matching public term IDs.
     * @param string $taxonomy
     * @param string $search
     * @param int $number
     * @param int $offset
     * @return array
     */
    protected function matching_public_term_ids(string $taxonomy, string $search = '', int $number = 0, int $offset = 0): array
    {
        // Count Map
        $counts = $this->hierarchical_public_term_count_map($taxonomy);
        if (!count($counts)) return [];

        $ids = [];
        $matched = 0;

        foreach ($this->matching_public_term_id_rows($taxonomy, $search) as $term_id)
        {
            if (($counts[$term_id] ?? 0) <= 0) continue;

            if ($matched++ < $offset) continue;

            $ids[] = $term_id;
            if ($number > 0 && count($ids) >= $number) break;
        }

        return $ids;
    }

    /**
     * Count matching public terms.
     * @param string $taxonomy
     * @param string $search
     * @return int
     */
    protected function matching_public_term_count(string $taxonomy, string $search = ''): int
    {
        // Count Map
        $counts = $this->hierarchical_public_term_count_map($taxonomy);
        if (!count($counts)) return 0;

        $total = 0;

        foreach ($this->matching_public_term_id_rows($taxonomy, $search) as $term_id)
        {
            if (($counts[$term_id] ?? 0) > 0) $total++;
        }

        return $total;
    }

    /**
     * Query matching public term ID rows.
     * @param string $taxonomy
     * @param string $search
     * @return array
     */
    protected function matching_public_term_id_rows(string $taxonomy, string $search = ''): array
    {
        // SQL Parts
        $conditions = ['tt.taxonomy = %s'];
        $parameters = [$taxonomy];

        if ($search !== '')
        {
            $like = '%' . addcslashes($search, '_%\\') . '%';
            $conditions[] = '(t.name LIKE %s OR t.slug LIKE %s)';
            $parameters[] = $like;
            $parameters[] = $like;
        }

        $db = new LSD_db();
        $query = $db->prepare(
            "
            SELECT t.term_id
            FROM `#__terms` AS t
            INNER JOIN `#__term_taxonomy` AS tt ON tt.term_id = t.term_id
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY t.name ASC, t.term_id ASC
            ",
            $parameters
        );

        $rows = $db->select($query, 'loadAssocList');
        if (!is_array($rows)) return [];

        $term_ids = [];
        foreach ($rows as $row)
        {
            $term_id = isset($row['term_id']) ? (int) $row['term_id'] : 0;
            if ($term_id > 0) $term_ids[] = $term_id;
        }

        return $term_ids;
    }

    /**
     * Get hierarchical public term counts.
     * @param string $taxonomy
     * @param array $term_ids
     * @return array
     */
    protected function hierarchical_public_term_counts(string $taxonomy, array $term_ids): array
    {
        // Count Map
        $count_map = $this->hierarchical_public_term_count_map($taxonomy);

        $counts = [];
        foreach ($term_ids as $term_id) $counts[$term_id] = $count_map[$term_id] ?? 0;

        return $counts;
    }

    /**
     * Get cached hierarchical public term count map.
     * @param string $taxonomy
     * @return array
     */
    protected function hierarchical_public_term_count_map(string $taxonomy): array
    {
        // Cached Counts
        if (!isset($this->hierarchical_public_term_count_cache[$taxonomy]))
        {
            $this->hierarchical_public_term_count_cache[$taxonomy] = $this->build_hierarchical_public_term_counts($taxonomy);
        }

        return $this->hierarchical_public_term_count_cache[$taxonomy];
    }

    /**
     * Build hierarchical public term counts.
     * @param string $taxonomy
     * @return array
     */
    protected function build_hierarchical_public_term_counts(string $taxonomy): array
    {
        // Term Tree
        $db = new LSD_db();
        $query = $db->prepare(
            "
            SELECT tt.term_id, tt.parent
            FROM `#__term_taxonomy` AS tt
            WHERE tt.taxonomy = %s
            ",
            [$taxonomy]
        );

        $terms = $db->select($query, 'loadAssocList');
        if (!is_array($terms) || !count($terms)) return [];

        $parents = [];
        $term_ids = [];
        $counts = [];

        foreach ($terms as $term)
        {
            $term_id = isset($term['term_id']) ? (int) $term['term_id'] : 0;
            if ($term_id <= 0) continue;

            $term_ids[] = $term_id;
            $parents[$term_id] = isset($term['parent']) ? (int) $term['parent'] : 0;
            $counts[$term_id] = 0;
        }

        if (!count($term_ids)) return [];

        $ancestor_cache = [];
        $expand_ancestors = static function (array $direct_terms) use (&$ancestor_cache, $parents): array
        {
            $expanded = [];

            foreach ($direct_terms as $term_id)
            {
                if (isset($ancestor_cache[$term_id]))
                {
                    $expanded += $ancestor_cache[$term_id];
                    continue;
                }

                $ancestors = [];
                $current = $term_id;

                while ($current > 0 && !isset($ancestors[$current]))
                {
                    $ancestors[$current] = true;
                    $current = $parents[$current] ?? 0;
                }

                $ancestor_cache[$term_id] = $ancestors;
                $expanded += $ancestors;
            }

            return $expanded;
        };

        $current_object_id = 0;
        $object_terms = [];
        $chunk_size = 5000;
        $offset = 0;
        $listing_constraints = $this->public_listing_sql_constraints('p');
        $constraint_where = count($listing_constraints['where']) ? ' AND ' . implode(' AND ', $listing_constraints['where']) : '';

        do
        {
            $query = $db->prepare(
                "
                SELECT tt.term_id, p.ID AS object_id
                FROM `#__term_relationships` AS tr
                INNER JOIN `#__term_taxonomy` AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                INNER JOIN `#__posts` AS p ON p.ID = tr.object_id
                {$listing_constraints['joins']}
                WHERE tt.taxonomy = %s
                    AND p.post_type = %s
                    AND p.post_status = %s
                    AND p.post_password = %s
                    {$constraint_where}
                ORDER BY p.ID ASC, tt.term_id ASC
                LIMIT %d OFFSET %d
                ",
                array_merge([$taxonomy, LSD_Base::PTYPE_LISTING, 'publish', ''], $listing_constraints['parameters'], [$chunk_size, $offset])
            );

            $rows = $db->select($query, 'loadAssocList');
            if (!is_array($rows) || !count($rows)) break;

            foreach ($rows as $row)
            {
                $term_id = isset($row['term_id']) ? (int) $row['term_id'] : 0;
                $object_id = isset($row['object_id']) ? (int) $row['object_id'] : 0;
                if ($term_id <= 0 || $object_id <= 0) continue;

                if ($current_object_id !== 0 && $object_id !== $current_object_id)
                {
                    foreach (array_keys($expand_ancestors(array_keys($object_terms))) as $ancestor_id)
                    {
                        if (isset($counts[$ancestor_id])) $counts[$ancestor_id]++;
                    }

                    $object_terms = [];
                }

                $current_object_id = $object_id;
                $object_terms[$term_id] = true;
            }

            $offset += $chunk_size;
        }
        while (count($rows) === $chunk_size);

        if ($current_object_id !== 0 && count($object_terms))
        {
            foreach (array_keys($expand_ancestors(array_keys($object_terms))) as $ancestor_id)
            {
                if (isset($counts[$ancestor_id])) $counts[$ancestor_id]++;
            }
        }

        return $counts;
    }
}
