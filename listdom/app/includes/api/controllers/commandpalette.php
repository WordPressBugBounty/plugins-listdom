<?php

class LSD_API_Controllers_CommandPalette
{
    private const RESULT_LIMIT = 10;
    private const QUERY_LIMIT = 50;

    public function init(): void
    {
        add_action('rest_api_init', [$this, 'register']);
    }

    public function register(): void
    {
        register_rest_route('listdom/v1', 'command-search', [
            'methods' => 'GET',
            'callback' => [$this, 'search'],
            'permission_callback' => [$this, 'permission'],
            'args' => [
                'search' => [
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    public function permission(): bool
    {
        return is_user_logged_in();
    }

    public function search(WP_REST_Request $request): WP_REST_Response
    {
        $search = trim((string) $request->get_param('search'));
        if ($search === '') return new WP_REST_Response(['items' => []]);

        $posts = $this->posts($search);
        $terms = $this->terms($search);
        $items = [];

        // Alternate record kinds so a common listing title cannot hide matching terms.
        while (count($items) < self::RESULT_LIMIT && ($posts || $terms))
        {
            if ($posts) $items[] = array_shift($posts);
            if (count($items) < self::RESULT_LIMIT && $terms) $items[] = array_shift($terms);
        }

        return new WP_REST_Response(['items' => $items]);
    }

    private function posts(string $search): array
    {
        $statuses = array_values(array_diff(get_post_stati(), ['trash', 'auto-draft', 'inherit']));
        if (!$statuses) return [];

        $groups = ['own' => [], 'all' => []];
        foreach (get_post_types(['show_ui' => true], 'objects') as $name => $type)
        {
            if (strpos($name, 'listdom-') !== 0 || !current_user_can($type->cap->edit_posts)) continue;

            $editable_statuses = $statuses;
            if ($type->map_meta_cap && !current_user_can($type->cap->edit_published_posts))
            {
                // Published and scheduled posts would consume the query limit but fail edit_post.
                $editable_statuses = array_values(array_diff($editable_statuses, ['publish', 'future']));
            }

            if (!$editable_statuses) continue;

            $scope = current_user_can($type->cap->edit_others_posts) ? 'all' : 'own';
            if ($scope === 'all' && $type->map_meta_cap && !current_user_can($type->cap->edit_private_posts))
            {
                // Private posts by others are not editable; the user's own private posts still are.
                $others_statuses = array_values(array_diff($editable_statuses, ['private']));
                if ($others_statuses) $groups['all'][implode(',', $others_statuses)][] = $name;
                if (in_array('private', $editable_statuses, true)) $groups['own']['private'][] = $name;
            }
            else $groups[$scope][implode(',', $editable_statuses)][] = $name;
        }

        $query_args = [
            's' => $search,
            'search_columns' => ['post_title'],
            'posts_per_page' => self::QUERY_LIMIT,
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ];

        $items = [];
        foreach ($groups as $scope => $status_groups)
        {
            foreach ($status_groups as $status_list => $types)
            {
                $query_args['post_type'] = $types;
                $query_args['post_status'] = explode(',', $status_list);

                // Scope the bounded query before filtering individual records by edit permission.
                if ($scope === 'own') $query_args['author'] = get_current_user_id();
                else unset($query_args['author']);

                $query = new WP_Query($query_args);
                foreach ($query->posts as $post)
                {
                    if (!current_user_can('edit_post', $post->ID)) continue;

                    $url = get_edit_post_link($post->ID, 'raw');
                    if (!$url) continue;

                    $type = get_post_type_object($post->post_type);
                    $items[] = [
                        'kind' => 'post',
                        'id' => (int) $post->ID,
                        'title' => wp_strip_all_tags($post->post_title),
                        'type_label' => $type->labels->singular_name,
                        'url' => $url,
                    ];

                    if (count($items) >= self::RESULT_LIMIT) break 3;
                }
            }
        }

        return $items;
    }

    private function terms(string $search): array
    {
        $taxonomies = [];
        foreach (get_taxonomies(['show_ui' => true], 'objects') as $name => $taxonomy)
        {
            if (strpos($name, 'listdom-') !== 0 || !current_user_can($taxonomy->cap->edit_terms)) continue;
            $taxonomies[] = $name;
        }

        if (!$taxonomies) return [];

        $terms = get_terms([
            'taxonomy' => $taxonomies,
            'name__like' => $search,
            'hide_empty' => false,
            'number' => self::QUERY_LIMIT,
            'orderby' => 'name',
        ]);

        if (is_wp_error($terms)) return [];

        $items = [];
        foreach ($terms as $term)
        {
            if (!current_user_can('edit_term', $term->term_id)) continue;

            $url = get_edit_term_link($term->term_id, $term->taxonomy);
            if (!$url) continue;

            $taxonomy = get_taxonomy($term->taxonomy);
            $items[] = [
                'kind' => 'term',
                'id' => (int) $term->term_id,
                'title' => wp_strip_all_tags($term->name),
                'type_label' => $taxonomy->labels->singular_name,
                'url' => $url,
            ];

            if (count($items) >= self::RESULT_LIMIT) break;
        }

        return $items;
    }
}
