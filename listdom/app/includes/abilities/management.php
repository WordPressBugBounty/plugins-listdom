<?php

/** Thin native adapters for authenticated listing and taxonomy workflows. */
class LSD_Abilities_Management
{
    public static function pagination(): array
    {
        return ['page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000], 'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100]];
    }

    public function register(): void
    {
        $s = 'LSD_Abilities_Schema';
        $id = ['type' => 'integer', 'minimum' => 1];
        $string = ['type' => 'string'];
        $page = ['type' => 'integer'];
        $more = ['type' => 'boolean'];
        $field = $s::object([
            'key' => $string, 'owner' => $string, 'module' => $string, 'input_type' => $string,
            'schema' => ['type' => 'object', 'additionalProperties' => true],
            'default' => ['type' => ['string', 'integer', 'number', 'boolean', 'array', 'object', 'null']],
            'required' => $more, 'available' => $more, 'readable' => $more, 'writable' => $more, 'clearable' => $more, 'nullable' => $more,
            'max_text_length' => ['type' => 'integer', 'minimum' => 0],
            'categories' => ['type' => 'object', 'additionalProperties' => true], 'applicable' => $more,
        ], ['key', 'schema', 'available', 'readable', 'writable', 'clearable']);
        $catalog = new LSD_Listings_Catalog();
        $properties = $catalog->input_properties();
        $values = [];
        foreach ($properties as $key => $schema)
            if (!in_array($key, ['request_id', 'primary_category', 'gallery_add', 'gallery_remove'], true)) $values[$key] = ['type' => ['string', 'integer', 'number', 'boolean', 'array', 'object', 'null']];
        $result = self::result_schema();
        $term = $s::object(['id' => $id, 'taxonomy' => $string, 'name' => $string, 'slug' => $string, 'description' => $string, 'parent' => ['type' => 'integer']], ['id', 'taxonomy', 'name', 'slug', 'description', 'parent']);
        $taxonomy = $s::object(['slug' => $string, 'label' => $string, 'hierarchical' => $more, 'can_create' => $more, 'can_edit' => $more, 'can_assign' => $more, 'metadata' => $s::list($string)], ['slug', 'label', 'hierarchical', 'can_create', 'can_edit', 'can_assign', 'metadata']);
        $read = $s::object(['id' => $id, 'status' => $string, 'values' => $s::object($values), 'edit_url' => $string], ['id', 'status', 'values', 'edit_url']);
        $this->ability('get-listing-schema', 'Discover writable listing fields', $s::object(self::pagination() + ['category_id' => $id, 'extensions' => $properties['extensions']]),
            $s::object(['fields' => $s::list($field), 'page' => $page, 'has_more' => $more], ['fields', 'page', 'has_more']), 'can_create', 'schema', true);
        $this->ability('get-managed-listing', 'Read an authorized listing for review', $s::object(['id' => $id], ['id']), $read, 'can_edit', 'read', true);
        $this->ability('find-managed-listings', 'Find listings you may edit', $s::object(self::pagination() + ['search' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100]], ['search']),
            $s::object(['listings' => $s::list($s::object(['id' => $id, 'title' => $string, 'status' => $string], ['id', 'title', 'status'])), 'page' => $page, 'has_more' => $more], ['listings', 'page', 'has_more']), 'can_create', 'find', true);
        $this->ability('create-draft-listing', 'Create a complete listing draft safely', $catalog->creation_schema(false, ['title', 'request_id']), $result, 'can_create', 'create', false);
        $this->ability('validate-listing', 'Validate listing input without saving', $s::object(['id' => $id] + $properties),
            $s::object(['valid' => $more, 'errors' => $s::list($s::object(['field' => $string, 'message' => $string], ['field', 'message'])), 'warnings' => $s::list($string)], ['valid', 'errors', 'warnings']), 'can_validate', 'validate', true);
        $this->ability('transition-listing-status', 'Request an authorized listing status transition', $s::object(['id' => $id, 'status' => ['type' => 'string', 'enum' => ['draft', 'pending', 'private', 'publish']]], ['id', 'status']), $result, 'can_edit', 'transition', false, true);
        $this->ability('list-managed-taxonomies', 'Discover authorized listing taxonomies', $s::object(self::pagination()),
            $s::object(['taxonomies' => $s::list($taxonomy), 'page' => $page, 'has_more' => $more], ['taxonomies', 'page', 'has_more']), 'can_discover_terms', 'taxonomies', true);
        $this->ability('list-managed-terms', 'Discover management terms including empty terms', $s::object(self::pagination() + ['taxonomy' => $string, 'parent' => ['type' => 'integer', 'minimum' => 0], 'search' => ['type' => 'string', 'maxLength' => 100]], ['taxonomy']),
            $s::object(['terms' => $s::list($term), 'page' => $page, 'has_more' => $more], ['terms', 'page', 'has_more']), 'can_read_terms', 'terms', true);
        $metadata = [];
        foreach ((new LSD_Listings_Taxonomies())->available() as $slug => $object) $metadata += (new LSD_Listings_Taxonomies())->metadata($slug);
        $term_input = ['taxonomy' => $string, 'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 200], 'slug' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 200], 'description' => ['type' => 'string', 'maxLength' => 10000], 'parent' => ['type' => 'integer', 'minimum' => 0], 'metadata' => $s::object($metadata)];
        $term_result = $s::object(['term' => $term, 'outcome' => ['type' => 'string', 'enum' => ['created', 'updated', 'existing']]], ['term', 'outcome']);
        $this->ability('create-term', 'Explicitly create an authorized listing term', $s::object($term_input, ['taxonomy', 'name']), $term_result, 'can_create_term', 'create_term', false);
        $this->ability('update-term', 'Update only supplied listing term fields', $s::object(['id' => $id] + $term_input, ['id', 'taxonomy']), $term_result, 'can_update_term', 'update_term', false, true);
    }

    public static function result_schema(): array
    {
        return LSD_Abilities_Schema::object(['id' => ['type' => 'integer', 'minimum' => 1], 'status' => ['type' => 'string'], 'message' => ['type' => 'string'],
            'warnings' => LSD_Abilities_Schema::list(['type' => 'string']), 'edit_url' => ['type' => 'string']], ['id', 'status', 'message', 'warnings', 'edit_url']);
    }

    private function ability(string $name, string $description, array $input, array $output, string $permission, string $execute, bool $readonly, bool $destructive = false): void
    {
        LSD_Abilities_Schema::register('listdom/' . $name, __($description, 'listdom'), __($description, 'listdom'), $input, $output, [$this, $permission], [$this, $execute], $readonly, $destructive, true, [], in_array($name, ['create-draft-listing', 'create-term'], true));
    }

    public function can_create(): bool { return (new LSD_Abilities_Listings())->can_create(); }
    public function can_edit($input): bool { return (new LSD_Abilities_Listings())->can_update($input); }
    public function can_validate($input): bool { $input = LSD_Abilities_Schema::input($input); return isset($input['id']) ? $this->can_edit($input) : $this->can_create(); }
    public function can_read_terms($input): bool { $input = LSD_Abilities_Schema::input($input); return (new LSD_Listings_Taxonomies())->permission($input['taxonomy'] ?? '', 'read'); }
    public function can_create_term($input): bool { $input = LSD_Abilities_Schema::input($input); return (new LSD_Listings_Taxonomies())->permission($input['taxonomy'] ?? '', 'create'); }
    public function can_update_term($input): bool
    {
        $input = LSD_Abilities_Schema::input($input);
        return (new LSD_Listings_Taxonomies())->permission($input['taxonomy'] ?? '', 'edit') && current_user_can('edit_term', (int) ($input['id'] ?? 0));
    }
    public function can_discover_terms(): bool
    {
        $service = new LSD_Listings_Taxonomies();
        foreach ($service->available() as $slug => $taxonomy) if ($service->permission($slug, 'read')) return true;
        return false;
    }

    public function schema($input) { return $this->bounded((new LSD_Listings_Catalog())->describe(LSD_Abilities_Schema::input($input))); }
    public function read($input) { $input = LSD_Abilities_Schema::input($input); return $this->bounded((new LSD_Listings_Workflow())->read((int) $input['id']), 262144); }
    public function create($input) { return (new LSD_Listings_Service())->create_managed(LSD_Abilities_Schema::input($input), true); }
    public function validate($input) { return (new LSD_Listings_Workflow())->validate(LSD_Abilities_Schema::input($input)); }
    public function transition($input) { return (new LSD_Listings_Workflow())->transition(LSD_Abilities_Schema::input($input)); }
    public function taxonomies($input) { return $this->bounded((new LSD_Listings_Taxonomies())->describe(LSD_Abilities_Schema::input($input))); }
    public function terms($input) { return $this->bounded((new LSD_Listings_Taxonomies())->terms(LSD_Abilities_Schema::input($input))); }
    public function create_term($input) { return (new LSD_Listings_Taxonomies())->write(LSD_Abilities_Schema::input($input)); }
    public function update_term($input) { return (new LSD_Listings_Taxonomies())->write(LSD_Abilities_Schema::input($input), true); }

    private function bounded($result, int $limit = 65536)
    {
        if (!is_wp_error($result) && strlen(wp_json_encode($result)) > $limit)
            return new WP_Error('listdom_response_limit', __('This response exceeds the management payload limit. Request a smaller page or reduce oversized listing content.', 'listdom'));
        return $result;
    }

    public function find($input)
    {
        $input = LSD_Abilities_Schema::input($input);
        $page = $input['page'] ?? 1;
        $size = $input['per_page'] ?? 20;
        $args = ['post_type' => LSD_Base::PTYPE_LISTING, 'post_status' => array_values(get_post_stati(['internal' => false])), 's' => $input['search'],
            'posts_per_page' => $size, 'paged' => $page, 'orderby' => 'ID', 'order' => 'ASC', 'perm' => 'editable'];
        if (!current_user_can(get_post_type_object(LSD_Base::PTYPE_LISTING)->cap->edit_others_posts)) $args['author'] = get_current_user_id();
        $query = new WP_Query($args);
        $items = [];
        foreach ($query->posts as $post)
            if (current_user_can('edit_post', $post->ID)) $items[] = ['id' => $post->ID, 'title' => $post->post_title, 'status' => $post->post_status];
        return $this->bounded(['listings' => $items, 'page' => $page, 'has_more' => $query->max_num_pages > $page]);
    }
}
