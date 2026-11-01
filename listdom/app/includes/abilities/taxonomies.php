<?php

class LSD_Abilities_Taxonomies
{
    /** Register public listing taxonomy and term lookups. */
    public function register(): void
    {
        // Share the taxonomy and term schemas across both read abilities.
        $taxonomy = LSD_Abilities_Schema::object([
            'slug' => ['type' => 'string'],
            'label' => ['type' => 'string'],
            'hierarchical' => ['type' => 'boolean'],
        ], ['slug', 'label', 'hierarchical']);
        $empty = LSD_Abilities_Schema::object([]) + ['default' => (object) []];
        $term = LSD_Abilities_Schema::object([
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
            'slug' => ['type' => 'string'],
            'parent' => ['type' => 'integer'],
            'count' => ['type' => 'integer'],
            'url' => ['type' => 'string'],
        ], ['id', 'name', 'slug', 'parent', 'count', 'url']);

        LSD_Abilities_Schema::register(
            'listdom/list-taxonomies',
            __('List listing taxonomies', 'listdom'),
            __('List public taxonomies currently registered for Listdom listings, including active add-on taxonomies.', 'listdom'),
            $empty,
            LSD_Abilities_Schema::object(['taxonomies' => LSD_Abilities_Schema::list($taxonomy)], ['taxonomies']),
            '__return_true',
            [$this, 'taxonomies'],
            true
        );

        LSD_Abilities_Schema::register(
            'listdom/list-terms',
            __('List listing terms', 'listdom'),
            __('List public terms in a registered Listdom listing taxonomy, with optional parent and search filters.', 'listdom'),
            LSD_Abilities_Schema::object([
                'taxonomy' => ['type' => 'string', 'minLength' => 1],
                'parent' => ['type' => 'integer', 'minimum' => 0],
                'search' => ['type' => 'string', 'maxLength' => 100],
                'hide_empty' => ['type' => 'boolean'],
                'page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000],
                'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
            ], ['taxonomy']),
            LSD_Abilities_Schema::object(['terms' => LSD_Abilities_Schema::list($term), 'page' => ['type' => 'integer']], ['terms', 'page']),
            '__return_true',
            [$this, 'terms'],
            true
        );
    }

    /** Find public taxonomies attached to listings. */
    private function available(): array
    {
        $available = [];
        foreach (get_object_taxonomies(LSD_Base::PTYPE_LISTING, 'objects') as $taxonomy)
        {
            if (!$taxonomy->public) continue;
            $available[$taxonomy->name] = $taxonomy;
        }
        return $available;
    }

    /** List the currently available public taxonomies. */
    public function taxonomies(): array
    {
        $items = [];
        foreach ($this->available() as $taxonomy)
        {
            $items[] = [
                'slug' => $taxonomy->name,
                'label' => $taxonomy->labels->name,
                'hierarchical' => (bool) $taxonomy->hierarchical,
            ];
        }
        return ['taxonomies' => $items];
    }

    /**
     * List terms in a public listing taxonomy.
     *
     * @return array|WP_Error
     */
    public function terms($input)
    {
        $input = LSD_Abilities_Schema::input($input);
        $slug = $input['taxonomy'] ?? '';
        $available = $this->available();

        // Only expose public taxonomies attached to listings.
        if (!isset($available[$slug])) return new WP_Error('listdom_invalid_taxonomy', __('The requested listing taxonomy is unavailable.', 'listdom'));

        $visibility = LSD_AI_Visibility::instance();
        if (!$visibility->settings_service()->public_feed_available())
            return new WP_Error('listdom_feed_unavailable', __('Public listing discovery is disabled.', 'listdom'));

        $page = (int) ($input['page'] ?? 1);
        $per_page = (int) ($input['per_page'] ?? 20);
        $api = $visibility->public_api_service();
        $args = [
            'search' => sanitize_text_field($input['search'] ?? ''),
            'hide_empty' => (bool) ($input['hide_empty'] ?? true),
            'number' => $per_page,
            'offset' => ($page - 1) * $per_page,
        ];
        if (isset($input['parent'])) $args['parent'] = (int) $input['parent'];

        // Apply the parent filter before pagination in the public term query.
        $terms = $api->public_terms_query($slug, $args);

        $counts = $api->public_term_counts($slug, $terms);
        $items = [];

        foreach ($terms as $term)
        {
            $url = get_term_link($term);
            $items[] = [
                'id' => (int) $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'parent' => (int) $term->parent,
                'count' => $counts[$term->term_id] ?? 0,
                'url' => is_wp_error($url) ? '' : $url,
            ];
        }
        return ['terms' => $items, 'page' => $page];
    }
}
