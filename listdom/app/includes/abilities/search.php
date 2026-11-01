<?php

class LSD_Abilities_Search
{
    /** Register search schema discovery and listing search. */
    public function register(): void
    {
        // Prepare the public result and search input schemas.
        $object = [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'url' => ['type' => 'string'],
            ],
            'required' => ['id', 'title', 'url'],
            'additionalProperties' => true, // Public payload fields vary with configured elements and add-ons.
        ];

        $empty = LSD_Abilities_Schema::object([]) + ['default' => (object) []];

        $input = self::input_schema();

        $pagination = LSD_Abilities_Schema::object([
            'page' => ['type' => 'integer'],
            'per_page' => ['type' => 'integer'],
            'total_pages' => ['type' => 'integer'],
            'has_more' => ['type' => 'boolean'],
            'seed' => ['type' => 'integer'],
        ], ['page', 'per_page', 'has_more', 'seed']);

        // Expose schema discovery before callers submit module filters.
        LSD_Abilities_Schema::register(
            'listdom/get-search-schema',
            __('Get Listdom search schema', 'listdom'),
            __('Discover published Listdom search modules and the exact parameter names accepted by each configured filter.', 'listdom'),
            $empty,
            LSD_Abilities_Schema::object(['modules' => LSD_Abilities_Schema::list(LSD_Abilities_Schema::object([
                'id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'filters' => LSD_Abilities_Schema::list(LSD_Abilities_Schema::object([
                    'label' => ['type' => 'string'],
                    'field_type' => ['type' => 'string'],
                    'operator' => ['type' => 'string'],
                    'parameters' => LSD_Abilities_Schema::list(LSD_Abilities_Schema::object([
                        'name' => ['type' => 'string'],
                        'type' => ['type' => 'string'],
                        'array' => ['type' => 'boolean'],
                        'required' => ['type' => 'boolean'],
                        'role' => ['type' => 'string'],
                        'source' => ['type' => 'string'],
                    ], ['name', 'type', 'array', 'required', 'role', 'source'])),
                ], ['label', 'field_type', 'operator', 'parameters']), null),
            ], ['id', 'title', 'filters']))], ['modules']),
            '__return_true',
            [$this, 'schema'],
            true
        );

        LSD_Abilities_Schema::register(
            'listdom/search-listings',
            __('Search Listdom listings', 'listdom'),
            __('Search visible published listings through the Listdom search engine. Call listdom/get-search-schema before supplying module filter parameters.', 'listdom'),
            $input,
            LSD_Abilities_Schema::object(['items' => LSD_Abilities_Schema::list($object), 'pagination' => $pagination], ['items', 'pagination']),
            '__return_true',
            [$this, 'search'],
            true
        );
    }

    /** Shared existing parameter contract for search and administrator diagnostics. */
    public static function input_schema(): array
    {
        $parameter = LSD_Abilities_Schema::object([
            'name' => ['type' => 'string', 'pattern' => '^sf-[a-zA-Z0-9_\[\]-]+$'],
            'value' => ['type' => ['string', 'integer', 'number', 'boolean', 'array'], 'items' => ['type' => ['string', 'integer', 'number']]],
        ], ['name', 'value']);

        $input = LSD_Abilities_Schema::object([
            'keyword' => ['type' => 'string', 'maxLength' => 200],
            'search_module' => ['type' => 'integer', 'minimum' => 1],
            'parameters' => LSD_Abilities_Schema::list($parameter, 60),
            'page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000],
            'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
            'orderby' => ['type' => 'string', 'maxLength' => 50],
            'order' => ['type' => 'string', 'enum' => ['ASC', 'DESC']],
            'seed' => ['type' => 'integer', 'minimum' => 10000, 'maximum' => 99999],
        ]) + ['default' => (object) []];

        return $input;
    }

    /** Describe the filters on published search modules. */
    public function schema(): array
    {
        $modules = [];
        $posts = get_posts(['post_type' => LSD_Base::PTYPE_SEARCH, 'post_status' => 'publish', 'numberposts' => 100]);
        foreach ($posts as $post)
        {
            $module = LSD_API_Resources_SearchModule::get($post->ID);
            $modules[] = [
                'id' => (int) $post->ID,
                'title' => get_the_title($post),
                'filters' => $this->filters($module),
            ];
        }
        return ['modules' => $modules];
    }

    /** Validate native search parameters without fetching or serializing listings. */
    public function parameters(array $input)
    {
        $vars = [
            'page' => (int) ($input['page'] ?? 1),
            'limit' => (int) ($input['per_page'] ?? 20),
            'seed' => (int) ($input['seed'] ?? wp_rand(10000, 99999)),
        ];
        $parameters = $input['parameters'] ?? [];

        // Listdom's range parser joins bounds in request order. Keep minimum
        // values first even when a client submits the parameters in reverse.
        $minimums = [];
        $remaining = [];
        foreach ($parameters as $parameter)
        {
            if (preg_match('/(?:-|_)min$/', (string) ($parameter['name'] ?? ''))) $minimums[] = $parameter;
            else $remaining[] = $parameter;
        }

        $parameters = array_merge($minimums, $remaining);

        // Apply simple query fields before validating module-specific filters.
        if (!empty($input['keyword'])) $vars['sf-s'] = sanitize_text_field($input['keyword']);
        if (!empty($input['orderby'])) $vars['orderby'] = sanitize_key($input['orderby']);
        if (!empty($input['order'])) $vars['order'] = $input['order'];

        if ($parameters)
        {
            $id = (int) ($input['search_module'] ?? 0);
            $module = get_post($id);
            if (!$module instanceof WP_Post || $module->post_type !== LSD_Base::PTYPE_SEARCH || $module->post_status !== 'publish')
                return new WP_Error('listdom_invalid_search_module', __('Select a published Listdom search module before supplying filters.', 'listdom'));

            $filters = $this->filters(LSD_API_Resources_SearchModule::get($id));
            $allowed = [];
            foreach ($filters as $filter)
            {
                foreach ($filter['parameters'] as $parameter) $allowed[$parameter['name']] = $parameter;
            }

            $submitted = [];
            foreach ($parameters as $parameter)
            {
                $name = $parameter['name'] ?? '';
                if (!isset($allowed[$name])) return new WP_Error('listdom_invalid_search_filter', __('The search module does not support a supplied filter.', 'listdom'));
                if (!array_key_exists('value', $parameter)) return new WP_Error('listdom_invalid_search_filter', __('A search filter is missing its value.', 'listdom'));
                $value = $parameter['value'];

                if (is_array($value) !== $allowed[$name]['array']) return new WP_Error('listdom_invalid_search_filter', __('A search filter has the wrong value shape.', 'listdom'));

                foreach (is_array($value) ? $value : [$value] as $item)
                {
                    $type = $allowed[$name]['type'];
                    $valid = false;
                    foreach (explode('|', $type) as $accepted)
                    {
                        if (($accepted === 'string' && is_string($item))
                            || ($accepted === 'integer' && !is_bool($item) && filter_var($item, FILTER_VALIDATE_INT) !== false)
                            || ($accepted === 'number' && !is_bool($item) && is_numeric($item))
                            || ($accepted === 'boolean' && in_array($item, [true, false, 0, 1, '0', '1'], true))) $valid = true;
                    }
                    if (!$valid)
                        return new WP_Error('listdom_invalid_search_filter', __('A search filter has an invalid value.', 'listdom'));
                }

                $submitted[$name] = true;
                // PHP turns HTTP parameter names ending in [] into array values
                // under the name without brackets before Listdom reads $_GET.
                $vars[preg_replace('/\[\]$/', '', $name)] = $value;
            }

            // A submitted range or radius must include its companion parameters.
            foreach ($filters as $filter)
            {
                $names = array_column($filter['parameters'], 'name');
                if (!array_intersect($names, array_keys($submitted))) continue;
                foreach ($filter['parameters'] as $parameter)
                {
                    if ($parameter['required'] && !isset($submitted[$parameter['name']]))
                        return new WP_Error('listdom_invalid_search_filter', __('A search filter is missing a required parameter.', 'listdom'));
                }
            }
        }

        return $vars;
    }

    /** Extract supported parameters from a search module. */
    private function filters(array $module): array
    {
        $filters = [];
        foreach ($module['data']['fields'] ?? [] as $row)
        {
            foreach ($row['filters'] ?? [] as $field)
            {
                $request = $field['request'] ?? [];
                $parameters = [];
                foreach ($request['params'] ?? [] as $param)
                {
                    if (empty($param['name'])) continue;
                    $parameters[] = [
                        'name' => (string) $param['name'],
                        'type' => (string) ($param['type'] ?? 'string'),
                        'array' => !empty($param['array']),
                        'required' => !empty($param['required']),
                        'role' => (string) ($param['role'] ?? ''),
                        'source' => (string) ($param['source'] ?? ''),
                    ];
                }
                if (!$parameters) continue;
                $filters[] = [
                    'label' => (string) ($field['label'] ?? ''),
                    'field_type' => (string) ($request['field_type'] ?? ''),
                    'operator' => (string) ($request['operator'] ?? ''),
                    'parameters' => $parameters,
                ];
            }
        }
        return $filters;
    }

    /**
     * Search listings using validated module filters and public payloads.
     *
     * @return array|WP_Error
     */
    public function search($input = null)
    {
        $input = LSD_Abilities_Schema::input($input);
        $visibility = LSD_AI_Visibility::instance();
        if (!$visibility->settings_service()->public_feed_available()) return new WP_Error('listdom_feed_unavailable', __('Public listing discovery is disabled.', 'listdom'));

        $vars = $this->parameters($input);
        if (is_wp_error($vars)) return $vars;

        // Resolve every result through the public payload service.
        $payload = $visibility->payload_service();
        if (has_filter('lsd_ai_visibility_is_public_listing') !== false)
        {
            // A listing access filter can reject query matches, so page after checking access.
            $result = $this->visible_results($vars, $payload);
            if (is_wp_error($result)) return $result;
            [$items, $total_pages, $has_more] = $result;
        }
        else
        {
            $skin = (new LSD_Search_Service())->run($vars, true, true);
            $items = [];
            foreach ($skin->listings as $id)
            {
                $item = $payload->public_listing_payload((int) $id);
                if ($item) $items[] = $item;
            }

            $total_pages = (int) ceil((int) $skin->found_listings / $vars['limit']);
            $has_more = $vars['page'] < $total_pages;
        }

        $pagination = [
            'page' => $vars['page'],
            'per_page' => $vars['limit'],
            'has_more' => $has_more,
            'seed' => $vars['seed'],
        ];

        if ($total_pages !== null) $pagination['total_pages'] = $total_pages;

        return [
            'items' => $items,
            'pagination' => $pagination,
        ];
    }

    /**
     * Page visible listings within a fixed work budget, or fail without partial results.
     *
     * @return array|WP_Error
     */
    private function visible_results(array $vars, LSD_AI_Visibility_Payload $payload)
    {
        $batch = $vars;
        $batch['page'] = 1;
        $batch['limit'] = 500;
        $offset = ($vars['page'] - 1) * $vars['limit'];
        $end = $offset + $vars['limit'];
        $visible = 0;
        $items = [];
        $has_more = false;
        $budget = 2000;
        $scanned = 0;

        // Stop after one extra visible listing; an exact total needs a full scan.
        do
        {
            // Four batches at most, regardless of the requested visible page.
            // Returning an error keeps incomplete scans out of pagination totals.
            if ($scanned >= $budget)
                return new WP_Error('listdom_search_limit_exceeded', __('This visibility-filtered search exceeds the listing scan limit. Narrow the search with a keyword or search module filters.', 'listdom'));

            $skin = (new LSD_Search_Service())->run($batch, true, true);
            $scanned += $batch['limit'];
            foreach ($skin->listings as $id)
            {
                $id = (int) $id;
                if (!$payload->is_public_listing(get_post($id))) continue;

                if ($visible >= $offset && $visible < $end)
                {
                    $item = $payload->public_listing_payload($id);
                    if ($item) $items[] = $item;
                }
                $visible++;
                if ($visible > $end)
                {
                    $has_more = true;
                    break;
                }
            }
            if ($has_more) break;
            $pages = (int) ceil((int) $skin->found_listings / $batch['limit']);
            $batch['page']++;
        }
        while ($batch['page'] <= $pages);

        return [$items, $has_more ? null : (int) ceil($visible / $vars['limit']), $has_more];
    }
}
