<?php

class LSD_IX extends LSD_Base
{
    protected $db;

    protected static $last_result = null;

    public function __construct()
    {
        // DB Library
        $this->db = new LSD_db();
    }

    public static function import_types(): array
    {
        $types = [
            'listings' => ['label' => esc_html__('Listings', 'listdom')],
            'categories' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_CATEGORY, 'plural')), 'taxonomy' => LSD_Base::TAX_CATEGORY],
            'locations' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_LOCATION, 'plural')), 'taxonomy' => LSD_Base::TAX_LOCATION],
            'tags' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_TAG, 'plural')), 'taxonomy' => LSD_Base::TAX_TAG],
            'features' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_FEATURE, 'plural')), 'taxonomy' => LSD_Base::TAX_FEATURE],
            'labels' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_LABEL, 'plural')), 'taxonomy' => LSD_Base::TAX_LABEL],
            'attributes' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_ATTRIBUTE, 'plural')), 'taxonomy' => LSD_Base::TAX_ATTRIBUTE],
        ];

        /**
         * Allow third parties to extend the available JSON import / export types.
         */
        return apply_filters('lsd_ix_json_types', $types);
    }

    public static function import_type_labels(): array
    {
        $labels = [];
        foreach (self::import_types() as $key => $type) $labels[$key] = $type['label'];

        return $labels;
    }

    public static function normalize_import_type($type): string
    {
        $type = is_string($type) ? strtolower(sanitize_key($type)) : '';
        $types = self::import_types();

        return isset($types[$type]) ? $type : 'listings';
    }

    public static function import_type_label($type): string
    {
        $types = self::import_types();
        $type = self::normalize_import_type($type);

        return $types[$type]['label'] ?? '';
    }

    protected function filename_type(string $type): string
    {
        $type = self::normalize_import_type($type);

        return $type === 'attributes' ? 'custom-fields' : $type;
    }

    public static function taxonomy_for_type($type): ?string
    {
        $types = self::import_types();
        $type = self::normalize_import_type($type);

        return $types[$type]['taxonomy'] ?? null;
    }

    public static function set_last_result($result): void
    {
        self::$last_result = $result;
    }

    /**
     * @return WP_Error|array|null
     */
    public static function get_last_result()
    {
        return self::$last_result;
    }

    protected function get_data(string $type = 'listings'): array
    {
        $type = self::normalize_import_type($type);
        if ($taxonomy = self::taxonomy_for_type($type)) return $this->taxonomy_data($taxonomy);

        return $this->listings_data();
    }

    protected function listings_data(): array
    {
        $listings = get_posts([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => ['publish', 'pending', 'draft', 'future', 'private'],
            'posts_per_page' => '-1',
        ]);

        $data = [];
        foreach ($listings as $listing)
        {
            // Force to Array
            $listing = (array) $listing;

            // ID
            $listing_id = $listing['ID'];

            // Remove Useless Keys
            foreach ([
                 'ID', 'comment_count', 'comment_status', 'filter', 'guid',
                 'menu_order', 'ping_status', 'pinged', 'to_ping', 'post_content_filtered',
                 'post_parent', 'post_mime_type',
             ] as $key) if (isset($listing[$key])) unset($listing[$key]);

            $metas = $this->get_post_meta($listing_id);

            $listing['excerpt'] = $listing['post_excerpt'] ?? '';

            // Remove Useless Keys
            foreach ($metas as $key => $value)
            {
                if (in_array($key, [
                    '_edit_last', '_edit_lock', '_thumbnail_id',
                    'lsd_attributes', 'lsd_gallery', 'lsd_faqs',
                ]) || strpos($key, 'lsd_attribute_') !== false) unset($metas[$key]);
            }

            // Meta Values
            $listing['meta'] = $metas;

            // Taxonomies
            $listing['taxonomies'] = $this->get_taxonomies($listing_id);

            // Gallery
            $listing['gallery'] = $this->get_gallery($listing_id);

            // Featured Image
            $listing['image'] = get_the_post_thumbnail_url($listing_id, 'full');

            // Attributes
            $listing['attributes'] = $this->get_attributes($listing_id);

            // FAQs
            $faqs = get_post_meta($listing_id, 'lsd_faqs', true);
            $listing['faqs'] = is_array($faqs) ? $faqs : [];

            $data[] = $listing;
        }

        return $data;
    }

    protected function normalize_faqs($faqs): array
    {
        if (is_string($faqs))
        {
            $decoded = json_decode($faqs, true);
            if (json_last_error() === JSON_ERROR_NONE) $faqs = $decoded;
        }

        if (!is_array($faqs)) return [];

        if (array_key_exists('question', $faqs) || array_key_exists('answer', $faqs))
        {
            $faqs = [$faqs];
        }

        $normalized = [];
        foreach ($faqs as $faq)
        {
            if (!is_array($faq)) continue;
            if (!array_key_exists('question', $faq) && !array_key_exists('answer', $faq)) continue;

            $normalized[] = $faq;
        }

        return $normalized;
    }

    protected function taxonomy_data(string $taxonomy): array
    {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);

        if (!is_array($terms) || !count($terms)) return [];

        $data = [];
        foreach ($terms as $term)
        {
            $term = (array) $term;

            $term_id = (int) ($term['term_id'] ?? 0);
            $parent = (int) ($term['parent'] ?? 0);

            foreach ([
                 'term_id', 'count', 'filter',
                 'term_taxonomy_id', 'parent',
             ] as $key) if (isset($term[$key])) unset($term[$key]);

            $metas = $this->get_term_meta($term_id);
            if (isset($metas['lsd_image']))
            {
                $term['image'] = wp_get_attachment_url($metas['lsd_image']);
                unset($metas['lsd_image']);
            }

            $term['meta'] = $metas;

            if ($parent)
            {
                $parent_term = get_term($parent, $taxonomy);
                if ($parent_term && !is_wp_error($parent_term))
                {
                    $term['parent_slug'] = $parent_term->slug;
                }
            }

            $data[] = $term;
        }

        return $data;
    }

    protected function taxonomy_data_chunk(string $taxonomy, int $offset, int $limit): array
    {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'offset' => $offset,
            'number' => $limit,
        ]);

        if (!is_array($terms) || !count($terms)) return [];

        $data = [];
        foreach ($terms as $term)
        {
            $term = (array) $term;

            $term_id = (int) ($term['term_id'] ?? 0);
            $parent = (int) ($term['parent'] ?? 0);

            foreach ([
                 'term_id', 'count', 'filter',
                 'term_taxonomy_id', 'parent',
             ] as $key) if (isset($term[$key])) unset($term[$key]);

            $metas = $this->get_term_meta($term_id);
            if (isset($metas['lsd_image']))
            {
                $term['image'] = wp_get_attachment_url($metas['lsd_image']);
                unset($metas['lsd_image']);
            }

            $term['meta'] = $metas;

            if ($parent)
            {
                $parent_term = get_term($parent, $taxonomy);
                if ($parent_term && !is_wp_error($parent_term))
                {
                    $term['parent_slug'] = $parent_term->slug;
                }
            }

            $data[] = $term;
        }

        return $data;
    }

    public function import($file, $type = 'listings')
    {
        // Content to Import
        $content = LSD_File::read($file);

        $ex = explode('.', $file);
        $extension = strtolower(end($ex));

        switch ($extension)
        {
            case 'json':

                $result = $this->import_json($content, $type);
                self::set_last_result($result);

                return $result;

            default:
                $error = new WP_Error('lsd_import_invalid_extension', esc_html__('Invalid file extension! Only JSON files are allowed.', 'listdom'));
                self::set_last_result($error);

                return $error;
        }
    }

    public function import_json($JSON, $type = 'listings')
    {
        $decoded = json_decode($JSON, true);
        if (json_last_error() !== JSON_ERROR_NONE)
        {
            return new WP_Error('lsd_import_invalid_json', esc_html__('The uploaded file does not contain valid JSON data.', 'listdom'));
        }

        $file_type = 'listings';
        $items = [];

        if (is_array($decoded))
        {
            if (isset($decoded['type']))
            {
                $file_type = self::normalize_import_type($decoded['type']);
                $items = isset($decoded['items']) && is_array($decoded['items']) ? $decoded['items'] : [];
            }
            else
            {
                $items = $decoded;
            }
        }

        $type = self::normalize_import_type($type);
        if ($file_type !== $type)
        {
            return new WP_Error('lsd_import_type_mismatch', '', ['file_type' => $file_type]);
        }

        if ($taxonomy = self::taxonomy_for_type($type))
        {
            return $this->import_terms(is_array($items) ? $items : [], $taxonomy);
        }

        return $this->collection(is_array($items) ? $items : []);
    }

    public function collection($listings): array
    {
        $ids = [];
        foreach ($listings as $listing)
        {
            $ids[] = $this->save($listing);
        }

        do_action('lsd_import_finished');

        return $ids;
    }

    public function import_term_collection(array $terms, string $taxonomy, array $options = []): array
    {
        return $this->import_terms($terms, $taxonomy, $options);
    }

    protected function import_terms(array $terms, string $taxonomy, array $options = []): array
    {
        $ids = [];
        $pending_storage_key = 'lsd_pending_term_parents_' . $taxonomy;

        $pending_parents_option = get_option($pending_storage_key, []);
        $pending_parents = [];

        if (is_array($pending_parents_option))
        {
            foreach ($pending_parents_option as $pending)
            {
                if (!is_array($pending)) continue;

                $term_id = isset($pending['term_id']) ? (int) $pending['term_id'] : 0;
                if (!$term_id) continue;

                $pending_parents[$term_id] = [
                    'term_id' => $term_id,
                    'parent_slug' => isset($pending['parent_slug']) ? (string) $pending['parent_slug'] : '',
                    'parent' => array_key_exists('parent', $pending) ? (int) $pending['parent'] : null,
                ];
            }
        }

        foreach ($terms as $index => $term)
        {
            if (!is_array($term)) continue;

            if ($this->should_import_hierarchy_path($term, $taxonomy, $options))
            {
                $path = $this->normalize_hierarchy_path($term['path']);
                if (!count($path)) continue;

                $term_id = $this->upsert_term_by_path($path, $taxonomy, $term);
                if (!$term_id) continue;

                $meta = isset($term['meta']) && is_array($term['meta']) ? $term['meta'] : [];
                foreach ($meta as $key => $value) update_term_meta($term_id, $key, $value);

                if (!empty($term['image']))
                {
                    $attachment_id = $this->attach(trim((string) $term['image']));
                    if ($attachment_id) update_term_meta($term_id, 'lsd_image', $attachment_id);
                }

                $ids[$index] = $term_id;
                continue;
            }

            $name = isset($term['name']) ? trim((string) $term['name']) : '';
            if (!$name) continue;

            $slug = isset($term['slug']) ? sanitize_title((string) $term['slug']) : '';
            $exists = $slug ? term_exists($slug, $taxonomy) : term_exists($name, $taxonomy);

            $args = [
                'description' => $term['description'] ?? '',
            ];
            if ($slug) $args['slug'] = $slug;

            $parent_slug = isset($term['parent_slug']) ? sanitize_title((string) $term['parent_slug']) : '';
            $has_parent = ($parent_slug !== '') || array_key_exists('parent', $term);

            unset($term_id);

            if (is_array($exists) && isset($exists['term_id']))
            {
                $term_id = (int) $exists['term_id'];
                $update_args = ['name' => $name, 'description' => $args['description']];
                if (isset($args['slug'])) $update_args['slug'] = $args['slug'];

                wp_update_term($term_id, $taxonomy, $update_args);
            }
            else if ($exists)
            {
                $term_id = (int) $exists;
                $update_args = ['name' => $name, 'description' => $args['description']];
                if (isset($args['slug'])) $update_args['slug'] = $args['slug'];

                wp_update_term($term_id, $taxonomy, $update_args);
            }
            else
            {
                $insert = wp_insert_term($name, $taxonomy, $args);
                if (is_wp_error($insert)) continue;

                $term_id = (int) $insert['term_id'];
            }

            if (!isset($term_id) || !$term_id) continue;

            $meta = isset($term['meta']) && is_array($term['meta']) ? $term['meta'] : [];
            foreach ($meta as $key => $value) update_term_meta($term_id, $key, $value);

            if (!empty($term['image']))
            {
                $attachment_id = $this->attach(trim((string) $term['image']));
                if ($attachment_id) update_term_meta($term_id, 'lsd_image', $attachment_id);
            }

            $ids[$index] = $term_id;

            if ($has_parent)
            {
                $pending_parents[$term_id] = [
                    'term_id' => $term_id,
                    'parent_slug' => $parent_slug,
                    'parent' => array_key_exists('parent', $term) ? (int) $term['parent'] : null,
                ];
            }
        }

        if (count($pending_parents))
        {
            $remaining_pending_parents = $this->assign_term_parents($pending_parents, $taxonomy);

            if (count($remaining_pending_parents)) update_option($pending_storage_key, $remaining_pending_parents, false);
            else delete_option($pending_storage_key);
        }

        return $ids;
    }

    protected function assign_term_parents(array $pending_parents, string $taxonomy): array
    {
        $remaining = [];

        foreach ($pending_parents as $pending)
        {
            $term_id = isset($pending['term_id']) ? (int) $pending['term_id'] : 0;
            if (!$term_id) continue;

            $parent_id = $this->find_term_parent($pending, $taxonomy);
            if ($parent_id === null)
            {
                $remaining[$term_id] = [
                    'term_id' => $term_id,
                    'parent_slug' => isset($pending['parent_slug']) ? (string) $pending['parent_slug'] : '',
                    'parent' => array_key_exists('parent', $pending) ? (int) $pending['parent'] : null,
                ];

                continue;
            }

            wp_update_term($term_id, $taxonomy, ['parent' => $parent_id]);
        }

        return $remaining;
    }

    protected function find_term_parent(array $pending, string $taxonomy): ?int
    {
        $parent_slug = isset($pending['parent_slug']) ? (string) $pending['parent_slug'] : '';
        if ($parent_slug !== '')
        {
            $parent_term = get_term_by('slug', $parent_slug, $taxonomy);
            if ($parent_term && !is_wp_error($parent_term)) return $parent_term->term_id;
        }

        if (array_key_exists('parent', $pending))
        {
            $parent = (int) $pending['parent'];
            if ($parent > 0)
            {
                $maybe_parent = get_term($parent, $taxonomy);
                if ($maybe_parent && !is_wp_error($maybe_parent)) return $maybe_parent->term_id;
            }
            else if ($parent === 0)
            {
                return 0;
            }
        }

        return null;
    }

    protected function should_import_hierarchy_path(array $term, string $taxonomy, array $options): bool
    {
        return !empty($options['hierarchical_terms'])
            && is_taxonomy_hierarchical($taxonomy)
            && in_array($taxonomy, [LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION], true)
            && isset($term['path'])
            && is_array($term['path'])
            && count($term['path']);
    }

    protected function normalize_hierarchy_path(array $path): array
    {
        $normalized = [];
        foreach ($path as $item)
        {
            if (!is_string($item) && !is_numeric($item)) continue;

            $item = trim((string) $item);
            if ($item === '') continue;

            $normalized[] = $item;
        }

        return $normalized;
    }

    protected function upsert_term_by_path(array $path, string $taxonomy, array $term = []): int
    {
        $term_ids = $this->upsert_term_path($path, $taxonomy, $term);
        if (!count($term_ids)) return 0;

        return (int) end($term_ids);
    }

    protected function upsert_term_path(array $path, string $taxonomy, array $term = []): array
    {
        $path = $this->normalize_hierarchy_path($path);
        if (!count($path)) return [];

        $parent_id = 0;
        $total = count($path);
        $term_ids = [];

        foreach ($path as $index => $segment)
        {
            $is_leaf = $index === ($total - 1);
            $args = [];

            if ($is_leaf)
            {
                if (array_key_exists('description', $term))
                {
                    $args['description'] = (string) $term['description'];
                }

                $slug = isset($term['slug']) ? sanitize_title((string) $term['slug']) : '';
                if ($slug !== '') $args['slug'] = $slug;
            }

            $term_id = $this->upsert_term_under_parent($segment, $taxonomy, $parent_id, $args);
            if (!$term_id) return [];

            $term_ids[] = $term_id;
            $parent_id = $term_id;
        }

        return $term_ids;
    }

    protected function upsert_term_under_parent(string $name, string $taxonomy, int $parent_id = 0, array $args = []): int
    {
        $name = trim($name);
        if ($name === '') return 0;

        $slug = isset($args['slug']) ? sanitize_title((string) $args['slug']) : '';
        $has_description = array_key_exists('description', $args);

        $term_id = $slug !== '' ? $this->find_term_id_by_parent_and_slug($taxonomy, $parent_id, $slug) : 0;
        if (!$term_id) $term_id = $this->find_term_id_by_parent_and_name($taxonomy, $parent_id, $name);

        if ($term_id)
        {
            $update_args = ['name' => $name];
            if ($has_description) $update_args['description'] = (string) $args['description'];

            if ($slug !== '') $update_args['slug'] = $slug;

            wp_update_term($term_id, $taxonomy, $update_args);
            return $term_id;
        }

        $insert_args = ['parent' => $parent_id];
        if ($has_description) $insert_args['description'] = (string) $args['description'];
        if ($slug !== '') $insert_args['slug'] = $slug;

        $insert = wp_insert_term($name, $taxonomy, $insert_args);
        if (!is_array($insert) || !isset($insert['term_id'])) return 0;

        return (int) $insert['term_id'];
    }

    protected function find_term_id_by_parent_and_slug(string $taxonomy, int $parent_id, string $slug): int
    {
        $term = get_term_by('slug', $slug, $taxonomy);
        if (!$term || is_wp_error($term)) return 0;

        return (int) $term->parent === $parent_id ? (int) $term->term_id : 0;
    }

    protected function find_term_id_by_parent_and_name(string $taxonomy, int $parent_id, string $name): int
    {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'name' => $name,
            'parent' => $parent_id,
            'number' => 1,
        ]);

        if (!is_array($terms) || !count($terms)) return 0;

        $term = $terms[0];
        return isset($term->term_id) ? (int) $term->term_id : 0;
    }

    public function save($listing)
    {
        $post = [
            'post_title' => $listing['post_title'],
            'post_name' => isset($listing['post_name']) && trim($listing['post_name']) ? $listing['post_name'] : $listing['post_title'],
            'post_content' => $listing['post_content'] ?? '',
            'post_excerpt' => $listing['post_excerpt'] ?? ($listing['excerpt'] ?? ''),
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => $listing['post_status'] ?? 'publish',
            'post_date' => isset($listing['post_date']) ? lsd_date('Y-m-d', strtotime($listing['post_date'])) : null,
            'post_password' => $listing['post_password'] ?? '',
        ];

        // Don't Duplicate the Listing by Unique ID
        if (isset($listing['unique_id']) && $listing['unique_id'])
        {
            $db = new LSD_db();
            $exists = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='" . esc_sql($listing['unique_id']) . "' AND `meta_key`='lsd_sys_unique_id'", 'loadResult');

            // Perform Duplicate Detection with ID or Not.
            $id_duplicate_detection = apply_filters('lsd_ix_id_duplicate_detection', false);

            // Detect Duplicate using ID
            if (!$exists && $id_duplicate_detection)
            {
                $p = get_post($listing['unique_id']);
                if ($p && is_object($p) && isset($p->ID) && $p->post_type === LSD_Base::PTYPE_LISTING) $exists = $p->ID;
            }
        }
        // Don't Duplicate the Listing by Title and Content
        else
        {
            $exists = post_exists($listing['post_title'], $listing['post_content'] ?? '', '', LSD_Base::PTYPE_LISTING);
        }

        $mode = 'add';
        if ($exists)
        {
            $post['ID'] = $exists;
            $mode = 'edit';
        }

        // Insert User
        if (isset($listing['post_author']) && is_email($listing['post_author']))
        {
            $post['post_author'] = LSD_User::create($listing['post_author']);
        }

        // Add-ons and Third Party Applications
        $post = apply_filters('lsd_ix_before_upsert', $post, $listing);

        // Insert / Update Post
        $post_id = wp_insert_post($post);

        // Import Taxonomies
        $taxonomies = isset($listing['taxonomies']) && is_array($listing['taxonomies']) ? $listing['taxonomies'] : [];
        $primary_category_slug = '';
        foreach ($taxonomies as $taxonomy => $terms)
        {
            if (!is_array($terms) || !count($terms)) continue;

            $t = [];
            foreach ($terms as $term)
            {
                if (is_string($term)) $term = ['name' => $term];
                if (!is_array($term)) continue;

                if (isset($term['path']) && is_array($term['path']) && is_taxonomy_hierarchical($taxonomy))
                {
                    $path = $this->normalize_hierarchy_path($term['path']);
                    if (!count($path)) continue;

                    $path_ids = $this->upsert_term_path($path, $taxonomy, $term);
                    if (!count($path_ids)) continue;

                    $assign_full_path = apply_filters('lsd_ix_assign_full_hierarchy_path_terms', true, $taxonomy, $term, $listing);
                    $assigned_ids = $assign_full_path ? $path_ids : [(int) end($path_ids)];

                    foreach ($assigned_ids as $assigned_id)
                    {
                        if ($assigned_id) $t[] = (int) $assigned_id;
                    }

                    if ($taxonomy === LSD_Base::TAX_CATEGORY)
                    {
                        $leaf_category_id = (int) end($path_ids);
                        if ($leaf_category_id)
                        {
                            $primary_category = get_term($leaf_category_id, LSD_Base::TAX_CATEGORY);
                            if ($primary_category && !is_wp_error($primary_category) && isset($primary_category->slug))
                            {
                                $primary_category_slug = $primary_category->slug;
                            }
                        }
                    }

                    continue;
                }

                $term_name = isset($term['name']) ? trim($term['name']) : '';
                $term_slug = isset($term['slug']) ? sanitize_title($term['slug']) : '';
                $term_id = 0;

                if ($term_slug)
                {
                    $exists = term_exists($term_slug, $taxonomy);
                    if (!$term_name) $term_name = $term_slug;
                }
                else if ($term_name) $exists = term_exists($term_name, $taxonomy);
                else continue;

                if (is_array($exists) && isset($exists['term_id']))
                {
                    $term_id = (int) $exists['term_id'];
                }
                else if ($exists)
                {
                    $term_id = (int) $exists;
                }
                else
                {
                    $term_args = [
                        'description' => $term['description'] ?? '',
                        'parent' => isset($term['parent']) ? (int) $term['parent'] : 0,
                    ];

                    if ($term_slug) $term_args['slug'] = $term_slug;

                    // Create Term
                    $wpt = wp_insert_term($term_name, $taxonomy, $term_args);

                    // An Error Occurred
                    if (is_wp_error($wpt))
                    {
                        if ($wpt->get_error_code() === 'term_exists')
                        {
                            $existing_term_id = (int) $wpt->get_error_data('term_exists');
                            if (!$existing_term_id) $existing_term_id = (int) $wpt->get_error_data();

                            if ($existing_term_id) $term_id = $existing_term_id;
                        }

                        if (!$term_id) continue;
                    }
                    else if (!is_array($wpt) || !isset($wpt['term_id']))
                    {
                        continue;
                    }
                    else
                    {
                        // Term ID
                        $term_id = (int) $wpt['term_id'];

                        // Import Term Meta
                        if (isset($term['meta']) && is_array($term['meta']) && count($term['meta']))
                        {
                            foreach ($term['meta'] as $key => $value) update_term_meta($term_id, $key, $value);
                        }

                        // Import Image
                        if (!empty($term['image']))
                        {
                            $attachment_id = $this->attach($term['image']);
                            if ($attachment_id) update_term_meta($term_id, 'lsd_image', $attachment_id);
                        }
                    }
                }

                if ($term_id)
                {
                    $t[] = $term_id;

                    if ($taxonomy === LSD_Base::TAX_CATEGORY)
                    {
                        $primary_category = get_term($term_id, LSD_Base::TAX_CATEGORY);
                        if ($primary_category && !is_wp_error($primary_category) && isset($primary_category->slug))
                        {
                            $primary_category_slug = $primary_category->slug;
                        }
                    }
                }
            }

            $t = array_values(array_unique(array_filter(array_map('intval', $t))));
            if (count($t)) wp_set_post_terms($post_id, $t, $taxonomy);
        }

        if ($primary_category_slug !== '') update_post_meta($post_id, 'lsd_primary_category', $primary_category_slug);

        // Import Image
        if (isset($listing['image']) && trim($listing['image']))
        {
            $attachment_id = $this->attach(trim($listing['image']));
            if ($attachment_id) set_post_thumbnail($post_id, $attachment_id);
        }

        // Import Gallery
        $gallery = [];
        if (isset($listing['gallery']) && is_array($listing['gallery']) && count($listing['gallery']))
        {
            foreach ($listing['gallery'] as $image)
            {
                $attachment_id = $this->attach(trim($image));
                if ($attachment_id) $gallery[] = $attachment_id;
            }
        }

        // Import Attributes
        $attributes = [];
        if (isset($listing['attributes']) && is_array($listing['attributes']) && count($listing['attributes']))
        {
            foreach ($listing['attributes'] as $attribute)
            {
                $term = $attribute['term'] ?? [];
                if (!is_array($term) || !count($term)) continue;

                $term_name = isset($term['name']) ? trim((string) $term['name']) : '';
                $slug = isset($term['slug']) ? sanitize_title((string) $term['slug']) : '';

                if (!$term_name && $slug) $term_name = $slug;
                if (!$term_name) continue;

                $exists = $slug ? term_exists($slug, LSD_Base::TAX_ATTRIBUTE) : term_exists($term_name, LSD_Base::TAX_ATTRIBUTE);

                if (is_array($exists) && isset($exists['term_id'])) $term_id = (int) $exists['term_id'];
                else
                {
                    // Create Term
                    $wpt = wp_insert_term($term_name, LSD_Base::TAX_ATTRIBUTE, [
                        'description' => $term['description'] ?? '',
                        'parent' => isset($term['parent']) ? (int) $term['parent'] : 0,
                        'slug' => $slug,
                    ]);

                    // An Error Occurred
                    if (!is_array($wpt)) continue;

                    // Term ID
                    $term_id = (int) $wpt['term_id'];

                    // Import Term Meta for newly created attributes only.
                    if (isset($term['meta']) && is_array($term['meta']) && count($term['meta']))
                    {
                        foreach ($term['meta'] as $key => $value) update_term_meta($term_id, $key, $value);
                    }
                }

                $term_obj = get_term($term_id, LSD_Base::TAX_ATTRIBUTE);
                if (!$term_obj || is_wp_error($term_obj)) continue;

                $field_type = get_term_meta($term_obj->term_id, 'lsd_field_type', true);
                $value = $this->normalize_imported_attribute_value($attribute['value'] ?? '', $field_type);
                $this->sync_attribute_values($term_obj->term_id, $value);

                // Add to Attributes
                $attributes[$term_obj->slug] = $value;
            }
        }

        // Metas
        $metas = isset($listing['meta']) && is_array($listing['meta']) ? $listing['meta'] : [];

        $faqs = [];
        if (isset($listing['faqs'])) $faqs = $this->normalize_faqs($listing['faqs']);
        else if (isset($metas['lsd_faqs'])) $faqs = $this->normalize_faqs($metas['lsd_faqs']);

        // Prepare Data
        $data = [
            'listing_category' => null,
            'object_type' => $metas['lsd_object_type'] ?? 'marker',
            'zoomlevel' => $metas['lsd_zoomlevel'] ?? 6,
            'latitude' => $metas['lsd_latitude'] ?? null,
            'longitude' => $metas['lsd_longitude'] ?? null,
            'address' => $metas['lsd_address'] ?? '',
            'shape_type' => $metas['lsd_shape_type'] ?? '',
            'shape_paths' => $metas['lsd_shape_paths'] ?? '',
            'shape_radius' => $metas['lsd_shape_radius'] ?? '',
            'attributes' => $attributes,
            'link' => $metas['lsd_link'] ?? '',
            'price' => $metas['lsd_price'] ?? 0,
            'price_max' => $metas['lsd_price_max'] ?? 0,
            'price_after' => $metas['lsd_price_after'] ?? '',
            'currency' => $metas['lsd_currency'] ?? 'USD',
            'ava' => $metas['lsd_ava'] ?? [],
            'email' => $metas['lsd_email'] ?? '',
            'phone' => $metas['lsd_phone'] ?? '',
            'website' => $metas['lsd_website'] ?? '',
            'contact_address' => $metas['lsd_contact_address'] ?? '',
            'remark' => $metas['lsd_remark'] ?? '',
            'displ' => $metas['lsd_displ'] ?? [],
            'gallery' => $gallery,
            'faqs' => $faqs,
            'sc' => [], // Social Networks
        ];

        // Social Networks
        $SN = new LSD_Socials();

        $networks = LSD_Options::socials();
        foreach ($networks as $network => $values)
        {
            $obj = $SN->get($network, $values);

            // Social Network is Disabled or Data Not Available
            if (!$obj || !isset($metas['lsd_' . $obj->key()])) continue;

            // Save Social Network Data
            $data['sc'][$obj->key()] = $metas['lsd_' . $obj->key()];
        }

        if (isset($metas['lsd_guest_email']))
        {
            $data['guest_email'] = sanitize_email($metas['lsd_guest_email']);
            $data['guest_fullname'] = isset($metas['lsd_guest_fullname']) ? sanitize_email($metas['lsd_guest_fullname']) : '';
            $data['guest_message'] = $metas['lsd_guest_message'] ?? '';
        }

        $entity = new LSD_Entity_Listing($post_id);
        $entity->save($data, false);

        // Save the Unique ID
        if (isset($listing['unique_id']) && $listing['unique_id']) update_post_meta($post_id, 'lsd_sys_unique_id', $listing['unique_id']);

        // New Listing Imported
        do_action('lsd_listing_imported', $post_id, $listing, $mode);

        return $post_id;
    }

    protected function sync_attribute_values(int $term_id, $value): void
    {
        $field_type = get_term_meta($term_id, 'lsd_field_type', true);
        if (!in_array($field_type, ['dropdown', 'radio', 'checkbox'], true)) return;

        $values_raw = (string) get_term_meta($term_id, 'lsd_values', true);
        $existing_values = array_filter(array_map('trim', explode(',', $values_raw)));

        if (is_array($value)) $incoming_values = $value;
        else if (is_string($value)) $incoming_values = explode(',', $value);
        else if (is_numeric($value)) $incoming_values = [(string) $value];
        else $incoming_values = [];

        $added = false;

        foreach ($incoming_values as $incoming_value)
        {
            $incoming_value = trim((string) $incoming_value);
            if ($incoming_value === '') continue;

            if (!in_array($incoming_value, $existing_values, true))
            {
                $existing_values[] = $incoming_value;
                $added = true;
            }
        }

        if ($added) update_term_meta($term_id, 'lsd_values', implode(',', $existing_values));
    }

    protected function strict_datetime_from_format(string $format, string $value): ?DateTime
    {
        $dt = DateTime::createFromFormat($format, $value);
        if (!$dt instanceof DateTime) return null;

        $errors = DateTime::getLastErrors();
        if (is_array($errors) && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0)) return null;

        return $dt;
    }

    protected function normalize_imported_attribute_value($value, string $field_type)
    {
        if (is_object($value)) return '';

        if (is_array($value))
        {
            $value = array_values(array_filter(array_map(static function ($item)
            {
                return is_scalar($item) ? sanitize_text_field((string) $item) : '';
            }, $value), static function (string $item): bool
            {
                return $item !== '';
            }));

            // Preserve imported multi-value selections only for fields that store arrays.
            if ($field_type === 'checkbox') return $value;

            $value = reset($value);
            if ($value === false) return '';
        }

        if ($field_type === 'date')
        {
            $value = trim((string) $value);
            if ($value === '') return '';

            $dt = $this->strict_datetime_from_format('Y-m-d', $value);
            if ($dt instanceof DateTime) return sanitize_text_field($dt->format('Y-m-d'));
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return $value;

            $timestamp = strtotime($value);
            return $timestamp !== false ? sanitize_text_field(lsd_date('Y-m-d', $timestamp)) : $value;
        }

        if ($field_type === 'time')
        {
            $value = trim((string) $value);
            if ($value === '') return '';

            foreach (['H:i', 'H:i:s'] as $format)
            {
                $dt = $this->strict_datetime_from_format($format, $value);
                if ($dt instanceof DateTime) return sanitize_text_field($dt->format($format));
            }
            if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) return $value;

            $timestamp = strtotime($value);
            return $timestamp !== false ? sanitize_text_field(lsd_date('H:i', $timestamp)) : $value;
        }

        if ($field_type === 'datetime')
        {
            $value = trim((string) $value);
            if ($value === '') return '';

            $normalized = str_replace(' ', 'T', $value);

            foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s'] as $format)
            {
                $dt = $this->strict_datetime_from_format($format, $normalized);
                if ($dt instanceof DateTime) return sanitize_text_field($dt->format('Y-m-d\TH:i'));
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?$/', $value)) return $value;

            $timestamp = strtotime(str_replace('T', ' ', $normalized));
            return $timestamp !== false ? sanitize_text_field(lsd_date('Y-m-d\TH:i', $timestamp)) : $value;
        }

        if ($field_type !== 'file') return is_scalar($value) ? sanitize_text_field((string) $value) : '';

        $value = trim((string) $value);
        if ($value === '') return '';

        if (filter_var($value, FILTER_VALIDATE_URL))
        {
            $attachment_id = $this->attach($value);
            if ($attachment_id) return (string) $attachment_id;

            return esc_url_raw($value);
        }

        return '';
    }

    protected function normalize_export_attribute_value($value, string $field_type)
    {
        if ($field_type !== 'file') return $value;

        $url = $this->attribute_file_url($value);
        if ($url !== '') return $url;

        if (is_array($value) || is_object($value)) return '';

        return sanitize_text_field((string) $value);
    }

    protected function attribute_file_url($value): string
    {
        if (is_array($value) || is_object($value)) return '';

        $value = trim((string) $value);
        if ($value === '') return '';

        if (is_numeric($value))
        {
            $attachment_url = wp_get_attachment_url((int) $value);
            return $attachment_url ? esc_url_raw($attachment_url) : '';
        }

        if (filter_var($value, FILTER_VALIDATE_URL))
        {
            return esc_url_raw($value);
        }

        return '';
    }

    public function get_attributes($post_id): array
    {
        $attributes = [];

        $values = get_post_meta($post_id, 'lsd_attributes', true);
        if (!is_array($values)) $values = [];

        $attribute_context = LSD_Taxonomies_Attribute::context([
            'post_id' => (int) $post_id,
        ]);

        foreach ($values as $slug => $value)
        {
            $term = (array) get_term_by('slug', $slug, LSD_Base::TAX_ATTRIBUTE);

            // Term ID
            $term_id = $term['term_id'] ?? '';
            if (!$term_id || !LSD_Taxonomies_Attribute::applies((int) $term_id, $attribute_context)) continue;

            // Remove Useless Keys
            foreach ([
                 'count', 'filter', 'term_group',
                 'term_id', 'term_taxonomy_id',
             ] as $key) if (isset($term[$key])) unset($term[$key]);

            // Meta Values
            $term['meta'] = $this->get_term_meta($term_id);

            $field_type = get_term_meta($term_id, 'lsd_field_type', true);
            $value = $this->normalize_export_attribute_value($value, $field_type);

            $attributes[] = [
                'value' => $value,
                'term' => $term,
            ];
        }

        return $attributes;
    }

    public function get_taxonomies($post_id): array
    {
        $taxonomies = [];

        foreach ([
             LSD_Base::TAX_CATEGORY,
             LSD_Base::TAX_LABEL,
             LSD_Base::TAX_LOCATION,
             LSD_Base::TAX_FEATURE,
             LSD_Base::TAX_TAG,
         ] as $taxonomy)
        {
            $terms = get_the_terms($post_id, $taxonomy);
            if ($terms && !is_wp_error($terms))
            {
                $t = [];
                foreach ($terms as $term)
                {
                    // Force to Array
                    $term = (array) $term;

                    // Term ID
                    $term_id = $term['term_id'];

                    // Remove Useless Keys
                    foreach ([
                         'count', 'filter',
                         'term_group', 'term_taxonomy_id',
                     ] as $key) if (isset($term[$key])) unset($term[$key]);

                    // Metas
                    $metas = $this->get_term_meta($term_id);

                    // Image
                    if (isset($metas['lsd_image']))
                    {
                        $term['image'] = wp_get_attachment_url($metas['lsd_image']);
                        unset($metas['lsd_image']);
                    }

                    $term['meta'] = $metas;

                    $t[] = $term;
                }

                $taxonomies[$taxonomy] = $t;
            }
        }

        return $taxonomies;
    }

    public function get_gallery($post_id): array
    {
        $value = get_post_meta($post_id, 'lsd_gallery', true);
        if (!is_array($value)) $value = [];

        $gallery = [];
        foreach ($value as $attachment_id)
        {
            $image = wp_get_attachment_url($attachment_id);
            if (!$image) continue;

            $gallery[] = $image;
        }

        return $gallery;
    }

    public function attach($image)
    {
        // Already imported
        if ($attachment_id = LSD_Main::get_post_id_by_meta('lsd_imported_from', $image)) return $attachment_id;

        // Image is already exist in website
        if ($attachment_id = attachment_url_to_postid($image)) return $attachment_id;

        $buffer = LSD_File::download($image);
        if (!$buffer) return false;

        return $this->attach_by_buffer($buffer, basename($image), $image);
    }

    public function attach_by_buffer($buffer, $name, $url = null)
    {
        // Media Libraries
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $upload = wp_upload_bits($name, null, $buffer);

        $file = $upload['file'];
        $wp_filetype = wp_check_filetype($file);
        $attachment = [
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => basename($file),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attachment_id = wp_insert_attachment($attachment, $file);
        $attach_data = wp_generate_attachment_metadata($attachment_id, $file);
        wp_update_attachment_metadata($attachment_id, $attach_data);

        // Flag the attachment imported from URL
        update_post_meta($attachment_id, 'lsd_imported_from', $url);

        return $attachment_id;
    }
}
